<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function cetakLaporan(Request $request)
    {
        try {
            // Bersihkan output buffer di awal agar PDF tidak corrupt
            if (ob_get_length()) ob_end_clean();

            $status = $request->status;
            $tingkat = $request->tingkat;
            $jurusan = $request->jurusan;
            $tahun_ajaran_id = $request->tahun_ajaran_id;

            // 1. Inisialisasi Query
            $query = Absensi::with(['siswa.kelas', 'sesi.jadwal.mapel']);

            // 2. Terapkan Filter dengan casting tipe data
            if ($tahun_ajaran_id) {
                $query->where('tahun_ajaran_id', (int)$tahun_ajaran_id);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($tingkat || $jurusan) {
                $query->whereHas('siswa.kelas', function($q) use ($tingkat, $jurusan) {
                    if ($tingkat) $q->where('tingkat', (int)$tingkat);
                    if ($jurusan) $q->where('jurusan', $jurusan);
                });
            }

            // --- LOGIKA JSON (FLUTTER DASHBOARD) ---
            if (($request->expectsJson() || $request->get('format') == 'json') && !$request->is('*/pdf')) {
                
                $allData = (clone $query)->get();

                // Hitung Statistik Grafik (Wajib Loop dari allData)
                $chartData = [];
                foreach ($allData as $item) {
                    if ($item->siswa && $item->siswa->kelas) {
                        $namaKelas = trim($item->siswa->kelas->tingkat . ' ' . $item->siswa->kelas->jurusan . ' ' . ($item->siswa->kelas->nomor_kelas ?? ''));
                        $chartData[$namaKelas] = ($chartData[$namaKelas] ?? 0) + 1;
                    }
                }

                $stats = [
                    'Hadir' => $allData->where('status', 'Hadir')->count(),
                    'Sakit' => $allData->where('status', 'Sakit')->count(),
                    'Izin'  => $allData->where('status', 'Izin')->count(),
                    'Alpa'  => $allData->where('status', 'Alpa')->count(),
                ];

                $paginated = $query->latest()->paginate(5);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'filter' => $request->all(),
                        'statistik' => $stats,
                        'total_data' => $paginated->total(),
                        'statistik_grafik' => empty($chartData) ? (object)[] : $chartData,
                        'absensi' => $paginated->items()
                    ]
                ]);
            }

            // --- LOGIKA PDF (DOWNLOAD) ---
            $absensi = $query->latest()->get();
            $stats = [
                'Hadir' => $absensi->where('status', 'Hadir')->count(),
                'Sakit' => $absensi->where('status', 'Sakit')->count(),
                'Izin'  => $absensi->where('status', 'Izin')->count(),
                'Alpa'  => $absensi->where('status', 'Alpa')->count(),
            ];
            $total = $absensi->count();
            $filter = $request->all();

            $pdf = Pdf::loadView('laporan.laporan_pdf', compact('absensi', 'filter', 'stats', 'total'));

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi Kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
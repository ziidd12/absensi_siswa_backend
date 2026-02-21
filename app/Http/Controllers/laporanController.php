<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; 

class LaporanController {
    public function cetakLaporan(Request $request)
    {
        try {
            $query = Absensi::with(['siswa.kelas', 'sesi.jadwal.mapel', 'tahunAjaran']);

            // --- FILTER ---
            if ($request->tahun_ajaran_id) $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
            if ($request->status) $query->where('status', $request->status);
            if ($request->tingkat || $request->jurusan) {
                $query->whereHas('siswa.kelas', function($q) use ($request) {
                    if ($request->tingkat) $q->where('tingkat', $request->tingkat);
                    if ($request->jurusan) $q->where('jurusan', $request->jurusan);
                });
            }

            $query->latest();

            // --- LOGIKA JSON (Hanya jika BUKAN request PDF) ---
            // PENTING: Tambahkan pengecekan !$request->is('*/pdf')
            if (($request->expectsJson() || $request->get('format') == 'json') && !$request->is('*/pdf')) {
                $allDataForChart = (clone $query)->get();
                $chartData = [];
                foreach ($allDataForChart as $item) {
                    if ($item->siswa && $item->siswa->kelas) {
                        $namaKelas = trim($item->siswa->kelas->tingkat . ' ' . $item->siswa->kelas->jurusan . ' ' . ($item->siswa->kelas->nomor_kelas ?? ''));
                        $chartData[$namaKelas] = ($chartData[$namaKelas] ?? 0) + 1;
                    }
                }

                $paginated = $query->paginate(5);
                $stats = [
                    'Hadir' => $allDataForChart->where('status', 'Hadir')->count(),
                    'Sakit' => $allDataForChart->where('status', 'Sakit')->count(),
                    'Izin'  => $allDataForChart->where('status', 'Izin')->count(),
                    'Alpa'  => $allDataForChart->where('status', 'Alpa')->count(),
                ];

                return response()->json([
                    'success' => true,
                    'data' => [
                        'statistik' => $stats,
                        'total_data' => $paginated->total(),
                        'statistik_grafik' => empty($chartData) ? (object)[] : $chartData,
                        'absensi' => $paginated->items()
                    ]
                ]);
            }

            // --- LOGIKA PDF ---
            $absensi = $query->get();
            $stats = [
                'Hadir' => $absensi->where('status', 'Hadir')->count(),
                'Sakit' => $absensi->where('status', 'Sakit')->count(),
                'Izin'  => $absensi->where('status', 'Izin')->count(),
                'Alpa'  => $absensi->where('status', 'Alpa')->count(),
            ];
            
            if (ob_get_length()) ob_end_clean();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.laporan_pdf', [
                'absensi' => $absensi,
                'filter' => $request->all(),
                'stats' => $stats,
                'total' => $absensi->count()
            ]);

            return response($pdf->output(), 200)
                    ->header('Content-Type', 'application/pdf');

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
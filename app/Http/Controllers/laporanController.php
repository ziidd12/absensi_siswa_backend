<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function cetakLaporan(Request $request)
    {
        $status = $request->status;
        $tingkat = $request->tingkat;
        $jurusan = $request->jurusan;
        $tahun_ajaran_id = $request->tahun_ajaran_id;

        $filter = [
            'tingkat' => $tingkat,
            'jurusan' => $jurusan,
            'status'  => $status,
            'tahun_ajaran_id' => $tahun_ajaran_id
        ];

        // Query Utama
        $query = Absensi::with(['siswa.kelas', 'sesi.jadwal.mapel'])->latest();

        // Terapkan filter yang sama untuk Query Utama
        if ($request->status) $query->where('status', $request->status);
        if ($request->tingkat || $request->jurusan) {
            $query->whereHas('siswa.kelas', function($q) use ($request) {
                if ($request->tingkat) $q->where('tingkat', $request->tingkat);
                if ($request->jurusan) $q->where('jurusan', $request->jurusan);
            });
        }

        if ($request->expectsJson() || $request->get('format') == 'json') {
            // --- 1. AMBIL DATA UNTUK GRAFIK (KESELURUHAN) ---
            // Kita hitung semua kehadiran per kelas tanpa dipaginasi
            $allDataForChart = (clone $query)->get();
            
            $chartData = [];
            foreach ($allDataForChart as $item) {
                $namaKelas = $item->siswa->kelas->tingkat . ' ' . 
                            $item->siswa->kelas->jurusan . ' ' . 
                            ($item->siswa->kelas->nomor_kelas ?? '');
                $namaKelas = trim($namaKelas);
                
                if (!isset($chartData[$namaKelas])) {
                    $chartData[$namaKelas] = 0;
                }
                $chartData[$namaKelas]++;
            }

            // --- 2. AMBIL STATISTIK UMUM (HADIR, SAKIT, DLL) ---
            $stats = [
                'Hadir' => (clone $query)->where('status', 'Hadir')->count(),
                'Sakit' => (clone $query)->where('status', 'Sakit')->count(),
                'Izin'  => (clone $query)->where('status', 'Izin')->count(),
                'Alpa'  => (clone $query)->where('status', 'Alpa')->count(),
            ];

            // --- 3. AMBIL DATA TABEL (PAGINATION 5) ---
            $paginated = $query->paginate(5);

            return response()->json([
                'success' => true,
                'data' => [
                    'filter' => $request->all(),
                    'statistik' => $stats,
                    'total_data' => $paginated->total(),
                    'statistik_grafik' => $chartData, // <--- Data Grafik Utuh
                    'absensi' => $paginated->items()     // <--- Data Tabel Terpotong
                ]
            ]);
        }

        // --- LOGIKA KHUSUS PDF ---
        $absensi = $query->get(); // Ambil semua data untuk cetak
        $stats = [
            'Hadir' => $absensi->where('status', 'Hadir')->count(),
            'Sakit' => $absensi->where('status', 'Sakit')->count(),
            'Izin'  => $absensi->where('status', 'Izin')->count(),
            'Alpa'  => $absensi->where('status', 'Alpa')->count(),
        ];
        $total = $absensi->count();

        $pdf = Pdf::loadView('laporan.laporan_pdf', compact('absensi', 'filter', 'stats', 'total'));
        return $pdf->stream('laporan-kehadiran.pdf');
    }
}
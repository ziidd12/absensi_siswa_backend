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
        // Kita simpan tahun_ajaran_id hanya untuk tampilan di PDF, bukan untuk filter database sementara ini
        $tahun_ajaran_id = $request->tahun_ajaran_id;

        $filter = [
            'tingkat' => $tingkat,
            'jurusan' => $jurusan,
            'status'  => $status,
            'tahun_ajaran_id' => $tahun_ajaran_id
        ];

        // Query Utama - Hanya meload relasi yang pasti ada
        $query = Absensi::with(['siswa.kelas', 'sesi.jadwal.mapel']);

        // 1. Filter Status
        if ($status) {
            $query->where('status', $status);
        }

        // 2. Filter Tingkat & Jurusan (Lewat Siswa -> Kelas)
        if ($tingkat || $jurusan) {
            $query->whereHas('siswa.kelas', function($q) use ($tingkat, $jurusan) {
                if ($tingkat) $q->where('tingkat', $tingkat);
                if ($jurusan) $q->where('jurusan', $jurusan);
            });
        }

        // AMBIL DATA
        $absensi = $query->latest()->get();

        // Statistik
        $stats = [
            'Hadir' => $absensi->where('status', 'Hadir')->count(),
            'Sakit' => $absensi->where('status', 'Sakit')->count(),
            'Izin'  => $absensi->where('status', 'Izin')->count(),
            'Alpa'  => $absensi->where('status', 'Alpa')->count(),
        ];
        $total = $absensi->count();

        if ($request->expectsJson() || $request->get('format') == 'json') {
            return response()->json([
                'success' => true,
                'data' => [
                    'filter' => $filter,
                    'statistik' => $stats,
                    'total_data' => $total,
                    'absensi' => $absensi
                ]
            ]);
        }

        $pdf = Pdf::loadView('laporan.laporan_pdf', compact('absensi', 'filter', 'stats', 'total'));
        return $pdf->stream('laporan-kehadiran.pdf');
    }
}
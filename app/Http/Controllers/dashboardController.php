<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman Dashboard Utama.
     */
    public function index(Request $request)
    {
        // 1. Ambil data hari ini menggunakan Carbon
        $hariIni = Carbon::today();

        // 2. Statistik Ringkasan (Hanya untuk hari ini)
        $stats = [
            'total_siswa' => Siswa::count(),
            'hadir'       => Absensi::whereDate('created_at', $hariIni)->where('status', 'Hadir')->count(),
            'izin'        => Absensi::whereDate('created_at', $hariIni)->where('status', 'Izin')->count(),
            'sakit'       => Absensi::whereDate('created_at', $hariIni)->where('status', 'Sakit')->count(),
            'alpa'        => Absensi::whereDate('created_at', $hariIni)->where('status', 'Alpa')->count(),
        ];

        // 3. Ambil data kehadiran terbaru dengan pencarian (Search)
        $query = Absensi::with(['siswa.kelas'])->latest();

        // Jika ada input pencarian nama siswa
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->whereHas('siswa', function($q) use ($searchTerm) {
                $q->where('nama', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('nis', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Ambil 10 data terbaru
        $kehadiranTerbaru = \App\Models\Absensi::with(['siswa.kelas'])->latest()->take(10)->get();

        // 4. Kirim data ke view
        return view('dashboard', [
            'stats'            => $stats,
            'kehadiranTerbaru' => $kehadiranTerbaru,
            'tanggal'          => $hariIni->translatedFormat('d F Y') // Contoh: 08 Maret 2026
        ]);
    }
}
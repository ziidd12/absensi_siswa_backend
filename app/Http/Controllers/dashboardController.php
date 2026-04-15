<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $hariIni = Carbon::today();
        
        // Formating tanggal keur di tampilan
        $tanggal = $hariIni->translatedFormat('d F Y');

        // Data Leaderboard
        $leaderboard = Siswa::with('kelas')
            ->orderBy('points_store', 'desc')
            ->take(10)
            ->get();

        // Statistik (Mun rek dipake)
        $stats = [
            'total_siswa' => Siswa::count(),
            'hadir'       => Absensi::whereDate('created_at', $hariIni)->where('status', 'Hadir')->count(),
            'alpa'        => Absensi::whereDate('created_at', $hariIni)->where('status', 'Alpa')->count(),
        ];

        // LOGIKA PEMISAH (Meh teu nabrak)
        if ($request->is('leaderboard-ranking*')) {
            return view('leaderboardAdmin.index', compact('leaderboard', 'stats', 'tanggal'));
        }

        // Data riwayat absen jang di Dashboard utama
        $kehadiranTerbaru = Absensi::with(['siswa.kelas'])->latest()->take(10)->get();

        return view('dashboard', compact('kehadiranTerbaru', 'stats', 'leaderboard', 'tanggal'));
    }
}
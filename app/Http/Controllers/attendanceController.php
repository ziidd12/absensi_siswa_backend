<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Absensi, sesi};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class AttendanceController extends Controller {
    public function createSesi(Request $request) {
        $token = bin2hex(random_bytes(16));
        $sesi = sesi::create([
            'jadwal_id' => $request->jadwal_id,
            'tanggal' => now()->toDateString(),
            'token_qr' => $token
        ]);
        return response()->json(['status' => 'success', 'token_qr' => $token]);
    }

    public function scanQR(Request $request) {
        $user = Auth::user();
        $sesi = Sesi::where('token_qr', $request->token_qr)->firstOrFail();

        // 1. Ambil Tahun Ajaran yang sedang Aktif
        $tahunAktif = \App\Models\TahunAjaran::where('is_active', true)->first();

        if (!$tahunAktif) {
            return response()->json(['status' => 'error', 'message' => 'Tahun ajaran aktif belum disetting!'], 400);
        }

        // 2. Simpan Absensi dengan tahun_ajaran_id
        Absensi::create([
            'sesi_id' => $sesi->id,
            'siswa_id' => $user->siswa->id,
            'tahun_ajaran_id' => $tahunAktif->id, // OTOMATIS DISIMPAN DI SINI
            'waktu_scan' => now(),
            'status' => 'Hadir',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Absen Berhasil'], 200);
    }

    // private function haversine($lat1, $lon1, $lat2, $lon2) {
    //     $r = 6371000;
    //     $dLat = deg2rad($lat2 - $lat1);
    //     $dLon = deg2rad($lon2 - $lon1);
    //     $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2)**2;
    //     return $r * (2 * atan2(sqrt($a), sqrt(1-$a)));
    // }

    public function historySiswa() {
        $user = Auth::user();
        
        $history = Absensi::with(['sesi.jadwal.mapel'])
            ->where('siswa_id', $user->siswa->id)
            ->orderBy('waktu_scan', 'desc')
            ->get();

        $summary = [
            'total_hadir' => $history->where('status', 'Hadir')->count(),
            'total_izin'  => $history->where('status', 'izin')->count(),
            'total_invalid' => $history->where('is_valid', false)->count(),
        ];

        return response()->json([
            'status' => 'success',
            'summary' => $summary,
            'data' => $history
        ]);
    }
}
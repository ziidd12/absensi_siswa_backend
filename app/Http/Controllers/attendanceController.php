<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Absensi, Sesi, TahunAjaran}; // Pastikan Sesi (S besar) jika modelnya Sesi.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class AttendanceController extends Controller {
    public function createSesi(Request $request) {
        $token = bin2hex(random_bytes(16));
        $sesi = Sesi::create([
            'jadwal_id' => $request->jadwal_id,
            'tanggal' => now()->toDateString(),
            'token_qr' => $token
        ]);
        return response()->json(['status' => 'success', 'token_qr' => $token]);
    }

    public function scanQR(Request $request) {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'siswa') {
            return response()->json(['status' => 'error', 'message' => 'Hanya siswa yang dapat melakukan absensi!'], 403);
        }

        // Cari Sesi berdasarkan Token QR
        $sesi = Sesi::where('token_qr', $request->token_qr)->first();
        if (!$sesi) {
            return response()->json(['status' => 'error', 'message' => 'Token QR tidak valid atau sudah kedaluwarsa'], 404);
        }

        // 1. Ambil Tahun Ajaran yang sedang AKTIF
        $tahunAktif = TahunAjaran::where('is_active', true)->first();

        if (!$tahunAktif) {
            return response()->json(['status' => 'error', 'message' => 'Sistem gagal menemukan Tahun Ajaran aktif. Hubungi Admin.'], 400);
        }

        // Cek apakah sudah absen di sesi ini
        $sudahAbsen = Absensi::where('siswa_id', $user->siswa->id)->where('sesi_id', $sesi->id)->exists();
        if ($sudahAbsen) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah melakukan absensi pada sesi ini'], 400);
        }

        // 2. Simpan Absensi dengan tahun_ajaran_id
        Absensi::create([
            'sesi_id' => $sesi->id,
            'siswa_id' => $user->siswa->id,
            'tahun_ajaran_id' => $tahunAktif->id, // OTOMATIS TERSIMPAN
            'waktu_scan' => now(),
            'status' => 'Hadir',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Absen Berhasil'], 200);
    }

    public function historySiswa() {
        $user = Auth::user();
        
        $history = Absensi::with(['sesi.jadwal.mapel', 'tahunAjaran'])
            ->where('siswa_id', $user->siswa->id)
            ->orderBy('waktu_scan', 'desc')
            ->get();

        $summary = [
            'total_hadir' => $history->where('status', 'Hadir')->count(),
            'total_izin'  => $history->where('status', 'Izin')->count(),
            'total_sakit' => $history->where('status', 'Sakit')->count(),
            'total_alpa'  => $history->where('status', 'Alpa')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'summary' => $summary,
            'data' => $history
        ]);
    }
}
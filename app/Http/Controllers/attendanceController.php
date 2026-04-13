<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Absensi, Sesi, TahunAjaran, SesiPresensi, Siswa};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;

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
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user || $user->role !== 'siswa' || !$user->siswa) {
                return response()->json(['status' => 'error', 'message' => 'Hanya siswa yang dapat melakukan absensi!'], 403);
            }

            $sesi = Sesi::where('token_qr', $request->token_qr)->with('jadwal.kelas')->first();
            
            if (!$sesi) {
                return response()->json(['status' => 'error', 'message' => 'Token QR tidak valid atau sudah kedaluwarsa'], 404);
            }

            $siswaKelasId  = (int) $user->siswa->id_kelas;
            $jadwalKelasId = (int) ($sesi->jadwal->id_kelas ?? ($sesi->jadwal->kelas->id ?? 0));

            if ($siswaKelasId !== $jadwalKelasId) {
                return response()->json([
                    'status' => 'error', 
                    'message' => "Absensi Gagal: Anda terdaftar di kelas lain! (ID Kelas Kamu: $siswaKelasId, ID Kelas Jadwal: $jadwalKelasId)"
                ], 403);
            }

            $tahunAktif = TahunAjaran::where('is_active', true)->first();
            if (!$tahunAktif) {
                return response()->json(['status' => 'error', 'message' => 'Sistem gagal menemukan Tahun Ajaran aktif.'], 400);
            }

            $sudahAbsen = Absensi::where('siswa_id', $user->siswa->id)
                ->where('sesi_id', $sesi->id)
                ->exists();

            if ($sudahAbsen) {
                return response()->json(['status' => 'error', 'message' => 'Anda sudah melakukan absensi pada sesi ini'], 400);
            }

            Absensi::create([
                'sesi_id' => $sesi->id,
                'siswa_id' => $user->siswa->id,
                'tahun_ajaran_id' => $tahunAktif->id,
                'waktu_scan' => now(),
                'status' => 'hadir',
                'is_valid' => true,
                'lat_siswa' => $request->lat_siswa,
                'long_siswa' => $request->long_siswa,
            ]);

            // ============================================================
            // --- LOGIKA POIN BISA MINUS (UPDATE TERBARU) ---
            // ============================================================
            $siswa = $user->siswa;
            $poinPerubahan = 0;
            $pesanPoin = "";

            $jamMulai = $sesi->jadwal->jam_mulai ?? '07:10:00'; 
            $waktuSiswa = now();
            
            $start = \Carbon\Carbon::parse($jamMulai);
            $diffInMinutes = $start->diffInMinutes($waktuSiswa, false);

            if ($diffInMinutes <= 1) {
                $poinPerubahan = 10;
                $pesanPoin = "Tepat waktu! +10 Poin.";
            } elseif ($diffInMinutes > 1 && $diffInMinutes <= 10) {
                $poinPerubahan = 5;
                $pesanPoin = "Terlambat tipis. +5 Poin.";
            } elseif ($diffInMinutes > 10 && $diffInMinutes <= 15) {
                $poinPerubahan = -5;
                $pesanPoin = "Telat >10 menit. Poin -5!";
            } else {
                $poinPerubahan = -10;
                $pesanPoin = "Telat parah! Poin -10!";
            }

            // Langsung tambah/kurang tanpa filter 0
            $siswa->points_store += $poinPerubahan;
            $siswa->save(); 
            // ============================================================

            return response()->json([
                'status' => 'success', 
                'message' => 'Absen Berhasil! ' . $pesanPoin . ' Selamat belajar di kelas ' . ($sesi->jadwal->kelas->nama_kelas ?? ''),
                'poin_terbaru' => $siswa->points_store
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan di server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function historySiswa() {
        $user = Auth::user();
        $history = Absensi::with(['sesi.jadwal.mapel', 'tahunAjaran'])
            ->where('siswa_id', $user->siswa->id)
            ->orderBy('waktu_scan', 'desc')
            ->get();

        $summary = [
            'total_hadir' => $history->where('status', 'hadir')->count(),
            'total_izin'  => $history->where('status', 'izin')->count(),
            'total_sakit' => $history->where('status', 'sakit')->count(),
            'total_alpa'  => $history->where('status', 'alpa')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'summary' => $summary,
            'data' => $history
        ]);
    }

    public function storeManual(Request $request) {
        try {
            $validated = $request->validate([
                'jadwal_id' => 'required',
                'absensi'   => 'required|array',
            ]);

            $tahunAktif = TahunAjaran::where('is_active', true)->first();

            foreach ($validated['absensi'] as $item) {
                Absensi::updateOrCreate(
                    [
                        'siswa_id'  => $item['siswa_id'],
                        'jadwal_id' => $validated['jadwal_id'],
                        'tanggal'   => now()->toDateString(),
                    ],
                    [
                        'tahun_ajaran_id' => $tahunAktif ? $tahunAktif->id : null,
                        'status'          => $item['status'], 
                        'waktu_scan'      => now(),
                    ]
                );
            }

            return response()->json(['status' => 'success', 'message' => 'Absensi berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Absensi, Sesi, TahunAjaran, SesiPresensi}; 
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

            // Memastikan relasi jadwal dan kelas ikut terbawa
            $sesi = Sesi::where('token_qr', $request->token_qr)->with('jadwal.kelas')->first();
            
            if (!$sesi) {
                return response()->json(['status' => 'error', 'message' => 'Token QR tidak valid atau sudah kedaluwarsa'], 404);
            }

            // --- PERBAIKAN DI SINI (BAGIAN 3) ---
            // Kita cek langsung: Apakah kelas_id di tabel siswa SAMA dengan kelas_id di jadwal pelajaran?
            // --- PERBAIKAN AKURAT DI BAGIAN 3 ---
// Gunakan (int) untuk memastikan keduanya dibandingkan sebagai angka
// --- PERBAIKAN: Gunakan id_kelas sesuai database kamu ---
// --- PERBAIKAN SAKTI: Biar tidak terbaca 0 lagi ---
$siswaKelasId  = (int) $user->siswa->id_kelas;

// Kita ambil ID kelas dari jadwal. Jika id_kelas null, kita ambil dari relasi kelasnya langsung
$jadwalKelasId = (int) ($sesi->jadwal->id_kelas ?? ($sesi->jadwal->kelas->id ?? 0));

if ($siswaKelasId !== $jadwalKelasId) {
    $namaKelasSeharusnya = $sesi->jadwal->kelas->nama_kelas ?? 'kelas yang sesuai';
    return response()->json([
        'status' => 'error', 
        'message' => "Absensi Gagal: Anda terdaftar di kelas lain! (ID Kelas Kamu: $siswaKelasId, ID Kelas Jadwal: $jadwalKelasId)"
    ], 403);
}
            // --- AKHIR PERBAIKAN ---

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

            return response()->json([
                'status' => 'success', 
                'message' => 'Absen Berhasil! Selamat belajar di kelas ' . ($sesi->jadwal->kelas->nama_kelas ?? '')
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
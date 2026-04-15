<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Absensi, Sesi, TahunAjaran, SesiPresensi, Siswa, Redeem, PoinHistory}; // <--- Nambahkeun Redeem & PoinHistory di dieu
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller {

    /**
     * FUNGSI: Membuat sesi absensi baru (Biasanya dipicu oleh Guru di kelas)
     */
    public function createSesi(Request $request) {
        // Alur: Membuat token unik untuk keamanan QR Code agar tidak mudah ditebak
        $token = bin2hex(random_bytes(16));
        
        // Alur: Menyimpan data sesi ke database dengan menghubungkan jadwal_id dan tanggal hari ini
        $sesi = Sesi::create([
            'jadwal_id' => $request->jadwal_id,
            'tanggal' => now()->toDateString(),
            'token_qr' => $token
        ]);
        
        // Output: Mengirimkan hasil token ke aplikasi untuk ditampilkan dalam bentuk gambar QR
        return response()->json(['status' => 'success', 'token_qr' => $token]);
    }

    /**
     * FUNGSI: Menangani proses Scan QR oleh Siswa
     */
    public function scanQR(Request $request) {
        try {
            /** @var \App\Models\User $user */
            // Ambil data user yang sedang login saat ini melalui token API
            $user = Auth::user();

            // Validasi 1: Memastikan yang scan benar-benar user dengan role 'siswa'
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

            // Validasi 4: Memastikan sistem mencatat pada Tahun Ajaran yang sedang aktif (is_active = true)
            $tahunAktif = TahunAjaran::where('is_active', true)->first();
            if (!$tahunAktif) {
                return response()->json(['status' => 'error', 'message' => 'Sistem gagal menemukan Tahun Ajaran aktif.'], 400);
            }

            // Validasi 5: Mencegah kecurangan (Satu siswa tidak boleh absen 2 kali di sesi yang sama)
            $sudahAbsen = Absensi::where('siswa_id', $user->siswa->id)
                ->where('sesi_id', $sesi->id)
                ->exists();

            if ($sudahAbsen) {
                return response()->json(['status' => 'error', 'message' => 'Anda sudah melakukan absensi pada sesi ini'], 400);
            }

            // Alur: Jika semua validasi lolos, buat record baru di tabel Absensi
            Absensi::create([
                'sesi_id' => $sesi->id,
                'siswa_id' => $user->siswa->id,
                'tahun_ajaran_id' => $tahunAktif->id,
                'waktu_scan' => now(), // Mencatat jam absensi secara real-time
                'status' => 'hadir',
                'is_valid' => true,
                'lat_siswa' => $request->lat_siswa, // Menyimpan koordinat GPS Latitude
                'long_siswa' => $request->long_siswa, // Menyimpan koordinat GPS Longitude
            ]);

            // ============================================================
            // --- LOGIKA POIN BISA MINUS (UPDATE TERBARU) ---
            // ============================================================
            // ============================================================
            // --- LOGIKA POIN + ITEM SAKTI (ANTI TELAT) ---
            // ============================================================
           // ============================================================
            // --- LOGIKA POIN + FIX TIMEZONE & ITEM SAKTI ---
            // ============================================================
            // --- LOGIKA POIN + FIX TIMEZONE & ITEM SAKTI ---
            // ... (Bagian validasi di luhur tetep sarua) ...

// --- LOGIKA POIN + FIX TIMEZONE & ITEM SAKTI ---
$siswa = $user->siswa;
$poinPerubahan = 0;
$pesanPoin = "";

$waktuSekarang = \Carbon\Carbon::now('Asia/Jakarta');

// 1. Nangtukeun Jam Mulai
$jamMulaiStr = $sesi->jadwal->jam_mulai ?? '07:10:00'; 
$start = \Carbon\Carbon::parse($jamMulaiStr, 'Asia/Jakarta');
$diffInMinutes = $start->diffInMinutes($waktuSekarang, false);

// 2. LOGIKA UTAMA (Téangan voucher ngan mun TELAT hungkul)
if ($diffInMinutes <= 5) {
    // TEPAT WAKTU (Aman nepi ka menit ka-5)
    $poinPerubahan = 10;
    $pesanPoin = "Tepat waktu! +10 Poin.";
} 
elseif ($diffInMinutes > 5 && $diffInMinutes <= 15) {
    // TELAT (Menit 6 - 15) -> KAKARA DI DIEU URANG CEK VOUCHER
    $itemSakti = \App\Models\Redeem::where('siswa_id', $siswa->id)
                ->where('status', 'pending')
                ->first();

    if ($itemSakti) {
        $poinPerubahan = 0; // Poin aman (teu dikurangan)
        $pesanPoin = "Voucher SAKTI Aktif! Telat dibantu voucher, poin aman.";
        
        // PENTING: Robah status jadi 'used' meh teu bisa dipake deui isukan
        $itemSakti->status = 'used';
        $itemSakti->save();
    } else {
        $poinPerubahan = -10; 
        $pesanPoin = "Telat! Poin -10 (Maneh teu boga voucher protection, Lekk!).";
    }
} 
else {
    // TELAT PARAH (> 15 Menit) -> Voucher ge moal sanggup nulungan
    $poinPerubahan = -20;
    $pesanPoin = "Telat parah leuwih ti 15 menit! Poin -20.";
}

// 3. Update & Simpen ka Database
$siswa->points_store = $siswa->points_store + $poinPerubahan;
$siswa->save(); 

// 4. Catet History-na
\App\Models\PoinHistory::create([
    'siswa_id'       => $siswa->id,
    'judul'          => 'Poin Absensi',
    'poin_perubahan' => $poinPerubahan,
    'tipe'           => ($poinPerubahan > 0) ? 'masuk' : (($poinPerubahan < 0) ? 'keluar' : 'tetap'),
    'keterangan'     => $pesanPoin . ' (' . $waktuSekarang->format('H:i') . ')',
]);

// ... (Sésana balikkeun response json) ...

            return response()->json([
                'status' => 'success', 
                'message' => 'Absen Berhasil! ' . $pesanPoin . ' Selamat belajar di kelas ' . ($sesi->jadwal->kelas->nama_kelas ?? ''),
                'poin_terbaru' => $siswa->points_store
            ], 200);

        } catch (\Exception $e) {
            // Alur: Menangkap jika ada error pada sistem/database agar tidak berakibat crash pada aplikasi
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan di server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * FUNGSI: Menampilkan Riwayat Absensi khusus untuk profil Siswa
     */
    public function historySiswa() {
        $user = Auth::user();
        $history = Absensi::with(['sesi.jadwal.mapel', 'tahunAjaran'])
            ->where('siswa_id', $user->siswa->id)
            ->orderBy('waktu_scan', 'desc')
            ->get();

        // Alur: Menghitung total kehadiran berdasarkan status masing-masing (Hadir, Izin, Sakit, Alpa)
        // Isi: Ini menggunakan Array Asosiatif untuk membungkus ringkasan data
        $summary = [
            'total_hadir' => $history->where('status', 'hadir')->count(),
            'total_izin'  => $history->where('status', 'izin')->count(),
            'total_sakit' => $history->where('status', 'sakit')->count(),
            'total_alpa'  => $history->where('status', 'alpa')->count(),
        ];

        // Output: Mengirimkan ringkasan (summary) dan daftar riwayat lengkap (data) ke Flutter
        return response()->json([
            'status' => 'success',
            'summary' => $summary,
            'data' => $history
        ]);
    }

    /**
     * FUNGSI: Input Absensi Manual (Biasanya digunakan oleh Guru/Admin untuk banyak siswa)
     */
    public function storeManual(Request $request) {
        try {
            // Validasi: Memastikan data input jadwal ada dan data absensi berupa daftar (array)
            $validated = $request->validate([
                'jadwal_id' => 'required',
                'absensi'   => 'required|array',
            ]);

            $tahunAktif = TahunAjaran::where('is_active', true)->first();

            // Alur Looping: Memproses setiap data siswa yang dikirim di dalam daftar array
            foreach ($validated['absensi'] as $item) {
                // Alur: Menggunakan updateOrCreate (Jika data sudah ada di tgl tersebut, akan diupdate; jika belum, akan dibuatkan baru)
                Absensi::updateOrCreate(
                    [
                        'siswa_id'  => $item['siswa_id'],
                        'jadwal_id' => $validated['jadwal_id'],
                        'tanggal'   => now()->toDateString(),
                    ],
                    [
                        'tahun_ajaran_id' => $tahunAktif ? $tahunAktif->id : null,
                        'status'          => $item['status'], // Mengambil status dari inputan manual (Hadir/Izin/Sakit/Alpa)
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
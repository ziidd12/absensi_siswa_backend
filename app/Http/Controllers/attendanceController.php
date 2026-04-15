<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Absensi, Sesi, TahunAjaran, Siswa, UserToken};
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller {

    protected $pointService;

    public function __construct(PointService $pointService) {
        $this->pointService = $pointService;
    }

    public function createSesi(Request $request) {
        $request->validate(['jadwal_id' => 'required|exists:jadwal,id']);

        $token = bin2hex(random_bytes(16));
        
        $sesi = Sesi::create([
            'jadwal_id' => $request->jadwal_id,
            'tanggal' => now()->toDateString(),
            'token_qr' => $token
        ]);
        
        return response()->json([
            'status' => 'success', 
            'message' => 'Sesi presensi berhasil dibuat',
            'token_qr' => $token
        ]);
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
                return response()->json(['status' => 'error', 'message' => "Absensi Gagal: Anda bukan anggota kelas ini!"], 403);
            }

            $sudahAbsen = Absensi::where('siswa_id', $user->siswa->id)
                ->where('sesi_id', $sesi->id)
                ->exists();

            if ($sudahAbsen) {
                return response()->json(['status' => 'error', 'message' => 'Anda sudah melakukan absensi pada sesi ini'], 400);
            }

            $tahunAktif = TahunAjaran::where('is_active', true)->first();

            return DB::transaction(function () use ($sesi, $user, $tahunAktif) {
                $waktuSekarang = Carbon::now('Asia/Jakarta');
                $jamMulaiStr = $sesi->jadwal->jam_mulai ?? '07:15:00';
                $jamMulai = Carbon::parse($jamMulaiStr, 'Asia/Jakarta');
                
                $isLate = $waktuSekarang->gt($jamMulai);
                $tokenUsed = null;
                $messageStatus = "Hadir Tepat Waktu";

                if ($isLate) {
                    $tokenUsed = $this->pointService->useTokenIfLate($user->siswa->id);
                    if ($tokenUsed) {
                        $messageStatus = "Hadir (Token Digunakan)";
                    } else {
                        $messageStatus = "Hadir Terlambat";
                    }
                }

                // SIMPAN ABSENSI (Tanpa Lat & Long)
                Absensi::create([
                    'sesi_id' => $sesi->id,
                    'siswa_id' => $user->siswa->id,
                    'tahun_ajaran_id' => $tahunAktif ? $tahunAktif->id : null,
                    'waktu_scan' => $waktuSekarang,
                    'status' => 'hadir',
                    'is_valid' => true,
                ]);

                if (!$tokenUsed) {
                    $this->pointService->evaluateAttendancePoin(
                        $user->siswa->id, 
                        $waktuSekarang->toTimeString(), 
                        'siswa'
                    );
                }

                return response()->json([
                    'status' => 'success', 
                    'message' => "Absen Berhasil! Status: $messageStatus",
                    'poin_terbaru' => $this->pointService->getSiswaBalance($user->siswa->id)
                ], 200);
            });

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function storeManual(Request $request) {
        try {
            $validated = $request->validate([
                'jadwal_id' => 'required',
                'absensi'   => 'required|array',
            ]);

            $tahunAktif = TahunAjaran::where('is_active', true)->first();

            DB::transaction(function () use ($validated, $tahunAktif) {
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
                            'is_valid'        => true
                        ]
                    );

                    $this->pointService->evaluateStatusPoin($item['siswa_id'], $item['status'], 'siswa');
                }
            });

            return response()->json(['status' => 'success', 'message' => 'Absensi manual & poin berhasil diproses']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Sesi;
use App\Models\Siswa;
use App\Models\PoinHistory; // <--- WAJIB AYA
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $data = Absensi::with(['siswa', 'sesi', 'tahunAjaran'])->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => $data
            ]);
        }

        return view('absensi/index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'        => 'required|exists:siswa,id',
            'sesi_id'         => 'required|exists:sesi,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'waktu_scan'      => 'nullable',
            'status'          => 'required|in:Hadir,Izin,Sakit,Alpa',
        ]);

        $sesi = Sesi::find($validated['sesi_id']);
        $siswa = Siswa::find($validated['siswa_id']);

        if ($siswa && $sesi) {
            
            // --- LOGIKA 1: HADIR (TEPAT WAKTU VS TELAT) ---
            if ($validated['status'] === 'Hadir') {
                $jamMulaiJadwal = Carbon::createFromFormat('H:i:s', $sesi->jam_mulai);
                $batasToleransi = $jamMulaiJadwal->copy()->addMinutes(5); 
                $waktuAbsen = Carbon::now();

                if ($waktuAbsen->lessThanOrEqualTo($batasToleransi)) {
                    // TEPAT WAKTU: +5 Poin
                    $siswa->increment('points_store', 5);
                    
                    PoinHistory::create([
                        'siswa_id' => $siswa->id,
                        'poin_perubahan' => 5,
                        'keterangan' => 'Hadir Tepat Waktu (' . $sesi->nama_sesi . ')'
                    ]);
                } else {
                    // --- LOGIKA ITEM SAKTI (TAMBAHAN DI DIEU) ---
                    // Cek naha boga item aktif?
                    $itemSakti = PoinHistory::where('siswa_id', $siswa->id)
    ->where('status', 'aktif')
    ->whereNotNull('store_item_id')
    ->oldest() // Ambil voucher yang paling lama biar nggak numpuk
    ->first();

                    if ($itemSakti) {
                        // DISALAMETKEUN KU ITEM SAKTI
                        // Poin teu dikurangan, cukup itemna dijantenkeun 'used'
                        $itemSakti->update(['status' => 'used']);

                        PoinHistory::create([
                            'siswa_id' => $siswa->id,
                            'poin_perubahan' => 0,
                            'keterangan' => 'Telat dibantu ku Voucher Sakti! (' . $sesi->nama_sesi . ')'
                        ]);
                    } else {
                        // TELAT BIASA: -3 Poin (Mun teu boga voucher)
                        $potongan = 3;
                        $siswa->update([
                            'points_store' => max(0, $siswa->points_store - $potongan)
                        ]);

                        PoinHistory::create([
                            'siswa_id' => $siswa->id,
                            'poin_perubahan' => -$potongan,
                            'keterangan' => 'Telat Absen (' . $sesi->nama_sesi . ')'
                        ]);
                    }
                }
            } 
            
            // --- LOGIKA 2: ALPA ---
            else if ($validated['status'] === 'Alpa') {
                $potonganAlpa = 10;
                $siswa->update([
                    'points_store' => max(0, $siswa->points_store - $potonganAlpa)
                ]);

                PoinHistory::create([
                    'siswa_id' => $siswa->id,
                    'poin_perubahan' => -$potonganAlpa,
                    'keterangan' => 'Tidak Hadir/Alpa (' . $sesi->nama_sesi . ')'
                ]);
            }
        }

        // Simpen data absensi
        $status = Absensi::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Absensi & Riwayat Poin berhasil dicatat',
                'data' => $status
            ], 201);
        }

        return redirect('/absensi')->with('success', 'Absensi berhasil dicatat');
    }
}
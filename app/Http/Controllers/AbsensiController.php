<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa; // Tambahkan ini untuk akses model Siswa
use Illuminate\Http\Request;
use Carbon\Carbon; // Tambahkan ini untuk urusan waktu

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
            'sesi_id'         => 'required|exists:Sesi,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'waktu_scan'      => 'nullable',
            'status'          => 'required|in:Hadir,Izin,Sakit,Alpa',
        ]);

        // --- LOGIKA TAMBAH POIN STORE (MULAI) ---
        if ($validated['status'] === 'Hadir') {
            $siswa = Siswa::find($validated['siswa_id']);
            if ($siswa) {
                // Tentukan batas waktu (contoh jam 07:05:00)
                $batasWaktu = Carbon::createFromFormat('H:i:s', '07:05:00');
                $waktuSekarang = Carbon::now();

                // Cek apakah datang sebelum/pas batas waktu
                if ($waktuSekarang->lessThanOrEqualTo($batasWaktu)) {
                    $siswa->increment('points_store', 5); // Tepat waktu +5
                } else {
                    // Cek jika telat, tapi pastikan poin tidak minus di bawah 0
                    if ($siswa->points_store >= 3) {
                        $siswa->decrement('points_store', 3); // Telat -3
                    } else {
                        $siswa->points_store = 0; // Set 0 kalau poin gak cukup buat dikurangi
                        $siswa->save();
                    }
                }
            }
        }
        // --- LOGIKA TAMBAH POIN STORE (SELESAI) ---

        $status = Absensi::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Absensi berhasil dicatat',
                'data' => $status
            ], 201);
        }

        return redirect('/absensi')->with('success', 'Absensi berhasil dicatat');
    }
}
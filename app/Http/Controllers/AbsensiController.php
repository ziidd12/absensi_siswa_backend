<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Sesi;
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

        // Ambil data sesi/jadwal untuk tahu jam mulai yang seharusnya
        $sesi = Sesi::find($validated['sesi_id']); // Asumsi Sesi punya jam_mulai

        if ($validated['status'] === 'Hadir' && $sesi) {
            $siswa = Siswa::find($validated['siswa_id']);
            
            // Batas telat = Jam Mulai di Jadwal + 5 menit toleransi
            $jamMulaiJadwal = Carbon::createFromFormat('H:i:s', $sesi->jam_mulai);
            $batasToleransi = $jamMulaiJadwal->addMinutes(5); 
            
            $waktuAbsen = Carbon::now();

            if ($waktuAbsen->lessThanOrEqualTo($batasToleransi)) {
                $siswa->increment('points_store', 5);
            } else {
                // Gunakan update agar lebih stabil dan tidak double decrement yang aneh
                $currentPoints = $siswa->points_store;
                $potongan = 3; // Ubah sesuai keinginan, misal 3 atau 10
                
                $siswa->update([
                    'points_store' => max(0, $currentPoints - $potongan)
                ]);
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
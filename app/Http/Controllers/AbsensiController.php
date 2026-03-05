<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;

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
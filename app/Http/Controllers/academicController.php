<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Kelas, Mapel, TahunAjaran};

class AcademicController extends Controller {
    public function getMasterData() {
        return response()->json([
            'tahun_aktif' => TahunAjaran::where('status', true)->first(),
            'daftar_kelas' => Kelas::with('waliKelas')->get(),
            'daftar_mapel' => Mapel::all()
        ]);
    }
}
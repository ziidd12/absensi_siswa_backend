<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Kelas, Mapel, TahunAjaran};

class AcademicController extends Controller {
    public function getMasterData() {
        return response()->json([
            'tahun_aktif' => TahunAjaran::where('is_active', true)->first(),
            'daftar_tahun_ajaran' => TahunAjaran::all(),
            'daftar_kelas' => Kelas::all(),
            'daftar_mapel' => Mapel::all()
        ]);
    }
}
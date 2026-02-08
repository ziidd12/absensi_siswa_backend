<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index()
    {
        $absensi = Absensi::with(['siswa','tahunAjaran'])->get();

        return view('absensi.index', compact('absensi'));
    }

    public function create()
    {
        $siswa = Siswa::all();
        $tahun = TahunAjaran::all();

        return view('absensi.create', compact('siswa','tahun'));
    }

    public function store(Request $request)
    {
        Absensi::create($request->all());

        return redirect()->route('absensi.index');
    }
}

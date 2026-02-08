<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::all();

        return view('kelas.index', compact('kelas'));
    }

    public function store(Request $request)
    {
        Kelas::create($request->all());

        return back();
    }

    public function destroy($id)
    {
        Kelas::destroy($id);

        return back();
    }
}

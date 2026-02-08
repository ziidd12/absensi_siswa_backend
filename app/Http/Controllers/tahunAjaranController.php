<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahun = TahunAjaran::all();

        return view('tahun.index', compact('tahun'));
    }

    public function store(Request $request)
    {
        TahunAjaran::create($request->all());

        return back();
    }

    public function destroy($id)
    {
        TahunAjaran::destroy($id);

        return back();
    }
}

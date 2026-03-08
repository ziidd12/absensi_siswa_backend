<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index(Request $request)
    {
        $data = TahunAjaran::all();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => $data
            ]);
        }

        return view('tahun-ajaran.index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun'     => 'required|string',
            'semester'  => 'required|in:Ganjil,Genap',
            'is_active' => 'required|boolean',
        ]);

        $status = TahunAjaran::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Tahun ajaran berhasil ditambahkan',
                'data' => $status
            ], 201);
        }

        return redirect('/tahun-ajaran')->with('success', 'Tahun ajaran berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $ta = TahunAjaran::findOrFail($id);
        
        $validated = $request->validate([
            'tahun'     => 'required|string',
            'semester'  => 'required|in:Ganjil,Genap',
            'is_active' => 'required|boolean',
        ]);

        $ta->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Tahun ajaran berhasil diupdate',
            ]);
        }

        return redirect('/tahun-ajaran')->with('success', 'Tahun ajaran berhasil diupdate');
    }
}
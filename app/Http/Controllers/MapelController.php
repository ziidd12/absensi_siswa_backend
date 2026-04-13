<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class MapelController extends Controller
{
    /**
     * Helper untuk mencari data Mapel berdasarkan ID
     */
    private function findMapelById($id)
    {
        return Mapel::findOrFail($id);
    }

    /**
     * Menampilkan daftar mata pelajaran
     */
    public function index(Request $request)
    {
        $data = Mapel::all();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => $data
            ]);
        }

        return view('mapel/index', ['data' => $data]);
    }

    /**
     * Menyimpan data mata pelajaran baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:255|unique:mapel,nama_mapel',
        ]);

        $status = Mapel::create($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Data mata pelajaran berhasil ditambahkan',
                'data' => $status
            ], 201);
        }

        return redirect('/mapel')->with('success', 'Data mata pelajaran berhasil ditambahkan');
    }

    /**
     * Mengupdate data mata pelajaran
     */
    public function update(Request $request, $id)
    {
        $mapel = $this->findMapelById($id);
        
        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:255|unique:mapel,nama_mapel,' . $id,
        ]);

        $mapel->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Data mata pelajaran berhasil diupdate',
            ]);
        }

        return redirect('/mapel')->with('success', 'Data mata pelajaran berhasil diupdate');
    }

    /**
     * Menghapus data mata pelajaran
     */
    public function destroy(Request $request, $id)
    {
        $mapel = $this->findMapelById($id);
        $mapel->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Data mata pelajaran berhasil dihapus',
            ]);
        }

        return redirect('/mapel')->with('success', 'Data mata pelajaran berhasil dihapus');
    }

    /**
     * Contoh fungsi tambahan (Opsional) 
     * Mengambil detail mapel berdasarkan ID untuk keperluan API/Ajax
     */
    public function show($id)
    {
        try {
            $mapel = Mapel::find($id);

            if (!$mapel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mata pelajaran tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $mapel
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    private function findSiswaById($id)
    {
        return Siswa::findOrFail($id);
    }

    public function index(Request $request)
    {
        $data = Siswa::with(['user', 'kelas'])->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => $data
            ]);
        }

        return view('siswa/index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'kelas_id'   => 'required|exists:kelas,id',
            'nama_siswa' => 'required|string|max:255',
            'nis'        => 'required|string|unique:siswa,nis',
        ]);

        $status = Siswa::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Data siswa berhasil ditambahkan',
                'data' => $status
            ], 201);
        }

        return redirect('/siswa')->with('success', 'Data siswa berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $siswa = $this->findSiswaById($id);
        
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'kelas_id'   => 'required|exists:kelas,id',
            'nama_siswa' => 'required|string|max:255',
            'nis'        => 'required|string|unique:siswa,nis,' . $id,
        ]);

        $siswa->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Data siswa berhasil diupdate',
            ]);
        }

        return redirect('/siswa')->with('success', 'Data siswa berhasil diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $siswa = $this->findSiswaById($id);
        $siswa->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Data siswa berhasil dihapus',
            ]);
        }

        return redirect('/siswa')->with('success', 'Data siswa berhasil dihapus');
    }
}
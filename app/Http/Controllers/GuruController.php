<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    private function findGuruById($id)
    {
        return Guru::findOrFail($id);
    }

    public function index(Request $request)
    {
        $data = Guru::with('user')->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => $data
            ]);
        }

        return view('guru/index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'nama_guru' => 'required|string|max:255',
            'nip'       => 'required|string|unique:guru,nip',
        ]);

        $status = Guru::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Data guru berhasil ditambahkan',
                'data' => $status
            ], 201);
        }

        return redirect('/guru')->with('success', 'Data guru berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $guru = $this->findGuruById($id);
        
        $validated = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'nama_guru' => 'required|string|max:255',
            'nip'       => 'required|string|unique:guru,nip,' . $id,
        ]);

        $guru->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Data guru berhasil diupdate',
            ]);
        }

        return redirect('/guru')->with('success', 'Data guru berhasil diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $guru = $this->findGuruById($id);
        $guru->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Data guru berhasil dihapus',
            ]);
        }

        return redirect('/guru')->with('success', 'Data guru berhasil dihapus');
    }
}
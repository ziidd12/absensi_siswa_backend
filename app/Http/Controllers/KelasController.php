<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Guru;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    private function findKelasById($id)
    {
        return Kelas::findOrFail($id);
    }

    public function index(Request $request)
    {
        // Ambil semua kelas (Master Data)
        $data = Kelas::all(); 
        
        // Ambil tahun yang sedang aktif saja untuk ditampilkan di Header/Label
        $tahunAktif = TahunAjaran::where('is_active', true)->first();
        $tahunAjaran = TahunAjaran::all(); // Untuk dropdown di modal jika tetap butuh

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'tahun_aktif' => $tahunAktif,
                'data' => $data
            ]);
        }

        return view('kelas.index', compact('data', 'tahunAktif', 'tahunAjaran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tingkat'         => 'required|integer',
            'jurusan'         => 'required|string|max:20',
            'nomor_kelas'     => 'required|string|max:5',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
        ]);

        $status = Kelas::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Data kelas berhasil ditambahkan',
                'data' => $status
            ], 201);
        }

        return redirect('/kelas')->with('success', 'Data kelas berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $kelas = $this->findKelasById($id);
        
        $validated = $request->validate([
            'tingkat'         => 'required|integer',
            'jurusan'         => 'required|string|max:20',
            'nomor_kelas'     => 'required|string|max:5',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
        ]);

        $kelas->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Data kelas berhasil diupdate',
            ]);
        }

        return redirect('/kelas')->with('success', 'Data kelas berhasil diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $kelas = $this->findKelasById($id);
        $kelas->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Data kelas berhasil dihapus',
            ]);
        }

        return redirect('/kelas')->with('success', 'Data kelas berhasil dihapus');
    }

}
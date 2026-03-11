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
    // Gunakan eager loading 'with' agar relasi terpanggil
    $data = Kelas::with('tahunAjaran')->get(); 
    
    $tahunAktif = TahunAjaran::where('is_active', true)->first();
    $tahunAjaran = TahunAjaran::all();

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
    // 1. Cari tahun ajaran yang AKTIF
    $tahunAktif = TahunAjaran::where('is_active', true)->first();

    // 2. Jika tidak ada tahun aktif, jangan lanjut! 
    if (!$tahunAktif) {
        return redirect()->back()->with('error', 'Gagal: Tidak ada Tahun Ajaran yang sedang AKTIF di database.');
    }

    // 3. Gabungkan ID tahun aktif ke request
    $request->merge(['tahun_ajaran_id' => $tahunAktif->id]);

    // 4. Validasi
    $validated = $request->validate([
        'tingkat'         => 'required|integer',
        'jurusan'         => 'required|string|max:20',
        'nomor_kelas'     => 'required|string|max:5',
        'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
    ]);

    try {
        // 5. Simpan ke database
        Kelas::create($validated);
        return redirect('/kelas')->with('success', 'Data kelas berhasil ditambahkan ke periode ' . $tahunAktif->tahun);
    } catch (\Exception $e) {
        // Jika ada error database, munculkan pesannya
        return redirect()->back()->with('error', 'Error Database: ' . $e->getMessage());
    }
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
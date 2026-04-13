<?php

namespace App\Http\Controllers;

use App\Models\AnggotaKelas;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;

class AnggotaKelasController extends Controller
{
    /**
     * Menampilkan daftar anggota kelas
     */
    public function index()
    {
        // Mengambil data dengan eager loading agar tidak berat (N+1 Problem)
        $data = AnggotaKelas::with(['siswa', 'kelas'])->latest()->get();
        
        // Data pendukung untuk dropdown di Modal Tambah/Edit
        $siswa = Siswa::all();
        $kelas = Kelas::all();

        return view('anggota-kelas.index', compact('data', 'siswa', 'kelas'));
    }

    /**
     * Menyimpan data anggota kelas baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        // Cek apakah siswa tersebut sudah terdaftar di kelas manapun (opsional)
        $exists = AnggotaKelas::where('siswa_id', $request->siswa_id)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Siswa sudah terdaftar di dalam kelas.');
        }

        AnggotaKelas::create($request->all());

        return redirect()->route('anggota-kelas.index')
            ->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    /**
     * Memperbarui data anggota kelas
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $anggota = AnggotaKelas::findOrFail($id);
        $anggota->update($request->all());

        return redirect()->route('anggota-kelas.index')
            ->with('success', 'Data anggota kelas berhasil diperbarui.');
    }

    /**
     * Menghapus siswa dari kelas
     */
    public function destroy($id)
    {
        $anggota = AnggotaKelas::findOrFail($id);
        $anggota->delete();

        return redirect()->route('anggota-kelas.index')
            ->with('success', 'Siswa berhasil dihapus dari kelas.');
    }
}
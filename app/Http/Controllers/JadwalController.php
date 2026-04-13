<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    /**
     * Menampilkan daftar jadwal
     */
    public function index(Request $request)
    {
        // Ambil data berdasarkan User yang login (Guru)
        // Asumsi: Tabel 'guru' memiliki 'user_id' untuk relasi ke tabel users
        $query = Jadwal::with(['kelas', 'mapel', 'guru']);

        // Jika yang login adalah Guru, filter jadwal miliknya saja
        if (Auth::check() && Auth::user()->role === 'guru') {
            $query->whereHas('guru', function($q) {
                $q->where('user_id', Auth::id());
            });
        }

        $data = $query->orderBy('jam_mulai', 'asc')->get();

        // Response Hybrid
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        }

        $kelas = Kelas::all();
        $mapel = Mapel::all();
        $guru = Guru::all();

        return view('jadwal.index', compact('data', 'kelas', 'mapel', 'guru'));
    }

    /**
     * Menyimpan jadwal baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas_id'    => 'required|exists:kelas,id',
            'mapel_id'    => 'required|exists:mapel,id',
            'guru_id'     => 'required|exists:guru,id',
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
        ], [
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.'
        ]);

        Jadwal::create($request->all());

        return redirect()->route('jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    /**
     * Memperbarui data jadwal
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'kelas_id'    => 'required|exists:kelas,id',
            'mapel_id'    => 'required|exists:mapel,id',
            'guru_id'     => 'required|exists:guru,id',
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
        ]);

        $jadwal = Jadwal::findOrFail($id);
        $jadwal->update($request->all());

        return redirect()->route('jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    /**
     * Menghapus jadwal
     */
    public function destroy($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();

        return redirect()->route('jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}
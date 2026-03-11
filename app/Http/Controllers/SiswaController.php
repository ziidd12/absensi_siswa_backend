<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
// PENTING: Baris ini tetap ada agar tidak Error 500!
use Illuminate\Support\Facades\DB; 

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
            'id_kelas'   => 'required|exists:kelas,id',
            'nama_siswa' => 'required|string|max:255',
            'nis'        => 'required|string|unique:siswa,nis',
        ]);

        $status = Siswa::create($validated);

        if ($request->expectsJson() || $request->ajax()) {
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
            'id_kelas'   => 'required|exists:kelas,id',
            'nama_siswa' => 'required|string|max:255',
            'nis'        => 'required|string|unique:siswa,nis,' . $id,
        ]);

        $siswa->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Data siswa berhasil dihapus',
            ]);
        }

        return redirect('/siswa')->with('success', 'Data siswa berhasil dihapus');
    }

    public function getByKelas(Request $request, $nama_full)
    {
        try {
            $nama_full = trim(urldecode($nama_full));
            $jadwal_id = $request->query('jadwal_id'); 

            $parts = explode(' ', $nama_full);
            $romawiKeAngka = ['X' => '10', 'XI' => '11', 'XII' => '12'];
            
            $tingkat = $parts[0] ?? '';
            $jurusan = $parts[1] ?? '';
            $nomor   = $parts[2] ?? '';
            
            $tingkatAngka = $romawiKeAngka[strtoupper($tingkat)] ?? $tingkat;

            $kelas = Kelas::where(function($q) use ($tingkat, $tingkatAngka) {
                        $q->where('tingkat', $tingkat)->orWhere('tingkat', $tingkatAngka);
                    })
                    ->where('jurusan', 'LIKE', "%$jurusan%")
                    ->where('nomor_kelas', 'LIKE', "%$nomor%") 
                    ->first();

            if (!$kelas) {
                return response()->json(['status' => 'error', 'message' => "Kelas '$nama_full' tidak ditemukan"], 404);
            }

            $siswa = Siswa::where('id_kelas', $kelas->id)
                ->orderBy('nama_siswa', 'asc')
                ->get();

            $siswa->transform(function($s) use ($jadwal_id) {
                $s->status_absen = 'Belum';
                $s->is_locked = false;
                return $s;
            });

            return response()->json([
                'status' => 'success',
                'data' => $siswa
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // FUNGSI BARU: KHUSUS UNTUK FLUTTER (PAKAI ID)
    // ============================================================
    public function getSiswaByKelasId(Request $request, $id)
    {
        try {
            // Kita cari siswa berdasarkan id_kelas yang dikirim Flutter
            $siswa = Siswa::where('id_kelas', $id)
                ->orderBy('nama_siswa', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $siswa
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
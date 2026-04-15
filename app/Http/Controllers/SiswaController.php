<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\PoinHistory; // Wajib aya meh teu beureum
use App\Models\Redeem;
use App\Models\StoreItem; // Tambahkeun ieu bisi can aya!
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Meh auth-na aman
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
            'user_id'    => 'required',
            'id_kelas'   => 'required',
            'nama_siswa' => 'required',
            'NIS'        => 'required|unique:siswa,NIS',
        ]);

        $validated['points_store'] = 0; 

        try {
            Siswa::create($validated);
            return redirect('/siswa')->with('success', 'Data Berhasil!');
        } catch (\Exception $e) {
            dd($e->getMessage()); 
        }
    }

    public function update(Request $request, $id)
    {
        $siswa = $this->findSiswaById($id);
        
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'id_kelas'   => 'required|exists:kelas,id',
            'nama_siswa' => 'required|string|max:255',
            'NIS'        => 'required|string|unique:siswa,NIS,' . $id,
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

    public function getSiswaByKelasId(Request $request, $id)
    {
        try {
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

    public function getPointsStore() // Hapus parameter $id
{
    try {
        $user = Auth::user(); 
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'points' => (int) $siswa->points_store, 
            'nama'   => $siswa->nama_siswa
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

    // ============================================================
    // FUNGSI RIWAYAT POIN (FIXED & NO ERROR)
    // ============================================================
    public function getPoinHistory()
    {
        try {
            // Paké Auth facade meh VS Code teu beureum
            $user = Auth::user(); 

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Urang kudu login heula, Lekk!'
                ], 401);
            }

            $siswa = Siswa::where('user_id', $user->id)->first();

            if (!$siswa) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data siswa tidak ditemukan'
                ], 404);
            }

            $history = PoinHistory::where('siswa_id', $siswa->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $history
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * FUNGSI REDEEM: Témpélkeun di handap méméh kurung kurawal tutup } file SiswaController
     */
   public function redeemItem(Request $request)
{
    try {
        $user = Auth::user();
        $siswa = \App\Models\Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json(['status' => 'error', 'message' => 'Data Siswa teu kapanggih!'], 404);
        }

        $item = \App\Models\StoreItem::find($request->item_id);
        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Item Toko teu kapanggih!'], 404);
        }

        // Cek naha keur aya voucher nu pending keneh
        $sudahPunya = \App\Models\Redeem::where('siswa_id', $siswa->id)
            ->where('status', 'pending')
            ->exists();

        if ($sudahPunya) {
            return response()->json(['status' => 'error', 'message' => 'Maneh geus boga item ieu! Pake heula meh bisa meuli deui.'], 400);
        }

        if ($siswa->points_store < $item->harga_poin) {
            return response()->json(['status' => 'error', 'message' => 'Poin teu cukup, Lekk!'], 400);
        }

        // PROSES TRANSAKSI
       return DB::transaction(function () use ($siswa, $item) {
    // 1. Kurangi Poin
    $siswa->points_store = $siswa->points_store - $item->harga_poin;
    $siswa->save();

    // 2. TULIS KE RIWAYAT POIN (Dikasih Status Aktif & ID Item)
    \App\Models\PoinHistory::create([
        'siswa_id' => $siswa->id,
        'store_item_id' => $item->id, // <-- PENTING: Biar kedeteksi pas absen
        'poin_perubahan' => -$item->harga_poin,
        'keterangan' => 'Beli Item: ' . $item->nama_item,
        'status' => 'aktif', // <-- PENTING: Status awal harus aktif
    ]);

    // 3. Data Redeem (Tetap ada buat cadangan)
    \App\Models\Redeem::create([
        'siswa_id'         => $siswa->id,
        'store_item_id'    => $item->id,
        'poin_dikeluarkan' => $item->harga_poin,
        'status'           => 'pending' 
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Berhasil beli ' . $item->nama_item,
        'sisa_poin' => (int) $siswa->points_store
    ]);
});

    } catch (\Exception $e) {
        // IEU PENTING: Mun gagal, urang hayang nyaho errorna naon!
        return response()->json(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()], 500);
    }
}
}
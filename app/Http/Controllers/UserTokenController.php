<?php

namespace App\Http\Controllers;

use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserTokenController extends Controller
{
    /**
     * Sisi Admin: Monitoring semua inventory token siswa (Web)
     */
    public function index()
    {
        $tokens = UserToken::with(['siswa', 'item', 'absensi'])
            ->latest()
            ->paginate(20);

        return view('gamifikasi.tokens', compact('tokens'));
    }

    /**
     * Sisi Siswa: Melihat daftar token milik pribadi (API Flutter)
     * Digunakan untuk Tab 3: My Inventory
     */
    public function userInventory()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Validasi: Pastikan akun terhubung dengan profil siswa
        if (!$user || !$user->siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil siswa tidak ditemukan.'
            ], 404);
        }

        // 2. Ambil data token milik siswa tersebut
        // Kita sertakan 'with(item)' agar Flutter tahu nama & detail itemnya
        $tokens = UserToken::with(['item', 'absensi'])
            ->where('siswa_id', $user->siswa->id)
            ->latest()
            ->get();

        // 3. Response JSON
        return response()->json([
            'status' => 'success',
            'data' => $tokens
        ]);
    }
}
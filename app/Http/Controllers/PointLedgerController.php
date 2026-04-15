<?php

namespace App\Http\Controllers;

use App\Models\PointLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PointLedgerController extends Controller
{
    /**
     * Sisi Admin: Melihat semua mutasi poin (Web)
     */
    public function index(Request $request)
    {
        $ledgers = PointLedger::with('siswa')
            ->latest()
            ->paginate(20);

        return view('gamifikasi.ledgers', compact('ledgers'));
    }

    /**
     * Sisi Siswa: Melihat riwayat mutasi pribadi (API Flutter)
     */
    public function userHistory()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Validasi: Apakah user yang login punya profil siswa?
        if (!$user || !$user->siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil siswa tidak ditemukan. Akses ditolak.'
            ], 404);
        }

        // 2. Ambil Riwayat berdasarkan siswa_id
        $history = PointLedger::where('siswa_id', $user->siswa->id)
            ->latest()
            ->get();

        // 3. Response Standar JSON
        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
}
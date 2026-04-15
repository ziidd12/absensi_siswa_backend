<?php

namespace App\Http\Controllers;

use App\Models\FlexibilityItem;
use App\Models\UserToken;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FlexibilityController extends Controller
{
    protected $pointService;

    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    /**
     * Menampilkan daftar item marketplace dan saldo poin siswa
     * Digunakan oleh Flutter (Tab Marketplace)
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Proteksi: Pastikan user punya profil siswa
        if (!$user || !$user->siswa) {
            return response()->json(['message' => 'Hanya siswa yang dapat mengakses marketplace'], 403);
        }

        // Ambil saldo terbaru dari service (Membaca kolom points_store)
        $balance = $this->pointService->getSiswaBalance($user->siswa->id);

        // Ambil semua item marketplace
        $items = FlexibilityItem::all();

        // Response untuk Flutter
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'balance' => (int) $balance,
                    'items'   => $items
                ]
            ]);
        }

        // Response untuk Web Admin/User
        return view('user.marketplace', compact('items', 'balance'));
    }

    /**
     * Proses penukaran poin menjadi token
     * Logika: Sekali sebulan per item & Stock limit
     */
    public function redeemToken(Request $request)
    {
        $request->validate(['item_id' => 'required|exists:flexibility_items,id']);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json(['message' => 'Profil siswa tidak ditemukan'], 404);
        }

        $item = FlexibilityItem::findOrFail($request->item_id);

        // --- 1. LOGIKA SEKALI BELI DALAM SEBULAN ---
        // Mencari apakah ada transaksi item yang sama di bulan & tahun ini
        $hasPurchasedThisMonth = UserToken::where('siswa_id', $siswa->id)
            ->where('item_id', $item->id)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->exists();

        if ($hasPurchasedThisMonth) {
            $msg = "Batas Tercapai: Anda hanya bisa menukar item '" . $item->item_name . "' satu kali dalam sebulan.";
            return $request->expectsJson() 
                ? response()->json(['message' => $msg], 422) 
                : back()->with('error', $msg);
        }

        // --- 2. LOGIKA STOCK LIMIT ---
        if ($item->stock_limit <= 0) {
            $msg = "Stok Habis: Item '" . $item->item_name . "' sudah tidak tersedia untuk bulan ini.";
            return $request->expectsJson() 
                ? response()->json(['message' => $msg], 422) 
                : back()->with('error', $msg);
        }

        // --- 3. CEK SALDO POIN ---
        $currentBalance = $this->pointService->getSiswaBalance($siswa->id);
        if ($currentBalance < $item->point_cost) {
            $msg = "Poin Tidak Cukup. Saldo Anda: $currentBalance, Harga: $item->point_cost";
            return $request->expectsJson() 
                ? response()->json(['message' => $msg], 422) 
                : back()->with('error', $msg);
        }

        // --- 4. EKSEKUSI TRANSAKSI (ATOMIC) ---
        try {
            return DB::transaction(function () use ($siswa, $item, $request) {
                // A. Kurangi stok item
                $item->decrement('stock_limit');

                // B. Catat mutasi di Ledger (Buku Besar) & Update points_store siswa
                $this->pointService->addTransaction(
                    $siswa->id, 
                    'SPEND', 
                    -$item->point_cost, 
                    "Menukarkan poin untuk " . $item->item_name
                );

                // C. Tambahkan ke inventory token siswa
                $token = UserToken::create([
                    'siswa_id' => $siswa->id,
                    'item_id'  => $item->id,
                    'status'   => 'AVAILABLE'
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'status'  => 'success',
                        'message' => 'Berhasil menukarkan token ' . $item->item_name,
                        'data'    => $token
                    ]);
                }

                return back()->with('success', 'Token berhasil ditukarkan!');
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}
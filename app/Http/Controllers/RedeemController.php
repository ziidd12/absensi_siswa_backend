<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\StoreItem;
use App\Models\PoinHistory; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedeemController extends Controller
{
    public function store(Request $request)
    {
        // 1. Ambil Data (Siswa ID 1 dumasar screenshot maneh)
        $siswa = Siswa::find(1); 
        $item = StoreItem::find($request->item_id);

        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Item tidak ditemukan!'], 200);
        }

        // 2. CEK APAKAH SUDAH PUNYA ITEM AKTIF (Anti-Spam/Beli loba)
        $cekToken = PoinHistory::where('siswa_id', $siswa->id)
            ->where('store_item_id', $item->id)
            ->where('status', 'aktif') 
            ->first();

        if ($cekToken) {
            return response()->json([
                'status' => 'has_token',
                'message' => 'Gagal! Item ini masih aktif di inventori Anda.'
            ], 200);
        }

        // 3. CEK SALDO POIN
        if ($siswa->points_store < $item->harga_poin) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Maaf, saldo poin Anda tidak mencukupi.'
            ], 200);
        }

        // 4. PROSES TRANSAKSI
        DB::beginTransaction();
        try {
            // Kurangi Poin
            $siswa->decrement('points_store', $item->harga_poin);

            // Simpan History Lengkap
            PoinHistory::create([
                'siswa_id' => $siswa->id,
                'store_item_id' => $item->id,
                'poin_perubahan' => -$item->harga_poin,
                'keterangan' => 'Beli Item: ' . $item->nama_item,
                'status' => 'aktif', 
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penukaran berhasil! Item siap digunakan.',
                'sisa_poin' => $siswa->points_store
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => 'Sistem Error: ' . $e->getMessage()
            ], 200);
        }
    }
}
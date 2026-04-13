<?php

namespace App\Http\Controllers;

use App\Models\StoreItem;
use Illuminate\Http\Request;

class StoreAdminController extends Controller
{
    // Ambil semua item untuk ditampilin di Store Flutter
    public function index() {
        return response()->json([
            'status' => 'success', 
            'data' => StoreItem::all()
        ]);
    }

    // Tambah item baru (Bisa lewat Postman dulu buat ngetes)
    public function store(Request $request) {
        $request->validate([
            'nama_item' => 'required',
            'harga_poin' => 'required|integer',
        ]);

        $item = StoreItem::create($request->all());
        return response()->json([
            'status' => 'success', 
            'message' => 'Item berhasil ditambah', 
            'data' => $item
        ]);
    }

    // Update data item
    public function update(Request $request, $id) {
        $item = StoreItem::findOrFail($id);
        $item->update($request->all());
        return response()->json([
            'status' => 'success', 
            'message' => 'Item berhasil diupdate'
        ]);
    }

    // Hapus item
    public function destroy($id) {
        StoreItem::destroy($id);
        return response()->json([
            'status' => 'success', 
            'message' => 'Item berhasil dihapus'
        ]);
    }
}
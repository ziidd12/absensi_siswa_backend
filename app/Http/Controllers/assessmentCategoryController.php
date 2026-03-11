<?php

namespace App\Http\Controllers;

use App\Models\AssessmentCategory;
use Illuminate\Http\Request;

class AssessmentCategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori.
     * Web: Menampilkan halaman index.
     * API: Mengembalikan daftar kategori dalam format JSON.
     */
    public function index(Request $request)
    {
        // Mengambil kategori beserta jumlah pertanyaan di dalamnya
        $data = AssessmentCategory::withCount('questions')->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Daftar kategori penilaian berhasil diambil',
                'data' => $data
            ]);
        }

        return view('setup-penilaian.kategori.index', compact('data'));
    }

    /**
     * Menyimpan kategori baru ke database.
     */
    public function store(Request $request)
    {
        // Ini akan menghentikan program dan menampilkan data di layar
        // Jika setelah klik simpan muncul data name & description, berarti form aman.
        // dd($request->all()); 

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Pastikan nama Model-nya benar: AssessmentCategory
        \App\Models\AssessmentCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => 'student', 
            'is_active' => true
        ]);

        return redirect()->route('setup-penilaian.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan!');
    }

    /**
     * Memperbarui kategori yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required'
        ]);

        $kategori = \App\Models\AssessmentCategory::findOrFail($id);
        
        // Kita update secara manual satu per satu untuk memastikan tidak ada Mass Assignment error
        $kategori->name = $request->name;
        $kategori->description = $request->description;
        $kategori->is_active = $request->is_active;
        
        if($kategori->save()) {
            return redirect()->route('setup-penilaian.kategori.index')
                ->with('success', 'Kategori berhasil diperbarui!');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui data.');
    }

    /**
     * Menghapus kategori.
     */
    public function destroy(Request $request, $id)
    {
        $category = AssessmentCategory::findOrFail($id);
        
        // Cek jika ada relasi (opsional: agar tidak menghapus kategori yang sudah punya nilai)
        if ($category->questions()->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Gagal menghapus! Kategori masih memiliki pertanyaan aktif.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Gagal menghapus! Kategori masih memiliki pertanyaan aktif.');
        }

        $category->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil dihapus'
            ]);
        }

        return redirect()->route('setup-penilaian.kategori.index')
            ->with('success', 'Kategori berhasil dihapus');
    }

    /**
     * Fitur khusus untuk mengaktifkan/menonaktifkan kategori tanpa menghapus.
     * Sangat berguna agar data lama (riwayat nilai) tidak hilang.
     */
    public function toggleStatus(Request $request, $id)
    {
        $category = AssessmentCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Status kategori berhasil diubah',
                'is_active' => $category->is_active
            ]);
        }

        return redirect()->back()->with('success', 'Status kategori berhasil diperbarui');
    }

    /**
     * API khusus untuk dropdown di Flutter (Hanya yang aktif).
     */
    public function getActiveCategories(Request $request)
    {
        $type = $request->query('type', 'student');
        
        $categories = AssessmentCategory::where('is_active', true)
            ->where('type', $type)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }
}
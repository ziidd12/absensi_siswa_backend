<?php

namespace App\Http\Controllers;

use App\Models\AssessmentQuestion;
use App\Models\AssessmentCategory;
use Illuminate\Http\Request;

class AssessmentQuestionController extends Controller
{
    /**
     * Menampilkan daftar semua pertanyaan.
     * Bisa difilter berdasarkan category_id melalui query string.
     */
    public function index(Request $request)
    {
        $categoryId = $request->query('category_id');
        
        // Ambil data kategori agar nama kategori bisa tampil di judul
        $category = \App\Models\AssessmentCategory::findOrFail($categoryId);

        // Ambil pertanyaan yang filternya berdasarkan kategori tersebut
        $questions = \App\Models\AssessmentQuestion::where('category_id', $categoryId)
                    ->orderBy('created_at', 'desc')
                    ->get();

        // KIRIM KEDUA VARIABLE INI KE VIEW
        return view('setup-penilaian.pertanyaan.index', compact('questions', 'category'));
    }

    /**
     * Menyimpan pertanyaan/indikator baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'   => 'required|exists:assessment_categories,id',
            'question_text' => 'required|string|max:500',
        ]);

        $question = AssessmentQuestion::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Indikator penilaian berhasil ditambahkan',
                'data' => $question->load('category')
            ], 201);
        }

        return redirect()->back()->with('success', 'Indikator penilaian berhasil ditambahkan');
    }

    /**
     * Memperbarui teks pertanyaan atau kategorinya.
     */
    public function update(Request $request, $id)
    {
        $question = AssessmentQuestion::findOrFail($id);

        $validated = $request->validate([
            'category_id'   => 'required|exists:assessment_categories,id',
            'question_text' => 'required|string|max:500',
        ]);

        $question->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Indikator berhasil diperbarui',
                'data' => $question
            ]);
        }

        return redirect()->back()->with('success', 'Indikator berhasil diperbarui');
    }

    /**
     * Menghapus pertanyaan.
     */
    public function destroy(Request $request, $id)
    {
        $question = AssessmentQuestion::findOrFail($id);

        // Proteksi: Jangan hapus jika sudah pernah digunakan dalam penilaian (AssessmentDetail)
        if ($question->details()->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Indikator tidak bisa dihapus karena sudah memiliki riwayat penilaian.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Indikator tidak bisa dihapus karena sudah memiliki riwayat penilaian.');
        }

        $question->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Indikator berhasil dihapus'
            ]);
        }

        return redirect()->back()->with('success', 'Indikator berhasil dihapus');
    }

    /**
     * API Khusus untuk Flutter: Mengambil pertanyaan berdasarkan kategori tertentu.
     * Digunakan saat user memilih kategori di aplikasi.
     */
    public function getByCategory($categoryId)
    {
        $questions = AssessmentQuestion::where('category_id', $categoryId)->get();

        return response()->json([
            'status' => 'success',
            'category_id' => $categoryId,
            'data' => $questions
        ]);
    }
}
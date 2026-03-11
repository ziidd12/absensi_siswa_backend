<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Assessment, AssessmentCategory, Siswa}; // Sesuaikan Model yang digunakan
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    // 1. Mengambil daftar kategori untuk ditampilkan di Flutter (Sekarang include type & default_score)
    public function getCategories()
    {
        $categories = AssessmentCategory::all();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    // 2. Simpan Penilaian dari Guru (Versi Simple untuk satu input per klik)
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                
                // Validasi data yang masuk dari Flutter
                $request->validate([
                    'siswa_id'    => 'required',
                    'category_id' => 'required',
                    'score'       => 'required',
                ]);

                // Simpan langsung ke tabel Assessments
                // Pastikan migration tabel assessments kamu sudah punya kolom-kolom ini
                $assessment = Assessment::create([
                    'evaluator_id'  => Auth::id(), // ID Guru yang login
                    'siswa_id'      => $request->siswa_id,
                    'category_id'   => $request->category_id,
                    'score'         => $request->score,
                    'period'        => $request->period ?? 'Ganjil 2026',
                    'general_notes' => $request->general_notes, // Nama field sesuaikan dengan Flutter kamu
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Penilaian berhasil disimpan!',
                    'data' => $assessment
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal simpan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan batch penilaian (untuk form dengan multiple kategori)
     */
    public function storeBatch(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate([
                    'siswa_id' => 'required',
                    'scores' => 'required|array',
                    'scores.*.category_id' => 'required',
                    'scores.*.score' => 'required|numeric|min:0|max:100',
                ]);

                $assessment = Assessment::create([
                    'evaluator_id' => Auth::id(),
                    'siswa_id' => $request->siswa_id,
                    'period' => $request->period ?? 'Ganjil 2026',
                    'general_notes' => $request->general_notes,
                ]);

                foreach ($request->scores as $scoreData) {
                    AssessmentDetail::create([
                        'assessment_id' => $assessment->id,
                        'category_id' => $scoreData['category_id'],
                        'score' => $scoreData['score'],
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Penilaian berhasil disimpan!',
                    'data' => $assessment->load('details')
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal simpan: ' . $e->getMessage()
            ], 500);
        }
    }
}
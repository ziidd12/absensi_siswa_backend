<?php

namespace App\Http\Controllers; // Namespace tetap di folder utama

use Illuminate\Http\Request;
use App\Models\StudentRating; 
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    /**
     * Fungsi untuk GURU menyimpan atau update penilaian siswa
     */
    public function simpanPenilaian(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required',
            'kedisiplinan' => 'required|numeric',
            'kerja_sama' => 'required|numeric',
            'tanggung_jawab' => 'required|numeric',
            'inisiatif' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $rating = StudentRating::updateOrCreate(
                ['siswa_id' => $request->siswa_id],
                [
                    'guru_id' => $request->guru_id ?? 1, 
                    'kedisiplinan' => $request->kedisiplinan,
                    'kerja_sama' => $request->kerja_sama,
                    'tanggung_jawab' => $request->tanggung_jawab,
                    'inisiatif' => $request->inisiatif,
                    'catatan' => $request->catatan ?? 'Penilaian otomatis sistem.',
                ]
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Mantap Lek, data masuk!',
                'data' => $rating
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fungsi baru untuk SISWA mengambil data penilaiannya sendiri
     */
    public function getRatingSiswa($siswa_id)
    {
        try {
            // Mencari data rating berdasarkan ID siswa di tabel student_ratings
            $rating = StudentRating::where('siswa_id', $siswa_id)->first();

            if (!$rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada penilaian untuk kamu hari ini.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $rating
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }
}
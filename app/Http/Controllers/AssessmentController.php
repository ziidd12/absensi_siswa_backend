<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentDetail;
use App\Models\AssessmentCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    /**
     * Tampilan Web: Daftar riwayat penilaian terbaru.
     */
    public function index(Request $request)
    {
        $data = Assessment::with(['evaluator', 'siswaUser', 'tahunAjaran'])
            ->latest()
            ->paginate(10);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $data]);
        }

        return view('assessments.index', compact('data'));
    }

    /**
     * API untuk Flutter: Mengambil daftar siswa yang bisa dinilai.
     * Disertai info apakah sudah dinilai di tahun ajaran aktif atau belum.
     */
    public function getStudentsToAssess(Request $request)
    {
        // Gunakan default 1 jika param kosong agar data tidak []
        $tahunAjaranId = $request->query('tahun_ajaran_id', 1); 

        $students = User::where('role', 'siswa')
            ->with(['siswa', 'assessmentsReceived' => function ($query) use ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            }])
            ->get()
            ->map(function ($user) {
                $user->nis = $user->siswa ? $user->siswa->nis : '-';
                
                // PAKSA mapping ke snake_case agar terbaca oleh Flutter
                $user->assessments_received = $user->assessmentsReceived;
                
                return $user;
            });

        return response()->json([
            'status' => 'success',
            'data' => $students // Bungkus dalam 'data' agar sesuai ApiService Flutter
        ]);
    }

    /**
     * API untuk Flutter/Web: Mengambil struktur form penilaian (Kategori -> Pertanyaan).
     */
    public function getAssessmentForm(Request $request)
    {
        // 'type' bisa disesuaikan apakah untuk 'student' atau 'employee'
        $type = $request->query('type', 'student');

        $formStructure = AssessmentCategory::where('is_active', true)
            ->where('type', $type)
            ->with(['questions'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $formStructure
        ]);
    }

    /**
     * Simpan Penilaian Baru (Header & Detail).
     * Menggunakan Database Transaction untuk memastikan data aman.
     */
    public function store(Request $request)
    {
        $request->validate([
            'evaluatee_id'    => 'required|exists:users,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'assessment_date' => 'required|date',
            'scores'          => 'required|array', 
            'scores.*.question_id' => 'required|exists:assessment_questions,id',
            'scores.*.score'       => 'required|numeric|min:1|max:10', // Skala 1-10
            'general_notes'   => 'nullable|string'
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Simpan Header Penilaian
                $assessment = Assessment::create([
                    'evaluator_id'    => Auth::id(),
                    'evaluatee_id'    => $request->evaluatee_id,
                    'tahun_ajaran_id' => $request->tahun_ajaran_id,
                    'assessment_date' => $request->assessment_date,
                    'general_notes'   => $request->general_notes
                ]);

                // 2. Simpan Detail (Nilai per Indikator/Pertanyaan)
                $details = [];
                foreach ($request->scores as $item) {
                    $details[] = [
                        'assessment_id' => $assessment->id,
                        'question_id'   => $item['question_id'],
                        'score'         => $item['score'],
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
                
                AssessmentDetail::insert($details); // Menggunakan insert batch untuk performa

                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Penilaian berhasil disimpan',
                        'assessment_id' => $assessment->id
                    ], 201);
                }

                return redirect()->route('web.assessments.index')->with('success', 'Penilaian berhasil disimpan');
            });
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(), // Tampilkan pesan error asli (misal: "Column not found")
                    'trace' => $e->getTrace()[0]   // Kasih tahu di baris mana dia error
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Melihat detail penilaian tertentu.
     */
    public function show(Request $request, $id)
    {
        $assessment = Assessment::with([
            'evaluator', 
            'siswaUser', 
            'details.question.category'
        ])->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $assessment]);
        }

        return view('assessments.show', compact('assessment'));
    }

    /**
     * Menghapus data penilaian.
     */
    public function destroy(Request $request, $id)
    {
        $assessment = Assessment::findOrFail($id);
        $assessment->delete(); // Detail akan terhapus otomatis jika migrations menggunakan onDelete('cascade')

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Data penilaian berhasil dihapus']);
        }

        return redirect()->back()->with('success', 'Data penilaian berhasil dihapus');
    }
}
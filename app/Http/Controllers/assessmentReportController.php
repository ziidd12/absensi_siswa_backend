<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentDetail;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentReportController extends Controller
{
    /**
     * Helper: Mendapatkan ID Tahun Ajaran Aktif
     */
    private function getActiveYearId($request) {
        if ($request->has('tahun_ajaran_id') && (int)$request->tahun_ajaran_id > 0) {
            return $request->tahun_ajaran_id;
        }
        $activeYear = TahunAjaran::where('is_active', true)->first();
        return $activeYear ? $activeYear->id : null;
    }

    /**
     * API UTAMA UNTUK FLUTTER: studentPerformance
     * Digunakan untuk Radar Chart dan Riwayat Penilaian
     */
    public function studentPerformance(Request $request) {
        $userId = $request->query('student_id') ?: Auth::id(); 
        $tahunAjaranId = $this->getActiveYearId($request); 

        // 1. Query Radar Chart - Mengelompokkan rata-rata score per kategori
        // Join: details -> questions -> categories
        $scores = AssessmentDetail::join('assessment_questions', 'assessment_questions.id', '=', 'assessment_details.question_id')
            ->join('assessment_categories', 'assessment_categories.id', '=', 'assessment_questions.category_id')
            ->join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            ->where('assessments.evaluatee_id', $userId)
            ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                return $q->where('assessments.tahun_ajaran_id', $tahunAjaranId);
            })
            ->select(
                'assessment_categories.name', 
                DB::raw('CAST(AVG(score) AS FLOAT) as average_score')
            )
            ->groupBy('assessment_categories.name')
            ->get();

        // 2. Query Riwayat Lengkap untuk Timeline
        $history = Assessment::where('evaluatee_id', $userId)
            ->with(['evaluator:id,name', 'details.question'])
            ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                return $q->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->latest()
            ->get();

        $totalAvg = $scores->avg('average_score') ?? 0;

        return response()->json([
            'status' => 'success',
            'total_score' => round($totalAvg, 1),
            'scores' => $scores,
            'history' => $history
        ]);
    }

    /**
     * API: Rekapitulasi Sederhana untuk Dashboard Siswa
     */
    public function student(Request $request)
    {
        $userId = Auth::id();
        $tahunAjaranId = $this->getActiveYearId($request);

        $data = AssessmentDetail::join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            ->join('assessment_questions', 'assessment_questions.id', '=', 'assessment_details.question_id')
            ->join('assessment_categories', 'assessment_categories.id', '=', 'assessment_questions.category_id')
            ->where('assessments.evaluatee_id', $userId)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('assessments.tahun_ajaran_id', $tahunAjaranId);
            })
            ->select('assessment_categories.name', DB::raw('CAST(AVG(score) AS FLOAT) as average_score'))
            ->groupBy('assessment_categories.name')
            ->get();

        return response()->json($data);
    }

    /**
     * WEB ADMIN: Daftar Laporan Seluruh Siswa
     */
    public function index(Request $request)
    {
        $tahunId = $this->getActiveYearId($request);
        $tingkat = $request->get('tingkat');
        $jurusan = $request->get('jurusan');

        $query = User::where('role', 'siswa');

        // Filter berdasarkan Kelas (melalui relasi anggotaKelas jika ada)
        if ($tingkat || $jurusan) {
            $query->whereHas('siswa', function($q) use ($tingkat, $jurusan) {
                $q->whereHas('kelas', function($k) use ($tingkat, $jurusan) {
                    if ($tingkat) $k->where('tingkat', $tingkat);
                    if ($jurusan) $k->where('jurusan', $jurusan);
                });
            });
        }

        $data = $query->with([
            'siswa.kelas',
            'assessmentsReceived' => function($q) use ($tahunId) {
                if ($tahunId) $q->where('tahun_ajaran_id', $tahunId);
                $q->with('details');
            }
        ])->get();

        $years = TahunAjaran::orderBy('id', 'desc')->get();
        $majors = Kelas::distinct()->pluck('jurusan');

        return view('assessment-reports.index', compact('data', 'years', 'majors'));
    }

    /**
     * WEB ADMIN: Detail Laporan per Siswa
     */
    public function show(Request $request, $id)
    {
        $user = User::with('siswa.kelas')->findOrFail($id);
        $tahunAjaranId = $this->getActiveYearId($request);

        $scores = AssessmentDetail::join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            ->join('assessment_questions', 'assessment_questions.id', '=', 'assessment_details.question_id')
            ->join('assessment_categories', 'assessment_categories.id', '=', 'assessment_questions.category_id')
            ->where('assessments.evaluatee_id', $id)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('assessments.tahun_ajaran_id', $tahunAjaranId);
            })
            ->select('assessment_categories.name', DB::raw('CAST(AVG(score) AS FLOAT) as average_score'))
            ->groupBy('assessment_categories.name')
            ->get();

        return view('assessment-reports.show', compact('user', 'scores'));
    }

    /**
     * API: Progres Penilaian Guru
     */
    public function teacherProgress(Request $request) {
        $guruId = Auth::id();
        $tahunAjaranId = $this->getActiveYearId($request);

        $totalStudents = User::where('role', 'siswa')->count();
        
        $assessedCount = Assessment::where('evaluator_id', $guruId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->distinct('evaluatee_id')
            ->count();

        return response()->json([
            'total' => $totalStudents,
            'assessed' => $assessedCount,
            'percentage' => $totalStudents > 0 ? round(($assessedCount / $totalStudents) * 100, 1) : 0
        ]);
    }
}
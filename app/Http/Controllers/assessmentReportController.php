<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentDetail;
use App\Models\AssessmentCategory;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Models\Siswa;
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
        $activeYear = TahunAjaran::where('is_active', 1)->first();
        return $activeYear ? $activeYear->id : null;
    }

    /**
     * Helper: Mendapatkan data tahun ajaran aktif
     */
    private function getActiveYear() {
        return TahunAjaran::where('is_active', 1)->first();
    }

    /**
     * Menampilkan halaman utama laporan penilaian (Web View)
     */
    public function index(Request $request)
    {
        $tahunId = $this->getActiveYearId($request);
        $tahunAktif = $this->getActiveYear();
        $tingkat = $request->get('tingkat');
        $jurusan = $request->get('jurusan');
        $kelasId = $request->get('kelas_id');

        // Query untuk mendapatkan data siswa (User dengan role siswa)
        $query = User::where('role', 'siswa');

        // Filter berdasarkan kelas
        if ($kelasId) {
            $query->whereHas('siswa', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        } 
        // Filter berdasarkan tingkat dan jurusan
        elseif ($tingkat || $jurusan) {
            $query->whereHas('siswa.kelas', function($q) use ($tingkat, $jurusan) {
                if ($tingkat) $q->where('tingkat', $tingkat);
                if ($jurusan) $q->where('jurusan', $jurusan);
            });
        }

        // Ambil data dengan relasi
        $data = $query->with([
            'siswa' => function($q) {
                $q->with('kelas');
            },
            'assessmentsReceived' => function($q) use ($tahunId) {
                if ($tahunId) {
                    $q->where('tahun_ajaran_id', $tahunId);
                }
                $q->with('details');
            }
        ])->get();

        // Data untuk filter
        $years = TahunAjaran::orderBy('tahun', 'desc')->get();
        $majors = Kelas::distinct()->pluck('jurusan');
        
        // Ambil daftar kelas berdasarkan filter tingkat/jurusan
        $classes = Kelas::when($tingkat, function($q) use ($tingkat) {
                return $q->where('tingkat', $tingkat);
            })
            ->when($jurusan, function($q) use ($jurusan) {
                return $q->where('jurusan', $jurusan);
            })
            ->get();

        return view('assessment-reports.index', compact(
            'data', 
            'years', 
            'majors', 
            'classes', 
            'tahunAktif',
            'tahunId', 
            'tingkat', 
            'jurusan', 
            'kelasId'
        ));
    }

    /**
     * Menampilkan detail laporan per siswa
     */
    public function show(Request $request, $id)
    {
        $siswa = User::with(['siswa.kelas', 'assessmentsReceived.details.category'])
            ->where('role', 'siswa')
            ->findOrFail($id);
            
        $tahunAjaranId = $this->getActiveYearId($request);
        $tahunAktif = $this->getActiveYear();

        // Ambil nilai per kategori
        $scores = AssessmentDetail::join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            ->join('assessment_categories', 'assessment_categories.id', '=', 'assessment_details.category_id')
            ->where('assessments.siswa_id', $siswa->siswa->id ?? 0)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('assessments.tahun_ajaran_id', $tahunAjaranId);
            })
            ->select(
                'assessment_categories.name', 
                DB::raw('CAST(AVG(assessment_details.score) AS DECIMAL(10,2)) as average_score'),
                DB::raw('COUNT(assessment_details.id) as total_data')
            )
            ->groupBy('assessment_categories.name')
            ->get();

        // Ambil riwayat penilaian
        $history = Assessment::with(['evaluator', 'details.category'])
            ->where('siswa_id', $siswa->siswa->id ?? 0)
            ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                return $q->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->latest()
            ->get();

        return view('assessment-reports.show', compact('siswa', 'scores', 'history', 'tahunAktif'));
    }

    /**
     * Filter laporan berdasarkan parameter (AJAX)
     */
    public function filter(Request $request)
    {
        $tahunId = $this->getActiveYearId($request);
        $tingkat = $request->get('tingkat');
        $jurusan = $request->get('jurusan');
        $kelasId = $request->get('kelas_id');

        $query = User::where('role', 'siswa');

        if ($kelasId) {
            $query->whereHas('siswa', function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        } elseif ($tingkat || $jurusan) {
            $query->whereHas('siswa.kelas', function($q) use ($tingkat, $jurusan) {
                if ($tingkat) $q->where('tingkat', $tingkat);
                if ($jurusan) $q->where('jurusan', $jurusan);
            });
        }

        $data = $query->with([
            'siswa.kelas',
            'assessmentsReceived' => function($q) use ($tahunId) {
                if ($tahunId) $q->where('tahun_ajaran_id', $tahunId);
            }
        ])->get();

        $classes = Kelas::when($tingkat, function($q) use ($tingkat) {
                return $q->where('tingkat', $tingkat);
            })
            ->when($jurusan, function($q) use ($jurusan) {
                return $q->where('jurusan', $jurusan);
            })
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'data' => $data,
                'classes' => $classes
            ]);
        }

        return redirect()->route('laporan-penilaian.index');
    }

    /**
     * Export laporan (Excel/PDF)
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $tahunId = $this->getActiveYearId($request);
        
        $data = User::where('role', 'siswa')
            ->with(['siswa.kelas', 'assessmentsReceived' => function($q) use ($tahunId) {
                if ($tahunId) $q->where('tahun_ajaran_id', $tahunId);
                $q->with('details.category');
            }])
            ->get()
            ->map(function($siswa) {
                $totalScore = $siswa->assessmentsReceived->flatMap(function($a) {
                    return $a->details;
                })->avg('score') ?? 0;
                
                return [
                    'nama' => $siswa->name,
                    'nis' => $siswa->siswa->nis ?? '-',
                    'kelas' => $siswa->siswa->kelas->nama_kelas ?? '-',
                    'total_penilaian' => $siswa->assessmentsReceived->count(),
                    'rata_rata' => number_format($totalScore, 2),
                    'status' => $totalScore >= 75 ? 'Lulus' : ($totalScore >= 60 ? 'Cukup' : 'Kurang')
                ];
            });

        if ($format === 'excel') {
            // Redirect dengan pesan (sementara)
            return redirect()->route('laporan-penilaian.index')
                ->with('info', 'Fitur export Excel sedang dalam pengembangan');
        } else {
            // Redirect dengan pesan (sementara)
            return redirect()->route('laporan-penilaian.index')
                ->with('info', 'Fitur export PDF sedang dalam pengembangan');
        }
    }

    /**
     * Statistik per kelas
     */
    public function classStatistics(Request $request, $kelasId)
    {
        $kelas = Kelas::findOrFail($kelasId);
        $tahunId = $this->getActiveYearId($request);
        
        $siswa = Siswa::where('kelas_id', $kelasId)
            ->with(['user', 'assessments' => function($q) use ($tahunId) {
                if ($tahunId) $q->where('tahun_ajaran_id', $tahunId);
                $q->with('details');
            }])
            ->get();

        $statistics = $siswa->map(function($s) {
            $avgScore = $s->assessments->flatMap(function($a) {
                return $a->details;
            })->avg('score') ?? 0;
            
            return [
                'siswa_id' => $s->id,
                'nama' => $s->user->name ?? $s->nama,
                'nis' => $s->nis,
                'average_score' => round($avgScore, 2),
                'total_assessments' => $s->assessments->count()
            ];
        })->sortByDesc('average_score')->values();

        $classAvg = $statistics->avg('average_score');
        $topStudent = $statistics->first();
        $lowestStudent = $statistics->last();

        if ($request->ajax()) {
            return response()->json([
                'kelas' => $kelas->nama_kelas,
                'total_siswa' => $statistics->count(),
                'class_average' => round($classAvg, 2),
                'top_student' => $topStudent,
                'lowest_student' => $lowestStudent,
                'statistics' => $statistics
            ]);
        }

        return view('assessment-reports.class-statistics', compact('kelas', 'statistics', 'classAvg', 'topStudent', 'lowestStudent'));
    }

    /**
     * Laporan per kategori
     */
    public function categoryReport(Request $request, $categoryId)
    {
        $category = AssessmentCategory::findOrFail($categoryId);
        $tahunId = $this->getActiveYearId($request);

        $data = AssessmentDetail::join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            ->join('siswa', 'siswa.id', '=', 'assessments.siswa_id')
            ->join('users', 'users.id', '=', 'siswa.user_id')
            ->where('assessment_details.category_id', $categoryId)
            ->when($tahunId, function($query) use ($tahunId) {
                return $query->where('assessments.tahun_ajaran_id', $tahunId);
            })
            ->select(
                'siswa.id as siswa_id',
                'users.name as siswa_name',
                'siswa.nis',
                DB::raw('CAST(AVG(assessment_details.score) AS DECIMAL(10,2)) as average_score'),
                DB::raw('COUNT(assessments.id) as total_assessments')
            )
            ->groupBy('siswa.id', 'users.name', 'siswa.nis')
            ->orderBy('average_score', 'desc')
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'category' => $category->name,
                'description' => $category->description,
                'data' => $data
            ]);
        }

        return view('assessment-reports.category-report', compact('category', 'data'));
    }

    // ==================== API METHODS UNTUK FLUTTER ====================

    /**
     * API UTAMA UNTUK FLUTTER: studentPerformance
     */
    public function studentPerformance(Request $request) {
        $studentId = $request->query('student_id');
        
        if (!$studentId) {
            $user = Auth::user();
            $siswa = Siswa::where('user_id', $user->id)->first();
            $studentId = $siswa ? $siswa->id : null;
        }
        
        $tahunAjaranId = $this->getActiveYearId($request); 

        if (!$studentId) {
            return response()->json([
                'error' => 'Data siswa tidak ditemukan'
            ], 404);
        }

        $scores = AssessmentDetail::join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            ->join('assessment_categories', 'assessment_categories.id', '=', 'assessment_details.category_id')
            ->where('assessments.siswa_id', $studentId)
            ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                return $q->where('assessments.tahun_ajaran_id', $tahunAjaranId);
            })
            ->select(
                'assessment_categories.name', 
                DB::raw('CAST(AVG(assessment_details.score) AS FLOAT) as average_score')
            )
            ->groupBy('assessment_categories.name')
            ->get();

        $history = Assessment::with(['evaluator', 'details.category'])
            ->where('siswa_id', $studentId)
            ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                return $q->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->latest()
            ->get()
            ->map(function($assessment) {
                return [
                    'id' => $assessment->id,
                    'period' => $assessment->period,
                    'evaluator_name' => $assessment->evaluator->name ?? 'Unknown',
                    'general_notes' => $assessment->general_notes,
                    'total_score' => $assessment->details->avg('score'),
                    'details' => $assessment->details->map(function($detail) {
                        return [
                            'category' => $detail->category->name ?? 'Unknown',
                            'score' => $detail->score
                        ];
                    }),
                    'created_at' => $assessment->created_at->format('d M Y')
                ];
            });

        $totalAvg = $scores->avg('average_score') ?? 0;
        $siswa = Siswa::with('user', 'kelas')->find($studentId);

        return response()->json([
            'student' => [
                'id' => $siswa->id,
                'name' => $siswa->user->name ?? $siswa->nama,
                'nis' => $siswa->nis,
                'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : null
            ],
            'total_score' => round($totalAvg, 1),
            'scores' => $scores,
            'history' => $history
        ]);
    }

    /**
     * API: Rekapitulasi Sederhana (Untuk Dashboard Siswa)
     */
    public function student(Request $request)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();
        
        if (!$siswa) {
            return response()->json([
                'error' => 'Data siswa tidak ditemukan'
            ], 404);
        }
        
        $tahunAjaranId = $this->getActiveYearId($request);

        $data = AssessmentDetail::join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            ->join('assessment_categories', 'assessment_categories.id', '=', 'assessment_details.category_id')
            ->where('assessments.siswa_id', $siswa->id)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('assessments.tahun_ajaran_id', $tahunAjaranId);
            })
            ->select('assessment_categories.name', DB::raw('CAST(AVG(assessment_details.score) AS FLOAT) as average_score'))
            ->groupBy('assessment_categories.name')
            ->get();

        return response()->json([
            'student_name' => $siswa->user->name ?? $siswa->nama,
            'data' => $data
        ]);
    }

    /**
     * API: Progress Penilaian Guru
     */
    public function teacherProgress(Request $request) {
        $guruId = Auth::id();
        $tahunAjaranId = $this->getActiveYearId($request);

        $guru = \App\Models\Guru::where('user_id', $guruId)->first();
        
        if (!$guru) {
            return response()->json([
                'error' => 'Data guru tidak ditemukan'
            ], 404);
        }

        $totalStudents = Siswa::whereHas('kelas', function($q) use ($guru) {
            $q->where('wali_kelas_id', $guru->id);
        })->count();
        
        $assessedCount = Assessment::where('evaluator_id', $guruId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->distinct('siswa_id')
            ->count('siswa_id');

        return response()->json([
            'teacher' => [
                'id' => $guru->id,
                'name' => $guru->nama,
                'nip' => $guru->nip
            ],
            'total' => $totalStudents,
            'assessed' => $assessedCount,
            'percentage' => $totalStudents > 0 ? round(($assessedCount / $totalStudents) * 100, 2) : 0
        ]);
    }
}
<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessmentQuestionController;
use App\Http\Controllers\AssessmentReportController;
use App\Http\Controllers\StoreAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- 1. ROUTE PUBLIC (TANPA LOGIN) ---
Route::post('/login', [AuthController::class, 'login']);
    Route::apiResource('store-items', StoreAdminController::class);
    Route::get('/siswa/store/points/{id}', [SiswaController::class, 'getPointsStore']);

// --- 2. ROUTE PROTECTED (HARUS LOGIN / PAKAI TOKEN) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // --- AUTH & USER ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-profile', [AuthController::class, 'profile']); // Optional jika ada

    // --- FITUR STORE & POIN (Siswa) ---
    // Dipindah ke sini supaya token Bearer dari Flutter bisa dibaca Laravel
  

    // --- SISWA & AKADEMIK ---
    Route::get('/siswa-by-kelas/{nama_kelas}', [SiswaController::class, 'getByKelas']);
    Route::get('academic/siswa-by-kelas/{id}', [SiswaController::class, 'getSiswaByKelasId']);
    Route::get('academic/master-data', [AcademicController::class, 'getMasterData'])->name('academic.master-data');

    // --- ABSENSI (Attendance) ---
    Route::post('/attendance/session', [AttendanceController::class, 'createSesi']);
    Route::post('/attendance/scan', [AttendanceController::class, 'scanQR']);
    Route::get('/attendance/history', [AttendanceController::class, 'historySiswa']);
    Route::post('/attendance/manual', [AttendanceController::class, 'storeManual']);

    // --- LAPORAN ---
    Route::get('/laporan/kehadiran', [LaporanController::class, 'cetakLaporan']);
    Route::get('/laporan/kehadiran/pdf', [LaporanController::class, 'cetakLaporan'])->name('laporan.kehadiran.pdf');

    // --- PENILAIAN (Scoring) ---
    Route::prefix('scoring')->group(function () {
        Route::get('/form-structure', [AssessmentController::class, 'getAssessmentForm']);
        Route::get('/questions/category/{id}', [AssessmentQuestionController::class, 'getByCategory']);
        Route::get('/students-to-assess', [AssessmentController::class, 'getStudentsToAssess']);
        Route::post('/submit', [AssessmentController::class, 'store']); 
        Route::get('/performance-radar', [AssessmentReportController::class, 'studentPerformance']);
        Route::get('/summary-student', [AssessmentReportController::class, 'student']);
        Route::get('/teacher-stats', [AssessmentReportController::class, 'teacherProgress']);
    });
});
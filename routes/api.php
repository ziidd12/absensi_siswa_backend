<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\AssessmentController; // Import Controller Penilaian Baru
use App\Http\Controllers\AssessmentQuestionController;
use App\Http\Controllers\AssessmentReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RatingController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // 1. Route Siswa & User
    // Route lama tetap ada (pakai nama kelas)
    Route::get('/siswa-by-kelas/{nama_kelas}', [SiswaController::class, 'getByKelas']);
    
    // --- TAMBAHKAN BARIS INI (Sangat Penting untuk Flutter kamu) ---
    Route::get('academic/siswa-by-kelas/{id}', [SiswaController::class, 'getSiswaByKelasId']);
    
    Route::apiResource('/user', UserController::class);
    Route::post('/logout', [AuthController::class, 'logout']);

    // 2. Route Absensi
    Route::post('/attendance/session', [AttendanceController::class, 'createSesi']);
    Route::post('/attendance/scan', [AttendanceController::class, 'scanQR']);
    Route::get('/attendance/history', [AttendanceController::class, 'historySiswa']);
    Route::post('/attendance/manual', [AttendanceController::class, 'storeManual']);

    // 4. Route Laporan & Akademik
    Route::get('/laporan/kehadiran', [LaporanController::class, 'cetakLaporan']);
    Route::get('/laporan/kehadiran/pdf', [LaporanController::class, 'cetakLaporan'])->name('laporan.kehadiran.pdf');
    Route::get('academic/master-data', [AcademicController::class, 'getMasterData'])->name('academic.master-data');

    Route::prefix('scoring')->group(function () {
        // Ambil Form & Kategori
        Route::get('/form-structure', [AssessmentController::class, 'getAssessmentForm']);
        Route::get('/questions/category/{id}', [AssessmentQuestionController::class, 'getByCategory']);
        
        // Transaksi Penilaian
        Route::get('/students-to-assess', [AssessmentController::class, 'getStudentsToAssess']);
        Route::post('/submit', [AssessmentController::class, 'store']); // Ganti dari /store ke /submit
        
        // Laporan untuk Mobile
        Route::get('/performance-radar', [AssessmentReportController::class, 'studentPerformance']);
        Route::get('/summary-student', [AssessmentReportController::class, 'student']);
        Route::get('/teacher-stats', [AssessmentReportController::class, 'teacherProgress']);
    });
});
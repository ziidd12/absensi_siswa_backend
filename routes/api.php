<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\AssessmentController; // Import Controller Penilaian Baru
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

    // 3. Route Penilaian (Assessment) - FITUR BARU
    Route::get('/assessment/categories', [AssessmentController::class, 'getCategories']);
    Route::post('/assessment/store', [AssessmentController::class, 'store']);
    Route::get('siswa', [SiswaController::class, 'index']);
    Route::post('/simpan-penilaian', [RatingController::class, 'simpanPenilaian']);
    // Route untuk Siswa mengambil nilai rating-nya
    Route::get('/penilaian-siswa/{siswa_id}', [RatingController::class, 'getRatingSiswa']);
    // routes/api.php

    // 4. Route Laporan & Akademik
    Route::get('/laporan/kehadiran', [LaporanController::class, 'cetakLaporan']);
    Route::get('/laporan/kehadiran/pdf', [LaporanController::class, 'cetakLaporan'])->name('laporan.kehadiran.pdf');
    Route::get('academic/master-data', [AcademicController::class, 'getMasterData'])->name('academic.master-data');

    Route::get('/student-performance', [AssessmentReportController::class, 'studentPerformance']);
    Route::get('/student', [AssessmentReportController::class, 'student']);
    Route::get('/teacher-progress', [AssessmentReportController::class, 'teacherProgress']);
    Route::get('/class/{kelasId}/statistics', [AssessmentReportController::class, 'classStatistics']);
    Route::get('/category/{categoryId}/report', [AssessmentReportController::class, 'categoryReport']);
});
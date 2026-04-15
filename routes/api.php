<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessmentQuestionController;
use App\Http\Controllers\AssessmentReportController;
use App\Http\Controllers\FlexibilityController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\PointLedgerController;
use App\Http\Controllers\StoreAdminController;
use App\Http\Controllers\UserTokenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Versi Gacor Anti Bocor
|--------------------------------------------------------------------------
*/

// --- 1. ROUTE PUBLIC (Bisa diakses tanpa login) ---
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('store-items', StoreAdminController::class);


// --- 2. ROUTE PROTECTED (Wajib bawa Token / Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // --- AUTH & USER ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-profile', [AuthController::class, 'profile']);

    // --- FITUR STORE & POIN (Siswa) ---
    // Jalur ini sudah otomatis deteksi User lewat Token, jadi GAK PERLU {id} lagi
    Route::get('/siswa/store/points', [SiswaController::class, 'getPointsStore']);
    Route::get('/poin/history', [SiswaController::class, 'getPoinHistory']);
    Route::post('/siswa/redeem', [SiswaController::class, 'redeemItem']);
    Route::get('/siswa/inventory', [SiswaController::class, 'getInventory']);

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

    // =============================================================
    // --- FITUR GAMIFIKASI (DOMPET INTEGRITAS) ---
    // =============================================================
    Route::prefix('gamifikasi')->group(function () {
        
        /**
         * HERO SECTION & TAB 2: MARKETPLACE
         * Return: Saldo Poin (points_store) & List Item Marketplace
         */
        Route::get('/marketplace', [FlexibilityController::class, 'index']);

        /**
         * TAB 1: RIWAYAT MUTASI
         * Return: List riwayat masuk/keluar poin (Ledger)
         */
        Route::get('/history', [PointLedgerController::class, 'userHistory']);

        /**
         * TAB 3: MY INVENTORY
         * Return: List token yang dimiliki (AVAILABLE / USED)
         */
        Route::get('/inventory', [UserTokenController::class, 'userInventory']);

        /**
         * ACTION: TUKAR POIN (REDEEM)
         * Params: item_id
         */
        Route::post('/redeem', [FlexibilityController::class, 'redeemToken']);
        
    });

    // --- JADWAL ---
    Route::get('/jadwal-hari-ini', [JadwalController::class, 'index']);
});
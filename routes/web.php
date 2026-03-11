<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\UserWebController;
use App\Http\Controllers\AssessmentCategoryController;
use App\Http\Controllers\AssessmentReportController; // Tambahkan ini
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Semua route di bawah ini memerlukan login dan verifikasi
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen User (Web)
    Route::get('/users-management', [UserWebController::class, 'index'])->name('admin.users.index');
    Route::post('/users-management', [UserWebController::class, 'store'])->name('admin.users.store');
    Route::put('/users-management/{id}', [UserWebController::class, 'update'])->name('admin.users.update');
    Route::delete('/users-management/{id}', [UserWebController::class, 'destroy'])->name('admin.users.destroy');
    Route::patch('/users-management/{id}/reset-device', [UserWebController::class, 'resetDevice'])->name('admin.users.reset_device');
    
    // Resource Routes untuk Data Master
    Route::resource('absensi', AbsensiController::class);
    Route::resource('guru', GuruController::class);
    Route::resource('siswa', SiswaController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('tahun-ajaran', TahunAjaranController::class);
    
    // Routes untuk Assessment Category (Penilaian Siswa)
    Route::prefix('penilaian-siswa')->name('penilaian-siswa.')->group(function () {
        Route::get('/', [AssessmentCategoryController::class, 'index'])->name('index');
        Route::get('/create', [AssessmentCategoryController::class, 'create'])->name('create');
        Route::post('/', [AssessmentCategoryController::class, 'store'])->name('store');
        Route::get('/{id}', [AssessmentCategoryController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AssessmentCategoryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AssessmentCategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [AssessmentCategoryController::class, 'destroy'])->name('destroy');
        Route::post('/delete-multiple', [AssessmentCategoryController::class, 'destroyMultiple'])->name('destroyMultiple');
    });

    // Routes untuk Laporan Penilaian (Web View)
    Route::prefix('laporan-penilaian')->name('laporan-penilaian.')->group(function () {
        // Halaman utama laporan (daftar siswa)
        Route::get('/', [AssessmentReportController::class, 'index'])->name('index');
        
        // Detail laporan per siswa
        Route::get('/siswa/{id}', [AssessmentReportController::class, 'show'])->name('show');
        
        // Export laporan
        Route::get('/export', [AssessmentReportController::class, 'export'])->name('export');
        
        // Filter berdasarkan kelas/tahun ajaran
        Route::get('/filter', [AssessmentReportController::class, 'filter'])->name('filter');
        
        // Statistik kelas
        Route::get('/kelas/{kelasId}', [AssessmentReportController::class, 'classStatistics'])->name('kelas');
        
        // Laporan per kategori
        Route::get('/kategori/{categoryId}', [AssessmentReportController::class, 'categoryReport'])->name('kategori');
    });

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// API Routes untuk Mobile (Flutter) - tidak perlu middleware auth web, tapi pakai sanctum
Route::prefix('api')->group(function () {
    Route::get('/reports/student-performance', [AssessmentReportController::class, 'studentPerformance']);
    Route::get('/reports/student', [AssessmentReportController::class, 'student']);
    Route::get('/reports/teacher-progress', [AssessmentReportController::class, 'teacherProgress']);
    // Tambahkan route API lainnya jika diperlukan
});

// Memuat rute autentikasi bawaan
require __DIR__.'/auth.php';
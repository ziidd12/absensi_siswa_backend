<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AnggotaKelasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\UserWebController;
use App\Http\Controllers\AssessmentCategoryController;
use App\Http\Controllers\AssessmentQuestionController;
use App\Http\Controllers\AssessmentReportController; // Tambahkan ini
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\MapelController;
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
    // Ganti baris dashboard nu tadi, jadi kieu:
    Route::get('/leaderboard-ranking', [DashboardController::class, 'index'])->name('leaderboardAdmin.index');
    // Resource Routes untuk Data Master
    Route::resource('absensi', AbsensiController::class);
    Route::resource('guru', GuruController::class);
    Route::resource('siswa', SiswaController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('tahun-ajaran', TahunAjaranController::class);
    
    // Routes untuk Assessment Category (Penilaian Siswa)
    Route::prefix('setup-penilaian')->name('setup-penilaian.')->group(function () {
        Route::resource('kategori', AssessmentCategoryController::class);
        Route::resource('pertanyaan', AssessmentQuestionController::class);
    });

    // --- MONITORING & LAPORAN (Web View) ---
    Route::prefix('monitoring-nilai')->name('monitoring-nilai.')->group(function () {
        Route::get('/', [AssessmentReportController::class, 'index'])->name('index');
        Route::get('/detail-siswa/{id}', [AssessmentReportController::class, 'show'])->name('show');
        Route::get('/statistik-kelas/{kelasId}', [AssessmentReportController::class, 'classStatistics'])->name('kelas');
        Route::get('/rekap-kategori/{categoryId}', [AssessmentReportController::class, 'categoryReport'])->name('kategori');
        Route::get('/export-excel', [AssessmentReportController::class, 'export'])->name('export');
    });

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('mapel', MapelController::class);
    Route::resource('anggota-kelas', AnggotaKelasController::class);
    Route::resource('jadwal', JadwalController::class);
});
require __DIR__.'/auth.php';
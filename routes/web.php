<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\UserWebController;
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
    
    // Dashboard Utama - Menggunakan ::class (titik dua ganda)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    
    // Manajemen User (Web)
    Route::get('/users-management', [UserWebController::class, 'index'])->name('admin.users.index');
    // Jika menggunakan resource, bisa disederhanakan, tapi ini versi manual sesuai kodemu:
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

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Memuat rute autentikasi bawaan (login, register, logout, dll)
require __DIR__.'/auth.php';
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // 2. Route Absensi
    Route::post('/attendance/session', [AttendanceController::class, 'createSesi']);
    Route::post('/attendance/scan', [AttendanceController::class, 'scanQR']);
    Route::get('/attendance/history', [AttendanceController::class, 'historySiswa']);

    Route::apiResource('/user', UserController::class);

    Route::get('/laporan/kehadiran/pdf', [LaporanController::class, 'cetakLaporan'])->name('laporan.kehadiran.pdf');
    
    Route::post('/logout', [AuthController::class, 'logout']);
});
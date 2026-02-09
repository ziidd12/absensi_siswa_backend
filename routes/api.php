<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // 2. Route Absensi
    Route::post('/attendance/session', [AttendanceController::class, 'createSesi']);
    Route::post('/attendance/scan', [AttendanceController::class, 'scanQR']);
    Route::get('/attendance/history', [AttendanceController::class, 'historySiswa']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
<?php

use App\Http\Controllers\{
    AbsensiController,
    AttendanceController,
    AnggotaKelasController,
    DashboardController,
    ProfileController,
    GuruController,
    KelasController,
    SiswaController,
    TahunAjaranController,
    UserWebController,
    AssessmentCategoryController,
    AssessmentQuestionController,
    AssessmentReportController,
    JadwalController,
    MapelController,
    // Tambahkan Controller Gamifikasi Baru
    PointRuleController,
    PointLedgerController,
    FlexibilityItemController,
    FlexibilityController,
    UserTokenController
};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- DASHBOARD & LEADERBOARD ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/leaderboard-ranking', [DashboardController::class, 'index'])->name('leaderboardAdmin.index');

    // --- MANAJEMEN USER & DEVICE ---
    Route::prefix('users-management')->name('admin.users.')->group(function () {
        Route::get('/', [UserWebController::class, 'index'])->name('index');
        Route::post('/', [UserWebController::class, 'store'])->name('store');
        Route::put('/{id}', [UserWebController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserWebController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/reset-device', [UserWebController::class, 'resetDevice'])->name('reset_device');
    });

    // --- GAMIFIKASI: RULE ENGINE & LEDGER (Admin) ---
    Route::prefix('gamifikasi')->name('gamifikasi.')->group(function () {
        // Master Aturan Poin
        Route::get('/rules', [PointRuleController::class, 'index'])->name('rules.index');
        Route::post('/rules', [PointRuleController::class, 'store'])->name('rules.store');
        Route::put('/rules/{id}', [PointRuleController::class, 'update'])->name('rules.update');
        Route::delete('/rules/{id}', [PointRuleController::class, 'destroy'])->name('rules.destroy');

        // Monitoring Mutasi Poin (Buku Besar)
        Route::get('/ledgers', [PointLedgerController::class, 'index'])->name('ledgers.index');

        // Monitoring Inventory Token Siswa
        Route::get('/tokens', [UserTokenController::class, 'index'])->name('tokens.index');
    });

    // --- MARKETPLACE: ITEM & REDEEM (Admin & Siswa) ---
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        // Kelola Item Marketplace (Admin)
        Route::get('/items', [FlexibilityItemController::class, 'index'])->name('items.index');
        Route::post('/items', [FlexibilityItemController::class, 'store'])->name('items.store');
        Route::put('/items/{id}', [FlexibilityItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{id}', [FlexibilityItemController::class, 'destroy'])->name('items.destroy');

        // Tampilan Marketplace & Proses Tukar Poin (Siswa Web View)
        Route::get('/browse', [FlexibilityController::class, 'index'])->name('index');
        Route::post('/redeem', [FlexibilityController::class, 'redeemToken'])->name('redeem');
    });

    // --- ABSENSI & ATTENDANCE LOGIC (Guru/Admin) ---
    Route::prefix('attendance')->name('attendance.')->group(function () {
        // Buat Sesi QR (Guru)
        Route::post('/create-session', [AttendanceController::class, 'createSesi'])->name('create-sesi');
        // Input Absen Manual (Guru - Ini yang memicu Rule Engine Status)
        Route::post('/manual-store', [AttendanceController::class, 'storeManual'])->name('store-manual');
        
        // Monitoring Laporan Attendance Baru
        Route::get('/report', [AttendanceController::class, 'getAttendanceReport'])->name('report');
    });

    // --- DATA MASTER (Resource Routes) ---
    Route::resource('absensi', AbsensiController::class);
    Route::resource('guru', GuruController::class);
    Route::resource('siswa', SiswaController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('tahun-ajaran', TahunAjaranController::class);
    Route::resource('mapel', MapelController::class);
    Route::resource('jadwal', JadwalController::class);
    Route::resource('anggota-kelas', AnggotaKelasController::class);
    
    // --- SISTEM PENILAIAN (Setup & Monitoring) ---
    Route::prefix('setup-penilaian')->name('setup-penilaian.')->group(function () {
        Route::resource('kategori', AssessmentCategoryController::class);
        Route::resource('pertanyaan', AssessmentQuestionController::class);
    });

    Route::prefix('monitoring-nilai')->name('monitoring-nilai.')->group(function () {
        Route::get('/', [AssessmentReportController::class, 'index'])->name('index');
        Route::get('/detail-siswa/{id}', [AssessmentReportController::class, 'show'])->name('show');
        Route::get('/statistik-kelas/{kelasId}', [AssessmentReportController::class, 'classStatistics'])->name('kelas');
        Route::get('/rekap-kategori/{categoryId}', [AssessmentReportController::class, 'categoryReport'])->name('kategori');
        Route::get('/export-excel', [AssessmentReportController::class, 'export'])->name('export');
    });

    // --- PROFILE SETTINGS ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
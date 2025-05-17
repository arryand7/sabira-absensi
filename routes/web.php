<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaporanKaryawanExport;
// use App\Exports\LaporanKaryawanExport;


// Halaman welcome
Route::get('/', function () {
    return view('welcome');
});

// Redirect setelah login berdasarkan role
Route::get('/redirect-after-login', function () {
    $role = Auth::user()->role;

    return match ($role) {
        'admin' => redirect('/dashboard-admin'),
        'guru' => redirect('/dashboard-guru'),
        'karyawan' => redirect('/dashboard-karyawan'),
        default => abort(403, 'Unauthorized'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Role: Admin
Route::middleware(['auth', 'checkRole:admin'])->group(function () {
    Route::get('/dashboard-admin', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // karyawan
    Route::resource('/karyawan', KaryawanController::class);
    // Route::get('/absensi', [\App\Http\Controllers\AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('/laporan-karyawan', [LaporanController::class, 'index'])->name('laporan.karyawan');

    // export
    Route::get('/laporan-karyawan/export', [LaporanController::class, 'export'])->name('laporan.karyawan.export');
    Route::get('/laporan/karyawan/{id}/export', [LaporanController::class, 'exportDetail'])->name('laporan.karyawan.detail.export');

    // Route::get('/laporan-karyawan/{id}', [LaporanController::class, 'show'])->name('laporan.karyawan.show');
    Route::get('/laporan/karyawan/{id}/detail', [LaporanController::class, 'detail'])
    ->name('laporan.karyawan.detail');


    // user
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::resource('users', UserController::class)->except(['show']);
});

// Role: Guru
Route::middleware(['auth', 'checkRole:guru'])->group(function () {
    Route::get('/dashboard-guru', function () {
        return view('guru.dashboard');
    })->name('guru.dashboard');
});

// Role: Karyawan
Route::middleware(['auth', 'checkRole:karyawan'])->group(function () {
    Route::get('/dashboard-karyawan', function () {
        return view('karyawan.dashboard');
    })->name('karyawan.dashboard');

    // Route untuk AbsensiController index
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi/checkin', [AbsensiController::class, 'checkin'])->name('absensi.checkin');
    Route::post('/absensi/checkout', [AbsensiController::class, 'checkout'])->name('absensi.checkout');

    Route::get('/history-absensi', [AbsensiController::class, 'history'])->name('karyawan.history');
});


// Profile (bawaan Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth (Breeze)
require __DIR__.'/auth.php';

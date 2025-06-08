<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeacherScheduleController;
use App\Http\Controllers\LaporanKaryawanExport;
use App\Http\Controllers\AdminScheduleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\AdminLokasiAbsenController;
use App\Http\Controllers\SubjectController;


// Halaman welcome
Route::get('/', function () {
    return view('welcome');
});

// Redirect setelah login berdasarkan role
Route::get('/redirect-after-login', function () {
    $role = Auth::user()->role;

    return match ($role) {
        'admin' => redirect('/dashboard-admin'),
        'guru' => redirect('/dashboard-karyawan'),
        'karyawan' => redirect('/dashboard-karyawan'),
        default => abort(403, 'Unauthorized'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Role: Admin
Route::middleware(['auth', 'checkRole:admin'])->group(function () {
    Route::get('/dashboard-admin', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // karyawan
    Route::resource('/karyawan', KaryawanController::class);
    Route::get('/laporan-karyawan', [LaporanController::class, 'index'])->name('laporan.karyawan');

    // export
    Route::get('/laporan-karyawan/export', [LaporanController::class, 'export'])->name('laporan.karyawan.export');
    Route::get('/laporan/karyawan/{id}/export', [LaporanController::class, 'exportDetail'])->name('laporan.karyawan.detail.export');
    Route::get('/laporan/karyawan/{id}/detail', [LaporanController::class, 'detail'])
    ->name('laporan.karyawan.detail');


    // user
    // Route::get('/users', [UserController::class, 'index'])->name('users.index');
    // Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    // Route::post('/users', [UserController::class, 'store'])->name('users.store');
    // Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    // Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    // Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::resource('users', UserController::class)->except(['show']);

    //schedule
    Route::prefix('admin/schedules')->name('admin.schedules.')->group(function () {
        Route::get('/', [AdminScheduleController::class, 'index'])->name('index'); // Daftar semua jadwal
        Route::get('/create', [AdminScheduleController::class, 'create'])->name('create'); // Form tambah jadwal
        Route::post('/', [AdminScheduleController::class, 'store'])->name('store'); // Simpan jadwal baru

        Route::get('/{schedule}/edit', [AdminScheduleController::class, 'edit'])->name('edit'); // Form edit
        Route::put('/{schedule}', [AdminScheduleController::class, 'update'])->name('update'); // Update data
        Route::delete('/{schedule}', [AdminScheduleController::class, 'destroy'])->name('destroy'); // Hapus
        Route::get('/guru/{id}', [AdminScheduleController::class, 'showByTeacher'])->name('show-by-teacher');
    });

    Route::get('/admin/students', [StudentController::class, 'index'])->name('admin.students.index');
    Route::post('/admin/students/import', [StudentController::class, 'import'])->name('admin.students.import');
    Route::get('/admin/students/{id}/edit', [StudentController::class, 'edit'])->name('admin.students.edit');
    Route::put('/admin/students/{id}', [StudentController::class, 'update'])->name('admin.students.update');
    Route::delete('/admin/students/{id}', [StudentController::class, 'destroy'])->name('admin.students.destroy');
    Route::get('/admin/students/create', [StudentController::class, 'create'])->name('admin.students.create');
    Route::post('/admin/students', [StudentController::class, 'store'])->name('admin.students.store');


    Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
        Route::resource('class-groups', \App\Http\Controllers\ClassGroupController::class);
        Route::post('/students/bulk-delete', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
    });

    Route::get('/lokasi-absen/edit', [AdminLokasiAbsenController::class, 'edit'])->name('admin.lokasi.edit');
    Route::put('/lokasi-absen', [AdminLokasiAbsenController::class, 'update'])->name('admin.lokasi.update');

    //subject
    Route::resource('subjects', SubjectController::class);


});

// Role: Guru
Route::middleware(['auth', 'checkRole:guru'])->group(function () {
    Route::get('/jadwal-guru', [TeacherScheduleController::class, 'index'])->name('guru.schedule');
    Route::get('/jadwal-guru/{id}/absen', [TeacherScheduleController::class, 'absen'])->name('guru.schedule.absen');

    Route::post('/jadwal-guru/absen/{classGroup}', [TeacherScheduleController::class, 'submitAbsen'])
    ->name('guru.schedule.absen.submit');
});

// Role: Karyawan
Route::middleware(['auth', 'checkRole:karyawan,guru'])->group(function () {
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

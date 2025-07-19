<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanMuridController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeacherScheduleController;
use App\Http\Controllers\LaporanKaryawanExport;
use App\Http\Controllers\AdminScheduleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\AdminLokasiAbsenController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherHistoryController;
use App\Http\Controllers\AsramaAbsenController;
use App\Http\Controllers\StudentPromotionController;
use App\Http\Controllers\AcademicYearController;


// Halaman welcome
Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/redirect-after-login', function () {
    $user = Auth::user();

    if ($user->role === 'admin') {
        return redirect('/dashboard-admin');
    }

    if ($user->role === 'guru') {
    return redirect('/dashboard-karyawan');
}

    if ($user->role === 'organisasi') {
        return redirect()->route('asrama.index');
    }

    if ($user->role === 'karyawan') {
        return redirect('/dashboard-karyawan');
    }

    abort(403, 'Unauthorized');
})->middleware(['auth', 'verified'])->name('dashboard');


// Role: Admin
Route::middleware(['auth', 'checkRole:admin'])->group(function () {
    Route::get('/dashboard-admin', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // karyawan
    Route::resource('/karyawan', KaryawanController::class);

    Route::get('/laporan-karyawan', [LaporanController::class, 'index'])->name('laporan.karyawan');
    Route::get('/laporan/karyawan/{id}/detail', [LaporanController::class, 'detail'])
    ->name('laporan.karyawan.detail');
    // export
    Route::get('/laporan-karyawan/export', [LaporanController::class, 'export'])->name('laporan.karyawan.export');
    Route::get('/laporan/karyawan/{id}/export', [LaporanController::class, 'exportDetail'])->name('laporan.karyawan.detail.export');

    Route::get('/admin/laporan/murid', [LaporanMuridController::class, 'dashboard'])->name('laporan.murid.dashboard');
    Route::get('/laporan-murid', [LaporanMuridController::class, 'index'])->name('laporan.murid');
    Route::get('/admin/muid/{student}/download', [LaporanMuridController::class, 'download'])->name('laporan.murid.download');
    Route::get('/admin/laporan/murid/mapel', [LaporanMuridController::class, 'laporanMapel'])->name('laporan.murid.mapel');
    Route::get('/admin/laporan/murid/mapel/download', [LaporanMuridController::class, 'downloadMapel'])->name('laporan.murid.mapel.download');
    Route::get('/laporan/murid/mapel/excel', [LaporanMuridController::class, 'exportExcel'])->name('laporan.murid.mapel.excel');

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
        Route::post('/import', [AdminScheduleController::class, 'import'])->name('import');

    });

    Route::get('/admin/students', [StudentController::class, 'index'])->name('admin.students.index');
    Route::post('/admin/students/import', [StudentController::class, 'import'])->name('admin.students.import');
    Route::get('/admin/students/{id}/edit', [StudentController::class, 'edit'])->name('admin.students.edit');
    Route::put('/admin/students/{id}', [StudentController::class, 'update'])->name('admin.students.update');
    Route::delete('/admin/students/{id}', [StudentController::class, 'destroy'])->name('admin.students.destroy');
    Route::get('/admin/students/create', [StudentController::class, 'create'])->name('admin.students.create');
    Route::post('/admin/students', [StudentController::class, 'store'])->name('admin.students.store');
    Route::post('/admin/students/bulk-delete', [StudentController::class, 'bulkDelete'])->name('admin.students.bulk-delete');

    Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
        Route::resource('class-groups', \App\Http\Controllers\ClassGroupController::class)->except(['show']);
    });

    Route::get('admin/class-groups/duplicate', [ClassGroupController::class, 'duplicateForm'])->name('admin.class-groups.duplicate-form');
    Route::post('admin/class-groups/duplicate', [ClassGroupController::class, 'duplicate'])->name('admin.class-groups.duplicate');

    Route::get('/lokasi-absen/edit', [AdminLokasiAbsenController::class, 'edit'])->name('admin.lokasi.edit');
    Route::put('/lokasi-absen', [AdminLokasiAbsenController::class, 'update'])->name('admin.lokasi.update');

    //subject
    Route::resource('subjects', SubjectController::class);

    // CED only: Create, Edit, Delete
    Route::get('/academic-years', [\App\Http\Controllers\AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::get('/academic-years/create', [\App\Http\Controllers\AcademicYearController::class, 'create'])->name('academic-years.create');
    Route::post('/academic-years', [\App\Http\Controllers\AcademicYearController::class, 'store'])->name('academic-years.store');
    Route::get('/academic-years/{academicYear}/edit', [\App\Http\Controllers\AcademicYearController::class, 'edit'])->name('academic-years.edit');
    Route::put('/academic-years/{academicYear}', [\App\Http\Controllers\AcademicYearController::class, 'update'])->name('academic-years.update');
    Route::delete('/academic-years/{academicYear}', [\App\Http\Controllers\AcademicYearController::class, 'destroy'])->name('academic-years.destroy');


    Route::get('/promote', [StudentPromotionController::class, 'index'])->name('promotion.index');
    Route::post('/promote', [StudentPromotionController::class, 'promote'])->name('promotion.promote');
    Route::get('/promotion', [StudentPromotionController::class, 'index'])->name('promotion.index');
    Route::post('/promotion/add', [StudentPromotionController::class, 'add'])->name('promotion.add');
    Route::post('/promotion/remove', [StudentPromotionController::class, 'remove'])->name('promotion.remove');



    Route::resource('/divisis', \App\Http\Controllers\DivisiController::class);

    Route::get('/admin/sholat', [AsramaAbsenController::class, 'masterSholat'])->name('admin.sholat');
    Route::post('/admin/sholat', [AsramaAbsenController::class, 'storeSholat'])->name('admin.sholat.store');
    Route::delete('/admin/sholat/{id}', [AsramaAbsenController::class, 'deleteSholat'])->name('admin.sholat.delete');
});

// Route untuk GURU
    Route::middleware(['auth', 'checkRole:guru'])->group(function () {
        Route::get('/jadwal-guru', [TeacherScheduleController::class, 'index'])->name('guru.schedule');
        Route::get('/jadwal-guru/{schedule}/absen', [TeacherScheduleController::class, 'absen'])->name('guru.schedule.absen');
        
        // Route::get('/', [TeacherScheduleController::class, 'index'])->name('guru.schedule.index');
        Route::get('/create', [TeacherScheduleController::class, 'create'])->name('guru.schedule.create'); // Form tambah jadwal
        Route::post('/', [TeacherScheduleController::class, 'store'])->name('guru.schedule.store'); // Simpan jadwal baru

        Route::post('/jadwal-guru/absen/{classGroup}', [TeacherScheduleController::class, 'submitAbsen'])
            ->name('guru.schedule.absen.submit');

        Route::get('/history', [TeacherHistoryController::class, 'index'])->name('guru.history.index');
        Route::get('/history/{schedule}/{pertemuan}', [TeacherHistoryController::class, 'detail'])->name('guru.history.detail');
        Route::get('history/{scheduleId}/{pertemuan}/edit', [TeacherHistoryController::class, 'edit'])->name('guru.history.edit');
        Route::post('history/{scheduleId}/{pertemuan}/update', [TeacherHistoryController::class, 'update'])->name('guru.history.update');
    });

    // Route untuk ORGANISASI (asrama)
    Route::middleware(['auth', 'checkRole:organisasi'])->prefix('asrama')->group(function () {
        Route::get('/', [AsramaAbsenController::class, 'index'])->name('asrama.index');

        // Sholat
        Route::get('/sholat', [AsramaAbsenController::class, 'pilihSholat'])->name('asrama.sholat');
        Route::get('/sholat/form/{jenis}', [AsramaAbsenController::class, 'formAbsenSholat'])->name('asrama.sholat.form');
        Route::post('/sholat/{jenis}', [AsramaAbsenController::class, 'submitAbsenSholat'])->name('asrama.sholat.submit');
        Route::get('/sholat/search/{jenis}', [AsramaAbsenController::class, 'searchStudent'])->name('asrama.sholat.search');

        // Kegiatan
        Route::get('/kegiatan', [AsramaAbsenController::class, 'listKegiatan'])->name('asrama.kegiatan');
        Route::post('/kegiatan', [AsramaAbsenController::class, 'createKegiatan'])->name('asrama.kegiatan.create');
        Route::get('/kegiatan/{id}/absen', [AsramaAbsenController::class, 'formAbsenKegiatan'])->name('asrama.kegiatan.absen');
        Route::post('/kegiatan/{id}/absen', [AsramaAbsenController::class, 'submitAbsenKegiatan'])->name('asrama.kegiatan.absen.submit');
        Route::get('/kegiatan/{id}/search', [AsramaAbsenController::class, 'searchStudentKegiatan'])->name('asrama.kegiatan.search');

        Route::get('/sholat/history', [AsramaAbsenController::class, 'historySholat'])->name('asrama.sholat.history');
        Route::get('/kegiatan/{id}/history', [AsramaAbsenController::class, 'historyKegiatan'])->name('asrama.kegiatan.history');
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

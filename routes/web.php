<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\Admin\GateUserSyncController;
use App\Http\Controllers\Admin\ScheduleConflictController;
use App\Http\Controllers\Admin\ScheduleTimeSlotController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminLokasiAbsenController;
use App\Http\Controllers\AdminScheduleController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\AsramaAbsenController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\EducationProgramController;
use App\Http\Controllers\GuruDashboardController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanMuridController;
use App\Http\Controllers\LaporanPertemuanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SsoSettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPromotionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherHistoryController;
use App\Http\Controllers\TeacherScheduleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Halaman welcome
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/redirect-after-login', function () {
    $user = Auth::user();

    if (in_array($user->role, ['super_admin', 'admin'], true)) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'guru') {
        return redirect()->route('guru.dashboard');
    }

    if ($user->role === 'organisasi') {
        return redirect()->route('asrama.index');
    }

    if ($user->role === 'karyawan') {
        return redirect()->route('karyawan.dashboard');
    }

    if (in_array($user->role, ['siswa', 'wali'], true)) {
        return redirect()->route('profile.edit');
    }

    abort(403, 'Unauthorized');
})->middleware(['auth', 'verified'])->name('dashboard');

// Role: Admin
Route::middleware(['auth', 'checkRole:admin,super_admin'])->group(function () {
    Route::get('/dashboard-admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/settings/sso', [SsoSettingController::class, 'edit'])->name('admin.settings.sso');
    Route::put('/admin/settings/sso', [SsoSettingController::class, 'update'])->name('admin.settings.sso.update');
    Route::get('/admin/settings/app', [AppSettingController::class, 'edit'])->name('admin.settings.app');
    Route::put('/admin/settings/app', [AppSettingController::class, 'update'])->name('admin.settings.app.update');

    // Program Pendidikan (Formal / Muadalah)
    Route::resource('/admin/education-programs', EducationProgramController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('admin.education-programs');

    Route::get('/admin/schedule-time-slots', [ScheduleTimeSlotController::class, 'index'])
        ->name('admin.schedule-time-slots.index');
    Route::post('/admin/schedule-time-slots', [ScheduleTimeSlotController::class, 'store'])
        ->name('admin.schedule-time-slots.store');
    Route::put('/admin/schedule-time-slots/{scheduleTimeSlot}', [ScheduleTimeSlotController::class, 'update'])
        ->name('admin.schedule-time-slots.update');
    Route::delete('/admin/schedule-time-slots/{scheduleTimeSlot}', [ScheduleTimeSlotController::class, 'destroy'])
        ->name('admin.schedule-time-slots.destroy');

    // Gate User Synchronization — privileged identity operation, superadmin only.
    Route::middleware('checkRole:super_admin')->group(function () {
        Route::get('/admin/sync', [GateUserSyncController::class, 'index'])->name('admin.sync.index');
        Route::post('/admin/sync/preview', [GateUserSyncController::class, 'preview'])->name('admin.sync.preview');
        Route::get('/admin/sync/{run}', [GateUserSyncController::class, 'show'])->name('admin.sync.show');
        Route::put('/admin/sync/{run}/actions', [GateUserSyncController::class, 'updateActions'])->name('admin.sync.actions.update');
        Route::post('/admin/sync/{run}/apply', [GateUserSyncController::class, 'apply'])->name('admin.sync.apply');
        Route::post('/admin/sync/{run}/retry-report', [GateUserSyncController::class, 'retryReport'])->name('admin.sync.retry-report');
    });

    Route::post('/dashboard/absen/manual', [AdminDashboardController::class, 'storeManualAbsen'])->name('admin.absensi.manual.store');
    Route::get('/dashboard/absen/{id}/edit', [AdminDashboardController::class, 'editAbsen'])->name('admin.absensi.edit');
    Route::put('/dashboard/absen/{id}', [AdminDashboardController::class, 'updateAbsen'])->name('admin.absensi.update');

    Route::get('/admin/attendance-corrections', [AttendanceCorrectionController::class, 'index'])
        ->name('admin.attendance-corrections.index');
    Route::get('/admin/attendance-corrections/{correction}', [AttendanceCorrectionController::class, 'show'])
        ->name('admin.attendance-corrections.show');
    Route::post('/admin/attendance-corrections/{correction}/review', [AttendanceCorrectionController::class, 'review'])
        ->name('admin.attendance-corrections.review');

    Route::get('/admin/schedule-conflicts', [ScheduleConflictController::class, 'index'])
        ->name('admin.schedule-conflicts.index');
    Route::get('/admin/schedule-conflicts/{scheduleConflict}', [ScheduleConflictController::class, 'show'])
        ->name('admin.schedule-conflicts.show');
    Route::post('/admin/schedule-conflicts/{scheduleConflict}/resolve', [ScheduleConflictController::class, 'resolve'])
        ->name('admin.schedule-conflicts.resolve');

    // karyawan
    Route::resource('/karyawan', KaryawanController::class);

    Route::get('/laporan-karyawan', [LaporanController::class, 'index'])->name('laporan.karyawan');
    Route::get('/laporan/karyawan/{id}/detail', [LaporanController::class, 'detail'])
        ->name('laporan.karyawan.detail');
    // export
    Route::get('/laporan-karyawan/export', [LaporanController::class, 'export'])->name('laporan.karyawan.export');
    Route::get('/laporan-karyawan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.karyawan.export.pdf');
    Route::get('/laporan/karyawan/{id}/export', [LaporanController::class, 'exportDetail'])->name('laporan.karyawan.detail.export');

    Route::get('/laporan-murid', [LaporanMuridController::class, 'index'])->name('laporan.murid');
    Route::get('/admin/laporan/pertemuan', [LaporanPertemuanController::class, 'index'])->name('laporan.pertemuan');
    Route::get('/admin/laporan/pertemuan/guru/{teacher}', [LaporanPertemuanController::class, 'teacherDetail'])->name('laporan.pertemuan.teacher');
    Route::get('/admin/laporan/pertemuan/export/pdf', [LaporanPertemuanController::class, 'exportPdf'])->name('laporan.pertemuan.export.pdf');
    Route::get('/admin/laporan/pertemuan/export/excel', [LaporanPertemuanController::class, 'exportExcel'])->name('laporan.pertemuan.export.excel');
    Route::get('/admin/laporan/murid/kelas/export/pdf', [LaporanMuridController::class, 'exportKelasPdf'])->name('laporan.murid.kelas.export.pdf');
    Route::get('/admin/laporan/murid/kelas/export/excel', [LaporanMuridController::class, 'exportKelasExcel'])->name('laporan.murid.kelas.export.excel');
    Route::get('/admin/laporan/murid/mapel', [LaporanMuridController::class, 'laporanMapel'])->name('laporan.murid.mapel');
    Route::get('/admin/laporan/murid/mapel/download', [LaporanMuridController::class, 'downloadMapel'])->name('laporan.murid.mapel.download');
    Route::get('/laporan/murid/mapel/excel', [LaporanMuridController::class, 'exportExcel'])->name('laporan.murid.mapel.excel');
    Route::get('/admin/laporan/murid/{student}/download/pdf', [LaporanMuridController::class, 'download'])->name('laporan.murid.download');
    Route::get('/admin/laporan/murid/{student}/download/excel', [LaporanMuridController::class, 'exportStudentExcel'])->name('laporan.murid.download.excel');
    Route::get('/admin/laporan/murid/{student}', [LaporanMuridController::class, 'show'])->name('laporan.murid.show');

    // user
    Route::resource('users', UserController::class)->except(['show']);

    // schedule
    Route::prefix('admin/schedules')->name('admin.schedules.')->group(function () {
        Route::get('/', [AdminScheduleController::class, 'index'])->name('index'); // Daftar semua jadwal
        Route::get('/create', [AdminScheduleController::class, 'create'])->name('create'); // Form tambah jadwal
        Route::post('/', [AdminScheduleController::class, 'store'])->name('store'); // Simpan jadwal baru

        Route::get('/{schedule}/edit', [AdminScheduleController::class, 'edit'])->name('edit'); // Form edit
        Route::put('/{schedule}', [AdminScheduleController::class, 'update'])->name('update'); // Update data
        Route::delete('/{schedule}', [AdminScheduleController::class, 'destroy'])->name('destroy'); // Hapus
        Route::post('/{schedule}/substitute', [AdminScheduleController::class, 'assignSubstitute'])->name('assign-substitute');
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
        Route::resource('class-groups', ClassGroupController::class)->except(['show']);
    });

    Route::get('admin/class-groups/duplicate', [ClassGroupController::class, 'duplicateForm'])->name('admin.class-groups.duplicate-form');
    Route::post('admin/class-groups/duplicate', [ClassGroupController::class, 'duplicate'])->name('admin.class-groups.duplicate');

    Route::get('/lokasi-absen/edit', [AdminLokasiAbsenController::class, 'edit'])->name('admin.lokasi.edit');
    Route::put('/lokasi-absen', [AdminLokasiAbsenController::class, 'update'])->name('admin.lokasi.update');

    // subject
    Route::resource('subjects', SubjectController::class)->except(['show']);

    // CED only: Create, Edit, Delete
    Route::get('/academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::get('/academic-years/create', [AcademicYearController::class, 'create'])->name('academic-years.create');
    Route::post('/academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
    Route::get('/academic-years/{academicYear}/edit', [AcademicYearController::class, 'edit'])->name('academic-years.edit');
    Route::put('/academic-years/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
    Route::delete('/academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');

    Route::get('/promote', fn () => redirect()->route('promotion.index'));
    Route::post('/promote', [StudentPromotionController::class, 'promote'])->name('promotion.promote');
    Route::post('/promotion/preview', [StudentPromotionController::class, 'preview'])->name('promotion.preview');
    Route::get('/promotion', [StudentPromotionController::class, 'index'])->name('promotion.index');

    Route::resource('/divisis', DivisiController::class)->except(['show']);

    Route::get('/admin/sholat', [AsramaAbsenController::class, 'masterSholat'])->name('admin.sholat');
    Route::post('/admin/sholat', [AsramaAbsenController::class, 'storeSholat'])->name('admin.sholat.store');
    Route::delete('/admin/sholat/{id}', [AsramaAbsenController::class, 'deleteSholat'])->name('admin.sholat.delete');
});

// Route untuk GURU
Route::middleware(['auth', 'checkRole:guru'])->group(function () {
    Route::get('/dashboard-guru', [GuruDashboardController::class, 'index'])->name('guru.dashboard');
    Route::get('/jadwal-guru', [TeacherScheduleController::class, 'index'])->name('guru.schedule');
    Route::get('/jadwal-guru/create', [TeacherScheduleController::class, 'create'])->name('guru.schedule.create'); // Form tambah jadwal
    Route::post('/jadwal-guru', [TeacherScheduleController::class, 'store'])->name('guru.schedule.store'); // Simpan jadwal baru
    Route::get('/jadwal-guru/guru/{id}', [TeacherScheduleController::class, 'showByTeacher'])->name('guru.schedule.show-by-teacher');
    Route::get('/jadwal-guru/{schedule}/edit', [TeacherScheduleController::class, 'edit'])->name('guru.schedule.edit'); // Form edit
    Route::put('/jadwal-guru/{schedule}', [TeacherScheduleController::class, 'update'])->name('guru.schedule.update'); // Update data
    Route::delete('/jadwal-guru/{schedule}', [TeacherScheduleController::class, 'destroy'])->name('guru.schedule.destroy'); // Hapus
    Route::get('/jadwal-guru/{schedule}/absen', [TeacherScheduleController::class, 'absen'])->name('guru.schedule.absen');
    Route::post('/jadwal-guru/{schedule}/draft', [TeacherScheduleController::class, 'saveDraft'])->name('guru.schedule.draft');
    Route::post('/jadwal-guru/absen/{classGroup}', [TeacherScheduleController::class, 'submitAbsen'])
        ->name('guru.schedule.absen.submit');

    Route::get('/history', [TeacherHistoryController::class, 'index'])->name('guru.history.index');
    Route::get('/history/session/{session}', [TeacherHistoryController::class, 'detail'])->name('guru.history.detail');
    Route::post('/history/session/{session}/correction', [TeacherHistoryController::class, 'requestCorrection'])
        ->name('guru.history.correction.store');
});

// Route untuk ORGANISASI (asrama)
Route::middleware(['auth', 'checkRole:organisasi'])->prefix('asrama')->group(function () {
    Route::get('/', [AsramaAbsenController::class, 'index'])->name('asrama.index');

    // Sholat
    Route::get('/sholat', [AsramaAbsenController::class, 'pilihSholat'])->name('asrama.sholat');
    Route::get('/sholat/form/{jenis}', [AsramaAbsenController::class, 'formAbsenSholat'])->name('asrama.sholat.form');
    Route::get('/sholat/search/{jenis}', [AsramaAbsenController::class, 'searchStudent'])->name('asrama.sholat.search');
    Route::post('/sholat/absen/update', [AsramaAbsenController::class, 'updateAbsenStatus'])
        ->name('asrama.sholat.absen.update');

    // Kegiatan
    Route::get('/kegiatan', [AsramaAbsenController::class, 'listKegiatan'])->name('asrama.kegiatan');
    Route::post('/kegiatan', [AsramaAbsenController::class, 'createKegiatan'])->name('asrama.kegiatan.create');
    Route::get('/kegiatan/{id}/absen', [AsramaAbsenController::class, 'formAbsenKegiatan'])->name('asrama.kegiatan.absen');
    Route::get('/kegiatan/{id}/search', [AsramaAbsenController::class, 'searchStudentKegiatan'])->name('asrama.kegiatan.search');
    Route::post('/kegiatan/{id}/absen/update', [AsramaAbsenController::class, 'updateAbsenStatusKegiatan'])
        ->name('asrama.kegiatan.absen.update');

    Route::get('/sholat/history', [AsramaAbsenController::class, 'historySholat'])->name('asrama.sholat.history');
    Route::get('/kegiatan/{id}/history', [AsramaAbsenController::class, 'historyKegiatan'])->name('asrama.kegiatan.history');

    Route::delete('/asrama/kegiatan/{id}', [AsramaAbsenController::class, 'deleteKegiatan'])->name('asrama.kegiatan.delete');
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

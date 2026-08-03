<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ScheduleSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $guru = $user->guru ?? null;
        $guruUserId = $user->id;

        $today = Carbon::today();
        $todayHariName = Carbon::now()->locale('id')->isoFormat('dddd');

        // Sesi mengajar hari ini (baik sebagai guru utama maupun guru pengganti)
        $todaySessions = ScheduleSession::with([
            'classGroup.educationProgram',
            'subject',
            'scheduledTeacher',
            'actualTeacher',
        ])
            ->whereDate('date', $today)
            ->where(function ($query) use ($guruUserId) {
                $query->where('scheduled_teacher_id', $guruUserId)
                    ->orWhere('actual_teacher_id', $guruUserId)
                    ->orWhereHas('schedule', function ($q) use ($guruUserId) {
                        $q->where('user_id', $guruUserId);
                    });
            })
            ->orderBy('start_time')
            ->get();

        // Jadwal rutin mengajar hari ini dari tabel schedules
        $rutinSchedules = Schedule::with(['classGroup.educationProgram', 'subject'])
            ->where('user_id', $guruUserId)
            ->where('hari', $todayHariName)
            ->get();

        $completedSessionsCount = $todaySessions->where('status', 'completed')->count();
        $pendingSessionsCount = $todaySessions->where('status', '!=', 'completed')->count();
        $completedThisMonth = ScheduleSession::query()
            ->where('actual_teacher_id', $guruUserId)
            ->where('status', 'completed')
            ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->get();
        $geofenceChecked = $completedThisMonth->whereIn('location_validation_status', [
            'inside_geofence',
            'outside_geofence',
            'low_accuracy',
        ]);
        $geofenceComplianceRate = $geofenceChecked->isEmpty()
            ? null
            : round(($geofenceChecked->where('location_validation_status', 'inside_geofence')->count() / $geofenceChecked->count()) * 100, 1);

        return view('guru.dashboard', compact(
            'user',
            'guru',
            'todaySessions',
            'rutinSchedules',
            'completedSessionsCount',
            'pendingSessionsCount',
            'completedThisMonth',
            'geofenceComplianceRate'
        ));
    }
}

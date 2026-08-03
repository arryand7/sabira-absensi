<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewAttendanceCorrectionRequest;
use App\Models\AttendanceCorrection;
use App\Services\ReviewAttendanceCorrectionService;
use Illuminate\Http\Request;

class AttendanceCorrectionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $status = in_array($status, ['pending', 'approved', 'rejected'], true) ? $status : '';

        $corrections = AttendanceCorrection::with([
            'session.schedule.subject',
            'session.schedule.classGroup',
            'requester',
            'reviewer',
        ])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = AttendanceCorrection::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.attendance-corrections.index', compact('corrections', 'counts', 'status'));
    }

    public function show(AttendanceCorrection $correction)
    {
        $correction->load([
            'session.schedule.subject',
            'session.schedule.classGroup',
            'session.attendances.student',
            'requester',
            'reviewer',
        ]);

        return view('admin.attendance-corrections.show', compact('correction'));
    }

    public function review(
        ReviewAttendanceCorrectionRequest $request,
        AttendanceCorrection $correction,
        ReviewAttendanceCorrectionService $service
    ) {
        $service->execute(
            $correction,
            $request->user(),
            $request->validated('decision'),
            $request->validated('review_notes')
        );

        return redirect()->route('admin.attendance-corrections.show', $correction)
            ->with('success', 'Permintaan koreksi berhasil ditinjau.');
    }
}

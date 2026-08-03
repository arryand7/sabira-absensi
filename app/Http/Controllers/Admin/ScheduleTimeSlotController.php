<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleTimeSlotRequest;
use App\Http\Requests\UpdateScheduleTimeSlotRequest;
use App\Models\EducationProgram;
use App\Models\ScheduleTimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ScheduleTimeSlotController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ScheduleTimeSlot::class);

        $programs = EducationProgram::query()
            ->with('timeSlots')
            ->orderBy('name')
            ->get();
        $selectedProgram = $programs->firstWhere('id', $request->integer('program_id')) ?? $programs->first();

        return view('admin.schedule-time-slots.index', compact('programs', 'selectedProgram'));
    }

    public function store(StoreScheduleTimeSlotRequest $request)
    {
        Gate::authorize('create', ScheduleTimeSlot::class);
        ScheduleTimeSlot::create($this->payload($request->validated(), $request));

        return redirect()->route('admin.schedule-time-slots.index', ['program_id' => $request->integer('education_program_id')])
            ->with('success', 'Kebijakan jam pelajaran berhasil ditambahkan.');
    }

    public function update(UpdateScheduleTimeSlotRequest $request, ScheduleTimeSlot $scheduleTimeSlot)
    {
        Gate::authorize('update', $scheduleTimeSlot);
        $scheduleTimeSlot->update($this->payload($request->validated(), $request));

        return redirect()->route('admin.schedule-time-slots.index', ['program_id' => $scheduleTimeSlot->education_program_id])
            ->with('success', 'Kebijakan jam pelajaran berhasil diperbarui.');
    }

    public function destroy(ScheduleTimeSlot $scheduleTimeSlot)
    {
        Gate::authorize('delete', $scheduleTimeSlot);
        $programId = $scheduleTimeSlot->education_program_id;
        $scheduleTimeSlot->delete();

        return redirect()->route('admin.schedule-time-slots.index', ['program_id' => $programId])
            ->with('success', 'Slot jam dihapus. Jadwal yang sudah ada tidak ikut dihapus.');
    }

    private function payload(array $validated, Request $request): array
    {
        $isBreak = $request->boolean('is_break');

        return array_merge($validated, [
            'slot_number' => $isBreak ? null : ($validated['slot_number'] ?? null),
            'label' => ($validated['label'] ?? null) ?: ($isBreak ? 'Istirahat' : null),
            'is_break' => $isBreak,
            'friday_enabled' => $request->boolean('friday_enabled'),
            'is_active' => $request->boolean('is_active'),
        ]);
    }
}

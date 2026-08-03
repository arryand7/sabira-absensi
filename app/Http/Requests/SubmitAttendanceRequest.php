<?php

namespace App\Http\Requests;

use App\Models\ClassGroupStudent;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAttendanceRequest extends FormRequest
{
    protected ?Schedule $resolvedSchedule = null;

    public function authorize(): bool
    {
        $scheduleId = $this->input('schedule_id');
        if (! $scheduleId) {
            return false;
        }

        $this->resolvedSchedule = Schedule::with(['classGroup', 'subject'])->find($scheduleId);

        if (! $this->resolvedSchedule) {
            return false;
        }

        return $this->user()->can('submitAttendance', $this->resolvedSchedule);
    }

    public function rules(): array
    {
        return [
            'schedule_id' => 'required|exists:schedules,id',
            'pertemuan' => 'required|integer|min:1',
            'materi' => 'nullable|string|max:500',
            'jam_mulai' => 'nullable|string',
            'jam_selesai' => 'nullable|string',
            'classroom_condition' => 'nullable|string|max:500',
            'teacher_notes' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_accuracy' => 'nullable|numeric|min:0',
            'attendance' => 'required|array|min:1',
            'attendance.*' => 'required|string|in:hadir,sakit,izin,alpa',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->resolvedSchedule) {
                return;
            }

            $classGroupId = $this->resolvedSchedule->class_group_id;
            $academicYearId = $this->resolvedSchedule->academic_year_id;

            // Get valid student IDs belonging to the class group for the academic year
            $validStudentIds = ClassGroupStudent::where('class_group_id', $classGroupId)
                ->when($academicYearId, function ($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                })
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('joined_at')->orWhereDate('joined_at', '<=', today());
                })
                ->where(function ($query) {
                    $query->whereNull('left_at')->orWhereDate('left_at', '>=', today());
                })
                ->pluck('student_id')
                ->map(fn ($id) => (string) $id)
                ->toArray();

            $submittedStudentIds = array_keys($this->input('attendance', []));

            foreach ($submittedStudentIds as $studentId) {
                if (! in_array((string) $studentId, $validStudentIds, true)) {
                    $validator->errors()->add(
                        "attendance.{$studentId}",
                        "Siswa ID {$studentId} tidak terdaftar pada kelas untuk jadwal ini."
                    );
                }
            }

            $missingStudentIds = array_diff($validStudentIds, array_map('strval', $submittedStudentIds));
            if ($missingStudentIds !== []) {
                $validator->errors()->add(
                    'attendance',
                    'Status seluruh siswa aktif wajib diisi sebelum sesi diselesaikan.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'schedule_id.required' => 'Jadwal wajib dipilih.',
            'schedule_id.exists' => 'Jadwal yang dipilih tidak valid.',
            'pertemuan.required' => 'Nomor pertemuan wajib diisi.',
            'pertemuan.integer' => 'Nomor pertemuan harus berupa angka.',
            'attendance.required' => 'Daftar absensi siswa wajib diisi.',
            'attendance.*.in' => 'Status kehadiran siswa tidak valid.',
        ];
    }

    public function getSchedule(): ?Schedule
    {
        return $this->resolvedSchedule;
    }
}

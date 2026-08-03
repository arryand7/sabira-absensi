<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleTimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'super_admin'], true);
    }

    public function rules(): array
    {
        $slot = $this->route('scheduleTimeSlot');

        return [
            'education_program_id' => ['required', 'exists:education_programs,id'],
            'position' => [
                'required', 'integer', 'min:1', 'max:50',
                Rule::unique('schedule_time_slots')->where(
                    fn ($query) => $query->where('education_program_id', $this->integer('education_program_id'))
                )->ignore($slot?->id),
            ],
            'slot_number' => ['nullable', 'integer', 'min:1', 'max:30'],
            'label' => ['nullable', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_break' => ['nullable', 'boolean'],
            'friday_enabled' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

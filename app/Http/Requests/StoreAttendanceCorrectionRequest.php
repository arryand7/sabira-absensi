<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'guru';
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'materi' => ['required', 'string', 'max:5000'],
            'classroom_condition' => ['nullable', 'string', 'max:5000'],
            'teacher_notes' => ['nullable', 'string', 'max:5000'],
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*' => ['required', 'in:hadir,sakit,izin,alpa'],
        ];
    }
}

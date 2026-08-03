<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveScheduleConflictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resolve', $this->route('scheduleConflict')) ?? false;
    }

    public function rules(): array
    {
        return [
            'resolution' => ['required', Rule::in(['keep_current', 'keep_existing', 'keep_both', 'dismiss'])],
            'resolution_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

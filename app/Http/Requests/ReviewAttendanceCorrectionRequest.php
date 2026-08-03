<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewAttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approved,rejected'],
            'review_notes' => ['nullable', 'required_if:decision,rejected', 'string', 'max:2000'],
        ];
    }
}

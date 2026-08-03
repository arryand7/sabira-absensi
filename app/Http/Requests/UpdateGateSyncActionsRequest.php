<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGateSyncActionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'actions' => ['required', 'array'],
            'actions.*' => ['required', 'in:no_change,create_local,update_local,suspend_local,reactivate_local,manual_review'],
        ];
    }
}

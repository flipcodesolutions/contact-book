<?php

namespace App\Http\Requests;

use App\Models\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->canManageStatuses();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'color' => trim((string) $this->input('color')),
        ]);
    }

    public function rules(): array
    {
        $status = $this->route('status');
        $statusId = $status instanceof LeadStatus ? $status->id : null;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('lead_statuses', 'name')->ignore($statusId)],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter the status name.',
            'name.unique' => 'That status name is already used.',
            'color.required' => 'Choose a colour.',
            'color.regex' => 'Use a colour like #C4A58D.',
        ];
    }
}

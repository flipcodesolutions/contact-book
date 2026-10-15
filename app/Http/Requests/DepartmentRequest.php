<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->canManageDepartments();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        $department = $this->route('department');
        $departmentId = $department instanceof Department ? $department->id : null;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($departmentId)->whereNull('deleted_at')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter the department name.',
            'name.unique' => 'That department name is already used.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Only admins and super admins can save users.
     * A super admin account can only be edited by a super admin.
     */
    public function authorize(): bool
    {
        $actor = $this->user();

        if ($actor === null || ! $actor->canManageUsers()) {
            return false;
        }

        $target = $this->route('user');

        if ($target instanceof User && $target->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = trim((string) $this->input('phone'));

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => $phone === '' ? null : $phone,
        ]);
    }

    public function rules(): array
    {
        $allowedRoles = [];

        foreach (array_keys(User::roleOptions()) as $role) {
            if ($this->user()->canAssignRole($role)) {
                $allowedRoles[] = $role;
            }
        }

        $target = $this->route('user');
        $userId = $target instanceof User ? $target->id : null;

        $passwordRules = ['string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'];

        if ($userId) {
            array_unshift($passwordRules, 'nullable');
        } else {
            array_unshift($passwordRules, 'required');
        }

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in($allowedRoles)],
            'password' => $passwordRules,
            'is_active' => ['nullable', 'boolean'],
        ];

        $rules['all_departments'] = ['nullable', 'boolean'];
        $rules['all_countries'] = ['nullable', 'boolean'];

        if (! in_array($this->input('role'), ['super_admin', 'admin'])) {
            $rules['department_ids'] = ['nullable', 'array'];
            $rules['department_ids.*'] = [
                'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ];
            $rules['country_ids'] = ['nullable', 'array'];
            $rules['country_ids.*'] = [
                'integer',
                Rule::exists('countries', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (in_array($this->input('role'), ['super_admin', 'admin'])) {
                return;
            }

            $hasDepartments = $this->boolean('all_departments') || count((array) $this->input('department_ids', [])) > 0;
            $hasCountries = $this->boolean('all_countries') || count((array) $this->input('country_ids', [])) > 0;

            if (! $hasDepartments || ! $hasCountries) {
                $validator->errors()->add('department_ids', 'Choose at least one department and one country, or tick All on each side.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter the user name.',
            'email.required' => 'Enter an email address.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'That email is already in use.',
            'role.required' => 'Choose a role.',
            'role.in' => 'You cannot assign that role.',
            'password.required' => 'Enter a password.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must include letters and numbers.',
            'department_ids.required' => 'Choose departments, countries, or tick All on at least one side.',
        ];
    }
}

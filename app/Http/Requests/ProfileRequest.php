<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'old_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'required_with:old_password', 'string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter your name.',
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'That email is already in use.',
            'old_password.required_with' => 'Enter your old password to set a new password.',
            'old_password.current_password' => 'The old password is incorrect.',
            'password.required_with' => 'Enter a new password.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must include letters and numbers.',
        ];
    }
}

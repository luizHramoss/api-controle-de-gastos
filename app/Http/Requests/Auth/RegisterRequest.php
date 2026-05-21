<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'min:2', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'The name field is required.',
            'name.min'           => 'The name must be at least 2 characters.',
            'email.required'     => 'The email field is required.',
            'email.email'        => 'Please provide a valid email address.',
            'email.unique'       => 'This email is already registered.',
            'password.required'  => 'The password field is required.',
            'password.min'       => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}

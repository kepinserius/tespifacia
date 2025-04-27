<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin users can manage users
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ];

        // Add unique email rule for create or when updating a different user
        if ($this->isMethod('post') || ($this->isMethod('put') && $this->user->id != $this->route('user'))) {
            $rules['email'][] = 'unique:users';
        }

        // Only require password for new users
        if ($this->isMethod('post')) {
            $rules['password'] = [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ];
        } elseif ($this->filled('password')) {
            $rules['password'] = [
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'roles.required' => 'Please assign at least one role to the user.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
            'password.required' => 'Password is required for new users.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}

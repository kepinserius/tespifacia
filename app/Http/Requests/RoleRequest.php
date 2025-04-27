<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin users can manage roles
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ];

        // Add unique name rule for create or when updating a different role
        if ($this->isMethod('post')) {
            $rules['name'][] = 'unique:roles,name';
        } elseif ($this->isMethod('put')) {
            $rules['name'][] = Rule::unique('roles', 'name')->ignore($this->route('role'));
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
            'name.required' => 'The role name is required.',
            'name.unique' => 'This role name already exists.',
            'permissions.required' => 'Please assign at least one permission to the role.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.',
        ];
    }
}

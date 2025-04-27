<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'is_priority' => 'boolean',
            'due_date' => 'nullable|date',
            'metadata' => 'nullable|json',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'project_id.required' => 'Please select a project for this task.',
            'project_id.exists' => 'The selected project does not exist.',
            'status.required' => 'The task status is required.',
            'status.in' => 'The task status must be one of: pending, in progress, completed, or cancelled.',
            'metadata.json' => 'The metadata must be a valid JSON format.',
        ];
    }
}

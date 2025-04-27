<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'metadata' => 'nullable|json',
        ];

        // Add document validation only for create or when document is present
        if ($this->isMethod('post') || $this->hasFile('document')) {
            $rules['document'] = [
                'nullable',
                'file',
                'mimes:pdf',
                'min:100', // 100KB
                'max:500', // 500KB
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
            'name.required' => 'The project name is required.',
            'category_id.required' => 'Please select a category for this project.',
            'category_id.exists' => 'The selected category does not exist.',
            'document.mimes' => 'The document must be a PDF file.',
            'document.min' => 'The document must be at least 100KB.',
            'document.max' => 'The document must not exceed 500KB.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'metadata.json' => 'The metadata must be a valid JSON format.',
        ];
    }
}

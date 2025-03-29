<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        // Custom validation to prevent circular references
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $categoryId = $this->route('category');
            
            // Additional validation to prevent circular parent-child relationships
            if ($this->parent_id && $this->parent_id == $categoryId) {
                $rules['parent_id'] = 'nullable|exists:categories,id|different:category';
            }
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
            'parent_id.different' => 'A category cannot be its own parent.',
        ];
    }
}
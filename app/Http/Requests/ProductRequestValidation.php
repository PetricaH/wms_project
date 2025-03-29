<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'upc' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'dimensions.length' => 'nullable|numeric|min:0',
            'dimensions.width' => 'nullable|numeric|min:0',
            'dimensions.height' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
            'is_active' => 'boolean',
        ];

        // For SKU, we need a unique rule that ignores the current product when updating
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['sku'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('products')->ignore($this->route('product')),
            ];
        } else {
            $rules['sku'] = 'required|string|max:100|unique:products';
        }

        return $rules;
    }
}
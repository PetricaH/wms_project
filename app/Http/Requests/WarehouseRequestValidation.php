<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
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
            'name' => 'required|string|max:100',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        // For code, we need a unique rule that ignores the current warehouse when updating
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['code'] = [
                'required',
                'string',
                'max:20',
                Rule::unique('warehouses')->ignore($this->route('warehouse')),
            ];
        } else {
            $rules['code'] = 'required|string|max:20|unique:warehouses';
        }

        return $rules;
    }
}
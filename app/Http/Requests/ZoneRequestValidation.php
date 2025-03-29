<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ZoneRequest extends FormRequest
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
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        // For code, we need a unique rule that is scoped to the warehouse_id
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['code'] = [
                'required',
                'string',
                'max:20',
                Rule::unique('zones')->where(function ($query) {
                    return $query->where('warehouse_id', $this->warehouse_id);
                })->ignore($this->route('zone')),
            ];
        } else {
            $rules['code'] = [
                'required',
                'string',
                'max:20',
                Rule::unique('zones')->where(function ($query) {
                    return $query->where('warehouse_id', $this->warehouse_id);
                }),
            ];
        }

        return $rules;
    }
}
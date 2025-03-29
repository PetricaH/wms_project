<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BinLocationRequest extends FormRequest
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
            'zone_id' => 'required|exists:zones,id',
            'name' => 'required|string|max:100',
            'position' => 'nullable|array',
            'position.aisle' => 'nullable|string',
            'position.bay' => 'nullable|string',
            'position.level' => 'nullable|string',
            'capacity' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];

        // For code, we need a unique rule that is scoped to the zone_id
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['code'] = [
                'required',
                'string',
                'max:20',
                Rule::unique('bin_locations')->where(function ($query) {
                    return $query->where('zone_id', $this->zone_id);
                })->ignore($this->route('bin_location')),
            ];
        } else {
            $rules['code'] = [
                'required',
                'string',
                'max:20',
                Rule::unique('bin_locations')->where(function ($query) {
                    return $query->where('zone_id', $this->zone_id);
                }),
            ];
        }

        return $rules;
    }
}
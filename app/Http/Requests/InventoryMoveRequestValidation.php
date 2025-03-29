<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryMoveRequestValidation extends FormRequest {
    // determine if the user is authorized to make this request
    public function authorize(): bool
    {
        return true;
    }

    // get the validation rules that apply to the request
    public function rules(): array 
    { 
        return [
            'product_id' => 'required|exists:products,id',
            'from_location_id' => 'required|exists:bin_locations,id',
            'to_location_id' => [
                'required',
                'exists:bin_locations,id',
                'different:from_location_id'
            ],
            'quantity' => 'required|numeric|gt:0',
            'lot_number' => 'nullable|string|max:100',
            'batch_number' => 'nullable|string|max:100',
            'unit_of_measure' => 'nullable|string|max:50',
            'reason' => 'nullable|string|max:255',
            'reference_type' => 'nullable|string|max:100',
            'reference_id' => 'nullable|integer',
        ];
    }

    // get custom messages for validator errors
    public function messages(): array
    {
        return [
            'to_location_id.different' => 'The destination location must be different from the source location.',
        ];
    }
}
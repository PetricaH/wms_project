<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryAdjustRequest extends FormRequest 
{
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
            'locaton_id' => 'required|exists:bin_locations,id',
            'new_quantity' => 'required|numeric|min:0',
            'lot_number' => 'nullable|string|max:100',
            'batch_number' => 'nullable|string|max:100',
            'unit_of_measure' => 'nullable|string|max:50',
            'unit_cost' => 'nullable|numeric|min:0',
            'reason' => 'required|string|max:255',
        ];
    }
}
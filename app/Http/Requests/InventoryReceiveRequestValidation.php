<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryReceiveRequest extends FormRequest
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
        return [
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:bin_locations,id',
            'quantity' => 'required|numeric|gt:0',
            'lot_number' => 'nullable|string|max:100',
            'batch_number' => 'nullable|string|max:100',
            'unit_of_measure' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',
            'unit_cost' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255',
            'reference_type' => 'nullable|string|max:100',
            'reference_id' => 'nullable|integer',
        ];
    }
}
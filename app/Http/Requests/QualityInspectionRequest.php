<?php

namespace App\Http\Requests;

class QualityInspectionRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'receiving.quality_inspection';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // The items array is required and must be an array
            'items' => 'required|array|min:1',
            
            // Each item in the array must have specific fields
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.status' => 'required|in:pass,fail',
            'items.*.notes' => 'nullable|string|max:500',
            
            // If status is 'fail', additional fields are required
            'items.*.quantity_rejected' => 'required_if:items.*.status,fail|nullable|numeric|min:0',
            'items.*.rejection_reason' => 'required_if:items.*.status,fail|nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one item must be specified for inspection.',
            'items.*.purchase_order_item_id.required' => 'Purchase order item ID is required for each item.',
            'items.*.purchase_order_item_id.exists' => 'The specified purchase order item does not exist.',
            'items.*.status.required' => 'Inspection status is required for each item.',
            'items.*.status.in' => 'Inspection status must be either "pass" or "fail".',
            'items.*.quantity_rejected.required_if' => 'Quantity rejected is required for failed items.',
            'items.*.rejection_reason.required_if' => 'Rejection reason is required for failed items.',
        ];
    }
}
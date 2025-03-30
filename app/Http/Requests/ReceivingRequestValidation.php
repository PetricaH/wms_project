<?php

namespace App\Http\Requests;

class ReceiveItemsRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'receiving.receive';

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
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.location_id' => 'required|exists:bin_locations,id',
            'items.*.lot_number' => 'nullable|string|max:50',
            'items.*.notes' => 'nullable|string|max:500',
            
            // Optional metadata can be provided
            'metadata' => 'nullable|array',
            'metadata.reference_number' => 'nullable|string|max:50',
            'metadata.carrier' => 'nullable|string|max:100',
            'metadata.tracking_number' => 'nullable|string|max:100',
        ];
    }
}

class RejectItemsRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'receiving.reject';

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
            'items.*.quantity' => 'required|numeric|min:0.001',
            
            // A reason for rejection is required
            'reason' => 'required|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one item must be specified for rejection.',
            'items.*.purchase_order_item_id.required' => 'Purchase order item ID is required for each item.',
            'items.*.purchase_order_item_id.exists' => 'The specified purchase order item does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.numeric' => 'Quantity must be a number.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
            'reason.required' => 'A reason for rejection is required.',
        ];
    }
}

class CloseReceivingRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'receiving.close';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Optional notes about closing the purchase order
            'notes' => 'nullable|string|max:1000',
        ];
    }
}

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
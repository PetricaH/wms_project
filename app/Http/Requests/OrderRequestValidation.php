<?php

namespace App\Http\Requests;

class CreateOrderRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'orders.create';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Order data validation
            'order' => 'required|array',
            'order.customer_name' => 'required|string|max:255',
            'order.customer_email' => 'nullable|email|max:255',
            'order.customer_phone' => 'nullable|string|max:50',
            'order.shipping_address' => 'required|string',
            'order.billing_address' => 'nullable|string',
            'order.warehouse_id' => 'required|exists:warehouses,id',
            'order.order_date' => 'nullable|date',
            'order.due_date' => 'nullable|date',
            'order.currency' => 'nullable|string|size:3',
            'order.external_order_id' => 'nullable|string|max:255',
            'order.customer_reference' => 'nullable|string|max:255',
            'order.customer_notes' => 'nullable|string',
            'order.source' => 'nullable|string|max:50',
            
            // Items array validation
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.line_number' => 'nullable|integer|min:1',
            'items.*.unit_of_measure' => 'nullable|string|max:50',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order.customer_name.required' => 'Customer name is required.',
            'order.shipping_address.required' => 'Shipping address is required.',
            'order.warehouse_id.required' => 'Warehouse must be specified.',
            'order.warehouse_id.exists' => 'The specified warehouse does not exist.',
            'items.required' => 'At least one item must be included in the order.',
            'items.*.product_id.required' => 'Product ID is required for each item.',
            'items.*.product_id.exists' => 'One or more specified products do not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.numeric' => 'Quantity must be a number.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
        ];
    }
}

class UpdateOrderRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'orders.edit';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Order data validation for updates
            'order' => 'required|array',
            'order.customer_name' => 'sometimes|string|max:255',
            'order.customer_email' => 'nullable|email|max:255',
            'order.customer_phone' => 'nullable|string|max:50',
            'order.shipping_address' => 'sometimes|string',
            'order.billing_address' => 'nullable|string',
            'order.warehouse_id' => 'sometimes|exists:warehouses,id',
            'order.due_date' => 'nullable|date',
            'order.currency' => 'nullable|string|size:3',
            'order.external_order_id' => 'nullable|string|max:255',
            'order.customer_reference' => 'nullable|string|max:255',
            'order.customer_notes' => 'nullable|string',
            'order.internal_notes' => 'nullable|string',
            'order.source' => 'nullable|string|max:50',
            'order.shipping_method' => 'nullable|string|max:100',
            'order.payment_method' => 'nullable|string|max:100',
            'order.payment_reference' => 'nullable|string|max:100',
        ];
    }
}

class ProcessPaymentRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'orders.process_payment';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Payment data validation
            'payment' => 'required|array',
            'payment.method' => 'required|string|max:100',
            'payment.reference' => 'nullable|string|max:100',
            'payment.amount' => 'required|numeric|min:0.01',
            'payment.processor' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment.method.required' => 'Payment method is required.',
            'payment.amount.required' => 'Payment amount is required.',
            'payment.amount.numeric' => 'Payment amount must be a number.',
            'payment.amount.min' => 'Payment amount must be greater than zero.',
        ];
    }
}
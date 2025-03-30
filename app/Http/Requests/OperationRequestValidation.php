<?php

namespace App\Http\Requests;

// Picking Request Classes

class RecordPickRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'picking.pick';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_item_id' => 'required|exists:order_items,id',
            'quantity' => 'required|numeric|min:0.001',
            'location_id' => 'required|exists:bin_locations,id',
            'additional_data' => 'nullable|array',
            'additional_data.lot_number' => 'nullable|string|max:50',
            'additional_data.notes' => 'nullable|string',
        ];
    }
}

class BatchPickRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'picking.pick';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'picks' => 'required|array|min:1',
            'picks.*.order_item_id' => 'required|exists:order_items,id',
            'picks.*.quantity' => 'required|numeric|min:0.001',
            'picks.*.location_id' => 'required|exists:bin_locations,id',
            'picks.*.lot_number' => 'nullable|string|max:50',
            'picks.*.notes' => 'nullable|string',
        ];
    }
}

class CompletePickingRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'picking.complete';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'completion_data' => 'nullable|array',
            'completion_data.notes' => 'nullable|string',
        ];
    }
}

class WavePickRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'picking.wave';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'required|exists:orders,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ];
    }
}

// Packing Request Classes

class PackOrderItemRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'packing.pack_items';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_item_id' => 'required|exists:order_items,id',
            'packing_data' => 'required|array',
            'packing_data.container_type' => 'nullable|string|max:50',
            'packing_data.container_identifier' => 'nullable|string|max:50',
            'packing_data.notes' => 'nullable|string',
        ];
    }
}

class PackOrderItemsRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'packing.pack_items';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'packing_data' => 'required|array',
            'packing_data.items' => 'required|array|min:1',
            'packing_data.items.*.order_item_id' => 'required|exists:order_items,id',
            'packing_data.items.*.container_type' => 'nullable|string|max:50',
            'packing_data.items.*.container_identifier' => 'nullable|string|max:50',
            'packing_data.items.*.notes' => 'nullable|string',
            'packing_data.container_type' => 'nullable|string|max:50',
            'packing_data.container_identifier' => 'nullable|string|max:50',
            'packing_data.notes' => 'nullable|string',
        ];
    }
}

class CompletePackingRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'packing.complete';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'completion_data' => 'nullable|array',
            'completion_data.container_type' => 'nullable|string|max:50',
            'completion_data.container_identifier' => 'nullable|string|max:50',
            'completion_data.notes' => 'nullable|string',
            'completion_data.shipping_weight' => 'nullable|numeric|min:0',
            'completion_data.dimensions' => 'nullable|array',
            'completion_data.dimensions.length' => 'nullable|numeric|min:0',
            'completion_data.dimensions.width' => 'nullable|numeric|min:0',
            'completion_data.dimensions.height' => 'nullable|numeric|min:0',
            'completion_data.packing_materials' => 'nullable|array',
            'completion_data.ready_for_shipment' => 'nullable|boolean',
        ];
    }
}

// Shipping Request Classes

class ShipOrderRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'shipping.ship_orders';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'shipping_data' => 'required|array',
            'shipping_data.provider' => 'nullable|string',
            'shipping_data.service' => 'required|string',
            'shipping_data.carrier' => 'nullable|string',
            'shipping_data.packages' => 'nullable|array',
            'shipping_data.packages.*.weight' => 'nullable|numeric|min:0',
            'shipping_data.packages.*.dimensions' => 'nullable|array',
            'shipping_data.packages.*.dimensions.length' => 'nullable|numeric|min:0',
            'shipping_data.packages.*.dimensions.width' => 'nullable|numeric|min:0',
            'shipping_data.packages.*.dimensions.height' => 'nullable|numeric|min:0',
            'shipping_data.generate_label' => 'nullable|boolean',
            'shipping_data.label_options' => 'nullable|array',
        ];
    }
}

class ManualShipRequest extends BaseFormRequest
{
    /**
     * The permission required for this request
     */
    protected $permission = 'shipping.ship_orders';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'shipping_data' => 'required|array',
            'shipping_data.tracking_number' => 'nullable|string|max:100',
            'shipping_data.carrier' => 'nullable|string|max:100',
            'shipping_data.shipping_method' => 'nullable|string|max:100',
            'shipping_data.shipped_date' => 'nullable|date',
            'shipping_data.notes' => 'nullable|string',
        ];
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryAdjustRequest;
use App\Http\Requests\InventoryMoveRequest;
use App\Http\Requests\InventoryPickRequest;
use App\Http\Requests\InventoryReceiveRequest;
use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\Inventory\InventoryStrategyInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    protected $inventoryStrategy;

    /**
     * Constructor with dependency injection.
     */
    public function __construct(InventoryStrategyInterface $inventoryStrategy)
    {
        $this->inventoryStrategy = $inventoryStrategy;
    }

    /**
     * Display a listing of inventory.
     */
    public function index(Request $request)
    {
        $query = Inventory::query();
        
        // Apply filters
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        
        if ($request->has('lot_number')) {
            $query->where('lot_number', 'like', '%' . $request->lot_number . '%');
        }
        
        if ($request->has('batch_number')) {
            $query->where('batch_number', 'like', '%' . $request->batch_number . '%');
        }
        
        if ($request->has('has_stock') && $request->has_stock) {
            $query->where('quantity', '>', 0);
        }
        
        if ($request->has('has_available') && $request->has_available) {
            $query->where('available_quantity', '>', 0);
        }
        
        if ($request->has('expires_before')) {
            $query->where(function ($query) use ($request) {
                $query->where('expiry_date', '<=', $request->expires_before)
                    ->orWhereNull('expiry_date');
            });
        }
        
        // With relationships
        $query->with(['product', 'binLocation.zone.warehouse']);
        
        // Order by
        $orderBy = $request->get('order_by', 'id');
        $orderDirection = $request->get('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        
        return $query->paginate($perPage);
    }

    /**
     * Get inventory summary.
     */
    public function summary(Request $request)
    {
        // Summary by product
        if ($request->has('by_product') && $request->by_product) {
            $summary = Inventory::selectRaw('
                product_id,
                SUM(quantity) as total_quantity,
                SUM(reserved_quantity) as total_reserved,
                SUM(available_quantity) as total_available
            ')
            ->with('product')
            ->groupBy('product_id')
            ->get();
            
            return response()->json($summary);
        }
        
        // Summary by location
        if ($request->has('by_location') && $request->by_location) {
            $summary = Inventory::selectRaw('
                location_id,
                SUM(quantity) as total_quantity,
                SUM(reserved_quantity) as total_reserved,
                SUM(available_quantity) as total_available
            ')
            ->with('binLocation.zone.warehouse')
            ->groupBy('location_id')
            ->get();
            
            return response()->json($summary);
        }
        
        // Summary by warehouse
        if ($request->has('by_warehouse') && $request->by_warehouse) {
            $summary = Inventory::selectRaw('
                wh.id as warehouse_id,
                wh.name as warehouse_name,
                SUM(i.quantity) as total_quantity,
                SUM(i.reserved_quantity) as total_reserved,
                SUM(i.available_quantity) as total_available
            ')
            ->from('inventory as i')
            ->join('bin_locations as bl', 'i.location_id', '=', 'bl.id')
            ->join('zones as z', 'bl.zone_id', '=', 'z.id')
            ->join('warehouses as wh', 'z.warehouse_id', '=', 'wh.id')
            ->groupBy('wh.id', 'wh.name')
            ->get();
            
            return response()->json($summary);
        }
        
        // Default overall summary
        $summary = [
            'total_products' => Product::count(),
            'total_quantity' => Inventory::sum('quantity'),
            'total_reserved' => Inventory::sum('reserved_quantity'),
            'total_available' => Inventory::sum('available_quantity'),
            'unique_product_locations' => Inventory::count(),
        ];
        
        return response()->json($summary);
    }

    /**
     * Get inventory movements.
     */
    public function movements(Request $request)
    {
        $query = InventoryMovement::query();
        
        // Apply filters
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        if ($request->has('from_location_id')) {
            $query->where('from_location_id', $request->from_location_id);
        }
        
        if ($request->has('to_location_id')) {
            $query->where('to_location_id', $request->to_location_id);
        }
        
        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }
        
        if ($request->has('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }
        
        if ($request->has('reference_id')) {
            $query->where('reference_id', $request->reference_id);
        }
        
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // With relationships
        $query->with(['product', 'fromLocation.zone.warehouse', 'toLocation.zone.warehouse']);
        
        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        
        return $query->paginate($perPage);
    }

    /**
     * Process inventory receipt.
     */
    public function receive(InventoryReceiveRequest $request)
    {
        $product = Product::findOrFail($request->product_id);
        $location = BinLocation::findOrFail($request->location_id);
        
        try {
            $result = $this->inventoryStrategy->receive(
                $product,
                $location,
                $request->quantity,
                $request->only(['lot_number', 'batch_number', 'unit_of_measure', 'expiry_date', 'unit_cost', 'reason']),
                $request->reference_type,
                $request->reference_id
            );
            
            return response()->json([
                'message' => 'Inventory received successfully',
                'data' => $result
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to receive inventory',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Process inventory transfer.
     */
    public function transfer(InventoryMoveRequest $request)
    {
        $product = Product::findOrFail($request->product_id);
        $fromLocation = BinLocation::findOrFail($request->from_location_id);
        $toLocation = BinLocation::findOrFail($request->to_location_id);
        
        try {
            $result = $this->inventoryStrategy->transfer(
                $product,
                $fromLocation,
                $toLocation,
                $request->quantity,
                $request->only(['lot_number', 'batch_number', 'unit_of_measure', 'reason']),
                $request->reference_type,
                $request->reference_id
            );
            
            return response()->json([
                'message' => 'Inventory transferred successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to transfer inventory',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Process inventory picking.
     */
    public function pick(InventoryPickRequest $request)
    {
        $product = Product::findOrFail($request->product_id);
        $location = BinLocation::findOrFail($request->location_id);
        
        try {
            $result = $this->inventoryStrategy->pick(
                $product,
                $location,
                $request->quantity,
                $request->only(['lot_number', 'batch_number', 'unit_of_measure', 'reason']),
                $request->reference_type,
                $request->reference_id
            );
            
            return response()->json([
                'message' => 'Inventory picked successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to pick inventory',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Process inventory adjustment.
     */
    public function adjust(InventoryAdjustRequest $request)
    {
        $product = Product::findOrFail($request->product_id);
        $location = BinLocation::findOrFail($request->location_id);
        
        try {
            $result = $this->inventoryStrategy->adjust(
                $product,
                $location,
                $request->new_quantity,
                $request->only(['lot_number', 'batch_number', 'unit_of_measure', 'unit_cost']),
                $request->reason
            );
            
            return response()->json([
                'message' => 'Inventory adjusted successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to adjust inventory',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Warehouse::query();
        
        // Apply filters
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        if ($request->has('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // With relationships
        if ($request->has('with_zones')) {
            $query->with('zones');
        }
        
        // Order by
        $orderBy = $request->get('order_by', 'name');
        $orderDirection = $request->get('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        
        return $query->paginate($perPage);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WarehouseRequest $request)
    {
        $warehouse = Warehouse::create($request->validated());
        
        return response()->json($warehouse, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $warehouse = Warehouse::with('zones.binLocations')->findOrFail($id);
        
        return response()->json($warehouse);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(WarehouseRequest $request, string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update($request->validated());
        
        return response()->json($warehouse);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        // Check if warehouse has zones
        if ($warehouse->zones()->exists()) {
            return response()->json([
                'message' => 'Cannot delete warehouse with existing zones'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $warehouse->delete();
        
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get all zones for a warehouse.
     */
    public function zones(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $zones = $warehouse->zones()->with('binLocations')->get();
        
        return response()->json($zones);
    }

    /**
     * Get all bin locations for a warehouse.
     */
    public function binLocations(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $binLocations = $warehouse->binLocations()->with('zone')->get();
        
        return response()->json($binLocations);
    }
}
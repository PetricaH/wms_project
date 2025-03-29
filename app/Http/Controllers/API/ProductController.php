<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query();
        
        // Apply filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        if ($request->has('sku')) {
            $query->where('sku', 'like', '%' . $request->sku . '%');
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // Order by
        $orderBy = $request->get('order_by', 'name');
        $orderDirection = $request->get('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        
        // With relationships
        if ($request->has('with_category')) {
            $query->with('category');
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());
        
        return response()->json($product, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->findOrFail($id);
        
        // Add inventory summary
        $product->append(['total_quantity', 'available_quantity', 'reserved_quantity']);
        
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, string $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());
        
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        
        // Check if product has inventory
        if ($product->inventory()->exists()) {
            return response()->json([
                'message' => 'Cannot delete product with existing inventory'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $product->delete();
        
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Get inventory locations for a product.
     */
    public function inventory(string $id)
    {
        $product = Product::findOrFail($id);
        
        $inventory = $product->inventory()
            ->with('binLocation.zone.warehouse')
            ->get();
            
        return response()->json($inventory);
    }
}
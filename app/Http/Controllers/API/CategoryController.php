<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::query();
        
        // Filter by parent
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id === 'null' ? null : $request->parent_id);
        }
        
        // Filter by name
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Filter active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // With relationships
        if ($request->has('with_parent')) {
            $query->with('parent');
        }
        
        if ($request->has('with_children')) {
            $query->with('children');
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
     * Get all categories as a tree structure.
     */
    public function tree()
    {
        // Get root categories first
        $categories = Category::whereNull('parent_id')
            ->with(['children.children']) // Load two levels of children
            ->get();
            
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());
        
        return response()->json($category, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::with(['parent', 'children'])->findOrFail($id);
        
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, string $id)
    {
        $category = Category::findOrFail($id);
        
        // Prevent circular references
        if ($id == $request->parent_id) {
            return response()->json([
                'message' => 'A category cannot be its own parent',
                'errors' => [
                    'parent_id' => ['A category cannot be its own parent'],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $category->update($request->validated());
        
        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        
        // Check if category has children
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with subcategories'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        // Check if category has products
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with associated products'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $category->delete();
        
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
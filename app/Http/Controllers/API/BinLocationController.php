<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\BinLocationRequest;
use App\Models\BinLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BinLocationController extends Controller
{
    // display a listing of the resource
    public function index(Request $request)
    {
        $query = BinLocation::query();

        // apply filters
        if ($request->has('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        if ($request->where('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // with relationships
        if ($request->has('with_zone')) {
            $query->with('zone');
        }

        if ($request->has('with_warehouse')) {
            $query->with('zone.warehouse');
        }

        // order by
        $orderBy = $request->get('order_by', 'name');
        $orderDirection = $request->get('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

        // pagination
        $perPage = $request->get('per_page', 15);
        
        return $query->paginate($perPage);
    }

    // store a newly created resource in storage
    public function store(BinLocationRequest $request) {
        $binLocation = BinLocation::create($request->validated());

        return response()->json($binLocation, Response::HTTP_CREATED);
    }

    // display the specified resource
    public function show(string $id) {
        $binLocation = BinLocation::with('zone.warehouse')->findOrFail($id);

        return response()->json($binLocation);
    }

    // update the specified resource in storage
    public function update(BinLocationRequest $request, string $id) {
        $binLocation = BinLocation::findOrFail($id);
        $binLocation->update($request->validate());

        return response()->json($binLocation);
    }
    
    // remove the specified resource from storage
    public function destroy(string $id) {
        $binLocation = BinLocation::findOrFail($id);

        // check if bin location has inventory
        if ($binLocation->inventory()->exists()) {
            return response()->json([
                'message' => 'Cannot delete bin location with existing inventory'
            ], Response::HTTP_UNPROCESSABLE_ENTITY); 
        }

        $binLocation->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    // get inventory for a bin location
    public function inventory(string $id) {
        $binLocation = BinLocation::findOrFail($id);

        $inventory = $binLocation->inventory()
            ->with('product')
            ->get();

        return response()->json($inventory);
    }
}
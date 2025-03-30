<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZoneRequest;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ZoneController extends Controller
{
    // display a listing of the resources
    public function index(Request $request) 
    {
        $query = Zone::query();

        // apply filters
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // with relationships
        if ($request->has('with_warehouse')) {
            $query->where('warehouse');
        }

        if ($request->has('with_bin_locations')) {
            $query->with('binLocations');
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
    public function store(ZoneRequest $request) 
    {
        $zone = Zone::create($request->validate());

        return response()->json($zone, Response::HTTP_CREATED);
    }

    // display the specified resource
    public function show(string $id) 
    {
        $zone = Zone::with(['warehouse', 'binLocations'])->findOrFail($id);

        return response()->json($zone);
    }

    // update the specified resource in storage
    public function update(ZoneRequest $request, string $id)
    {
        $zone = Zone::findOrFail($id);
        $zone->update($request->validate());

        return response()->json($zone);
    }

    // remove the specified resource from storage
    public function destroy(string $id) 
    {
        $zone = Zone::findOrFail($id);

        // check if zone has bin locations
        if ($zone->binLocations()->exists()) {
            return response()->json([
                'message' => 'Cannot delete zone with existing locations'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $zone->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    // get all bin locations for a zone
    public function binLocations(string $id) 
    {
        $zone = Zone::findOrFail($id);
        $binLocations = $zone->binLocations()->get();

        return response()->json($binLocations);
    }
}
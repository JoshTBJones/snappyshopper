<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Postcode;
use App\Http\Resources\StoreResource;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return StoreResource::collection(
            Store::where('organisation_id', request('organisation_id'))->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        try {
            $validatedData = $request->validate([
                'name'                  => 'required|string|max:255',
                'organisation_id'       => 'required|exists:organisations,id',
                'category_ids'          => 'required|array',
                'category_ids.*'        => 'exists:categories,id', // Validate each category ID exists in the categories table
                'open'                  => 'required|boolean',
                'max_delivery_distance' => 'required|integer|min:1',
                'postcode'              => 'required_without_all:longitude,latitude|string|max:10',
                'longitude'             => 'required_with:latitude|required_without:postcode|numeric',
                'latitude'              => 'required_with:longitude|required_without:postcode|numeric',
            ]);

            // The task defines that the store should be created with geo
            // coordinates data, but we already have this information in the
            // postcode table. To avoid creating duplicate data, we will use the
            // postcode id to set the coordinates.

            // get postcode for store
            if (!$request->has(['longitude', 'latitude']))
            {
                // normalise postcode
                $validatedData['postcode'] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $validatedData['postcode']));
                $postcode = Postcode::where('postcode', $validatedData['postcode'])->first();
                if (!$postcode)
                {
                    return response()->json([
                        'message' => 'Supplied postcode not found. Please submit longitude and latitude instead.'
                    ], 404);
                }

                $validatedData['longitude'] = $postcode->longitude;
                $validatedData['latitude'] = $postcode->latitude;
                $validatedData['postcode_id'] = $postcode->id;
            }

            // Create the store
            $store = Store::create($validatedData);

            // Attach categories
            $store->categories()->sync($validatedData['category_ids']);

            // Return the created store as a resource
            return response()->json([
                'message' => 'Store created successfully.',
                'store' => new StoreResource($store),
            ], 201);

        }
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create store',
                'error' => $e->getMessage()
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        try
        {
            $store = Store::findByUuid($uuid);

            if (!$store) { throw new \Exception('Store not found', 404); }

            return response()->json(new StoreResource($store));
        }
        catch (\Exception $e)
        {
            return response()->json([
                'message' => 'Failed to get store',
                'error' => $e->getMessage()
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        try
        {
            $store = Store::findByUuid($uuid);

            if (!$store) { throw new \Exception('Store not found', 404); }
            $store->delete();

            return response()->json([
                'message' => 'Store deleted successfully.'
            ], 204);
        }
        catch (\Exception $e)
        {
            return response()->json([
                'message' => 'Failed to delete store',
                'error' => $e->getMessage()
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Get all stores within a specified radius of a postcode.
     * 
     * Uses the Haversine formula to calculate distances between coordinates.
     * Distance is calculated in kilometers from the postcode to each store.
     *
     * @param \Illuminate\Http\Request $request The request object containing radius
     * @param string $postcode The postcode to search around
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Collection of stores within radius
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If postcode not found
     * @throws \Illuminate\Validation\ValidationException If radius validation fails
     */
    public function storesNear(Request $request, string $postcode)
    {
        try
        {
            $validated = $request->validate([
                'radius' => 'required|integer',
            ]);

            $postcode = Postcode::where('postcode', $postcode)->firstOrFail();
            $radius = $validated['radius'];

            return StoreResource::collection(Store::near($postcode, $radius));
        }
        catch (\Exception $e)
        {
            return response()->json([
                'message' => 'Failed to get stores',
                'error' => $e->getMessage()
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    /**
     * Get all stores that will deliver to a given postcode.
     * 
     * Uses each store's max_delivery_distance to determine if they will deliver.
     * Distance is calculated using the Haversine formula.
     *
     * @param string $postcode The postcode to check delivery for
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Collection of stores that deliver to postcode
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If postcode not found
     */
    public function storesDelivering(string $postcode)
    {
        try
        {
            $postcode = Postcode::where('postcode', $postcode)->firstOrFail();

            $stores = Store::near($postcode);

            return StoreResource::collection($stores);
        }
        catch (\Exception $e)
        {
            return response()->json([
                'message' => 'Failed to get stores',
                'error' => $e->getMessage()
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }
}

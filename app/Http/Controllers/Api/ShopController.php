<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display the authenticated user's shop details.
     */
    public function show(Request $request)
    {
        $shopId = $request->user()->shop_id;
        $shop = Shop::findOrFail($shopId);

        return response()->json([
            'success' => true,
            'data' => $shop
        ]);
    }

    /**
     * Update the authenticated user's shop details.
     */
    public function update(Request $request)
    {
        $shopId = $request->user()->shop_id;
        $shop = Shop::findOrFail($shopId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $shop->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Shop profile updated successfully.',
            'data' => $shop
        ]);
    }
}

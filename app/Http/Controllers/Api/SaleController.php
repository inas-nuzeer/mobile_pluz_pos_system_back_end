<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Store a new sale with its items.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:CASH,CARD,UPI,MOBILE_MONEY',
            'created_at' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
        ]);

        try {
            $sale = DB::transaction(function () use ($validated) {
                $saleData = [
                    'user_id' => auth()->id(),
                    'total_amount' => $validated['total_amount'],
                    'discount' => $validated['discount'] ?? 0,
                    'final_amount' => $validated['final_amount'],
                    'payment_method' => $validated['payment_method'],
                ];

                if (isset($validated['created_at'])) {
                    $saleData['created_at'] = $validated['created_at'];
                }

                $sale = Sale::create($saleData);

                foreach ($validated['items'] as $item) {
                    if (!$product->is_service) {
                        if ($product->quantity < $item['quantity']) {
                            throw new \Exception("Insufficient stock for product: {$product->name}");
                        }

                        // Atomic decrement
                        $product->decrement('quantity', $item['quantity']);

                        // Log stock movement
                        Stock::create([
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'type' => 'out',
                            'note' => "Sale #{$sale->id}",
                        ]);
                    }

                    $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                return $sale->fresh();
            });

            return response()->json([
                'success' => true,
                'message' => 'Sale created successfully',
                'data' => [
                    'id' => $sale->id,
                    'shop_id' => $sale->shop_id,
                    'user_id' => $sale->user_id,
                    'total_amount' => (float) $sale->total_amount,
                    'discount' => (float) $sale->discount,
                    'final_amount' => (float) $sale->final_amount,
                    'payment_method' => $sale->payment_method,
                    'created_at' => $sale->created_at->toIso8601String(),
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified sale.
     */
    public function show($id)
    {
        $sale = Sale::with(['items.product', 'user'])->findOrFail($id);

        // Map product details for the frontend
        $sale->items->each(function ($item) {
            $item->product_name = $item->product->name;
            $item->brand_name = $item->product->brand->name ?? null;
            $item->model_name = $item->product->model->name ?? null;
        });

        return response()->json([
            'success' => true,
            'data' => $sale
        ]);
    }

    public function index(Request $request)
    {
        $query = Sale::with(['items.product', 'user']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhere('payment_method', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $sales = $query->latest()->get();
        
        // Map product details for each sale's items
        $sales->each(function ($sale) {
            $sale->items->each(function ($item) {
                if ($item->product) {
                    $item->product_name = $item->product->name;
                    $item->brand_name = $item->product->brand->name ?? null;
                    $item->model_name = $item->product->model->name ?? null;
                }
            });
        });

        return response()->json([
            'success' => true,
            'data' => $sales
        ]);
    }

    /**
     * Update the specified sale.
     */
    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($sale, $validated) {
                // 1. Reverse old stock deductions
                foreach ($sale->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product && !$product->is_service) {
                        $product->increment('quantity', $item->quantity);
                        Stock::create([
                            'product_id' => $item['product_id'],
                            'quantity' => $item->quantity,
                            'type' => 'in',
                            'note' => "Sale #{$sale->id} Updated (Stock Reversal)",
                        ]);
                    }
                }

                // 2. Update sale header
                $sale->update([
                    'total_amount' => $validated['total_amount'],
                    'discount' => $validated['discount'] ?? 0,
                    'final_amount' => $validated['final_amount'],
                    'payment_method' => $validated['payment_method'],
                ]);

                // 3. Replace sale items
                $sale->items()->delete();
                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);

                    if (!$product->is_service) {
                        if ($product->quantity < $item['quantity']) {
                            throw new \Exception("Insufficient stock for product: {$product->name}");
                        }

                        // Deduct new stock
                        $product->decrement('quantity', $item['quantity']);

                        // Log new stock movement
                        Stock::create([
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'type' => 'out',
                            'note' => "Sale #{$sale->id} Updated (New Deduction)",
                        ]);
                    }

                    $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Sale updated successfully',
                'data' => $sale->fresh(['items.product'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified sale.
     */
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);

        try {
            DB::transaction(function () use ($sale) {
                // Restore stock
                foreach ($sale->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product && !$product->is_service) {
                        $product->increment('quantity', $item->quantity);
                        Stock::create([
                            'product_id' => $item['product_id'],
                            'quantity' => $item->quantity,
                            'type' => 'in',
                            'note' => "Sale #{$sale->id} Deleted",
                        ]);
                    }
                }

                $sale->items()->delete();
                $sale->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Sale deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

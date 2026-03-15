<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $today = \Carbon\Carbon::now()->toDateString();
        $shopId = $request->shop_id;
        
        // Base Query builder with shop_id if provided
        $saleQuery = Sale::query()->whereDate('created_at', $today);
        $itemQuery = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.created_at', $today);
            
        if ($shopId) {
            $saleQuery->where('sales.shop_id', $shopId);
            $itemQuery->where('sales.shop_id', $shopId);
        }

        // Today's Sales
        $todaySales = (float) $saleQuery->sum('final_amount');
        
        // Today's Profit (Revenue - COGS)
        $todayCogs = (float) $itemQuery->sum(\DB::raw('sale_items.quantity * products.purchase_price'));
            
        $todayProfit = $todaySales - $todayCogs;
        
        // Transaction Count Today
        $transactionCount = $saleQuery->count();
        
        // Low Stock Products
        $productQuery = Product::with(['category', 'brand', 'phoneModel']);
        if ($shopId) $productQuery->where('products.shop_id', $shopId);
        
        $lowStockProducts = $productQuery
            ->where('quantity', '<=', 10)
            ->orderBy('quantity', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'todaySales' => $todaySales,
                'todayProfit' => $todayProfit,
                'transactionCount' => $transactionCount,
                'lowStockProducts' => $lowStockProducts,
                'totalSales' => (float) Sale::when($shopId, fn($q) => $q->where('shop_id', $shopId))->sum('final_amount'),
                'productCount' => Product::when($shopId, fn($q) => $q->where('shop_id', $shopId))->count(),
            ]
        ]);
    }
}

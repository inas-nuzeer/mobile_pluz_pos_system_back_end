<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Sales Report with summary, daily breakdown, and payment methods
     */
    public function sales(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'shop_id' => 'nullable|exists:shops,id',
        ]);

        // Default date range: start of current month to today
        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $validated['end_date'] ?? Carbon::now()->format('Y-m-d');

        // Build base query
        $query = Sale::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        // Admin can filter by shop_id, otherwise use authenticated user's shop
        if (isset($validated['shop_id'])) {
            $query->where('shop_id', $validated['shop_id']);
        }

        // Summary statistics
        $totalSales = (float) $query->sum('final_amount');
        $totalTransactions = $query->count();
        $totalDiscount = (float) $query->sum('discount');
        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Daily breakdown
        $dailyBreakdown = Sale::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->when(isset($validated['shop_id']), fn($q) => $q->where('shop_id', $validated['shop_id']))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(final_amount) as sales'),
                DB::raw('COUNT(*) as transactions')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'sales' => (float) $item->sales,
                'transactions' => $item->transactions
            ]);

        // Payment methods breakdown
        $paymentMethods = Sale::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->when(isset($validated['shop_id']), fn($q) => $q->where('shop_id', $validated['shop_id']))
            ->select('payment_method', DB::raw('SUM(final_amount) as total'))
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method')
            ->map(fn($value) => (float) $value);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_sales' => $totalSales,
                    'total_transactions' => $totalTransactions,
                    'total_discount' => $totalDiscount,
                    'average_transaction' => round($averageTransaction, 2),
                ],
                'daily_breakdown' => $dailyBreakdown,
                'payment_methods' => $paymentMethods,
                'today_stats' => [
                    'totalSales' => (float) Sale::whereDate('created_at', Carbon::now()->toDateString())->sum('final_amount'),
                    'transactionCount' => Sale::whereDate('created_at', Carbon::now()->toDateString())->count(),
                ]
            ]
        ]);
    }

    /**
     * Expenses Report with summary, category breakdown, and daily breakdown
     */
    public function expenses(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'shop_id' => 'nullable|exists:shops,id',
        ]);

        // Default date range
        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $validated['end_date'] ?? Carbon::now()->format('Y-m-d');

        // Build base query
        $query = Expense::query()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if (isset($validated['shop_id'])) {
            $query->where('shop_id', $validated['shop_id']);
        }

        // Summary statistics
        $totalExpenses = (float) $query->sum('amount');
        $expenseCount = $query->count();
        $averageExpense = $expenseCount > 0 ? $totalExpenses / $expenseCount : 0;

        // Category breakdown
        $categoryBreakdown = Expense::query()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->when(isset($validated['shop_id']), fn($q) => $q->where('shop_id', $validated['shop_id']))
            ->select(
                'category',
                DB::raw('SUM(amount) as amount'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('category')
            ->groupBy('category')
            ->get()
            ->map(fn($item) => [
                'category' => $item->category,
                'amount' => (float) $item->amount,
                'count' => $item->count
            ]);

        // Daily breakdown
        $dailyBreakdown = Expense::query()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->when(isset($validated['shop_id']), fn($q) => $q->where('shop_id', $validated['shop_id']))
            ->select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(amount) as amount'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'amount' => (float) $item->amount,
                'count' => $item->count
            ]);

        // Recent expenses
        $recentExpenses = (clone $query)
            ->with(['shop'])
            ->orderBy('date', 'desc')
            ->limit(50)
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_expenses' => $totalExpenses,
                    'expense_count' => $expenseCount,
                    'average_expense' => round($averageExpense, 2),
                ],
                'category_breakdown' => $categoryBreakdown,
                'daily_breakdown' => $dailyBreakdown,
                'expenses' => $recentExpenses,
            ]
        ]);
    }

    /**
     * Profit Report with accurate COGS, gross profit, and net profit
     */
    public function profit(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'shop_id' => 'nullable|exists:shops,id',
        ]);

        // Default date range
        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $validated['end_date'] ?? Carbon::now()->format('Y-m-d');

        // Calculate revenue from sales
        $revenueQuery = Sale::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if (isset($validated['shop_id'])) {
            $revenueQuery->where('shop_id', $validated['shop_id']);
        }

        $revenue = (float) $revenueQuery->sum('final_amount');

        // Calculate COGS (Cost of Goods Sold) based on purchase_price
        $cogsQuery = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.created_at', '>=', $startDate)
            ->whereDate('sales.created_at', '<=', $endDate);

        if (isset($validated['shop_id'])) {
            $cogsQuery->where('sales.shop_id', $validated['shop_id']);
        }

        $cogs = (float) $cogsQuery->sum(DB::raw('sale_items.quantity * products.purchase_price'));

        // Calculate gross profit
        $grossProfit = $revenue - $cogs;

        // Calculate total expenses
        $expensesQuery = Expense::query()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if (isset($validated['shop_id'])) {
            $expensesQuery->where('shop_id', $validated['shop_id']);
        }

        $totalExpenses = (float) $expensesQuery->sum('amount');

        // Calculate net profit
        $netProfit = $grossProfit - $totalExpenses;

        // Calculate profit margin
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'revenue' => $revenue,
                'cost_of_goods_sold' => $cogs,
                'gross_profit' => $grossProfit,
                'expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'profit_margin' => round($profitMargin, 2),
                'transaction_count' => (int) $revenueQuery->count(),
            ]
        ]);
    }

    /**
     * Stock Report with low stock items and top selling products
     */
    public function stock(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'nullable|exists:shops,id',
            'low_stock_threshold' => 'nullable|integer|min:1',
        ]);

        $threshold = $validated['low_stock_threshold'] ?? 10;

        // Build base query
        $query = Product::query();

        if (isset($validated['shop_id'])) {
            $query->where('shop_id', $validated['shop_id']);
        }

        // Total products
        $totalProducts = $query->count();

        // Low stock count
        $lowStockCount = (clone $query)->lowStock($threshold)->count();

        // Out of stock count
        $outOfStockCount = (clone $query)->outOfStock()->count();

        // Total stock value
        $totalStockValue = (float) (clone $query)->sum(DB::raw('quantity * selling_price'));

        // Low stock items
        $lowStockItems = (clone $query)
            ->with(['category', 'brand'])
            ->lowStock($threshold)
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => $product->quantity,
                'category' => $product->category?->name ?? null,
                'brand' => $product->brand?->name ?? null,
            ]);

        // Top selling products (based on sale items)
        $topSellingQuery = Product::query()
            ->join('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.subtotal) as revenue')
            )
            ->groupBy('products.id', 'products.name');

        if (isset($validated['shop_id'])) {
            $topSellingQuery->where('products.shop_id', $validated['shop_id']);
        }

        $topSelling = $topSellingQuery
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'total_sold' => $item->total_sold,
                'revenue' => (float) $item->revenue,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'total_stock_value' => $totalStockValue,
                'low_stock_items' => $lowStockItems,
                'top_selling' => $topSelling,
            ]
        ]);
    }
}

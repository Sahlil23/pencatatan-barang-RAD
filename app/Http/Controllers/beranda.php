<?php

namespace App\Http\Controllers;
use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\StockTransaction;


class beranda extends Controller
{
    public function beranda()
    {
        // Key metrics
        $totalItems = Item::count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();
        $lowStockItems = Item::lowStock()->count();

        // Recent transactions
        $recentTransactions = StockTransaction::with(['item.category'])
            ->latest()
            ->take(10)
            ->get();

        // Low stock items
        $lowStockItemsList = Item::lowStock()
            ->with(['category', 'supplier'])
            ->take(5)
            ->get();

        // Today's transactions summary
        $todayStockIn = StockTransaction::stockIn()
            ->whereDate('created_at', today())
            ->sum('quantity');

        $todayStockOut = StockTransaction::stockOut()
            ->whereDate('created_at', today())
            ->sum('quantity');

        // Monthly chart data
        $monthlyData = StockTransaction::selectRaw('
                DATE(created_at) as date,
                transaction_type,
                SUM(quantity) as total
            ')
            ->whereMonth('created_at', now()->month)
            ->groupBy('date', 'transaction_type')
            ->orderBy('date')
            ->get();

        // Stock by category
        $stockByCategory = Category::withSum('items', 'current_stock')
            ->having('items_sum_current_stock', '>', 0)
            ->get();

        return view('index', compact(
            'totalItems',
            'totalCategories', 
            'totalSuppliers',
            'lowStockItems',
            'recentTransactions',
            'lowStockItemsList',
            'todayStockIn',
            'todayStockOut',
            'monthlyData',
            'stockByCategory'
        ));
    }

    public function quickStats()
    {
        return response()->json([
            'total_items' => Item::count(),
            'low_stock_count' => Item::lowStock()->count(),
            'out_of_stock_count' => Item::outOfStock()->count(),
            'today_transactions' => StockTransaction::whereDate('created_at', today())->count()
        ]);
    }
}
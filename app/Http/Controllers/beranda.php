<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\BranchStockTransaction;
use Carbon\Carbon;

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
        $recentTransactions = BranchStockTransaction::with(['item.category'])
            ->latest()
            ->take(10)
            ->get();

        // Low stock items
        $lowStockItemsList = Item::lowStock()
            ->with(['category'])
            ->take(5)
            ->get();

        // Today's transactions summary
        $todayStockIn = BranchStockTransaction::stockIn()
            ->whereDate('created_at', today())
            ->sum('quantity');

        $todayStockOut = BranchStockTransaction::stockOut()
            ->whereDate('created_at', today())
            ->sum('quantity');

        // This month's transactions summary
        $monthStockIn = BranchStockTransaction::stockIn()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('quantity');

        $monthStockOut = BranchStockTransaction::stockOut()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('quantity');

        // Monthly chart data - daily breakdown for current month
        $monthlyChartData = BranchStockTransaction::selectRaw('
                DATE(created_at) as date,
                transaction_type,
                SUM(quantity) as total
            ')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('date', 'transaction_type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Prepare data for ApexCharts
        $monthlyDates = [];
        $monthlyStockIn = [];
        $monthlyStockOut = [];

        // Get all dates in current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $monthlyDates[] = $date->format('d/m');

            $dayData = $monthlyChartData->get($dateStr, collect());
            $monthlyStockIn[] = $dayData->where('transaction_type', 'IN')->sum('total');
            $monthlyStockOut[] = $dayData->where('transaction_type', 'OUT')->sum('total');
        }

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
            'monthStockIn',
            'monthStockOut',
            'monthlyDates',
            'monthlyStockIn',
            'monthlyStockOut',
            'stockByCategory'
        ));
    }

    public function quickStats()
    {
        return response()->json([
            'total_items' => Item::count(),
            'low_stock_count' => Item::lowStock()->count(),
            'out_of_stock_count' => Item::outOfStock()->count(),
            'today_transactions' => BranchStockTransaction::whereDate('created_at', today())->count()
        ]);
    }
}
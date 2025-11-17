<?php
// filepath: d:\xampp\htdocs\Chicking-BJM\app\Http\Controllers\beranda.php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\BranchStockTransaction;
use App\Models\CentralStockTransaction;
use App\Models\OutletStockTransaction;
use App\Models\KitchenStockTransaction;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Models\CentralToBranchWarehouseTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class beranda extends Controller
{
    public function beranda()
    {
        $user = auth()->user();
        
        // Role-based data
        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        } elseif ($user->isCentralManager() && $user->isCentralStaff()) {
            return $this->centralDashboard();
        } elseif ($user->isBranchManager() && $user->isBranchStaff()) {
            return $this->branchDashboard();
        } elseif ($user->isOutletManager() && $user->isOutletStaff()) {
            return $this->outletAndKitchenDashboard(); // ✅ GABUNG OUTLET + KITCHEN
        }
        
        // Default fallback
        return $this->defaultDashboard();
    }
    
    // ========================================
    // SUPER ADMIN DASHBOARD
    // ========================================
    private function superAdminDashboard()
    {
        $data = [
            // Global Statistics
            'totalBranches' => Branch::count(),
            'totalWarehouses' => Warehouse::count(),
            'centralWarehouses' => Warehouse::where('warehouse_type', 'central')->count(),
            'branchWarehouses' => Warehouse::where('warehouse_type', 'branch')->count(),
            'outletWarehouses' => Warehouse::where('warehouse_type', 'outlet')->count(),
            'totalItems' => Item::count(),
            'totalCategories' => Category::count(),
            'totalSuppliers' => Supplier::count(),
            'totalUsers' => User::count(),
            
            // Stock Overview (All Levels)
            'totalCentralStock' => DB::table('central_stock_balances')
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->sum('closing_stock'),
            'totalBranchStock' => DB::table('branch_warehouse_monthly_balances')
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->sum('closing_stock'),
            'totalOutletStock' => DB::table('outlet_warehouse_monthly_balances')
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->sum('closing_stock'),
            'totalKitchenStock' => DB::table('monthly_kitchen_stock_balances')
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->sum('closing_stock'),
            
            // Pending Actions
            'pendingDistributions' => CentralToBranchWarehouseTransaction::where('status', 'PENDING')->count(),
            'lowStockItems' => Item::lowStock()->count(),
            
            // Today's Activity Summary
            'todayActivity' => [
                'central_in' => CentralStockTransaction::where('transaction_type', 'PURCHASE' && 'BRANCH_RETURN')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'central_out' => CentralStockTransaction::where('transaction_type', 'DISTRIBUTE_OUT')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'branch_in' => BranchStockTransaction::where('transaction_type', 'IN')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'branch_out' => BranchStockTransaction::where('transaction_type', 'OUT')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'outlet_in' => OutletStockTransaction::where('transaction_type', 'IN')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'kitchen_usage' => KitchenStockTransaction::where('transaction_type', 'USAGE')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
            ],
            
            // Monthly Summary
            'monthlyActivity' => $this->getMonthlyActivitySummary(),
            
            // Stock Movement Chart (Combined All Levels)
            'stockMovementChart' => $this->getCombinedStockMovementChart(),
            
            // Top Performing Branches
            'topBranches' => Warehouse::withCount(['branchStockTransactions' => function($query) {
                    $query->whereMonth('transaction_date', now()->month)
                          ->whereYear('transaction_date', now()->year);
                }])
                ->orderBy('branch_stock_transactions_count', 'desc')
                ->take(5)
                ->get(),
            
            // Alerts & Notifications
            'alerts' => $this->getSystemAlerts(),
        ];
        
        return view('dashboard.super-admin', $data);
    }
    
    // ========================================
    // CENTRAL WAREHOUSE DASHBOARD
    // ========================================
    private function centralDashboard()
    {
        $user = auth()->user();
        $warehouseId = $user->warehouse_id;
        
        $data = [
            // Warehouse Info
            'warehouse' => Warehouse::find($warehouseId),
            
            // Current Stock Summary
            'currentStock' => [
                'total_items' => DB::table('central_stock_balances')
                    ->where('warehouse_id', $warehouseId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->count(),
                'total_quantity' => DB::table('central_stock_balances')
                    ->where('warehouse_id', $warehouseId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->sum('closing_stock'),
                // ✅ FIX: Specify table name for unit_cost
                'total_value' => DB::table('central_stock_balances')
                    ->join('items', 'central_stock_balances.item_id', '=', 'items.id')
                    ->where('central_stock_balances.warehouse_id', $warehouseId)
                    ->where('central_stock_balances.month', now()->month)
                    ->where('central_stock_balances.year', now()->year)
                    ->sum(DB::raw('central_stock_balances.closing_stock * items.unit_cost')), // ← SPECIFY TABLE
                'low_stock_count' => DB::table('central_stock_balances')
                    ->join('items', 'central_stock_balances.item_id', '=', 'items.id')
                    ->where('central_stock_balances.warehouse_id', $warehouseId)
                    ->where('central_stock_balances.month', now()->month)
                    ->where('central_stock_balances.year', now()->year)
                    ->whereRaw('central_stock_balances.closing_stock <= items.low_stock_threshold') // ← SPECIFY TABLE
                    ->count(),
            ],
            
            // Today's Activity
            'todayActivity' => [
                'received' => CentralStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'PURCHASE')
                    ->orwhere('transaction_type', 'BRANCH_RETURN')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'distributed' => CentralStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'DISTRIBUTE_OUT')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'transactions_count' => CentralStockTransaction::where('warehouse_id', $warehouseId)
                    ->whereDate('transaction_date', today())
                    ->count(),
            ],
            
            // This Month Activity
            'monthActivity' => [
                'received' => CentralStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'PURCHASE')
                    ->orwhere('transaction_type', 'BRANCH_RETURN')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum(DB::raw('abs(`quantity`)')),
                'distributed' => CentralStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'DISTRIBUTE_OUT')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum(DB::raw('abs(`quantity`)')),
                'distributions_count' => CentralStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'DISTRIBUTE_OUT')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->count(),
            ],
            
            // Pending Distributions to Branches
            'pendingDistributions' => CentralToBranchWarehouseTransaction::where('central_warehouse_id', $warehouseId)
                ->where('status', 'PENDING')
                ->with(['branchWarehouse.branch', 'item'])
                ->get(),
            
            // Recent Transactions
            'recentTransactions' => CentralStockTransaction::where('warehouse_id', $warehouseId)
                ->with(['item.category', 'user'])
                ->latest('transaction_date')
                ->take(10)
                ->get(),
            
            // Stock Movement Chart
            'stockMovementChart' => $this->getStockMovementChart('central', $warehouseId),
            
            // Top Distributed Items
            'topDistributedItems' => CentralStockTransaction::where('warehouse_id', $warehouseId)
                ->where('transaction_type', 'DISTRIBUTE_OUT')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->select('item_id', DB::raw('SUM(quantity) as total'))
                ->groupBy('item_id')
                ->with('item')
                ->orderBy('total', 'desc')
                ->take(5)
                ->get(),
            
            // Distribution by Branch Status
            'distributionStatus' => CentralToBranchWarehouseTransaction::where('central_warehouse_id', $warehouseId)
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),
        ];
        
        return view('dashboard.central', $data);
    }
    
    // ========================================
    // BRANCH WAREHOUSE DASHBOARD
    // ========================================
    private function branchDashboard()
    {
        $user = auth()->user();
        $warehouseId = $user->warehouse_id;
        $branchId = $user->branch_id;
        
        $data = [
            // Warehouse Info
            'warehouse' => Warehouse::with('branch')->find($warehouseId),
            
            // Current Stock Summary
            'currentStock' => [
                'total_items' => DB::table('branch_warehouse_monthly_balances')
                    ->where('warehouse_id', $warehouseId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->count(),
                'total_quantity' => DB::table('branch_warehouse_monthly_balances')
                    ->where('warehouse_id', $warehouseId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->sum('closing_stock'),
                'low_stock_count' => DB::table('branch_warehouse_monthly_balances')
                    ->join('items', 'branch_warehouse_monthly_balances.item_id', '=', 'items.id')
                    ->where('branch_warehouse_monthly_balances.warehouse_id', $warehouseId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->whereRaw('closing_stock <= low_stock_threshold')
                    ->count(),
            ],
            
            // Pending from Central
            'pendingFromCentral' => CentralToBranchWarehouseTransaction::where('warehouse_id', $warehouseId)
                ->where('status', 'PENDING')
                ->with(['centralWarehouse', 'item'])
                ->get(),
            
            // Today's Activity
            'todayActivity' => [
                'received' => BranchStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'IN')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'distributed' => BranchStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'OUT')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'transactions_count' => BranchStockTransaction::where('warehouse_id', $warehouseId)
                    ->whereDate('transaction_date', today())
                    ->count(),
            ],
            
            // This Month Activity
            'monthActivity' => [
                'received' => BranchStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'IN')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum(DB::raw('abs(`quantity`)')),
                'distributed' => BranchStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'OUT')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum(DB::raw('abs(`quantity`)')),
            ],
            
            // Recent Transactions
            'recentTransactions' => BranchStockTransaction::where('warehouse_id', $warehouseId)
                ->with(['item.category', 'user'])
                ->latest('transaction_date')
                ->take(10)
                ->get(),
            
            // Stock Movement Chart
            'stockMovementChart' => $this->getStockMovementChart('branch', $warehouseId),
            
            // Top Distributed Items to Outlets
            'topDistributedItems' => BranchStockTransaction::where('warehouse_id', $warehouseId)
                ->where('transaction_type', 'OUT')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->select('item_id', DB::raw('SUM(quantity) as total'))
                ->groupBy('item_id')
                ->with('item')
                ->orderBy('total', 'desc')
                ->take(5)
                ->get(),
            
            // Outlet Warehouses in Branch
            // 'outletWarehouses' => Warehouse::where('warehouse_type', 'outlet')
            //     ->where('branch_id', $branchId)
            //     ->withCount(['outletStockTransaction' => function($query) {
            //         $query->whereMonth('transaction_date', now()->month)
            //               ->whereYear('transaction_date', now()->year);
            //     }])
            //     ->get(),
        ];
        
        return view('dashboard.branch', $data);
    }
    
    // ========================================
    // ✅ OUTLET & KITCHEN DASHBOARD (GABUNG)
    // ========================================
    private function outletAndKitchenDashboard()
    {
        $user = auth()->user();
        $warehouseId = $user->warehouse_id;
        $branchId = $user->branch_id;
        
        $data = [
            // Warehouse Info
            'warehouse' => Warehouse::with('branch')->find($warehouseId),
            
            // ========== OUTLET WAREHOUSE SECTION ==========
            'outletStock' => [
                'total_items' => DB::table('outlet_warehouse_monthly_balances')
                    ->where('warehouse_id', $warehouseId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->count(),
                'total_quantity' => DB::table('outlet_warehouse_monthly_balances')
                    ->where('warehouse_id', $warehouseId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->sum('closing_stock'),
                'low_stock_count' => DB::table('outlet_warehouse_monthly_balances')
                    ->join('items', 'outlet_warehouse_monthly_balances.item_id', '=', 'items.id')
                    ->where('outlet_warehouse_monthly_balances.warehouse_id', $warehouseId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->whereRaw('closing_stock <= low_stock_threshold')
                    ->count(),
            ],
            
            // ========== KITCHEN SECTION ==========
            'kitchenStock' => [
                'total_items' => DB::table('monthly_kitchen_stock_balances')
                    ->where('branch_id', $branchId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->count(),
                'total_quantity' => DB::table('monthly_kitchen_stock_balances')
                    ->where('branch_id', $branchId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->sum('closing_stock'),
                'low_stock_count' => DB::table('monthly_kitchen_stock_balances')
                    ->join('items', 'monthly_kitchen_stock_balances.item_id', '=', 'items.id')
                    ->where('monthly_kitchen_stock_balances.branch_id', $branchId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->whereRaw('closing_stock <= low_stock_threshold')
                    ->count(),
            ],
            
            // ========== TODAY'S ACTIVITY ==========
            'todayActivity' => [
                // Outlet
                'outlet_received' => OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                    ->where('transaction_type', 'IN')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'outlet_to_kitchen' => OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                    ->where('transaction_type', 'OUT')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                    
                // Kitchen
                'kitchen_received' => KitchenStockTransaction::where('branch_id', $branchId)
                    ->where('transaction_type', 'IN')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                'kitchen_usage' => KitchenStockTransaction::where('branch_id', $branchId)
                    ->where('transaction_type', 'USAGE')
                    ->whereDate('transaction_date', today())
                    ->sum(DB::raw('abs(`quantity`)')),
                    
                // Total transactions
                'total_transactions' => OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                    ->whereDate('transaction_date', today())
                    ->count() +
                    KitchenStockTransaction::where('branch_id', $branchId)
                    ->whereDate('transaction_date', today())
                    ->count(),
            ],
            
            // ========== THIS MONTH ACTIVITY ==========
            'monthActivity' => [
                // Outlet
                'outlet_received' => OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                    ->where('transaction_type', 'IN')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum(DB::raw('abs(`quantity`)')),
                'outlet_to_kitchen' => OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                    ->where('transaction_type', 'OUT')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum(DB::raw('abs(`quantity`)')),
                    
                // Kitchen
                'kitchen_received' => KitchenStockTransaction::where('branch_id', $branchId)
                    ->where('transaction_type', 'IN')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum(DB::raw('abs(`quantity`)')),
                'kitchen_usage' => KitchenStockTransaction::where('branch_id', $branchId)
                    ->where('transaction_type', 'USAGE')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum(DB::raw('abs(`quantity`)')),
            ],
            
            // ========== RECENT TRANSACTIONS (COMBINED) ==========
            'recentTransactions' => [
                'outlet' => OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                    ->with(['item.category', 'user'])
                    ->latest('transaction_date')
                    ->take(5)
                    ->get(),
                'kitchen' => KitchenStockTransaction::where('branch_id', $branchId)
                    ->with(['item.category', 'user'])
                    ->latest('transaction_date')
                    ->take(5)
                    ->get(),
            ],
            
            // ========== STOCK MOVEMENT CHARTS ==========
            'outletChartData' => $this->getStockMovementChart('outlet', $warehouseId),
            'kitchenChartData' => $this->getStockMovementChart('kitchen', null, $branchId),
            
            // ========== TOP USED ITEMS ==========
            'topUsedItems' => [
                'outlet' => OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                    ->where('transaction_type', 'OUT')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->select('item_id', DB::raw('SUM(quantity) as total'))
                    ->groupBy('item_id')
                    ->with('item')
                    ->orderBy('total', 'desc')
                    ->take(5)
                    ->get(),
                'kitchen' => KitchenStockTransaction::where('branch_id', $branchId)
                    ->where('transaction_type', 'USAGE')
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->select('item_id', DB::raw('SUM(quantity) as total'))
                    ->groupBy('item_id')
                    ->with('item')
                    ->orderBy('total', 'desc')
                    ->take(5)
                    ->get(),
            ],
            
            // ========== LOW STOCK ITEMS (BOTH OUTLET & KITCHEN) ==========
            'lowStockItems' => [
                'outlet' => DB::table('outlet_warehouse_monthly_balances')
                    ->join('items', 'outlet_warehouse_monthly_balances.item_id', '=', 'items.id')
                    ->where('outlet_warehouse_monthly_balances.warehouse_id', $warehouseId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->whereRaw('closing_stock <= low_stock_threshold')
                    ->select('items.*', 'outlet_warehouse_monthly_balances.closing_stock')
                    ->get(),
                'kitchen' => DB::table('monthly_kitchen_stock_balances')
                    ->join('items', 'monthly_kitchen_stock_balances.item_id', '=', 'items.id')
                    ->where('monthly_kitchen_stock_balances.branch_id', $branchId)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->whereRaw('closing_stock <= low_stock_threshold')
                    ->select('items.*', 'monthly_kitchen_stock_balances.closing_stock')
                    ->get(),
            ],
        ];
        
        return view('dashboard.outlet-kitchen', $data);
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================
    
    private function getMonthlyActivitySummary()
    {
        return [
            'central_in' => CentralStockTransaction::where('transaction_type', 'PURCHASE' && 'BRANCH_RETURN')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum(DB::raw('abs(`quantity`)')),
            'central_out' => CentralStockTransaction::where('transaction_type', 'DISTRIBUTE_OUT')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum(DB::raw('abs(`quantity`)')),
            'branch_in' => BranchStockTransaction::where('transaction_type', 'IN')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum(DB::raw('abs(`quantity`)')),
            'branch_out' => BranchStockTransaction::where('transaction_type', 'OUT')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum(DB::raw('abs(`quantity`)')),
            'outlet_in' => OutletStockTransaction::where('transaction_type', 'RECEIVE_FROM_BRANCH' && 'TRANSFER_IN' && 'ADJUSTMENT_IN')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum(DB::raw('abs(`quantity`)')),
            'kitchen_usage' => KitchenStockTransaction::where('transaction_type', 'USAGE')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum(DB::raw('abs(`quantity`)')),
        ];
    }
    
    private function getCombinedStockMovementChart()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $dates = [];
        $centralIn = [];
        $centralOut = [];
        $branchIn = [];
        $branchOut = [];
        $kitchenUsage = [];
        
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dates[] = $date->format('d/m');
            
            $centralIn[] = CentralStockTransaction::where('transaction_type', 'PURCHASE' && 'BRANCH_RETURN')
                ->whereDate('transaction_date', $dateStr)
                ->sum(DB::raw('abs(`quantity`)'));
            $centralOut[] = CentralStockTransaction::where('transaction_type', 'DISTRIBUTE_OUT')
                ->whereDate('transaction_date', $dateStr)
                ->sum(DB::raw('abs(`quantity`)'));
            $branchIn[] = BranchStockTransaction::where('transaction_type', 'IN')
                ->whereDate('transaction_date', $dateStr)
                ->sum(DB::raw('abs(`quantity`)'));
            $branchOut[] = BranchStockTransaction::where('transaction_type', 'OUT')
                ->whereDate('transaction_date', $dateStr)
                ->sum(DB::raw('abs(`quantity`)'));
            $kitchenUsage[] = KitchenStockTransaction::where('transaction_type', 'USAGE')
                ->whereDate('transaction_date', $dateStr)
                ->sum(DB::raw('abs(`quantity`)'));
        }
        
        return compact('dates', 'centralIn', 'centralOut', 'branchIn', 'branchOut', 'kitchenUsage');
    }
    
    private function getStockMovementChart($level, $warehouseId = null, $branchId = null)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $dates = [];
        $stockIn = [];
        $stockOut = [];
        
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dates[] = $date->format('d/m');
            
            if ($level === 'central') {
                $in = CentralStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'PURCHASE' && 'BRANCH_RETURN')
                    ->whereDate('transaction_date', $dateStr)
                    ->sum(DB::raw('abs(`quantity`)'));
                $out = CentralStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'DISTRIBUTE_OUT')
                    ->whereDate('transaction_date', $dateStr)
                    ->sum(DB::raw('abs(`quantity`)'));
            } elseif ($level === 'branch') {
                $in = BranchStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'IN')
                    ->whereDate('transaction_date', $dateStr)
                    ->sum(DB::raw('abs(`quantity`)'));
                $out = BranchStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'OUT')
                    ->whereDate('transaction_date', $dateStr)
                    ->sum(DB::raw('abs(`quantity`)'));
            } elseif ($level === 'outlet') {
                $in = OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                    ->where('transaction_type', 'IN')
                    ->whereDate('transaction_date', $dateStr)
                    ->sum(DB::raw('abs(`quantity`)'));
                $out = OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                    ->where('transaction_type', 'OUT')
                    ->whereDate('transaction_date', $dateStr)
                    ->sum(DB::raw('abs(`quantity`)'));
            } elseif ($level === 'kitchen') {
                $in = KitchenStockTransaction::where('branch_id', $branchId)
                    ->where('transaction_type', 'IN')
                    ->whereDate('transaction_date', $dateStr)
                    ->sum(DB::raw('abs(`quantity`)'));
                $out = KitchenStockTransaction::where('branch_id', $branchId)
                    ->where('transaction_type', 'USAGE')
                    ->whereDate('transaction_date', $dateStr)
                    ->sum(DB::raw('abs(`quantity`)'));
            } else {
                $in = 0;
                $out = 0;
            }
            
            $stockIn[] = $in;
            $stockOut[] = $out;
        }
        
        return compact('dates', 'stockIn', 'stockOut');
    }
    
    private function getSystemAlerts()
    {
        return [
            'low_stock' => Item::lowStock()->count(),
            'pending_distributions' => CentralToBranchWarehouseTransaction::where('status', 'PENDING')->count(),
            'out_of_stock' => DB::table('outlet_warehouse_monthly_balances')
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->where('closing_stock', '<=', 0)
                ->count() +
                DB::table('monthly_kitchen_stock_balances')
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->where('closing_stock', '<=', 0)
                ->count(),
        ];
    }
    
    private function defaultDashboard()
    {
        return view('dashboard.default', [
            'message' => 'Welcome to Chicking BJM Inventory System'
        ]);
    }
}
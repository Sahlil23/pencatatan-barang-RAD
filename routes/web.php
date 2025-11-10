<?php
// filepath: routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BranchWarehouseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CentralWarehouseController;
use App\Http\Controllers\beranda; 
use App\Http\Controllers\ItemController;
use App\Http\Controllers\KitchenStockController;
use App\Http\Controllers\OutletWarehouseController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;

// ========================================
// PUBLIC ROUTES (Guest Only)
// ========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    // Quick login for development only
    if (app()->environment(['local', 'staging'])) {
        Route::get('/quick-login', [LoginController::class, 'quickLogin'])->name('quick-login');
    }
});

// ========================================
// AUTHENTICATED ROUTES
// ========================================
Route::middleware(['auth', 'set.branch.context'])->group(function () {
    
    // ========================================
    // DASHBOARD & PROFILE
    // ========================================
    Route::get('/', [beranda::class, 'beranda'])->name('dashboard');
    Route::get('/beranda', [beranda::class, 'beranda'])->name('beranda'); // Alias
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    Route::get('/profile', [LoginController::class, 'profile'])->name('profile');
    Route::put('/profile', [LoginController::class, 'updateProfile'])->name('profile.update');
    
    // ========================================
    // MASTER DATA (Categories, Suppliers, Items)
    // ========================================
    
    // Categories
    Route::resource('categories', CategoryController::class);
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
        ->name('categories.toggle-status');
    
    // Suppliers
    Route::resource('suppliers', SupplierController::class);
    
    // Items
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('index');
        Route::get('/create', [ItemController::class, 'create'])->name('create');
        Route::post('/', [ItemController::class, 'store'])->name('store');
        Route::get('/{item}', [ItemController::class, 'show'])->name('show');
        Route::get('/{item}/edit', [ItemController::class, 'edit'])->name('edit');
        Route::put('/{item}', [ItemController::class, 'update'])->name('update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');
        
        // Additional item actions
        Route::get('/low-stock', [ItemController::class, 'lowStock'])->name('low-stock');
        Route::post('/{item}/adjust-stock', [ItemController::class, 'adjustStock'])->name('adjust-stock');
        Route::get('/report', [ItemController::class, 'report'])->name('report');
        Route::get('/print-report', [ItemController::class, 'printReport'])->name('print-report');
        Route::get('/compare-months', [ItemController::class, 'compareMonths'])->name('compare-months');
        Route::get('/{item}/monthly-history', [ItemController::class, 'monthlyHistory'])->name('monthly-history');
        
        // Notifications
        Route::post('/send-low-stock-notification', [ItemController::class, 'sendLowStockNotification'])
            ->name('send-low-stock-notification');
        Route::post('/schedule-daily-low-stock', [ItemController::class, 'scheduleDailyLowStock'])
            ->name('schedule-daily-low-stock');
        Route::post('/cancel-daily-low-stock-schedule', [ItemController::class, 'cancelDailyLowStockSchedule'])
            ->name('cancel-daily-low-stock-schedule');
    });
    
    // ========================================
    // STOCK TRANSACTIONS (Legacy)
    // ========================================
    Route::prefix('stock-transactions')->name('stock-transactions.')->group(function () {
        Route::get('/', [StockTransactionController::class, 'index'])->name('index');
        Route::get('/create', [StockTransactionController::class, 'create'])->name('create');
        Route::post('/', [StockTransactionController::class, 'store'])->name('store');
        Route::get('/{stockTransaction}', [StockTransactionController::class, 'show'])->name('show');
        
        Route::post('/store-multiple', [StockTransactionController::class, 'storeMultiple'])
            ->name('store-multiple');
        Route::get('/report', [StockTransactionController::class, 'report'])->name('report');
        Route::get('/print-report', [StockTransactionController::class, 'printReport'])->name('print-report');
    });
    
    // ========================================
    // RECIPES
    // ========================================
    Route::resource('recipes', RecipeController::class)->except(['show']);
    Route::get('/recipes/{slug}', [RecipeController::class, 'show'])->name('recipes.show');
    
    // ========================================
    // BACKUP & RESTORE
    // ========================================
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/create', [BackupController::class, 'create'])->name('create');
        Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
        Route::post('/restore', [BackupController::class, 'restore'])->name('restore');
        Route::delete('/delete/{filename}', [BackupController::class, 'delete'])->name('delete');
    });
    
    // ========================================
    // USER MANAGEMENT
    // ========================================
    Route::prefix('users')->name('users.')->middleware('check.user.management')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
    
    // ========================================
    // WAREHOUSES MANAGEMENT
    // ========================================
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        // Standard CRUD
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('/create', [WarehouseController::class, 'create'])->name('create');
        Route::post('/', [WarehouseController::class, 'store'])->name('store');
        Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
        Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
        Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
        
        // Additional actions
        Route::post('/{warehouse}/restore', [WarehouseController::class, 'restore'])->name('restore');
        Route::delete('/{warehouse}/force-delete', [WarehouseController::class, 'forceDestroy'])
            ->name('force-destroy');
        Route::post('/{warehouse}/change-status', [WarehouseController::class, 'changeStatus'])
            ->name('change-status');
        
        // Bulk operations
        Route::post('/bulk-action', [WarehouseController::class, 'bulkAction'])->name('bulk-action');
        
        // Export
        Route::get('/export/csv', [WarehouseController::class, 'export'])->name('export');
        
        // AJAX
        Route::get('/api/list', [WarehouseController::class, 'getWarehouses'])->name('api.list');
    });
    
    // ========================================
    // CENTRAL WAREHOUSE
    // ========================================
    Route::prefix('central-warehouse')->name('central-warehouse.')->group(function () {
        // Dashboard & View
        Route::get('/', [CentralWarehouseController::class, 'index'])->name('index');
        Route::get('/{balance}', [CentralWarehouseController::class, 'show'])->name('show');
        Route::get('/transactions/list', [CentralWarehouseController::class, 'transactions'])
            ->name('transactions');
        
        // Stock Receipt
        Route::get('/receive-stock/create', [CentralWarehouseController::class, 'receiveStock'])
            ->name('receive-stock');
        Route::post('/receive-stock', [CentralWarehouseController::class, 'storeReceipt'])
            ->name('store-receipt');
        
        // Stock Adjustment
        Route::get('/{balance}/adjust', [CentralWarehouseController::class, 'adjustStock'])
            ->name('adjust-stock');
        Route::post('/{balance}/adjust', [CentralWarehouseController::class, 'storeAdjustment'])
            ->name('store-adjustment');
        
        // Stock Distribution
        Route::get('/{balance}/distribute', [CentralWarehouseController::class, 'distributeStock'])
            ->name('distribute-stock');
        Route::post('/{balance}/distribute', [CentralWarehouseController::class, 'storeDistribution'])
            ->name('store-distribution');
        
        // Cancel Transaction
        Route::post('/transactions/{transaction}/cancel', [CentralWarehouseController::class, 'cancelTransaction'])
            ->name('cancel-transaction');
        
        // AJAX Endpoints
        Route::get('/api/stock-balance', [CentralWarehouseController::class, 'getStockBalance'])
            ->name('api.stock-balance');
        Route::get('/api/warehouse-items/{warehouse}', [CentralWarehouseController::class, 'getWarehouseItems'])
            ->name('api.warehouse-items');
    });
    
    // ========================================
    // BRANCH WAREHOUSE
    // ========================================
    Route::prefix('branch-warehouse')->name('branch-warehouse.')->group(function () {
        // Dashboard & View
        Route::get('/', [BranchWarehouseController::class, 'index'])->name('index');
        Route::get('/{id}', [BranchWarehouseController::class, 'show'])->name('show');
        Route::get('/{id}/current-stock', [BranchWarehouseController::class, 'getCurrentStock'])
            ->name('current-stock');
        
        // Receive Stock
        Route::get('/{id}/receive-stock', [BranchWarehouseController::class, 'showReceiveStockForm'])
            ->name('receive-form');
        Route::post('/{id}/receive-stock', [BranchWarehouseController::class, 'storeReceiveStock'])
            ->name('receive');
        
        // Adjustment
        Route::get('/{id}/adjust-stock', [BranchWarehouseController::class, 'showAdjustmentForm'])
            ->name('adjust-form');
        Route::post('/{id}/adjust-stock', [BranchWarehouseController::class, 'storeAdjustment'])
            ->name('adjust');
        
        // Distribution
        Route::get('/{id}/distribute', [BranchWarehouseController::class, 'showDistributionForm'])
            ->name('distribute-form');
        Route::post('/{id}/distribute', [BranchWarehouseController::class, 'storeDistribution'])
            ->name('distribute');
        Route::get('/{id}/distributions', [BranchWarehouseController::class, 'distributionHistory'])
            ->name('distributions');
        
        // Reports
        Route::get('/{id}/transaction-summary', [BranchWarehouseController::class, 'getTransactionSummary'])
            ->name('transaction-summary');
        Route::get('/{id}/export-report', [BranchWarehouseController::class, 'exportStockReport'])
            ->name('export-report');
    });
    
    // ========================================
    // OUTLET WAREHOUSE
    // ========================================
    Route::prefix('outlet-warehouse')->name('outlet-warehouse.')->group(function () {
        // Dashboard
        Route::get('/', [OutletWarehouseController::class, 'index'])->name('index');
        Route::get('/{warehouseId}/detail', [OutletWarehouseController::class, 'show'])->name('show');
        
        // Stock Management
        Route::prefix('{warehouseId}')->group(function () {
            // Receive from Branch
            Route::get('/receive', [OutletWarehouseController::class, 'receiveCreate'])
                ->name('receive.create');
            Route::post('/receive', [OutletWarehouseController::class, 'receiveStore'])
                ->name('receive.store');
            
            // Adjustment
            Route::get('/adjustment', [OutletWarehouseController::class, 'adjustmentCreate'])
                ->name('adjustment.create');
            Route::post('/adjustment', [OutletWarehouseController::class, 'adjustmentStore'])
                ->name('adjustment.store');
            
            // Distribution to Kitchen
            Route::get('/distribute', [OutletWarehouseController::class, 'distributeCreate'])
                ->name('distribute.create');
            Route::post('/distribute', [OutletWarehouseController::class, 'distributeStore'])
                ->name('distribute.store');
            
            // Transactions
            Route::get('/transactions', [OutletWarehouseController::class, 'transactions'])
                ->name('transactions');
            
            // API - Get available stock
            Route::get('/stock/available', [OutletWarehouseController::class, 'getAvailableStock'])
                ->name('stock.available');
        });
        
        // AJAX
        Route::get('/ajax/stock/{warehouseId}', [OutletWarehouseController::class, 'getAvailableStock'])
            ->name('ajax.stock');
    });
    
    // ========================================
    // KITCHEN STOCK
    // ========================================
    Route::prefix('kitchen')->name('kitchen.')->group(function () {
        // Dashboard
        Route::get('/', [KitchenStockController::class, 'index'])->name('index');
        
        // Transfer from Outlet
        Route::get('/transfer', [KitchenStockController::class, 'transfer'])->name('transfer');
        Route::post('/transfer', [KitchenStockController::class, 'processTransfer'])
            ->name('transfer.process');
        
        // Usage
        Route::get('/usage', [KitchenStockController::class, 'usage'])->name('usage');
        Route::post('/usage', [KitchenStockController::class, 'processUsage'])->name('usage.process');
        Route::post('/usage-multiple', [KitchenStockController::class, 'processMultipleUsage'])
            ->name('usage.process-multiple');
        
        // Adjustment
        Route::get('/adjustment', [KitchenStockController::class, 'adjustment'])->name('adjustment');
        Route::post('/adjustment', [KitchenStockController::class, 'processAdjustment'])
            ->name('adjustment.process');
        
        // Transactions & Reports
        Route::get('/transactions', [KitchenStockController::class, 'transactions'])->name('transactions');
        Route::get('/report', [KitchenStockController::class, 'report'])->name('report');
        Route::get('/print-report', [KitchenStockController::class, 'printReport'])->name('print-report');
    });
    
    // ========================================
    // SUPER ADMIN ONLY ROUTES
    // ========================================
    Route::middleware('role:super_admin')->group(function () {
        // System settings (if exists)
        // Route::resource('system-settings', SystemSettingController::class);
        
        // Master data management (Super admin can manage all branches/warehouses)
        Route::prefix('master')->name('master.')->group(function () {
            // Add super admin only routes here if needed
        });
    });
});
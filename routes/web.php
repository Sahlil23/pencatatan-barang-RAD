<?php
// filepath: d:\xampp\htdocs\Chicking-BJM\routes\web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\beranda;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\KitchenStockController;
use App\Http\Controllers\CentralWarehouseController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\BranchWarehouseController;
use App\Http\Controllers\OutletWarehouseController;

// Auth Routes (Public - Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    // Quick login for development only
    if (app()->environment(['local', 'staging'])) {
        Route::get('/quick-login', [LoginController::class, 'quickLogin'])->name('quick-login');
    }
});

// Logout route (Available for authenticated users)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (Require Authentication)
Route::middleware('auth')->group(function () {
    Route::get('/beranda', [beranda::class, 'beranda'])->name('beranda');
    Route::get('/', [beranda::class, 'beranda'])->name('dashboard'); // Alias

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);

    // Items
    Route::get('items-low-stock', [ItemController::class, 'lowStock'])->name('items.low-stock');
    Route::post('items/{item}/adjust-stock', [ItemController::class, 'adjustStock'])->name('items.adjust-stock');
    Route::get('/items/report', [ItemController::class, 'report'])->name('items.report');
    Route::get('/items/print-report', [ItemController::class, 'printReport'])->name('items.print-report');
    Route::get('/items/compare-months', [ItemController::class, 'compareMonths'])->name('items.compare-months');
    Route::get('/items/{item}/monthly-history', [ItemController::class, 'monthlyHistory'])->name('items.monthly-history');
    Route::resource('items', ItemController::class);


    // Stock Transactions
    Route::get('stock-transactions-report', [StockTransactionController::class, 'report'])->name('stock-transactions.report');
    Route::get('/stock-transactions/print-report', [StockTransactionController::class, 'printReport'])->name('stock-transactions.print-report'); // Tambah route ini
    Route::resource('stock-transactions', StockTransactionController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/stock-transactions/store-multiple', [StockTransactionController::class, 'storeMultiple'])
         ->name('stock-transactions.store-multiple');

    // Profile routes
    Route::get('/profile', [LoginController::class, 'profile'])->name('profile');
    Route::put('/profile', [LoginController::class, 'updateProfile'])->name('profile.update');

    Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
    Route::get('/recipes/create', [RecipeController::class, 'create'])->name('recipes.create');
    Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');
    Route::get('/recipes/{slug}', [RecipeController::class, 'show'])->name('recipes.show');
    Route::get('/recipes/{recipe}/edit', [RecipeController::class, 'edit'])->name('recipes.edit');
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');
    Route::get('/backup', [App\Http\Controllers\BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup/create', [App\Http\Controllers\BackupController::class, 'create'])->name('backup.create');
    Route::get('/backup/download/{filename}', [App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
    Route::post('/backup/restore', [App\Http\Controllers\BackupController::class, 'restore'])->name('backup.restore');
    Route::delete('/backup/delete/{filename}', [App\Http\Controllers\BackupController::class, 'delete'])->name('backup.delete');


    Route::post('/items/send-low-stock-notification', [ItemController::class, 'sendLowStockNotification'])->name('items.send-low-stock-notification');
    Route::post('/items/schedule-daily-low-stock', [ItemController::class, 'scheduleDailyLowStock'])
         ->name('items.schedule-daily-low-stock');
    Route::post('/items/cancel-daily-low-stock-schedule', [ItemController::class, 'cancelDailyLowStockSchedule'])
         ->name('items.cancel-daily-low-stock-schedule');

    Route::prefix('kitchen')->name('kitchen.')->group(function () {
        Route::get('/', [KitchenStockController::class, 'index'])->name('index');
        
        Route::get('/transfer', [KitchenStockController::class, 'transfer'])->name('transfer');
        Route::post('/transfer', [KitchenStockController::class, 'processTransfer'])->name('transfer.process');
        
        Route::get('/usage', [KitchenStockController::class, 'usage'])->name('usage');
        Route::post('/usage', [KitchenStockController::class, 'processUsage'])->name('usage.process');
        Route::post('/usage-multiple', [KitchenStockController::class, 'processMultipleUsage'])->name('usage.process-multiple');
        
        Route::get('/adjustment', [KitchenStockController::class, 'adjustment'])->name('adjustment');
        Route::post('/adjustment', [KitchenStockController::class, 'processAdjustment'])->name('adjustment.process');
        
        Route::get('/transactions', [KitchenStockController::class, 'transactions'])->name('transactions');
        Route::get('/report', [KitchenStockController::class, 'report'])->name('report');
        Route::get('/print-report', [KitchenStockController::class, 'printReport'])->name('print-report');
    });

    Route::prefix('central-warehouse')->name('central-warehouse.')->group(function () {
        // Dashboard (READ)
        Route::get('/', [CentralWarehouseController::class, 'index'])->name('index');
        Route::get('/{balance}/show', [CentralWarehouseController::class, 'show'])->name('show');
        
        // Stock Receipt (CREATE)
        Route::get('/receive-stock', [CentralWarehouseController::class, 'receiveStock'])->name('receive-stock');
        Route::get('/receiveStock', [CentralWarehouseController::class, 'receiveStock'])->name('receiveStock'); // Alias untuk compatibility
        Route::post('/receive-stock', [CentralWarehouseController::class, 'storeReceipt'])->name('store-receipt');
        Route::post('/receive-stock', [CentralWarehouseController::class, 'storeReceipt'])->name('store-receipt');
        
        // Stock Adjustment (UPDATE)
        Route::get('/{balance}/adjust', [CentralWarehouseController::class, 'adjustStock'])->name('adjust-stock');
        Route::post('/{balance}/adjust', [CentralWarehouseController::class, 'storeAdjustment'])->name('store-adjustment');
        
        // Stock Distribution (CREATE)
        Route::get('/{balance}/distribute', [CentralWarehouseController::class, 'distributeStock'])->name('distribute-stock');
        Route::post('/{balance}/distribute', [CentralWarehouseController::class, 'storeDistribution'])->name('store-distribution');
        
        // Cancel Transaction (DELETE)
        Route::post('/cancel-transaction/{transaction}', [CentralWarehouseController::class, 'cancelTransaction'])->name('cancel-transaction');
        
        // AJAX Endpoints
        Route::get('/api/stock-balance', [CentralWarehouseController::class, 'getStockBalance'])->name('api.stock-balance');
        Route::get('/api/warehouse-items/{warehouse}', [CentralWarehouseController::class, 'getWarehouseItems'])->name('api.warehouse-items');
    });

    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        // Standard CRUD Operations
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('/create', [WarehouseController::class, 'create'])->name('create');
        Route::post('/', [WarehouseController::class, 'store'])->name('store');
        Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
        Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
        Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
        
        // Additional CRUD Actions
        Route::post('/{warehouse}/restore', [WarehouseController::class, 'restore'])->name('restore');
        Route::delete('/{warehouse}/force-delete', [WarehouseController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{warehouse}/change-status', [WarehouseController::class, 'changeStatus'])->name('change-status');
        
        // Bulk Operations
        Route::post('/bulk-action', [WarehouseController::class, 'bulkAction'])->name('bulk-action');
        
        // Data Export & Import
        Route::get('/export/csv', [WarehouseController::class, 'export'])->name('export');
        
        // AJAX Endpoints
        Route::get('/api/list', [WarehouseController::class, 'getWarehouses'])->name('api.list');
    });


        // Branch Warehouse - View Stock
    Route::get('/branch-warehouse', [BranchWarehouseController::class, 'index'])->name('branch-warehouse.index');
    Route::get('/branch-warehouse/{id}', [BranchWarehouseController::class, 'show'])->name('branch-warehouse.show');
    Route::get('/branch-warehouse/{id}/current-stock', [BranchWarehouseController::class, 'getCurrentStock'])->name('branch-warehouse.current-stock');

    // Branch Warehouse - Management Stock
    Route::get('/branch-warehouse/{id}/receive-stock', [BranchWarehouseController::class, 'showReceiveStockForm'])->name('branch-warehouse.receive-form');
    Route::post('/branch-warehouse/{id}/receive-stock', [BranchWarehouseController::class, 'storeReceiveStock'])->name('branch-warehouse.receive');

    Route::get('/branch-warehouse/{id}/adjust-stock', [BranchWarehouseController::class, 'showAdjustmentForm'])->name('branch-warehouse.adjust-form');
    Route::post('/branch-warehouse/{id}/adjust-stock', [BranchWarehouseController::class, 'storeAdjustment'])->name('branch-warehouse.adjust');

    // Branch Warehouse - Distribution
    Route::get('/branch-warehouse/{id}/distribute', [BranchWarehouseController::class, 'showDistributionForm'])->name('branch-warehouse.distribute-form');
    Route::post('/branch-warehouse/{id}/distribute', [BranchWarehouseController::class, 'storeDistribution'])->name('branch-warehouse.distribute');
    Route::get('/branch-warehouse/{id}/distributions', [BranchWarehouseController::class, 'distributionHistory'])->name('branch-warehouse.distributions');

    // Branch Warehouse - Reports
    Route::get('/branch-warehouse/{id}/transaction-summary', [BranchWarehouseController::class, 'getTransactionSummary'])->name('branch-warehouse.transaction-summary');
    Route::get('/branch-warehouse/{id}/export-report', [BranchWarehouseController::class, 'exportStockReport'])->name('branch-warehouse.export-report');


    Route::prefix('outlet-warehouse')->name('outlet-warehouse.')->middleware(['auth'])->group(function () {   
        // ============================================================
        // DASHBOARD
        // ============================================================
        Route::get('/', [OutletWarehouseController::class, 'index'])->name('index');
        
        // ============================================================
        // STOCK MANAGEMENT
        // ============================================================
        Route::prefix('stock')->name('stock.')->group(function () {
            Route::get('/', [OutletWarehouseController::class, 'show'])->name('index'); // ✅ FIXED: name('stock') → name('index')
            Route::get('/{warehouse}/{item}', [OutletWarehouseController::class, 'stockShow'])->name('show');
            
            // Receive
            Route::get('/{warehouse}/receive/create', [OutletWarehouseController::class, 'receiveCreate'])->name('receive.create');
            Route::post('/{warehouse}/receive', [OutletWarehouseController::class, 'receiveStore'])->name('receive.store');
            
            // Adjustment
            Route::get('/{warehouse}/adjustment/create', [OutletWarehouseController::class, 'adjustmentCreate'])->name('adjustment.create');
            Route::post('/{warehouse}/adjustment', [OutletWarehouseController::class, 'adjustmentStore'])->name('adjustment.store');
            
            // Transactions
            Route::get('/{warehouse}/transactions', [OutletWarehouseController::class, 'transactions'])->name('transactions');
            Route::get('/{warehouse}/transactions/{transaction}', [OutletWarehouseController::class, 'transactionDetail'])->name('transaction.detail');
        });
        
        // ============================================================
        // DISTRIBUTION TO KITCHEN
        // ============================================================
        Route::prefix('distribution')->name('distribution.')->group(function () {
            Route::get('/', [OutletWarehouseController::class, 'distributionIndex'])->name('index');
            Route::get('/create', [OutletWarehouseController::class, 'distributionCreate'])->name('create');
            Route::post('/', [OutletWarehouseController::class, 'distributionStore'])->name('store');
            Route::get('/{distribution}', [OutletWarehouseController::class, 'distributionShow'])->name('show');
            Route::post('/{distribution}/update-status', [OutletWarehouseController::class, 'distributionUpdateStatus'])->name('update-status');
        });
        
        // ============================================================
        // AJAX
        // ============================================================
        Route::get('/ajax/stock/{warehouse}', [OutletWarehouseController::class, 'getAvailableStock'])->name('ajax.stock');
        Route::get('/ajax/stock/{warehouse}/{item}', [OutletWarehouseController::class, 'getItemStockInfo'])->name('ajax.stock-info');
    });
});

Route::middleware(['auth'])->group(function () {
    // User Management Routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\beranda;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\Auth\LoginController;

// Dashboard
// Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', [beranda::class, 'beranda'])->name('beranda');
// Route::get('/dashboard/quick-stats', [DashboardController::class, 'quickStats'])->name('dashboard.quick-stats');

// Categories
Route::resource('categories', CategoryController::class);
Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

// Suppliers
Route::resource('suppliers', SupplierController::class);

// Items
Route::resource('items', ItemController::class);
Route::get('items-low-stock', [ItemController::class, 'lowStock'])->name('items.low-stock');
Route::post('items/{item}/adjust-stock', [ItemController::class, 'adjustStock'])->name('items.adjust-stock');

// Stock Transactions
Route::resource('stock-transactions', StockTransactionController::class)->except(['edit', 'update', 'destroy']);
Route::get('stock-transactions-report', [StockTransactionController::class, 'report'])->name('stock-transactions.report');

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Quick login for development
Route::get('/quick-login', [LoginController::class, 'quickLogin'])->name('quick-login');

// Profile routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [LoginController::class, 'profile'])->name('profile');
    Route::put('/profile', [LoginController::class, 'updateProfile'])->name('profile.update');
});

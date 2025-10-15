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
    Route::resource('items', ItemController::class);
    Route::get('items-low-stock', [ItemController::class, 'lowStock'])->name('items.low-stock');
    Route::post('items/{item}/adjust-stock', [ItemController::class, 'adjustStock'])->name('items.adjust-stock');

    // Stock Transactions
    Route::resource('stock-transactions', StockTransactionController::class)->except(['edit', 'update', 'destroy']);
    Route::get('stock-transactions-report', [StockTransactionController::class, 'report'])->name('stock-transactions.report');

    // Profile routes
    Route::get('/profile', [LoginController::class, 'profile'])->name('profile');
    Route::put('/profile', [LoginController::class, 'updateProfile'])->name('profile.update');
});

// User Management (Admin Only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
});
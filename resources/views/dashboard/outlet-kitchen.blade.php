
@extends('layouts.admin')

@section('title', 'Outlet & Kitchen Dashboard - Chicking BJM')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bx bx-store me-2"></i>
                        Outlet & Kitchen Dashboard
                    </h4>
                    <p class="text-muted mb-0">
                        <i class="bx bx-calendar me-1"></i>
                        {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}
                    </p>
                </div>
                <div class="mt-2 mt-md-0">
                    <button type="button" class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="bx bx-refresh me-1"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center">
                            <div>
                                <h4 class="text-white mb-1">{{ $warehouse->warehouse_name }}</h4>
                                <p class="mb-0 opacity-75">
                                    <i class="bx bx-building me-1"></i>
                                    {{ $warehouse->branch->branch_name ?? 'N/A' }}
                                    @if($warehouse->location)
                                    • {{ $warehouse->location }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-end mt-2 mt-md-0">
                            <span class="badge bg-white text-primary fs-6 px-3 py-2 mb-2 d-block">
                                Outlet Warehouse
                            </span>
                            <span class="badge bg-white bg-opacity-25 text-primary fs-6 px-3 py-2">
                                + Kitchen Stock
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(($lowStockItems['outlet']->count() > 0) || ($lowStockItems['kitchen']->count() > 0))
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h6 class="alert-heading mb-2">
                    <i class="bx bx-error-circle me-2"></i>
                    Stock Alert
                </h6>
                <ul class="mb-0">
                    @if($lowStockItems['outlet']->count() > 0)
                    <li>
                        <strong>{{ $lowStockItems['outlet']->count() }}</strong> items running low in <strong>Outlet Warehouse</strong>
                        <a href="{{ route('outlet-warehouse.show', $warehouse->id) }}?filter=low_stock" class="alert-link">View Details</a>
                    </li>
                    @endif
                    @if($lowStockItems['kitchen']->count() > 0)
                    <li>
                        <strong>{{ $lowStockItems['kitchen']->count() }}</strong> items running low in <strong>Kitchen Stock</strong>
                        <a href="{{ route('kitchen.index') }}?filter=low_stock" class="alert-link">View Details</a>
                    </li>
                    @endif
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Statistics Cards Row 1: Outlet Stock --}}
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="bx bx-store text-warning me-2"></i>
                Outlet Warehouse Stock
            </h5>
        </div>
        
        {{-- Outlet Total Items --}}
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card border-start border-warning border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Outlet Items</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">{{ number_format($outletStock['total_items']) }}</h3>
                            </div>
                            <small class="text-warning">
                                <i class="bx bx-package"></i>
                                Item Types
                            </small>
                        </div>
                        <span class="badge bg-label-warning rounded-pill p-3">
                            <i class="bx bx-package fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Outlet Total Quantity --}}
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card border-start border-warning border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Outlet Quantity</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">{{ number_format($outletStock['total_quantity'], 0) }}</h3>
                            </div>
                            <small class="text-warning">
                                <i class="bx bx-box"></i>
                                Total Units
                            </small>
                        </div>
                        <span class="badge bg-label-warning rounded-pill p-3">
                            <i class="bx bx-box fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Outlet Low Stock --}}
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card border-start border-warning border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Outlet Low Stock</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2 {{ $outletStock['low_stock_count'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($outletStock['low_stock_count']) }}
                                </h3>
                            </div>
                            <small class="{{ $outletStock['low_stock_count'] > 0 ? 'text-danger' : 'text-success' }}">
                                <i class="bx {{ $outletStock['low_stock_count'] > 0 ? 'bx-error' : 'bx-check-circle' }}"></i>
                                {{ $outletStock['low_stock_count'] > 0 ? 'Need Attention' : 'All Good' }}
                            </small>
                        </div>
                        <span class="badge {{ $outletStock['low_stock_count'] > 0 ? 'bg-label-danger' : 'bg-label-success' }} rounded-pill p-3">
                            <i class="bx {{ $outletStock['low_stock_count'] > 0 ? 'bx-error' : 'bx-check-circle' }} fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards Row 2: Kitchen Stock --}}
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="bx bx-restaurant text-success me-2"></i>
                Kitchen Stock
            </h5>
        </div>
        
        {{-- Kitchen Total Items --}}
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card border-start border-success border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Kitchen Items</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">{{ number_format($kitchenStock['total_items']) }}</h3>
                            </div>
                            <small class="text-success">
                                <i class="bx bx-package"></i>
                                Item Types
                            </small>
                        </div>
                        <span class="badge bg-label-success rounded-pill p-3">
                            <i class="bx bx-restaurant fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kitchen Total Quantity --}}
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card border-start border-success border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Kitchen Quantity</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">{{ number_format($kitchenStock['total_quantity'], 0) }}</h3>
                            </div>
                            <small class="text-success">
                                <i class="bx bx-box"></i>
                                Total Units
                            </small>
                        </div>
                        <span class="badge bg-label-success rounded-pill p-3">
                            <i class="bx bx-box fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kitchen Low Stock --}}
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card border-start border-success border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Kitchen Low Stock</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2 {{ $kitchenStock['low_stock_count'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($kitchenStock['low_stock_count']) }}
                                </h3>
                            </div>
                            <small class="{{ $kitchenStock['low_stock_count'] > 0 ? 'text-danger' : 'text-success' }}">
                                <i class="bx {{ $kitchenStock['low_stock_count'] > 0 ? 'bx-error' : 'bx-check-circle' }}"></i>
                                {{ $kitchenStock['low_stock_count'] > 0 ? 'Need Attention' : 'All Good' }}
                            </small>
                        </div>
                        <span class="badge {{ $kitchenStock['low_stock_count'] > 0 ? 'bg-label-danger' : 'bg-label-success' }} rounded-pill p-3">
                            <i class="bx {{ $kitchenStock['low_stock_count'] > 0 ? 'bx-error' : 'bx-check-circle' }} fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Activity --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-calendar-event me-2"></i>
                        Today's Activity
                    </h5>
                    <small class="text-muted">{{ \Carbon\Carbon::now()->format('d M Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Outlet Section --}}
                        <div class="col-lg-6 col-md-12 mb-4 mb-lg-0">
                            <div class="border-end pe-lg-4">
                                <h6 class="text-warning mb-3">
                                    <i class="bx bx-store me-1"></i>
                                    Outlet Warehouse
                                </h6>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar flex-shrink-0 me-3">
                                                <span class="avatar-initial rounded bg-label-success">
                                                    <i class="bx bx-import"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Received</small>
                                                <h6 class="mb-0">{{ number_format($todayActivity['outlet_received'], 2) }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar flex-shrink-0 me-3">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class="bx bx-export"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">To Kitchen</small>
                                                <h6 class="mb-0">{{ number_format($todayActivity['outlet_to_kitchen'], 2) }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kitchen Section --}}
                        <div class="col-lg-6 col-md-12">
                            <h6 class="text-success mb-3">
                                <i class="bx bx-restaurant me-1"></i>
                                Kitchen
                            </h6>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-success">
                                                <i class="bx bx-import"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Received</small>
                                            <h6 class="mb-0">{{ number_format($todayActivity['kitchen_received'], 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-dark">
                                                <i class="bx bx-food-menu"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Usage</small>
                                            <h6 class="mb-0">{{ number_format($todayActivity['kitchen_usage'], 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Total Transactions --}}
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-center py-3 border-top mt-3">
                                <div class="text-center">
                                    <small class="text-muted d-block mb-1">Total Transactions Today</small>
                                    <h4 class="mb-0 text-primary">{{ number_format($todayActivity['total_transactions']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Activity --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-calendar me-2"></i>
                        This Month Activity
                    </h5>
                    <small class="text-muted">{{ \Carbon\Carbon::now()->format('F Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Outlet Section --}}
                        <div class="col-lg-6 col-md-12 mb-4 mb-lg-0">
                            <div class="border-end pe-lg-4">
                                <h6 class="text-warning mb-3">
                                    <i class="bx bx-store me-1"></i>
                                    Outlet Warehouse
                                </h6>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar flex-shrink-0 me-3">
                                                <span class="avatar-initial rounded bg-label-success">
                                                    <i class="bx bx-import"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Received</small>
                                                <h6 class="mb-0">{{ number_format($monthActivity['outlet_received'], 2) }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar flex-shrink-0 me-3">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class="bx bx-export"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Out</small>
                                                <h6 class="mb-0">{{ number_format($monthActivity['outlet_to_kitchen'], 2) }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kitchen Section --}}
                        <div class="col-lg-6 col-md-12">
                            <h6 class="text-success mb-3">
                                <i class="bx bx-restaurant me-1"></i>
                                Kitchen
                            </h6>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-success">
                                                <i class="bx bx-import"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Received</small>
                                            <h6 class="mb-0">{{ number_format($monthActivity['kitchen_received'], 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-dark">
                                                <i class="bx bx-food-menu"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Usage</small>
                                            <h6 class="mb-0">{{ number_format($monthActivity['kitchen_usage'], 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock Movement Charts --}}
    <div class="row mb-4">
        {{-- Outlet Chart --}}
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-line-chart me-2"></i>
                        Outlet Stock Movement
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="outletStockChart" height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- Kitchen Chart --}}
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-line-chart me-2"></i>
                        Kitchen Stock Movement
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="kitchenStockChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Used Items --}}
    <div class="row mb-4">
        {{-- Top Outlet Items --}}
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-trending-up me-2 text-warning"></i>
                        Top Outlet Items
                    </h5>
                    <small class="text-muted">To Kitchen</small>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse($topUsedItems['outlet'] as $index => $transaction)
                        <li class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded 
                                        {{ $index == 0 ? 'bg-label-warning' : ($index == 1 ? 'bg-label-info' : 'bg-label-primary') }}">
                                        #{{ $index + 1 }}
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $transaction->item->item_name }}</h6>
                                    <small class="text-muted">
                                        {{ $transaction->item->category->category_name ?? 'No Category' }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-0">{{ number_format($transaction->total, 2) }}</h6>
                                    <small class="text-muted">{{ $transaction->item->unit }}</small>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="text-center py-4 text-muted">
                            <i class="bx bx-info-circle fs-4 d-block mb-2"></i>
                            No data available
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Top Kitchen Items --}}
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-trending-up me-2 text-success"></i>
                        Top Kitchen Usage
                    </h5>
                    <small class="text-muted">This Month</small>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse($topUsedItems['kitchen'] as $index => $transaction)
                        <li class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded 
                                        {{ $index == 0 ? 'bg-label-success' : ($index == 1 ? 'bg-label-info' : 'bg-label-primary') }}">
                                        #{{ $index + 1 }}
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $transaction->item->item_name }}</h6>
                                    <small class="text-muted">
                                        {{ $transaction->item->category->category_name ?? 'No Category' }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-0">{{ number_format($transaction->total, 2) }}</h6>
                                    <small class="text-muted">{{ $transaction->item->unit }}</small>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="text-center py-4 text-muted">
                            <i class="bx bx-info-circle fs-4 d-block mb-2"></i>
                            No data available
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="row mb-4">
        {{-- Outlet Transactions --}}
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-history me-2"></i>
                        Recent Outlet Transactions
                    </h5>
                    <a href="{{ route('outlet-warehouse.transactions', $warehouse->id) }}" class="btn btn-sm btn-outline-warning">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th class="text-end">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions['outlet'] as $transaction)
                                <tr>
                                    <td>
                                        <small>{{ $transaction->transaction_date->format('d/m H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ Str::limit($transaction->item->item_name, 20) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm {{ $transaction->transaction_type == 'IN' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $transaction->transaction_type }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($transaction->quantity, 2) }}</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No recent transactions
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kitchen Transactions --}}
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-history me-2"></i>
                        Recent Kitchen Transactions
                    </h5>
                    <a href="{{ route('kitchen.transactions') }}" class="btn btn-sm btn-outline-success">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th class="text-end">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions['kitchen'] as $transaction)
                                <tr>
                                    <td>
                                        <small>{{ $transaction->transaction_date->format('d/m H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ Str::limit($transaction->item->item_name, 20) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm {{ $transaction->transaction_type == 'IN' ? 'bg-success' : ($transaction->transaction_type == 'USAGE' ? 'bg-dark' : 'bg-warning') }}">
                                            {{ $transaction->transaction_type }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($transaction->quantity, 2) }}</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No recent transactions
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Outlet Actions --}}
                        <div class="col-12 mb-3">
                            <h6 class="text-warning">
                                <i class="bx bx-store me-1"></i>
                                Outlet Warehouse
                            </h6>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('outlet-warehouse.receive.create', $warehouse->id) }}" 
                               class="btn btn-outline-success w-100">
                                <i class="bx bx-import me-2"></i>
                                Receive Stock
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('outlet-warehouse.distribute.create', $warehouse->id) }}" 
                               class="btn btn-outline-primary w-100">
                                <i class="bx bx-export me-2"></i>
                                Transfer to Kitchen
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('outlet-warehouse.adjustment.create', $warehouse->id) }}" 
                               class="btn btn-outline-warning w-100">
                                <i class="bx bx-adjust me-2"></i>
                                Adjust Stock
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('outlet-warehouse.show', $warehouse->id) }}" 
                               class="btn btn-outline-info w-100">
                                <i class="bx bx-list-ul me-2"></i>
                                View Outlet Stock
                            </a>
                        </div>

                        {{-- Kitchen Actions --}}
                        <div class="col-12 mb-3 mt-3">
                            <h6 class="text-success">
                                <i class="bx bx-restaurant me-1"></i>
                                Kitchen
                            </h6>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('kitchen.transfer') }}" 
                               class="btn btn-outline-success w-100">
                                <i class="bx bx-transfer me-2"></i>
                                Transfer from Outlet
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('kitchen.usage') }}" 
                               class="btn btn-outline-dark w-100">
                                <i class="bx bx-food-menu me-2"></i>
                                Record Usage
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('kitchen.adjustment') }}" 
                               class="btn btn-outline-warning w-100">
                                <i class="bx bx-adjust me-2"></i>
                                Adjust Kitchen Stock
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('kitchen.index') }}" 
                               class="btn btn-outline-info w-100">
                                <i class="bx bx-list-ul me-2"></i>
                                View Kitchen Stock
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Outlet Stock Movement Chart
const outletCtx = document.getElementById('outletStockChart');
if (outletCtx) {
    new Chart(outletCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($outletChartData['dates']) !!},
            datasets: [
                {
                    label: 'Stock IN',
                    data: {!! json_encode($outletChartData['stockIn']) !!},
                    borderColor: 'rgb(113, 221, 55)',
                    backgroundColor: 'rgba(113, 221, 55, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Stock OUT (to kitchen)',
                    data: {!! json_encode($outletChartData['stockOut']) !!},
                    borderColor: 'rgb(255, 159, 67)',
                    backgroundColor: 'rgba(255, 159, 67, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Kitchen Stock Movement Chart
const kitchenCtx = document.getElementById('kitchenStockChart');
if (kitchenCtx) {
    new Chart(kitchenCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($kitchenChartData['dates']) !!},
            datasets: [
                {
                    label: 'Stock IN (from outlet)',
                    data: {!! json_encode($kitchenChartData['stockIn']) !!},
                    borderColor: 'rgb(113, 221, 55)',
                    backgroundColor: 'rgba(113, 221, 55, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Usage',
                    data: {!! json_encode($kitchenChartData['stockOut']) !!},
                    borderColor: 'rgb(133, 133, 133)',
                    backgroundColor: 'rgba(133, 133, 133, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Refresh Dashboard
function refreshDashboard() {
    location.reload();
}

// Auto refresh every 5 minutes
setInterval(function() {
    console.log('Auto refreshing dashboard data...');
    // Implement AJAX refresh if needed
}, 300000);
</script>
@endpush
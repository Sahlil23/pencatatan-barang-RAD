@extends('layouts.admin')

@section('title', 'Dashboard Super Admin - Chicking BJM')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bx bx-home-circle me-2"></i>
                        Super Admin Dashboard
                    </h4>
                    <p class="text-muted mb-0">
                        <i class="bx bx-calendar me-1"></i>
                        {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}
                    </p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="bx bx-refresh me-1"></i>
                        Refresh Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Total Branches</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">{{ $totalBranches }}</h3>
                            </div>
                            <small class="text-success">
                                <i class="bx bx-building"></i>
                                Active Locations
                            </small>
                        </div>
                        <span class="badge bg-label-primary rounded-pill p-3">
                            <i class="bx bx-building fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Total Warehouses</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">{{ $totalWarehouses }}</h3>
                            </div>
                            <small class="text-muted">
                                <span class="text-primary">{{ $centralWarehouses }}</span> Central •
                                <span class="text-info">{{ $branchWarehouses }}</span> Branch •
                                <span class="text-warning">{{ $outletWarehouses }}</span> Outlet
                            </small>
                        </div>
                        <span class="badge bg-label-info rounded-pill p-3">
                            <i class="bx bx-store fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Total Items</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">{{ $totalItems }}</h3>
                            </div>
                            <small class="text-muted">
                                {{ $totalCategories }} Categories • {{ $totalSuppliers }} Suppliers
                            </small>
                        </div>
                        <span class="badge bg-label-success rounded-pill p-3">
                            <i class="bx bx-package fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Total Users</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">{{ $totalUsers }}</h3>
                            </div>
                            <small class="text-muted">
                                <i class="bx bx-user"></i>
                                System Users
                            </small>
                        </div>
                        <span class="badge bg-label-warning rounded-pill p-3">
                            <i class="bx bx-user fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text text-muted mb-1">Central Stock</p>
                            <h4 class="mb-1">{{ number_format($totalCentralStock, 2) }}</h4>
                            <small class="text-primary">
                                <i class="bx bx-up-arrow-alt"></i>
                                Main Warehouse
                            </small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-primary rounded p-3">
                                <i class="bx bx-data fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text text-muted mb-1">Branch Stock</p>
                            <h4 class="mb-1">{{ number_format($totalBranchStock, 2) }}</h4>
                            <small class="text-info">
                                <i class="bx bx-trending-up"></i>
                                All Branches
                            </small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-info rounded p-3">
                                <i class="bx bx-box fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text text-muted mb-1">Outlet Stock</p>
                            <h4 class="mb-1">{{ number_format($totalOutletStock, 2) }}</h4>
                            <small class="text-warning">
                                <i class="bx bx-store-alt"></i>
                                All Outlets
                            </small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-warning rounded p-3">
                                <i class="bx bx-package fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text text-muted mb-1">Kitchen Stock</p>
                            <h4 class="mb-1">{{ number_format($totalKitchenStock, 2) }}</h4>
                            <small class="text-success">
                                <i class="bx bx-restaurant"></i>
                                All Kitchens
                            </small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-success rounded p-3">
                                <i class="bx bx-restaurant fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-calendar-event me-2"></i>
                        Today's Activity
                    </h5>
                    <small class="text-muted">{{ \Carbon\Carbon::now()->format('d M Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="bx bx-import"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Central IN</small>
                                    <h6 class="mb-0">{{ number_format($todayActivity['central_in'], 2) }}</h6>
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
                                    <small class="text-muted d-block">Central OUT</small>
                                    <h6 class="mb-0">{{ number_format($todayActivity['central_out'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="bx bx-import"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Branch IN</small>
                                    <h6 class="mb-0">{{ number_format($todayActivity['branch_in'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="bx bx-export"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Branch OUT</small>
                                    <h6 class="mb-0">{{ number_format($todayActivity['branch_out'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-import"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Outlet IN</small>
                                    <h6 class="mb-0">{{ number_format($todayActivity['outlet_in'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-dark">
                                        <i class="bx bx-restaurant"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Outlet Out</small>
                                    <h6 class="mb-0">{{ number_format($todayActivity['outlet_out'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-calendar me-2"></i>
                        This Month Activity
                    </h5>
                    <small class="text-muted">{{ \Carbon\Carbon::now()->format('F Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="bx bx-import"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Central IN</small>
                                    <h6 class="mb-0">{{ number_format($monthlyActivity['central_in'], 2) }}</h6>
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
                                    <small class="text-muted d-block">Central OUT</small>
                                    <h6 class="mb-0">{{ number_format($monthlyActivity['central_out'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="bx bx-import"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Branch IN</small>
                                    <h6 class="mb-0">{{ number_format($monthlyActivity['branch_in'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="bx bx-export"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Branch OUT</small>
                                    <h6 class="mb-0">{{ number_format($monthlyActivity['branch_out'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-import"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Outlet IN</small>
                                    <h6 class="mb-0">{{ number_format($monthlyActivity['outlet_in'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-dark">
                                        <i class="bx bx-restaurant"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Outlet Out</small>
                                    <h6 class="mb-0">{{ number_format($monthlyActivity['outlet_out'], 2) }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-line-chart me-2"></i>
                        Stock Movement This Month
                    </h5>
                    <!-- <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-filter me-1"></i>
                            Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="#">This Month</a></li>
                            <li><a class="dropdown-item" href="#">Last Month</a></li>
                        </ul>
                    </div> -->
                </div>
                <div class="card-body">
                    <canvas id="stockMovementChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
    <div class="col-12">
        <h5 class="text-muted">Sales Overview (All Outlets)</h5>
    </div>
    
    {{-- Sales Today --}}
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-start border-4 border-primary">
            <div class="card-body">
                <small class="text-muted fw-semibold">Total Sales Today</small>
                <h4 class="mb-0 text-primary">Rp {{ number_format($salesOverview['today']['total_sales'], 0, ',', '.') }}</h4>
                
                @php
                    $today = $salesOverview['today']['total_sales'];
                    $yesterday = $salesOverview['yesterday']['total_sales'];
                    $growth = $yesterday > 0 ? (($today - $yesterday) / $yesterday) * 100 : 0;
                    $icon = $growth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt';
                    $color = $growth >= 0 ? 'text-success' : 'text-danger';
                @endphp
                <small class="{{ $color }} mt-2 d-block">
                    <i class='bx {{ $icon }}'></i> {{ number_format(abs($growth), 1) }}% vs Yesterday
                </small>
            </div>
        </div>
    </div>

    {{-- MTD Sales --}}
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-start border-4 border-success">
            <div class="card-body">
                <small class="text-muted fw-semibold">MTD Sales (Bulan Ini)</small>
                <h4 class="mb-0 text-success">Rp {{ number_format($salesOverview['mtd']['total_sales'], 0, ',', '.') }}</h4>
                
                @php
                    $mtd = $salesOverview['mtd']['total_sales'];
                    $lastMonth = $salesOverview['last_month']['total_sales'];
                    // Estimasi growth bulan berjalan vs bulan lalu (simple comparison)
                    $growthMtd = $lastMonth > 0 ? (($mtd - $lastMonth) / $lastMonth) * 100 : 0;
                    $iconMtd = $growthMtd >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt';
                    $colorMtd = $growthMtd >= 0 ? 'text-success' : 'text-danger';
                @endphp
                <small class="{{ $colorMtd }} mt-2 d-block">
                    <i class='bx {{ $iconMtd }}'></i> {{ number_format(abs($growthMtd), 1) }}% vs Last Month
                </small>
            </div>
        </div>
    </div>

    {{-- Reporting Outlets Count --}}
    <div class="col-md-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted fw-semibold">Outlet Reporting Today</small>
                <h4 class="mb-0">{{ $salesOverview['today']['report_count'] }} <span class="fs-6 text-muted">/ {{ $outletWarehouses }}</span></h4>
                <small class="text-muted mt-2 d-block">Outlets have submitted reports</small>
            </div>
        </div>
    </div>

    {{-- Total Guests --}}
    <div class="col-md-3 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted fw-semibold">Total Guests (Today)</small>
                <h4 class="mb-0">{{ number_format($salesOverview['today']['guest_count']) }}</h4>
                <small class="text-muted mt-2 d-block">Customers served across all outlets</small>
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">Top Outlets (Bulan Ini)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th>Outlet</th>
                            <th class="text-end">Total Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesOverview['top_outlets'] as $index => $top)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-primary me-2">{{ $index + 1 }}</span>
                                    <span class="fw-semibold">{{ $top->outletWarehouse->warehouse_name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-end fw-bold">
                                Rp {{ number_format($top->total_omzet, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- ... Kolom lain (misal grafik trend) ... --}}
</div>
    <!-- <div class="row mb-4">
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-trophy me-2"></i>
                        Top Performing Branches
                    </h5>
                    <small class="text-muted">This Month</small>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse($topBranches as $index => $branch)
                        <li class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded 
                                        {{ $index == 0 ? 'bg-label-warning' : ($index == 1 ? 'bg-label-info' : 'bg-label-primary') }}">
                                        <i class="bx bx-building"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $branch->warehouse_name }}</h6>
                                    <small class="text-muted">{{ $branch->branch_stock_transactions_count }} transactions</small>
                                </div>
                                <div class="badge {{ $index == 0 ? 'bg-warning' : ($index == 1 ? 'bg-info' : 'bg-primary') }}">
                                    #{{ $index + 1 }}
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="text-center text-muted py-4">
                            <i class="bx bx-info-circle fs-4 d-block mb-2"></i>
                            No data available
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Pending Actions --}}
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-time-five me-2"></i>
                        Pending Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @if($pendingDistributions > 0)
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="bx bx-time-five"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Pending Distributions</h6>
                                    <small class="text-muted">Awaiting approval</small>
                                </div>
                            </div>
                            <span class="badge bg-warning rounded-pill">{{ $pendingDistributions }}</span>
                        </a>
                        @endif

                        @if($lowStockItems > 0)
                        <a href="{{ route('items.index') }}?filter=low_stock" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-danger">
                                        <i class="bx bx-error"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Low Stock Items</h6>
                                    <small class="text-muted">Need restock</small>
                                </div>
                            </div>
                            <span class="badge bg-danger rounded-pill">{{ $lowStockItems }}</span>
                        </a>
                        @endif

                        @if($alerts['out_of_stock'] > 0)
                        <a href="{{ route('items.index') }}?filter=out_of_stock" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-dark">
                                        <i class="bx bx-x-circle"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Out of Stock</h6>
                                    <small class="text-muted">Critical</small>
                                </div>
                            </div>
                            <span class="badge bg-dark rounded-pill">{{ $alerts['out_of_stock'] }}</span>
                        </a>
                        @endif

                        @if($pendingDistributions == 0 && $lowStockItems == 0 && $alerts['out_of_stock'] == 0)
                        <div class="text-center py-5 text-muted">
                            <i class="bx bx-check-circle fs-1 d-block mb-2 text-success"></i>
                            <p class="mb-0">All systems running smoothly!</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-link me-2"></i>
                        Quick Links
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('central-warehouse.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bx bx-data me-2"></i>
                                Central Warehouses
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('branch-warehouse.index') }}" class="btn btn-outline-info w-100">
                                <i class="bx bx-building me-2"></i>
                                Branch Warehouses
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('outlet-warehouse.index') }}" class="btn btn-outline-warning w-100">
                                <i class="bx bx-store me-2"></i>
                                Outlet Warehouses
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('items.index') }}" class="btn btn-outline-success w-100">
                                <i class="bx bx-package me-2"></i>
                                Items Management
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="bx bx-user me-2"></i>
                                User Management
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('warehouses.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bx bx-buildings me-2"></i>
                                Warehouses
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-info w-100">
                                <i class="bx bx-package me-2"></i>
                                Suppliers
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-warning w-100">
                                <i class="bx bx-category me-2"></i>
                                Categories
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
// Stock Movement Chart
const ctx = document.getElementById('stockMovementChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($stockMovementChart['dates']) !!},
            datasets: [
                {
                    label: 'Central IN',
                    data: {!! json_encode($stockMovementChart['centralIn']) !!},
                    borderColor: 'rgb(105, 108, 255)',
                    backgroundColor: 'rgba(105, 108, 255, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Central OUT',
                    data: {!! json_encode($stockMovementChart['centralOut']) !!},
                    borderColor: 'rgb(255, 77, 79)',
                    backgroundColor: 'rgba(255, 77, 79, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Branch IN',
                    data: {!! json_encode($stockMovementChart['branchIn']) !!},
                    borderColor: 'rgb(3, 195, 236)',
                    backgroundColor: 'rgba(3, 195, 236, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Branch OUT',
                    data: {!! json_encode($stockMovementChart['branchOut']) !!},
                    borderColor: 'rgb(255, 159, 67)',
                    backgroundColor: 'rgba(255, 159, 67, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Outlet IN',
                    data: {!! json_encode($stockMovementChart['outletIn']) !!},
                    borderColor: 'rgb(113, 221, 55)',
                    backgroundColor: 'rgba(113, 221, 55, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Outlet Out',
                    data: {!! json_encode($stockMovementChart['outletOut']) !!},
                    borderColor: 'rgba(210, 221, 55, 1)',
                    backgroundColor: 'rgba(113, 221, 55, 0.1)',
                    tension: 0.4
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
                title: {
                    display: false
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
    // You can implement AJAX refresh here
}, 300000);
</script>
@endpush
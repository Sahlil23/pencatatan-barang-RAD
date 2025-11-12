@extends('layouts.admin')

@section('title', 'Central Warehouse Dashboard - Chicking BJM')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bx bx-data me-2"></i>
                        Central Warehouse Dashboard
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
    </div> -->

    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div>
                                <h4 class="text-white mb-1">{{ $warehouse->warehouse_name }}</h4>
                                <p class="mb-0 opacity-75">
                                    <i class="bx bx-map me-1"></i>
                                    {{ $warehouse->location ?? 'Central Location' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-white text-primary fs-6 px-3 py-2">
                                Central Warehouse
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($currentStock['low_stock_count']) && $currentStock['low_stock_count'] > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h6 class="alert-heading mb-2">
                    <i class="bx bx-error-circle me-2"></i>
                    Stock Alert
                </h6>
                <p class="mb-0">
                    <strong>{{ $currentStock['low_stock_count'] }}</strong> items are running low on stock in your warehouse.
                    <a href="{{ route('central-warehouse.show', $warehouse->id) }}?filter=low_stock" class="alert-link">View Low Stock Items</a>
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Pending Distributions Alert --}}
    @if($pendingDistributions->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <h6 class="alert-heading mb-2">
                    <i class="bx bx-time-five me-2"></i>
                    Pending Distributions
                </h6>
                <p class="mb-0">
                    You have <strong>{{ $pendingDistributions->count() }}</strong> pending distribution(s) to branch warehouses.
                    <a href="#pendingDistributionsSection" class="alert-link">View Details Below</a>
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Statistics Cards Row 1: Current Stock Overview --}}
    <div class="row mb-4">
        {{-- Total Items --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Total Items</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">{{ number_format($currentStock['total_items']) }}</h3>
                            </div>
                            <small class="text-primary">
                                <i class="bx bx-package"></i>
                                In Stock
                            </small>
                        </div>
                        <span class="badge bg-label-primary rounded-pill p-3">
                            <i class="bx bx-package fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Stock Quantity --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Total Quantity</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">{{ number_format($currentStock['total_quantity'], 0) }}</h3>
                            </div>
                            <small class="text-info">
                                <i class="bx bx-box"></i>
                                Units
                            </small>
                        </div>
                        <span class="badge bg-label-info rounded-pill p-3">
                            <i class="bx bx-box fs-3"></i>
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
                            <span class="text-muted d-block mb-1">Total Value</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2">Rp {{ number_format($currentStock['total_value'], 0) }}</h3>
                            </div>
                            <small class="text-success">
                                <i class="bx bx-trending-up"></i>
                                Stock Value
                            </small>
                        </div>
                        <span class="badge bg-label-success rounded-pill p-3">
                            <i class="bx bx-dollar fs-3"></i>
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
                            <span class="text-muted d-block mb-1">Low Stock Items</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2 {{ $currentStock['low_stock_count'] > 0 ? 'text-warning' : 'text-success' }}">
                                    {{ number_format($currentStock['low_stock_count']) }}
                                </h3>
                            </div>
                            <small class="{{ $currentStock['low_stock_count'] > 0 ? 'text-warning' : 'text-success' }}">
                                <i class="bx {{ $currentStock['low_stock_count'] > 0 ? 'bx-error' : 'bx-check-circle' }}"></i>
                                {{ $currentStock['low_stock_count'] > 0 ? 'Need Attention' : 'All Good' }}
                            </small>
                        </div>
                        <span class="badge {{ $currentStock['low_stock_count'] > 0 ? 'bg-label-warning' : 'bg-label-success' }} rounded-pill p-3">
                            <i class="bx {{ $currentStock['low_stock_count'] > 0 ? 'bx-error' : 'bx-check-circle' }} fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Activity & Monthly Activity --}}
    <div class="row mb-4">
        {{-- Today's Activity --}}
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-calendar-event me-2"></i>
                        Today's Activity
                    </h5>
                    <small class="text-muted">{{ \Carbon\Carbon::now()->format('d M Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-import"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Received</small>
                                    <h5 class="mb-0">{{ number_format($todayActivity['received']) }}</h5>
                                    <small class="text-muted">From Suppliers</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-danger">
                                        <i class="bx bx-export"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Distributed</small>
                                    <h5 class="mb-0">{{ number_format($todayActivity['distributed'], 2) }}</h5>
                                    <small class="text-muted">To Branches</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-center py-3 border-top">
                                <div class="text-center">
                                    <small class="text-muted d-block mb-1">Total Transactions Today</small>
                                    <h4 class="mb-0 text-primary">{{ number_format($todayActivity['transactions_count']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-calendar me-2"></i>
                        This Month Activity
                    </h5>
                    <small class="text-muted">{{ \Carbon\Carbon::now()->format('F Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-import"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Received</small>
                                    <h5 class="mb-0">{{ number_format($monthActivity['received'], 2) }}</h5>
                                    <small class="text-muted">From Suppliers</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-danger">
                                        <i class="bx bx-export"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Distributed</small>
                                    <h5 class="mb-0">{{ number_format($monthActivity['distributed'], 2) }}</h5>
                                    <small class="text-muted">To Branches</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-center py-3 border-top">
                                <div class="text-center">
                                    <small class="text-muted d-block mb-1">Total Distributions</small>
                                    <h4 class="mb-0 text-info">{{ number_format($monthActivity['distributions_count']) }}</h4>
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
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-filter me-1"></i>
                            Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="#">This Month</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="stockMovementChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100" id="pendingDistributionsSection">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-time-five me-2"></i>
                        Pending Distributions
                    </h5>
                    <span class="badge bg-warning">{{ $pendingDistributions->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($pendingDistributions->take(5) as $distribution)
                    <div class="d-flex align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-building"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $distribution->branchWarehouse->warehouse_name ?? 'N/A' }}</h6>
                            <small class="text-muted">
                                {{ $distribution->item->item_name }} - 
                                <strong>{{ number_format($distribution->quantity, 2) }} {{ $distribution->item->unit }}</strong>
                            </small>
                            <br>
                            <small class="text-muted">
                                <i class="bx bx-calendar me-1"></i>
                                {{ $distribution->transaction_date->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div>
                            <span class="badge bg-warning">PENDING</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bx bx-check-circle fs-1 d-block mb-2 text-success"></i>
                        <p class="mb-0">No pending distributions</p>
                    </div>
                    @endforelse

                    @if($pendingDistributions->count() > 5)
                    <div class="text-center pt-3 border-top">
                        <a href="{{ route('central-warehouse.transactions'); }}" class="btn btn-sm btn-outline-warning">
                            View All {{ $pendingDistributions->count() }} Distributions
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-trending-up me-2"></i>
                        Top Distributed Items
                    </h5>
                    <small class="text-muted">This Month</small>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse($topDistributedItems as $index => $transaction)
                        <li class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded 
                                        {{ $index == 0 ? 'bg-label-warning' : ($index == 1 ? 'bg-label-info' : 'bg-label-primary') }}">
                                        <i class="bx bx-package"></i>
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
                            No distribution data available
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        {{-- Distribution Status --}}
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-doughnut-chart me-2"></i>
                        Distribution Status
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="distributionStatusChart"></canvas>
                    
                    <div class="mt-4">
                        @foreach($distributionStatus as $status)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge 
                                    {{ $status->status == 'PENDING' ? 'bg-warning' : 
                                       ($status->status == 'APPROVED' ? 'bg-success' : 'bg-danger') }} 
                                    me-2">
                                </span>
                                <span>{{ $status->status }}</span>
                            </div>
                            <strong>{{ $status->count }}</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-history me-2"></i>
                        Recent Transactions
                    </h5>
                    <a href="{{ route('central-warehouse.transactions', $warehouse->id) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Item</th>
                                    <th class="text-end">Quantity</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td>
                                        <small>{{ $transaction->transaction_date->format('d/m/Y') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $transaction->transaction_date->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $transaction->transaction_type == 'SUPPLIER_IN' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $transaction->transaction_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $transaction->item->item_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $transaction->item->sku }}</small>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($transaction->quantity, 2) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $transaction->item->unit }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $transaction->user->username ?? 'System' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
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
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('central-warehouse.receive-stock', $warehouse->id) }}" class="btn btn-outline-success w-100">
                                <i class="bx bx-import me-2"></i>
                                Receive Stock
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('central-warehouse.distribute-stock') }}" class="btn btn-outline-primary w-100">
                                <i class="bx bx-export me-2"></i>
                                Distribute to Branch
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('central-warehouse.show', $warehouse->id) }}" class="btn btn-outline-info w-100">
                                <i class="bx bx-list-ul me-2"></i>
                                View Stock
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('central-warehouse.transactions', $warehouse->id) }}" class="btn btn-outline-secondary w-100">
                                <i class="bx bx-history me-2"></i>
                                View Transactions
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
                    label: 'Stock IN (from suppliers)',
                    data: {!! json_encode($stockMovementChart['stockIn']) !!},
                    borderColor: 'rgb(113, 221, 55)',
                    backgroundColor: 'rgba(113, 221, 55, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Stock OUT (to branches)',
                    data: {!! json_encode($stockMovementChart['stockOut']) !!},
                    borderColor: 'rgb(255, 77, 79)',
                    backgroundColor: 'rgba(255, 77, 79, 0.1)',
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

// Distribution Status Chart
const statusCtx = document.getElementById('distributionStatusChart');
if (statusCtx) {
    const statusData = {!! json_encode($distributionStatus) !!};
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => item.status),
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: [
                    'rgba(255, 208, 0, 1)', // PENDING - Orange
                    'rgba(25, 0, 255, 1)', // APPROVED - Green
                    'rgba(255, 0, 191, 1)',  // REJECTED - Red
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
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
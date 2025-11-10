<!-- -- Active: 1746074663200@@127.0.0.1@3306 -->
@extends('layouts.admin')

@section('title', 'Kitchen Stock - Chicking BJM')

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('beranda') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Kitchen Stock</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-2 d-block">Total Items</span>
                        <h4 class="mb-2">{{ number_format($stats['total_items']) }}</h4>
                        <small class="text-muted">In kitchen</small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-package fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-2 d-block">Available Stock</span>
                        <h4 class="mb-2 text-success">{{ number_format($stats['available_items']) }}</h4>
                        <small class="text-success">Ready to use</small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-check-circle fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-2 d-block">Low Stock</span>
                        <h4 class="mb-2 text-warning">{{ number_format($stats['low_stock_items']) }}</h4>
                        <small class="text-warning">Need attention</small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-error fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-2 d-block">Out of Stock</span>
                        <h4 class="mb-2 text-danger">{{ number_format($stats['out_of_stock_items']) }}</h4>
                        <small class="text-danger">Urgent</small>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="bx bx-x-circle fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            <i class="bx bx-food-menu me-2"></i>
                            Kitchen Stock List
                        </h5>
                    </div>
                    <div class="col-auto">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('kitchen.receive.create') }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-download me-1"></i> Receive Stock
                            </a>
                            <a href="{{ route('kitchen.usage.create') }}" class="btn btn-danger btn-sm">
                                <i class="bx bx-minus me-1"></i> Record Usage
                            </a>
                            <a href="{{ route('kitchen.adjustment.create') }}" class="btn btn-warning btn-sm">
                                <i class="bx bx-edit me-1"></i> Adjustment
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Filters -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Item name or SKU...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock Status</label>
                        <select class="form-select" name="stock_status">
                            <option value="">All Status</option>
                            <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>
                                Available Stock
                            </option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>
                                Low Stock
                            </option>
                            <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>
                                Out of Stock
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bx bx-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('kitchen.index') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh"></i>
                        </a>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th class="text-end">Opening</th>
                                <th class="text-end">IN</th>
                                <th class="text-end">OUT</th>
                                <th class="text-end">Closing</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockBalances as $balance)
                                @php
                                    $closingStock = $balance->closing_stock ?? 0;
                                    $threshold = $balance->item->low_stock_threshold ?? 10;
                                    
                                    if ($closingStock <= 0) {
                                        $status = 'Out of Stock';
                                        $statusClass = 'danger';
                                    } elseif ($closingStock <= $threshold) {
                                        $status = 'Low Stock';
                                        $statusClass = 'warning';
                                    } else {
                                        $status = 'Available';
                                        $statusClass = 'success';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar flex-shrink-0 me-3">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="bx bx-package"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <strong>{{ $balance->item->item_name ?? '-' }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bx bx-barcode me-1"></i>
                                                    {{ $balance->item->sku ?? '-' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">
                                            {{ $balance->item->category->category_name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-secondary">
                                            {{ $balance->item->unit ?? 'Unit' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($balance->opening_stock ?? 0, 3) }}
                                    </td>
                                    <td class="text-end text-success">
                                        +{{ number_format($balance->total_in ?? 0, 3) }}
                                    </td>
                                    <td class="text-end text-danger">
                                        -{{ number_format($balance->total_out ?? 0, 3) }}
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-{{ $statusClass }}">
                                            {{ number_format($closingStock, 3) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ $status }}
                                        </span>
                                        @if($closingStock <= $threshold && $closingStock > 0)
                                            <br>
                                            <small class="text-muted">Min: {{ number_format($threshold, 3) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" 
                                                   href="{{ route('kitchen.receive.create') }}?item_id={{ $balance->item_id }}">
                                                    <i class="bx bx-download me-2"></i>
                                                    Receive Stock
                                                </a>
                                                @if($closingStock > 0)
                                                <a class="dropdown-item" 
                                                   href="{{ route('kitchen.usage.create') }}?item_id={{ $balance->item_id }}">
                                                    <i class="bx bx-minus me-2"></i>
                                                    Record Usage
                                                </a>
                                                @endif
                                                <a class="dropdown-item" 
                                                   href="{{ route('kitchen.adjustment.create') }}?item_id={{ $balance->item_id }}">
                                                    <i class="bx bx-edit me-2"></i>
                                                    Adjustment
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" 
                                                   href="{{ route('kitchen.transactions') }}?item_id={{ $balance->item_id }}">
                                                    <i class="bx bx-history me-2"></i>
                                                    View History
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bx bx-package display-4 d-block mb-3 text-secondary"></i>
                                            <h5>No kitchen stock data</h5>
                                            <p class="mb-3">Start by receiving stock from warehouse</p>
                                            <a href="{{ route('kitchen.receive.create') }}" class="btn btn-primary">
                                                <i class="bx bx-plus me-1"></i> Receive First Stock
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($stockBalances->isNotEmpty())
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                                <td class="text-end">
                                    <strong>{{ number_format($stockBalances->sum('opening_stock'), 3) }}</strong>
                                </td>
                                <td class="text-end text-success">
                                    <strong>+{{ number_format($stockBalances->sum('total_in'), 3) }}</strong>
                                </td>
                                <td class="text-end text-danger">
                                    <strong>-{{ number_format($stockBalances->sum('total_out'), 3) }}</strong>
                                </td>
                                <td class="text-end">
                                    <strong>{{ number_format($stockBalances->sum('closing_stock'), 3) }}</strong>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $stockBalances->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-history me-2"></i>
                    Recent Transactions
                </h5>
                <a href="{{ route('kitchen.transactions') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if($recentTransactions->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bx bx-time display-6 d-block mb-2"></i>
                        No recent transactions
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Item</th>
                                    <th class="text-end">Quantity</th>
                                    <th>User</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $trx)
                                <tr>
                                    <td>
                                        <small>
                                            {{ $trx->transaction_date->format('d M Y') }}
                                            <br>
                                            <span class="text-muted">{{ $trx->transaction_date->format('H:i') }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        @php
                                            $typeConfig = [
                                                'RECEIVE_FROM_WAREHOUSE' => ['success', 'Receive'],
                                                'RECEIVE_FROM_OUTLET_WAREHOUSE' => ['success', 'Receive Outlet'],
                                                'USAGE_PRODUCTION' => ['danger', 'Usage'],
                                                'USAGE_COOKING' => ['danger', 'Cooking'],
                                                'USAGE_PREPARATION' => ['danger', 'Preparation'],
                                                'ADJUSTMENT' => ['warning', 'Adjustment'],
                                            ];
                                            $config = $typeConfig[$trx->transaction_type] ?? ['secondary', 'Other'];
                                        @endphp
                                        <span class="badge bg-{{ $config[0] }}">
                                            {{ $config[1] }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $trx->item->item_name ?? '-' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $trx->item->sku ?? '-' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="{{ $trx->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $trx->quantity >= 0 ? '+' : '' }}{{ number_format($trx->quantity, 3) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $trx->user->name ?? 'System' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($trx->notes, 40) }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-primary">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('kitchen.receive.create') }}" 
                           class="btn btn-white w-100 d-flex flex-column align-items-center py-3">
                            <i class="bx bx-download display-6 mb-2"></i>
                            <span class="fw-semibold">Receive Stock</span>
                            <small class="text-muted">From warehouse</small>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('kitchen.usage.create') }}" 
                           class="btn btn-white w-100 d-flex flex-column align-items-center py-3">
                            <i class="bx bx-minus-circle display-6 mb-2"></i>
                            <span class="fw-semibold">Record Usage</span>
                            <small class="text-muted">Daily consumption</small>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('kitchen.transactions') }}" 
                           class="btn btn-white w-100 d-flex flex-column align-items-center py-3">
                            <i class="bx bx-list-ul display-6 mb-2"></i>
                            <span class="fw-semibold">Transactions</span>
                            <small class="text-muted">View history</small>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('kitchen.report') }}" 
                           class="btn btn-white w-100 d-flex flex-column align-items-center py-3">
                            <i class="bx bx-bar-chart display-6 mb-2"></i>
                            <span class="fw-semibold">Monthly Report</span>
                            <small class="text-muted">Stock analysis</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Alert for low/out of stock
    @if($stats['out_of_stock_items'] > 0)
        Swal.fire({
            icon: 'error',
            title: 'Out of Stock Alert!',
            html: '<strong>{{ $stats["out_of_stock_items"] }}</strong> items are out of stock in kitchen.<br>Please receive stock from warehouse immediately.',
            confirmButtonText: 'Receive Now',
            showCancelButton: true,
            cancelButtonText: 'Later'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("kitchen.receive.create") }}';
            }
        });
    @elseif($stats['low_stock_items'] > 0)
        Swal.fire({
            icon: 'warning',
            title: 'Low Stock Warning!',
            html: '<strong>{{ $stats["low_stock_items"] }}</strong> items are running low in kitchen.<br>Consider receiving stock from warehouse.',
            confirmButtonText: 'Receive Now',
            showCancelButton: true,
            cancelButtonText: 'Later'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("kitchen.receive.create") }}';
            }
        });
    @endif
});
</script>
@endpush

@push('styles')
<style>
.avatar {
    width: 40px;
    height: 40px;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    border-radius: 0.375rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(67, 89, 113, 0.05);
}

.btn-white {
    background-color: white;
    color: #435971;
}

.btn-white:hover {
    background-color: #f8f9fa;
    color: #435971;
}

.card-h-100 {
    height: calc(100% - 1rem);
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endpush
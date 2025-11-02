@extends('layouts.admin')

@section('title', 'Central Warehouse Dashboard - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Central Warehouse</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="Total Items" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Items</span>
        <h3 class="card-title mb-2">{{ number_format($totalItems) }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-package"></i> Central Stock
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Total Stock" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Stock</span>
        <h3 class="card-title mb-2">{{ number_format($totalStock, 0) }}</h3>
        <small class="text-info fw-semibold">
          <i class="bx bx-box"></i> Units
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-warning.png') }}" alt="Low Stock" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Low Stock Items</span>
        <h3 class="card-title mb-2 {{ $lowStockItems > 0 ? 'text-warning' : 'text-success' }}">{{ $lowStockItems }}</h3>
        <small class="{{ $lowStockItems > 0 ? 'text-warning' : 'text-success' }} fw-semibold">
          <i class="bx {{ $lowStockItems > 0 ? 'bx-error' : 'bx-check' }}"></i> 
          {{ $lowStockItems > 0 ? 'Needs Attention' : 'All Good' }}
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Warehouses" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Central Warehouses</span>
        <h3 class="card-title mb-2">{{ $warehouses->count() }}</h3>
        <small class="text-info fw-semibold">
          <i class="bx bx-buildings"></i> Active
        </small>
      </div>
    </div>
  </div>
</div>

<!-- Central Warehouse System Info -->
<div class="alert alert-info alert-dismissible mb-4" role="alert">
  <h6 class="alert-heading mb-2">
    <i class="bx bx-info-circle me-2"></i>
    Central Warehouse Management System
  </h6>
  <p class="mb-0">
    <strong>Current Period:</strong> {{ now()->format('F Y') }} |
    <strong>Operations:</strong> Stock Receipt, Adjustment, Distribution |
    <strong>Status:</strong> Real-time inventory tracking
  </p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-zap me-2"></i>
          Quick Actions
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <a href="{{ route('central-warehouse.receive-stock') }}" class="btn btn-primary d-flex align-items-center w-100">
              <i class="bx bx-package me-2"></i>
              <div class="text-start">
                <div class="fw-semibold">Receive Stock</div>
                <small>From Suppliers</small>
              </div>
            </a>
          </div>
          <div class="col-md-3">
            <button type="button" class="btn btn-warning d-flex align-items-center w-100" onclick="showBulkAdjustment()">
              <i class="bx bx-edit-alt me-2"></i>
              <div class="text-start">
                <div class="fw-semibold">Bulk Adjustment</div>
                <small>Multiple Items</small>
              </div>
            </button>
          </div>
          <div class="col-md-3">
            <button type="button" class="btn btn-success d-flex align-items-center w-100" onclick="showBulkDistribution()">
              <i class="bx bx-share me-2"></i>
              <div class="text-start">
                <div class="fw-semibold">Bulk Distribution</div>
                <small>To Branches</small>
              </div>
            </button>
          </div>
          <div class="col-md-3">
            <button type="button" class="btn btn-info d-flex align-items-center w-100" onclick="showStockReport()">
              <i class="bx bx-chart me-2"></i>
              <div class="text-start">
                <div class="fw-semibold">Stock Report</div>
                <small>Current Status</small>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('central-warehouse.index') }}" class="row g-3" id="filterForm">
      <!-- Search -->
      <div class="col-md-4">
        <label class="form-label">Search Items</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Item name or SKU...">
        </div>
      </div>
      
      <!-- Warehouse Filter -->
      <div class="col-md-3">
        <label class="form-label">Central Warehouse</label>
        <select class="form-select" name="warehouse_id">
          <option value="">All Warehouses</option>
          @foreach($warehouses as $warehouse)
          <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
            {{ $warehouse->warehouse_name }}
          </option>
          @endforeach
        </select>
      </div>
      
      <!-- Category Filter -->
      <div class="col-md-3">
        <label class="form-label">Category</label>
        <select class="form-select" name="category_id">
          <option value="">All Categories</option>
          @foreach($items->pluck('category')->filter()->unique('id') as $category)
          <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
            {{ $category->category_name }}
          </option>
          @endforeach
        </select>
      </div>
      
      <!-- Filter Actions -->
      <div class="col-md-2">
        <label class="form-label">&nbsp;</label>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill">
            <i class="bx bx-filter me-1"></i>
            Filter
          </button>
          <a href="{{ route('central-warehouse.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-reset"></i>
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Main Stock Table -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bx bx-package me-2"></i>
      Central Stock Inventory - {{ now()->format('F Y') }}
      @if(request()->hasAny(['search', 'warehouse_id', 'category_id']))
        <span class="badge bg-label-primary">Filtered</span>
      @endif
    </h5>
    <div class="d-flex gap-2">
      <span class="badge bg-label-info">Total: {{ $stockBalances->total() }} items</span>
    </div>
  </div>
  
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th style="width: 80px;">
            <i class="bx bx-hash me-1"></i>
            SKU
          </th>
          <th>
            <i class="bx bx-package me-1"></i>
            Item
          </th>
          <th>
            <i class="bx bx-buildings me-1"></i>
            Warehouse
          </th>
          <th>
            <i class="bx bx-category me-1"></i>
            Category
          </th>
          <th class="text-center">
            <i class="bx bx-trending-up me-1"></i>
            Opening Stock
          </th>
          <th class="text-center">
            <i class="bx bx-box me-1"></i>
            Current Stock
          </th>
          <th class="text-center">
            <i class="bx bx-transfer me-1"></i>
            Movement
          </th>
          <th class="text-center">
            <i class="bx bx-signal-3 me-1"></i>
            Status
          </th>
          <th class="text-center">
            <i class="bx bx-cog me-1"></i>
            Actions
          </th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse($stockBalances as $balance)
        <tr>
          <!-- SKU -->
          <td>
            <span class="badge bg-label-secondary">{{ $balance->item->item_code ?? 'N/A' }}</span>
          </td>
          
          <!-- Item -->
          <td>
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-box"></i>
                </span>
              </div>
              <div>
                <strong>{{ $balance->item->item_name ?? 'N/A' }}</strong>
                <br><small class="text-muted">
                  <i class="bx bx-cube"></i>
                  {{ $balance->item->unit ?? 'pcs' }}
                </small>
              </div>
            </div>
          </td>
          
          <!-- Warehouse -->
          <td>
            <div class="d-flex align-items-center">
              <i class="bx bx-buildings text-primary me-2"></i>
              <span>{{ $balance->warehouse->warehouse_name ?? 'N/A' }}</span>
            </div>
          </td>
          
          <!-- Category -->
          <td>
            <div class="d-flex align-items-center">
              <i class="bx bx-category text-info me-2"></i>
              <span>{{ $balance->item->category->category_name ?? 'N/A' }}</span>
            </div>
          </td>
          
          <!-- Opening Stock -->
          <td class="text-center">
            <div class="d-flex flex-column align-items-center">
              <span class="fw-bold text-info">
                {{ number_format($balance->opening_stock, 0) }}
              </span>
              <small class="text-muted">Opening</small>
            </div>
          </td>
          
          <!-- Current Stock -->
          <td class="text-center">
            @php
              $stockLevel = $balance->closing_stock;
              $threshold = $balance->item->low_stock_threshold ?? 0;
              $statusColor = 'success';
              
              if ($stockLevel <= 0) $statusColor = 'danger';
              elseif ($stockLevel <= $threshold) $statusColor = 'warning';
            @endphp
            <div class="d-flex flex-column align-items-center">
              <span class="fw-bold text-{{ $statusColor }}">
                {{ number_format($balance->closing_stock, 0) }}
              </span>
              <small class="text-muted">
                Min: {{ number_format($threshold, 0) }}
              </small>
            </div>
          </td>
          
          <!-- Movement -->
          <td class="text-center">
            @php
              $stockIn = $balance->stock_in ?? 0;
              $stockOut = $balance->stock_out ?? 0;
              $netMovement = $stockIn - $stockOut;
              
              $movementIcon = 'bx-minus';
              $movementColor = 'muted';
              
              if ($netMovement > 0) {
                  $movementIcon = 'bx-up-arrow-alt';
                  $movementColor = 'success';
              } elseif ($netMovement < 0) {
                  $movementIcon = 'bx-down-arrow-alt';
                  $movementColor = 'danger';
              }
            @endphp
            <div class="d-flex flex-column align-items-center">
              <span class="fw-bold text-{{ $movementColor }}">
                <i class="bx {{ $movementIcon }}"></i>
                {{ number_format(abs($netMovement), 0) }}
              </span>
              <div class="mt-1">
                <small class="text-success">+{{ number_format($stockIn, 0) }}</small>
                <small class="text-danger">-{{ number_format($stockOut, 0) }}</small>
              </div>
            </div>
          </td>
          
          <!-- Status -->
          <td class="text-center">
            @php
              if ($balance->closing_stock <= 0) {
                  $statusBadge = '<span class="badge bg-danger">Empty</span>';
              } elseif ($balance->isLowStock()) {
                  $statusBadge = '<span class="badge bg-warning">Low Stock</span>';
              } else {
                  $statusBadge = '<span class="badge bg-success">Normal</span>';
              }
            @endphp
            {!! $statusBadge !!}
          </td>
          
          <!-- Actions -->
          <td class="text-center">
            <div class="btn-group" role="group">
              <a href="{{ route('central-warehouse.show', $balance->id) }}" class="btn btn-sm btn-info" title="View Details">
                <i class="bx bx-show"></i>
              </a>
              <a href="{{ route('central-warehouse.adjust-stock', $balance->id) }}" class="btn btn-sm btn-primary" title="Adjust Stock">
                <i class="bx bx-edit-alt"></i>
              </a>
              <a href="{{ route('central-warehouse.distribute-stock', $balance->id) }}" class="btn btn-sm btn-success" title="Distribute">
                <i class="bx bx-share"></i>
              </a>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="text-center py-4">
            <div class="d-flex flex-column align-items-center">
              <i class="bx bx-package" style="font-size: 48px; color: #ddd;"></i>
              <p class="text-muted mt-2 mb-0">No stock data available</p>
              @if(request()->hasAny(['search', 'warehouse_id', 'category_id']))
                <small class="text-muted">Try adjusting your filters</small>
              @endif
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  <!-- Pagination -->
  @if($stockBalances->hasPages())
  <div class="card-footer">
    <div class="d-flex justify-content-between align-items-center">
      <div class="text-muted">
        Showing {{ $stockBalances->firstItem() }} to {{ $stockBalances->lastItem() }} of {{ $stockBalances->total() }} items
      </div>
      {{ $stockBalances->links() }}
    </div>
  </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
// Quick action functions
function showBulkAdjustment() {
    alert('Bulk adjustment feature coming soon!');
}

function showBulkDistribution() {
    alert('Bulk distribution feature coming soon!');
}

function showStockReport() {
    alert('Stock report feature coming soon!');
}

function exportStock() {
    alert('Export feature coming soon!');
}
</script>
@endpush

@push('styles')
<style>
.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  font-size: 18px;
}

.table-hover tbody tr:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.badge {
  font-size: 0.65em;
}

/* Quick actions card styling */
.btn .text-start {
  text-align: left !important;
}

.btn .text-start div {
  line-height: 1.2;
}

.btn .text-start small {
  opacity: 0.8;
}

/* Stock status colors */
.text-success { color: #71dd37 !important; }
.text-warning { color: #ffab00 !important; }
.text-danger { color: #ff3e1d !important; }
.text-info { color: #03c3ec !important; }
</style>
@endpush
@extends('layouts.admin')

@section('title', $warehouse->warehouse_name . ' - Outlet Warehouse')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ route('outlet-warehouse.index') }}">Outlet Warehouse</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ $warehouse->warehouse_name }}</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Warehouse Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h4 class="mb-2">
              <i class="bx bx-store-alt me-2 text-primary"></i>
              {{ $warehouse->warehouse_name }}
            </h4>
            <div class="text-muted">
              <span class="me-3">
                <i class="bx bx-code-alt me-1"></i>
                {{ $warehouse->warehouse_code }}
              </span>
              @if($warehouse->branch)
              <span class="me-3">
                <i class="bx bx-building me-1"></i>
                {{ $warehouse->branch->branch_name }}
              </span>
              @endif
              <span class="badge bg-success">{{ $warehouse->status }}</span>
            </div>
          </div>
          <div>
            <a href="{{ route('outlet-warehouse.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>Kembali
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-package fs-4"></i>
            </span>
          </div>
          <div>
            <small class="text-muted d-block">Total Items</small>
            <h5 class="mb-0">{{ number_format($stats['total_items']) }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-cube fs-4"></i>
            </span>
          </div>
          <div>
            <small class="text-muted d-block">Total Stock</small>
            <h5 class="mb-0">{{ number_format($stats['total_stock'], 2) }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-info">
              <i class="bx bx-download fs-4"></i>
            </span>
          </div>
          <div>
            <small class="text-muted d-block">Received (MTD)</small>
            <h5 class="mb-0">{{ number_format($stats['total_received'], 2) }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-upload fs-4"></i>
            </span>
          </div>
          <div>
            <small class="text-muted d-block">Distributed (MTD)</small>
            <h5 class="mb-0">{{ number_format($stats['total_distributed'], 2) }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <a href="{{ route('outlet-warehouse.receive.create', $warehouse->id) }}" class="btn btn-primary w-100">
              <i class="bx bx-package me-2"></i>
              Terima Stock
            </a>
          </div>
          <div class="col-md-3">
            <a href="{{ route('outlet-warehouse.distribute.create', $warehouse->id) }}" class="btn btn-success w-100">
              <i class="bx bx-send me-2"></i>
              Distribusi ke Kitchen
            </a>
          </div>
          <div class="col-md-3">
            <a href="{{ route('outlet-warehouse.adjustment.create', $warehouse->id) }}" class="btn btn-warning w-100">
              <i class="bx bx-edit-alt me-2"></i>
              Adjustment
            </a>
          </div>
          <div class="col-md-3">
            <a href="{{ route('outlet-warehouse.transactions', $warehouse->id) }}" class="btn btn-info w-100">
              <i class="bx bx-history me-2"></i>
              Transactions
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Current Stock Balance -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-bar-chart-alt-2 me-2"></i>
          Stock saat ini
        </h5>
        @if($stats['low_stock_items'] > 0)
        <span class="badge bg-warning">
          <i class="bx bx-error-circle me-1"></i>
          {{ $stats['low_stock_items'] }} Low Stock Items
        </span>
        @endif
      </div>
      <div class="card-body">
        @if($stockBalance->isEmpty())
          <div class="alert alert-info mb-0">
            <i class="bx bx-info-circle me-2"></i>
            Belum ada stock di warehouse ini untuk bulan ini.
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr>
                  <th>SKU</th>
                  <th>Item Name</th>
                  <th>Category</th>
                  <th class="text-end">Opening</th>
                  <th class="text-end">Received</th>
                  <th class="text-end">Distributed</th>
                  <th class="text-end">Adjustments</th>
                  <th class="text-end">Closing Stock</th>
                  <th class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($stockBalance as $balance)
                <tr>
                  <td>
                    <span class="badge bg-label-secondary">{{ $balance->item->sku ?? '-' }}</span>
                  </td>
                  <td>
                    <strong>{{ $balance->item->item_name ?? '-' }}</strong>
                    <br>
                    <small class="text-muted">{{ $balance->item->unit_measurement ?? 'Unit' }}</small>
                  </td>
                  <td>
                    @if($balance->item->category)
                      <span class="badge bg-label-info">{{ $balance->item->category->name }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="text-end">{{ number_format($balance->opening_stock, 2) }}</td>
                  <td class="text-end text-success">
                    <strong>+{{ number_format($balance->received_from_branch_warehouse, 2) }}</strong>
                  </td>
                  <td class="text-end text-warning">
                    <strong>-{{ number_format($balance->distributed_to_kitchen, 2) }}</strong>
                  </td>
                  <td class="text-end {{ $balance->adjustments >= 0 ? 'text-info' : 'text-danger' }}">
                    {{ $balance->adjustments >= 0 ? '+' : '' }}{{ number_format($balance->adjustments, 2) }}
                  </td>
                  <td class="text-end">
                    <strong class="fs-6">{{ number_format($balance->closing_stock, 2) }}</strong>
                  </td>
                  <td class="text-center">
                    @php
                      $lowThreshold = $balance->item->low_stock_threshold ?? 10;
                      $isLowStock = $balance->closing_stock < $lowThreshold;
                    @endphp
                    @if($balance->closing_stock == 0)
                      <span class="badge bg-danger">Empty</span>
                    @elseif($isLowStock)
                      <span class="badge bg-warning">Low Stock</span>
                    @else
                      <span class="badge bg-success">Available</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
              <tfoot class="table-secondary">
                <tr>
                  <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                  <td class="text-end fw-bold">{{ number_format($stockBalance->sum('opening_stock'), 2) }}</td>
                  <td class="text-end fw-bold text-success">+{{ number_format($stockBalance->sum('received_from_branch_warehouse'), 2) }}</td>
                  <td class="text-end fw-bold text-warning">-{{ number_format($stockBalance->sum('distributed_to_kitchen'), 2) }}</td>
                  <td class="text-end fw-bold">{{ number_format($stockBalance->sum('adjustments'), 2) }}</td>
                  <td class="text-end fw-bold">{{ number_format($stockBalance->sum('closing_stock'), 2) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="mt-3">
            {{ $stockBalance->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Recent Transactions -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-history me-2"></i>
          Recent Transactions (Last 10)
        </h5>
        <a href="{{ route('outlet-warehouse.transactions', $warehouse->id) }}" class="btn btn-sm btn-outline-primary">
          View All <i class="bx bx-right-arrow-alt ms-1"></i>
        </a>
      </div>
      <div class="card-body">
        @if($recentTransactions->isEmpty())
          <div class="alert alert-info mb-0">
            <i class="bx bx-info-circle me-2"></i>
            Belum ada transaksi.
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Reference</th>
                  <th>Type</th>
                  <th>Item</th>
                  <th class="text-end">Quantity</th>
                  <th>User</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recentTransactions as $transaction)
                <tr>
                  <td>
                    <small>{{ $transaction->transaction_date->format('d M Y') }}</small>
                    <br>
                    <small class="text-muted">{{ $transaction->transaction_date->format('H:i') }}</small>
                  </td>
                  <td>
                    <span class="badge bg-label-secondary">{{ $transaction->reference_no }}</span>
                  </td>
                  <td>
                    @php
                      $typeConfig = [
                        'RECEIVE_FROM_BRANCH' => ['bg-success', 'IN'],
                        'DISTRIBUTE_TO_KITCHEN' => ['bg-warning', 'OUT'],
                        'ADJUSTMENT' => ['bg-info', 'ADJ'],
                        'RETURN_FROM_KITCHEN' => ['bg-primary', 'RTN'],
                      ];
                      $config = $typeConfig[$transaction->transaction_type] ?? ['bg-secondary', 'N/A'];
                    @endphp
                    <span class="badge {{ $config[0] }}">{{ $config[1] }}</span>
                  </td>
                  <td>
                    <strong>{{ $transaction->item->item_name ?? '-' }}</strong>
                    <br>
                    <small class="text-muted">{{ $transaction->item->sku ?? '' }}</small>
                  </td>
                  <td class="text-end">
                    <span class="{{ $transaction->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                      <strong>{{ $transaction->quantity >= 0 ? '+' : '' }}{{ number_format($transaction->quantity, 2) }}</strong>
                    </span>
                  </td>
                  <td>
                    <small>{{ $transaction->user->full_name ?? '-' }}</small>
                  </td>
                  <td>
                    <small class="text-muted">{{ Str::limit($transaction->notes, 30) }}</small>
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
@endsection

@push('styles')
<style>
.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
}

.table-hover tbody tr:hover {
  background-color: rgba(67, 89, 113, 0.05);
}

.badge {
  font-weight: 500;
  padding: 0.375rem 0.75rem;
}

.card-header h5, .card-header h6 {
  margin-bottom: 0;
}

.text-success {
  color: #28c76f !important;
}

.text-warning {
  color: #ff9f43 !important;
}

.text-danger {
  color: #ea5455 !important;
}

.text-info {
  color: #00cfe8 !important;
}
</style>
@endpush
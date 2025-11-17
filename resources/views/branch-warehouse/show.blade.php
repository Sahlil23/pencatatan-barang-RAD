@extends('layouts.admin')

@section('title', 'Detail Branch Warehouse - Chicking BJM')

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
          <a href="{{ route('branch-warehouse.index') }}">Branch Warehouse</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ $warehouse->warehouse_name }}
        </li>
      </ol>
    </nav>
  </div>
</div>

<!-- Warehouse Header Info -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="d-flex align-items-start">
            <div>
              <h5 class="mb-0">{{ $warehouse->warehouse_name }}</h5>
              <small class="text-muted">
                <i class="bx bx-hash me-1"></i>{{ $warehouse->warehouse_code }}
              </small>
              <div class="mt-2">
                <span class="badge bg-label-success">
                  <i class="bx bx-check-circle me-1"></i>
                  {{ ucfirst($warehouse->status) }}
                </span>
              </div>
            </div>
          </div>
          <div class="text-end">
            <div class="dropdown">
              <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bx bx-cog me-1"></i>
                Actions
              </button>
            <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item text-warning" href="{{ route('branch-warehouse.pending-distributions', $warehouse->id) }}">
                    <i class="bx bx-time-five me-1"></i>
                    Pending Distributions
                  </a>  
                  <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('branch-warehouse.receive-form', $warehouse->id) }}">
                  <i class="bx bx-import me-1"></i>
                  Terima Stock
                </a>
                <a class="dropdown-item" href="{{ route('branch-warehouse.adjust-form', $warehouse->id) }}">
                  <i class="bx bx-cog me-1"></i>
                  Adjustment
                </a>
                <a class="dropdown-item" href="{{ route('branch-warehouse.distribute-form', $warehouse->id) }}">
                  <i class="bx bx-export me-1"></i>
                  Distribusi ke Outlet
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('warehouses.edit', $warehouse->id) }}">
                  <i class="bx bx-edit-alt me-1"></i>
                  Edit Warehouse
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Warehouse Details Grid -->
        <div class="row mt-3">
          <div class="col-md-3">
            <small class="text-muted d-block">Branch</small>
            <h6 class="mb-0">
              @if($warehouse->branch)
                <span class="badge bg-label-primary">{{ $warehouse->branch->branch_name }}</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </h6>
          </div>
          <div class="col-md-3">
            <small class="text-muted d-block">Tipe Warehouse</small>
            <h6 class="mb-0">
              <span class="badge bg-label-info">{{ ucfirst($warehouse->warehouse_type) }}</span>
            </h6>
          </div>
          <div class="col-md-3">
            <small class="text-muted d-block">Alamat</small>
            <h6 class="mb-0">{{ $warehouse->address ?? '-' }}</h6>
          </div>
          <div class="col-md-3">
            <small class="text-muted d-block">Dibuat</small>
            <h6 class="mb-0">{{ $warehouse->created_at->format('d M Y') }}</h6>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Stock Overview Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
              <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Items</span>
        <h3 class="card-title mb-2">{{ $stats['total_items'] ?? 0 }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-package me-1"></i>Item Types
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="total stock" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Stock</span>
        <h3 class="card-title text-success mb-2">{{ number_format($stats['total_stock'] ?? 0, 2) }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-up-arrow-alt me-1"></i>Units
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="stock in" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total In</span>
        <h3 class="card-title text-success mb-2">{{ number_format($stats['total_in'] ?? 0, 2) }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-import me-1"></i>Receipts
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="total value" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Value</span>
        <h3 class="card-title text-primary mb-2">Rp {{ number_format($stats['total_value'] ?? 0, 0, ',', '.') }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-money me-1"></i>Inventory
        </small>
      </div>
    </div>
  </div>
</div>

<!-- Main Content Row -->
<div class="row">
  <!-- Pending Distributions Section -->
  <!-- @if(isset($pendingDistributions) && $pendingDistributions->count() > 0) -->
  <div class="col-12 mb-4">
    <div class="card border-warning">
      <div class="card-header bg-label-warning d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-time-five me-2"></i>
          Pending Distributions
          <span class="badge bg-warning">{{ $pendingDistributions->count() }} pending</span>
        </h5>
        <a href="{{ route('branch-warehouse.pending-distributions', $warehouse->id) }}" class="btn btn-warning btn-sm">
          <i class="bx bx-list-ul me-1"></i>
          View All Pending
        </a>
      </div>

      <div class="alert alert-warning m-3 mb-0">
        <i class="bx bx-info-circle me-2"></i>
        <strong>Action Required:</strong> You have {{ $pendingDistributions->count() }} distribution(s) waiting for approval or rejection.
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>
                <i class="bx bx-calendar me-1"></i>
                Date
              </th>
              <th>
                <i class="bx bx-receipt me-1"></i>
                Reference
              </th>
              <th>
                <i class="bx bx-building me-1"></i>
                From
              </th>
              <th>
                <i class="bx bx-box me-1"></i>
                Item
              </th>
              <th class="text-center">
                <i class="bx bx-package me-1"></i>
                Quantity
              </th>
              <th class="text-center">
                <i class="bx bx-flag me-1"></i>
                Status
              </th>
              <th class="text-center">
                <i class="bx bx-cog me-1"></i>
                Action
              </th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach($pendingDistributions->take(5) as $distribution)
            <tr>
              <!-- Date -->
              <td>
                <div>
                  <strong>{{ $distribution->transaction_date->format('d/m/Y') }}</strong>
                  <br><small class="text-muted">{{ $distribution->transaction_date->format('H:i') }}</small>
                </div>
              </td>

              <!-- Reference -->
              <td>
                <span class="badge bg-label-primary">{{ $distribution->reference_no }}</span>
              </td>

              <!-- From -->
              <td>
                <div>
                  <strong>{{ $distribution->centralWarehouse->warehouse_name ?? 'N/A' }}</strong>
                  <br><small class="text-muted">{{ $distribution->centralWarehouse->warehouse_code ?? '-' }}</small>
                </div>
              </td>

              <!-- Item -->
              <td>
                <div>
                  <strong>{{ $distribution->item->item_name }}</strong>
                  <br><small class="text-muted">
                    <i class="bx bx-barcode me-1"></i>{{ $distribution->item->sku }}
                  </small>
                </div>
              </td>

              <!-- Quantity -->
              <td class="text-center">
                <span class="badge bg-label-info fs-6">
                  {{ number_format($distribution->quantity, 2) }} {{ $distribution->item->unit }}
                </span>
              </td>

              <!-- Status -->
              <td class="text-center">
                <span class="badge bg-warning">
                  <i class="bx bx-time-five me-1"></i>
                  {{ $distribution->status }}
                </span>
              </td>

              <!-- Action -->
              <td class="text-center">
                <a href="{{ route('branch-warehouse.pending-distributions', $warehouse->id) }}" 
                   class="btn btn-sm btn-warning"
                   data-bs-toggle="tooltip"
                   title="Review this distribution">
                  <i class="bx bx-show me-1"></i>
                  Review
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="table-light">
            <tr>
              <td colspan="4" class="text-end">
                <strong>Total Pending:</strong>
              </td>
              <td class="text-center">
                <strong>{{ number_format($pendingDistributions->sum('quantity'), 2) }}</strong>
              </td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      @if($pendingDistributions->count() > 5)
      <div class="card-footer text-center">
        <a href="{{ route('branch-warehouse.pending-distributions', $warehouse->id) }}" class="btn btn-outline-warning">
          <i class="bx bx-right-arrow-alt me-1"></i>
          View All {{ $pendingDistributions->count() }} Pending Distributions
        </a>
      </div>
      @endif
    </div>
  </div>
  <!-- @endif -->

  <!-- Current Stock Table -->
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-package me-2"></i>
          Stok saat ini
          <span class="badge bg-label-primary">{{ $stockBalance->count() }} items</span>
        </h5>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="bx bx-download me-1"></i>
            Export
          </button>
          <a href="{{ route('branch-warehouse.receive-form', $warehouse->id) }}" class="btn btn-primary btn-sm">
            <i class="bx bx-plus me-1"></i>
            Terima Stock
          </a>
        </div>
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th style="width: 80px;">
                <i class="bx bx-hash me-1"></i>
                Kode Item
              </th>
              <th>
                <i class="bx bx-box me-1"></i>
                Item Name
              </th>
              <th class="text-center" style="width: 120px;">
                <i class="bx bx-plus me-1"></i>
                Stock In
              </th>
              <th class="text-center" style="width: 120px;">
                <i class="bx bx-minus me-1"></i>
                Stock Out
              </th>
              <th class="text-center" style="width: 120px;">
                <i class="bx bx-package me-1"></i>
                Stock Akhir
              </th>
              <!-- <th class="text-right" style="width: 130px;">
                <i class="bx bx-dollar me-1"></i>
                Unit Cost
              </th>
              <th class="text-right" style="width: 150px;">
                <i class="bx bx-receipt me-1"></i>
                Total Value
              </th> -->
              <th class="text-center" style="width: 100px;">
                <i class="bx bx-cog me-1"></i>
                Action
              </th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse ($stockBalance as $balance)
            <tr>
              <!-- Item Code -->
              <td>
                <span class="badge bg-label-secondary">{{ $balance->item->sku }}</span>
              </td>

              <!-- Item Name -->
              <td>
                <div>
                  <strong>{{ $balance->item->item_name }}</strong>
                  <br><small class="text-muted">{{ $balance->item->category->category_name ?? 'N/A' }}</small>
                </div>
              </td>

              <!-- Stock In -->
              <td class="text-center">
                <span class="badge bg-label-success">
                  +{{ number_format($balance->stock_in, 2) }}
                </span>
              </td>

              <!-- Stock Out -->
              <td class="text-center">
                <span class="badge bg-label-danger">
                  -{{ number_format($balance->stock_out, 2) }}
                </span>
              </td>

              <!-- Closing Stock -->
              <td class="text-center">
                @php
                  $closingStock = $balance->closing_stock;
                  $statusColor = 'success';
                  if ($closingStock <= 0) {
                    $statusColor = 'danger';
                  } elseif ($closingStock <= ($balance->item->low_stock_threshold ?? 10)) {
                    $statusColor = 'warning';
                  }
                @endphp
                <div>
                  <span class="badge bg-label-{{ $statusColor }} fw-bold">
                    {{ number_format($closingStock, 2) }}
                  </span>
                  <br><small class="text-muted">{{ $balance->item->unit ?? 'Unit' }}</small>
                </div>
              </td>

              <!--
              <td class="text-right">
                @if($balance->avg_unit_cost > 0)
                  <span class="fw-semibold">Rp {{ number_format($balance->avg_unit_cost, 0, ',', '.') }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>


              <td class="text-right">
                @if($balance->total_value > 0)
                  <span class="fw-bold text-primary">Rp {{ number_format($balance->total_value, 0, ',', '.') }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td> -->

              <!-- Actions -->
              <td class="text-center">
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    @if($closingStock > 0)
                    <a class="dropdown-item" href="{{ route('branch-warehouse.distribute-form', $warehouse->id) }}">
                      <i class="bx bx-export me-1"></i>
                      Distribusi
                    </a>
                    @endif
                    
                    <a class="dropdown-item" href="{{ route('branch-warehouse.adjust-form', $warehouse->id) }}">
                      <i class="bx bx-cog me-1"></i>
                      Adjustment
                    </a>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4">
                <div class="d-flex flex-column align-items-center">
                  <i class="bx bx-package" style="font-size: 48px; color: #ddd;"></i>
                  <h6 class="mt-2 text-muted">Belum ada stock</h6>
                  <p class="text-muted mb-3">Warehouse ini belum memiliki stock item</p>
                  <a href="{{ route('branch-warehouse.receive-form', $warehouse->id) }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i>
                    Terima Stock Pertama
                  </a>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      @if($stockBalance->hasPages())
      <div class="card-footer">
        {{ $stockBalance->links() }}
      </div>
      @endif
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
          Recent Transactions
          <span class="badge bg-label-primary">{{ $recentTransactions->count() }} latest</span>
        </h5>
        <a href="{{ route('branch-warehouse.distributions', $warehouse->id) }}" class="btn btn-outline-secondary btn-sm">
          <i class="bx bx-right-arrow-alt me-1"></i>
          View All
        </a>
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>
                <i class="bx bx-calendar me-1"></i>
                Date
              </th>
              <th>
                <i class="bx bx-transfer me-1"></i>
                Type
              </th>
              <th>
                <i class="bx bx-box me-1"></i>
                Item
              </th>
              <th class="text-center">
                <i class="bx bx-plus me-1"></i>
                Qty
              </th>
              <th>
                <i class="bx bx-receipt me-1"></i>
                Reference
              </th>
              <th>
                <i class="bx bx-user me-1"></i>
                User
              </th>
              <th>
                <i class="bx bx-message me-1"></i>
                Notes
              </th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse ($recentTransactions as $transaction)
            <tr>
              <!-- Date -->
              <td>
                <div>
                  <strong>{{ $transaction->transaction_date->format('d/m/Y') }}</strong>
                  <br><small class="text-muted">{{ $transaction->transaction_date->format('H:i') }}</small>
                </div>
              </td>

              <!-- Transaction Type -->
              <td>
                @php
                  $typeConfig = [
                    'IN' => ['color' => 'success', 'icon' => 'bx-import', 'label' => 'IN'],
                    'OUT' => ['color' => 'danger', 'icon' => 'bx-export', 'label' => 'OUT'],
                    'RECEIVE_FROM_CENTRAL' => ['color' => 'info', 'icon' => 'bx-import', 'label' => 'From Central'],
                    'TRANSFER_TO_KITCHEN' => ['color' => 'primary', 'icon' => 'bx-send', 'label' => 'To Kitchen'],
                    'TRANSFER_TO_CENTRAL' => ['color' => 'warning', 'icon' => 'bx-export', 'label' => 'To Central'],
                    'WASTAGE' => ['color' => 'danger', 'icon' => 'bx-trash', 'label' => 'Waste'],
                    'ADJUSTMENT_IN' => ['color' => 'info', 'icon' => 'bx-edit', 'label' => 'Adj. IN'],
                    'ADJUSTMENT_OUT' => ['color' => 'warning', 'icon' => 'bx-edit', 'label' => 'Adj. OUT'],
                  ];
                  $config = $typeConfig[$transaction->transaction_type] ?? ['color' => 'secondary', 'icon' => 'bx-transfer', 'label' => $transaction->transaction_type];
                @endphp
                <span class="badge bg-{{ $config['color'] }}">
                  <i class="bx {{ $config['icon'] }} me-1"></i>
                  {{ $config['label'] }}
                </span>
              </td>

              <!-- Item -->
              <td>
                <strong>{{ $transaction->item->item_name ?? '-' }}</strong>
                <br><small class="text-muted">{{ $transaction->item->sku ?? '-' }}</small>
              </td>

              <!-- Quantity -->
              <td class="text-center">
                @if($transaction->quantity >= 0)
                  <span class="fw-bold text-success">+{{ number_format($transaction->quantity, 2) }}</span>
                @else
                  <span class="fw-bold text-danger">{{ number_format($transaction->quantity, 2) }}</span>
                @endif
              </td>

              <!-- Reference -->
              <td>
                <span class="badge bg-label-secondary">{{ $transaction->reference_no }}</span>
              </td>

              <!-- User -->
              <td>
                <div class="d-flex align-items-center">
                  <small>{{ $transaction->user->full_name ?? 'Unknown' }}</small>
                </div>
              </td>

              <!-- Notes -->
              <td>
                <small class="text-muted">
                  {{ Str::limit($transaction->notes ?? '-', 40) }}
                </small>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4">
                <div class="text-muted">
                  <i class="bx bx-history" style="font-size: 32px; color: #ddd;"></i>
                  <p class="mt-2">Belum ada transaksi</p>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exportModalLabel">
          <i class="bx bx-download me-2"></i>
          Export Current Stock
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="#" method="GET">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Format Export</label>
            <select class="form-select" name="format" required>
              <option value="csv">CSV (Comma Separated Values)</option>
              <option value="excel">Excel (.xlsx)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Include Details</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="include_transactions" id="include_transactions">
              <label class="form-check-label" for="include_transactions">
                Include recent transactions
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-download me-1"></i>
            Export
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });

  // Add row hover effects
  const tableRows = document.querySelectorAll('.table tbody tr');
  tableRows.forEach(row => {
    row.addEventListener('mouseenter', function() {
      this.style.backgroundColor = 'rgba(105, 108, 255, 0.04)';
    });
    row.addEventListener('mouseleave', function() {
      this.style.backgroundColor = '';
    });
  });
});
</script>
@endpush

@push('styles')
<style>
  .badge {
    font-size: 0.65em;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(105, 108, 255, 0.04) !important;
  }

  .avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }

  /* Card hover effects */
  .card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.1);
  }

  /* Status badge colors */
  .badge.bg-label-success { background-color: rgba(113, 221, 55, 0.16) !important; color: #71dd37 !important; }
  .badge.bg-label-danger { background-color: rgba(255, 62, 29, 0.16) !important; color: #ff3e1d !important; }
  .badge.bg-label-warning { background-color: rgba(255, 171, 0, 0.16) !important; color: #ffab00 !important; }
  .badge.bg-label-info { background-color: rgba(3, 195, 236, 0.16) !important; color: #03c3ec !important; }
  .badge.bg-label-primary { background-color: rgba(105, 108, 255, 0.16) !important; color: #696cff !important; }
  .badge.bg-label-secondary { background-color: rgba(108, 117, 125, 0.16) !important; color: #6c757d !important; }

  /* Text alignment */
  .text-right {
    text-align: right;
  }

  .text-center {
    text-align: center;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .table-responsive {
      font-size: 0.875rem;
    }

    .avatar-initial {
      width: 32px;
      height: 32px;
      font-size: 14px;
    }

    /* Hide less important columns on mobile */
    .table th:nth-child(4),
    .table td:nth-child(4),
    .table th:nth-child(7),
    .table td:nth-child(7) {
      display: none;
    }
  }
</style>
@endpush
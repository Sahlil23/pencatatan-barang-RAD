@extends('layouts.admin')

@section('title', 'Detail Stock Item - Central Warehouse')

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
          <a href="{{ route('central-warehouse.index') }}">Central Warehouse</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          Detail Stock - {{ $balance->item->item_name }}
        </li>
      </ol>
    </nav>
  </div>
</div>

<!-- Stock Overview Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/chart.png') }}" alt="opening stock" class="rounded" />
          </div>
          <div class="dropdown">
            <span class="badge bg-label-primary">{{ $balance->year }}/{{ str_pad($balance->month, 2, '0', STR_PAD_LEFT) }}</span>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Stock Awal</span>
        <h3 class="card-title mb-2">{{ number_format($balance->opening_stock, 2) }}</h3>
        <small class="text-muted">
          <i class="bx bx-cube me-1"></i>{{ $balance->item->unit }}
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
          <div class="dropdown">
            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu">
              <span class="dropdown-item-text">
                <small>Purchase: {{ number_format($balance->purchase_in, 2) }}</small><br>
                <small>Adjustment: {{ number_format($balance->adjustment_in, 2) }}</small><br>
                <small>Return: {{ number_format($balance->branch_return_in, 2) }}</small>
              </span>
            </div>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Masuk</span>
        <h3 class="card-title text-success mb-2">{{ number_format($balance->stock_in, 2) }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-up-arrow-alt me-1"></i>{{ $balance->item->unit }}
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="stock out" class="rounded" />
          </div>
          <div class="dropdown">
            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu">
              <span class="dropdown-item-text">
                <small>Distribution: {{ number_format($balance->distribute_out, 2) }}</small><br>
                <small>Adjustment: {{ number_format($balance->adjustment_out, 2) }}</small><br>
                <small>Return: {{ number_format($balance->purchase_return_out, 2) }}</small><br>
                <small>Waste: {{ number_format($balance->waste_out, 2) }}</small>
              </span>
            </div>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Keluar</span>
        <h3 class="card-title text-danger mb-2">{{ number_format($balance->stock_out, 2) }}</h3>
        <small class="text-danger fw-semibold">
          <i class="bx bx-down-arrow-alt me-1"></i>{{ $balance->item->unit }}
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="closing stock" class="rounded" />
          </div>
          @php
            $stockStatus = 'normal';
            $statusColor = 'success';
            if ($balance->closing_stock <= 0) {
              $stockStatus = 'empty';
              $statusColor = 'danger';
            } elseif ($balance->closing_stock <= $balance->item->low_stock_threshold) {
              $stockStatus = 'low';
              $statusColor = 'warning';
            }
          @endphp
          <div class="dropdown">
            <span class="badge bg-{{ $statusColor }}">
              @if($stockStatus === 'empty') Empty
              @elseif($stockStatus === 'low') Low Stock
              @else Normal
              @endif
            </span>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Stock Akhir</span>
        <h3 class="card-title text-{{ $statusColor }} mb-2">{{ number_format($balance->closing_stock, 2) }}</h3>
        <small class="text-{{ $statusColor }} fw-semibold">
          <i class="bx bx-package me-1"></i>{{ $balance->item->unit }}
        </small>
      </div>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="row">
  <!-- Item & Stock Info -->
  <div class="col-xl-4">
    <!-- Item Information Card -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-package me-2"></i>
          Informasi Item
        </h5>
        <small class="text-muted">Master Data</small>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-box"></i>
            </span>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-0">{{ $balance->item->item_name }}</h6>
            <small class="text-muted">{{ $balance->item->sku }}</small>
          </div>
        </div>

        <!-- Item Details -->
        <div class="info-container">
          <ul class="list-unstyled">
            <li class="mb-3">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">Kategori:</span>
                <span class="badge bg-label-info">{{ $balance->item->category->category_name ?? 'N/A' }}</span>
              </div>
            </li>
            <li class="mb-3">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">Unit:</span>
                <span class="badge bg-label-secondary">{{ $balance->item->unit }}</span>
              </div>
            </li>
            <li class="mb-3">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">Threshold:</span>
                <span class="badge bg-label-warning">{{ number_format($balance->item->low_stock_threshold, 2) }}</span>
              </div>
            </li>
            <li class="mb-3">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">Unit Cost:</span>
                <span class="fw-semibold text-success">
                  @if($balance->unit_cost > 0)
                    Rp {{ number_format($balance->unit_cost, 0) }}
                  @else
                    -
                  @endif
                </span>
              </div>
            </li>
            <li>
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">Total Value:</span>
                <span class="fw-semibold text-primary">
                  @if($balance->total_value > 0)
                    Rp {{ number_format($balance->total_value, 0) }}
                  @else
                    -
                  @endif
                </span>
              </div>
            </li>
          </ul>
        </div>

        <hr>

        <!-- Warehouse Info -->
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-buildings"></i>
            </span>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-0">{{ $balance->warehouse->warehouse_name }}</h6>
            <small class="text-muted">{{ $balance->warehouse->warehouse_code }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Actions Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-cog me-2"></i>
          Stock Actions
        </h6>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          @if($balance->closing_stock > 0)
          <a href="{{ route('central-warehouse.distribute-stock', $balance->id) }}" class="btn btn-primary btn-sm">
            <i class="bx bx-send me-1"></i>
            Distribute to Branch
          </a>
          @endif
          
          <a href="{{ route('central-warehouse.adjust-stock', $balance->id) }}" class="btn btn-outline-warning btn-sm">
            <i class="bx bx-edit me-1"></i>
            Adjust Stock
          </a>
          
          <a href="{{ route('central-warehouse.receive-stock') }}" class="btn btn-outline-success btn-sm">
            <i class="bx bx-plus me-1"></i>
            Receive New Stock
          </a>
          
          <hr class="my-2">
          
          <a href="{{ route('central-warehouse.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i>
            Back to Dashboard
          </a>
        </div>
      </div>
    </div>

    <!-- Stock Status Info -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Stock Status
        </h6>
      </div>
      <div class="card-body">
        @if($stockStatus === 'empty')
        <div class="alert alert-danger" role="alert">
          <h6 class="alert-heading">
            <i class="bx bx-error me-1"></i>
            Stock Habis
          </h6>
          <p class="mb-0">Item ini sudah habis dan perlu segera direstock dari supplier.</p>
        </div>
        @elseif($stockStatus === 'low')
        <div class="alert alert-warning" role="alert">
          <h6 class="alert-heading">
            <i class="bx bx-error-circle me-1"></i>
            Stock Menipis
          </h6>
          <p class="mb-0">Stock di bawah threshold {{ number_format($balance->item->low_stock_threshold, 2) }} {{ $balance->item->unit }}. Pertimbangkan untuk restock.</p>
        </div>
        @else
        <div class="alert alert-success" role="alert">
          <h6 class="alert-heading">
            <i class="bx bx-check me-1"></i>
            Stock Normal
          </h6>
          <p class="mb-0">Stock dalam kondisi normal dan aman untuk distribusi.</p>
        </div>
        @endif

        <!-- Movement Summary -->
        <div class="mt-3">
          <h6 class="mb-2">Movement Summary:</h6>
          <div class="row text-center">
            <div class="col-4">
              <div class="border rounded p-2">
                <h6 class="text-success mb-1">+{{ number_format($balance->stock_in, 2) }}</h6>
                <small class="text-muted">IN</small>
              </div>
            </div>
            <div class="col-4">
              <div class="border rounded p-2">
                <h6 class="text-danger mb-1">-{{ number_format($balance->stock_out, 2) }}</h6>
                <small class="text-muted">OUT</small>
              </div>
            </div>
            <div class="col-4">
              <div class="border rounded p-2">
                @php
                  $netMovement = $balance->stock_in - $balance->stock_out;
                  $netColor = $netMovement >= 0 ? 'success' : 'danger';
                  $netSign = $netMovement >= 0 ? '+' : '';
                @endphp
                <h6 class="text-{{ $netColor }} mb-1">{{ $netSign }}{{ number_format($netMovement, 2) }}</h6>
                <small class="text-muted">NET</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Transaction History -->
  <div class="col-xl-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-history me-2"></i>
          Transaction History
        </h5>
        <div class="d-flex gap-2">
          <span class="badge bg-label-primary">{{ $balance->year }}/{{ str_pad($balance->month, 2, '0', STR_PAD_LEFT) }}</span>
          <span class="badge bg-label-info">{{ $transactions->count() }} transactions</span>
        </div>
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
              <th class="text-center">
                <i class="bx bx-package me-1"></i>
                Quantity
              </th>
              <th class="text-center">
                <i class="bx bx-dollar me-1"></i>
                Unit Cost
              </th>
              <th class="text-center">
                <i class="bx bx-receipt me-1"></i>
                Total
              </th>
              <th>
                <i class="bx bx-user me-1"></i>
                User
              </th>
              <th>
                <i class="bx bx-file me-1"></i>
                Reference
              </th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse ($transactions as $transaction)
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
                    'PURCHASE' => ['color' => 'success', 'icon' => 'bx-plus', 'label' => 'Purchase'],
                    'PURCHASE_RETURN' => ['color' => 'warning', 'icon' => 'bx-undo', 'label' => 'Return'],
                    'DISTRIBUTE_OUT' => ['color' => 'primary', 'icon' => 'bx-send', 'label' => 'Distribute'],
                    'BRANCH_RETURN' => ['color' => 'info', 'icon' => 'bx-import', 'label' => 'Return In'],
                    'ADJUSTMENT' => ['color' => 'secondary', 'icon' => 'bx-edit', 'label' => 'Adjustment'],
                    'WASTE' => ['color' => 'danger', 'icon' => 'bx-trash', 'label' => 'Waste']
                  ];
                  $config = $typeConfig[$transaction->transaction_type] ?? ['color' => 'secondary', 'icon' => 'bx-transfer', 'label' => $transaction->transaction_type];
                @endphp
                <span class="badge bg-{{ $config['color'] }}">
                  <i class="bx {{ $config['icon'] }} me-1"></i>
                  {{ $config['label'] }}
                </span>
              </td>
              
              <!-- Quantity -->
              <td class="text-center">
                <div>
                  @if($transaction->quantity >= 0)
                    <span class="fw-bold text-success">+{{ number_format($transaction->quantity, 2) }}</span>
                  @else
                    <span class="fw-bold text-danger">{{ number_format($transaction->quantity, 2) }}</span>
                  @endif
                  <br><small class="text-muted">{{ $balance->item->unit }}</small>
                </div>
              </td>
              
              <!-- Unit Cost -->
              <td class="text-center">
                @if($transaction->unit_cost > 0)
                  <span class="fw-semibold">Rp {{ number_format($transaction->unit_cost, 0) }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              
              <!-- Total Cost -->
              <td class="text-center">
                @if($transaction->total_cost > 0)
                  <span class="fw-bold text-primary">Rp {{ number_format($transaction->total_cost, 0) }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              
              <!-- User -->
              <td>
                <div class="d-flex align-items-center">
                  <div>
                    <strong>{{ $transaction->user->full_name ?? 'Unknown' }}</strong>
                    @if($transaction->supplier)
                      <br><small class="text-muted">
                        <i class="bx bx-store me-1"></i>{{ $transaction->supplier->supplier_name }}
                      </small>
                    @endif
                  </div>
                </div>
              </td>
              
              <!-- Reference -->
              <td>
                <div>
                  <span class="badge bg-label-secondary">{{ $transaction->reference_no }}</span>
                  @if($transaction->notes)
                    <br><small class="text-muted">{{ Str::limit($transaction->notes, 30) }}</small>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4">
                <div class="d-flex flex-column align-items-center">
                  <i class="bx bx-history" style="font-size: 48px; color: #ddd;"></i>
                  <h6 class="mt-2 text-muted">No transaction history</h6>
                  <p class="text-muted mb-0">Belum ada transaksi untuk item ini pada periode ini</p>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($transactions->count() >= 20)
      <div class="card-footer">
        <div class="text-center">
          <small class="text-muted">Showing recent 20 transactions only</small>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<!-- Quick Statistics -->
<div class="row mt-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-line-chart me-2"></i>
          Stock Movement Breakdown
        </h6>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Stock In Breakdown -->
          <div class="col-md-6">
            <h6 class="text-success">Stock In Details:</h6>
            <div class="table-responsive">
              <table class="table table-sm table-borderless">
                <tbody>
                  <tr>
                    <td>
                      <i class="bx bx-plus text-success me-2"></i>
                      Purchase In:
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($balance->purchase_in, 2) }} {{ $balance->item->unit }}</td>
                  </tr>
                  <tr>
                    <td>
                      <i class="bx bx-import text-info me-2"></i>
                      Branch Return:
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($balance->branch_return_in, 2) }} {{ $balance->item->unit }}</td>
                  </tr>
                  <tr>
                    <td>
                      <i class="bx bx-edit text-secondary me-2"></i>
                      Adjustment In:
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($balance->adjustment_in, 2) }} {{ $balance->item->unit }}</td>
                  </tr>
                  <tr class="border-top">
                    <td class="fw-bold text-success">Total In:</td>
                    <td class="text-end fw-bold text-success">{{ number_format($balance->stock_in, 2) }} {{ $balance->item->unit }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Stock Out Breakdown -->
          <div class="col-md-6">
            <h6 class="text-danger">Stock Out Details:</h6>
            <div class="table-responsive">
              <table class="table table-sm table-borderless">
                <tbody>
                  <tr>
                    <td>
                      <i class="bx bx-send text-primary me-2"></i>
                      Distribute Out:
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($balance->distribute_out, 2) }} {{ $balance->item->unit }}</td>
                  </tr>
                  <tr>
                    <td>
                      <i class="bx bx-undo text-warning me-2"></i>
                      Purchase Return:
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($balance->purchase_return_out, 2) }} {{ $balance->item->unit }}</td>
                  </tr>
                  <tr>
                    <td>
                      <i class="bx bx-edit text-secondary me-2"></i>
                      Adjustment Out:
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($balance->adjustment_out, 2) }} {{ $balance->item->unit }}</td>
                  </tr>
                  <tr>
                    <td>
                      <i class="bx bx-trash text-danger me-2"></i>
                      Waste Out:
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($balance->waste_out, 2) }} {{ $balance->item->unit }}</td>
                  </tr>
                  <tr class="border-top">
                    <td class="fw-bold text-danger">Total Out:</td>
                    <td class="text-end fw-bold text-danger">{{ number_format($balance->stock_out, 2) }} {{ $balance->item->unit }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
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
  // Add any interactive functionality here
  
  // Tooltip initialization
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  
  // Highlight current stock status
  const stockStatus = '{{ $stockStatus }}';
  const stockCards = document.querySelectorAll('.card');
  
  if (stockStatus === 'empty' || stockStatus === 'low') {
    // Add subtle animation for low/empty stock
    stockCards[3]?.classList.add('border-warning');
  }
});
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

.info-container ul li {
  padding: 0.25rem 0;
}

.table-borderless td {
  border: none !important;
}

.border-warning {
  animation: subtle-pulse 2s infinite;
}

@keyframes subtle-pulse {
  0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

.alert {
  border-left: 4px solid;
}

.alert-success {
  border-left-color: #71dd37;
}

.alert-warning {
  border-left-color: #ffab00;
}

.alert-danger {
  border-left-color: #ff3e1d;
}
</style>
@endpush
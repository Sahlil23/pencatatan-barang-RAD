@extends('layouts.admin')

@section('title', 'Outlet Warehouse - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Outlet Warehouse</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Warehouse Selector + Quick Actions -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('outlet-warehouse.index') }}" class="row g-3 align-items-end">
      <div class="col-lg-4 col-md-6">
        <label class="form-label">Pilih Outlet Warehouse</label>
        <select class="form-select" name="warehouse_id" onchange="this.form.submit()">
          @forelse ($warehouses as $wh)
            <option value="{{ $wh->id }}" {{ optional($selectedWarehouse)->id === $wh->id ? 'selected' : '' }}>
              {{ $wh->warehouse_name }} - {{ $wh->branch->branch_name ?? '-' }} ({{ $wh->warehouse_code }})
            </option>
          @empty
            <option value="">Tidak ada outlet warehouse</option>
          @endforelse
        </select>
      </div>

      <div class="col-lg-8 col-md-6 d-flex gap-2 justify-content-lg-end">
        @if($selectedWarehouse)
          {{-- ✅ FIX: outlet-warehouse.show → outlet-warehouse.stock.index --}}
          <a href="{{ route('outlet-warehouse.stock.index', ['warehouse_id' => $selectedWarehouse->id]) }}" class="btn btn-outline-secondary">
            <i class="bx bx-show me-1"></i> Lihat Stock
          </a>
          {{-- ✅ FIX: outlet-warehouse.receive.create → outlet-warehouse.stock.receive.create --}}
          <a href="{{ route('outlet-warehouse.stock.receive.create', $selectedWarehouse->id) }}" class="btn btn-outline-info">
            <i class="bx bx-import me-1"></i> Terima Stock
          </a>
          {{-- ✅ FIX: outlet-warehouse.adjustment.create → outlet-warehouse.stock.adjustment.create --}}
          <a href="{{ route('outlet-warehouse.stock.adjustment.create', $selectedWarehouse->id) }}" class="btn btn-outline-warning">
            <i class="bx bx-cog me-1"></i> Adjustment
          </a>
          <a href="{{ route('outlet-warehouse.distribution.create', ['warehouse_id' => $selectedWarehouse->id]) }}" class="btn btn-primary">
            <i class="bx bx-export me-1"></i> Kirim ke Kitchen
          </a>
        @endif
      </div>
    </form>
  </div>
</div>

@if(!$selectedWarehouse)
  <div class="alert alert-info">
    <i class="bx bx-info-circle me-2"></i>
    Tidak ada outlet warehouse yang tersedia. Silakan tambah outlet warehouse terlebih dahulu.
  </div>
@else
  <!-- Header Selected Warehouse -->
  <div class="alert alert-primary mb-4" role="alert">
    <div class="d-flex align-items-center">
      <div class="avatar flex-shrink-0 me-3">
        <span class="avatar-initial rounded bg-label-primary">
          <i class="bx bx-store"></i>
        </span>
      </div>
      <div>
        <h6 class="mb-1">{{ $selectedWarehouse->warehouse_name }} 
          <span class="badge bg-label-secondary">{{ $selectedWarehouse->warehouse_code }}</span>
        </h6>
        <div class="text-muted small">
          Branch: <strong>{{ $selectedWarehouse->branch->branch_name ?? '-' }}</strong>
          @if(!empty($selectedWarehouse->address))
            • {{ $selectedWarehouse->address }}
          @endif
        </div>
      </div>
      <div class="ms-auto">
        <span class="badge bg-success">{{ $selectedWarehouse->type_label ?? 'Outlet Warehouse' }}</span>
      </div>
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
          <h3 class="card-title mb-2">{{ $stockSummary['total_items'] ?? 0 }}</h3>
          <small class="text-primary fw-semibold">
            <i class="bx bx-package"></i> Unique Products
          </small>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="card-title d-flex align-items-start justify-content-between">
            <div class="avatar flex-shrink-0">
              <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Total Value" class="rounded" />
            </div>
          </div>
          <span class="fw-semibold d-block mb-1">Total Value</span>
          <h3 class="card-title mb-2">Rp {{ number_format($stockSummary['total_stock_value'] ?? 0, 0, ',', '.') }}</h3>
          <small class="text-success fw-semibold">
            <i class="bx bx-money"></i> Inventory
          </small>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="card-title d-flex align-items-start justify-content-between">
            <div class="avatar flex-shrink-0">
              <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Low Stock" class="rounded" />
            </div>
          </div>
          <span class="fw-semibold d-block mb-1">Low Stock Items</span>
          <h3 class="card-title mb-2">{{ $stockSummary['low_stock_items'] ?? 0 }}</h3>
          <small class="text-warning fw-semibold">
            <i class="bx bx-signal-3"></i> Perlu perhatian
          </small>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="card-title d-flex align-items-start justify-content-between">
            <div class="avatar flex-shrink-0">
              <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="Distribusi Bulan Ini" class="rounded" />
            </div>
          </div>
          <span class="fw-semibold d-block mb-1">Distribusi Bulan Ini</span>
          <h3 class="card-title mb-2">{{ $distributionStats['this_month'] ?? 0 }}</h3>
          <small class="text-info fw-semibold">
            <i class="bx bx-export"></i> Ke Kitchen
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Info -->
  <div class="alert alert-info alert-dismissible mb-4" role="alert">
    <h6 class="alert-heading mb-2">
      <i class="bx bx-info-circle me-2"></i>
      Outlet Warehouse Management
    </h6>
    <p class="mb-0">
      <strong>Kelola Stock:</strong> Terima dari branch warehouse |
      <strong>Distribusi:</strong> Kirim ke kitchen |
      <strong>Adjustment:</strong> Catat selisih, rusak, atau expired
    </p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

  <!-- Lists -->
  <div class="row">
    <!-- Low Stock -->
    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bx bx-down-arrow-circle text-warning me-2"></i>
            Low Stock (Top 10)
          </h5>
          {{-- ✅ FIX: outlet-warehouse.show → outlet-warehouse.stock.index --}}
          <a href="{{ route('outlet-warehouse.stock.index', ['warehouse_id' => $selectedWarehouse->id, 'stock_status' => 'low_stock']) }}" class="btn btn-sm btn-outline-secondary">
            Lihat Semua
          </a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Item</th>
                <th class="text-end">Stock</th>
                <th class="text-end">Min</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($lowStockItems as $ls)
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-warning">
                          <i class="bx bx-package"></i>
                        </span>
                      </div>
                      <div>
                        <strong>{{ $ls->item->item_name ?? '-' }}</strong>
                        <br><small class="text-muted">{{ $ls->item->item_code ?? '-' }}</small>
                      </div>
                    </div>
                  </td>
                  <td class="text-end">
                    <span class="text-danger fw-semibold">{{ number_format($ls->closing_stock ?? 0, 3) }}</span>
                    <small class="text-muted">{{ $ls->item->unit ?? '' }}</small>
                  </td>
                  <td class="text-end">
                    {{-- ✅ FIX: minimum_stock → low_stock_threshold --}}
                    {{ number_format($ls->item->low_stock_threshold ?? 0, 3) }}
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <a href="{{ route('outlet-warehouse.stock.show', [$selectedWarehouse->id, $ls->item_id]) }}" class="btn btn-outline-secondary">
                        <i class="bx bx-show"></i>
                      </a>
                      <a href="{{ route('outlet-warehouse.distribution.create', ['warehouse_id' => $selectedWarehouse->id, 'item_id' => $ls->item_id]) }}" class="btn btn-outline-primary">
                        <i class="bx bx-export"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">Tidak ada item low stock</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pending Distributions -->
    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bx bx-timer text-info me-2"></i>
            Pending Distribusi ke Kitchen
          </h5>
          <a href="{{ route('outlet-warehouse.distribution.index', ['warehouse_id' => $selectedWarehouse->id, 'status' => 'PENDING']) }}" class="btn btn-sm btn-outline-secondary">
            Lihat Semua
          </a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th>Kitchen</th>
                <th>Item</th>
                <th class="text-end">Qty</th>
                <th class="text-center">Status</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($pendingDistributions as $dist)
                <tr>
                  <td>
                    {{ \Carbon\Carbon::parse($dist->transaction_date)->format('d M Y') }}
                    <div class="text-muted small">{{ $dist->reference_no ?? '' }}</div>
                  </td>
                  <td>
                    <span class="badge bg-label-primary">{{ $dist->branch->branch_name ?? '-' }}</span>
                  </td>
                  <td>
                    <div><strong>{{ $dist->item->item_name ?? '-' }}</strong></div>
                    <small class="text-muted">{{ $dist->item->item_code ?? '-' }}</small>
                  </td>
                  <td class="text-end">
                    {{ number_format($dist->quantity ?? 0, 3) }}
                    <small class="text-muted">{{ $dist->item->unit ?? '' }}</small>
                  </td>
                  <td class="text-center">
                    @php
                      $statusMap = [
                        'PENDING' => ['secondary','Pending'],
                        'PREPARED' => ['info','Prepared'],
                        'IN_TRANSIT' => ['primary','In Transit'],
                        'DELIVERED' => ['warning','Delivered'],
                        'RECEIVED' => ['success','Received'],
                        'CANCELLED' => ['danger','Cancelled'],
                      ];
                      $s = $statusMap[$dist->status] ?? ['secondary',$dist->status];
                    @endphp
                    <span class="badge bg-{{ $s[0] }}">{{ $s[1] }}</span>
                  </td>
                  <td class="text-center">
                    <a href="{{ route('outlet-warehouse.distribution.show', $dist->id) }}" class="btn btn-sm btn-outline-secondary">
                      <i class="bx bx-show"></i>
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">Tidak ada distribusi pending</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bx bx-history me-2"></i>
            Transaksi Terbaru
          </h5>
          <div class="d-flex gap-2">
            {{-- ✅ FIX: outlet-warehouse.transactions → outlet-warehouse.stock.transactions --}}
            <a href="{{ route('outlet-warehouse.stock.transactions', $selectedWarehouse->id) }}" class="btn btn-sm btn-outline-secondary">
              Lihat Semua
            </a>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Item</th>
                <th class="text-end">Qty</th>
                <th>User</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentTransactions as $trx)
                <tr>
                  <td>
                    {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y H:i') }}
                    <div class="text-muted small">{{ $trx->reference_no ?? '-' }}</div>
                  </td>
                  <td>
                    <span class="badge bg-label-secondary">{{ str_replace('_',' ', $trx->transaction_type ?? 'Unknown') }}</span>
                  </td>
                  <td>
                    <strong>{{ $trx->item->item_name ?? '-' }}</strong>
                    <div class="text-muted small">{{ $trx->item->item_code ?? '-' }}</div>
                  </td>
                  <td class="text-end">
                    @php $q = $trx->quantity ?? 0; @endphp
                    <span class="{{ $q >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                      {{ number_format($q, 3) }}
                    </span>
                    <small class="text-muted">{{ $trx->item->unit ?? '' }}</small>
                  </td>
                  <td>
                    <span class="badge bg-label-info">{{ $trx->user->name ?? '-' }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">Belum ada transaksi</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <div class="text-muted small">
            Hari ini: <strong>{{ $distributionStats['today'] ?? 0 }}</strong> distribusi •
            Minggu ini: <strong>{{ $distributionStats['this_week'] ?? 0 }}</strong> •
            Bulan ini: <strong>{{ $distributionStats['this_month'] ?? 0 }}</strong>
          </div>
          <div>
            <a href="{{ route('outlet-warehouse.distribution.index', ['warehouse_id' => $selectedWarehouse->id]) }}" class="btn btn-sm btn-outline-primary">
              Riwayat Distribusi
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif
@endsection

@push('styles')
<style>
  .badge { font-size: 0.65em; }
  .table-hover tbody tr:hover { background-color: rgba(105, 108, 255, 0.04); }
  .avatar-initial {
    display:flex; align-items:center; justify-content:center;
    width:40px; height:40px; font-size:18px;
  }
  .card:hover { transform: translateY(-2px); transition: .2s; box-shadow: 0 4px 25px 0 rgba(0,0,0,.1); }
  .text-end { text-align: end; }
  @media (max-width: 768px) {
    .table-responsive { font-size: .875rem; }
    .avatar-initial { width:32px; height:32px; font-size:14px; }
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(function(alert) {
      setTimeout(function() {
        try { new bootstrap.Alert(alert).close(); } catch(e) {}
      }, 5000);
    });
  });
</script>
@endpush
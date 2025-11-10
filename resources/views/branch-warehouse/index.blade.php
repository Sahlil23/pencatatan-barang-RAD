@extends('layouts.admin')

@section('title', 'Branch Warehouse - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Branch Warehouse</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Stats Cards - Sama seperti warehouses/index.blade.php -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="Total Warehouses" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Warehouse</span>
        <h3 class="card-title mb-2">{{ $warehouses->total() ?? 0 }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-building"></i> All Branches
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Total Items" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Items</span>
        <h3 class="card-title mb-2">{{ $totalItems ?? 0 }}</h3>
        <small class="text-success fw-semibold">
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
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Total Stock" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Quantity</span>
        <h3 class="card-title mb-2">{{ number_format($totalStock ?? 0, 2) }}</h3>
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
            <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="Total Value" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Value</span>
        <h3 class="card-title mb-2">Rp {{ number_format($totalValue ?? 0, 0, ',', '.') }}</h3>
        <small class="text-warning fw-semibold">
          <i class="bx bx-money"></i> Inventory
        </small>
      </div>
    </div>
  </div>
</div>

<!-- Branch Warehouse Management Info -->
<div class="alert alert-info alert-dismissible mb-4" role="alert">
  <h6 class="alert-heading mb-2">
    <i class="bx bx-info-circle me-2"></i>
    Branch Warehouse Management
  </h6>
  <p class="mb-0">
    <strong>Kelola Stock:</strong> Terima dan kelola stock dari central warehouse |
    <strong>Distribusi:</strong> Kirim stock ke outlet/kitchen cabang |
    <strong>Adjustment:</strong> Catat rusak, expired, atau return ke central
  </p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('branch-warehouse.index') }}" class="row g-3">
      <!-- Search -->
      <div class="col-md-4">
        <label class="form-label">Cari Warehouse</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nama warehouse atau branch...">
        </div>
      </div>
      
      <!-- Branch Filter -->
      <div class="col-md-3">
        <label class="form-label">Branch</label>
        <select class="form-select" name="branch">
          <option value="">Semua Branch</option>
          @foreach($branches ?? [] as $branch)
            <option value="{{ $branch->id }}" {{ request('branch') == $branch->id ? 'selected' : '' }}>
              {{ $branch->branch_name }}
            </option>
          @endforeach
        </select>
      </div>
      
      <!-- Status Filter -->
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="">Semua Status</option>
          <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
          <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
          <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
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
          <a href="{{ route('branch-warehouse.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-reset"></i>
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Main Table Card -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bx bx-building me-2"></i>
      Daftar Warehouse Cabang
      @if(request()->hasAny(['search', 'branch', 'status']))
        <span class="badge bg-label-primary">Filtered</span>
      @endif
    </h5>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
        <i class="bx bx-download me-1"></i>
        Export CSV
      </button>
      <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i>
        Tambah Warehouse
      </a>
    </div>
  </div>
  
  <div class="table-responsive text-nowrap">
    <table class="table table-hover" id="branchWarehousesTable">
      <thead class="table-light">
        <tr>
          <th style="width: 80px;">
            <i class="bx bx-hash me-1"></i>
            Kode
          </th>
          <th>
            <i class="bx bx-building me-1"></i>
            Warehouse
          </th>
          <th>
            <i class="bx bx-home me-1"></i>
            Branch
          </th>
          <th class="text-center">
            <i class="bx bx-package me-1"></i>
            Items
          </th>
          <th class="text-right">
            <i class="bx bx-box me-1"></i>
            Stock
          </th>
          <!-- <th class="text-right">
            <i class="bx bx-money me-1"></i>
            Value
          </th>
          <th class="text-center">
            <i class="bx bx-signal-3 me-1"></i>
            Status
          </th> -->
          <th class="text-center">
            <i class="bx bx-cog me-1"></i>
            Aksi
          </th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse ($warehouses as $warehouse)
        <tr>
          <!-- Warehouse Code -->
          <td>
            <span class="badge bg-label-secondary">{{ $warehouse->warehouse_code }}</span>
          </td>
          
          <!-- Warehouse Info -->
          <td>
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="bx bx-building"></i>
                </span>
              </div>
              <div>
                <strong>{{ $warehouse->warehouse_name }}</strong>
                <br><small class="text-muted">
                  <i class="bx bx-time"></i>
                  {{ $warehouse->created_at->format('d M Y') }}
                </small>
              </div>
            </div>
          </td>
          
          <!-- Branch -->
          <td>
            @if($warehouse->branch)
              <div>
                <span class="badge bg-label-primary">{{ $warehouse->branch->branch_name }}</span>
                <br><small class="text-muted">{{ $warehouse->branch->city ?? '-' }}</small>
              </div>
            @else
              <span class="text-muted">-</span>
            @endif
          </td>
          
          <!-- Items Count -->
          <td class="text-center">
            <span class="badge bg-label-success">
              {{ $warehouseStats[$warehouse->id]['total_items'] ?? 0 }}
            </span>
          </td>
          
          <!-- Total Stock -->
          <td class="text-right">
            <div>
              <span class="fw-bold">{{ number_format($warehouseStats[$warehouse->id]['total_stock'] ?? 0, 2) }}</span>
              <br><small class="text-muted">unit(s)</small>
            </div>
          </td>
          
          <!-- Total Value -->
          <!-- <td class="text-right">
            <div>
              <strong>Rp {{ number_format($warehouseStats[$warehouse->id]['total_value'] ?? 0, 0, ',', '.') }}</strong>
            </div>
          </td> -->
          
          <!-- Status -->
            <!-- <td class="text-center">
              @php
                $statusConfig = [
                  'ACTIVE' => ['class' => 'success', 'text' => 'Active'],
                  'INACTIVE' => ['class' => 'warning', 'text' => 'Inactive'],
                  'MAINTENANCE' => ['class' => 'danger', 'text' => 'Maintenance'],
                  'active' => ['class' => 'success', 'text' => 'Active'],
                  'inactive' => ['class' => 'warning', 'text' => 'Inactive'],
                  'maintenance' => ['class' => 'danger', 'text' => 'Maintenance']
                ];
                $config = $statusConfig[$warehouse->status] ?? ['class' => 'secondary', 'text' => ucfirst($warehouse->status)];
              @endphp
              <span class="badge bg-{{ $config['class'] }}">{{ $config['text'] }}</span>
            </td> -->
          
          <!-- Actions -->
          <td class="text-center">
            <div class="btn-group" role="group">
              <a href="{{ route('branch-warehouse.show', $warehouse->id) }}" class="btn btn-sm btn-info" title="View Details">
                <i class="bx bx-show"></i>
              </a>
              <a href="{{ route('branch-warehouse.adjust-form', $warehouse->id) }}" class="btn btn-sm btn-primary" title="Adjust Stock">
                <i class="bx bx-edit-alt"></i>
              </a>
              <a href="{{ route('branch-warehouse.distribute-form', $warehouse->id) }}" class="btn btn-sm btn-success" title="Distribute">
                <i class="bx bx-share"></i>
              </a>
            </div>
          </td>

                
        </tr>
        @empty
        <tr>
          <td colspan="8" class="text-center py-4">
            <div class="d-flex flex-column align-items-center">
              <i class="bx bx-building" style="font-size: 48px; color: #ddd;"></i>
              <h6 class="mt-2 text-muted">
                @if(request()->hasAny(['search', 'branch', 'status']))
                  Tidak ada warehouse yang sesuai dengan filter
                @else
                  Belum ada data warehouse cabang
                @endif
              </h6>
              <p class="text-muted mb-3">
                @if(request()->hasAny(['search', 'branch', 'status']))
                  Coba ubah filter atau kata kunci pencarian
                @else
                  Mulai dengan menambahkan warehouse cabang pertama Anda
                @endif
              </p>
              @if(!request()->hasAny(['search', 'branch', 'status']))
              <a href="{{ route('branch-warehouse.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>
                Tambah Warehouse Pertama
              </a>
              @else
              <a href="{{ route('branch-warehouse.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-reset me-1"></i>
                Reset Filter
              </a>
              @endif
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  @if($warehouses->hasPages())
  <div class="card-footer">
    <div class="d-flex justify-content-between align-items-center">
      <div class="text-muted">
        Showing {{ $warehouses->firstItem() }} to {{ $warehouses->lastItem() }} of {{ $warehouses->total() }} warehouses
      </div>
      {{ $warehouses->links() }}
    </div>
  </div>
  @endif
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exportModalLabel">
          <i class="bx bx-download me-2"></i>
          Export Branch Warehouse Data
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
            <label class="form-label">Filter Data</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="include_inactive" id="include_inactive">
              <label class="form-check-label" for="include_inactive">
                Include inactive warehouses
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="include_details" id="include_details" checked>
              <label class="form-check-label" for="include_details">
                Include detailed information (branch, stock, etc.)
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-download me-1"></i>
            Export Data
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
});
</script>
@endpush

@push('styles')
<style>
  .badge {
    font-size: 0.65em;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(105, 108, 255, 0.04);
  }

  .avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    font-size: 18px;
  }

  /* Status badge colors */
  .badge.bg-success { background-color: #71dd37 !important; }
  .badge.bg-warning { background-color: #ffab00 !important; }
  .badge.bg-danger { background-color: #ff3e1d !important; }
  .badge.bg-info { background-color: #03c3ec !important; }
  .badge.bg-primary { background-color: #696cff !important; }

  /* Card hover effects */
  .card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.1);
  }

  /* Table responsive enhancements */
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
    .table th:nth-child(5),
    .table td:nth-child(5) {
      display: none;
    }
  }

  /* Text alignment */
  .text-right {
    text-align: right;
  }

  .text-center {
    text-align: center;
  }
</style>
@endpush
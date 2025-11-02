@extends('layouts.admin')

@section('title', 'Data Warehouse - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Data Warehouse</li>
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
            <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="Total Warehouses" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Warehouse</span>
        <h3 class="card-title mb-2">{{ $warehouses->total() ?? 0 }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-buildings"></i> All Locations
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Central Warehouses" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Central Warehouse</span>
        <h3 class="card-title mb-2">{{ $warehouses->where('warehouse_type', 'central')->count() }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-store"></i> Central
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Branch Warehouses" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Branch Warehouse</span>
        <h3 class="card-title mb-2">{{ $warehouses->where('warehouse_type', 'branch')->count() }}</h3>
        <small class="text-info fw-semibold">
          <i class="bx bx-home"></i> Branches
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="Active Warehouses" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Active</span>
        <h3 class="card-title mb-2">{{ $warehouses->where('status', 'active')->count() }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-check-circle"></i> Operational
        </small>
      </div>
    </div>
  </div>
</div>

<!-- Warehouse Management Info -->
<div class="alert alert-info alert-dismissible mb-4" role="alert">
  <h6 class="alert-heading mb-2">
    <i class="bx bx-info-circle me-2"></i>
    Warehouse Management System
  </h6>
  <p class="mb-0">
    <strong>Central:</strong> Pusat distribusi utama untuk semua cabang |
    <strong>Branch:</strong> Gudang masing-masing cabang untuk operasional harian |
    <strong>Status:</strong> Monitoring kondisi operasional gudang
  </p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('warehouses.index') }}" class="row g-3">
      <!-- Search -->
      <div class="col-md-4">
        <label class="form-label">Cari Warehouse</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nama warehouse atau kode...">
        </div>
      </div>
      
      <!-- Type Filter -->
      <div class="col-md-3">
        <label class="form-label">Tipe Warehouse</label>
        <select class="form-select" name="type">
          <option value="">Semua Tipe</option>
          <option value="central" {{ request('type') == 'central' ? 'selected' : '' }}>Central Warehouse</option>
          <option value="branch" {{ request('type') == 'branch' ? 'selected' : '' }}>Branch Warehouse</option>
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
          <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary">
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
      <i class="bx bx-buildings me-2"></i>
      Data Warehouse
      @if(request()->hasAny(['search', 'type', 'status']))
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
    <table class="table table-hover" id="warehousesTable">
      <thead class="table-light">
        <tr>
          <th style="width: 100px;">
            <i class="bx bx-hash me-1"></i>
            Kode
          </th>
          <th>
            <i class="bx bx-buildings me-1"></i>
            Warehouse
          </th>
          <th class="text-center">
            <i class="bx bx-category me-1"></i>
            Tipe
          </th>
          <th>
            <i class="bx bx-map me-1"></i>
            Lokasi
          </th>
          <th class="text-center">
            <i class="bx bx-user me-1"></i>
            PIC
          </th>
          <th class="text-center">
            <i class="bx bx-cube me-1"></i>
            Kapasitas
          </th>
          <th class="text-center">
            <i class="bx bx-signal-3 me-1"></i>
            Status
          </th>
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
                <span class="avatar-initial rounded bg-label-{{ $warehouse->warehouse_type === 'central' ? 'primary' : 'info' }}">
                  <i class="bx {{ $warehouse->warehouse_type === 'central' ? 'bx-store' : 'bx-home' }}"></i>
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
          
          <!-- Type -->
          <td class="text-center">
            <span class="badge bg-{{ $warehouse->warehouse_type === 'central' ? 'primary' : 'info' }}">
              {{ $warehouse->warehouse_type === 'central' ? 'Central' : 'Branch' }}
            </span>
          </td>
          
          <!-- Location -->
          <td>
            <div>
              <i class="bx bx-map text-muted me-1"></i>
              {{ Str::limit($warehouse->address, 50) }}
              @if($warehouse->branch_id)
                <br><small class="text-muted">
                  <i class="bx bx-store"></i>
                  Branch ID: {{ $warehouse->branch_id }}
                </small>
              @endif
            </div>
          </td>
          
          <!-- PIC -->
          <td class="text-center">
            @if($warehouse->pic_name)
              <div>
                <span class="fw-bold">{{ $warehouse->pic_name }}</span>
                @if($warehouse->pic_phone)
                  <br><small class="text-muted">{{ $warehouse->pic_phone }}</small>
                @endif
              </div>
            @else
              <span class="text-muted">-</span>
            @endif
          </td>
          
          <!-- Capacity -->
          <td class="text-center">
            @if($warehouse->capacity_m2 || $warehouse->capacity_volume)
              <div>
                @if($warehouse->capacity_m2)
                  <span class="fw-bold">{{ number_format($warehouse->capacity_m2, 0) }} m²</span>
                @endif
                @if($warehouse->capacity_volume)
                  <br><small class="text-muted">{{ number_format($warehouse->capacity_volume, 0) }} m³</small>
                @endif
              </div>
            @else
              <span class="text-muted">-</span>
            @endif
          </td>
          
          <!-- Status -->
          <td class="text-center">
            @php
              $statusConfig = [
                'active' => ['class' => 'success', 'text' => 'Active'],
                'inactive' => ['class' => 'warning', 'text' => 'Inactive'],
                'maintenance' => ['class' => 'danger', 'text' => 'Maintenance']
              ];
              $config = $statusConfig[$warehouse->status] ?? ['class' => 'secondary', 'text' => ucfirst($warehouse->status)];
            @endphp
            <span class="badge bg-{{ $config['class'] }}">{{ $config['text'] }}</span>
          </td>
          
          <!-- Actions -->
          <td class="text-center">
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('warehouses.show', $warehouse->id) }}">
                  <i class="bx bx-show me-1"></i> 
                  Lihat Detail
                </a>
                <a class="dropdown-item" href="{{ route('warehouses.edit', $warehouse->id) }}">
                  <i class="bx bx-edit-alt me-1"></i> 
                  Edit
                </a>
                <div class="dropdown-divider"></div>
                @if($warehouse->status !== 'active')
                <form action="{{ route('warehouses.change-status', $warehouse->id) }}" method="POST" class="d-inline">
                  @csrf
                  <input type="hidden" name="status" value="active">
                  <button type="submit" class="dropdown-item text-success"
                          onclick="return confirm('Aktifkan warehouse {{ $warehouse->warehouse_name }}?')">
                    <i class="bx bx-check me-1"></i> 
                    Aktifkan
                  </button>
                </form>
                @endif
                @if($warehouse->status === 'active')
                <form action="{{ route('warehouses.change-status', $warehouse->id) }}" method="POST" class="d-inline">
                  @csrf
                  <input type="hidden" name="status" value="maintenance">
                  <button type="submit" class="dropdown-item text-warning"
                          onclick="return confirm('Set warehouse {{ $warehouse->warehouse_name }} ke maintenance?')">
                    <i class="bx bx-wrench me-1"></i> 
                    Maintenance
                  </button>
                </form>
                @endif
                <div class="dropdown-divider"></div>
                <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger" 
                          onclick="return confirm('Apakah Anda yakin ingin menghapus warehouse {{ $warehouse->warehouse_name }}? Data terkait akan terpengaruh.')">
                    <i class="bx bx-trash me-1"></i> 
                    Hapus
                  </button>
                </form>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="text-center py-4">
            <div class="d-flex flex-column align-items-center">
              <i class="bx bx-buildings" style="font-size: 48px; color: #ddd;"></i>
              <h6 class="mt-2 text-muted">
                @if(request()->hasAny(['search', 'type', 'status']))
                  Tidak ada warehouse yang sesuai dengan filter
                @else
                  Belum ada data warehouse
                @endif
              </h6>
              <p class="text-muted mb-3">
                @if(request()->hasAny(['search', 'type', 'status']))
                  Coba ubah filter atau kata kunci pencarian
                @else
                  Mulai dengan menambahkan warehouse pertama Anda
                @endif
              </p>
              @if(!request()->hasAny(['search', 'type', 'status']))
              <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>
                Tambah Warehouse Pertama
              </a>
              @else
              <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary">
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
          Export Warehouse Data
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('warehouses.export') }}" method="GET">
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
                Include detailed information (PIC, capacity, etc.)
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

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bulkActionModalLabel">
          <i class="bx bx-check-square me-2"></i>
          Bulk Action - Warehouses
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('warehouses.bulk-action') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Pilih Aksi <span class="text-danger">*</span></label>
            <select class="form-select" name="action" required>
              <option value="">Pilih aksi...</option>
              <option value="activate">Aktifkan Warehouse</option>
              <option value="deactivate">Nonaktifkan Warehouse</option>
              <option value="maintenance">Set ke Maintenance</option>
              <option value="delete">Hapus Warehouse</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Warehouses yang Dipilih</label>
            <div id="selectedWarehousesList" class="border rounded p-2 bg-light">
              <small class="text-muted">Pilih warehouse dari tabel terlebih dahulu</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-check me-1"></i>
            Jalankan Aksi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Bulk selection functionality (similar to items)
let selectedWarehouses = [];

function toggleWarehouseSelection(warehouseId, warehouseName) {
  const index = selectedWarehouses.findIndex(warehouse => warehouse.id === warehouseId);
  
  if (index > -1) {
    selectedWarehouses.splice(index, 1);
  } else {
    selectedWarehouses.push({ id: warehouseId, name: warehouseName });
  }
  
  updateBulkActionButton();
  updateSelectedWarehousesList();
}

function updateBulkActionButton() {
  const bulkActionBtn = document.getElementById('bulkActionBtn');
  if (bulkActionBtn) {
    if (selectedWarehouses.length > 0) {
      bulkActionBtn.style.display = 'inline-block';
      bulkActionBtn.textContent = `Bulk Action (${selectedWarehouses.length})`;
    } else {
      bulkActionBtn.style.display = 'none';
    }
  }
}

function updateSelectedWarehousesList() {
  const listContainer = document.getElementById('selectedWarehousesList');
  if (selectedWarehouses.length === 0) {
    listContainer.innerHTML = '<small class="text-muted">Pilih warehouse dari tabel terlebih dahulu</small>';
  } else {
    listContainer.innerHTML = selectedWarehouses.map(warehouse => 
      `<div class="badge bg-primary me-1 mb-1">${warehouse.name}</div>`
    ).join('');
  }
}

function showBulkActionModal() {
  if (selectedWarehouses.length === 0) {
    alert('Pilih minimal satu warehouse terlebih dahulu');
    return;
  }
  
  // Add hidden inputs for selected warehouses
  const form = document.querySelector('#bulkActionModal form');
  const existingInputs = form.querySelectorAll('input[name="warehouse_ids[]"]');
  existingInputs.forEach(input => input.remove());
  
  selectedWarehouses.forEach(warehouse => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'warehouse_ids[]';
    input.value = warehouse.id;
    form.appendChild(input);
  });
  
  const modal = new bootstrap.Modal(document.getElementById('bulkActionModal'));
  modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
  // Initialize tooltips if any
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
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
}
</style>
@endpush
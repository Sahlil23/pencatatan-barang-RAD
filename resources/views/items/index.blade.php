@extends('layouts.admin')

@section('title', 'Data Master Item - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Data Master Item</li>
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
        <span class="fw-semibold d-block mb-1">Total Item</span>
        <h3 class="card-title mb-2">{{ $stats['total'] ?? 0 }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-package"></i> Master Data
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Categories" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Kategori</span>
        <h3 class="card-title mb-2">{{ $categories->count() }}</h3>
        <small class="text-info fw-semibold">
          <i class="bx bx-category"></i> Total
        </small>
      </div>
    </div>
  </div>
</div>

<!-- Master Data Info -->
<div class="alert alert-info alert-dismissible mb-4" role="alert">
  <h6 class="alert-heading mb-2">
    <i class="bx bx-info-circle me-2"></i>
    Data Master Item Management
  </h6>
  <p class="mb-0">
    <strong>Focus:</strong> Kelola data master item, kategori, dan informasi dasar |
    <strong>Status:</strong> Data referensi untuk semua modul |
    <strong>Note:</strong> Data stok dikelola di modul warehouse masing-masing
  </p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('items.index') }}" class="row g-3">
      <!-- Search -->
      <div class="col-md-4">
        <label class="form-label">Cari Item</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nama item atau kode...">
        </div>
      </div>
      
      <!-- Category Filter -->
      <div class="col-md-3">
        <label class="form-label">Kategori</label>
        <select class="form-select" name="category_id">
          <option value="">Semua Kategori</option>
          @foreach($categories as $category)
          <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
            {{ $category->category_name }}
          </option>
          @endforeach
        </select>
      </div>
      
      <!-- Status Filter -->
      <div class="col-md-3">
        <label class="form-label">Status Item</label>
        <select class="form-select" name="status">
          <option value="">Semua Status</option>
          <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
          <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Nonaktif</option>
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
          <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
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
      <i class="bx bx-package me-2"></i>
      Data Master Item
      @if(request()->hasAny(['search', 'category_id', 'status']))
        <span class="badge bg-label-primary">Filtered</span>
      @endif
    </h5>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="bx bx-upload me-1"></i>
        Import CSV
      </button>
      <a href="{{ route('items.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i>
        Tambah Item
      </a>
    </div>
  </div>
  
  <div class="table-responsive text-nowrap">
    <table class="table table-hover" id="itemsTable">
      <thead class="table-light">
        <tr>
          <th style="width: 80px;">
            <i class="bx bx-hash me-1"></i>
            Kode
          </th>
          <th>
            <i class="bx bx-package me-1"></i>
            Item
          </th>
          <th>
            <i class="bx bx-category me-1"></i>
            Kategori
          </th>
          <th class="text-center">
            <i class="bx bx-cube me-1"></i>
            Unit
          </th>
          <th class="text-center">
            <i class="bx bx-line-chart me-1"></i>
            Threshold
          </th>
          <!-- <th class="text-center">
            <i class="bx bx-dollar me-1"></i>
            Unit Cost
          </th> -->
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
        @forelse ($items as $item)
        <tr>
          <!-- Item Code -->
          <td>
            <span class="badge bg-label-secondary">{{ $item->sku }}</span>
          </td>
          
          <!-- Item Info -->
          <td>
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ ['primary', 'success', 'info', 'warning', 'danger'][$loop->index % 5] }}">
                  <i class="bx bx-box"></i>
                </span>
              </div>
              <div>
                <strong>{{ $item->item_name }}</strong>
                <br><small class="text-muted">
                  <i class="bx bx-time"></i>
                  {{ $item->created_at->format('d M Y') }}
                </small>
              </div>
            </div>
          </td>
          
          <!-- Category -->
          <td>
            @if($item->category)
              <div class="d-flex align-items-center">
                <!-- <i class="bx bx-category text-primary me-2"></i> -->
                <div>
                  <span>{{ $item->category->category_name }}</span>
                  <!-- <br><small class="text-muted">{{ $item->category->category_code ?? '-' }}</small> -->
                </div>
              </div>
            @else
              <span class="text-muted">
                <i class="bx bx-category-alt me-1"></i>
                Tidak ada kategori
              </span>
            @endif
          </td>
          
          <!-- Unit -->
          <td class="text-center">
            <span class="fw-bold text-info">
              {{ $item->unit }}
            </span>
          </td>
          
          <!-- Low Stock Threshold -->
          <td class="text-center">
            <span class="fw-bold">
              {{ number_format($item->low_stock_threshold, 0) }}
            </span>
            <br><small class="text-muted">Min Stock</small>
          </td>
          
          <!-- Unit Cost -->
          <!-- <td class="text-center">
            @if($item->unit_cost)
              <span class="fw-bold text-success">
                Rp {{ number_format($item->unit_cost, 0) }}
              </span>
            @else
              <span class="text-muted">-</span>
            @endif
          </td> -->
          
          <!-- Status -->
          <td class="text-center">
            @if($item->status === 'ACTIVE')
              <span class="badge bg-success">Aktif</span>
            @else
              <span class="badge bg-warning">Nonaktif</span>
            @endif
          </td>
          
          <!-- Actions -->
          <td class="text-center">
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('items.show', $item->id) }}">
                  <i class="bx bx-show me-1"></i> 
                  Lihat Detail
                </a>
                <a class="dropdown-item" href="{{ route('items.edit', $item->id) }}">
                  <i class="bx bx-edit-alt me-1"></i> 
                  Edit
                </a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger" 
                          onclick="return confirm('Apakah Anda yakin ingin menghapus item {{ $item->item_name }}? Ini akan menghapus semua data terkait.')">
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
              <i class="bx bx-package" style="font-size: 48px; color: #ddd;"></i>
              <h6 class="mt-2 text-muted">
                @if(request()->hasAny(['search', 'category_id', 'status']))
                  Tidak ada item yang sesuai dengan filter
                @else
                  Belum ada data item
                @endif
              </h6>
              <p class="text-muted mb-3">
                @if(request()->hasAny(['search', 'category_id', 'status']))
                  Coba ubah filter atau kata kunci pencarian
                @else
                  Mulai dengan menambahkan item pertama Anda
                @endif
              </p>
              @if(!request()->hasAny(['search', 'category_id', 'status']))
              <a href="{{ route('items.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>
                Tambah Item Pertama
              </a>
              @else
              <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
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

  <x-simple-pagination :items="$items" type="item" />

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importModalLabel">
          <i class="bx bx-upload me-2"></i>
          Import Item dari CSV
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
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
          Bulk Action
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="#}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Pilih Aksi <span class="text-danger">*</span></label>
            <select class="form-select" name="action" required>
              <option value="">Pilih aksi...</option>
              <option value="activate">Aktifkan Item</option>
              <option value="deactivate">Nonaktifkan Item</option>
              <option value="delete">Hapus Item</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Item yang Dipilih</label>
            <div id="selectedItemsList" class="border rounded p-2 bg-light">
              <small class="text-muted">Pilih item dari tabel terlebih dahulu</small>
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
// Bulk selection functionality
let selectedItems = [];

function toggleItemSelection(itemId, itemName) {
  const index = selectedItems.findIndex(item => item.id === itemId);
  
  if (index > -1) {
    selectedItems.splice(index, 1);
  } else {
    selectedItems.push({ id: itemId, name: itemName });
  }
  
  updateBulkActionButton();
  updateSelectedItemsList();
}

function updateBulkActionButton() {
  const bulkActionBtn = document.getElementById('bulkActionBtn');
  if (bulkActionBtn) {
    if (selectedItems.length > 0) {
      bulkActionBtn.style.display = 'inline-block';
      bulkActionBtn.textContent = `Bulk Action (${selectedItems.length})`;
    } else {
      bulkActionBtn.style.display = 'none';
    }
  }
}

function updateSelectedItemsList() {
  const listContainer = document.getElementById('selectedItemsList');
  if (selectedItems.length === 0) {
    listContainer.innerHTML = '<small class="text-muted">Pilih item dari tabel terlebih dahulu</small>';
  } else {
    listContainer.innerHTML = selectedItems.map(item => 
      `<div class="badge bg-primary me-1 mb-1">${item.name}</div>`
    ).join('');
  }
}

function showBulkActionModal() {
  if (selectedItems.length === 0) {
    alert('Pilih minimal satu item terlebih dahulu');
    return;
  }
  
  // Add hidden inputs for selected items
  const form = document.querySelector('#bulkActionModal form');
  const existingInputs = form.querySelectorAll('input[name="item_ids[]"]');
  existingInputs.forEach(input => input.remove());
  
  selectedItems.forEach(item => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'item_ids[]';
    input.value = item.id;
    form.appendChild(input);
  });
  
  const modal = new bootstrap.Modal(document.getElementById('bulkActionModal'));
  modal.show();
}

// Add checkbox column and bulk action button (optional enhancement)
document.addEventListener('DOMContentLoaded', function() {
  // Add bulk action button to header if needed
  const headerActions = document.querySelector('.card-header .d-flex.gap-2');
  if (headerActions && selectedItems.length === 0) {
    const bulkBtn = document.createElement('button');
    bulkBtn.type = 'button';
    bulkBtn.className = 'btn btn-outline-primary btn-sm';
    bulkBtn.id = 'bulkActionBtn';
    bulkBtn.style.display = 'none';
    bulkBtn.onclick = showBulkActionModal;
    bulkBtn.innerHTML = '<i class="bx bx-check-square me-1"></i> Bulk Action';
    headerActions.insertBefore(bulkBtn, headerActions.firstChild);
  }
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

/* Selection checkbox styling */
.item-checkbox {
  transform: scale(1.2);
}

.selected-row {
  background-color: rgba(105, 108, 255, 0.1) !important;
}

/* Status badge colors */
.badge.bg-success { background-color: #71dd37 !important; }
.badge.bg-warning { background-color: #ffab00 !important; }
.badge.bg-danger { background-color: #ff3e1d !important; }
.badge.bg-info { background-color: #03c3ec !important; }
</style>
@endpush
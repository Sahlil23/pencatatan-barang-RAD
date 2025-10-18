@extends('layouts.admin')

@section('title', 'Daftar Item - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Item</li>
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
        <h3 class="card-title mb-2">{{ $items->total() }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-package"></i> Item
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Low Stock" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Stok Menipis</span>
        @php $lowStockCount = App\Models\Item::lowStock()->count(); @endphp
        <h3 class="card-title mb-2 {{ $lowStockCount > 0 ? 'text-warning' : 'text-success' }}">{{ $lowStockCount }}</h3>
        <small class="{{ $lowStockCount > 0 ? 'text-warning' : 'text-success' }} fw-semibold">
          <i class="bx {{ $lowStockCount > 0 ? 'bx-error' : 'bx-check' }}"></i> 
          {{ $lowStockCount > 0 ? 'Perlu Perhatian' : 'Aman' }}
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="Out of Stock" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Stok Habis</span>
        @php $outOfStockCount = App\Models\Item::outOfStock()->count(); @endphp
        <h3 class="card-title mb-2 {{ $outOfStockCount > 0 ? 'text-danger' : 'text-success' }}">{{ $outOfStockCount }}</h3>
        <small class="{{ $outOfStockCount > 0 ? 'text-danger' : 'text-success' }} fw-semibold">
          <i class="bx {{ $outOfStockCount > 0 ? 'bx-x' : 'bx-check' }}"></i> 
          {{ $outOfStockCount > 0 ? 'Stok Kosong' : 'Tersedia' }}
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
          <i class="bx bx-category"></i> Aktif
        </small>
      </div>
    </div>
  </div>
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
          <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nama item atau SKU...">
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
      
      <!-- Stock Status Filter -->
      <div class="col-md-3">
        <label class="form-label">Status Stok</label>
        <select class="form-select" name="stock_status">
          <option value="">Semua Status</option>
          <option value="in" {{ request('stock_status') == 'in' ? 'selected' : '' }}>Stok Tersedia</option>
          <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stok Menipis</option>
          <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Stok Habis</option>
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
      Daftar Item
      @if(request()->hasAny(['search', 'category_id', 'stock_status']))
        <span class="badge bg-label-primary">Filtered</span>
      @endif
    </h5>
    <div class="d-flex gap-2">
      <a href="{{ route('items.low-stock') }}" class="btn btn-outline-warning btn-sm">
        <i class="bx bx-error me-1"></i>
        Stok Menipis
      </a>
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
            SKU
          </th>
          <th>
            <i class="bx bx-package me-1"></i>
            Item
          </th>
          <th>
            <i class="bx bx-category me-1"></i>
            Kategori
          </th>
          <th>
            <i class="bx bx-group me-1"></i>
            Supplier
          </th>
          <th class="text-center">
            <i class="bx bx-box me-1"></i>
            Stok
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
        @forelse ($items as $item)
        <tr>
          <td>
            <span class="badge bg-label-secondary">{{ $item->sku }}</span>
          </td>
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
                  <i class="bx bx-cube"></i>
                  {{ $item->unit }}
                </small>
              </div>
            </div>
          </td>
          <td>
            @if($item->category)
              <div class="d-flex align-items-center">
                <i class="bx bx-category text-primary me-2"></i>
                <span>{{ $item->category->category_name }}</span>
              </div>
            @else
              <span class="text-muted">
                <i class="bx bx-category-alt me-1"></i>
                Tidak ada kategori
              </span>
            @endif
          </td>
          <td>
            @if($item->supplier)
              <div class="d-flex align-items-center">
                <i class="bx bx-store text-success me-2"></i>
                <div>
                  <span>{{ $item->supplier->supplier_name }}</span>
                  @if($item->supplier->contact_person)
                    <br><small class="text-muted">{{ $item->supplier->contact_person }}</small>
                  @endif
                </div>
              </div>
            @else
              <span class="text-muted">
                <i class="bx bx-store-alt me-1"></i>
                Tidak ada supplier
              </span>
            @endif
          </td>
          <td class="text-center">
            <div class="d-flex flex-column align-items-center">
              <span class="fw-bold text-{{ $item->stock_status_color }}">
                {{ number_format($item->current_stock, 0) }}
              </span>
              <small class="text-muted">
                Min: {{ number_format($item->low_stock_threshold, 0) }}
              </small>
            </div>
          </td>
          <td class="text-center">
            <span class="badge bg-{{ $item->stock_status_color }}">
              {{ $item->stock_status }}
            </span>
          </td>
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
                <a class="dropdown-item" href="#" onclick="showStockAdjustment({{ $item->id }}, '{{ $item->item_name }}', {{ $item->current_stock }})">
                  <i class="bx bx-transfer me-1"></i> 
                  Sesuaikan Stok
                </a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger" 
                          onclick="return confirm('Apakah Anda yakin ingin menghapus item {{ $item->item_name }}?')"
                          {{ $item->stockTransactions()->count() > 0 ? 'disabled title="Tidak dapat menghapus item yang memiliki riwayat transaksi"' : '' }}>
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
          <td colspan="7" class="text-center py-4">
            <div class="d-flex flex-column align-items-center">
              <i class="bx bx-package" style="font-size: 48px; color: #ddd;"></i>
              <h6 class="mt-2 text-muted">
                @if(request()->hasAny(['search', 'category_id', 'stock_status']))
                  Tidak ada item yang sesuai dengan filter
                @else
                  Belum ada data item
                @endif
              </h6>
              <p class="text-muted mb-3">
                @if(request()->hasAny(['search', 'category_id', 'stock_status']))
                  Coba ubah filter atau kata kunci pencarian
                @else
                  Mulai dengan menambahkan item pertama Anda
                @endif
              </p>
              @if(!request()->hasAny(['search', 'category_id', 'stock_status']))
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

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1" aria-labelledby="stockAdjustmentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="stockAdjustmentModalLabel">
          <i class="bx bx-transfer me-2"></i>
          Sesuaikan Stok
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="stockAdjustmentForm" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Item</label>
            <input type="text" class="form-control" id="adjustmentItemName" readonly>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Stok Saat Ini</label>
            <input type="text" class="form-control" id="adjustmentCurrentStock" readonly>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Tipe Penyesuaian <span class="text-danger">*</span></label>
            <select class="form-select" name="adjustment_type" required>
              <option value="">Pilih tipe penyesuaian</option>
              <option value="add">Tambah Stok</option>
              <option value="reduce">Kurangi Stok</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Catatan <span class="text-danger">*</span></label>
            <textarea class="form-control" name="notes" rows="3" placeholder="Alasan penyesuaian stok..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>
            Simpan Penyesuaian
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Export ke Excel
function exportToExcel() {
  const table = document.getElementById('itemsTable');
  const ws = XLSX.utils.table_to_sheet(table);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Daftar Item');
  XLSX.writeFile(wb, 'daftar_item_' + new Date().toISOString().slice(0,10) + '.xlsx');
}

// Stock adjustment modal
function showStockAdjustment(itemId, itemName, currentStock) {
  document.getElementById('adjustmentItemName').value = itemName;
  document.getElementById('adjustmentCurrentStock').value = currentStock;
  document.getElementById('stockAdjustmentForm').action = `/items/${itemId}/adjust-stock`;
  
  const modal = new bootstrap.Modal(document.getElementById('stockAdjustmentModal'));
  modal.show();
}

// Load library XLSX untuk export Excel
const script = document.createElement('script');
script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
document.head.appendChild(script);
</script>
@endpush

@push('styles')
<style>
@media print {
  .btn, .breadcrumb, .card-header .d-flex .btn, .dropdown, .modal {
    display: none !important;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
  }
  
  .table {
    font-size: 12px;
  }
}

.table th {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
  font-weight: 600;
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

.pagination {
  margin: 0;
}

.pagination .page-link {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}
</style>
@endpush
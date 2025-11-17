
@extends('layouts.admin')

@section('title', 'Detail Kategori: ' . $category->category_name . ' - Chicking BJM')

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
          <a href="{{ route('categories.index') }}">Kategori</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ $category->category_name }}</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Category Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-4">
              <span class="avatar-initial rounded bg-label-{{ $category->is_active ? 'success' : 'secondary' }}">
                <i class="bx bx-category text-{{ $category->is_active ? 'success' : 'secondary' }}" style="font-size: 24px;"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-1">{{ $category->category_name }}</h4>
              <div class="d-flex align-items-center gap-3 mb-2">
                <!-- <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                  <i class="bx bx-{{ $category->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                  {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                </span> -->
                <span class="text-muted">
                  <i class="bx bx-package me-1"></i>
                  {{ $category->items_count ?? $category->items->count() }} Item
                </span>
                <span class="text-muted">
                  <i class="bx bx-calendar me-1"></i>
                  Dibuat {{ $category->created_at->format('d/m/Y') }}
                </span>
              </div>
              @if($category->description)
              <p class="text-muted mb-0">{{ $category->description }}</p>
              @else
              <p class="text-muted mb-0 fst-italic">Tidak ada deskripsi</p>
              @endif
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="d-flex gap-2">
            <div class="dropdown">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bx bx-cog me-1"></i>
                Aksi
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="{{ route('categories.edit', $category) }}">
                    <i class="bx bx-edit me-2"></i>
                    Edit Kategori
                  </a>
                </li>
                <li>
                  <form action="{{ route('categories.toggle-status', $category) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="dropdown-item">
                      <i class="bx bx-{{ $category->is_active ? 'toggle-right' : 'toggle-left' }} me-2"></i>
                      {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                  </form>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="{{ route('items.create', ['category' => $category->id]) }}">
                    <i class="bx bx-plus me-2 text-success"></i>
                    Tambah Item Baru
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                @if($category->items->count() == 0)
                <li>
                  <form action="{{ route('categories.destroy', $category) }}" method="POST" 
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                      <i class="bx bx-trash me-2"></i>
                      Hapus Kategori
                    </button>
                  </form>
                </li>
                @else
                <li>
                  <span class="dropdown-item text-muted">
                    <i class="bx bx-info-circle me-2"></i>
                    Tidak dapat dihapus (ada item)
                  </span>
                </li>
                @endif
              </ul>
            </div>
            
            <a href="{{ route('categories.index') }}" class="btn btn-outline-primary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Statistics Cards -->
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
        <h3 class="card-title mb-2">{{ $category->items->count() }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-package"></i> 
          Item dalam kategori
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Active Items" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Item Aktif</span>
        @php $activeItems = $category->items->where('is_active', true)->count(); @endphp
        <h3 class="card-title mb-2">{{ $activeItems }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-check-circle"></i> 
          {{ $activeItems > 0 ? round(($activeItems / max(1, $category->items->count())) * 100, 1) : 0 }}% dari total
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
        <span class="fw-semibold d-block mb-1">Stok Menipis</span>
        @php $lowStockItems = $category->items->filter(function($item) {
          return $item->current_stock <= $item->low_stock_threshold;
        })->count(); @endphp
        <h3 class="card-title mb-2 text-{{ $lowStockItems > 0 ? 'warning' : 'success' }}">{{ $lowStockItems }}</h3>
        <small class="text-{{ $lowStockItems > 0 ? 'warning' : 'success' }} fw-semibold">
          <i class="bx bx-{{ $lowStockItems > 0 ? 'error' : 'check' }}-circle"></i> 
          {{ $lowStockItems > 0 ? 'Perlu perhatian' : 'Stok aman' }}
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Total Stock Value" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Stok</span>
        @php $totalStock = $category->items->sum('current_stock'); @endphp
        <h3 class="card-title mb-2">{{ number_format($totalStock, 0) }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-cube"></i> 
          Unit keseluruhan
        </small>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Items List -->
  <div class="col-xl-8 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-package me-2"></i>
          Daftar Item ({{ $category->items->count() }})
        </h5>
        <div class="d-flex gap-2">
          <a href="{{ route('items.create', ['category' => $category->id]) }}" class="btn btn-primary btn-sm">
            <i class="bx bx-plus me-1"></i>
            Tambah Item
          </a>
        </div>
      </div>
      
      @if($category->items->count() > 0)
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Item</th>
              <th>SKU</th>
              <!-- <th class="text-center">Stok</th> -->
              <th class="text-center">Unit</th>
              <th class="text-center">Status</th>
              <th class="text-center">Supplier</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach($category->items as $item)
            <tr class="item-row" 
                data-status="{{ $item->is_active ? 'active' : 'inactive' }}"
                data-stock-level="{{ $item->current_stock <= 0 ? 'out-of-stock' : ($item->current_stock <= $item->low_stock_threshold ? 'low-stock' : 'normal') }}">
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-{{ $item->stock_status_color }}">
                      <i class="bx bx-package"></i>
                    </span>
                  </div>
                  <div>
                    <h6 class="mb-0">
                      <a href="{{ route('items.show', $item) }}" class="text-decoration-none">
                        {{ $item->item_name }}
                      </a>
                    </h6>
                    <small class="text-muted">
                      {{ Str::limit($item->description, 50) ?: 'Tidak ada deskripsi' }}
                    </small>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge bg-label-dark">{{ $item->sku }}</span>
              </td>
              <!-- <td class="text-center">
                <div>
                  <span class="fw-bold text-{{ $item->stock_status_color }}">
                    {{ number_format($item->current_stock, 2) }}
                  </span>
                  <br>
                  <small class="text-muted">
                    Min: {{ number_format($item->low_stock_threshold, 0) }}
                  </small>
                </div>
              </td> -->
              <td class="text-center">
                <span class="badge bg-label-info">{{ $item->unit }}</span>
              </td>
              <td class="text-center">
                <div class="d-flex flex-column align-items-center gap-1">
                  <span class="badge bg-{{ $item->status == 'ACTIVE' ? 'success' : 'secondary' }}">
                    {{ $item->status }}
                  </span>
                  @if($item->current_stock <= 0)
                    <span class="badge bg-danger">Habis</span>
                  @elseif($item->current_stock <= $item->low_stock_threshold)
                    <span class="badge bg-warning">Menipis</span>
                  @else
                    <span class="badge bg-success">Aman</span>
                  @endif
                </div>
              </td>
              <td class="text-center">
                @if($item->supplier)
                  <span class="text-primary">{{ $item->supplier->supplier_name }}</span>
                @else
                  <span class="text-muted fst-italic">Tidak ada</span>
                @endif
              </td>
              <td class="text-center">
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('items.show', $item) }}">
                      <i class="bx bx-show me-1"></i> Detail
                    </a>
                    <a class="dropdown-item" href="{{ route('items.edit', $item) }}">
                      <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>
                    <!-- <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('stock-transactions.create', ['item' => $item->id]) }}">
                      <i class="bx bx-transfer me-1 text-primary"></i> Transaksi Stok
                    </a> -->
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <!-- Empty State -->
      <div class="card-body text-center py-5">
        <div class="d-flex flex-column align-items-center">
          <i class="bx bx-package text-muted" style="font-size: 64px;"></i>
          <h5 class="mt-3">Belum Ada Item</h5>
          <p class="text-muted mb-4">
            Kategori ini belum memiliki item apapun.<br>
            Mulai dengan menambahkan item pertama.
          </p>
          <a href="{{ route('items.create', ['category' => $category->id]) }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>
            Tambah Item Pertama
          </a>
        </div>
      </div>
      @endif
    </div>
  </div>

  <!-- Sidebar Information -->
  <div class="col-xl-4">
    <!-- Category Information -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Informasi Kategori
        </h6>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Nama Kategori:</span>
              <span class="fw-semibold">{{ $category->category_name }}</span>
            </div>
          </div>
          <!-- <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Status:</span>
              <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
          </div> -->
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Jumlah Item:</span>
              <span class="fw-semibold">{{ $category->items->count() }}</span>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Dibuat:</span>
              <span class="fw-semibold">{{ $category->created_at->format('d/m/Y') }}</span>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Diupdate:</span>
              <span class="fw-semibold">{{ $category->updated_at->format('d/m/Y') }}</span>
            </div>
          </div>
          @if($category->description)
          <div class="list-group-item px-0 py-2 border-0">
            <div>
              <span class="text-muted d-block mb-1">Deskripsi:</span>
              <p class="mb-0">{{ $category->description }}</p>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-flash me-2"></i>
          Quick Actions
        </h6>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('items.create', ['category' => $category->id]) }}" class="btn btn-primary btn-sm">
            <i class="bx bx-plus me-1"></i>
            Tambah Item Baru
          </a>
          <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-edit me-1"></i>
            Edit Kategori
          </a>
          @if($lowStockItems > 0)
          <!-- <a href="{{ route('items.low-stock', ['category' => $category->id]) }}" class="btn btn-outline-warning btn-sm">
            <i class="bx bx-error me-1"></i>
            Lihat Stok Menipis ({{ $lowStockItems }})
          </a> -->
          @endif
          <a href="{{ route('stock-transactions.index', ['category' => $category->id]) }}" class="btn btn-outline-info btn-sm">
            <i class="bx bx-history me-1"></i>
            Riwayat Transaksi
          </a>
        </div>
      </div>
    </div>

    <!-- Stock Status Distribution -->
    <!-- @if($category->items->count() > 0)
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-pie-chart me-2"></i>
          Distribusi Status Stok
        </h6>
      </div>
      <div class="card-body">
        @php
          $stockDistribution = [
            'aman' => $category->items->filter(function($item) {
              return $item->current_stock > $item->low_stock_threshold;
            })->count(),
            'menipis' => $category->items->filter(function($item) {
              return $item->current_stock <= $item->low_stock_threshold && $item->current_stock > 0;
            })->count(),
            'habis' => $category->items->filter(function($item) {
              return $item->current_stock <= 0;
            })->count()
          ];
          $total = array_sum($stockDistribution);
        @endphp
        
        <div class="list-group list-group-flush">
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <span class="badge bg-success me-2">●</span>
                <span>Stok Aman</span>
              </div>
              <div class="text-end">
                <span class="fw-bold">{{ $stockDistribution['aman'] }}</span>
                <small class="text-muted">
                  ({{ $total > 0 ? round(($stockDistribution['aman'] / $total) * 100, 1) : 0 }}%)
                </small>
              </div>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <span class="badge bg-warning me-2">●</span>
                <span>Stok Menipis</span>
              </div>
              <div class="text-end">
                <span class="fw-bold">{{ $stockDistribution['menipis'] }}</span>
                <small class="text-muted">
                  ({{ $total > 0 ? round(($stockDistribution['menipis'] / $total) * 100, 1) : 0 }}%)
                </small>
              </div>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <span class="badge bg-danger me-2">●</span>
                <span>Stok Habis</span>
              </div>
              <div class="text-end">
                <span class="fw-bold">{{ $stockDistribution['habis'] }}</span>
                <small class="text-muted">
                  ({{ $total > 0 ? round(($stockDistribution['habis'] / $total) * 100, 1) : 0 }}%)
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif -->
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Item filtering
  const filterOptions = document.querySelectorAll('.filter-option');
  const itemRows = document.querySelectorAll('.item-row');

  filterOptions.forEach(option => {
    option.addEventListener('click', function(e) {
      e.preventDefault();
      const filter = this.dataset.filter;
      
      // Update active filter
      filterOptions.forEach(opt => opt.classList.remove('active'));
      this.classList.add('active');
      
      // Filter items
      itemRows.forEach(row => {
        let show = false;
        
        switch(filter) {
          case 'all':
            show = true;
            break;
          case 'active':
            show = row.dataset.status === 'active';
            break;
          case 'inactive':
            show = row.dataset.status === 'inactive';
            break;
          case 'low-stock':
            show = row.dataset.stockLevel === 'low-stock';
            break;
          case 'out-of-stock':
            show = row.dataset.stockLevel === 'out-of-stock';
            break;
        }
        
        row.style.display = show ? '' : 'none';
      });
      
      // Update counter
      const visibleRows = Array.from(itemRows).filter(row => row.style.display !== 'none').length;
      const counter = document.querySelector('.card-header h5');
      const totalItems = itemRows.length;
      
      if (filter === 'all') {
        counter.innerHTML = `<i class="bx bx-package me-2"></i>Daftar Item (${totalItems})`;
      } else {
        counter.innerHTML = `<i class="bx bx-package me-2"></i>Daftar Item (${visibleRows} dari ${totalItems})`;
      }
    });
  });

  // Auto-refresh stock status colors
  function updateStockStatusColors() {
    itemRows.forEach(row => {
      const stockLevel = row.dataset.stockLevel;
      const avatar = row.querySelector('.avatar-initial');
      const stockText = row.querySelector('.fw-bold');
      
      if (avatar && stockText) {
        // Remove existing color classes
        avatar.className = avatar.className.replace(/bg-label-\w+/, '');
        stockText.className = stockText.className.replace(/text-\w+/, '');
        
        // Add appropriate color based on stock level
        switch(stockLevel) {
          case 'out-of-stock':
            avatar.classList.add('bg-label-danger');
            stockText.classList.add('text-danger');
            break;
          case 'low-stock':
            avatar.classList.add('bg-label-warning');
            stockText.classList.add('text-warning');
            break;
          default:
            avatar.classList.add('bg-label-success');
            stockText.classList.add('text-success');
        }
      }
    });
  }

  // Initialize
  updateStockStatusColors();
  
  // Set first filter as active
  if (filterOptions.length > 0) {
    filterOptions[0].classList.add('active');
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

.item-row {
  transition: all 0.2s ease-in-out;
}

.item-row:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.filter-option.active {
  background-color: rgba(105, 108, 255, 0.1);
  color: #696cff;
}

.list-group-item {
  transition: background-color 0.15s ease-in-out;
}

.list-group-item:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.card-header .btn-sm {
  padding: 0.375rem 0.75rem;
}

.badge {
  font-size: 0.75em;
}

.table th {
  font-weight: 600;
  background-color: #f8f9fa;
}

@media (max-width: 768px) {
  .table-responsive {
    font-size: 0.875rem;
  }
  
  .avatar-initial {
    width: 32px;
    height: 32px;
    font-size: 14px;
  }
  
  .d-flex.gap-2 {
    flex-direction: column;
    gap: 0.5rem !important;
  }
}
</style>
@endpush
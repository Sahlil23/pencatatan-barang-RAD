@extends('layouts.admin')

@section('title', 'Detail Supplier: ' . $supplier->supplier_name . ' - Chicking BJM')

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
          <a href="{{ route('suppliers.index') }}">Supplier</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ $supplier->supplier_name }}</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Supplier Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-store text-primary" style="font-size: 24px;"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-1">{{ $supplier->supplier_name }}</h4>
              <div class="d-flex align-items-center gap-3 mb-2">
                <span class="badge bg-primary">
                  <i class="bx bx-package me-1"></i>
                  {{ $supplier->items_count ?? $supplier->items->count() }} Item
                </span>
                @if($supplier->contact_person)
                <span class="text-muted">
                  <i class="bx bx-user me-1"></i>
                  {{ $supplier->contact_person }}
                </span>
                @endif
                @if($supplier->phone)
                <span class="text-muted">
                  <i class="bx bx-phone me-1"></i>
                  {{ $supplier->phone }}
                </span>
                @endif
              </div>
              @if($supplier->address)
              <p class="text-muted mb-0">
                <i class="bx bx-map me-1"></i>
                {{ $supplier->address }}
              </p>
              @else
              <p class="text-muted mb-0 fst-italic">Alamat tidak tersedia</p>
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
                  <a class="dropdown-item" href="{{ route('suppliers.edit', $supplier) }}">
                    <i class="bx bx-edit me-2"></i>
                    Edit Supplier
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="{{ route('items.create', ['supplier' => $supplier->id]) }}">
                    <i class="bx bx-plus me-2 text-success"></i>
                    Tambah Item Baru
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="{{ route('stock-transactions.create', ['supplier' => $supplier->id]) }}">
                    <i class="bx bx-transfer me-2 text-primary"></i>
                    Transaksi Stok
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                @if($supplier->phone)
                <li>
                  <a class="dropdown-item" href="tel:{{ $supplier->phone }}">
                    <i class="bx bx-phone me-2 text-info"></i>
                    Hubungi Supplier
                  </a>
                </li>
                @endif
                @if($supplier->items->count() == 0)
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" 
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus supplier ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                      <i class="bx bx-trash me-2"></i>
                      Hapus Supplier
                    </button>
                  </form>
                </li>
                @else
                <li><hr class="dropdown-divider"></li>
                <li>
                  <span class="dropdown-item text-muted">
                    <i class="bx bx-info-circle me-2"></i>
                    Tidak dapat dihapus (ada item)
                  </span>
                </li>
                @endif
              </ul>
            </div>
            
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-primary">
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
        <h3 class="card-title mb-2">{{ $supplier->items->count() }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-package"></i> 
          Item yang disupply
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
        @php $activeItems = $supplier->items->where('is_active', true)->count(); @endphp
        <h3 class="card-title mb-2">{{ $activeItems }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-check-circle"></i> 
          {{ $activeItems > 0 ? round(($activeItems / max(1, $supplier->items->count())) * 100, 1) : 0 }}% dari total
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
        @php $lowStockItems = $supplier->items->filter(function($item) {
          return $item->current_stock <= $item->low_stock_threshold;
        })->count(); @endphp
        <h3 class="card-title mb-2 text-{{ $lowStockItems > 0 ? 'warning' : 'success' }}">{{ $lowStockItems }}</h3>
        <small class="text-{{ $lowStockItems > 0 ? 'warning' : 'success' }} fw-semibold">
          <i class="bx bx-{{ $lowStockItems > 0 ? 'error' : 'check' }}-circle"></i> 
          {{ $lowStockItems > 0 ? 'Perlu restock' : 'Stok aman' }}
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
        @php $categoriesCount = $supplier->items->pluck('category_id')->unique()->count(); @endphp
        <h3 class="card-title mb-2">{{ $categoriesCount }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-category"></i> 
          Kategori berbeda
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
          Daftar Item ({{ $supplier->items->count() }})
        </h5>
        <div class="d-flex gap-2">
          <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
              <i class="bx bx-filter me-1"></i>
              Filter
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item filter-option" href="#" data-filter="all">Semua Item</a></li>
              <li><a class="dropdown-item filter-option" href="#" data-filter="active">Item Aktif</a></li>
              <li><a class="dropdown-item filter-option" href="#" data-filter="inactive">Item Nonaktif</a></li>
              <li><a class="dropdown-item filter-option" href="#" data-filter="low-stock">Stok Menipis</a></li>
              <li><a class="dropdown-item filter-option" href="#" data-filter="out-of-stock">Stok Habis</a></li>
            </ul>
          </div>
          <div class="dropdown">
            <button class="btn btn-outline-info btn-sm dropdown-toggle" data-bs-toggle="dropdown">
              <i class="bx bx-category me-1"></i>
              Kategori
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item category-filter" href="#" data-category="all">Semua Kategori</a></li>
              @foreach($supplier->items->pluck('category')->unique('id') as $category)
              <li><a class="dropdown-item category-filter" href="#" data-category="{{ $category->id }}">{{ $category->category_name }}</a></li>
              @endforeach
            </ul>
          </div>
          <a href="{{ route('items.create', ['supplier' => $supplier->id]) }}" class="btn btn-primary btn-sm">
            <i class="bx bx-plus me-1"></i>
            Tambah Item
          </a>
        </div>
      </div>
      
      @if($supplier->items->count() > 0)
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Item</th>
              <th>SKU</th>
              <th>Kategori</th>
              <th class="text-center">Stok</th>
              <th class="text-center">Unit</th>
              <th class="text-center">Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach($supplier->items as $item)
            <tr class="item-row" 
                data-status="{{ $item->is_active ? 'active' : 'inactive' }}"
                data-stock-level="{{ $item->current_stock <= 0 ? 'out-of-stock' : ($item->current_stock <= $item->low_stock_threshold ? 'low-stock' : 'normal') }}"
                data-category="{{ $item->category_id }}">
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
              <td>
                <span class="badge bg-label-secondary">{{ $item->category->category_name }}</span>
              </td>
              <td class="text-center">
                <div>
                  <span class="fw-bold text-{{ $item->stock_status_color }}">
                    {{ number_format($item->current_stock, 2) }}
                  </span>
                  <br>
                  <small class="text-muted">
                    Min: {{ number_format($item->low_stock_threshold, 0) }}
                  </small>
                </div>
              </td>
              <td class="text-center">
                <span class="badge bg-label-info">{{ $item->unit }}</span>
              </td>
              <td class="text-center">
                <div class="d-flex flex-column align-items-center gap-1">
                  <span class="badge bg-{{ $item->is_active ? 'success' : 'secondary' }}">
                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
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
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('stock-transactions.create', ['item' => $item->id]) }}">
                      <i class="bx bx-transfer me-1 text-primary"></i> Transaksi Stok
                    </a>
                    @if($item->current_stock <= $item->low_stock_threshold)
                    <a class="dropdown-item" href="{{ route('stock-transactions.create', ['item' => $item->id, 'type' => 'IN']) }}">
                      <i class="bx bx-plus-circle me-1 text-success"></i> Restock
                    </a>
                    @endif
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
            Supplier ini belum memiliki item apapun.<br>
            Mulai dengan menambahkan item pertama.
          </p>
          <a href="{{ route('items.create', ['supplier' => $supplier->id]) }}" class="btn btn-primary">
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
    <!-- Supplier Information -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Informasi Supplier
        </h6>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Nama Supplier:</span>
              <span class="fw-semibold">{{ $supplier->supplier_name }}</span>
            </div>
          </div>
          @if($supplier->contact_person)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Contact Person:</span>
              <span class="fw-semibold">{{ $supplier->contact_person }}</span>
            </div>
          </div>
          @endif
          @if($supplier->phone)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Telepon:</span>
              <div>
                <span class="fw-semibold">{{ $supplier->phone }}</span>
                <a href="tel:{{ $supplier->phone }}" class="btn btn-outline-primary btn-xs ms-2">
                  <i class="bx bx-phone"></i>
                </a>
              </div>
            </div>
          </div>
          @endif
          @if($supplier->address)
          <div class="list-group-item px-0 py-2 border-0">
            <div>
              <span class="text-muted d-block mb-1">Alamat:</span>
              <p class="mb-0">{{ $supplier->address }}</p>
            </div>
          </div>
          @endif
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Jumlah Item:</span>
              <span class="fw-semibold">{{ $supplier->items->count() }}</span>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Bergabung:</span>
              <span class="fw-semibold">{{ $supplier->created_at->format('d/m/Y') }}</span>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Diupdate:</span>
              <span class="fw-semibold">{{ $supplier->updated_at->format('d/m/Y') }}</span>
            </div>
          </div>
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
          <a href="{{ route('items.create', ['supplier' => $supplier->id]) }}" class="btn btn-primary btn-sm">
            <i class="bx bx-plus me-1"></i>
            Tambah Item Baru
          </a>
          <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-edit me-1"></i>
            Edit Supplier
          </a>
          @if($lowStockItems > 0)
          <a href="{{ route('items.low-stock', ['supplier' => $supplier->id]) }}" class="btn btn-outline-warning btn-sm">
            <i class="bx bx-error me-1"></i>
            Lihat Stok Menipis ({{ $lowStockItems }})
          </a>
          @endif
          <a href="{{ route('stock-transactions.index', ['supplier' => $supplier->id]) }}" class="btn btn-outline-info btn-sm">
            <i class="bx bx-history me-1"></i>
            Riwayat Transaksi
          </a>
          @if($supplier->phone)
          <a href="tel:{{ $supplier->phone }}" class="btn btn-outline-success btn-sm">
            <i class="bx bx-phone me-1"></i>
            Hubungi Supplier
          </a>
          @endif
        </div>
      </div>
    </div>

    <!-- Categories Distribution -->
    @if($supplier->items->count() > 0)
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-category me-2"></i>
          Distribusi Kategori
        </h6>
      </div>
      <div class="card-body">
        @php
          $categoryDistribution = $supplier->items->groupBy('category.category_name');
        @endphp
        
        <div class="list-group list-group-flush">
          @foreach($categoryDistribution as $categoryName => $items)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <span class="badge bg-label-primary me-2">●</span>
                <span>{{ $categoryName }}</span>
              </div>
              <div class="text-end">
                <span class="fw-bold">{{ $items->count() }}</span>
                <small class="text-muted">
                  ({{ round(($items->count() / $supplier->items->count()) * 100, 1) }}%)
                </small>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Stock Status Distribution -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-pie-chart me-2"></i>
          Status Stok
        </h6>
      </div>
      <div class="card-body">
        @php
          $stockDistribution = [
            'aman' => $supplier->items->filter(function($item) {
              return $item->current_stock > $item->low_stock_threshold;
            })->count(),
            'menipis' => $supplier->items->filter(function($item) {
              return $item->current_stock <= $item->low_stock_threshold && $item->current_stock > 0;
            })->count(),
            'habis' => $supplier->items->filter(function($item) {
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
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Item filtering by status
  const filterOptions = document.querySelectorAll('.filter-option');
  const itemRows = document.querySelectorAll('.item-row');

  filterOptions.forEach(option => {
    option.addEventListener('click', function(e) {
      e.preventDefault();
      const filter = this.dataset.filter;
      
      // Update active filter
      filterOptions.forEach(opt => opt.classList.remove('active'));
      this.classList.add('active');
      
      filterItems(filter, null);
    });
  });

  // Category filtering
  const categoryFilters = document.querySelectorAll('.category-filter');
  
  categoryFilters.forEach(filter => {
    filter.addEventListener('click', function(e) {
      e.preventDefault();
      const category = this.dataset.category;
      
      // Update active category filter
      categoryFilters.forEach(cat => cat.classList.remove('active'));
      this.classList.add('active');
      
      filterItems(null, category);
    });
  });

  function filterItems(statusFilter, categoryFilter) {
    let visibleCount = 0;
    
    itemRows.forEach(row => {
      let showByStatus = true;
      let showByCategory = true;
      
      // Apply status filter
      if (statusFilter) {
        switch(statusFilter) {
          case 'all':
            showByStatus = true;
            break;
          case 'active':
            showByStatus = row.dataset.status === 'active';
            break;
          case 'inactive':
            showByStatus = row.dataset.status === 'inactive';
            break;
          case 'low-stock':
            showByStatus = row.dataset.stockLevel === 'low-stock';
            break;
          case 'out-of-stock':
            showByStatus = row.dataset.stockLevel === 'out-of-stock';
            break;
        }
      }
      
      // Apply category filter
      if (categoryFilter) {
        if (categoryFilter === 'all') {
          showByCategory = true;
        } else {
          showByCategory = row.dataset.category === categoryFilter;
        }
      }
      
      const show = showByStatus && showByCategory;
      row.style.display = show ? '' : 'none';
      
      if (show) visibleCount++;
    });
    
    // Update counter
    updateItemCounter(visibleCount);
  }

  function updateItemCounter(visibleCount) {
    const counter = document.querySelector('.card-header h5');
    const totalItems = itemRows.length;
    
    if (visibleCount === totalItems) {
      counter.innerHTML = `<i class="bx bx-package me-2"></i>Daftar Item (${totalItems})`;
    } else {
      counter.innerHTML = `<i class="bx bx-package me-2"></i>Daftar Item (${visibleCount} dari ${totalItems})`;
    }
  }

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
  
  // Set first filters as active
  if (filterOptions.length > 0) {
    filterOptions[0].classList.add('active');
  }
  if (categoryFilters.length > 0) {
    categoryFilters[0].classList.add('active');
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

.filter-option.active,
.category-filter.active {
  background-color: rgba(105, 108, 255, 0.1);
  color: #696cff;
}

.list-group-item {
  transition: background-color 0.15s ease-in-out;
}

.list-group-item:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.btn-xs {
  padding: 0.125rem 0.25rem;
  font-size: 0.75rem;
  line-height: 1;
  border-radius: 0.25rem;
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
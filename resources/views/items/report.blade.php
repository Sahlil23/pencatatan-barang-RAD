@extends('layouts.admin')

@section('title', 'Laporan Stock Akhir - Chicking BJM')

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
          <a href="{{ route('items.index') }}">Item</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Laporan Stock Akhir</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Header Laporan -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body text-center">
        <h4 class="card-title mb-2">
          <i class="bx bx-chart me-2"></i>
          Laporan Stock Akhir Item
        </h4>
        <p class="text-muted mb-0">
          Periode: {{ now()->format('d F Y') }} | 
          Total Item: {{ $totalItems }} | 
          Total Stok: {{ number_format($totalStockValue) }} Unit
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Statistik Ringkasan -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <div class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-package"></i>
            </div>
          </div>
          <div>
            <span class="fw-semibold d-block mb-1">Total Item</span>
            <h3 class="card-title mb-0">{{ $totalItems }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <div class="avatar-initial rounded bg-label-success">
              <i class="bx bx-box"></i>
            </div>
          </div>
          <div>
            <span class="fw-semibold d-block mb-1">Total Stok</span>
            <h3 class="card-title mb-0">{{ number_format($totalStockValue) }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <div class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-error"></i>
            </div>
          </div>
          <div>
            <span class="fw-semibold d-block mb-1">Stok Menipis</span>
            <h3 class="card-title mb-0">{{ $lowStockItems }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <div class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-x"></i>
            </div>
          </div>
          <div>
            <span class="fw-semibold d-block mb-1">Stok Habis</span>
            <h3 class="card-title mb-0">{{ $outOfStockItems }}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filter dan Pengaturan Laporan -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">
      <i class="bx bx-filter me-2"></i>
      Filter Laporan
    </h5>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('items.report') }}" class="row g-3">
      <!-- Pencarian -->
      <div class="col-md-3">
        <label class="form-label">Cari Item</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nama atau SKU...">
        </div>
      </div>
      
      <!-- Filter Kategori -->
      <div class="col-md-2">
        <label class="form-label">Kategori</label>
        <select class="form-select" name="category_id">
          <option value="">Semua</option>
          @foreach($categories as $category)
          <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
            {{ $category->category_name }}
          </option>
          @endforeach
        </select>
      </div>
      
      <!-- Filter Supplier -->
      <div class="col-md-2">
        <label class="form-label">Supplier</label>
        <select class="form-select" name="supplier_id">
          <option value="">Semua</option>
          @foreach($suppliers as $supplier)
          <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
            {{ $supplier->supplier_name }}
          </option>
          @endforeach
        </select>
      </div>
      
      <!-- Filter Status Stok -->
      <div class="col-md-2">
        <label class="form-label">Status Stok</label>
        <select class="form-select" name="stock_status">
          <option value="">Semua</option>
          <option value="in" {{ request('stock_status') == 'in' ? 'selected' : '' }}>Stok Tersedia</option>
          <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stok Menipis</option>
          <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Stok Habis</option>
        </select>
      </div>
      
      <!-- Urutkan Berdasarkan -->
      <div class="col-md-2">
        <label class="form-label">Urutkan</label>
        <select class="form-select" name="sort_by">
          <option value="item_name" {{ request('sort_by') == 'item_name' ? 'selected' : '' }}>Nama Item</option>
          <option value="sku" {{ request('sort_by') == 'sku' ? 'selected' : '' }}>SKU</option>
          <option value="category" {{ request('sort_by') == 'category' ? 'selected' : '' }}>Kategori</option>
          <option value="stock" {{ request('sort_by') == 'stock' ? 'selected' : '' }}>Jumlah Stok</option>
        </select>
      </div>
      
      <!-- Urutan -->
      <div class="col-md-1">
        <label class="form-label">Urutan</label>
        <select class="form-select" name="sort_order">
          <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>A-Z</option>
          <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Z-A</option>
        </select>
      </div>
      
      <!-- Tombol Aksi -->
      <div class="col-12">
        <div class="d-flex gap-2 flex-wrap">
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-filter me-1"></i>
            Terapkan Filter
          </button>
          <a href="{{ route('items.report') }}" class="btn btn-outline-secondary">
            <i class="bx bx-reset me-1"></i>
            Reset
          </a>
          <button type="button" class="btn btn-success" onclick="exportToExcel()">
            <i class="bx bx-download me-1"></i>
            Export Excel
          </button>
          <button type="button" class="btn btn-info" onclick="openPrintWindow()">
            <i class="bx bx-printer me-1"></i>
            Print
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Tabel Laporan -->
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">
      <i class="bx bx-table me-2"></i>
      Detail Laporan Stock Akhir
    </h5>
  </div>
  
  <div class="table-responsive">
    <table class="table table-striped" id="reportTable">
      <thead class="table-dark">
        <tr>
          <th>No</th>
          <th>SKU</th>
          <th>Nama Item</th>
          <th>Kategori</th>
          <th>Supplier</th>
          <th>Unit</th>
          <th class="text-center">Stock Akhir</th>
          <th class="text-center">Minimum Stock</th>
          <th class="text-center">Status</th>
          <th class="text-center">Selisih</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $index => $item)
        @php
          $selisih = $item->current_stock - $item->low_stock_threshold;
        @endphp
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>
            <span class="badge bg-label-secondary">{{ $item->sku }}</span>
          </td>
          <td>
            <strong>{{ $item->item_name }}</strong>
          </td>
          <td>
            {{ $item->category->category_name ?? '-' }}
          </td>
          <td>
            {{ $item->supplier->supplier_name ?? '-' }}
          </td>
          <td>
            <span class="badge bg-label-info">{{ $item->unit }}</span>
          </td>
          <td class="text-center">
            <strong class="text-{{ $item->stock_status_color }}">
              {{ number_format($item->current_stock) }}
            </strong>
          </td>
          <td class="text-center">
            {{ number_format($item->low_stock_threshold) }}
          </td>
          <td class="text-center">
            <span class="badge bg-{{ $item->stock_status_color }}">
              {{ $item->stock_status }}
            </span>
          </td>
          <td class="text-center">
            <span class="fw-bold text-{{ $selisih >= 0 ? 'success' : 'danger' }}">
              {{ $selisih >= 0 ? '+' : '' }}{{ number_format($selisih) }}
            </span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="10" class="text-center py-4">
            <div class="d-flex flex-column align-items-center">
              <i class="bx bx-chart" style="font-size: 48px; color: #ddd;"></i>
              <h6 class="mt-2 text-muted">Tidak ada data untuk ditampilkan</h6>
              <p class="text-muted">Coba ubah filter atau tambah item baru</p>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
      @if($items->count() > 0)
      <tfoot class="table-light">
        <tr>
          <th colspan="6" class="text-end">Total:</th>
          <th class="text-center">{{ number_format($items->sum('current_stock')) }}</th>
          <th class="text-center">{{ number_format($items->sum('low_stock_threshold')) }}</th>
          <th colspan="2"></th>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Export ke Excel
function exportToExcel() {
  const table = document.getElementById('reportTable');
  const ws = XLSX.utils.table_to_sheet(table);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Laporan Stock');
  XLSX.writeFile(wb, 'laporan_stock_akhir_' + new Date().toISOString().slice(0,10) + '.xlsx');
}

function openPrintWindow() {
  const currentUrl = window.location.href;
  const printUrl = '{{ route("items.print-report") }}' + window.location.search;
  
  // Buka di tab baru
  const printWindow = window.open(printUrl, '_blank');
  
  // Auto print setelah page load (optional)
  printWindow.onload = function() {
    setTimeout(() => {
      printWindow.print();
    }, 500);
  };
}

// Print laporan
function printReport() {
  window.print();
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
  .btn, .breadcrumb, .card-header .d-flex .btn, .filter-section {
    display: none !important;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
  }
  
  .table {
    font-size: 11px;
  }
  
  .table th, .table td {
    padding: 4px !important;
  }
}

.table th {
  font-weight: 600;
  font-size: 0.875rem;
}

.badge {
  font-size: 0.75rem;
}
</style>
@endpush
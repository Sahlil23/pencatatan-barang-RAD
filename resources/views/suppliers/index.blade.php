@extends('layouts.admin')

@section('title', 'Daftar Supplier - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Supplier</li>
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
            <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="Total Suppliers" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Supplier</span>
        <h3 class="card-title mb-2">{{ $suppliers->count() }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-group"></i> Supplier
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Active Suppliers" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Supplier Aktif</span>
        <h3 class="card-title mb-2">{{ $suppliers->where('active_transactions_count', '>', 0)->count() }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-trending-up"></i> 3 Bulan Terakhir
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="Total Transactions" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Transaksi</span>
        <h3 class="card-title mb-2">{{ $suppliers->sum('stock_transactions_count') }}</h3>
        <small class="text-info fw-semibold">
          <i class="bx bx-transfer"></i> Transaksi
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Total Items" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Item</span>
        <h3 class="card-title mb-2">{{ $suppliers->sum('items_count') }}</h3>
        <small class="text-warning fw-semibold">
          <i class="bx bx-box"></i> Item
        </small>
      </div>
    </div>
  </div>
</div>

<!-- Main Table Card -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bx bx-group me-2"></i>
      Daftar Supplier
    </h5>
    <div class="d-flex gap-2">
      <!-- Search -->
      <div class="input-group" style="width: 250px;">
        <span class="input-group-text"><i class="bx bx-search"></i></span>
        <input type="text" class="form-control" placeholder="Cari supplier..." id="searchInput">
      </div>
      <!-- Export -->
      <button class="btn btn-outline-success" onclick="exportData()">
        <i class="bx bx-export me-1"></i>
        Export
      </button>
      <!-- Add Button -->
      <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i>
        Tambah Supplier
      </a>
    </div>
  </div>
  
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th class="text-center" style="width: 60px;">
            <i class="bx bx-hash"></i>
          </th>
          <th>
            <i class="bx bx-group me-1"></i>
            Supplier
          </th>
          <th>
            <i class="bx bx-user me-1"></i>
            Kontak Person
          </th>
          <th>
            <i class="bx bx-phone me-1"></i>
            Telepon
          </th>
          <th class="text-center">
            <i class="bx bx-transfer me-1"></i>
            Transaksi
          </th>
          <th class="text-center">
            <i class="bx bx-package me-1"></i>
            Item
          </th>
          <th>
            <i class="bx bx-time me-1"></i>
            Terakhir Aktif
          </th>
          <th class="text-center">
            <i class="bx bx-cog me-1"></i>
            Aksi
          </th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse ($suppliers as $supplier)
        <tr>
          <td class="text-center">
            <span class="badge bg-label-primary">#{{ $supplier->id }}</span>
          </td>
          <td>
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ ['primary', 'success', 'info', 'warning', 'danger'][$loop->index % 5] }}">
                  <i class="bx bx-store"></i>
                </span>
              </div>
              <div>
                <strong>{{ $supplier->supplier_name }}</strong>
                @if($supplier->address)
                <br><small class="text-muted">
                  <i class="bx bx-map-pin"></i>
                  {{ Str::limit($supplier->address, 30) }}
                </small>
                @endif
                @if($supplier->active_transactions_count > 0)
                <br><span class="badge bg-success badge-sm">Aktif</span>
                @endif
              </div>
            </div>
          </td>
          <td>
            @if($supplier->contact_person)
              <div class="d-flex align-items-center">
                <i class="bx bx-user-circle text-primary me-2"></i>
                <span>{{ $supplier->contact_person }}</span>
              </div>
            @else
              <span class="text-muted">
                <i class="bx bx-user-x me-1"></i>
                Tidak ada
              </span>
            @endif
          </td>
          <td>
            @if($supplier->phone)
              <div class="d-flex align-items-center">
                <i class="bx bx-phone text-success me-2"></i>
                <a href="tel:{{ $supplier->phone }}" class="text-decoration-none">
                  {{ $supplier->phone }}
                </a>
              </div>
            @else
              <span class="text-muted">
                <i class="bx bx-phone-off me-1"></i>
                Tidak ada
              </span>
            @endif
          </td>
          <td class="text-center">
            @if($supplier->stock_transactions_count > 0)
              <div class="d-flex flex-column align-items-center">
                <span class="badge bg-label-info">{{ $supplier->stock_transactions_count }}</span>
                <small class="text-muted">Total</small>
                @if($supplier->active_transactions_count > 0)
                <small class="text-success">{{ $supplier->active_transactions_count }} aktif</small>
                @endif
              </div>
            @else
              <span class="badge bg-label-secondary">0</span>
            @endif
          </td>
          <td class="text-center">
            @if($supplier->items_count > 0)
              <span class="badge bg-label-primary">{{ $supplier->items_count }}</span>
            @else
              <span class="badge bg-label-secondary">0</span>
            @endif
          </td>
          <td>
            @if($supplier->stockTransactions->isNotEmpty())
              @php $lastTransaction = $supplier->stockTransactions->first(); @endphp
              <div class="d-flex flex-column">
                <small class="text-muted">{{ $lastTransaction->transaction_date->format('d/m/Y') }}</small>
                <small class="text-muted">{{ $lastTransaction->transaction_date->diffForHumans() }}</small>
                <span class="badge bg-{{ $lastTransaction->transaction_type_color }} badge-sm">
                  {{ $lastTransaction->transaction_type }}
                </span>
              </div>
            @else
              <span class="text-muted">
                <i class="bx bx-minus"></i>
                <br><small>Belum ada transaksi</small>
              </span>
            @endif
          </td>
          <td class="text-center">
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('suppliers.show', $supplier->id) }}">
                  <i class="bx bx-show me-1"></i> 
                  Lihat Detail
                </a>
                <a class="dropdown-item" href="{{ route('suppliers.edit', $supplier->id) }}">
                  <i class="bx bx-edit-alt me-1"></i> 
                  Edit
                </a>
                @if($supplier->stock_transactions_count > 0)
                <a class="dropdown-item" href="{{ route('stock-transactions.index', ['supplier_id' => $supplier->id]) }}">
                  <i class="bx bx-transfer me-1"></i> 
                  Lihat Transaksi
                </a>
                @endif
                @if($supplier->items_count > 0)
                <a class="dropdown-item" href="{{ route('items.index', ['supplier_id' => $supplier->id]) }}">
                  <i class="bx bx-package me-1"></i> 
                  Lihat Items
                </a>
                @endif
                <div class="dropdown-divider"></div>
                {{-- UPDATE: TAMBAH QUICK ACTION CREATE TRANSACTION --}}
                <a class="dropdown-item text-success" href="{{ route('stock-transactions.create', ['supplier' => $supplier->id]) }}">
                  <i class="bx bx-plus me-1"></i> 
                  Transaksi Baru
                </a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger" 
                          onclick="return confirm('Apakah Anda yakin ingin menghapus supplier {{ $supplier->supplier_name }}?\n\nPerhatian: Semua riwayat transaksi dengan supplier ini akan tetap ada, namun tidak akan terkait dengan supplier ini lagi.')"
                          {{ $supplier->stock_transactions_count > 0 ? 'title="Supplier memiliki riwayat transaksi"' : '' }}>
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
              <i class="bx bx-store" style="font-size: 48px; color: #ddd;"></i>
              <h6 class="mt-2 text-muted">Belum ada data supplier</h6>
              <p class="text-muted mb-3">Mulai dengan menambahkan supplier pertama Anda</p>
              <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>
                Tambah Supplier Pertama
              </a>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Export function for suppliers
function exportData() {
  const headers = ['ID', 'Nama Supplier', 'Kontak Person', 'Telepon', 'Alamat', 'Total Transaksi', 'Total Item', 'Status'];
  const rows = [
    @foreach($suppliers as $supplier)
    [
      '{{ $supplier->id }}', 
      '{{ addslashes($supplier->supplier_name) }}', 
      '{{ addslashes($supplier->contact_person ?? "Tidak ada") }}', 
      '{{ $supplier->phone ?? "Tidak ada" }}', 
      '{{ addslashes($supplier->address ?? "Tidak ada") }}', 
      '{{ $supplier->stock_transactions_count }}',
      '{{ $supplier->items_count }}',
      '{{ $supplier->active_transactions_count > 0 ? "Aktif" : "Tidak Aktif" }}'
    ],
    @endforeach
  ];
  
  downloadCSV('suppliers', headers, rows);
}

// Search functionality  
document.getElementById('searchInput')?.addEventListener('keyup', function() {
  const filter = this.value.toLowerCase();
  const rows = document.querySelectorAll('tbody tr');
  
  rows.forEach(row => {
    if (row.children.length === 1) return;
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(filter) ? '' : 'none';
  });
});

// Download CSV helper function
function downloadCSV(filename, headers, rows) {
  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += headers.join(",") + "\n";
  
  rows.forEach(function(row) {
    csvContent += row.map(field => `"${field}"`).join(",") + "\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", filename + "_" + new Date().toISOString().slice(0,10) + ".csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>
@endpush

@push('styles')
<style>
.badge-sm {
  font-size: 0.6rem;
  padding: 0.2rem 0.4rem;
}

@media print {
  .btn, .breadcrumb, .card-header .d-flex .input-group, .card-header .d-flex .btn {
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
</style>
@endpush
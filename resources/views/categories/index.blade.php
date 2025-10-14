@extends('layouts.admin')

@section('title', 'Daftar Kategori - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Kategori</li>
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
            <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="Total Categories" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Kategori</span>
        <h3 class="card-title mb-2">{{ $categories->count() }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-category"></i> Kategori
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="With Description" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Dengan Deskripsi</span>
        <h3 class="card-title mb-2">{{ $categories->whereNotNull('description')->count() }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-comment"></i> Kategori
        </small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="With Items" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Dengan Item</span>
        <h3 class="card-title mb-2">{{ $categories->where('items_count', '>', 0)->count() }}</h3>
        <small class="text-info fw-semibold">
          <i class="bx bx-package"></i> Kategori
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
        <h3 class="card-title mb-2">{{ $categories->sum('items_count') }}</h3>
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
      <i class="bx bx-category me-2"></i>
      Daftar Kategori
    </h5>
    <div class="d-flex gap-2">
      <!-- Search -->
      <div class="input-group" style="width: 250px;">
        <span class="input-group-text"><i class="bx bx-search"></i></span>
        <input type="text" class="form-control" placeholder="Cari kategori..." id="searchInput">
      </div>
      <!-- Add Button -->
      <a href="{{ route('categories.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i>
        Tambah Kategori
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
            <i class="bx bx-category me-1"></i>
            Kategori
          </th>
          <th>
            <i class="bx bx-comment me-1"></i>
            Deskripsi
          </th>
          <th>
            <i class="bx bx-package me-1"></i>
            Total Item
          </th>
          <th>
            <i class="bx bx-time me-1"></i>
            Dibuat
          </th>
          <th class="text-center">
            <i class="bx bx-cog me-1"></i>
            Aksi
          </th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse ($categories as $category)
        <tr>
          <td class="text-center">
            <span class="badge bg-label-primary">#{{ $category->id }}</span>
          </td>
          <td>
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ ['primary', 'success', 'info', 'warning', 'danger'][$loop->index % 5] }}">
                  <i class="bx bx-category-alt"></i>
                </span>
              </div>
              <div>
                <strong>{{ $category->category_name }}</strong>
                <br><small class="text-muted">
                  <i class="bx bx-time-five"></i>
                  Dibuat {{ $category->created_at->diffForHumans() }}
                </small>
              </div>
            </div>
          </td>
          <td>
            @if($category->description)
              <div class="d-flex align-items-start">
                <i class="bx bx-comment-detail text-primary me-2 mt-1"></i>
                <div>
                  <span>{{ Str::limit($category->description, 50) }}</span>
                  @if(strlen($category->description) > 50)
                    <br><small class="text-muted">
                      <a href="#" onclick="showFullDescription('{{ addslashes($category->description) }}')" class="text-decoration-none">
                        Lihat selengkapnya
                      </a>
                    </small>
                  @endif
                </div>
              </div>
            @else
              <span class="text-muted">
                <i class="bx bx-comment-x me-1"></i>
                Tidak ada deskripsi
              </span>
            @endif
          </td>
          <td>
            @if($category->items_count > 0)
              <span class="badge bg-label-info">{{ $category->items_count }} Item</span>
            @else
              <span class="badge bg-label-secondary">0 Item</span>
            @endif
          </td>
          <td>
            <div class="d-flex flex-column">
              <small class="text-muted">{{ $category->created_at->format('d/m/Y') }}</small>
              <small class="text-muted">{{ $category->created_at->format('H:i') }}</small>
            </div>
          </td>
          <td class="text-center">
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('categories.show', $category->id) }}">
                  <i class="bx bx-show me-1"></i> 
                  Lihat Detail
                </a>
                <a class="dropdown-item" href="{{ route('categories.edit', $category->id) }}">
                  <i class="bx bx-edit-alt me-1"></i> 
                  Edit
                </a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger" 
                          onclick="return confirm('Apakah Anda yakin ingin menghapus kategori {{ $category->category_name }}?')"
                          {{ $category->items_count > 0 ? 'disabled title="Tidak dapat menghapus kategori yang memiliki item"' : '' }}>
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
          <td colspan="6" class="text-center py-4">
            <div class="d-flex flex-column align-items-center">
              <i class="bx bx-category" style="font-size: 48px; color: #ddd;"></i>
              <h6 class="mt-2 text-muted">Belum ada data kategori</h6>
              <p class="text-muted mb-3">Mulai dengan menambahkan kategori pertama Anda</p>
              <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>
                Tambah Kategori Pertama
              </a>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Simple Pagination -->
  <x-simple-pagination :items="$categories" type="kategori" />
</div>
@endsection

@push('scripts')
<script>
// Export function for categories
function exportData() {
  const headers = ['ID', 'Nama Kategori', 'Deskripsi', 'Total Item', 'Dibuat'];
  const rows = [
    @foreach($categories as $category)
    ['{{ $category->id }}', '{{ addslashes($category->category_name) }}', '{{ addslashes($category->description ?? "Tidak ada") }}', '{{ $category->items_count }}', '{{ $category->created_at->format("d/m/Y H:i") }}'],
    @endforeach
  ];
  
  downloadCSV('categories', headers, rows);
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('keyup', function() {
  const filter = this.value.toLowerCase();
  const rows = document.querySelectorAll('tbody tr');
  
  rows.forEach(row => {
    if (row.children.length === 1) return; // Skip empty state
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(filter) ? '' : 'none';
  });
});
</script>
@endpush

@push('styles')
<style>
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

.text-decoration-none:hover {
  text-decoration: underline !important;
}
</style>
@endpush
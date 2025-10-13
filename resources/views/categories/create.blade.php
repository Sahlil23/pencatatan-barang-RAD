@extends('layouts.admin')

@section('title', 'Tambah Kategori - Chicking BJM')

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
        <li class="breadcrumb-item active" aria-current="page">Tambah Kategori</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tambah Kategori Baru</h5>
        <small class="text-muted float-end">Form input kategori produk</small>
      </div>
      <div class="card-body">
        <form action="{{ route('categories.store') }}" method="POST">
          @csrf
          
          <!-- Category Name -->
          <div class="mb-3">
            <label class="form-label" for="category_name">Nama Kategori <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-category"></i></span>
              <input
                type="text"
                class="form-control @error('category_name') is-invalid @enderror"
                id="category_name"
                name="category_name"
                placeholder="Masukkan nama kategori"
                value="{{ old('category_name') }}"
                required
              />
            </div>
            @error('category_name')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Nama kategori harus unik dan maksimal 150 karakter</div>
            @enderror
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label class="form-label" for="description">Deskripsi</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-comment"></i></span>
              <textarea
                id="description"
                name="description"
                class="form-control @error('description') is-invalid @enderror"
                placeholder="Masukkan deskripsi kategori (opsional)"
                rows="4"
              >{{ old('description') }}</textarea>
            </div>
            @error('description')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Deskripsi kategori untuk memberikan informasi lebih detail</div>
            @enderror
          </div>
          <!-- Action Buttons -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
            <div>
              <button type="reset" class="btn btn-outline-warning me-2">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i>
                Simpan Kategori
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Info Card -->
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Informasi
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info" role="alert">
          <h6 class="alert-heading">Tips Kategori:</h6>
          <ul class="mb-0">
            <li>Gunakan nama yang jelas dan mudah dipahami</li>
            <li>Nama kategori harus unik</li>
            <li>Deskripsi membantu pengguna memahami kategori</li>
            <!-- <li>Kategori nonaktif tidak akan muncul saat input item</li> -->
          </ul>
        </div>
      </div>
    </div>

    <!-- Preview Card -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-show me-2"></i>
          Preview Kategori
        </h6>
      </div>
      <div class="card-body">
        <div class="preview-content">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-category"></i>
              </span>
            </div>
            <div>
              <h6 class="mb-0" id="preview-name">Nama Kategori</h6>
              <small class="text-muted" id="preview-desc">Deskripsi akan muncul di sini</small>
            </div>
          </div>
          <!-- <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Status:</small>
            <span class="badge bg-success" id="preview-status">Aktif</span>
          </div> -->
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Elements
  const categoryNameInput = document.getElementById('category_name');
  const descriptionInput = document.getElementById('description');
  const isActiveSwitch = document.getElementById('is_active');
  const statusBadge = document.getElementById('status-badge');
  
  // Preview elements
  const previewName = document.getElementById('preview-name');
  const previewDesc = document.getElementById('preview-desc');
  const previewStatus = document.getElementById('preview-status');

  // Update status badge
  function updateStatusBadge() {
    if (isActiveSwitch.checked) {
      statusBadge.textContent = 'Aktif';
      statusBadge.className = 'badge bg-success';
      previewStatus.textContent = 'Aktif';
      previewStatus.className = 'badge bg-success';
    } else {
      statusBadge.textContent = 'Nonaktif';
      statusBadge.className = 'badge bg-secondary';
      previewStatus.textContent = 'Nonaktif';
      previewStatus.className = 'badge bg-secondary';
    }
  }

  // Update preview
  function updatePreview() {
    const name = categoryNameInput.value || 'Nama Kategori';
    const desc = descriptionInput.value || 'Deskripsi akan muncul di sini';
    
    previewName.textContent = name;
    previewDesc.textContent = desc;
  }

  // Event listeners
  isActiveSwitch.addEventListener('change', updateStatusBadge);
  categoryNameInput.addEventListener('input', updatePreview);
  descriptionInput.addEventListener('input', updatePreview);

  // Initialize
  updateStatusBadge();
  updatePreview();

  // Form validation
  const form = document.querySelector('form');
  form.addEventListener('submit', function(e) {
    const categoryName = categoryNameInput.value.trim();
    
    if (!categoryName) {
      e.preventDefault();
      categoryNameInput.focus();
      categoryNameInput.classList.add('is-invalid');
      
      // Show error message
      let errorDiv = categoryNameInput.parentNode.parentNode.querySelector('.text-danger');
      if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'form-text text-danger';
        categoryNameInput.parentNode.parentNode.appendChild(errorDiv);
      }
      errorDiv.textContent = 'Nama kategori wajib diisi';
      
      return false;
    }
  });

  // Remove validation error on input
  categoryNameInput.addEventListener('input', function() {
    this.classList.remove('is-invalid');
    const errorDiv = this.parentNode.parentNode.querySelector('.text-danger');
    if (errorDiv && errorDiv.textContent === 'Nama kategori wajib diisi') {
      errorDiv.remove();
    }
  });
});
</script>
@endpush
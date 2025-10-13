@extends('layouts.admin')

@section('title', 'Edit Kategori - Chicking BJM')

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
        <li class="breadcrumb-item active" aria-current="page">Edit Kategori</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Kategori</h5>
        <small class="text-muted float-end">Form edit kategori produk</small>
      </div>
      <div class="card-body">
        <form action="{{ route('categories.update', $category->id) }}" method="POST">
          @csrf
          @method('PUT')
          
          <!-- Category ID Info -->
          <div class="mb-3">
            <label class="form-label">ID Kategori</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-hash"></i></span>
              <input
                type="text"
                class="form-control"
                value="#{{ $category->id }}"
                readonly
              />
            </div>
            <div class="form-text">ID kategori tidak dapat diubah</div>
          </div>

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
                value="{{ old('category_name', $category->category_name) }}"
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
              >{{ old('description', $category->description) }}</textarea>
            </div>
            @error('description')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Deskripsi kategori untuk memberikan informasi lebih detail</div>
            @enderror
          </div>

          <!-- Created/Updated Info -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Dibuat pada</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                <input
                  type="text"
                  class="form-control"
                  value="{{ $category->created_at->format('d/m/Y H:i') }}"
                  readonly
                />
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Terakhir diupdate</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-time"></i></span>
                <input
                  type="text"
                  class="form-control"
                  value="{{ $category->updated_at->format('d/m/Y H:i') }}"
                  readonly
                />
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
            <div>
              <a href="{{ route('categories.show', $category->id) }}" class="btn btn-outline-info me-2">
                <i class="bx bx-show me-1"></i>
                Lihat Detail
              </a>
              <button type="reset" class="btn btn-outline-warning me-2">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i>
                Update Kategori
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
          Informasi Kategori
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-warning" role="alert">
          <h6 class="alert-heading">
            <i class="bx bx-error-circle me-1"></i>
            Perhatian!
          </h6>
          <ul class="mb-0">
            <li>Perubahan nama kategori akan mempengaruhi semua item terkait</li>
            <li>Pastikan nama kategori tetap relevan dengan produk</li>
            <li>Kategori ini memiliki <strong>{{ $category->items->count() }} item</strong> terkait</li>
          </ul>
        </div>

        <hr>

        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">ID Kategori:</span>
          <span class="badge bg-primary">#{{ $category->id }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Total Item:</span>
          <span class="badge bg-info">{{ $category->items->count() }} Item</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Dibuat:</span>
          <span class="text-muted">{{ $category->created_at->diffForHumans() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Update terakhir:</span>
          <span class="text-muted">{{ $category->updated_at->diffForHumans() }}</span>
        </div>
      </div>
    </div>

    <!-- Preview Card -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-show me-2"></i>
          Preview Perubahan
        </h6>
      </div>
      <div class="card-body">
        <div class="preview-content">
          <div class="d-flex align-items-center mb-3">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-category"></i>
              </span>
            </div>
            <div class="flex-grow-1">
              <h6 class="mb-0" id="preview-name">{{ $category->category_name }}</h6>
              <small class="text-muted" id="preview-desc">{{ $category->description ?: 'Tidak ada deskripsi' }}</small>
            </div>
          </div>
          
          <!-- Original vs New Comparison -->
          <div class="border rounded p-3">
            <h6 class="text-muted mb-2">Perbandingan:</h6>
            <div class="row">
              <div class="col-6">
                <small class="text-muted d-block">Nama Asli:</small>
                <small class="fw-semibold" id="original-name">{{ $category->category_name }}</small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Nama Baru:</small>
                <small class="fw-semibold text-primary" id="new-name">{{ $category->category_name }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Related Items -->
    @if($category->items->count() > 0)
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-package me-2"></i>
          Item Terkait ({{ $category->items->count() }})
        </h6>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          @foreach($category->items->take(5) as $item)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="bx bx-box"></i>
                </span>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">{{ Str::limit($item->item_name, 20) }}</h6>
                <small class="text-muted">{{ $item->sku }}</small>
              </div>
              <div class="text-end">
                <small class="text-muted">{{ $item->current_stock }} {{ $item->unit }}</small>
              </div>
            </div>
          </div>
          @endforeach
          
          @if($category->items->count() > 5)
          <div class="list-group-item px-0 py-2 border-0 text-center">
            <small class="text-muted">Dan {{ $category->items->count() - 5 }} item lainnya...</small>
          </div>
          @endif
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
  // Elements
  const categoryNameInput = document.getElementById('category_name');
  const descriptionInput = document.getElementById('description');
  
  // Preview elements
  const previewName = document.getElementById('preview-name');
  const previewDesc = document.getElementById('preview-desc');
  const newName = document.getElementById('new-name');

  // Original values
  const originalName = "{{ $category->category_name }}";
  const originalDesc = "{{ $category->description ?? '' }}";

  // Update preview
  function updatePreview() {
    const name = categoryNameInput.value || originalName;
    const desc = descriptionInput.value || 'Tidak ada deskripsi';
    
    previewName.textContent = name;
    previewDesc.textContent = desc;
    newName.textContent = name;
    
    // Highlight changes
    if (name !== originalName) {
      newName.classList.add('text-warning');
      newName.classList.remove('text-primary');
    } else {
      newName.classList.add('text-primary');
      newName.classList.remove('text-warning');
    }
  }

  // Event listeners
  categoryNameInput.addEventListener('input', updatePreview);
  descriptionInput.addEventListener('input', updatePreview);

  // Initialize
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

    // Confirm if no changes made
    if (categoryName === originalName && descriptionInput.value === originalDesc) {
      if (!confirm('Tidak ada perubahan yang dibuat. Apakah Anda yakin ingin melanjutkan?')) {
        e.preventDefault();
        return false;
      }
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

  // Reset button functionality
  const resetButton = document.querySelector('button[type="reset"]');
  resetButton.addEventListener('click', function() {
    setTimeout(() => {
      updatePreview();
    }, 10);
  });
});
</script>
@endpush
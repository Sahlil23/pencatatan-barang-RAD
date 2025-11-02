@extends('layouts.admin')

@section('title', 'Edit Item - Chicking BJM')

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
          <a href="{{ route('items.index') }}">Data Master Item</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Edit Item</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-edit me-2"></i>
          Edit Data Master Item
        </h5>
        <small class="text-muted float-end">Master Data Management</small>
      </div>
      <div class="card-body">
        <form action="{{ route('items.update', $item->id) }}" method="POST">
          @csrf
          @method('PUT')
          
          <!-- Item Code -->
          <div class="mb-3">
            <label class="form-label" for="item_code">Kode Item <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-barcode"></i></span>
              <input
                type="text"
                class="form-control @error('item_code') is-invalid @enderror"
                id="item_code"
                name="item_code"
                placeholder="Contoh: CHK001"
                value="{{ old('item_code', $item->item_code) }}"
                required
                style="text-transform: uppercase;"
              />
            </div>
            @error('item_code')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Kode item harus unik dan maksimal 50 karakter</div>
            @enderror
          </div>

          <!-- Item Name -->
          <div class="mb-3">
            <label class="form-label" for="item_name">Nama Item <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-package"></i></span>
              <input
                type="text"
                class="form-control @error('item_name') is-invalid @enderror"
                id="item_name"
                name="item_name"
                placeholder="Masukkan nama item"
                value="{{ old('item_name', $item->item_name) }}"
                required
              />
            </div>
            @error('item_name')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Nama item maksimal 150 karakter</div>
            @enderror
          </div>

          <!-- Category -->
          <div class="mb-3">
            <label class="form-label" for="category_id">Kategori <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-category"></i></span>
              <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                <option value="">Pilih Kategori</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                  {{ $category->category_name }}
                </option>
                @endforeach
              </select>
            </div>
            @error('category_id')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Pilih kategori yang sesuai untuk item ini</div>
            @enderror
          </div>

          <!-- Unit -->
          <div class="mb-3">
            <label class="form-label" for="unit">Unit <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-cube"></i></span>
              <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                <option value="">Pilih Unit</option>
                <option value="pcs" {{ old('unit', $item->unit) == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                <option value="kg" {{ old('unit', $item->unit) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                <option value="gram" {{ old('unit', $item->unit) == 'gram' ? 'selected' : '' }}>Gram (gram)</option>
                <option value="liter" {{ old('unit', $item->unit) == 'liter' ? 'selected' : '' }}>Liter (liter)</option>
                <option value="ml" {{ old('unit', $item->unit) == 'ml' ? 'selected' : '' }}>Mililiter (ml)</option>
                <option value="box" {{ old('unit', $item->unit) == 'box' ? 'selected' : '' }}>Box (box)</option>
                <option value="pack" {{ old('unit', $item->unit) == 'pack' ? 'selected' : '' }}>Pack (pack)</option>
                <option value="dozen" {{ old('unit', $item->unit) == 'dozen' ? 'selected' : '' }}>Dozen (dozen)</option>
              </select>
            </div>
            @error('unit')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Pilih unit yang sesuai untuk item ini</div>
            @enderror
          </div>

          <!-- Low Stock Threshold -->
          <div class="mb-3">
            <label class="form-label" for="low_stock_threshold">Batas Minimum Stok <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-error"></i></span>
              <input
                type="number"
                class="form-control @error('low_stock_threshold') is-invalid @enderror"
                id="low_stock_threshold"
                name="low_stock_threshold"
                placeholder="Contoh: 10"
                value="{{ old('low_stock_threshold', $item->low_stock_threshold) }}"
                step="0.01"
                min="0"
                required
              />
              <span class="input-group-text" id="threshold-unit">{{ $item->unit }}</span>
            </div>
            @error('low_stock_threshold')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Batas untuk notifikasi stok menipis</div>
            @enderror
          </div>

          <!-- Unit Cost (Optional) -->
          <div class="mb-3">
            <label class="form-label" for="unit_cost">Harga Per Unit</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-dollar"></i></span>
              <input
                type="number"
                class="form-control @error('unit_cost') is-invalid @enderror"
                id="unit_cost"
                name="unit_cost"
                placeholder="Contoh: 25000"
                value="{{ old('unit_cost', $item->unit_cost) }}"
                step="0.01"
                min="0"
              />
              <span class="input-group-text">IDR</span>
            </div>
            @error('unit_cost')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Harga per unit (opsional, untuk referensi)</div>
            @enderror
          </div>

          <!-- Status -->
          <div class="mb-3">
            <label class="form-label" for="status">Status Item <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-signal-3"></i></span>
              <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="ACTIVE" {{ old('status', $item->status) == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                <option value="INACTIVE" {{ old('status', $item->status) == 'INACTIVE' ? 'selected' : '' }}>Nonaktif</option>
              </select>
            </div>
            @error('status')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Status aktif/nonaktif untuk master data</div>
            @enderror
          </div>

          <!-- Master Data Info -->
          <div class="alert alert-info">
            <h6 class="alert-heading">
              <i class="bx bx-info-circle me-2"></i>
              Data Master Item
            </h6>
            <p class="mb-0">
              <strong>Focus:</strong> Data referensi item untuk semua modul sistem |
              <strong>Stock:</strong> Dikelola di modul warehouse terpisah |
              <strong>Status:</strong> Nonaktif = tidak muncul di transaksi baru
            </p>
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
                  value="{{ $item->created_at->format('d/m/Y H:i') }}"
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
                  value="{{ $item->updated_at->format('d/m/Y H:i') }}"
                  readonly
                />
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
            <div>
              <a href="{{ route('items.show', $item->id) }}" class="btn btn-outline-info me-2">
                <i class="bx bx-show me-1"></i>
                Lihat Detail
              </a>
              <button type="reset" class="btn btn-outline-secondary me-2">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i>
                Update Item
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
          Informasi Item
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-primary" role="alert">
          <h6 class="alert-heading">
            <i class="bx bx-package me-1"></i>
            Master Data
          </h6>
          <ul class="mb-0">
            <li>Kode item harus unik dalam sistem</li>
            <li>Kategori mempengaruhi pengelompokan laporan</li>
            <li>Unit cost untuk referensi perhitungan</li>
            <li>Status nonaktif = tidak tampil di transaksi baru</li>
            <li>Data stok dikelola terpisah di warehouse</li>
          </ul>
        </div>

        <hr>

        <!-- Item Statistics -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Kode Item:</span>
          <span class="badge bg-primary">{{ $item->item_code }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Kategori:</span>
          <span class="badge bg-info">{{ $item->category->category_name ?? 'Tidak ada' }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Dibuat:</span>
          <span class="text-muted">{{ $item->created_at->diffForHumans() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Update terakhir:</span>
          <span class="text-muted">{{ $item->updated_at->diffForHumans() }}</span>
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
                <i class="bx bx-package"></i>
              </span>
            </div>
            <div class="flex-grow-1">
              <h6 class="mb-0" id="preview-name">{{ $item->item_name }}</h6>
              <small class="text-muted">
                <span id="preview-code">{{ $item->item_code }}</span> • 
                <span id="preview-unit">{{ $item->unit }}</span>
              </small>
            </div>
          </div>
          
          <div class="border rounded p-3">
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Kategori:</small>
                <small class="fw-semibold" id="preview-category">{{ $item->category->category_name ?? 'Tidak ada' }}</small>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Unit Cost:</small>
                <small class="fw-semibold text-success" id="preview-cost">
                  @if($item->unit_cost)
                    Rp {{ number_format($item->unit_cost, 0) }}
                  @else
                    -
                  @endif
                </small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Batas Minimum:</small>
                <small class="fw-semibold" id="preview-threshold">{{ number_format($item->low_stock_threshold, 2) }}</small>
              </div>
            </div>
            <div class="text-center">
              <span class="badge bg-{{ $item->status === 'ACTIVE' ? 'success' : 'warning' }}" id="preview-badge">
                {{ $item->status === 'ACTIVE' ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
          </div>
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
  const itemCodeInput = document.getElementById('item_code');
  const itemNameInput = document.getElementById('item_name');
  const categorySelect = document.getElementById('category_id');
  const unitSelect = document.getElementById('unit');
  const thresholdInput = document.getElementById('low_stock_threshold');
  const unitCostInput = document.getElementById('unit_cost');
  const statusSelect = document.getElementById('status');
  
  // Preview elements
  const previewName = document.getElementById('preview-name');
  const previewCode = document.getElementById('preview-code');
  const previewUnit = document.getElementById('preview-unit');
  const previewCategory = document.getElementById('preview-category');
  const previewStatus = document.getElementById('preview-status');
  const previewCost = document.getElementById('preview-cost');
  const previewThreshold = document.getElementById('preview-threshold');
  const previewBadge = document.getElementById('preview-badge');
  const thresholdUnit = document.getElementById('threshold-unit');

  // Update preview
  function updatePreview() {
    const name = itemNameInput.value || "{{ $item->item_name }}";
    const code = itemCodeInput.value || "{{ $item->item_code }}";
    const unit = unitSelect.value || "{{ $item->unit }}";
    const threshold = thresholdInput.value || "{{ $item->low_stock_threshold }}";
    const cost = unitCostInput.value || 0;
    const status = statusSelect.value || "{{ $item->status }}";
    
    const categoryText = categorySelect.options[categorySelect.selectedIndex]?.text || 'Tidak ada';
    const statusText = status === 'ACTIVE' ? 'Aktif' : 'Nonaktif';
    const statusColor = status === 'ACTIVE' ? 'success' : 'warning';
    
    previewName.textContent = name;
    previewCode.textContent = code;
    previewUnit.textContent = unit;
    previewCategory.textContent = categoryText;
    previewStatus.textContent = statusText;
    previewThreshold.textContent = parseFloat(threshold).toLocaleString('id-ID', { minimumFractionDigits: 2 });
    thresholdUnit.textContent = unit;
    
    // Update cost
    if (cost && parseFloat(cost) > 0) {
      previewCost.textContent = 'Rp ' + parseFloat(cost).toLocaleString('id-ID');
    } else {
      previewCost.textContent = '-';
    }
    
    // Update badge
    previewBadge.textContent = statusText;
    previewBadge.className = `badge bg-${statusColor}`;
  }

  // Event listeners
  itemNameInput.addEventListener('input', updatePreview);
  itemCodeInput.addEventListener('input', updatePreview);
  categorySelect.addEventListener('change', updatePreview);
  unitSelect.addEventListener('change', updatePreview);
  thresholdInput.addEventListener('input', updatePreview);
  unitCostInput.addEventListener('input', updatePreview);
  statusSelect.addEventListener('change', updatePreview);

  // Item code formatting
  itemCodeInput.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
  });

  // Initialize
  updatePreview();

  // Form validation
  const form = document.querySelector('form[action*="update"]');
  form.addEventListener('submit', function(e) {
    const code = itemCodeInput.value.trim();
    const itemName = itemNameInput.value.trim();
    const categoryId = categorySelect.value;
    const unit = unitSelect.value;
    const threshold = thresholdInput.value;
    
    if (!code || !itemName || !categoryId || !unit || !threshold) {
      e.preventDefault();
      alert('Mohon lengkapi semua field yang wajib diisi');
      return false;
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

.form-control:focus {
  border-color: #696cff;
  box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

.preview-content .border {
  background-color: #f8f9fa;
}
</style>
@endpush
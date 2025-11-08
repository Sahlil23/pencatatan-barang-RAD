@extends('layouts.admin')

@section('title', 'Tambah Item - Chicking BJM')

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
        <li class="breadcrumb-item active" aria-current="page">Tambah Item</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-plus me-2"></i>
          Tambah Item Baru
        </h5>
        <small class="text-muted float-end">Master Data Management</small>
      </div>
      <div class="card-body">
        <form action="{{ route('items.store') }}" method="POST">
          @csrf
          
          <!-- Item Code -->
          <div class="mb-3">
            <label class="form-label" for="sku">Kode Item <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-barcode"></i></span>
              <input
                type="text"
                class="form-control @error('sku') is-invalid @enderror"
                id="sku"
                name="sku"
                placeholder="Contoh: CHK001 (kosongkan untuk auto-generate)"
                value="{{ old('sku') }}"
                style="text-transform: uppercase;"
              />
              <button type="button" class="btn btn-outline-secondary" onclick="generateItemCode()">
                <i class="bx bx-refresh"></i>
              </button>
            </div>
            @error('sku')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Kode item harus unik. Kosongkan untuk auto-generate berdasarkan kategori.</div>
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
                value="{{ old('item_name') }}"
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
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                <option value="gram" {{ old('unit') == 'gram' ? 'selected' : '' }}>Gram (gram)</option>
                <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter (liter)</option>
                <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>Mililiter (ml)</option>
                <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box (box)</option>
                <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Pack (pack)</option>
                <option value="dozen" {{ old('unit') == 'dozen' ? 'selected' : '' }}>Dozen (dozen)</option>
              </select>
            </div>
            @error('unit')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Pilih unit yang sesuai untuk item ini</div>
            @enderror
          </div>

          <!-- Master Data Information Row -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="low_stock_threshold">Batas Minimum Stok <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-error"></i></span>
                <input
                  type="number"
                  class="form-control @error('low_stock_threshold') is-invalid @enderror"
                  id="low_stock_threshold"
                  name="low_stock_threshold"
                  placeholder="10"
                  value="{{ old('low_stock_threshold', 10) }}"
                  step="0.01"
                  min="0"
                  required
                />
                <span class="input-group-text" id="threshold-unit">pcs</span>
              </div>
              @error('low_stock_threshold')
                <div class="form-text text-danger">{{ $message }}</div>
              @else
                <div class="form-text">Batas untuk notifikasi stok menipis</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="unit_cost">Harga Per Unit</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-dollar"></i></span>
                <input
                  type="number"
                  class="form-control @error('unit_cost') is-invalid @enderror"
                  id="unit_cost"
                  name="unit_cost"
                  placeholder="25000"
                  value="{{ old('unit_cost') }}"
                  step="0.01"
                  min="0"
                />
                <span class="input-group-text">IDR</span>
              </div>
              @error('unit_cost')
                <div class="form-text text-danger">{{ $message }}</div>
              @else
                <div class="form-text">Harga per unit (opsional)</div>
              @enderror
            </div>
          </div>

          <!-- Master Data Info -->
          <div class="alert alert-info">
            <h6 class="alert-heading">
              <i class="bx bx-info-circle me-2"></i>
              Data Master Item
            </h6>
            <p class="mb-0">
              <strong>Focus:</strong> Data referensi item untuk semua modul sistem |
              <strong>Stock:</strong> Dikelola terpisah di modul warehouse |
              <strong>Auto-Code:</strong> Kode otomatis berdasarkan kategori jika dikosongkan
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
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
                Simpan Item
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
          Informasi Master Data
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-primary" role="alert">
          <h6 class="alert-heading">Data Master Item:</h6>
          <ul class="mb-0">
            <li>Kode item auto-generate berdasarkan kategori</li>
            <li>Unit cost untuk referensi perhitungan</li>
            <li>Threshold untuk notifikasi stok menipis</li>
            <li>Status mengatur visibilitas di transaksi</li>
            <li>Data stok dikelola terpisah di warehouse</li>
          </ul>
        </div>
        
        <hr>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Total Item:</span>
          <span class="badge bg-primary">{{ App\Models\Item::count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Kategori Tersedia:</span>
          <span class="badge bg-success">{{ $categories->count() }}</span>
        </div>
      </div>
    </div>

    <!-- Preview Card -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-show me-2"></i>
          Preview Item
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
              <h6 class="mb-0" id="preview-name">Nama Item</h6>
              <small class="text-muted">
                <span id="preview-code">AUTO-CODE</span> • 
                <span id="preview-unit">Unit</span>
              </small>
            </div>
          </div>
          
          <div class="border rounded p-3">
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Kategori:</small>
                <small class="fw-semibold" id="preview-category">Belum dipilih</small>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Unit Cost:</small>
                <small class="fw-semibold text-success" id="preview-cost">-</small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Batas Minimum:</small>
                <small class="fw-semibold text-warning" id="preview-threshold">10</small>
              </div>
            </div>
            <div class="text-center">
              <span class="badge bg-success" id="preview-badge">Master Data</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-flash me-2"></i>
          Quick Actions
        </h6>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('categories.create') }}" class="btn btn-outline-primary btn-sm" target="_blank">
            <i class="bx bx-plus me-1"></i>
            Tambah Kategori Baru
          </a>
          <a href="{{ route('items.index') }}" class="btn btn-outline-info btn-sm">
            <i class="bx bx-list-ul me-1"></i>
            Lihat Semua Item
          </a>
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
  const itemCodeInput = document.getElementById('sku');
  const itemNameInput = document.getElementById('item_name');
  const categorySelect = document.getElementById('category_id');
  const unitSelect = document.getElementById('unit');
  const thresholdInput = document.getElementById('low_stock_threshold');
  const unitCostInput = document.getElementById('unit_cost');
  // const statusSelect = document.getElementById('status');
  
  // Preview elements
  const previewName = document.getElementById('preview-name');
  const previewCode = document.getElementById('preview-code');
  const previewUnit = document.getElementById('preview-unit');
  const previewCategory = document.getElementById('preview-category');
  // const previewStatus = document.getElementById('preview-status');
  const previewCost = document.getElementById('preview-cost');
  const previewThreshold = document.getElementById('preview-threshold');
  const previewBadge = document.getElementById('preview-badge');
  
  // Unit display elements
  const thresholdUnit = document.getElementById('threshold-unit');

  // Generate Item Code function
  window.generateItemCode = function() {
    const categoryId = categorySelect.value;
    if (!categoryId) {
      alert('Pilih kategori terlebih dahulu');
      categorySelect.focus();
      return;
    }
    
    // Get category code from selected option
    const categoryText = categorySelect.options[categorySelect.selectedIndex].text;
    const categoryCode = categoryText.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '');
    
    const timestamp = Date.now().toString().slice(-4);
    const randomNum = Math.floor(Math.random() * 99).toString().padStart(2, '0');
    const code = `${categoryCode || 'ITM'}${timestamp}${randomNum}`;
    
    itemCodeInput.value = code;
    updatePreview();
  };

  // Update preview
  function updatePreview() {
    const name = itemNameInput.value || 'Nama Item';
    const code = itemCodeInput.value || 'AUTO-CODE';
    const unit = unitSelect.value || 'Unit';
    const threshold = parseFloat(thresholdInput.value) || 10;
    const cost = parseFloat(unitCostInput.value) || 0;
    const status = statusSelect.value || 'ACTIVE';
    
    const categoryText = categorySelect.options[categorySelect.selectedIndex]?.text === 'Pilih Kategori' ? 
                        'Belum dipilih' : 
                        categorySelect.options[categorySelect.selectedIndex]?.text || 'Belum dipilih';
    
    const statusText = status === 'ACTIVE' ? 'Aktif' : 'Nonaktif';
    const statusColor = status === 'ACTIVE' ? 'success' : 'warning';
    
    previewName.textContent = name;
    previewCode.textContent = code;
    previewUnit.textContent = unit;
    previewCategory.textContent = categoryText;
    previewStatus.textContent = statusText;
    previewThreshold.textContent = threshold.toLocaleString('id-ID', { minimumFractionDigits: 2 });
    
    // Update unit displays
    thresholdUnit.textContent = unit;
    
    // Update cost
    if (cost > 0) {
      previewCost.textContent = 'Rp ' + cost.toLocaleString('id-ID');
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

  // Auto-generate code on category change if code is empty
  categorySelect.addEventListener('change', function() {
    if (!itemCodeInput.value.trim() && this.value) {
      generateItemCode();
    }
    updatePreview();
  });

  // Initialize
  updatePreview();

  // Form validation
  const form = document.querySelector('form');
  form.addEventListener('submit', function(e) {
    const itemName = itemNameInput.value.trim();
    const categoryId = categorySelect.value;
    const unit = unitSelect.value;
    const threshold = thresholdInput.value;
    
    if (!itemName || !categoryId || !unit || !threshold) {
      e.preventDefault();
      alert('Mohon lengkapi semua field yang wajib diisi');
      return false;
    }
    
    if (parseFloat(threshold) < 0) {
      e.preventDefault();
      alert('Batas minimum stok tidak boleh negatif');
      thresholdInput.focus();
      return false;
    }
    
    if (unitCostInput.value && parseFloat(unitCostInput.value) < 0) {
      e.preventDefault();
      alert('Unit cost tidak boleh negatif');
      unitCostInput.focus();
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

  // Focus management
  itemCodeInput.addEventListener('focus', function() {
    if (!this.value && categorySelect.value) {
      generateItemCode();
    }
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

.quick-actions .btn {
  justify-content: flex-start;
}
</style>
@endpush
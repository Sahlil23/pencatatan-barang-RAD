
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
          <a href="{{ route('items.index') }}">Item</a>
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
        <h5 class="mb-0">Tambah Item Baru</h5>
        <small class="text-muted float-end">Form input data item</small>
      </div>
      <div class="card-body">
        <form action="{{ route('items.store') }}" method="POST">
          @csrf
          
          <!-- SKU -->
          <div class="mb-3">
            <label class="form-label" for="sku">SKU (Stock Keeping Unit) <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-barcode"></i></span>
              <input
                type="text"
                class="form-control @error('sku') is-invalid @enderror"
                id="sku"
                name="sku"
                placeholder="Contoh: CHK-001"
                value="{{ old('sku') }}"
                required
                style="text-transform: uppercase;"
              />
              <button type="button" class="btn btn-outline-secondary" onclick="generateSKU()">
                <i class="bx bx-refresh"></i>
              </button>
            </div>
            @error('sku')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">SKU harus unik dan maksimal 50 karakter. Klik refresh untuk generate otomatis.</div>
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

          <!-- Category and Supplier Row -->
          <div class="row mb-3">
            <div class="col-md-6">
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
                <div class="form-text">
                  <a href="{{ route('categories.create') }}" target="_blank" class="text-decoration-none">
                    <i class="bx bx-plus me-1"></i>Tambah kategori baru
                  </a>
                </div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="supplier_id">Supplier</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-store"></i></span>
                <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                  <option value="">Pilih Supplier (Opsional)</option>
                  @foreach($suppliers as $supplier)
                  <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->supplier_name }}
                  </option>
                  @endforeach
                </select>
              </div>
              @error('supplier_id')
                <div class="form-text text-danger">{{ $message }}</div>
              @else
                <div class="form-text">
                  <a href="{{ route('suppliers.create') }}" target="_blank" class="text-decoration-none">
                    <i class="bx bx-plus me-1"></i>Tambah supplier baru
                  </a>
                </div>
              @enderror
            </div>
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

          <!-- Stock Information Row -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="current_stock">Stok Awal <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-box"></i></span>
                <input
                  type="number"
                  class="form-control @error('current_stock') is-invalid @enderror"
                  id="current_stock"
                  name="current_stock"
                  placeholder="0"
                  value="{{ old('current_stock', 0) }}"
                  step="0.01"
                  min="0"
                  required
                />
                <span class="input-group-text" id="stock-unit">pcs</span>
              </div>
              @error('current_stock')
                <div class="form-text text-danger">{{ $message }}</div>
              @else
                <div class="form-text">Stok awal item ini</div>
              @enderror
            </div>
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
                <div class="form-text">Sistem akan memberikan peringatan jika stok mencapai batas ini</div>
              @enderror
            </div>
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
          Informasi
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info" role="alert">
          <h6 class="alert-heading">Tips Item Baru:</h6>
          <ul class="mb-0">
            <li>SKU harus unik untuk setiap item</li>
            <li>Nama item harus jelas dan deskriptif</li>
            <li>Pilih kategori yang sesuai</li>
            <li>Set batas minimum stok untuk notifikasi</li>
            <li>Stok awal bisa diubah nanti</li>
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
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Supplier Tersedia:</span>
          <span class="badge bg-info">{{ $suppliers->count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Stok Menipis:</span>
          <span class="badge bg-warning">{{ App\Models\Item::lowStock()->count() }}</span>
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
                <span id="preview-sku">SKU</span> • 
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
              <div class="col-6">
                <small class="text-muted d-block">Supplier:</small>
                <small class="fw-semibold" id="preview-supplier">Belum dipilih</small>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Stok Awal:</small>
                <small class="fw-semibold text-primary" id="preview-stock">0</small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Batas Minimum:</small>
                <small class="fw-semibold text-warning" id="preview-threshold">10</small>
              </div>
            </div>
            <div class="text-center">
              <span class="badge bg-success" id="preview-status">Stok Tersedia</span>
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
          <a href="{{ route('suppliers.create') }}" class="btn btn-outline-success btn-sm" target="_blank">
            <i class="bx bx-plus me-1"></i>
            Tambah Supplier Baru
          </a>
          <a href="{{ route('items.index') }}" class="btn btn-outline-info btn-sm">
            <i class="bx bx-list-ul me-1"></i>
            Lihat Semua Item
          </a>
          <a href="{{ route('items.low-stock') }}" class="btn btn-outline-warning btn-sm">
            <i class="bx bx-error me-1"></i>
            Item Stok Menipis
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
  const skuInput = document.getElementById('sku');
  const itemNameInput = document.getElementById('item_name');
  const categorySelect = document.getElementById('category_id');
  const supplierSelect = document.getElementById('supplier_id');
  const unitSelect = document.getElementById('unit');
  const currentStockInput = document.getElementById('current_stock');
  const thresholdInput = document.getElementById('low_stock_threshold');
  
  // Preview elements
  const previewName = document.getElementById('preview-name');
  const previewSku = document.getElementById('preview-sku');
  const previewUnit = document.getElementById('preview-unit');
  const previewCategory = document.getElementById('preview-category');
  const previewSupplier = document.getElementById('preview-supplier');
  const previewStock = document.getElementById('preview-stock');
  const previewThreshold = document.getElementById('preview-threshold');
  const previewStatus = document.getElementById('preview-status');
  
  // Unit display elements
  const stockUnit = document.getElementById('stock-unit');
  const thresholdUnit = document.getElementById('threshold-unit');

  // Generate SKU function
  window.generateSKU = function() {
    const prefix = 'CHK';
    const timestamp = Date.now().toString().slice(-6);
    const randomNum = Math.floor(Math.random() * 99).toString().padStart(2, '0');
    const sku = `${prefix}-${timestamp}${randomNum}`;
    skuInput.value = sku;
    updatePreview();
  };

  // Update preview
  function updatePreview() {
    const name = itemNameInput.value || 'Nama Item';
    const sku = skuInput.value || 'SKU';
    const unit = unitSelect.value || 'Unit';
    const stock = parseFloat(currentStockInput.value) || 0;
    const threshold = parseFloat(thresholdInput.value) || 0;
    
    const categoryText = categorySelect.options[categorySelect.selectedIndex]?.text === 'Pilih Kategori' ? 
                        'Belum dipilih' : 
                        categorySelect.options[categorySelect.selectedIndex]?.text || 'Belum dipilih';
    
    const supplierText = supplierSelect.options[supplierSelect.selectedIndex]?.text === 'Pilih Supplier (Opsional)' ? 
                        'Belum dipilih' : 
                        supplierSelect.options[supplierSelect.selectedIndex]?.text || 'Belum dipilih';
    
    previewName.textContent = name;
    previewSku.textContent = sku;
    previewUnit.textContent = unit;
    previewCategory.textContent = categoryText;
    previewSupplier.textContent = supplierText;
    previewStock.textContent = stock.toLocaleString('id-ID', { minimumFractionDigits: 2 });
    previewThreshold.textContent = threshold.toLocaleString('id-ID', { minimumFractionDigits: 2 });
    
    // Update unit displays
    stockUnit.textContent = unit;
    thresholdUnit.textContent = unit;
    
    // Update status
    let status = 'Stok Tersedia';
    let statusClass = 'bg-success';
    
    if (stock === 0) {
      status = 'Stok Habis';
      statusClass = 'bg-danger';
    } else if (stock <= threshold) {
      status = 'Stok Menipis';
      statusClass = 'bg-warning';
    }
    
    previewStatus.textContent = status;
    previewStatus.className = `badge ${statusClass}`;
  }

  // Event listeners
  itemNameInput.addEventListener('input', updatePreview);
  skuInput.addEventListener('input', updatePreview);
  categorySelect.addEventListener('change', updatePreview);
  supplierSelect.addEventListener('change', updatePreview);
  unitSelect.addEventListener('change', updatePreview);
  currentStockInput.addEventListener('input', updatePreview);
  thresholdInput.addEventListener('input', updatePreview);

  // SKU formatting
  skuInput.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
  });

  // Auto-generate SKU on item name change if SKU is empty
  itemNameInput.addEventListener('input', function() {
    if (!skuInput.value.trim()) {
      const name = this.value.trim();
      if (name) {
        const prefix = 'CHK';
        const namePrefix = name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '');
        const randomNum = Math.floor(Math.random() * 999).toString().padStart(3, '0');
        skuInput.value = `${prefix}-${namePrefix || 'ITM'}-${randomNum}`;
      }
    }
    updatePreview();
  });

  // Initialize
  updatePreview();

  // Form validation
  const form = document.querySelector('form');
  form.addEventListener('submit', function(e) {
    const sku = skuInput.value.trim();
    const itemName = itemNameInput.value.trim();
    const categoryId = categorySelect.value;
    const unit = unitSelect.value;
    const currentStock = currentStockInput.value;
    const threshold = thresholdInput.value;
    
    if (!sku || !itemName || !categoryId || !unit || !currentStock || !threshold) {
      e.preventDefault();
      alert('Mohon lengkapi semua field yang wajib diisi');
      return false;
    }
    
    if (parseFloat(currentStock) < 0) {
      e.preventDefault();
      alert('Stok awal tidak boleh negatif');
      currentStockInput.focus();
      return false;
    }
    
    if (parseFloat(threshold) < 0) {
      e.preventDefault();
      alert('Batas minimum stok tidak boleh negatif');
      thresholdInput.focus();
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
  skuInput.addEventListener('focus', function() {
    if (!this.value) {
      generateSKU();
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
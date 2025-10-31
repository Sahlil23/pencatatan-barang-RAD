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
          <a href="{{ route('items.index') }}">Item</a>
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
        <h5 class="mb-0">Edit Item</h5>
        <small class="text-muted float-end">Form edit data item</small>
      </div>
      <div class="card-body">
        <form action="{{ route('items.update', $item->id) }}" method="POST">
          @csrf
          @method('PUT')
          
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
                value="{{ old('sku', $item->sku) }}"
                required
                style="text-transform: uppercase;"
              />
            </div>
            @error('sku')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">SKU harus unik dan maksimal 50 karakter</div>
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

          <!-- Current Stock (Read Only) -->
          <div class="mb-3">
            <label class="form-label">Stok Saat Ini</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-box"></i></span>
              <input
                type="text"
                class="form-control"
                value="{{ number_format($item->current_stock, 2) }} {{ $item->unit }}"
                readonly
              />
              <span class="input-group-text">
                <span class="badge bg-{{ $item->stock_status_color }}">
                  {{ $item->stock_status }}
                </span>
              </span>
            </div>
            <div class="form-text">
              <i class="bx bx-info-circle me-1"></i>
              Stok tidak dapat diubah melalui form edit. Gunakan fitur <strong>Sesuaikan Stok</strong> untuk mengubah stok.
            </div>
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
              <div class="form-text">Sistem akan memberikan peringatan jika stok mencapai batas ini</div>
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
              <button type="button" class="btn btn-outline-warning me-2" onclick="showStockAdjustment()">
                <i class="bx bx-transfer me-1"></i>
                Sesuaikan Stok
              </button>
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
        <div class="alert alert-warning" role="alert">
          <h6 class="alert-heading">
            <i class="bx bx-error-circle me-1"></i>
            Perhatian!
          </h6>
          <ul class="mb-0">
            <li>Perubahan kategori dapat mempengaruhi laporan</li>
            <li>SKU tidak boleh duplikat dengan item lain</li>
            <li>Stok hanya bisa diubah melalui penyesuaian stok</li>
            <li>Item ini memiliki <strong>{{ $item->stockTransactions()->count() }} transaksi</strong> stok</li>
          </ul>
        </div>

        <hr>

        <!-- Item Statistics -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">SKU:</span>
          <span class="badge bg-primary">{{ $item->sku }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Status Stok:</span>
          <span class="badge bg-{{ $item->stock_status_color }}">{{ $item->stock_status }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Total Transaksi:</span>
          <span class="badge bg-info">{{ $item->stockTransactions()->count() }} Transaksi</span>
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
                <span id="preview-sku">{{ $item->sku }}</span> • 
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
              <div class="col-6">
                <small class="text-muted d-block">Supplier:</small>
                <small class="fw-semibold" id="preview-supplier">{{ $item->supplier->supplier_name ?? 'Tidak ada' }}</small>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Stok Saat Ini:</small>
                <small class="fw-semibold text-{{ $item->stock_status_color }}">{{ number_format($item->current_stock, 2) }}</small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Batas Minimum:</small>
                <small class="fw-semibold" id="preview-threshold">{{ number_format($item->low_stock_threshold, 2) }}</small>
              </div>
            </div>
            <div class="text-center">
              <span class="badge bg-{{ $item->stock_status_color }}">{{ $item->stock_status }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    @if($item->stockTransactions()->count() > 0)
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-history me-2"></i>
          Transaksi Terakhir
        </h6>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          @foreach($item->stockTransactions()->latest()->take(5)->get() as $transaction)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }}">
                  <i class="bx {{ $transaction->transaction_type == 'IN' ? 'bx-plus' : ($transaction->transaction_type == 'OUT' ? 'bx-minus' : 'bx-transfer') }}"></i>
                </span>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">{{ $transaction->transaction_type }}</h6>
                <small class="text-muted">{{ Str::limit($transaction->notes, 30) }}</small>
              </div>
              <div class="text-end">
                <small class="fw-bold text-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }}">
                  {{ $transaction->transaction_type == 'OUT' ? '-' : '+' }}{{ number_format($transaction->quantity, 2) }}
                </small>
                <br><small class="text-muted">{{ $transaction->transaction_date->format('d/m/Y') }}</small>
              </div>
            </div>
          </div>
          @endforeach
          
          @if($item->stockTransactions()->count() > 5)
          <div class="list-group-item px-0 py-2 border-0 text-center">
            <small class="text-muted">Dan {{ $item->stockTransactions()->count() - 5 }} transaksi lainnya...</small>
          </div>
          @endif
        </div>
      </div>
    </div>
    @endif
  </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1" aria-labelledby="stockAdjustmentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="stockAdjustmentModalLabel">
          <i class="bx bx-transfer me-2"></i>
          Sesuaikan Stok
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('items.adjust-stock', $item->id) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Item</label>
            <input type="text" class="form-control" value="{{ $item->item_name }}" readonly>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Stok Saat Ini</label>
            <input type="text" class="form-control" value="{{ number_format($item->current_stock, 2) }} {{ $item->unit }}" readonly>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Tipe Penyesuaian <span class="text-danger">*</span></label>
            <select class="form-select" name="adjustment_type" required>
              <option value="">Pilih tipe penyesuaian</option>
              <option value="add">Tambah Stok</option>
              <option value="reduce">Kurangi Stok</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Catatan <span class="text-danger">*</span></label>
            <textarea class="form-control" name="notes" rows="3" placeholder="Alasan penyesuaian stok..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>
            Simpan Penyesuaian
          </button>
        </div>
      </form>
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
  const thresholdInput = document.getElementById('low_stock_threshold');
  
  // Preview elements
  const previewName = document.getElementById('preview-name');
  const previewSku = document.getElementById('preview-sku');
  const previewUnit = document.getElementById('preview-unit');
  const previewCategory = document.getElementById('preview-category');
  const previewSupplier = document.getElementById('preview-supplier');
  const previewThreshold = document.getElementById('preview-threshold');
  const thresholdUnit = document.getElementById('threshold-unit');

  // Update preview
  function updatePreview() {
    const name = itemNameInput.value || "{{ $item->item_name }}";
    const sku = skuInput.value || "{{ $item->sku }}";
    const unit = unitSelect.value || "{{ $item->unit }}";
    const threshold = thresholdInput.value || "{{ $item->low_stock_threshold }}";
    
    const categoryText = categorySelect.options[categorySelect.selectedIndex]?.text || 'Tidak ada';
    const supplierText = supplierSelect.options[supplierSelect.selectedIndex]?.text || 'Tidak ada';
    
    previewName.textContent = name;
    previewSku.textContent = sku;
    previewUnit.textContent = unit;
    previewCategory.textContent = categoryText;
    previewSupplier.textContent = supplierText;
    previewThreshold.textContent = parseFloat(threshold).toLocaleString('id-ID', { minimumFractionDigits: 2 });
    thresholdUnit.textContent = unit;
  }

  // Event listeners
  itemNameInput.addEventListener('input', updatePreview);
  skuInput.addEventListener('input', updatePreview);
  categorySelect.addEventListener('change', updatePreview);
  supplierSelect.addEventListener('change', updatePreview);
  unitSelect.addEventListener('change', updatePreview);
  thresholdInput.addEventListener('input', updatePreview);

  // SKU formatting
  skuInput.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
  });

  // Stock adjustment modal
  window.showStockAdjustment = function() {
    const modal = new bootstrap.Modal(document.getElementById('stockAdjustmentModal'));
    modal.show();
  };

  // Initialize
  updatePreview();

  // Form validation
  const form = document.querySelector('form[action*="update"]');
  form.addEventListener('submit', function(e) {
    const sku = skuInput.value.trim();
    const itemName = itemNameInput.value.trim();
    const categoryId = categorySelect.value;
    const unit = unitSelect.value;
    const threshold = thresholdInput.value;
    
    if (!sku || !itemName || !categoryId || !unit || !threshold) {
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
@extends('layouts.admin')

@section('title', 'Stock Adjustment - ' . $warehouse->warehouse_name)

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
          <a href="{{ route('outlet-warehouse.index') }}">Outlet Warehouse</a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ route('outlet-warehouse.show', $warehouse->id) }}">{{ $warehouse->warehouse_name }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Adjustment</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-1">
            <i class="bx bx-edit-alt me-2"></i>
            Stock Adjustment
          </h5>
          <p class="text-muted mb-0 small">
            <i class="bx bx-store me-1"></i>{{ $warehouse->warehouse_name }} 
            @if($warehouse->branch)
              <span class="mx-2">|</span>
              <i class="bx bx-building me-1"></i>{{ $warehouse->branch->branch_name }}
            @endif
          </p>
        </div>
        <a href="{{ route('outlet-warehouse.show', $warehouse->id) }}" class="btn btn-outline-secondary">
          <i class="bx bx-arrow-back me-1"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('outlet-warehouse.adjustment.store', $warehouse->id) }}" method="POST" id="adjustmentForm">
          @csrf
          
          <!-- Transaction Info -->
          <div class="row mb-4">
            <div class="col-md-4">
              <label class="form-label">Outlet Warehouse <span class="text-danger">*</span></label>
              <input type="text" class="form-control" value="{{ $warehouse->warehouse_name }}" readonly>
              <small class="text-muted">{{ $warehouse->warehouse_code }}</small>
            </div>
            <div class="col-md-4">
              <label class="form-label">Transaction Date <span class="text-danger">*</span></label>
              <input type="date" 
                     class="form-control @error('transaction_date') is-invalid @enderror" 
                     name="transaction_date" 
                     value="{{ old('transaction_date', now()->format('Y-m-d')) }}"
                     max="{{ now()->format('Y-m-d') }}"
                     required>
              @error('transaction_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Reference No</label>
              <input type="text" class="form-control" value="AUTO-GENERATE" readonly>
            </div>
          </div>

          <!-- Adjustment Type & Item -->
          <div class="row mb-4">
            <div class="col-md-6">
              <label class="form-label">Tipe Adjustment <span class="text-danger">*</span></label>
              <select class="form-select @error('adjustment_type') is-invalid @enderror" 
                      name="adjustment_type" 
                      id="adjustment_type" 
                      required
                      onchange="updateAdjustmentInfo()">
                <option value="">-- Pilih Tipe --</option>
                <option value="IN" @selected(old('adjustment_type') === 'IN')>
                  ➕ Tambah Stock (IN)
                </option>
                <option value="OUT" @selected(old('adjustment_type') === 'OUT')>
                  ➖ Kurangi Stock (OUT)
                </option>
              </select>
              @error('adjustment_type')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted" id="adjustmentTypeHelp">Pilih apakah menambah atau mengurangi stock</small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Item <span class="text-danger">*</span></label>
              <select class="form-select @error('item_id') is-invalid @enderror" 
                      name="item_id" 
                      id="item_id" 
                      required>
                <option value="">-- Pilih Item --</option>
                @foreach($stockItems as $stockItem)
                  <option value="{{ $stockItem->item_id }}" 
                          data-name="{{ $stockItem->item->item_name ?? '' }}"
                          data-sku="{{ $stockItem->item->sku ?? '' }}"
                          data-stock="{{ $stockItem->closing_stock }}"
                          data-unit="{{ $stockItem->item->unit ?? 'Unit' }}"
                          @selected(old('item_id') == $stockItem->item_id)>
                    {{ $stockItem->item->sku ?? '' }} - {{ $stockItem->item->item_name ?? '' }} 
                    (Stock: {{ number_format($stockItem->closing_stock, 2) }})
                  </option>
                @endforeach
              </select>
              @error('item_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Item Info Display -->
          <div class="row mb-4" id="itemInfoSection" style="display: none;">
            <div class="col-12">
              <div class="alert d-flex align-items-start" id="itemInfoAlert">
                <i class="bx bx-info-circle me-2 fs-4"></i>
                <div class="flex-grow-1">
                  <strong id="itemInfoTitle">Item yang dipilih:</strong>
                  <div class="mt-2">
                    <div class="row">
                      <div class="col-md-3">
                        <small class="text-muted">SKU:</small><br>
                        <strong id="displaySKU">-</strong>
                      </div>
                      <div class="col-md-3">
                        <small class="text-muted">Nama Item:</small><br>
                        <strong id="displayName">-</strong>
                      </div>
                      <div class="col-md-3">
                        <small class="text-muted">Stock Tersedia:</small><br>
                        <strong id="displayStock" class="text-success">-</strong>
                      </div>
                      <div class="col-md-3">
                        <small class="text-muted">Stock Setelah Adjustment:</small><br>
                        <strong id="displayNewStock" class="text-primary">-</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quantity -->
          <div class="row mb-4">
            <div class="col-md-12">
              <label class="form-label">Quantity <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text" id="quantityIcon">
                  <i class="bx bx-package"></i>
                </span>
                <input type="number" 
                       class="form-control @error('quantity') is-invalid @enderror" 
                       name="quantity" 
                       id="quantity"
                       step="0.001" 
                       min="0.001"
                       placeholder="0.000"
                       value="{{ old('quantity') }}"
                       oninput="calculateNewStock()"
                       required>
                <span class="input-group-text" id="unitLabel">Unit</span>
              </div>
              @error('quantity')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted" id="quantityHelp">Masukkan jumlah adjustment</small>
            </div>
          </div>

          <!-- Reason -->
          <div class="row mb-4">
            <div class="col-12">
              <label class="form-label">Alasan / Catatan <span class="text-danger">*</span></label>
              <textarea class="form-control @error('reason') is-invalid @enderror" 
                        name="reason" 
                        rows="4" 
                        placeholder="Jelaskan alasan adjustment stock secara detail (minimal 10 karakter)..."
                        required>{{ old('reason') }}</textarea>
              @error('reason')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Berikan penjelasan detail tentang adjustment ini</small>
            </div>
          </div>

          <div class="alert d-flex align-items-center" id="warningAlert">
            <i class="bx bx-info-circle me-2 fs-4"></i>
            <div id="warningText">
              <strong>Info:</strong> Pilih tipe adjustment dan item untuk melihat informasi.
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="row mt-4">
            <div class="col-12">
              <div class="d-flex justify-content-between">
                <a href="{{ route('outlet-warehouse.show', $warehouse->id) }}" class="btn btn-outline-secondary">
                  <i class="bx bx-arrow-back me-1"></i>
                  Kembali
                </a>
                <div>
                  <button type="button" class="btn btn-outline-warning me-2" onclick="resetForm()">
                    <i class="bx bx-reset me-1"></i>
                    Reset
                  </button>
                  <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="bx bx-check me-1"></i>
                    Proses Adjustment
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let currentStock = 0;
let adjustmentType = '';

document.addEventListener('DOMContentLoaded', function() {
  const itemSelect = document.getElementById('item_id');
  const quantityInput = document.getElementById('quantity');
  const adjustmentTypeSelect = document.getElementById('adjustment_type');
  const form = document.getElementById('adjustmentForm');
  const submitBtn = document.getElementById('submitBtn');
  const itemInfoSection = document.getElementById('itemInfoSection');

  adjustmentTypeSelect.addEventListener('change', updateAdjustmentInfo);

  itemSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (selectedOption.value) {
      currentStock = parseFloat(selectedOption.getAttribute('data-stock')) || 0;
      const unit = selectedOption.getAttribute('data-unit') || 'Unit';
      const sku = selectedOption.getAttribute('data-sku') || '-';
      const name = selectedOption.getAttribute('data-name') || '-';

      itemInfoSection.style.display = 'block';
      document.getElementById('displaySKU').textContent = sku;
      document.getElementById('displayName').textContent = name;
      document.getElementById('displayStock').textContent = currentStock.toFixed(3);
      document.getElementById('unitLabel').textContent = unit;

      updateAdjustmentInfo();
      calculateNewStock();
    } else {
      itemInfoSection.style.display = 'none';
      quantityInput.max = '';
      quantityInput.placeholder = '0.000';
      document.getElementById('unitLabel').textContent = 'Unit';
    }
  });

  quantityInput.addEventListener('input', calculateNewStock);

  form.addEventListener('submit', function(e) {
    const itemId = itemSelect.value;
    const quantity = parseFloat(quantityInput.value);
    adjustmentType = adjustmentTypeSelect.value;
    const reason = document.querySelector('textarea[name="reason"]').value.trim();

    if (!itemId) {
      e.preventDefault();
      alert('Pilih item terlebih dahulu');
      itemSelect.focus();
      return false;
    }

    if (!adjustmentType) {
      e.preventDefault();
      alert('Pilih tipe adjustment');
      adjustmentTypeSelect.focus();
      return false;
    }

    if (!quantity || quantity <= 0) {
      e.preventDefault();
      alert('Quantity harus lebih dari 0');
      quantityInput.focus();
      return false;
    }

    if (adjustmentType === 'OUT' && quantity > closing_stock) {
      e.preventDefault();
      alert(`Stock tidak mencukupi!\nTersedia: ${closing_stock.toFixed(3)}\nDiminta: ${quantity.toFixed(3)}`);
      quantityInput.focus();
      return false;
    }

    if (!reason || reason.length < 10) {
      e.preventDefault();
      alert('Alasan harus minimal 10 karakter');
      document.querySelector('textarea[name="reason"]').focus();
      return false;
    }

    const itemName = itemSelect.options[itemSelect.selectedIndex].getAttribute('data-name');
    const action = adjustmentType === 'IN' ? 'menambah' : 'mengurangi';
    const newStock = adjustmentType === 'IN' 
      ? (currentStock + quantity).toFixed(3)
      : (currentStock - quantity).toFixed(3);
    
    const confirmMsg = `Yakin akan ${action} stock?\n\n` +
                      `Item: ${itemName}\n` +
                      `Tipe: ${adjustmentTypeSelect.options[adjustmentTypeSelect.selectedIndex].text}\n` +
                      `Quantity: ${quantity.toFixed(3)}\n` +
                      `Stock saat ini: ${currentStock.toFixed(3)}\n` +
                      `Stock setelah adjustment: ${newStock}`;

    if (!confirm(confirmMsg)) {
      e.preventDefault();
      return false;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
  });

  if (typeof $.fn.select2 !== 'undefined') {
    $('#item_id').select2({
      placeholder: '-- Pilih Item --',
      allowClear: true,
      width: '100%'
    });

    $('#adjustment_type').select2({
      placeholder: '-- Pilih Tipe --',
      allowClear: true,
      width: '100%'
    });
  }
});

function updateAdjustmentInfo() {
  adjustmentType = document.getElementById('adjustment_type').value;
  const quantityInput = document.getElementById('quantity');
  const quantityHelp = document.getElementById('quantityHelp');
  const adjustmentTypeHelp = document.getElementById('adjustmentTypeHelp');
  const warningAlert = document.getElementById('warningAlert');
  const warningText = document.getElementById('warningText');
  const itemInfoAlert = document.getElementById('itemInfoAlert');
  const itemInfoTitle = document.getElementById('itemInfoTitle');
  const quantityIcon = document.getElementById('quantityIcon');

  if (!adjustmentType) {
    warningAlert.className = 'alert alert-info d-flex align-items-center';
    warningText.innerHTML = '<strong>Info:</strong> Pilih tipe adjustment dan item untuk melihat informasi.';
    return;
  }

  if (adjustmentType === 'IN') {
    quantityHelp.textContent = '✅ Stock akan BERTAMBAH';
    quantityHelp.className = 'text-success fw-semibold';
    adjustmentTypeHelp.textContent = 'Tambah stock (koreksi, stock opname, dll)';
    adjustmentTypeHelp.className = 'text-success';
    warningAlert.className = 'alert alert-success d-flex align-items-center';
    warningText.innerHTML = '<strong>Tambah Stock:</strong> Stock akan bertambah sesuai quantity yang diinput.';
    itemInfoAlert.className = 'alert alert-success d-flex align-items-start';
    itemInfoTitle.innerHTML = '✅ Stock akan BERTAMBAH:';
    quantityIcon.innerHTML = '<i class="bx bx-plus-circle text-success"></i>';
    quantityInput.removeAttribute('max');
  } else if (adjustmentType === 'OUT') {
    quantityHelp.textContent = '⚠️ Stock akan BERKURANG';
    quantityHelp.className = 'text-warning fw-semibold';
    
    if (currentStock > 0) {
      adjustmentTypeHelp.textContent = `Kurangi stock (max: ${currentStock.toFixed(3)})`;
      quantityInput.max = currentStock;
      quantityInput.placeholder = `Max: ${currentStock.toFixed(3)}`;
    } else {
      adjustmentTypeHelp.textContent = 'Kurangi stock';
    }
    
    adjustmentTypeHelp.className = 'text-warning';
    warningAlert.className = 'alert alert-warning d-flex align-items-center';
    warningText.innerHTML = '<strong>Peringatan:</strong> Stock akan berkurang. Pastikan data sudah benar.';
    itemInfoAlert.className = 'alert alert-warning d-flex align-items-start';
    itemInfoTitle.innerHTML = '⚠️ Stock akan BERKURANG:';
    quantityIcon.innerHTML = '<i class="bx bx-minus-circle text-warning"></i>';
  }

  calculateNewStock();
}

function calculateNewStock() {
  const quantityInput = document.getElementById('quantity');
  const quantity = parseFloat(quantityInput.value) || 0;
  const displayNewStock = document.getElementById('displayNewStock');
  
  if (!adjustmentType || !currentStock) {
    displayNewStock.textContent = '-';
    return;
  }

  let newStock = currentStock;
  
  if (adjustmentType === 'IN') {
    newStock = currentStock + quantity;
    displayNewStock.className = 'text-success fw-bold';
    displayNewStock.textContent = `+${quantity.toFixed(3)} = ${newStock.toFixed(3)}`;
  } else if (adjustmentType === 'OUT') {
    newStock = currentStock - quantity;
    
    if (newStock < 0) {
      displayNewStock.className = 'text-danger fw-bold';
      quantityInput.setCustomValidity('Quantity melebihi stock tersedia');
    } else {
      displayNewStock.className = 'text-warning fw-bold';
      quantityInput.setCustomValidity('');
    }
    
    displayNewStock.textContent = `-${quantity.toFixed(3)} = ${newStock.toFixed(3)}`;
  }
}

function resetForm() {
  if (confirm('Yakin ingin reset form?')) {
    document.getElementById('adjustmentForm').reset();
    document.getElementById('itemInfoSection').style.display = 'none';
    document.getElementById('unitLabel').textContent = 'Unit';
    currentStock = 0;
    adjustmentType = '';
    
    if (typeof $.fn.select2 !== 'undefined') {
      $('#item_id').val(null).trigger('change');
      $('#adjustment_type').val(null).trigger('change');
    }
    
    updateAdjustmentInfo();
  }
}
</script>
@endpush

@push('styles')
<style>
.alert-info { border-left: 4px solid #0dcaf0; }
.alert-success { border-left: 4px solid #28c76f; }
.alert-warning { border-left: 4px solid #ff9f43; }

#itemInfoSection {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.input-group-text {
  min-width: 80px;
  justify-content: center;
  font-weight: 600;
  background-color: #f8f9fa;
}

.select2-container--default .select2-selection--single {
  height: 38px;
  padding: 0.375rem 0.75rem;
  border: 1px solid #d9dee3;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
  line-height: 24px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
  height: 36px;
}

textarea { resize: vertical; }
.btn-primary { min-width: 180px; }
</style>
@endpush
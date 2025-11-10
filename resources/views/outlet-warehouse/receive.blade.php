@extends('layouts.admin')

@section('title', 'Terima Stock dari Branch - ' . $warehouse->warehouse_name)

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
        <li class="breadcrumb-item active" aria-current="page">Terima Stock</li>
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
            <i class="bx bx-package me-2"></i>
            Terima Stock dari Branch Warehouse
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
        <form action="{{ route('outlet-warehouse.receive.store', $warehouse->id) }}" method="POST" id="receiveForm">
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

          <!-- Branch Transaction Reference (Optional) -->
          <div class="row mb-4">
            <div class="col-md-6">
              <label class="form-label">Branch Transaction Reference (Optional)</label>
              <input type="text" 
                     class="form-control @error('branch_warehouse_transaction_id') is-invalid @enderror" 
                     name="branch_warehouse_transaction_id" 
                     placeholder="Masukkan ID transaksi branch jika ada"
                     value="{{ old('branch_warehouse_transaction_id') }}">
              @error('branch_warehouse_transaction_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Opsional - untuk tracking dari branch warehouse</small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Notes</label>
              <input type="text" 
                     class="form-control @error('notes') is-invalid @enderror" 
                     name="notes" 
                     placeholder="Catatan umum untuk semua item"
                     value="{{ old('notes') }}">
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Items Section -->
          <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0">
                <i class="bx bx-list-ul me-2"></i>
                Items yang Diterima
              </h6>
              <button type="button" class="btn btn-primary btn-sm" onclick="addItemRow()">
                <i class="bx bx-plus me-1"></i>
                Tambah Item
              </button>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                  <thead class="table-light">
                    <tr>
                      <th width="30%">Item</th>
                      <th width="10%">Unit</th>
                      <th width="12%">Quantity</th>
                      <th width="12%">Unit Cost</th>
                      <th width="12%">Batch No</th>
                      <th width="18%">Notes</th>
                      <th width="6%">Action</th>
                    </tr>
                  </thead>
                  <tbody id="itemsTableBody">
                    <!-- Default first row -->
                    <tr class="item-row">
                      <td>
                        <select class="form-select item-select" name="items[0][item_id]" required onchange="updateItemInfo(this, 0)">
                          <option value="">Pilih Item</option>
                          @foreach($items as $item)
                          <option value="{{ $item->id }}" 
                                  data-unit="{{ $item->unit ?? 'Unit' }}" 
                                  data-cost="{{ $item->unit_cost ?? 0 }}"
                                  data-sku="{{ $item->sku }}">
                            {{ $item->sku }} - {{ $item->item_name }}
                          </option>
                          @endforeach
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control unit-display" readonly>
                      </td>
                      <td>
                        <input type="number" 
                               class="form-control quantity-input" 
                               name="items[0][quantity]" 
                               step="0.001" 
                               min="0.001" 
                               placeholder="0.000"
                               required 
                               onchange="calculateRowTotal(0)">
                      </td>
                      <td>
                        <input type="number" 
                               class="form-control cost-input" 
                               name="items[0][unit_cost]" 
                               step="0.01" 
                               min="0" 
                               placeholder="0.00"
                               onchange="calculateRowTotal(0)">
                      </td>
                      <td>
                        <input type="text" 
                               class="form-control batch-input" 
                               name="items[0][batch_no]" 
                               placeholder="Batch/Lot"
                               maxlength="50">
                      </td>
                      <td>
                        <input type="text" 
                               class="form-control notes-input" 
                               name="items[0][notes]" 
                               placeholder="Catatan item"
                               maxlength="255">
                      </td>
                      <td>
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeItemRow(this)" disabled>
                          <i class="bx bx-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="table-secondary">
                    <tr>
                      <td colspan="2" class="text-end fw-bold">Total Items:</td>
                      <td>
                        <input type="text" class="form-control fw-bold" id="totalQuantity" value="0.000" readonly>
                      </td>
                      <td colspan="2" class="text-end fw-bold">Grand Total:</td>
                      <td>
                        <input type="text" class="form-control fw-bold" id="grandTotal" readonly>
                      </td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          <div class="alert alert-info d-flex align-items-center mt-3">
            <i class="bx bx-info-circle me-2 fs-4"></i>
            <div>
              <strong>Info:</strong> Pastikan data yang diterima sesuai dengan dokumen pengiriman dari branch warehouse.
              Sistem akan otomatis menambahkan stock ke balance bulan ini.
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
                    Reset Form
                  </button>
                  <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="bx bx-save me-1"></i>
                    Proses Penerimaan
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
let rowIndex = 1;

function addItemRow() {
    const tbody = document.getElementById('itemsTableBody');
    const newRow = document.createElement('tr');
    newRow.className = 'item-row';
    newRow.innerHTML = `
        <td>
            <select class="form-select item-select" name="items[${rowIndex}][item_id]" required onchange="updateItemInfo(this, ${rowIndex})">
                <option value="">Pilih Item</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}" 
                        data-unit="{{ $item->unit ?? 'Unit' }}" 
                        data-cost="{{ $item->unit_cost ?? 0 }}"
                        data-sku="{{ $item->sku }}">
                    {{ $item->sku }} - {{ $item->item_name }}
                </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" class="form-control unit-display" readonly>
        </td>
        <td>
            <input type="number" class="form-control quantity-input" name="items[${rowIndex}][quantity]" 
                   step="0.001" min="0.001" placeholder="0.000" required onchange="calculateRowTotal(${rowIndex})">
        </td>
        <td>
            <input type="number" class="form-control cost-input" name="items[${rowIndex}][unit_cost]" 
                   step="0.01" min="0" placeholder="0.00" onchange="calculateRowTotal(${rowIndex})">
        </td>
        <td>
            <input type="text" class="form-control batch-input" name="items[${rowIndex}][batch_no]" 
                   placeholder="Batch/Lot" maxlength="50">
        </td>
        <td>
            <input type="text" class="form-control notes-input" name="items[${rowIndex}][notes]" 
                   placeholder="Catatan item" maxlength="255">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeItemRow(this)">
                <i class="bx bx-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(newRow);
    rowIndex++;
    updateDeleteButtons();
}

function removeItemRow(button) {
    const row = button.closest('tr');
    const tbody = document.getElementById('itemsTableBody');
    
    if (tbody.children.length > 1) {
        row.remove();
        calculateGrandTotal();
        updateDeleteButtons();
    } else {
        alert('Minimal satu item harus ada');
    }
}

function updateDeleteButtons() {
    const tbody = document.getElementById('itemsTableBody');
    const deleteButtons = tbody.querySelectorAll('.btn-danger');
    
    deleteButtons.forEach(btn => {
        btn.disabled = tbody.children.length <= 1;
    });
}

function updateItemInfo(select, index) {
    const option = select.selectedOptions[0];
    const row = select.closest('tr');
    
    if (option.value) {
        const unit = option.dataset.unit || 'Unit';
        const cost = parseFloat(option.dataset.cost) || 0;
        
        row.querySelector('.unit-display').value = unit;
        row.querySelector('.cost-input').value = cost.toFixed(2);
        
        calculateRowTotal(index);
    } else {
        row.querySelector('.unit-display').value = '';
        row.querySelector('.cost-input').value = '';
        calculateGrandTotal();
    }
}

function calculateRowTotal(index) {
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let totalQuantity = 0;
    let grandTotal = 0;
    const rows = document.querySelectorAll('.item-row');
    
    rows.forEach((row) => {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
        
        totalQuantity += quantity;
        grandTotal += quantity * cost;
    });
    
    document.getElementById('totalQuantity').value = totalQuantity.toFixed(3);
    document.getElementById('grandTotal').value = formatCurrency(grandTotal);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

function resetForm() {
    if (confirm('Yakin ingin reset form?')) {
        document.getElementById('receiveForm').reset();
        
        // Keep only first row
        const tbody = document.getElementById('itemsTableBody');
        while (tbody.children.length > 1) {
            tbody.removeChild(tbody.lastChild);
        }
        
        // Clear first row
        const firstRow = tbody.firstElementChild;
        firstRow.querySelector('.item-select').value = '';
        firstRow.querySelector('.unit-display').value = '';
        firstRow.querySelector('.quantity-input').value = '';
        firstRow.querySelector('.cost-input').value = '';
        firstRow.querySelector('.batch-input').value = '';
        firstRow.querySelector('.notes-input').value = '';
        
        document.getElementById('totalQuantity').value = '0.000';
        document.getElementById('grandTotal').value = '';
        rowIndex = 1;
        updateDeleteButtons();
    }
}

// Form validation
document.getElementById('receiveForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.item-row');
    let hasValidItem = false;
    let itemNames = [];
    
    items.forEach(row => {
        const itemSelect = row.querySelector('.item-select');
        const quantity = row.querySelector('.quantity-input');
        
        if (itemSelect.value && quantity.value && parseFloat(quantity.value) > 0) {
            hasValidItem = true;
            const itemText = itemSelect.options[itemSelect.selectedIndex].text;
            itemNames.push(itemText.substring(0, 30));
        }
    });
    
    if (!hasValidItem) {
        e.preventDefault();
        alert('Minimal satu item dengan quantity valid harus diisi');
        return false;
    }

    // Confirmation
    const confirmMsg = `Yakin akan menerima ${itemNames.length} item?\n\n` +
                      `Items:\n${itemNames.join('\n')}`;
    
    if (!confirm(confirmMsg)) {
        e.preventDefault();
        return false;
    }

    // Disable button
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    calculateGrandTotal();
    updateDeleteButtons();

    // Initialize Select2 if available
    if (typeof $.fn.select2 !== 'undefined') {
        $('.item-select').select2({
            placeholder: 'Pilih Item',
            allowClear: true,
            width: '100%'
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.table th, .table td {
    vertical-align: middle;
}

.item-select {
    width: 100%;
}

.quantity-input, .cost-input {
    text-align: right;
}

.unit-display {
    background-color: #f8f9fa;
    text-align: center;
    font-weight: 500;
}

#totalQuantity, #grandTotal {
    background-color: #e3f2fd;
    font-weight: bold;
    text-align: right;
    border: 2px solid #1976d2;
}

.btn-danger:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.batch-input, .notes-input {
    font-size: 0.875rem;
}

/* Select2 styling */
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

.alert-info {
    border-left: 4px solid #0dcaf0;
}
</style>
@endpush
@extends('layouts.admin')

@section('title', 'Receive Stock - Central Warehouse')

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
          <a href="{{ route('central-warehouse.index') }}">Central Warehouse</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Receive Stock</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-package me-2"></i>
          Receive Stock from Supplier
        </h5>
        <small class="text-muted">Stock Receipt Process</small>
      </div>
      <div class="card-body">
        <form action="{{ route('central-warehouse.store-receipt') }}" method="POST" id="receiptForm">
          @csrf
          
          <!-- Transaction Info -->
          <div class="row mb-4">
            <div class="col-md-3">
              <label class="form-label">Central Warehouse <span class="text-danger">*</span></label>
              <select class="form-select @error('warehouse_id') is-invalid @enderror" name="warehouse_id" required>
                <option value="">Select Warehouse</option>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                  {{ $warehouse->warehouse_name }}
                </option>
                @endforeach
              </select>
              @error('warehouse_id')
                <div class="form-text text-danger">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Supplier <span class="text-danger">*</span></label>
              <select class="form-select @error('supplier_id') is-invalid @enderror" name="supplier_id" required>
                <option value="">Select Supplier</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                  {{ $supplier->supplier_name }}
                </option>
                @endforeach
              </select>
              @error('supplier_id')
                <div class="form-text text-danger">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Transaction Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" 
                     name="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required>
              @error('transaction_date')
                <div class="form-text text-danger">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Reference No</label>
              <input type="text" class="form-control" value="AUTO-GENERATE" readonly>
            </div>
          </div>

          <!-- Notes -->
          <div class="row mb-4">
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea class="form-control @error('notes') is-invalid @enderror" 
                        name="notes" rows="2" placeholder="Receipt notes...">{{ old('notes') }}</textarea>
              @error('notes')
                <div class="form-text text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Items Section -->
          <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0">
                <i class="bx bx-list-ul me-2"></i>
                Items to Receive
              </h6>
              <button type="button" class="btn btn-primary btn-sm" onclick="addItemRow()">
                <i class="bx bx-plus me-1"></i>
                Add Item
              </button>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                  <thead class="table-light">
                    <tr>
                      <th width="30%">Item</th>
                      <th width="15%">Unit</th>
                      <th width="20%">Quantity</th>
                      <th width="20%">Unit Cost</th>
                      <th width="10%">Total</th>
                      <th width="5%">Action</th>
                    </tr>
                  </thead>
                  <tbody id="itemsTableBody">
                    <!-- Default first row -->
                    <tr class="item-row">
                      <td>
                        <select class="form-select item-select" name="items[0][item_id]" required onchange="updateItemInfo(this, 0)">
                          <option value="">Select Item</option>
                          @foreach($items as $item)
                          <option value="{{ $item->id }}" data-unit="{{ $item->unit }}" data-cost="{{ $item->unit_cost ?? 0 }}">
                            {{ $item->item_code }} - {{ $item->item_name }}
                          </option>
                          @endforeach
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control unit-display" readonly>
                      </td>
                      <td>
                        <input type="number" class="form-control quantity-input" name="items[0][quantity]" 
                               step="0.01" min="0" required onchange="calculateRowTotal(0)">
                      </td>
                      <td>
                        <input type="number" class="form-control cost-input" name="items[0][unit_cost]" 
                               step="0.01" min="0" required onchange="calculateRowTotal(0)">
                      </td>
                      <td>
                        <input type="text" class="form-control total-display" readonly>
                      </td>
                      <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeItemRow(this)">
                          <i class="bx bx-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="table-secondary">
                    <tr>
                      <td colspan="4" class="text-end fw-bold">Grand Total:</td>
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

          <!-- Action Buttons -->
          <div class="row mt-4">
            <div class="col-12">
              <div class="d-flex justify-content-between">
                <a href="{{ route('central-warehouse.index') }}" class="btn btn-outline-secondary">
                  <i class="bx bx-arrow-back me-1"></i>
                  Back to Dashboard
                </a>
                <div>
                  <button type="button" class="btn btn-outline-warning me-2" onclick="resetForm()">
                    <i class="bx bx-reset me-1"></i>
                    Reset Form
                  </button>
                  <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-1"></i>
                    Process Receipt
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

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="previewModalLabel">
          <i class="bx bx-show me-2"></i>
          Receipt Preview
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="previewContent">
        <!-- Preview content will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="submitForm()">
          <i class="bx bx-check me-1"></i>
          Confirm Receipt
        </button>
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
                <option value="">Select Item</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}" data-unit="{{ $item->unit }}" data-cost="{{ $item->unit_cost ?? 0 }}">
                    {{ $item->item_code }} - {{ $item->item_name }}
                </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" class="form-control unit-display" readonly>
        </td>
        <td>
            <input type="number" class="form-control quantity-input" name="items[${rowIndex}][quantity]" 
                   step="0.01" min="0" required onchange="calculateRowTotal(${rowIndex})">
        </td>
        <td>
            <input type="number" class="form-control cost-input" name="items[${rowIndex}][unit_cost]" 
                   step="0.01" min="0" required onchange="calculateRowTotal(${rowIndex})">
        </td>
        <td>
            <input type="text" class="form-control total-display" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeItemRow(this)">
                <i class="bx bx-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(newRow);
    rowIndex++;
}

function removeItemRow(button) {
    const row = button.closest('tr');
    const tbody = document.getElementById('itemsTableBody');
    
    if (tbody.children.length > 1) {
        row.remove();
        calculateGrandTotal();
    } else {
        alert('At least one item is required');
    }
}

function updateItemInfo(select, index) {
    const option = select.selectedOptions[0];
    const row = select.closest('tr');
    
    if (option.value) {
        const unit = option.dataset.unit || '';
        const cost = parseFloat(option.dataset.cost) || 0;
        
        row.querySelector('.unit-display').value = unit;
        row.querySelector('.cost-input').value = cost;
        
        calculateRowTotal(index);
    } else {
        row.querySelector('.unit-display').value = '';
        row.querySelector('.cost-input').value = '';
        row.querySelector('.total-display').value = '';
        calculateGrandTotal();
    }
}

function calculateRowTotal(index) {
    const row = document.querySelector(`tr:nth-child(${index + 1})`);
    if (!row) return;
    
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
    const total = quantity * cost;
    
    row.querySelector('.total-display').value = formatCurrency(total);
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    const totalDisplays = document.querySelectorAll('.total-display');
    
    totalDisplays.forEach(display => {
        const value = display.value.replace(/[^\d.-]/g, '');
        grandTotal += parseFloat(value) || 0;
    });
    
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
    if (confirm('Are you sure you want to reset the form?')) {
        document.getElementById('receiptForm').reset();
        
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
        firstRow.querySelector('.total-display').value = '';
        
        document.getElementById('grandTotal').value = '';
        rowIndex = 1;
    }
}

function submitForm() {
    document.getElementById('receiptForm').submit();
}

// Form validation
document.getElementById('receiptForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.item-row');
    let hasValidItem = false;
    
    items.forEach(row => {
        const itemSelect = row.querySelector('.item-select');
        const quantity = row.querySelector('.quantity-input');
        const cost = row.querySelector('.cost-input');
        
        if (itemSelect.value && quantity.value && cost.value) {
            hasValidItem = true;
        }
    });
    
    if (!hasValidItem) {
        e.preventDefault();
        alert('Please add at least one valid item with quantity and cost');
        return false;
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    calculateGrandTotal();
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

.total-display {
    background-color: #f8f9fa;
    font-weight: bold;
    text-align: right;
}

.unit-display {
    background-color: #f8f9fa;
    text-align: center;
}

#grandTotal {
    background-color: #e3f2fd;
    font-weight: bold;
    text-align: right;
    border: 2px solid #1976d2;
}
</style>
@endpush
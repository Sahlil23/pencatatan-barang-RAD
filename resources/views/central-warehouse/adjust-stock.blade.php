@extends('layouts.admin')

@section('title', 'Adjust Stock - Central Warehouse')

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
        <li class="breadcrumb-item active" aria-current="page">Adjust Stock</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-edit-alt me-2"></i>
          Stock Adjustment
        </h5>
      </div>
      <div class="card-body">
        <form action="{{ route('central-warehouse.store-adjustment', $balance->id) }}" method="POST">
          @csrf
          
          <!-- Current Stock Info -->
          <div class="alert alert-info">
            <h6 class="alert-heading">
              <i class="bx bx-info-circle me-2"></i>
              Current Stock Information
            </h6>
            <div class="row">
              <div class="col-md-6">
                <strong>Item:</strong> {{ $balance->item->item_name }}<br>
                <strong>SKU:</strong> {{ $balance->item->sku }}<br>
                <strong>Unit:</strong> {{ $balance->item->unit }}
              </div>
              <div class="col-md-6">
                <strong>Warehouse:</strong> {{ $balance->warehouse->warehouse_name }}<br>
                <strong>Current Stock:</strong> <span class="badge bg-primary">{{ number_format($balance->closing_stock, 2) }}</span><br>
                <strong>Period:</strong> {{ $balance->year }}-{{ str_pad($balance->month, 2, '0', STR_PAD_LEFT) }}
              </div>
            </div>
          </div>

          <!-- Adjustment Type -->
          <div class="mb-3">
            <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
            <div class="row">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="adjustment_type" id="add_stock" value="ADD" checked onchange="updateAdjustmentType()">
                  <label class="form-check-label" for="add_stock">
                    <i class="bx bx-plus-circle text-success me-1"></i>
                    Add Stock
                  </label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="adjustment_type" id="reduce_stock" value="REDUCE" onchange="updateAdjustmentType()">
                  <label class="form-check-label" for="reduce_stock">
                    <i class="bx bx-minus-circle text-danger me-1"></i>
                    Reduce Stock
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Quantity -->
          <div class="mb-3">
            <label class="form-label">Quantity <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bx bx-package"></i>
              </span>
              <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                     name="quantity" id="quantity" step="0.01" min="0" required 
                     value="{{ old('quantity') }}" onchange="calculateNewStock()">
              <span class="input-group-text">{{ $balance->item->unit }}</span>
            </div>
            @error('quantity')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text" id="quantity-help">Enter the quantity to adjust</div>
            @enderror
          </div>

          <!-- Unit Cost (Optional) -->
          <div class="mb-3">
            <label class="form-label">Unit Cost (Optional)</label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" class="form-control @error('unit_cost') is-invalid @enderror" 
                     name="unit_cost" step="0.01" min="0" 
                     value="{{ old('unit_cost', $balance->item->unit_cost ?? 0) }}">
            </div>
            @error('unit_cost')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">For cost calculation purposes</div>
            @enderror
          </div>

          <!-- Reason -->
          <div class="mb-3">
            <label class="form-label">Reason <span class="text-danger">*</span></label>
            <textarea class="form-control @error('reason') is-invalid @enderror" 
                      name="reason" rows="3" required 
                      placeholder="Explain the reason for this adjustment...">{{ old('reason') }}</textarea>
            @error('reason')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Provide detailed reason for audit trail</div>
            @enderror
          </div>

          <!-- Preview Calculation -->
          <div class="card border-primary mb-3">
            <div class="card-header bg-primary text-white">
              <h6 class="mb-0">
                <i class="bx bx-calculator me-2"></i>
                Adjustment Preview
              </h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4 text-center">
                  <div class="border rounded p-3">
                    <h6 class="text-muted">Current Stock</h6>
                    <h4 class="text-info" id="current-stock">{{ number_format($balance->closing_stock, 2) }}</h4>
                    <small class="text-muted">{{ $balance->item->unit }}</small>
                  </div>
                </div>
                <div class="col-md-4 text-center">
                  <div class="border rounded p-3">
                    <h6 class="text-muted">Adjustment</h6>
                    <h4 id="adjustment-amount" class="text-success">+0.00</h4>
                    <small class="text-muted">{{ $balance->item->unit }}</small>
                  </div>
                </div>
                <div class="col-md-4 text-center">
                  <div class="border rounded p-3 bg-light">
                    <h6 class="text-muted">New Stock</h6>
                    <h4 class="text-primary" id="new-stock">{{ number_format($balance->closing_stock, 2) }}</h4>
                    <small class="text-muted">{{ $balance->item->unit }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('central-warehouse.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Back
            </a>
            <div>
              <button type="button" class="btn btn-outline-warning me-2" onclick="resetForm()">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i>
                Process Adjustment
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Info Panel -->
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Item Information
        </h6>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-package"></i>
            </span>
          </div>
          <div>
            <h6 class="mb-0">{{ $balance->item->item_name }}</h6>
            <small class="text-muted">{{ $balance->item->sku }}</small>
          </div>
        </div>

        <hr>

        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Category:</span>
            <span class="badge bg-info">{{ $balance->item->category->category_name ?? 'N/A' }}</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Unit:</span>
            <span class="fw-bold">{{ $balance->item->unit }}</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Low Stock Threshold:</span>
            <span class="text-warning">{{ number_format($balance->item->low_stock_threshold ?? 0, 2) }}</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Current Unit Cost:</span>
            <span class="text-success">Rp {{ number_format($balance->item->unit_cost ?? 0, 0) }}</span>
          </div>
        </div>

        <hr>

        <h6 class="mb-2">Stock Movement (Current Period)</h6>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Opening Stock:</span>
            <span>{{ number_format($balance->opening_stock, 2) }}</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-success">Stock In:</span>
            <span class="text-success">+{{ number_format($balance->stock_in ?? 0, 2) }}</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-danger">Stock Out:</span>
            <span class="text-danger">-{{ number_format($balance->stock_out ?? 0, 2) }}</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-muted fw-bold">Closing Stock:</span>
            <span class="fw-bold">{{ number_format($balance->closing_stock, 2) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Warning Card -->
    <div class="card mt-3">
      <div class="card-header bg-warning text-white">
        <h6 class="mb-0">
          <i class="bx bx-error me-2"></i>
          Important Notes
        </h6>
      </div>
      <div class="card-body">
        <ul class="mb-0 small">
          <li>Stock adjustments are permanent and cannot be undone</li>
          <li>Provide clear reason for audit purposes</li>
          <li>Reduction cannot exceed current stock</li>
          <li>All adjustments are logged with timestamp</li>
          <li>Unit cost update will affect future calculations</li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const currentStock = {{ $balance->closing_stock }};

function updateAdjustmentType() {
    const addRadio = document.getElementById('add_stock');
    const quantityHelp = document.getElementById('quantity-help');
    
    if (addRadio.checked) {
        quantityHelp.textContent = 'Enter the quantity to add to current stock';
        quantityHelp.className = 'form-text text-success';
    } else {
        quantityHelp.textContent = 'Enter the quantity to reduce from current stock (max: ' + currentStock.toLocaleString() + ')';
        quantityHelp.className = 'form-text text-warning';
    }
    
    calculateNewStock();
}

function calculateNewStock() {
    const quantityInput = document.getElementById('quantity');
    const addRadio = document.getElementById('add_stock');
    const adjustmentAmount = document.getElementById('adjustment-amount');
    const newStock = document.getElementById('new-stock');
    
    const quantity = parseFloat(quantityInput.value) || 0;
    let adjustment = 0;
    let newStockValue = currentStock;
    
    if (addRadio.checked) {
        adjustment = quantity;
        newStockValue = currentStock + quantity;
        adjustmentAmount.textContent = '+' + quantity.toLocaleString();
        adjustmentAmount.className = 'text-success';
    } else {
        adjustment = -quantity;
        newStockValue = currentStock - quantity;
        adjustmentAmount.textContent = '-' + quantity.toLocaleString();
        adjustmentAmount.className = 'text-danger';
        
        // Validate reduction
        if (quantity > currentStock) {
            quantityInput.setCustomValidity('Reduction quantity cannot exceed current stock');
            newStock.className = 'text-danger';
        } else {
            quantityInput.setCustomValidity('');
            newStock.className = 'text-primary';
        }
    }
    
    newStock.textContent = newStockValue.toLocaleString();
    
    // Update color based on stock level
    const threshold = {{ $balance->item->low_stock_threshold ?? 0 }};
    if (newStockValue <= 0) {
        newStock.className = 'text-danger';
    } else if (newStockValue <= threshold) {
        newStock.className = 'text-warning';
    } else {
        newStock.className = 'text-primary';
    }
}

function resetForm() {
    if (confirm('Are you sure you want to reset the form?')) {
        document.querySelector('form').reset();
        document.getElementById('add_stock').checked = true;
        updateAdjustmentType();
        calculateNewStock();
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const reduceRadio = document.getElementById('reduce_stock');
    
    if (reduceRadio.checked && quantity > currentStock) {
        e.preventDefault();
        alert('Reduction quantity cannot exceed current stock (' + currentStock.toLocaleString() + ')');
        return false;
    }
    
    if (quantity <= 0) {
        e.preventDefault();
        alert('Quantity must be greater than 0');
        return false;
    }
    
    const reason = document.querySelector('textarea[name="reason"]').value.trim();
    if (reason.length < 10) {
        e.preventDefault();
        alert('Please provide a detailed reason (at least 10 characters)');
        return false;
    }
    
    // Confirmation
    const type = document.getElementById('add_stock').checked ? 'add' : 'reduce';
    const message = `Are you sure you want to ${type} ${quantity.toLocaleString()} units?`;
    
    if (!confirm(message)) {
        e.preventDefault();
        return false;
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateAdjustmentType();
    calculateNewStock();
    
    // Real-time calculation
    document.getElementById('quantity').addEventListener('input', calculateNewStock);
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

.form-check-input:checked {
    background-color: #696cff;
    border-color: #696cff;
}

.border.rounded.p-3 {
    transition: all 0.3s ease;
}

.border.rounded.p-3:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

#new-stock {
    font-size: 1.5rem;
    font-weight: bold;
}

#adjustment-amount {
    font-size: 1.5rem;
    font-weight: bold;
}
</style>
@endpush
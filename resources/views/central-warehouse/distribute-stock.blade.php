@extends('layouts.admin')

@section('title', 'Distribute Stock - Central Warehouse')

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
        <li class="breadcrumb-item active" aria-current="page">Distribute Stock</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-share me-2"></i>
          Distribute Stock to Branch
        </h5>
      </div>
      <div class="card-body">
        <form action="{{ route('central-warehouse.store-distribution', $balance->id) }}" method="POST">
          @csrf
          
          <!-- Current Stock Info -->
          <div class="alert alert-info">
            <h6 class="alert-heading">
              <i class="bx bx-info-circle me-2"></i>
              Stock Information
            </h6>
            <div class="row">
              <div class="col-md-6">
                <strong>Item:</strong> {{ $balance->item->item_name }}<br>
                <strong>SKU:</strong> {{ $balance->item->item_code }}<br>
                <strong>Unit:</strong> {{ $balance->item->unit }}
              </div>
              <div class="col-md-6">
                <strong>Central Warehouse:</strong> {{ $balance->warehouse->warehouse_name }}<br>
                <strong>Available Stock:</strong> <span class="badge bg-success">{{ number_format($balance->closing_stock, 2) }}</span><br>
                <strong>Unit Cost:</strong> <span class="text-success">Rp {{ number_format($balance->item->unit_cost ?? 0, 0) }}</span>
              </div>
            </div>
          </div>

          <!-- Branch Warehouse Selection -->
        <div class="mb-3">
        <label class="form-label">Destination Branch Warehouse <span class="text-danger">*</span></label>
        <select class="form-select @error('branch_warehouse_id') is-invalid @enderror" 
                name="branch_warehouse_id" required onchange="updateBranchInfo()">
            <option value="">Select Branch Warehouse</option>
            @if(isset($branchWarehouses) && $branchWarehouses->count() > 0)
            @foreach($branchWarehouses as $warehouse)
            <option value="{{ $warehouse->id }}" 
                    data-branch="{{ $warehouse->branch ? $warehouse->branch->branch_name : 'N/A' }}"
                    data-address="{{ $warehouse->address }}"
                    data-warehouse-name="{{ $warehouse->warehouse_name }}"
                    {{ old('branch_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                {{ $warehouse->warehouse_name }} 
                @if($warehouse->branch)
                - {{ $warehouse->branch->branch_name }} ({{ $warehouse->branch->city }})
                @endif
            </option>
            @endforeach
            @else
            <option value="" disabled>No branch warehouses available</option>
            @endif
        </select>
        @error('branch_warehouse_id')
            <div class="form-text text-danger">{{ $message }}</div>
        @else
            <div class="form-text">
            @if(isset($branchWarehouses) && $branchWarehouses->count() > 0)
                {{ $branchWarehouses->count() }} branch warehouses available
            @else
                No branch warehouses found. Please create branch warehouses first.
            @endif
            </div>
        @enderror
        </div>

          <!-- Branch Info Display -->
          <div class="card border-secondary mb-3" id="branchInfoCard" style="display: none;">
            <div class="card-header bg-secondary text-white">
              <h6 class="mb-0">
                <i class="bx bx-buildings me-2"></i>
                Destination Information
              </h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <strong>Branch:</strong> <span id="branch-name">-</span><br>
                  <strong>Warehouse:</strong> <span id="warehouse-name">-</span>
                </div>
                <div class="col-md-6">
                  <strong>Address:</strong> <span id="warehouse-address">-</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Distribution Quantity -->
          <div class="mb-3">
            <label class="form-label">Distribution Quantity <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bx bx-package"></i>
              </span>
              <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                     name="quantity" id="quantity" step="0.01" min="0.01" 
                     max="{{ $balance->closing_stock }}" required 
                     value="{{ old('quantity') }}" onchange="calculateDistribution()">
              <span class="input-group-text">{{ $balance->item->unit }}</span>
              <button type="button" class="btn btn-outline-secondary" onclick="setMaxQuantity()">
                Max
              </button>
            </div>
            @error('quantity')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Maximum available: {{ number_format($balance->closing_stock, 2) }} {{ $balance->item->unit }}</div>
            @enderror
          </div>

          <!-- Notes -->
          <div class="mb-3">
            <label class="form-label">Distribution Notes</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" 
                      name="notes" rows="3" 
                      placeholder="Add notes for this distribution...">{{ old('notes') }}</textarea>
            @error('notes')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Optional notes for tracking purposes</div>
            @enderror
          </div>

          <!-- Distribution Summary -->
          <div class="card border-primary mb-3">
            <div class="card-header bg-primary text-white">
              <h6 class="mb-0">
                <i class="bx bx-calculator me-2"></i>
                Distribution Summary
              </h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3 text-center">
                  <div class="border rounded p-3">
                    <h6 class="text-muted">Available Stock</h6>
                    <h4 class="text-success" id="available-stock">{{ number_format($balance->closing_stock, 2) }}</h4>
                    <small class="text-muted">{{ $balance->item->unit }}</small>
                  </div>
                </div>
                <div class="col-md-3 text-center">
                  <div class="border rounded p-3">
                    <h6 class="text-muted">Distribution Qty</h6>
                    <h4 class="text-primary" id="distribution-qty">0.00</h4>
                    <small class="text-muted">{{ $balance->item->unit }}</small>
                  </div>
                </div>
                <div class="col-md-3 text-center">
                  <div class="border rounded p-3 bg-light">
                    <h6 class="text-muted">Remaining Stock</h6>
                    <h4 class="text-info" id="remaining-stock">{{ number_format($balance->closing_stock, 2) }}</h4>
                    <small class="text-muted">{{ $balance->item->unit }}</small>
                  </div>
                </div>
                <div class="col-md-3 text-center">
                  <div class="border rounded p-3">
                    <h6 class="text-muted">Total Value</h6>
                    <h4 class="text-warning" id="total-value">Rp 0</h4>
                    <small class="text-muted">IDR</small>
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
              <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="bx bx-share me-1"></i>
                Process Distribution
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Info Panel -->
  <div class="col-md-4">
    <!-- Item Info -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Item Details
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
            <small class="text-muted">{{ $balance->item->item_code }}</small>
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
            <span class="text-muted">Unit Cost:</span>
            <span class="text-success">Rp {{ number_format($balance->item->unit_cost ?? 0, 0) }}</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Total Value:</span>
            <span class="text-primary">Rp {{ number_format(($balance->item->unit_cost ?? 0) * $balance->closing_stock, 0) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Central Warehouse Info -->
    <div class="card mt-3">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-buildings me-2"></i>
          Source Warehouse
        </h6>
      </div>
      <div class="card-body">
        <h6>{{ $balance->warehouse->warehouse_name }}</h6>
        <p class="text-muted mb-2">{{ $balance->warehouse->address ?? 'No address available' }}</p>
        
        <hr>
        
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Type:</span>
            <span class="badge bg-primary">{{ strtoupper($balance->warehouse->warehouse_type) }}</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Capacity:</span>
            <span>{{ $balance->warehouse->capacity_m2 ?? 'N/A' }} m²</span>
          </div>
        </div>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Status:</span>
            <span class="badge bg-success">{{ strtoupper($balance->warehouse->status) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Distribution Guidelines -->
    <div class="card mt-3">
      <div class="card-header bg-warning text-white">
        <h6 class="mb-0">
          <i class="bx bx-error me-2"></i>
          Distribution Guidelines
        </h6>
      </div>
      <div class="card-body">
        <ul class="mb-0 small">
          <li>Distribution will create pending transaction</li>
          <li>Branch must confirm receipt to complete</li>
          <li>Stock will be deducted immediately from central</li>
          <li>Reference number will be auto-generated</li>
          <li>Track distribution status in transaction history</li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const availableStock = {{ $balance->closing_stock }};
const unitCost = {{ $balance->item->unit_cost ?? 0 }};

function updateBranchInfo() {
    const select = document.querySelector('select[name="branch_warehouse_id"]');
    const option = select.selectedOptions[0];
    const infoCard = document.getElementById('branchInfoCard');
    
    if (option.value) {
        const branchName = option.dataset.branch || 'N/A';
        const warehouseName = option.dataset.warehouseName || option.text.split(' - ')[0] || 'N/A';
        const address = option.dataset.address || 'No address available';
        
        document.getElementById('branch-name').textContent = branchName;
        document.getElementById('warehouse-name').textContent = warehouseName;
        document.getElementById('warehouse-address').textContent = address;
        
        infoCard.style.display = 'block';
    } else {
        infoCard.style.display = 'none';
    }
}

function calculateDistribution() {
    const quantityInput = document.getElementById('quantity');
    const distributionQty = document.getElementById('distribution-qty');
    const remainingStock = document.getElementById('remaining-stock');
    const totalValue = document.getElementById('total-value');
    const submitBtn = document.getElementById('submitBtn');
    
    const quantity = parseFloat(quantityInput.value) || 0;
    const remaining = availableStock - quantity;
    const value = quantity * unitCost;
    
    distributionQty.textContent = quantity.toLocaleString();
    remainingStock.textContent = remaining.toLocaleString();
    totalValue.textContent = 'Rp ' + value.toLocaleString();
    
    // Update colors based on values
    if (quantity > availableStock) {
        distributionQty.className = 'text-danger';
        remainingStock.className = 'text-danger';
        quantityInput.setCustomValidity('Distribution quantity cannot exceed available stock');
        submitBtn.disabled = true;
    } else if (quantity <= 0) {
        distributionQty.className = 'text-muted';
        remainingStock.className = 'text-info';
        quantityInput.setCustomValidity('Quantity must be greater than 0');
        submitBtn.disabled = true;
    } else {
        distributionQty.className = 'text-primary';
        remainingStock.className = 'text-info';
        quantityInput.setCustomValidity('');
        submitBtn.disabled = false;
        
        // Color remaining stock based on level
        const threshold = {{ $balance->item->low_stock_threshold ?? 0 }};
        if (remaining <= 0) {
            remainingStock.className = 'text-danger';
        } else if (remaining <= threshold) {
            remainingStock.className = 'text-warning';
        } else {
            remainingStock.className = 'text-info';
        }
    }
}

function setMaxQuantity() {
    document.getElementById('quantity').value = availableStock;
    calculateDistribution();
}

function resetForm() {
    if (confirm('Are you sure you want to reset the form?')) {
        document.querySelector('form').reset();
        document.getElementById('branchInfoCard').style.display = 'none';
        calculateDistribution();
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const branchWarehouse = document.querySelector('select[name="branch_warehouse_id"]').value;
    
    if (!branchWarehouse) {
        e.preventDefault();
        alert('Please select a destination branch warehouse');
        return false;
    }
    
    if (quantity <= 0) {
        e.preventDefault();
        alert('Distribution quantity must be greater than 0');
        return false;
    }
    
    if (quantity > availableStock) {
        e.preventDefault();
        alert('Distribution quantity cannot exceed available stock (' + availableStock.toLocaleString() + ')');
        return false;
    }
    
    // Confirmation
    const warehouseName = document.querySelector('select[name="branch_warehouse_id"] option:checked').text;
    const message = `Are you sure you want to distribute ${quantity.toLocaleString()} units to ${warehouseName}?`;
    
    if (!confirm(message)) {
        e.preventDefault();
        return false;
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    calculateDistribution();
    updateBranchInfo();
    
    // Real-time calculation
    document.getElementById('quantity').addEventListener('input', calculateDistribution);
    document.querySelector('select[name="branch_warehouse_id"]').addEventListener('change', updateBranchInfo);
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

.border.rounded.p-3 {
    transition: all 0.3s ease;
    min-height: 100px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.border.rounded.p-3:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.border.rounded.p-3 h4 {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0.5rem 0;
}

.border.rounded.p-3 h6 {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.bg-light {
    background-color: #f8f9fa !important;
}

#submitBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
@endpush
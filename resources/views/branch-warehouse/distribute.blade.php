@extends('layouts.admin')

@section('title', 'Distribusi Stock ke Outlet - ' . $warehouse->warehouse_name)

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
          <a href="{{ route('branch-warehouse.index') }}">Branch Warehouse</a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ route('branch-warehouse.show', $warehouse->id) }}">{{ $warehouse->warehouse_name }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Distribusi ke Outlet</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Warehouse Info Card -->
<div class="card mb-4">
  <div class="card-body">
    <div class="row align-items-center">
      <div class="col-md-8">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-store bx-lg"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-1">{{ $warehouse->warehouse_name }}</h5>
            <div class="text-muted small">
              <span class="badge bg-label-secondary me-2">{{ $warehouse->warehouse_code }}</span>
              Branch: <strong>{{ $warehouse->branch->branch_name ?? '-' }}</strong>
              @if($warehouse->address)
                • {{ $warehouse->address }}
              @endif
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('branch-warehouse.show', $warehouse->id) }}" class="btn btn-outline-secondary">
          <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Distribution Form -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bx bx-export me-2"></i>
      Form Distribusi Stock ke Outlet Warehouse
    </h5>
    <span class="badge bg-info">{{ date('d M Y') }}</span>
  </div>

  <div class="card-body">
    <!-- Info Alert -->
    <div class="alert alert-info alert-dismissible" role="alert">
      <h6 class="alert-heading mb-2">
        <i class="bx bx-info-circle me-2"></i>
        Informasi Distribusi
      </h6>
      <p class="mb-0">
        <strong>Pastikan:</strong> Stock yang akan didistribusikan tersedia dan cukup •
        <strong>Pilih outlet:</strong> Outlet warehouse tujuan distribusi •
        <strong>Input quantity:</strong> Sesuai kebutuhan outlet
      </p>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <strong>Error!</strong> {{ session('error') }}
      </div>
    @endif

    <form action="{{ route('branch-warehouse.distribute', $warehouse->id) }}" method="POST" id="distributionForm">
      @csrf

      <!-- Outlet Selection -->
      <div class="row mb-4">
        <div class="col-md-6">
          <label class="form-label required">
            <i class="bx bx-store-alt me-1"></i>
            Outlet Warehouse Tujuan
          </label>
          <select class="form-select @error('outlet_id') is-invalid @enderror" 
                  name="outlet_id" 
                  id="outletSelect" 
                  required>
            <option value="">-- Pilih Outlet Warehouse --</option>
            @forelse($outlets as $outlet)
              <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                {{ $outlet->warehouse_name }} - {{ $outlet->warehouse_code }}
                @if($outlet->address)
                  ({{ Str::limit($outlet->address, 30) }})
                @endif
              </option>
            @empty
              <option value="" disabled>Tidak ada outlet warehouse tersedia</option>
            @endforelse
          </select>
          @error('outlet_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">
            <i class="bx bx-note me-1"></i>
            Catatan (Opsional)
          </label>
          <textarea class="form-control @error('notes') is-invalid @enderror" 
                    name="notes" 
                    rows="3" 
                    placeholder="Catatan tambahan untuk distribusi ini...">{{ old('notes') }}</textarea>
          @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <!-- Items Selection Table -->
      <div class="table-responsive border rounded">
        <table class="table table-hover mb-0" id="itemsTable">
          <thead class="table-light">
            <tr>
              <th width="5%">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="selectAll">
                </div>
              </th>
              <th width="25%">Item</th>
              <th width="15%" class="text-center">Stock Tersedia</th>
              <th width="20%">Quantity Distribusi</th>
              <th width="15%">Unit</th>
              <th width="20%">Catatan Item</th>
            </tr>
          </thead>
          <tbody>
            @forelse($stockItems as $index => $stock)
              <tr class="item-row" data-item-id="{{ $stock->item_id }}">
                <td>
                  <div class="form-check">
                    <input class="form-check-input item-checkbox" 
                           type="checkbox" 
                           name="items[{{ $index }}][selected]" 
                           value="1"
                           data-index="{{ $index }}">
                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                      <span class="avatar-initial rounded bg-label-primary">
                        <i class="bx bx-package"></i>
                      </span>
                    </div>
                    <div>
                      <strong>{{ $stock->item->item_name ?? '-' }}</strong>
                      <div class="text-muted small">
                        {{ $stock->item->sku ?? '-' }}
                        @if($stock->item->category)
                          • {{ $stock->item->category->category_name }}
                        @endif
                      </div>
                    </div>
                  </div>
                  <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $stock->item_id }}">
                </td>
                <td class="text-center">
                  <span class="badge bg-label-success stock-available" data-stock="{{ $stock->closing_stock }}">
                    {{ number_format($stock->closing_stock, 2) }}
                  </span>
                </td>
                <td>
                  <input type="number" 
                         class="form-control quantity-input @error("items.{$index}.quantity") is-invalid @enderror" 
                         name="items[{{ $index }}][quantity]" 
                         step="0.001" 
                         min="0" 
                         max="{{ $stock->closing_stock }}"
                         placeholder="0.000"
                         data-max="{{ $stock->closing_stock }}"
                         data-index="{{ $index }}"
                         disabled>
                  @error("items.{$index}.quantity")
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </td>
                <td>
                  <span class="badge bg-label-secondary">{{ $stock->item->unit ?? '-' }}</span>
                </td>
                <td>
                  <input type="text" 
                         class="form-control form-control-sm" 
                         name="items[{{ $index }}][item_notes]" 
                         placeholder="Catatan..."
                         disabled>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                  <i class="bx bx-info-circle bx-lg mb-2"></i>
                  <p class="mb-0">Tidak ada stock yang tersedia untuk didistribusikan</p>
                </td>
              </tr>
            @endforelse
          </tbody>
          <tfoot class="table-light">
            <tr>
              <td colspan="2" class="text-end"><strong>Total Item Dipilih:</strong></td>
              <td class="text-center" id="totalItemsSelected">0</td>
              <td colspan="3"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Submit Buttons -->
      <div class="row mt-4">
        <div class="col-12">
          <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="bx bx-export me-1"></i>
            Distribusikan Stock
          </button>
          <button type="reset" class="btn btn-outline-secondary" id="resetBtn">
            <i class="bx bx-reset me-1"></i>
            Reset Form
          </button>
          <a href="{{ route('branch-warehouse.show', $warehouse->id) }}" class="btn btn-outline-danger">
            <i class="bx bx-x me-1"></i>
            Batal
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Stock Summary Card -->
<div class="card mt-4">
  <div class="card-header">
    <h6 class="mb-0">
      <i class="bx bx-bar-chart me-2"></i>
      Ringkasan Stock Tersedia
    </h6>
  </div>
  <div class="card-body">
    <div class="row text-center">
      <div class="col-md-3 col-6 mb-3">
        <div class="border rounded p-3">
          <div class="text-muted small mb-1">Total Items</div>
          <h4 class="mb-0">{{ $stockItems->count() }}</h4>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="border rounded p-3">
          <div class="text-muted small mb-1">Total Stock</div>
          <h4 class="mb-0">{{ number_format($stockItems->sum('closing_stock'), 3) }}</h4>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="border rounded p-3">
          <div class="text-muted small mb-1">Outlet Tersedia</div>
          <h4 class="mb-0">{{ $outlets->count() }}</h4>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="border rounded p-3">
          <div class="text-muted small mb-1">Tanggal</div>
          <h4 class="mb-0">{{ date('d M') }}</h4>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
  .required::after {
    content: ' *';
    color: red;
  }
  
  .item-row {
    transition: background-color 0.2s;
  }
  
  .item-row:hover {
    background-color: rgba(105, 108, 255, 0.04);
  }
  
  .item-row.selected {
    background-color: rgba(105, 108, 255, 0.08);
  }
  
  .quantity-input:disabled {
    background-color: #f5f5f9;
    cursor: not-allowed;
  }
  
  .quantity-input.is-invalid {
    border-color: #dc3545;
  }
  
  .avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    font-size: 18px;
  }
  
  .table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
  }
  
  @media (max-width: 768px) {
    .table-responsive {
      font-size: 0.875rem;
    }
    
    .avatar-initial {
      width: 32px;
      height: 32px;
      font-size: 14px;
    }
  }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn = document.getElementById('resetBtn');
    const outletSelect = document.getElementById('outletSelect');
    const totalItemsSelectedEl = document.getElementById('totalItemsSelected');

    // Select All functionality
    selectAllCheckbox?.addEventListener('change', function() {
        const isChecked = this.checked;
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            toggleRowInputs(checkbox);
        });
        updateSubmitButton();
        updateTotalSelected();
    });

    // Individual checkbox change
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            toggleRowInputs(this);
            updateSelectAllCheckbox();
            updateSubmitButton();
            updateTotalSelected();
        });
    });

    // Quantity input validation
    quantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            const max = parseFloat(this.getAttribute('data-max'));
            const value = parseFloat(this.value) || 0;
            
            // Remove previous error
            this.classList.remove('is-invalid');
            const existingError = this.parentElement.querySelector('.invalid-feedback');
            if (existingError) {
                existingError.remove();
            }

            // Validate
            if (value > max) {
                this.classList.add('is-invalid');
                const error = document.createElement('div');
                error.className = 'invalid-feedback d-block';
                error.textContent = `Stock tidak cukup! Maksimal: ${max.toFixed(3)}`;
                this.parentElement.appendChild(error);
            } else if (value < 0) {
                this.value = 0;
            }

            updateSubmitButton();
        });

        // Format on blur
        input.addEventListener('blur', function() {
            if (this.value) {
                const value = parseFloat(this.value) || 0;
                this.value = value.toFixed(3);
            }
        });
    });

    // Outlet selection required
    outletSelect?.addEventListener('change', function() {
        updateSubmitButton();
    });

    // Reset form
    resetBtn?.addEventListener('click', function() {
        selectAllCheckbox.checked = false;
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
            toggleRowInputs(checkbox);
        });
        updateSubmitButton();
        updateTotalSelected();
    });

    // ✅ FIX: Form submission validation + Remove unselected items
    document.getElementById('distributionForm')?.addEventListener('submit', function(e) {
        // Check if outlet selected
        if (!outletSelect?.value) {
            e.preventDefault();
            alert('Silakan pilih outlet warehouse tujuan!');
            outletSelect?.focus();
            return false;
        }

        // Check if at least one item selected with quantity
        let hasValidItem = false;
        itemCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const index = checkbox.getAttribute('data-index');
                const quantityInput = document.querySelector(`input[name="items[${index}][quantity]"]`);
                const quantity = parseFloat(quantityInput?.value) || 0;
                if (quantity > 0) {
                    hasValidItem = true;
                }
            }
        });

        if (!hasValidItem) {
            e.preventDefault();
            alert('Silakan pilih minimal 1 item dan masukkan quantity yang valid!');
            return false;
        }

        // ✅ NEW: Remove inputs for unselected items BEFORE submit
        itemCheckboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                const index = checkbox.getAttribute('data-index');
                const row = checkbox.closest('.item-row');
                
                // Remove all inputs in this row from form submission
                const inputs = row.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    // Option 1: Remove name attribute (recommended)
                    input.removeAttribute('name');
                    
                    // Option 2: Alternative - remove from DOM
                    // input.remove();
                });
            }
        });

        // Confirm before submit
        if (!confirm('Apakah Anda yakin ingin mendistribusikan stock ini ke outlet?')) {
            e.preventDefault();
            return false;
        }

        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    });

    // Helper Functions
    function toggleRowInputs(checkbox) {
        const row = checkbox.closest('.item-row');
        const index = checkbox.getAttribute('data-index');
        const quantityInput = document.querySelector(`input[name="items[${index}][quantity]"]`);
        const noteInput = document.querySelector(`input[name="items[${index}][item_notes]"]`);

        if (checkbox.checked) {
            row.classList.add('selected');
            quantityInput.disabled = false;
            noteInput.disabled = false;
            quantityInput.focus();
        } else {
            row.classList.remove('selected');
            quantityInput.disabled = true;
            quantityInput.value = '';
            noteInput.disabled = true;
            noteInput.value = '';
            // Remove validation error
            quantityInput.classList.remove('is-invalid');
            const error = quantityInput.parentElement.querySelector('.invalid-feedback');
            if (error) error.remove();
        }
    }

    function updateSelectAllCheckbox() {
        const totalCheckboxes = itemCheckboxes.length;
        const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked').length;
        selectAllCheckbox.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
    }

    function updateSubmitButton() {
        const outletSelected = outletSelect?.value;
        let hasValidSelection = false;

        itemCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const index = checkbox.getAttribute('data-index');
                const quantityInput = document.querySelector(`input[name="items[${index}][quantity]"]`);
                const quantity = parseFloat(quantityInput?.value) || 0;
                const hasError = quantityInput?.classList.contains('is-invalid');
                
                if (quantity > 0 && !hasError) {
                    hasValidSelection = true;
                }
            }
        });

        submitBtn.disabled = !(outletSelected && hasValidSelection);
    }

    function updateTotalSelected() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        totalItemsSelectedEl.textContent = checkedCount;
    }

    // Auto-hide alerts
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            try {
                new bootstrap.Alert(alert).close();
            } catch(e) {}
        });
    }, 5000);
});
</script>
@endpush
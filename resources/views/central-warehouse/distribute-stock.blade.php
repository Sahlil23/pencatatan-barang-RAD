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

<form action="{{ $selectedItem ? route('central-warehouse.store-distribution-item', $selectedItem->id) : route('central-warehouse.store-distribution') }}" 
      method="POST" 
      id="distributionForm">
  @csrf
  
  <div class="row">
    <div class="col-lg-8">
      <!-- Warehouse Selection Card -->
      <div class="card mb-3">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bx bx-buildings me-2"></i>Warehouse Selection</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Source Central Warehouse -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Source Central Warehouse <span class="text-danger">*</span></label>
                {{-- Cek jumlah data --}}
                @if($centralWarehouses->count() > 1)
                  
                  {{-- JIKA DATA > 1: Tampilkan Dropdown (Select) --}}
                  <select class="form-select @error('source_warehouse_id') is-invalid @enderror" 
                          name="source_warehouse_id" 
                          id="sourceWarehouse" 
                          required 
                          onchange="loadAvailableStock()">
                    <option value="">Select Central Warehouse</option>
                    @foreach($centralWarehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" 
                            {{ (old('source_warehouse_id') ?? $selectedWarehouseId) == $warehouse->id ? 'selected' : '' }}>
                      {{ $warehouse->warehouse_name }}
                    </option>
                    @endforeach
                  </select>

                @else
                  
                  {{-- JIKA DATA HANYA 1: Tampilkan Input Readonly & Input Hidden --}}
                  @php 
                    $singleWarehouse = $centralWarehouses->first(); 
                  @endphp
                  
                  {{-- Tampilan Visual (Hanya untuk dibaca user) --}}
                  <input type="text" class="form-control bg-light" value="{{ $singleWarehouse->warehouse_name }}" readonly>
                  
                  {{-- Data Sebenarnya (Hidden Input untuk dikirim ke Controller & dibaca JS) --}}
                  <input type="hidden" 
                        name="source_warehouse_id" 
                        id="sourceWarehouse" 
                        value="{{ $singleWarehouse->id }}">
                        
                @endif

                @error('source_warehouse_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Destination Branch Warehouse -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Destination Branch Warehouse <span class="text-danger">*</span></label>
              <select class="form-select @error('destination_warehouse_id') is-invalid @enderror" 
                      name="destination_warehouse_id" 
                      required 
                      onchange="updateDestinationInfo()">
                <option value="">Select Branch Warehouse</option>
                @foreach($branchWarehouses as $warehouse)
                <option value="{{ $warehouse->id }}"
                        data-branch="{{ $warehouse->branch ? $warehouse->branch->branch_name : 'N/A' }}"
                        data-address="{{ $warehouse->address }}"
                        {{ old('destination_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                  {{ $warehouse->warehouse_name }}
                  @if($warehouse->branch)
                  - {{ $warehouse->branch->branch_name }}
                  @endif
                </option>
                @endforeach
              </select>
              @error('destination_warehouse_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Destination Info Display -->
          <div class="alert alert-info d-none" id="destinationInfo">
            <strong>Destination:</strong> <span id="destWarehouseName"></span><br>
            <strong>Branch:</strong> <span id="destBranchName"></span><br>
            <strong>Address:</strong> <span id="destAddress"></span>
          </div>
        </div>
      </div>

      <!-- Items Selection Card -->
      <div class="card mb-3">
        <div class="card-header bg-success text-white">
          <div class="d-flex justify-content-between align-items-center">
            <label class="form-label">Cari Item</label>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama item, SKU, atau kategori...">
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0" id="itemsTable">
              <thead class="table-light">
                <tr>
                  <th width="50"><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()"></th>
                  <th>Item Name</th>
                  <th>SKU</th>
                  <th>Category</th>
                  <th>Unit</th>
                  <th width="120">Available Stock</th>
                  <th width="150">Quantity</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody id="itemsTableBody">
                @if(isset($selectedItem))
                {{-- Pre-selected item --}}
                <tr data-item-id="{{ $selectedItem->item_id }}">
                  <td>
                    <input type="checkbox" class="item-checkbox" name="items[0][selected]" value="1" checked>
                    <input type="hidden" name="items[0][item_id]" value="{{ $selectedItem->item_id }}">
                  </td>
                  <td>{{ $selectedItem->item->item_name }}</td>
                  <td><code>{{ $selectedItem->item->sku }}</code></td>
                  <td><span class="badge bg-info">{{ $selectedItem->item->category->category_name ?? 'N/A' }}</span></td>
                  <td>{{ $selectedItem->item->unit }}</td>
                  <td>
                    <strong class="text-success" data-available-stock="{{ $selectedItem->closing_stock }}">
                      {{ number_format($selectedItem->closing_stock, 2) }}
                    </strong>
                  </td>
                  <td>
                    <input type="number" 
                           class="form-control form-control-sm quantity-input" 
                           name="items[0][quantity]" 
                           step="0.01" 
                           min="0.01" 
                           max="{{ $selectedItem->closing_stock }}"
                           placeholder="0.00"
                           onchange="calculateTotal()">
                  </td>
                  <td>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="items[0][notes]" 
                           placeholder="Optional notes">
                  </td>
                </tr>
                @else
                {{-- Empty state - will be populated by AJAX --}}
                <tr id="emptyState">
                  <td colspan="8" class="text-center text-muted py-4">
                    <i class="bx bx-info-circle me-2"></i>Select a source warehouse to load available items
                  </td>
                </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer">
          <div class="row">
            <div class="col-md-6">
              <small class="text-muted">
                <i class="bx bx-info-circle me-1"></i>
                Selected: <strong id="selectedCount">0</strong> items
              </small>
            </div>
            <div class="col-md-6 text-end">
              <small class="text-muted">
                Total Quantity: <strong id="totalQuantity">0.00</strong>
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- General Notes Card -->
      <div class="card mb-3">
        <div class="card-body">
          <label class="form-label">General Distribution Notes</label>
          <textarea class="form-control" 
                    name="general_notes" 
                    rows="3" 
                    placeholder="Add general notes for this distribution...">{{ old('general_notes') }}</textarea>
          <small class="form-text text-muted">This note will be applied to all selected items</small>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('central-warehouse.index') }}" class="btn btn-outline-secondary">
          <i class="bx bx-arrow-back me-1"></i>Back
        </a>
        <div>
          <button type="button" class="btn btn-outline-warning me-2" onclick="resetForm()">
            <i class="bx bx-reset me-1"></i>Reset
          </button>
          <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="bx bx-share me-1"></i>Process Distribution
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Sidebar -->
    <div class="col-lg-4">
      <!-- Distribution Summary -->
      <div class="card mb-3 border-primary">
        <div class="card-header bg-primary text-white">
          <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Distribution Summary</h6>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <small class="text-muted">Selected Items</small>
            <h3 class="mb-0" id="summarySelectedCount">0</h3>
          </div>
          <div class="mb-3">
            <small class="text-muted">Total Quantity</small>
            <h3 class="mb-0" id="summaryTotalQty">0.00</h3>
          </div>
          <div class="mb-0">
            <small class="text-muted">Estimated Value</small>
            <h3 class="mb-0 text-success" id="summaryTotalValue">Rp 0</h3>
          </div>
        </div>
      </div>

      <!-- Guidelines
      <div class="card border-warning">
        <div class="card-header bg-warning text-white">
          <h6 class="mb-0"><i class="bx bx-error me-2"></i>Distribution Guidelines</h6>
        </div>
        <div class="card-body">
          <ul class="mb-0 small">
            <li>Select source central warehouse first</li>
            <li>Choose destination branch warehouse</li>
            <li>Select items to distribute</li>
            <li>Enter quantity for each item</li>
            <li>Stock will be deducted from central immediately</li>
            <li>Branch will receive stock automatically</li>
            <li>All items will share same reference number</li>
          </ul>
        </div>
      </div> -->
    </div>
  </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('itemsTable');
    const tr = table.getElementsByTagName('tr');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();

        // Loop melalui semua baris tabel (mulai dari index 1 karena index 0 adalah header)
        for (let i = 1; i < tr.length; i++) {
            // Kita ambil kolom ke-2 (index 1) karena di situ letak Nama Item/SKU
            // Sesuaikan index ini jika posisi kolom berubah
            const td = tr[i].getElementsByTagName('td')[1]; 
            
            if (td) {
                const txtValue = td.textContent || td.innerText;
                // Cek apakah teks cocok dengan pencarian
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = ""; // Tampilkan baris
                } else {
                    tr[i].style.display = "none"; // Sembunyikan baris
                }
            }
        }
    });
});
let itemsData = @json($items);
let selectedItemsCount = 0;
let allItemsSelected = false;

// Load available stock when source warehouse changes
function loadAvailableStock() {
    const sourceWarehouseId = document.getElementById('sourceWarehouse').value;
    const tbody = document.getElementById('itemsTableBody');
    
    if (!sourceWarehouseId) {
        tbody.innerHTML = '<tr id="emptyState"><td colspan="8" class="text-center text-muted py-4"><i class="bx bx-info-circle me-2"></i>Select a source warehouse to load available items</td></tr>';
        return;
    }

    // Show loading
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border spinner-border-sm me-2"></div>Loading items...</td></tr>';

    // ✅ FIX: Generate URL correctly
    const url = "{{ route('central-warehouse.api.warehouse-items', ':warehouse') }}".replace(':warehouse', sourceWarehouseId);
    
    // Fetch stock data via AJAX
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderItemsTable(data.items);
            } else {
                throw new Error(data.message || 'Failed to load items');
            }
        })
        .catch(error => {
            console.error('Error loading items:', error);
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4"><i class="bx bx-error me-2"></i>Failed to load items: ' + error.message + '</td></tr>';
        });
}

// Render items table
function renderItemsTable(items) {
    const tbody = document.getElementById('itemsTableBody');
    
    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4"><i class="bx bx-info-circle me-2"></i>No items available in this warehouse</td></tr>';
        return;
    }

    let html = '';
    items.forEach((item, index) => {
        html += `
            <tr data-item-id="${item.item_id}" data-unit-cost="${item.unit_cost || 0}">
                <td>
                    <input type="checkbox" class="item-checkbox" name="items[${index}][selected]" value="1" onchange="updateSelection()">
                    <input type="hidden" name="items[${index}][item_id]" value="${item.item_id}">
                </td>
                <td>${escapeHtml(item.item_name)}</td>
                <td><code>${escapeHtml(item.sku)}</code></td>
                <td><span class="badge bg-info">${escapeHtml(item.category_name || 'N/A')}</span></td>
                <td>${escapeHtml(item.unit)}</td>
                <td>
                    <strong class="text-success" data-available-stock="${item.closing_stock}">
                        ${parseFloat(item.closing_stock).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </strong>
                </td>
                <td>
                    <input type="number" 
                           class="form-control form-control-sm quantity-input" 
                           name="items[${index}][quantity]" 
                           step="0.01" 
                           min="0.01" 
                           max="${item.closing_stock}"
                           placeholder="0.00"
                           onchange="calculateTotal()">
                </td>
                <td>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="items[${index}][notes]" 
                           placeholder="Optional notes"
                           maxlength="255">
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    
    // Reset selections
    document.getElementById('selectAllCheckbox').checked = false;
    updateSelection();
    calculateTotal();
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Update destination info
function updateDestinationInfo() {
    const select = document.querySelector('select[name="destination_warehouse_id"]');
    const option = select.selectedOptions[0];
    const infoDiv = document.getElementById('destinationInfo');
    
    if (option && option.value) {
        const warehouseName = option.text.split(' - ')[0].trim();
        const branchName = option.dataset.branch || 'N/A';
        const address = option.dataset.address || 'N/A';
        
        document.getElementById('destWarehouseName').textContent = warehouseName;
        document.getElementById('destBranchName').textContent = branchName;
        document.getElementById('destAddress').textContent = address;
        infoDiv.classList.remove('d-none');
    } else {
        infoDiv.classList.add('d-none');
    }
}

// Toggle select all
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.item-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateSelection();
}

// Update selection count
function updateSelection() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    selectedItemsCount = checkboxes.length;
    
    document.getElementById('selectedCount').textContent = selectedItemsCount;
    document.getElementById('summarySelectedCount').textContent = selectedItemsCount;
    
    // Update select all checkbox state
    const totalCheckboxes = document.querySelectorAll('.item-checkbox').length;
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    if (totalCheckboxes > 0) {
        selectAllCheckbox.checked = selectedItemsCount === totalCheckboxes;
        selectAllCheckbox.indeterminate = selectedItemsCount > 0 && selectedItemsCount < totalCheckboxes;
    }
    
    // Enable/disable submit button
    const submitBtn = document.getElementById('submitBtn');
    const sourceWarehouse = document.getElementById('sourceWarehouse').value;
    const destWarehouse = document.querySelector('select[name="destination_warehouse_id"]').value;
    
    submitBtn.disabled = !(selectedItemsCount > 0 && sourceWarehouse && destWarehouse);
    
    calculateTotal();
}

// Calculate totals
function calculateTotal() {
    let totalQty = 0;
    let totalValue = 0;
    
    document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
        const row = checkbox.closest('tr');
        const qtyInput = row.querySelector('.quantity-input');
        const qty = parseFloat(qtyInput.value) || 0;
        const unitCost = parseFloat(row.dataset.unitCost) || 0;
        
        totalQty += qty;
        totalValue += qty * unitCost;
    });
    
    // Format numbers
    const qtyFormatted = totalQty.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const valueFormatted = 'Rp ' + Math.round(totalValue).toLocaleString('id-ID');
    
    document.getElementById('totalQuantity').textContent = qtyFormatted;
    document.getElementById('summaryTotalQty').textContent = qtyFormatted;
    document.getElementById('summaryTotalValue').textContent = valueFormatted;
}

// Reset form
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.getElementById('distributionForm').reset();
        document.getElementById('destinationInfo').classList.add('d-none');
        document.getElementById('selectAllCheckbox').checked = false;
        
        // Reset to empty state
        const tbody = document.getElementById('itemsTableBody');
        tbody.innerHTML = '<tr id="emptyState"><td colspan="8" class="text-center text-muted py-4"><i class="bx bx-info-circle me-2"></i>Select a source warehouse to load available items</td></tr>';
        
        updateSelection();
        calculateTotal();
    }
}

// Form validation
document.getElementById('distributionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const sourceWarehouse = document.querySelector('select[name="source_warehouse_id"]').value;
    const destWarehouse = document.querySelector('select[name="destination_warehouse_id"]').value;
    const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    
    // Validations...
    if (!sourceWarehouse || !destWarehouse) {
        alert('❌ Please select both warehouses');
        return false;
    }
    
    if (selectedCheckboxes.length === 0) {
        alert('❌ Please select at least one item');
        return false;
    }
    
    // Validate quantities
    let hasError = false;
    selectedCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const qtyInput = row.querySelector('.quantity-input');
        const qty = parseFloat(qtyInput.value) || 0;
        const maxStock = parseFloat(row.querySelector('[data-available-stock]').dataset.availableStock);
        
        if (qty <= 0 || qty > maxStock) {
            hasError = true;
            qtyInput.classList.add('is-invalid');
        }
    });
    
    if (hasError) {
        alert('❌ Please check quantities');
        return false;
    }
    
    if (!confirm(`Distribute ${selectedCheckboxes.length} items?`)) {
        return false;
    }
    
    // ✅ NEW APPROACH: Create new FormData with only selected items
    const newFormData = new FormData();
    
    // Add warehouse IDs
    newFormData.append('_token', document.querySelector('input[name="_token"]').value);
    newFormData.append('source_warehouse_id', sourceWarehouse);
    newFormData.append('destination_warehouse_id', destWarehouse);
    
    // Add general notes
    const generalNotes = document.querySelector('textarea[name="general_notes"]').value;
    if (generalNotes) {
        newFormData.append('general_notes', generalNotes);
    }
    
    // Add only selected items
    let itemIndex = 0;
    selectedCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const itemId = row.querySelector('input[type="hidden"]').value;
        const qty = row.querySelector('.quantity-input').value;
        const notes = row.querySelector('input[name*="[notes]"]').value;
        
        newFormData.append(`items[${itemIndex}][selected]`, '1');
        newFormData.append(`items[${itemIndex}][item_id]`, itemId);
        newFormData.append(`items[${itemIndex}][quantity]`, qty);
        if (notes) {
            newFormData.append(`items[${itemIndex}][notes]`, notes);
        }
        
        itemIndex++;
    });
    
    // Debug log
    console.log('=== SUBMITTING FORM DATA ===');
    for (let [key, value] of newFormData.entries()) {
        console.log(key, '=', value);
    }
    
    // Disable submit button
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    // Send via fetch
    fetch(form.action, {
        method: 'POST',
        body: newFormData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success:', data);
        
        if (data.success) {
            alert('✅ ' + data.message);
            window.location.href = '{{ route("central-warehouse.index") }}';
        } else {
            alert('❌ Error: ' + (data.message || 'Unknown error'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bx bx-share me-1"></i>Process Distribution';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bx bx-share me-1"></i>Process Distribution';
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($selectedItem))
    // Pre-selected item exists
    updateSelection();
    calculateTotal();
    @endif
    
    // Update destination info if already selected
    updateDestinationInfo();
    
    // Listen for warehouse changes
    document.getElementById('sourceWarehouse').addEventListener('change', function() {
        updateSelection(); // Update button state
    });
    
    document.querySelector('select[name="destination_warehouse_id"]').addEventListener('change', function() {
        updateSelection(); // Update button state
    });
});
document.addEventListener("DOMContentLoaded", function() {
    // Cek apakah elemen sourceWarehouse ada
    const sourceEl = document.getElementById('sourceWarehouse');
    
    // Jika ada dan sudah memiliki nilai (misalnya karena hanya ada 1 gudang 
    // sehingga Anda merendernya sebagai hidden input atau pre-selected option)
    if (sourceEl && sourceEl.value) {
        loadAvailableStock(); // Panggil fungsi otomatis
    }
});
</script>
@endpush
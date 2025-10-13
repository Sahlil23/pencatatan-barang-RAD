@extends('layouts.admin')

@section('title', 'Item Stok Menipis - Chicking BJM')

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
        <li class="breadcrumb-item active" aria-current="page">Stok Menipis</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Alert Warning -->
<div class="row mb-4">
  <div class="col-12">
    @if($items->count() > 0)
    <div class="alert alert-warning d-flex align-items-center" role="alert">
      <i class="bx bx-error-circle me-3" style="font-size: 24px;"></i>
      <div>
        <h6 class="alert-heading mb-1">Peringatan Stok Menipis!</h6>
        <p class="mb-0">
          Terdapat <strong>{{ $items->count() }} item</strong> yang stoknya mencapai atau di bawah batas minimum. 
          Segera lakukan restocking untuk menghindari kehabisan stok.
        </p>
      </div>
    </div>
    @else
    <div class="alert alert-success d-flex align-items-center" role="alert">
      <i class="bx bx-check-circle me-3" style="font-size: 24px;"></i>
      <div>
        <h6 class="alert-heading mb-1">Stok Aman!</h6>
        <p class="mb-0">
          Semua item memiliki stok yang mencukupi. Tidak ada item yang perlu di-restock saat ini.
        </p>
      </div>
    </div>
    @endif
  </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card border-warning">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Low Stock Items" class="rounded" />
          </div>
          <div class="dropdown">
            <span class="badge bg-warning">!</span>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1 text-warning">Stok Menipis</span>
        <h3 class="card-title mb-2 text-warning">{{ $items->count() }}</h3>
        <small class="text-warning fw-semibold">
          <i class="bx bx-error"></i> Item
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card border-danger">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-warning.png') }}" alt="Critical Items" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1 text-danger">Stok Kritis</span>
        @php $criticalCount = $items->where('current_stock', '<=', 0)->count(); @endphp
        <h3 class="card-title mb-2 text-danger">{{ $criticalCount }}</h3>
        <small class="text-danger fw-semibold">
          <i class="bx bx-x"></i> Item Habis
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="Categories Affected" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Kategori Terdampak</span>
        @php $categoriesAffected = $items->pluck('category_id')->unique()->count(); @endphp
        <h3 class="card-title mb-2">{{ $categoriesAffected }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-category"></i> Kategori
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Suppliers Affected" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Supplier Terdampak</span>
        @php $suppliersAffected = $items->whereNotNull('supplier_id')->pluck('supplier_id')->unique()->count(); @endphp
        <h3 class="card-title mb-2">{{ $suppliersAffected }}</h3>
        <small class="text-info fw-semibold">
          <i class="bx bx-store"></i> Supplier
        </small>
      </div>
    </div>
  </div>
</div>

<!-- Main Table Card -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bx bx-error me-2 text-warning"></i>
      Item Stok Menipis
      @if($items->count() > 0)
        <span class="badge bg-warning ms-2">{{ $items->count() }} Item</span>
      @endif
    </h5>
    <div class="d-flex gap-2">
      @if($items->count() > 0)
      <button class="btn btn-outline-warning btn-sm" onclick="selectAll()">
        <i class="bx bx-check-square me-1"></i>
        Pilih Semua
      </button>
      <button class="btn btn-warning btn-sm" onclick="bulkAdjustStock()" id="bulkAdjustBtn" disabled>
        <i class="bx bx-transfer me-1"></i>
        Sesuaikan Terpilih
      </button>
      @endif
      <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i>
        Kembali ke Item
      </a>
      <a href="{{ route('items.create') }}" class="btn btn-primary btn-sm">
        <i class="bx bx-plus me-1"></i>
        Tambah Item
      </a>
    </div>
  </div>
  
  @if($items->count() > 0)
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead class="table-warning">
        <tr>
          <th style="width: 50px;">
            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll()">
          </th>
          <th style="width: 80px;">
            <i class="bx bx-barcode me-1"></i>
            SKU
          </th>
          <th>
            <i class="bx bx-package me-1"></i>
            Item
          </th>
          <th>
            <i class="bx bx-category me-1"></i>
            Kategori
          </th>
          <th>
            <i class="bx bx-store me-1"></i>
            Supplier
          </th>
          <th class="text-center">
            <i class="bx bx-box me-1"></i>
            Stok vs Minimum
          </th>
          <th class="text-center">
            <i class="bx bx-signal-1 me-1"></i>
            Status
          </th>
          <th class="text-center">
            <i class="bx bx-cog me-1"></i>
            Aksi
          </th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach ($items as $item)
        <tr class="{{ $item->current_stock <= 0 ? 'table-danger' : 'table-warning' }}">
          <td>
            <input type="checkbox" class="form-check-input item-checkbox" value="{{ $item->id }}" onchange="updateBulkButton()">
          </td>
          <td>
            <span class="badge bg-label-dark">{{ $item->sku }}</span>
          </td>
          <td>
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ $item->current_stock <= 0 ? 'danger' : 'warning' }}">
                  <i class="bx {{ $item->current_stock <= 0 ? 'bx-x' : 'bx-error' }}"></i>
                </span>
              </div>
              <div>
                <strong>{{ $item->item_name }}</strong>
                <br><small class="text-muted">
                  <i class="bx bx-cube"></i>
                  {{ $item->unit }}
                </small>
              </div>
            </div>
          </td>
          <td>
            @if($item->category)
              <div class="d-flex align-items-center">
                <i class="bx bx-category text-primary me-2"></i>
                <span>{{ $item->category->category_name }}</span>
              </div>
            @else
              <span class="text-muted">
                <i class="bx bx-category-alt me-1"></i>
                Tidak ada kategori
              </span>
            @endif
          </td>
          <td>
            @if($item->supplier)
              <div class="d-flex align-items-center">
                <i class="bx bx-store text-success me-2"></i>
                <div>
                  <span>{{ Str::limit($item->supplier->supplier_name, 20) }}</span>
                  @if($item->supplier->contact_person)
                    <br><small class="text-muted">{{ $item->supplier->contact_person }}</small>
                  @endif
                </div>
              </div>
            @else
              <span class="text-muted">
                <i class="bx bx-store-alt me-1"></i>
                Tidak ada supplier
              </span>
            @endif
          </td>
          <td class="text-center">
            <div class="d-flex flex-column align-items-center">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fw-bold text-{{ $item->current_stock <= 0 ? 'danger' : 'warning' }}">
                  {{ number_format($item->current_stock, 0) }}
                </span>
                <span class="text-muted">/</span>
                <span class="text-muted">{{ number_format($item->low_stock_threshold, 0) }}</span>
              </div>
              <div class="progress" style="width: 80px; height: 6px;">
                @php
                  $percentage = $item->low_stock_threshold > 0 ? 
                    min(100, max(0, ($item->current_stock / $item->low_stock_threshold) * 100)) : 0;
                @endphp
                <div class="progress-bar bg-{{ $item->current_stock <= 0 ? 'danger' : 'warning' }}" 
                     style="width: {{ $percentage }}%"></div>
              </div>
              <small class="text-muted">{{ number_format($percentage, 0) }}%</small>
            </div>
          </td>
          <td class="text-center">
            @if($item->current_stock <= 0)
              <span class="badge bg-danger">
                <i class="bx bx-x me-1"></i>Habis
              </span>
            @else
              <span class="badge bg-warning">
                <i class="bx bx-error me-1"></i>Menipis
              </span>
            @endif
          </td>
          <td class="text-center">
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('items.show', $item->id) }}">
                  <i class="bx bx-show me-1"></i> 
                  Lihat Detail
                </a>
                <a class="dropdown-item" href="#" onclick="showStockAdjustment({{ $item->id }}, '{{ $item->item_name }}', {{ $item->current_stock }})">
                  <i class="bx bx-transfer me-1 text-warning"></i> 
                  Sesuaikan Stok
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('items.edit', $item->id) }}">
                  <i class="bx bx-edit-alt me-1"></i> 
                  Edit Item
                </a>
                @if($item->supplier)
                <a class="dropdown-item" href="{{ route('suppliers.show', $item->supplier->id) }}">
                  <i class="bx bx-store me-1 text-success"></i> 
                  Lihat Supplier
                </a>
                @endif
              </div>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  
  <div class="card-footer d-flex justify-content-between align-items-center">
    <small class="text-muted">
      Menampilkan {{ $items->count() }} item dengan stok menipis
    </small>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
        <i class="bx bx-printer me-1"></i>
        Print Laporan
      </button>
      <button class="btn btn-outline-danger btn-sm" onclick="exportToCSV()">
        <i class="bx bx-download me-1"></i>
        Export CSV
      </button>
      <button class="btn btn-outline-info btn-sm" onclick="generateRestockReport()">
        <i class="bx bx-file me-1"></i>
        Laporan Restock
      </button>
    </div>
  </div>
  @else
  <!-- Empty State -->
  <div class="card-body text-center py-5">
    <div class="d-flex flex-column align-items-center">
      <i class="bx bx-check-circle text-success" style="font-size: 64px;"></i>
      <h5 class="mt-3 text-success">Semua Stok Aman!</h5>
      <p class="text-muted mb-4">
        Tidak ada item yang memiliki stok menipis saat ini.<br>
        Semua item memiliki stok di atas batas minimum yang ditentukan.
      </p>
      <div class="d-flex gap-2">
        <a href="{{ route('items.index') }}" class="btn btn-primary">
          <i class="bx bx-package me-1"></i>
          Lihat Semua Item
        </a>
        <a href="{{ route('items.create') }}" class="btn btn-outline-primary">
          <i class="bx bx-plus me-1"></i>
          Tambah Item Baru
        </a>
      </div>
    </div>
  </div>
  @endif
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
      <form id="stockAdjustmentForm" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Item</label>
            <input type="text" class="form-control" id="adjustmentItemName" readonly>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Stok Saat Ini</label>
            <input type="text" class="form-control" id="adjustmentCurrentStock" readonly>
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
          <button type="submit" class="btn btn-warning">
            <i class="bx bx-save me-1"></i>
            Simpan Penyesuaian
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bulk Stock Adjustment Modal -->
<div class="modal fade" id="bulkStockAdjustmentModal" tabindex="-1" aria-labelledby="bulkStockAdjustmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bulkStockAdjustmentModalLabel">
          <i class="bx bx-transfer me-2"></i>
          Sesuaikan Stok Massal
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <i class="bx bx-info-circle me-2"></i>
          Penyesuaian ini akan diterapkan pada semua item yang dipilih
        </div>
        
        <form id="bulkStockAdjustmentForm">
          @csrf
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Tipe Penyesuaian <span class="text-danger">*</span></label>
              <select class="form-select" name="bulk_adjustment_type" required>
                <option value="">Pilih tipe penyesuaian</option>
                <option value="add">Tambah Stok</option>
                <option value="reduce">Kurangi Stok</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Jumlah <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="bulk_quantity" step="0.01" min="0.01" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Catatan <span class="text-danger">*</span></label>
            <textarea class="form-control" name="bulk_notes" rows="3" placeholder="Alasan penyesuaian stok massal..." required></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Item Terpilih:</label>
            <div id="selectedItemsList" class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
              <!-- Selected items will be populated here -->
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-warning" onclick="processBulkAdjustment()">
          <i class="bx bx-save me-1"></i>
          Proses Penyesuaian
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Stock adjustment modal
  window.showStockAdjustment = function(itemId, itemName, currentStock) {
    document.getElementById('adjustmentItemName').value = itemName;
    document.getElementById('adjustmentCurrentStock').value = currentStock;
    document.getElementById('stockAdjustmentForm').action = `/items/${itemId}/adjust-stock`;
    
    const modal = new bootstrap.Modal(document.getElementById('stockAdjustmentModal'));
    modal.show();
  };

  // Select all functionality
  window.selectAll = function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAllBtn = document.querySelector('[onclick="selectAll()"]');
    
    if (selectAllBtn.textContent.includes('Pilih Semua')) {
      checkboxes.forEach(cb => cb.checked = true);
      selectAllBtn.innerHTML = '<i class="bx bx-square me-1"></i>Batal Pilih';
      selectAllBtn.classList.remove('btn-outline-warning');
      selectAllBtn.classList.add('btn-warning');
    } else {
      checkboxes.forEach(cb => cb.checked = false);
      selectAllBtn.innerHTML = '<i class="bx bx-check-square me-1"></i>Pilih Semua';
      selectAllBtn.classList.remove('btn-warning');
      selectAllBtn.classList.add('btn-outline-warning');
    }
    
    updateBulkButton();
  };

  // Toggle select all checkbox
  window.toggleSelectAll = function() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.item-checkbox');
    
    checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
    updateBulkButton();
  };

  // Update bulk button state
  window.updateBulkButton = function() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const bulkBtn = document.getElementById('bulkAdjustBtn');
    
    if (checkedBoxes.length > 0) {
      bulkBtn.disabled = false;
      bulkBtn.textContent = `Sesuaikan ${checkedBoxes.length} Item`;
    } else {
      bulkBtn.disabled = true;
      bulkBtn.innerHTML = '<i class="bx bx-transfer me-1"></i>Sesuaikan Terpilih';
    }
  };

  // Bulk adjust stock
  window.bulkAdjustStock = function() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    // Populate selected items list
    const selectedItemsList = document.getElementById('selectedItemsList');
    selectedItemsList.innerHTML = '';
    
    checkedBoxes.forEach(checkbox => {
      const row = checkbox.closest('tr');
      const itemName = row.querySelector('strong').textContent;
      const sku = row.querySelector('.badge').textContent;
      
      const itemDiv = document.createElement('div');
      itemDiv.className = 'mb-2 p-2 border rounded bg-white';
      itemDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
          <span><strong>${itemName}</strong></span>
          <span class="badge bg-label-secondary">${sku}</span>
        </div>
      `;
      selectedItemsList.appendChild(itemDiv);
    });
    
    const modal = new bootstrap.Modal(document.getElementById('bulkStockAdjustmentModal'));
    modal.show();
  };

  // Process bulk adjustment
  window.processBulkAdjustment = function() {
    const form = document.getElementById('bulkStockAdjustmentForm');
    const formData = new FormData(form);
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    
    // Add selected item IDs
    const itemIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    // Process each item individually (you might want to create a bulk endpoint)
    let processedCount = 0;
    
    itemIds.forEach(itemId => {
      fetch(`/items/${itemId}/adjust-stock`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          adjustment_type: formData.get('bulk_adjustment_type'),
          quantity: formData.get('bulk_quantity'),
          notes: formData.get('bulk_notes')
        })
      }).then(response => {
        processedCount++;
        if (processedCount === itemIds.length) {
          location.reload(); // Reload page after all adjustments
        }
      });
    });
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('bulkStockAdjustmentModal')).hide();
  };

  // Export to CSV function
  window.exportToCSV = function() {
    const headers = ['SKU', 'Nama Item', 'Kategori', 'Supplier', 'Stok Saat Ini', 'Minimum Stok', 'Status', 'Persentase'];
    const rows = [];
    
    @foreach($items as $item)
    rows.push([
      '{{ $item->sku }}',
      '{{ $item->item_name }}',
      '{{ $item->category->category_name ?? "Tidak ada" }}',
      '{{ $item->supplier->supplier_name ?? "Tidak ada" }}',
      '{{ $item->current_stock }}',
      '{{ $item->low_stock_threshold }}',
      '{{ $item->current_stock <= 0 ? "Habis" : "Menipis" }}',
      '{{ $item->low_stock_threshold > 0 ? round(($item->current_stock / $item->low_stock_threshold) * 100, 2) : 0 }}%'
    ]);
    @endforeach
    
    let csvContent = headers.join(',') + '\n';
    rows.forEach(row => {
      csvContent += row.map(cell => `"${cell}"`).join(',') + '\n';
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'low_stock_items_' + new Date().toISOString().slice(0,10) + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Generate restock report
  window.generateRestockReport = function() {
    let reportContent = `LAPORAN RESTOCK - ${new Date().toLocaleDateString('id-ID')}\n`;
    reportContent += '=' .repeat(50) + '\n\n';
    
    @foreach($items as $item)
    reportContent += `Item: {{ $item->item_name }}\n`;
    reportContent += `SKU: {{ $item->sku }}\n`;
    reportContent += `Kategori: {{ $item->category->category_name ?? "Tidak ada" }}\n`;
    reportContent += `Supplier: {{ $item->supplier->supplier_name ?? "Tidak ada" }}\n`;
    reportContent += `Stok Saat Ini: {{ $item->current_stock }} {{ $item->unit }}\n`;
    reportContent += `Minimum Stok: {{ $item->low_stock_threshold }} {{ $item->unit }}\n`;
    reportContent += `Status: {{ $item->current_stock <= 0 ? "HABIS" : "MENIPIS" }}\n`;
    reportContent += `Rekomendasi Order: {{ max($item->low_stock_threshold * 2, $item->low_stock_threshold + 50) }} {{ $item->unit }}\n`;
    reportContent += '-'.repeat(30) + '\n\n';
    @endforeach
    
    const blob = new Blob([reportContent], { type: 'text/plain;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'restock_report_' + new Date().toISOString().slice(0,10) + '.txt');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };
});
</script>
@endpush

@push('styles')
<style>
@media print {
  .btn, .breadcrumb, .card-header .d-flex .btn, .dropdown, .modal {
    display: none !important;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
  }
  
  .table {
    font-size: 12px;
  }
  
  .alert {
    border: 1px solid #000 !important;
    background: #fff !important;
    color: #000 !important;
  }
}

.table th {
  background-color: #fff3cd;
  border-top: 1px solid #ffeaa7;
  font-weight: 600;
}

.table-warning {
  background-color: rgba(255, 193, 7, 0.1);
}

.table-danger {
  background-color: rgba(220, 53, 69, 0.1);
}

.table-hover tbody tr:hover {
  background-color: rgba(255, 193, 7, 0.2);
}

.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  font-size: 18px;
}

.progress {
  background-color: #e9ecef;
}

.card.border-warning {
  border-color: #ffc107 !important;
}

.card.border-danger {
  border-color: #dc3545 !important;
}
</style>
@endpush
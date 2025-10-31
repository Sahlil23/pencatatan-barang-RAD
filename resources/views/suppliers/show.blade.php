@extends('layouts.admin')

@section('title', 'Detail Supplier - ' . $supplier->supplier_name . ' - Chicking BJM')

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
          <a href="{{ route('suppliers.index') }}">Supplier</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ $supplier->supplier_name }}</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <!-- Supplier Info -->
  <div class="col-xl-4 col-lg-5">
    <div class="card mb-4">
      <div class="card-body">
        <div class="user-avatar-section">
          <div class="d-flex align-items-center flex-column">
            <div class="avatar avatar-xl mx-auto mb-3">
              <span class="avatar-initial rounded-circle bg-label-primary" style="font-size: 2rem;">
                {{ strtoupper(substr($supplier->supplier_name, 0, 2)) }}
              </span>
            </div>
            <div class="user-info text-center">
              <h4 class="mb-2">{{ $supplier->supplier_name }}</h4>
              <span class="badge bg-label-primary">
                {{ $supplier->items_count }} Item{{ $supplier->items_count != 1 ? 's' : '' }}
              </span>
            </div>
          </div>
        </div>
        
 <div class="d-flex justify-content-around flex-wrap mt-4 pt-3 pb-2">
          <div class="d-flex align-items-start me-4 mt-3 gap-3">
            <span class="badge bg-label-success p-2 rounded">
              <i class="bx bx-transfer bx-sm"></i>
            </span>
            <div>
              <h5 class="mb-0">{{ $stats['total_transactions'] }}</h5>
              <span>Total Transaksi</span>
            </div>
          </div>
          <div class="d-flex align-items-start me-4 mt-3 gap-3">
            <span class="badge bg-label-info p-2 rounded">
              <i class="bx bx-package bx-sm"></i>
            </span>
            <div>
              <h5 class="mb-0">{{ $stats['total_items'] }}</h5>
              <span>Items</span>
            </div>
          </div>
          <div class="d-flex align-items-start mt-3 gap-3">
            <span class="badge bg-label-warning p-2 rounded">
              <i class="bx bx-trending-up bx-sm"></i>
            </span>
            <div>
              <h5 class="mb-0">{{ $stats['recent_activity'] }}</h5>
              <span>Aktif 30 Hari</span>
            </div>
          </div>
        </div>
        
        <h5 class="pb-2 border-bottom mb-4">Details</h5>
        <div class="info-container">
          <ul class="list-unstyled">
            <li class="mb-3">
              <span class="fw-medium me-2">Contact Person:</span>
              <span>{{ $supplier->contact_person ?: '-' }}</span>
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">Phone:</span>
              @if($supplier->phone)
                @php
                  $phone = preg_replace('/\D+/', '', $supplier->phone);
                  if (strpos($phone, '0') === 0) {
                    $phone = '62' . substr($phone, 1);
                  }
                @endphp
                <a href="https://wa.me/{{ $phone }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                  {{ $supplier->phone }}
                </a>
              @else
                <span>-</span>
              @endif
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">Address:</span>
              <span>{{ $supplier->address ?: '-' }}</span>
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">First Transaction:</span>
              <span>{{ $stats['first_transaction'] ? $stats['first_transaction']->transaction_date->format('d M Y') : 'Belum ada' }}</span>
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">Last Transaction:</span>
              <span>{{ $stats['last_transaction'] ? $stats['last_transaction']->transaction_date->format('d M Y') : 'Belum ada' }}</span>
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">Created:</span>
              <span>{{ $supplier->created_at->format('d M Y, H:i') }}</span>
            </li>
          </ul>
        </div>

        
        <!-- Action Buttons -->
        <div class="d-flex justify-content-center pt-3">
          <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary me-3">
            <i class="bx bx-edit-alt me-1"></i>
            Edit
          </a>
          <a href="{{ route('stock-transactions.create', ['supplier' => $supplier->id]) }}" class="btn btn-success">
            <i class="bx bx-plus me-1"></i>
            Transaksi
          </a>
          <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
            <i class="bx bx-trash-alt me-1"></i>
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Card -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="bx bx-chart me-2"></i>
          Statistics
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-6">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial bg-label-success rounded">
                  <i class="bx bx-trending-up"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block">Total Stock</small>
                <div class="d-flex align-items-center">
                  <h6 class="mb-0 me-1">{{ number_format($supplier->items->sum('current_stock'), 0) }}</h6>
                  <small class="text-success fw-medium">
                    <i class="bx bx-chevron-up"></i>
                    Items
                  </small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial bg-label-warning rounded">
                  <i class="bx bx-error-circle"></i>
                </span>
              </div>
              <div>
                <small class="text-muted d-block">Low Stock</small>
                <div class="d-flex align-items-center">
                  <h6 class="mb-0 me-1">{{ $supplier->items->where('current_stock', '<=', function($item) { return $item->low_stock_threshold; })->count() }}</h6>
                  <small class="text-warning fw-medium">
                    <i class="bx bx-chevron-down"></i>
                    Items
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <hr>
        
        <!-- Categories Breakdown -->
        <div class="mt-4">
          <small class="text-muted d-block mb-3">Items by Category</small>
          @php
            $categories = $supplier->items->groupBy('category.category_name');
          @endphp
          @foreach($categories as $categoryName => $items)
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-medium">{{ $categoryName ?: 'No Category' }}</span>
              <span class="badge bg-primary">{{ $items->count() }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <!-- Items Table -->
  <div class="col-xl-8 col-lg-7">
    <div class="nav-align-top mb-4">
      <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item">
          <button
            type="button"
            class="nav-link active"
            role="tab"
            data-bs-toggle="tab"
            data-bs-target="#navs-pills-items"
            aria-controls="navs-pills-items"
            aria-selected="true"
          >
            <i class="bx bx-package me-1"></i>
            Items ({{ $supplier->items->count() }})
          </button>
        </li>
        <li class="nav-item">
          <button
            type="button"
            class="nav-link"
            role="tab"
            data-bs-toggle="tab"
            data-bs-target="#navs-pills-activity"
            aria-controls="navs-pills-activity"
            aria-selected="false"
          >
            <i class="bx bx-history me-1"></i>
            Recent Activity
          </button>
        </li>
      </ul>
      <div class="tab-content">
        <!-- Items Tab -->
        <div class="tab-pane fade show active" id="navs-pills-items">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Items dari {{ $supplier->supplier_name }}</h5>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="exportItems()">
                  <i class="bx bx-export me-1"></i>
                  Export
                </button>
                <a href="{{ route('items.create') }}?supplier={{ $supplier->id }}" class="btn btn-sm btn-primary">
                  <i class="bx bx-plus me-1"></i>
                  Add Item
                </a>
              </div>
            </div>
            
            <!-- Search and Filter -->
            <div class="card-body border-bottom">
              <div class="row g-3">
                <div class="col-md-4">
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Search items..."
                      id="searchItems"
                    />
                  </div>
                </div>
                <div class="col-md-3">
                  <select class="form-select" id="filterCategory">
                    <option value="">All Categories</option>
                    @foreach($categories as $categoryName => $items)
                      <option value="{{ $categoryName }}">{{ $categoryName ?: 'No Category' }}</option>
                    @endforeach
                  </select>
                </div>
                <!-- <div class="col-md-3">
                  <select class="form-select" id="filterStock">
                    <option value="">All Stock</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                  </select>
                </div> -->
                <div class="col-md-2">
                  <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                    <i class="bx bx-x me-1"></i>
                    Clear
                  </button>
                </div>
              </div>
            </div>

            <div class="table-responsive text-nowrap">
              <table class="table table-hover" id="itemsTable">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Unit</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                  @forelse($supplier->items as $item)
                    <tr data-category="{{ $item->category->category_name ?? '' }}" 
                        data-stock-status="{{ $item->current_stock <= $item->low_stock_threshold ? 'low_stock' : ($item->current_stock > 0 ? 'in_stock' : 'out_of_stock') }}">
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial bg-label-primary rounded">
                              {{ strtoupper(substr($item->item_name, 0, 2)) }}
                            </span>
                          </div>
                          <div class="d-flex flex-column">
                            <h6 class="mb-0">{{ $item->item_name }}</h6>
                            <small class="text-muted">{{ Str::limit($item->description, 30) }}</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="fw-medium">{{ $item->sku }}</span>
                      </td>
                      <td>
                        @if($item->category)
                          <span class="badge bg-label-info">{{ $item->category->category_name }}</span>
                        @else
                          <span class="badge bg-label-secondary">No Category</span>
                        @endif
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <span class="fw-medium me-2">{{ number_format($item->current_stock, 2) }}</span>
                          @if($item->current_stock <= $item->low_stock_threshold)
                            <i class="bx bx-error-circle text-warning" title="Low Stock"></i>
                          @endif
                        </div>
                      </td>
                      <td>{{ $item->unit }}</td>
                      <td>
                        <div class="dropdown">
                          <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('items.show', $item) }}">
                              <i class="bx bx-show me-1"></i> View
                            </a>
                            <a class="dropdown-item" href="{{ route('items.edit', $item) }}">
                              <i class="bx bx-edit-alt me-1"></i> Edit
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-warning" href="#" onclick="quickStockUpdate({{ $item->id }}, '{{ $item->item_name }}')">
                              <i class="bx bx-package me-1"></i> Update Stock
                            </a>
                          </div>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                          <i class="bx bx-package" style="font-size: 3rem; color: #ddd;"></i>
                          <h6 class="mt-2 mb-1">No Items Found</h6>
                          <p class="text-muted mb-3">This supplier doesn't have any items yet.</p>
                          <a href="{{ route('items.create') }}?supplier={{ $supplier->id }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i>
                            Add First Item
                          </a>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Activity Tab -->
        <div class="tab-pane fade" id="navs-pills-activity">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
              <div class="timeline">
                <div class="timeline-item">
                  <div class="timeline-indicator-advanced timeline-indicator-success">
                    <i class="bx bx-check-circle"></i>
                  </div>
                  <div class="timeline-event">
                    <div class="timeline-header border-bottom mb-3">
                      <h6 class="mb-0">Supplier Created</h6>
                      <small class="text-muted">{{ $supplier->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-2">Supplier {{ $supplier->supplier_name }} was added to the system</p>
                  </div>
                </div>
                
                @if($supplier->updated_at != $supplier->created_at)
                <div class="timeline-item">
                  <div class="timeline-indicator-advanced timeline-indicator-info">
                    <i class="bx bx-edit"></i>
                  </div>
                  <div class="timeline-event">
                    <div class="timeline-header border-bottom mb-3">
                      <h6 class="mb-0">Supplier Updated</h6>
                      <small class="text-muted">{{ $supplier->updated_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-2">Supplier information was updated</p>
                  </div>
                </div>
                @endif
                
                @foreach($supplier->items->take(5) as $item)
                <div class="timeline-item">
                  <div class="timeline-indicator-advanced timeline-indicator-primary">
                    <i class="bx bx-package"></i>
                  </div>
                  <div class="timeline-event">
                    <div class="timeline-header border-bottom mb-3">
                      <h6 class="mb-0">Item Added</h6>
                      <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-2">{{ $item->item_name }} was added from this supplier</p>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Stock Update Modal -->
<div class="modal fade" id="quickStockModal" tabindex="-1" aria-labelledby="quickStockModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="quickStockModalLabel">Quick Stock Update</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="quickStockForm">
        <div class="modal-body">
          <input type="hidden" id="stockItemId">
          <div class="mb-3">
            <label class="form-label">Item</label>
            <p class="form-control-static fw-medium" id="stockItemName"></p>
          </div>
          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Transaction Type</label>
              <select class="form-select" id="stockTransactionType" required>
                <option value="IN">Stock In (+)</option>
                <option value="OUT">Stock Out (-)</option>
                <option value="ADJUSTMENT">Adjustment (±)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Quantity</label>
              <input type="number" class="form-control" id="stockQuantity" step="0.01" min="0.01" required>
            </div>
          </div>
          <div class="mt-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" id="stockNotes" rows="2" placeholder="Optional notes..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Stock</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete supplier <strong>"{{ $supplier->supplier_name }}"</strong>?</p>
        @if($supplier->items->count() > 0)
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Warning:</strong> This supplier has {{ $supplier->items->count() }} item(s). You cannot delete this supplier until all items are removed or transferred to another supplier.
          </div>
        @else
          <div class="alert alert-danger">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Warning:</strong> This action cannot be undone. All data related to this supplier will be permanently deleted.
          </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        @if($supplier->items->count() == 0)
          <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Yes, Delete Supplier</button>
          </form>
        @else
          <button type="button" class="btn btn-danger" disabled>Cannot Delete</button>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchItems');
    const categoryFilter = document.getElementById('filterCategory');
    const stockFilter = document.getElementById('filterStock');
    const tableRows = document.querySelectorAll('#itemsTable tbody tr[data-category]');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;
        const selectedStock = stockFilter.value;

        tableRows.forEach(row => {
            const itemName = row.querySelector('td:first-child h6').textContent.toLowerCase();
            const sku = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const category = row.dataset.category;
            const stockStatus = row.dataset.stockStatus;

            const matchesSearch = itemName.includes(searchTerm) || sku.includes(searchTerm);
            const matchesCategory = !selectedCategory || category === selectedCategory;
            const matchesStock = !selectedStock || stockStatus === selectedStock;

            if (matchesSearch && matchesCategory && matchesStock) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterTable);
    categoryFilter.addEventListener('change', filterTable);
    stockFilter.addEventListener('change', filterTable);

    // Clear filters
    window.clearFilters = function() {
        searchInput.value = '';
        categoryFilter.value = '';
        stockFilter.value = '';
        filterTable();
    };

    // Quick stock update
    window.quickStockUpdate = function(itemId, itemName) {
        document.getElementById('stockItemId').value = itemId;
        document.getElementById('stockItemName').textContent = itemName;
        
        const modal = new bootstrap.Modal(document.getElementById('quickStockModal'));
        modal.show();
    };

    // Handle quick stock form submission
    document.getElementById('quickStockForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            item_id: document.getElementById('stockItemId').value,
            transaction_type: document.getElementById('stockTransactionType').value,
            quantity: document.getElementById('stockQuantity').value,
            notes: document.getElementById('stockNotes').value
        };

        // Submit to stock transaction endpoint
        fetch('/api/stock-transactions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh page to show updated data
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating stock');
        });
    });

    // Export function
    window.exportItems = function() {
        window.open('/suppliers/{{ $supplier->id }}/export-items', '_blank');
    };

    // Delete confirmation
    window.confirmDelete = function() {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    };
});
</script>
@endpush

@push('styles')
<style>
.user-avatar-section {
    padding: 2rem 0 1rem;
}

.avatar-xl {
    width: 6rem;
    height: 6rem;
}

.avatar-xl .avatar-initial {
    font-size: 2.5rem;
}

.info-container .list-unstyled li {
    padding: 0.375rem 0;
}

.timeline {
    position: relative;
    padding-left: 1.5rem;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 2rem;
    width: 2px;
    height: calc(100% - 1rem);
    background-color: #ddd;
}

.timeline-indicator-advanced {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: white;
    border: 2px solid;
}

.timeline-indicator-success {
    border-color: #28c76f;
    color: #28c76f;
}

.timeline-indicator-info {
    border-color: #00cfe8;
    color: #00cfe8;
}

.timeline-indicator-primary {
    border-color: #696cff;
    color: #696cff;
}

.timeline-event {
    margin-left: 1rem;
}

.nav-pills .nav-link.active {
    background-color: #696cff;
}

.table td {
    vertical-align: middle;
}

@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .col-md-4, .col-md-3, .col-md-2 {
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush
@extends('layouts.admin')

@section('title', 'Pending Distribusi - ' . $warehouse->warehouse_name)

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('outlet-warehouse.index') }}">Outlet Warehouse</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('outlet-warehouse.show', $warehouse->id) }}">{{ $warehouse->warehouse_name }}</a>
                </li>
                <li class="breadcrumb-item active">Pending Distribusi</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-warning text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="text-white mb-2">
                            <i class='bx bx-time-five me-2'></i>{{ $warehouse->warehouse_name }}
                        </h4>
                        <p class="mb-0 opacity-75">
                            <i class='bx bx-map me-1'></i>{{ $warehouse->address }}
                            @if($warehouse->branch)
                                <span class="ms-3">
                                    <i class='bx bx-building me-1'></i>{{ $warehouse->branch->branch_name }}
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        {{-- UBAH: Rute ke outlet show --}}
                        <a href="{{ route('outlet-warehouse.show', $warehouse->id) }}" class="btn btn-light">
                            <i class='bx bx-arrow-back me-1'></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class='bx bx-file fs-4'></i>
                        </span>
                    </div>
                    <div>
                        <span class="fw-semibold d-block text-muted mb-1">Pending Distribusi</span>
                        <h3 class="mb-0">{{ $summary['total_references'] }}</h3>
                        <small class="text-muted">Reference Numbers</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class='bx bx-package fs-4'></i>
                        </span>
                    </div>
                    <div>
                        <span class="fw-semibold d-block text-muted mb-1">Total Items</span>
                        <h3 class="mb-0">{{ $summary['total_items'] }}</h3>
                        <small class="text-muted">Items to Review</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class='bx bx-box fs-4'></i>
                        </span>
                    </div>
                    <div>
                        <span class="fw-semibold d-block text-muted mb-1">Total Quantity</span>
                        <h3 class="mb-0">{{ number_format($summary['total_quantity'], 2) }}</h3>
                        <small class="text-muted">Units</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@forelse($distributions as $referenceNo => $items)
    @php
        $firstItem = $items->first();
        $totalQuantity = $items->sum('quantity');
        $itemCount = $items->count();
    @endphp
    
    {{-- UBAH: Rute ke outlet approve --}}
    <form action="{{ route('outlet-warehouse.approve-distribution') }}" 
          method="POST" 
          id="form-{{ $referenceNo }}"
          class="distribution-form">
        @csrf
        <input type="hidden" name="reference_no" value="{{ $referenceNo }}">
        
        <div class="card mb-3 distribution-card" data-reference="{{ $referenceNo }}">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1">
                            <span class="badge bg-label-warning me-2">PENDING</span>
                            <strong>{{ $referenceNo }}</strong>
                        </h5>
                        <div class="text-muted small">
                            <i class='bx bx-building me-1'></i>
                            <strong>From:</strong> {{ $firstItem->branchWarehouse->warehouse_name ?? 'N/A' }}
                            <span class="mx-2">|</span>
                            <i class='bx bx-calendar me-1'></i>
                            <strong>Date:</strong> {{ $firstItem->transaction_date->format('d M Y H:i') }}
                            <span class="mx-2">|</span>
                            <i class='bx bx-user me-1'></i>
                            <strong>Requested by:</strong> {{ $firstItem->user->full_name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <button class="btn btn-sm btn-outline-secondary toggle-details" 
                                data-reference="{{ $referenceNo }}"
                                type="button">
                            <i class='bx bx-chevron-down'></i> Show Details
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body distribution-details" id="details-{{ $referenceNo }}" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-3">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" 
                                           class="form-check-input select-all-items" 
                                           data-reference="{{ $referenceNo }}"
                                           checked>
                                </th>
                                <th width="35%">Item</th>
                                <th width="15%" class="text-end">Requested Qty</th>
                                <th width="20%">Approved Qty</th>
                                <th width="25%">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr class="distribution-item-row">
                                    <td>
                                        <input type="checkbox" 
                                               class="form-check-input item-checkbox" 
                                               name="items[{{ $loop->index }}][selected]"
                                               value="1"
                                               data-reference="{{ $referenceNo }}"
                                               checked>
                                        <input type="hidden" 
                                               name="items[{{ $loop->index }}][id]" 
                                               value="{{ $item->id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $item->item->item_name }}</strong>
                                            <small class="text-muted">
                                                <i class='bx bx-barcode me-1'></i>{{ $item->item->sku }}
                                                <span class="ms-2">
                                                    <i class='bx bx-category me-1'></i>{{ $item->item->category->category_name ?? '-' }}
                                                </span>
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-label-primary fs-6">
                                            {{ number_format($item->quantity, 2) }} {{ $item->item->unit }}
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               class="form-control form-control-sm approved-quantity" 
                                               name="items[{{ $loop->index }}][approved_quantity]"
                                               data-max="{{ $item->quantity }}"
                                               value="{{ $item->quantity }}"
                                               min="0"
                                               max="{{ $item->quantity }}"
                                               step="0.01"
                                               placeholder="0.00"
                                               required>
                                        <small class="text-muted">Max: {{ number_format($item->quantity, 2) }}</small>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               class="form-control form-control-sm" 
                                               name="items[{{ $loop->index }}][notes]"
                                               placeholder="Optional notes"
                                               maxlength="255">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end">
                                    <strong>{{ number_format($totalQuantity, 2) }}</strong>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row">
                    <div class="col-12">
                        <label class="form-label">General Notes (Optional)</label>
                        <textarea class="form-control mb-3" 
                                  name="general_notes"
                                  rows="2" 
                                  placeholder="Add notes for this distribution..."
                                  maxlength="500"></textarea>
                    </div>
                </div>

                <div class="alert alert-info mb-3">
                    <i class='bx bx-info-circle me-2'></i>
                    <strong>Info:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Approve All:</strong> All items will be received into outlet stock</li>
                        <li><strong>Approve Selected:</strong> Only checked items with approved quantity will be received</li>
                        <li><strong>Reject:</strong> Distribution will be cancelled and stock will be returned to branch</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="submit" 
                                name="action" 
                                value="approve_all"
                                class="btn btn-success btn-approve-all">
                            <i class='bx bx-check me-1'></i>Approve All
                        </button>
                        <button type="submit" 
                                name="action" 
                                value="approve_selected"
                                class="btn btn-primary btn-approve-selected">
                            <i class='bx bx-check-double me-1'></i>Approve Selected
                        </button>
                    </div>
                    <div>
                        <button type="button" 
                                class="btn btn-danger btn-reject" 
                                data-reference="{{ $referenceNo }}">
                            <i class='bx bx-x me-1'></i>Reject Distribution
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@empty
    <div class="card">
        <div class="card-body text-center py-5">
            <i class='bx bx-check-circle text-success' style="font-size: 4rem;"></i>
            <h4 class="mt-3">No Pending Distributions</h4>
            <p class="text-muted">All distributions have been processed</p>
            <a href="{{ route('outlet-warehouse.show', $warehouse->id) }}" class="btn btn-primary">
                <i class='bx bx-arrow-back me-1'></i>Back to Dashboard
            </a>
        </div>
    </div>
@endforelse

<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('outlet-warehouse.reject-distribution') }}" method="POST" id="rejectForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class='bx bx-error-circle text-danger me-2'></i>Reject Distribution
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class='bx bx-info-circle me-2'></i>
                        {{-- UBAH: Teks ke branch --}}
                        <strong>Warning:</strong> This action will reject the entire distribution. 
                        Stock will be returned to branch warehouse.
                    </div>
                    
                    <input type="hidden" id="reject_reference_no" name="reference_no">
                    
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <select class="form-select" name="rejection_reason" required>
                            <option value="">-- Select Reason --</option>
                            <option value="Already have enough stock">Already have enough stock</option>
                            <option value="Not needed at this time">Not needed at this time</option>
                            <option value="Wrong items">Wrong items</option>
                            <option value="Storage capacity full">Storage capacity full</option>
                            <option value="Budget constraints">Budget constraints</option>
                            <option value="Other">Other (Specify below)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" 
                                  name="rejection_notes" 
                                  rows="3" 
                                  placeholder="Please provide detailed reason for rejection..."
                                  maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class='bx bx-x me-1'></i>Confirm Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffab00 0%, #ff6f00 100%);
    }
    
    .distribution-card {
        transition: all 0.3s ease;
    }
    
    .distribution-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .approved-quantity:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }
    
    .item-checkbox:checked ~ td {
        background-color: rgba(105, 108, 255, 0.05);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle details
    document.querySelectorAll('.toggle-details').forEach(btn => {
        btn.addEventListener('click', function() {
            const reference = this.dataset.reference;
            const details = document.getElementById(`details-${reference}`);
            const icon = this.querySelector('i');
            
            if (details.style.display === 'none') {
                details.style.display = 'block';
                icon.classList.remove('bx-chevron-down');
                icon.classList.add('bx-chevron-up');
                this.innerHTML = '<i class="bx bx-chevron-up"></i> Hide Details';
            } else {
                details.style.display = 'none';
                icon.classList.remove('bx-chevron-up');
                icon.classList.add('bx-chevron-down');
                this.innerHTML = '<i class="bx bx-chevron-down"></i> Show Details';
            }
        });
    });

    // Select all items checkbox
    document.querySelectorAll('.select-all-items').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const reference = this.dataset.reference;
            const itemCheckboxes = document.querySelectorAll(`.item-checkbox[data-reference="${reference}"]`);
            itemCheckboxes.forEach(cb => cb.checked = this.checked);
        });
    });

    // Update select-all when individual items change
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const reference = this.dataset.reference;
            const allCheckboxes = document.querySelectorAll(`.item-checkbox[data-reference="${reference}"]`);
            const selectAllCheckbox = document.querySelector(`.select-all-items[data-reference="${reference}"]`);
            
            const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(allCheckboxes).some(cb => cb.checked);
            
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            }
        });
    });

    // Validate approved quantity
    document.querySelectorAll('.approved-quantity').forEach(input => {
        input.addEventListener('input', function() {
            const max = parseFloat(this.dataset.max);
            const value = parseFloat(this.value);
            
            if (value > max) {
                this.value = max;
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: `Quantity cannot exceed ${max}`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
            
            if (value < 0) {
                this.value = 0;
            }
        });
    });

    // Form submission confirmation
    document.querySelectorAll('.distribution-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default submission
            
            const action = e.submitter.value;
            const actionName = e.submitter.name;
            
            // ADD: Create hidden input for 'type' field
            let typeInput = form.querySelector('input[name="type"]');
            if (!typeInput) {
                typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'type';
                form.appendChild(typeInput);
            }
            
            if (action === 'approve_all') {
                // Set type to 'all'
                typeInput.value = 'all';
                
                Swal.fire({
                    title: 'Approve All Items?',
                    text: 'All items in this distribution will be received into your stock',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve All',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Check all checkboxes before submit
                        form.querySelectorAll('.item-checkbox').forEach(cb => {
                            cb.checked = true;
                        });
                        form.submit();
                    }
                });
            } else if (action === 'approve_selected') {
                // Set type to 'selected'
                typeInput.value = 'selected';
                
                const checkedItems = form.querySelectorAll('.item-checkbox:checked').length;
                
                if (checkedItems === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: 'Please select at least one item'
                    });
                    return;
                }
                
                Swal.fire({
                    title: 'Approve Selected Items?',
                    text: `${checkedItems} item(s) will be received into your stock`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#696cff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    });

    // Reject Distribution
    document.querySelectorAll('.btn-reject').forEach(btn => {
        btn.addEventListener('click', function() {
            const reference = this.dataset.reference;
            document.getElementById('reject_reference_no').value = reference;
            
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        });
    });
});
</script>
@endpush
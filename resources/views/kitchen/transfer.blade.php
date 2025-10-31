@extends('layouts.admin')

@section('title', 'Transfer ke Dapur - Chicking BJM')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">📦 Transfer ke Dapur</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('kitchen.index') }}">Stock Dapur</a></li>
                    <li class="breadcrumb-item active">Transfer</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Form Transfer ke Dapur</h4>
                <p class="card-title-desc">Pilih item dari gudang untuk ditransfer ke dapur</p>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('kitchen.transfer.process') }}" method="POST" id="transferForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Item <span class="text-danger">*</span></label>
                                <select class="form-select @error('item_id') is-invalid @enderror" 
                                        name="item_id" id="item_select" required>
                                    <option value="">Pilih Item...</option>
                                    @foreach($warehouseItems as $item)
                                        @php
                                            $warehouseStock = $item->currentBalance?->closing_stock ?? 0;
                                            $kitchenStock = $item->current_kitchen_stock;
                                        @endphp
                                        <option value="{{ $item->id }}" 
                                                data-stock="{{ $warehouseStock }}"
                                                data-kitchen-stock="{{ $kitchenStock }}"
                                                data-unit="{{ $item->unit }}"
                                                data-name="{{ $item->item_name }}"
                                                {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->item_name }} ({{ $item->sku }}) - Stock: {{ number_format($warehouseStock, 1) }} {{ $item->unit }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                           name="quantity" id="quantity_input" step="0.1" min="0.1" 
                                           value="{{ old('quantity') }}" required disabled>
                                    <span class="input-group-text" id="unit_display">Unit</span>
                                </div>
                                <div class="form-text">
                                    <span id="stock_info" class="text-muted">Pilih item terlebih dahulu</span>
                                </div>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  name="notes" rows="3" 
                                  placeholder="Keterangan transfer (opsional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('kitchen.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary" id="submit_btn" disabled>
                            <i class="bx bx-transfer me-1"></i> Transfer ke Dapur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Stock Info Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">ℹ️ Informasi Stock</h5>
            </div>
            <div class="card-body">
                <div id="selected_item_info" style="display: none;">
                    <div class="mb-3">
                        <label class="text-muted">Item:</label>
                        <div id="item_name" class="fw-bold">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted">Stock Gudang:</label>
                        <div id="warehouse_stock" class="fw-bold text-primary">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted">Stock Dapur Saat Ini:</label>
                        <div id="kitchen_stock" class="fw-bold text-success">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted">Akan Ditransfer:</label>
                        <div id="transfer_amount" class="fw-bold text-warning">-</div>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <label class="text-muted">Estimasi Stock Dapur Setelah Transfer:</label>
                        <div id="estimated_kitchen_stock" class="fw-bold text-info">-</div>
                    </div>
                </div>
                <div id="no_item_selected">
                    <div class="text-center text-muted">
                        <i class="bx bx-package font-size-48"></i>
                        <p class="mt-2">Pilih item untuk melihat informasi stock</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Transfer Suggestions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">⚡ Saran Transfer Cepat</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2" id="quick_transfer_buttons" style="display: none;">
                    <button type="button" class="btn btn-outline-primary btn-sm quick-transfer" data-percent="10">
                        10% dari Stock Gudang
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm quick-transfer" data-percent="25">
                        25% dari Stock Gudang
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm quick-transfer" data-percent="50">
                        50% dari Stock Gudang
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm quick-transfer" data-amount="10">
                        10 Unit
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm quick-transfer" data-amount="25">
                        25 Unit
                    </button>
                </div>
                <div id="no_quick_suggestions">
                    <div class="text-center text-muted">
                        <i class="bx bx-bulb font-size-24"></i>
                        <p class="mb-0">Pilih item untuk saran transfer</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Items Search -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">📋 Cari Item Tersedia</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="search_items" 
                           placeholder="Cari nama item atau SKU...">
                </div>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm">
                        <tbody id="items_list">
                            @foreach($warehouseItems->take(10) as $item)
                                @php
                                    $warehouseStock = $item->currentBalance?->closing_stock ?? 0;
                                    $kitchenStock = $item->current_kitchen_stock;
                                @endphp
                                <tr class="item-row" 
                                    data-name="{{ strtolower($item->item_name . ' ' . $item->sku) }}"
                                    data-item-id="{{ $item->id }}"
                                    style="cursor: pointer;">
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $item->item_name }}</strong><br>
                                                <small class="text-muted">{{ $item->sku }}</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="text-primary fw-bold">
                                                    {{ number_format($warehouseStock, 1) }}
                                                </div>
                                                <small class="text-muted">{{ $item->unit }}</small>
                                                @if($kitchenStock > 0)
                                                    <br><small class="text-success">Dapur: {{ number_format($kitchenStock, 1) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($warehouseItems->count() > 10)
                        <div class="text-center text-muted mt-2">
                            <small>Menampilkan 10 dari {{ $warehouseItems->count() }} item tersedia</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const itemSelect = $('#item_select');
    const quantityInput = $('#quantity_input');
    const unitDisplay = $('#unit_display');
    const stockInfo = $('#stock_info');
    const submitBtn = $('#submit_btn');
    const selectedItemInfo = $('#selected_item_info');
    const noItemSelected = $('#no_item_selected');
    const quickTransferButtons = $('#quick_transfer_buttons');
    const noQuickSuggestions = $('#no_quick_suggestions');

    let currentItemData = null;

    // Handle item selection
    itemSelect.change(function() {
        const selectedOption = $(this).find('option:selected');
        const itemId = selectedOption.val();
        
        if (itemId) {
            currentItemData = {
                id: itemId,
                stock: parseFloat(selectedOption.data('stock')),
                kitchenStock: parseFloat(selectedOption.data('kitchen-stock')),
                unit: selectedOption.data('unit'),
                name: selectedOption.data('name')
            };
            
            updateUI();
            enableQuantityInput();
            
        } else {
            currentItemData = null;
            resetUI();
        }
        
        updateTransferInfo();
    });

    // Handle quantity input
    quantityInput.on('input', function() {
        updateTransferInfo();
        validateForm();
    });

    // Quick transfer buttons
    $(document).on('click', '.quick-transfer', function() {
        if (!currentItemData) return;
        
        let quantity = 0;
        const percent = $(this).data('percent');
        const amount = $(this).data('amount');
        
        if (percent) {
            quantity = (currentItemData.stock * percent / 100);
        } else if (amount) {
            quantity = Math.min(amount, currentItemData.stock);
        }
        
        if (quantity > 0) {
            quantityInput.val(quantity.toFixed(1));
            updateTransferInfo();
            validateForm();
        }
    });

    // Search items
    $('#search_items').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.item-row').each(function() {
            const itemName = $(this).data('name');
            if (itemName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Click item row to select
    $(document).on('click', '.item-row', function() {
        const itemId = $(this).data('item-id');
        itemSelect.val(itemId).trigger('change');
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#transferForm').offset().top - 100
        }, 500);
    });

    function updateUI() {
        if (!currentItemData) return;
        
        // Update UI elements
        unitDisplay.text(currentItemData.unit);
        stockInfo.html(`Stock tersedia: <strong class="text-primary">${currentItemData.stock.toLocaleString()} ${currentItemData.unit}</strong>`);
        
        // Update info card
        $('#item_name').text(currentItemData.name);
        $('#warehouse_stock').text(`${currentItemData.stock.toLocaleString()} ${currentItemData.unit}`);
        $('#kitchen_stock').text(`${currentItemData.kitchenStock.toLocaleString()} ${currentItemData.unit}`);
        
        selectedItemInfo.show();
        noItemSelected.hide();
        quickTransferButtons.show();
        noQuickSuggestions.hide();
    }

    function resetUI() {
        unitDisplay.text('Unit');
        stockInfo.text('Pilih item terlebih dahulu');
        selectedItemInfo.hide();
        noItemSelected.show();
        quickTransferButtons.hide();
        noQuickSuggestions.show();
        quantityInput.prop('disabled', true);
        submitBtn.prop('disabled', true);
    }

    function enableQuantityInput() {
        if (!currentItemData) return;
        
        quantityInput.attr('max', currentItemData.stock);
        quantityInput.prop('disabled', false);
    }

    function updateTransferInfo() {
        if (!currentItemData) return;
        
        const quantity = parseFloat(quantityInput.val()) || 0;
        
        if (quantity > 0) {
            $('#transfer_amount').text(`${quantity.toLocaleString()} ${currentItemData.unit}`);
            
            const estimatedKitchenStock = currentItemData.kitchenStock + quantity;
            $('#estimated_kitchen_stock').text(`${estimatedKitchenStock.toLocaleString()} ${currentItemData.unit}`);
        } else {
            $('#transfer_amount').text('-');
            $('#estimated_kitchen_stock').text('-');
        }
    }

    function validateForm() {
        if (!currentItemData) {
            submitBtn.prop('disabled', true);
            return;
        }
        
        const quantity = parseFloat(quantityInput.val()) || 0;
        const isValid = quantity > 0 && quantity <= currentItemData.stock;
        
        submitBtn.prop('disabled', !isValid);
        
        // Show warning if quantity exceeds stock
        if (quantity > currentItemData.stock && currentItemData.stock > 0) {
            stockInfo.html(`<span class="text-danger">Quantity melebihi stock tersedia (${currentItemData.stock.toLocaleString()})</span>`);
        } else if (quantity > 0) {
            stockInfo.html(`Stock tersedia: <strong class="text-primary">${currentItemData.stock.toLocaleString()} ${currentItemData.unit}</strong>`);
        }
    }

    // Initialize with URL parameter
    @if(request('item_id'))
        setTimeout(function() {
            itemSelect.trigger('change');
        }, 100);
    @endif

    // Form submission confirmation
    $('#transferForm').submit(function(e) {
        if (!currentItemData) {
            e.preventDefault();
            return false;
        }
        
        const quantity = parseFloat(quantityInput.val()) || 0;
        
        if (quantity <= 0) {
            e.preventDefault();
            alert('Quantity harus lebih dari 0');
            return false;
        }
        
        if (quantity > currentItemData.stock) {
            e.preventDefault();
            alert('Quantity melebihi stock yang tersedia');
            return false;
        }

        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Processing...');
    });

    // Auto-focus on quantity input when item selected
    itemSelect.change(function() {
        if ($(this).val()) {
            setTimeout(function() {
                quantityInput.focus();
            }, 100);
        }
    });
});
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Transfer Berhasil!',
        text: '{{ session("success") }}',
        confirmButtonText: 'OK'
    });
</script>
@endif
@endpush

@push('styles')
<style>
.item-row:hover {
    background-color: #f8f9fa;
}

.quick-transfer {
    transition: all 0.2s ease;
}

.quick-transfer:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#selected_item_info {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    padding: 15px;
    margin: -15px;
    margin-bottom: 0;
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
    transform: translateY(-1px);
}
</style>
@endpush
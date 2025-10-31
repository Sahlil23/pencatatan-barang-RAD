
@extends('layouts.admin')

@section('title', 'Penggunaan Stock Dapur - Chicking BJM')

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
          <a href="{{ route('kitchen.index') }}">Items Dapur</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Penggunaan</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <ul class="nav nav-tabs" role="tablist" id="usageTabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="single-usage-tab" data-bs-toggle="tab" href="#single-usage" role="tab">
                            <i class="bx bx-minus me-1"></i>Single Usage
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="multiple-usage-tab" data-bs-toggle="tab" href="#multiple-usage" role="tab">
                            <i class="bx bx-list-minus me-1"></i>Multiple Usage
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="tab-content">
                <!-- Single Usage Tab -->
                <div class="tab-pane fade show active" id="single-usage" role="tabpanel">
                    <div class="row">
                        <!-- Main Form -->
                        <div class="col-xl-8">
                            <div class="card-body">
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <strong>Error!</strong> {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <form action="{{ route('kitchen.usage.process') }}" method="POST" id="usageForm">
                                    @csrf
                                    
                                    <!-- Item Selection -->
                                    <div class="mb-4">
                                        <label class="form-label" for="item_search">Pilih Item <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="bx bx-package"></i></span>
                                            <input 
                                                type="text" 
                                                class="form-control @error('item_id') is-invalid @enderror"
                                                id="item_search"
                                                placeholder="Ketik untuk mencari item..."
                                                autocomplete="off"
                                                list="items_list"
                                            >
                                            <input type="hidden" name="item_id" id="item_id" required>
                                            <datalist id="items_list">
                                                @foreach($kitchenItems as $item)
                                                    @php
                                                        $kitchenStock = $item->current_kitchen_stock;
                                                    @endphp
                                                    <option value="{{ $item->item_name }} ({{ $item->sku }})" 
                                                            data-id="{{ $item->id }}"
                                                            data-name="{{ $item->item_name }}"
                                                            data-sku="{{ $item->sku }}"
                                                            data-unit="{{ $item->unit }}"
                                                            data-stock="{{ $kitchenStock }}"
                                                            data-category="{{ $item->category?->category_name ?? 'Tidak ada' }}"
                                                            data-status-color="{{ $item->kitchen_stock_status_color }}">
                                                        {{ $item->item_name }} ({{ $item->sku }}) - Stock: {{ number_format($kitchenStock, 1) }} {{ $item->unit }}
                                                    </option>
                                                @endforeach
                                            </datalist>
                                        </div>
                                        @error('item_id')
                                            <div class="form-text text-danger">{{ $message }}</div>
                                        @else
                                            <div class="form-text">Ketik untuk mencari item berdasarkan nama atau SKU</div>
                                        @enderror
                                    </div>

                                    <!-- Selected Item Info -->
                                    <div id="itemInfo" class="card mb-4" style="display: none;">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar flex-shrink-0 me-3">
                                                            <span class="avatar-initial rounded bg-label-primary" id="itemAvatar">
                                                                <i class="bx bx-package"></i>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1" id="itemName">Nama Item</h6>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <span class="badge bg-label-dark" id="itemSku">SKU</span>
                                                                <span class="text-muted">
                                                                    <i class="bx bx-cube me-1"></i>
                                                                    <span id="itemUnit">Unit</span>
                                                                </span>
                                                                <span class="text-muted">
                                                                    <i class="bx bx-category me-1"></i>
                                                                    <span id="itemCategory">Kategori</span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-end">
                                                        <div class="d-flex flex-column align-items-end">
                                                            <span class="text-muted small">Stock Dapur Saat Ini</span>
                                                            <h5 class="mb-1 text-primary" id="currentStock">0</h5>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge" id="stockStatus">Status</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Quantity and Date Row -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label" for="quantity">
                                                Quantity Digunakan <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text"><i class="bx bx-minus"></i></span>
                                                <input
                                                    type="number"
                                                    class="form-control @error('quantity') is-invalid @enderror"
                                                    id="quantity"
                                                    name="quantity"
                                                    placeholder="0"
                                                    value="{{ old('quantity') }}"
                                                    step="0.1"
                                                    min="0.1"
                                                    required
                                                    disabled
                                                />
                                                <span class="input-group-text" id="quantityUnit">unit</span>
                                            </div>
                                            @error('quantity')
                                                <div class="form-text text-danger">{{ $message }}</div>
                                            @else
                                                <div class="form-text" id="quantityHelp">Masukkan jumlah yang akan digunakan</div>
                                            @enderror
                                            
                                            <!-- Stock Warning -->
                                            <div id="stockWarning" class="alert alert-warning mt-2" style="display: none;">
                                                <i class="bx bx-error-circle me-2"></i>
                                                <span id="warningText"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="usage_date">Tanggal Penggunaan</label>
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                                <input
                                                    type="datetime-local"
                                                    class="form-control @error('usage_date') is-invalid @enderror"
                                                    id="usage_date"
                                                    name="usage_date"
                                                    value="{{ old('usage_date', now()->format('Y-m-d\TH:i')) }}"
                                                />
                                            </div>
                                            @error('usage_date')
                                                <div class="form-text text-danger">{{ $message }}</div>
                                            @else
                                                <div class="form-text">Kosongkan untuk menggunakan waktu saat ini</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="mb-4">
                                        <label class="form-label" for="notes">Keterangan Penggunaan <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="bx bx-note"></i></span>
                                            <textarea
                                                class="form-control @error('notes') is-invalid @enderror"
                                                id="notes"
                                                name="notes"
                                                rows="3"
                                                placeholder="Contoh: Untuk masak ayam crispy 20 porsi, Testing resep baru, dll"
                                                required
                                            >{{ old('notes') }}</textarea>
                                        </div>
                                        @error('notes')
                                            <div class="form-text text-danger">{{ $message }}</div>
                                        @else
                                            <div class="form-text">Jelaskan untuk apa item ini digunakan (maksimal 255 karakter)</div>
                                        @enderror
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('kitchen.index') }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i>
                                            Kembali
                                        </a>
                                        <div>
                                            <button type="reset" class="btn btn-outline-warning me-2">
                                                <i class="bx bx-reset me-1"></i>
                                                Reset
                                            </button>
                                            <button type="submit" class="btn btn-danger" id="submitBtn" disabled>
                                                <i class="bx bx-minus me-1"></i>
                                                Catat Penggunaan
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Sidebar untuk single usage -->
                        <div class="col-xl-4">
                            <!-- Usage Preview -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-show me-2"></i>
                                        Preview Penggunaan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="usagePreview">
                                        <div class="text-center text-muted py-3">
                                            <i class="bx bx-info-circle" style="font-size: 32px;"></i>
                                            <p class="mt-2 mb-0">Pilih item untuk melihat preview</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Usage Templates -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-flash me-2"></i>
                                        Template Penggunaan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm usage-template" 
                                                data-notes="Untuk produksi menu harian - {{ date('d/m/Y') }}">
                                            🍽️ Produksi Menu Harian
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm usage-template" 
                                                data-notes="Testing resep baru - {{ date('d/m/Y') }}">
                                            🧪 Testing Resep Baru
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm usage-template" 
                                                data-notes="Untuk catering/pesanan khusus - {{ date('d/m/Y') }}">
                                            🎂 Catering/Pesanan Khusus
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm usage-template" 
                                                data-notes="Training staff dapur - {{ date('d/m/Y') }}">
                                            👨‍🍳 Training Staff
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm usage-template" 
                                                data-notes="Item rusak/expired - {{ date('d/m/Y') }}">
                                            🗑️ Item Rusak/Expired
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm usage-template" 
                                                data-notes="Sampling untuk quality control - {{ date('d/m/Y') }}">
                                            ✅ Quality Control
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Kitchen Usage -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-history me-2"></i>
                                        Penggunaan Terbaru
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @php 
                                        $recentUsage = \App\Models\KitchenStockTransaction::with(['item', 'user'])
                                                      ->where('transaction_type', 'USAGE')
                                                      ->latest()->take(5)->get();
                                    @endphp
                                    
                                    @if($recentUsage->count() > 0)
                                    <div class="list-group list-group-flush">
                                        @foreach($recentUsage as $usage)
                                        <div class="list-group-item px-0 py-2 border-0">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-danger me-2">USE</span>
                                                    <div>
                                                        <small class="fw-semibold">{{ Str::limit($usage->item->item_name, 15) }}</small>
                                                        <br><small class="text-muted">{{ $usage->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <small class="fw-bold text-danger">
                                                        -{{ number_format($usage->quantity, 1) }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <p class="text-muted mb-0">Belum ada penggunaan</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Multiple Usage Tab -->
                <div class="tab-pane fade" id="multiple-usage" role="tabpanel">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Multiple Usage:</strong> Input beberapa penggunaan sekaligus untuk efisiensi.
                            Setiap baris akan divalidasi secara terpisah.
                        </div>
                        
                        <form action="{{ route('kitchen.usage.process-multiple') }}" method="POST" id="multipleUsageForm">
                            @csrf
                            
                            <!-- Usage Rows Container -->
                            <div id="usageRows">
                                <!-- Initial row will be added by JavaScript -->
                            </div>
                            
                            <!-- Add Row Button -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <button type="button" class="btn btn-outline-primary" onclick="addUsageRow()">
                                    <i class="bx bx-plus me-1"></i>Tambah Baris
                                </button>
                                <small class="text-muted">
                                    <span id="usageRowCount">0</span> penggunaan akan diproses
                                </small>
                            </div>
                            
                            <!-- Bulk Actions -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="clearAllUsageRows()">
                                        <i class="bx bx-trash me-1"></i>Hapus Semua
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm ms-2" onclick="duplicateLastUsageRow()">
                                        <i class="bx bx-copy me-1"></i>Duplikat Baris Terakhir
                                    </button>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="validateAllUsageRows()">
                                        <i class="bx bx-check-circle me-1"></i>Validasi Semua
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Submit Buttons -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('kitchen.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-danger" id="submitMultipleUsageBtn" disabled>
                                    <i class="bx bx-minus me-1"></i>
                                    Catat <span id="totalUsage">0</span> Penggunaan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template untuk Usage Row -->
<script type="text/template" id="usageRowTemplate">
    <div class="usage-row border rounded p-3 mb-3" data-row-id="{rowId}">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Item <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control item-search" 
                    placeholder="Cari item..."
                    data-row="{rowId}"
                    autocomplete="off"
                    list="usage_items_list_{rowId}"
                >
                <input type="hidden" name="usages[{rowId}][item_id]" class="item-id" data-row="{rowId}">
                <datalist id="usage_items_list_{rowId}">
                    @foreach($kitchenItems as $item)
                        @php
                            $kitchenStock = $item->current_kitchen_stock;
                        @endphp
                        <option value="{{ $item->item_name }} ({{ $item->sku }})" 
                                data-id="{{ $item->id }}"
                                data-name="{{ $item->item_name }}"
                                data-sku="{{ $item->sku }}"
                                data-unit="{{ $item->unit }}"
                                data-stock="{{ $kitchenStock }}"
                                data-category="{{ $item->category?->category_name ?? 'Tidak ada' }}"
                                data-status-color="{{ $item->kitchen_stock_status_color }}">
                            {{ $item->item_name }} ({{ $item->sku }}) - Stock: {{ number_format($kitchenStock, 1) }} {{ $item->unit }}
                        </option>
                    @endforeach
                </datalist>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                <input 
                    type="number" 
                    class="form-control quantity" 
                    name="usages[{rowId}][quantity]" 
                    placeholder="0" 
                    step="0.1" 
                    min="0.1" 
                    data-row="{rowId}"
                    required
                >
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Tanggal</label>
                <input 
                    type="datetime-local" 
                    class="form-control" 
                    name="usages[{rowId}][usage_date]" 
                    value="{{ now()->format('Y-m-d\TH:i') }}"
                >
            </div>
            
            <div class="col-md-4">
                <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control notes" 
                    name="usages[{rowId}][notes]" 
                    placeholder="Keterangan penggunaan..."
                    data-row="{rowId}"
                    required
                >
            </div>
            
            <div class="col-md-1">
                <label class="form-label">Aksi</label>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeUsageRow({rowId})" title="Hapus">
                        <i class="bx bx-trash"></i>
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="duplicateUsageRow({rowId})" title="Duplikat">
                        <i class="bx bx-copy"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Row Info & Validation -->
        <div class="row mt-2">
            <div class="col-12">
                <div class="row-info d-none">
                    <small class="text-muted">
                        <span class="current-stock">Stock: -</span> | 
                        <span class="unit">Unit: -</span> | 
                        <span class="category">Kategori: -</span>
                    </small>
                </div>
                <div class="row-validation text-danger d-none">
                    <small><i class="bx bx-error-circle me-1"></i><span class="error-text"></span></small>
                </div>
            </div>
        </div>
    </div>
</script>
@endsection

@push('scripts')
<script>
// Single usage JavaScript (existing code)
document.getElementById('item_search').addEventListener('input', function() {
    const input = this.value;
    const datalist = document.getElementById('items_list');
    const hiddenInput = document.getElementById('item_id');
    
    // Find matching option
    const option = [...datalist.options].find(opt => opt.value === input);
    
    if (option) {
        hiddenInput.value = option.dataset.id;
        // Trigger update functions
        updateItemInfo();
        updateUsagePreview();
    } else {
        hiddenInput.value = '';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('item_id');
    const quantityInput = document.getElementById('quantity');
    
    // Add initial row when multiple tab is shown
    document.getElementById('multiple-usage-tab')?.addEventListener('shown.bs.tab', function() {
        if (document.querySelectorAll('.usage-row').length === 0) {
            addUsageRow();
        }
    });

    // Single form functions...
    function updateItemInfo() {
        const input = document.getElementById('item_search').value;
        const datalist = document.getElementById('items_list');
        const hiddenInput = document.getElementById('item_id');
        const itemInfo = document.getElementById('itemInfo');
        
        const option = [...datalist.options].find(opt => opt.value === input);
        
        if (option && hiddenInput.value) {
            const data = option.dataset;
            
            document.getElementById('itemName').textContent = data.name;
            document.getElementById('itemSku').textContent = data.sku;
            document.getElementById('itemUnit').textContent = data.unit;
            document.getElementById('itemCategory').textContent = data.category;
            document.getElementById('currentStock').textContent = parseFloat(data.stock).toLocaleString('id-ID', {minimumFractionDigits: 1});
            document.getElementById('quantityUnit').textContent = data.unit;
            
            // Update avatar color
            const avatar = document.getElementById('itemAvatar');
            avatar.className = `avatar-initial rounded bg-label-${data.statusColor}`;
            
            // Update stock status
            const statusBadge = document.getElementById('stockStatus');
            const currentStock = parseFloat(data.stock);
            
            if (currentStock <= 0) {
                statusBadge.className = 'badge bg-danger';
                statusBadge.textContent = 'Habis';
            } else if (currentStock <= 5) {
                statusBadge.className = 'badge bg-warning';
                statusBadge.textContent = 'Menipis';
            } else {
                statusBadge.className = 'badge bg-success';
                statusBadge.textContent = 'Tersedia';
            }
            
            itemInfo.style.display = 'block';
            quantityInput.disabled = false;
        } else {
            itemInfo.style.display = 'none';
            quantityInput.disabled = true;
        }
    }

    function validateQuantity() {
        const input = document.getElementById('item_search').value;
        const datalist = document.getElementById('items_list');
        const hiddenInput = document.getElementById('item_id');
        const stockWarning = document.getElementById('stockWarning');
        const warningText = document.getElementById('warningText');
        const submitBtn = document.getElementById('submitBtn');
        
        if (!hiddenInput.value || !quantityInput.value) {
            stockWarning.style.display = 'none';
            return;
        }
        
        const option = [...datalist.options].find(opt => opt.value === input);
        if (!option) return;
        
        const currentStock = parseFloat(option.dataset.stock);
        const quantity = parseFloat(quantityInput.value);
        
        if (quantity > currentStock) {
            stockWarning.style.display = 'block';
            stockWarning.className = 'alert alert-danger mt-2';
            warningText.textContent = `Stock tidak mencukupi! Stock saat ini: ${currentStock.toLocaleString('id-ID')}`;
            submitBtn.disabled = true;
        } else if (quantity === currentStock) {
            stockWarning.style.display = 'block';
            stockWarning.className = 'alert alert-warning mt-2';
            warningText.textContent = 'Penggunaan ini akan menghabiskan seluruh stock item!';
            submitBtn.disabled = false;
        } else if (quantity > (currentStock * 0.8)) {
            stockWarning.style.display = 'block';
            stockWarning.className = 'alert alert-info mt-2';
            warningText.textContent = 'Penggunaan ini akan mengurangi stock secara signifikan.';
            submitBtn.disabled = false;
        } else {
            stockWarning.style.display = 'none';
            submitBtn.disabled = false;
        }
    }

    function updateUsagePreview() {
        const input = document.getElementById('item_search').value;
        const datalist = document.getElementById('items_list');
        const hiddenInput = document.getElementById('item_id');
        const quantity = quantityInput.value;
        const notes = document.getElementById('notes').value;
        const preview = document.getElementById('usagePreview');
        
        if (!hiddenInput.value) {
            preview.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="bx bx-info-circle" style="font-size: 32px;"></i>
                    <p class="mt-2 mb-0">Pilih item untuk melihat preview</p>
                </div>
            `;
            return;
        }
        
        const option = [...datalist.options].find(opt => opt.value === input);
        if (!option) return;
        
        const data = option.dataset;
        const currentStock = parseFloat(data.stock);
        let newStock = currentStock;
        
        if (quantity) {
            const qty = parseFloat(quantity);
            newStock = currentStock - qty;
        }
        
        preview.innerHTML = `
            <div class="d-flex align-items-center mb-3">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-${data.statusColor}">
                        <i class="bx bx-package"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0">${data.name}</h6>
                    <small class="text-muted">${data.sku}</small>
                </div>
            </div>
            
            ${quantity ? `
            <div class="border rounded p-3 mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-danger">
                        <i class="bx bx-minus me-1"></i>
                        Penggunaan
                    </span>
                    <span class="fw-bold text-danger">
                        -${parseFloat(quantity).toLocaleString('id-ID')} ${data.unit}
                    </span>
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <small class="text-muted d-block">Stock Saat Ini</small>
                        <span class="fw-bold">${currentStock.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Stock Setelah</small>
                        <span class="fw-bold text-${newStock < 0 ? 'danger' : (newStock <= 5 ? 'warning' : 'success')}">${newStock.toLocaleString('id-ID')}</span>
                    </div>
                </div>
                
                ${newStock < 0 ? '<div class="alert alert-danger mt-2 p-2"><small><i class="bx bx-error-circle me-1"></i>Stock akan menjadi negatif!</small></div>' : ''}
                ${newStock === 0 ? '<div class="alert alert-warning mt-2 p-2"><small><i class="bx bx-error-circle me-1"></i>Stock akan habis!</small></div>' : ''}
                ${newStock > 0 && newStock <= 5 ? '<div class="alert alert-info mt-2 p-2"><small><i class="bx bx-info-circle me-1"></i>Stock akan menipis!</small></div>' : ''}
            </div>
            ` : ''}
            
            <div class="border rounded p-3 bg-light">
                <div class="row mb-2">
                    <div class="col-6">
                        <small class="text-muted d-block">Kategori:</small>
                        <small class="fw-semibold">${data.category}</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Unit:</small>
                        <small class="fw-semibold">${data.unit}</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <small class="text-muted d-block">Stock Saat Ini:</small>
                        <small class="fw-semibold text-primary">${currentStock.toLocaleString('id-ID')}</small>
                    </div>
                </div>
            </div>
            
            ${notes ? `
            <div class="border rounded p-2 mt-3 bg-info text-white">
                <small><strong>Keterangan:</strong> ${notes}</small>
            </div>
            ` : ''}
        `;
    }

    // Event listeners for single form
    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            validateQuantity();
            updateUsagePreview();
        });
    }

    // Notes change
    document.getElementById('notes')?.addEventListener('input', function() {
        updateUsagePreview();
    });

    // Usage template buttons
    document.querySelectorAll('.usage-template').forEach(btn => {
        btn.addEventListener('click', function() {
            const notes = this.dataset.notes;
            document.getElementById('notes').value = notes;
            updateUsagePreview();
        });
    });

    // Form validation
    document.getElementById('usageForm')?.addEventListener('submit', function(e) {
        const hiddenInput = document.getElementById('item_id');
        const notes = document.getElementById('notes').value;
        
        if (!hiddenInput.value || !quantityInput.value || !notes.trim()) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi');
            return false;
        }
        
        const input = document.getElementById('item_search').value;
        const datalist = document.getElementById('items_list');
        const option = [...datalist.options].find(opt => opt.value === input);
        
        if (option) {
            const currentStock = parseFloat(option.dataset.stock);
            const quantity = parseFloat(quantityInput.value);
            
            if (quantity > currentStock) {
                e.preventDefault();
                alert('Stock tidak mencukupi untuk penggunaan ini!');
                return false;
            }
        }
    });

    // Reset form
    document.querySelector('button[type="reset"]')?.addEventListener('click', function() {
        setTimeout(() => {
            document.getElementById('itemInfo').style.display = 'none';
            document.getElementById('stockWarning').style.display = 'none';
            updateUsagePreview();
        }, 10);
    });

    // Initialize
    updateItemInfo();
    updateUsagePreview();
});

// Multiple Usage Functions
let usageRowCounter = 0;
const maxUsageRows = 50;

function addUsageRow(data = null) {
    if (document.querySelectorAll('.usage-row').length >= maxUsageRows) {
        alert(`Maksimal ${maxUsageRows} baris penggunaan`);
        return;
    }
    
    usageRowCounter++;
    const rowId = usageRowCounter;
    
    // Get template
    const template = document.getElementById('usageRowTemplate').innerHTML;
    const rowHtml = template.replace(/{rowId}/g, rowId);
    
    // Add to container
    document.getElementById('usageRows').insertAdjacentHTML('beforeend', rowHtml);
    
    // Add event listeners
    setupUsageRowEventListeners(rowId);
    
    updateUsageRowCount();
    updateUsageSubmitButton();
}

function setupUsageRowEventListeners(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    
    // Item search
    row.querySelector('.item-search').addEventListener('input', function() {
        handleUsageItemSearch(rowId);
    });
    
    // Quantity change
    row.querySelector('.quantity').addEventListener('input', function() {
        validateUsageRow(rowId);
    });
    
    // Notes change
    row.querySelector('.notes').addEventListener('input', function() {
        validateUsageRow(rowId);
    });
}

function handleUsageItemSearch(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    const searchInput = row.querySelector('.item-search');
    const hiddenInput = row.querySelector('.item-id');
    const datalist = row.querySelector('datalist');
    
    const input = searchInput.value;
    const option = Array.from(datalist.options).find(opt => opt.value === input);
    
    if (option) {
        hiddenInput.value = option.dataset.id;
        updateUsageRowInfo(rowId);
        validateUsageRow(rowId);
    } else {
        hiddenInput.value = '';
        clearUsageRowInfo(rowId);
    }
}

function updateUsageRowInfo(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    const hiddenInput = row.querySelector('.item-id');
    const datalist = row.querySelector('datalist');
    
    if (hiddenInput.value) {
        const option = Array.from(datalist.options).find(opt => opt.dataset.id == hiddenInput.value);
        if (option) {
            const data = option.dataset;
            row.querySelector('.current-stock').textContent = `Stock: ${parseFloat(data.stock).toLocaleString('id-ID')}`;
            row.querySelector('.unit').textContent = `Unit: ${data.unit}`;
            row.querySelector('.category').textContent = `Kategori: ${data.category}`;
            row.querySelector('.row-info').classList.remove('d-none');
        }
    }
}

function clearUsageRowInfo(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    row.querySelector('.row-info').classList.add('d-none');
}

function validateUsageRow(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    const itemId = row.querySelector('.item-id').value;
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const notes = row.querySelector('.notes').value.trim();
    
    const validationDiv = row.querySelector('.row-validation');
    const errorText = validationDiv.querySelector('.error-text');
    
    let errors = [];
    
    if (!itemId) {
        errors.push('Item harus dipilih');
    }
    
    if (!quantity || quantity <= 0) {
        errors.push('Quantity harus > 0');
    }
    
    if (!notes) {
        errors.push('Keterangan harus diisi');
    }
    
    // Check stock
    if (itemId && quantity > 0) {
        const datalist = row.querySelector('datalist');
        const option = Array.from(datalist.options).find(opt => opt.dataset.id == itemId);
        if (option) {
            const currentStock = parseFloat(option.dataset.stock);
            if (quantity > currentStock) {
                errors.push(`Stock tidak cukup (${currentStock} tersedia)`);
            }
        }
    }
    
    if (errors.length > 0) {
        errorText.textContent = errors.join(', ');
        validationDiv.classList.remove('d-none');
        row.classList.add('border-danger');
    } else {
        validationDiv.classList.add('d-none');
        row.classList.remove('border-danger');
    }
    
    updateUsageSubmitButton();
}

function removeUsageRow(rowId) {
    if (document.querySelectorAll('.usage-row').length <= 1) {
        alert('Minimal harus ada 1 baris penggunaan');
        return;
    }
    
    document.querySelector(`[data-row-id="${rowId}"]`).remove();
    updateUsageRowCount();
    updateUsageSubmitButton();
}

function duplicateUsageRow(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    const data = {
        item_id: row.querySelector('.item-id').value,
        quantity: row.querySelector('.quantity').value,
        usage_date: row.querySelector('input[type="datetime-local"]').value,
        notes: row.querySelector('.notes').value
    };
    
    addUsageRow(data);
}

function duplicateLastUsageRow() {
    const rows = document.querySelectorAll('.usage-row');
    if (rows.length > 0) {
        const lastRow = rows[rows.length - 1];
        const rowId = lastRow.dataset.rowId;
        duplicateUsageRow(rowId);
    }
}

function clearAllUsageRows() {
    if (confirm('Hapus semua baris penggunaan?')) {
        document.getElementById('usageRows').innerHTML = '';
        usageRowCounter = 0;
        addUsageRow();
    }
}

function validateAllUsageRows() {
    const rows = document.querySelectorAll('.usage-row');
    rows.forEach(row => {
        const rowId = row.dataset.rowId;
        validateUsageRow(rowId);
    });
    
    const invalidRows = document.querySelectorAll('.usage-row.border-danger');
    if (invalidRows.length > 0) {
        alert(`${invalidRows.length} baris memiliki error. Perbaiki sebelum submit.`);
    } else {
        alert('Semua baris valid! Siap untuk disimpan.');
    }
}

function updateUsageRowCount() {
    const count = document.querySelectorAll('.usage-row').length;
    const rowCountEl = document.getElementById('usageRowCount');
    const totalUsageEl = document.getElementById('totalUsage');
    
    if (rowCountEl) rowCountEl.textContent = count;
    if (totalUsageEl) totalUsageEl.textContent = count;
}

function updateUsageSubmitButton() {
    const rows = document.querySelectorAll('.usage-row');
    const invalidRows = document.querySelectorAll('.usage-row.border-danger');
    const submitBtn = document.getElementById('submitMultipleUsageBtn');
    
    if (!submitBtn) return;
    
    const hasValidRows = rows.length > 0 && invalidRows.length === 0;
    submitBtn.disabled = !hasValidRows;
    
    if (hasValidRows) {
        submitBtn.classList.remove('btn-secondary');
        submitBtn.classList.add('btn-danger');
    } else {
        submitBtn.classList.remove('btn-danger');
        submitBtn.classList.add('btn-secondary');
    }
}

// Form submit handler
document.getElementById('multipleUsageForm')?.addEventListener('submit', function(e) {
    const invalidRows = document.querySelectorAll('.usage-row.border-danger');
    if (invalidRows.length > 0) {
        e.preventDefault();
        alert('Ada baris yang belum valid. Perbaiki error terlebih dahulu.');
        return false;
    }
    
    // Show loading
    const submitBtn = document.getElementById('submitMultipleUsageBtn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
    }
});
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Penggunaan Berhasil Dicatat!',
        text: '{{ session("success") }}',
        confirmButtonText: 'OK'
    });
</script>
@endif
@endpush

@push('styles')
<style>
.usage-row:hover {
    background-color: #f8f9fa;
}

.usage-template {
    transition: all 0.2s ease;
}

.usage-template:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    font-size: 18px;
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border: none;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    transform: translateY(-1px);
}

#stockWarning {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
</style>
@endpush
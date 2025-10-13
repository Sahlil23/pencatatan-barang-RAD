@extends('layouts.admin')

@section('title', 'Tambah Transaksi Stok - Chicking BJM')

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
          <a href="{{ route('stock-transactions.index') }}">Transaksi Stok</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Transaksi</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <!-- Main Form -->
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tambah Transaksi Stok</h5>
        <small class="text-muted float-end">Form transaksi stok baru</small>
      </div>
      <div class="card-body">
        <form action="{{ route('stock-transactions.store') }}" method="POST" id="transactionForm">
          @csrf
          
          <!-- Item Selection -->
          <div class="mb-4">
            <label class="form-label" for="item_id">Pilih Item <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-package"></i></span>
              <select class="form-select @error('item_id') is-invalid @enderror" id="item_id" name="item_id" required>
                <option value="">Pilih item untuk transaksi</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}" 
                        data-sku="{{ $item->sku }}"
                        data-name="{{ $item->item_name }}"
                        data-unit="{{ $item->unit }}"
                        data-stock="{{ $item->current_stock }}"
                        data-threshold="{{ $item->low_stock_threshold }}"
                        data-category="{{ $item->category->category_name ?? 'Tidak ada' }}"
                        data-status-color="{{ $item->stock_status_color }}"
                        {{ old('item_id') == $item->id ? 'selected' : '' }}>
                  {{ $item->item_name }} ({{ $item->sku }}) - Stok: {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                </option>
                @endforeach
              </select>
            </div>
            @error('item_id')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Pilih item yang akan ditransaksikan</div>
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
                      <span class="text-muted small">Stok Saat Ini</span>
                      <h5 class="mb-1 text-primary" id="currentStock">0</h5>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge" id="stockStatus">Status</span>
                        <small class="text-muted">dari <span id="minThreshold">0</span></small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Transaction Type -->
          <div class="mb-4">
            <label class="form-label" for="transaction_type">Tipe Transaksi <span class="text-danger">*</span></label>
            <div class="row">
              <div class="col-md-4">
                <div class="form-check card-radio">
                  <input class="form-check-input" type="radio" name="transaction_type" id="type_in" value="IN" {{ old('transaction_type') == 'IN' ? 'checked' : '' }}>
                  <label class="form-check-label card-radio-label" for="type_in">
                    <div class="card">
                      <div class="card-body text-center">
                        <i class="bx bx-plus-circle text-success" style="font-size: 32px;"></i>
                        <h6 class="mt-2 mb-1 text-success">Stok Masuk</h6>
                        <small class="text-muted">Penambahan stok item</small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card-radio">
                  <input class="form-check-input" type="radio" name="transaction_type" id="type_out" value="OUT" {{ old('transaction_type') == 'OUT' ? 'checked' : '' }}>
                  <label class="form-check-label card-radio-label" for="type_out">
                    <div class="card">
                      <div class="card-body text-center">
                        <i class="bx bx-minus-circle text-danger" style="font-size: 32px;"></i>
                        <h6 class="mt-2 mb-1 text-danger">Stok Keluar</h6>
                        <small class="text-muted">Pengurangan stok item</small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card-radio">
                  <input class="form-check-input" type="radio" name="transaction_type" id="type_adjustment" value="ADJUSTMENT" {{ old('transaction_type') == 'ADJUSTMENT' ? 'checked' : '' }}>
                  <label class="form-check-label card-radio-label" for="type_adjustment">
                    <div class="card">
                      <div class="card-body text-center">
                        <i class="bx bx-transfer text-warning" style="font-size: 32px;"></i>
                        <h6 class="mt-2 mb-1 text-warning">Penyesuaian</h6>
                        <small class="text-muted">Koreksi stok manual</small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>
            </div>
            @error('transaction_type')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Pilih tipe transaksi yang sesuai</div>
            @enderror
          </div>

          <!-- Quantity and Date Row -->
          <div class="row mb-4">
            <div class="col-md-6">
              <label class="form-label" for="quantity">
                <span id="quantityLabel">Jumlah</span> <span class="text-danger">*</span>
              </label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-package"></i></span>
                <input
                  type="number"
                  class="form-control @error('quantity') is-invalid @enderror"
                  id="quantity"
                  name="quantity"
                  placeholder="0"
                  value="{{ old('quantity') }}"
                  step="0.01"
                  min="0.01"
                  required
                />
                <span class="input-group-text" id="quantityUnit">unit</span>
              </div>
              @error('quantity')
                <div class="form-text text-danger">{{ $message }}</div>
              @else
                <div class="form-text" id="quantityHelp">Masukkan jumlah yang akan ditransaksikan</div>
              @enderror
              
              <!-- Stock Warning -->
              <div id="stockWarning" class="alert alert-warning mt-2" style="display: none;">
                <i class="bx bx-error-circle me-2"></i>
                <span id="warningText"></span>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="transaction_date">Tanggal Transaksi</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                <input
                  type="datetime-local"
                  class="form-control @error('transaction_date') is-invalid @enderror"
                  id="transaction_date"
                  name="transaction_date"
                  value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}"
                />
              </div>
              @error('transaction_date')
                <div class="form-text text-danger">{{ $message }}</div>
              @else
                <div class="form-text">Kosongkan untuk menggunakan waktu saat ini</div>
              @enderror
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-4">
            <label class="form-label" for="notes">Catatan <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-note"></i></span>
              <textarea
                class="form-control @error('notes') is-invalid @enderror"
                id="notes"
                name="notes"
                rows="3"
                placeholder="Masukkan alasan atau keterangan transaksi..."
                required
              >{{ old('notes') }}</textarea>
            </div>
            @error('notes')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Jelaskan alasan atau keterangan untuk transaksi ini (maksimal 255 karakter)</div>
            @enderror
          </div>

          <!-- Action Buttons -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
            <div>
              <button type="reset" class="btn btn-outline-warning me-2">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
              <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="bx bx-save me-1"></i>
                Simpan Transaksi
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-xl-4">
    <!-- Transaction Preview -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-show me-2"></i>
          Preview Transaksi
        </h6>
      </div>
      <div class="card-body">
        <div id="transactionPreview">
          <div class="text-center text-muted py-3">
            <i class="bx bx-info-circle" style="font-size: 32px;"></i>
            <p class="mt-2 mb-0">Pilih item untuk melihat preview</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-flash me-2"></i>
          Quick Actions
        </h6>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('items.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-plus me-1"></i>
            Tambah Item Baru
          </a>
          <a href="{{ route('items.low-stock') }}" class="btn btn-outline-warning btn-sm">
            <i class="bx bx-error me-1"></i>
            Item Stok Menipis
          </a>
          <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-info btn-sm">
            <i class="bx bx-history me-1"></i>
            Riwayat Transaksi
          </a>
          <a href="{{ route('stock-transactions.report') }}" class="btn btn-outline-success btn-sm">
            <i class="bx bx-bar-chart me-1"></i>
            Laporan Stok
          </a>
        </div>
      </div>
    </div>

    <!-- Transaction Types Info -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Jenis Transaksi
        </h6>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex align-items-center">
              <i class="bx bx-plus-circle text-success me-3" style="font-size: 20px;"></i>
              <div>
                <h6 class="mb-0 text-success">Stok Masuk</h6>
                <small class="text-muted">Pembelian, produksi, atau penambahan stok</small>
              </div>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex align-items-center">
              <i class="bx bx-minus-circle text-danger me-3" style="font-size: 20px;"></i>
              <div>
                <h6 class="mb-0 text-danger">Stok Keluar</h6>
                <small class="text-muted">Penjualan, penggunaan, atau pengurangan stok</small>
              </div>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex align-items-center">
              <i class="bx bx-transfer text-warning me-3" style="font-size: 20px;"></i>
              <div>
                <h6 class="mb-0 text-warning">Penyesuaian</h6>
                <small class="text-muted">Koreksi stok akibat kerusakan, kehilangan, dll</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-history me-2"></i>
          Transaksi Terbaru
        </h6>
      </div>
      <div class="card-body">
        @php 
          $recentTransactions = \App\Models\StockTransaction::with(['item', 'user'])
                                ->latest()->take(5)->get();
        @endphp
        
        @if($recentTransactions->count() > 0)
        <div class="list-group list-group-flush">
          @foreach($recentTransactions as $transaction)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <span class="badge bg-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }} me-2">
                  {{ $transaction->transaction_type == 'IN' ? 'IN' : ($transaction->transaction_type == 'OUT' ? 'OUT' : 'ADJ') }}
                </span>
                <div>
                  <small class="fw-semibold">{{ Str::limit($transaction->item->item_name, 15) }}</small>
                  <br><small class="text-muted">{{ $transaction->created_at->diffForHumans() }}</small>
                </div>
              </div>
              <div class="text-end">
                <small class="fw-bold text-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }}">
                  {{ $transaction->transaction_type == 'OUT' ? '-' : '+' }}{{ number_format($transaction->quantity, 0) }}
                </small>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <p class="text-muted mb-0">Belum ada transaksi</p>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const itemSelect = document.getElementById('item_id');
  const quantityInput = document.getElementById('quantity');
  const typeRadios = document.querySelectorAll('input[name="transaction_type"]');
  
  // Update item info when item is selected
  itemSelect.addEventListener('change', function() {
    updateItemInfo();
    updatePreview();
  });

  // Update preview when quantity or type changes
  quantityInput.addEventListener('input', function() {
    validateQuantity();
    updatePreview();
  });

  typeRadios.forEach(radio => {
    radio.addEventListener('change', function() {
      updateQuantityLabel();
      validateQuantity();
      updatePreview();
    });
  });

  function updateItemInfo() {
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    const itemInfo = document.getElementById('itemInfo');
    
    if (itemSelect.value) {
      const data = selectedOption.dataset;
      
      document.getElementById('itemName').textContent = data.name;
      document.getElementById('itemSku').textContent = data.sku;
      document.getElementById('itemUnit').textContent = data.unit;
      document.getElementById('itemCategory').textContent = data.category;
      document.getElementById('currentStock').textContent = parseFloat(data.stock).toLocaleString('id-ID', {minimumFractionDigits: 2});
      document.getElementById('minThreshold').textContent = parseFloat(data.threshold).toLocaleString('id-ID');
      document.getElementById('quantityUnit').textContent = data.unit;
      
      // Update avatar color
      const avatar = document.getElementById('itemAvatar');
      avatar.className = `avatar-initial rounded bg-label-${data.statusColor}`;
      
      // Update stock status
      const statusBadge = document.getElementById('stockStatus');
      const currentStock = parseFloat(data.stock);
      const threshold = parseFloat(data.threshold);
      
      if (currentStock <= 0) {
        statusBadge.className = 'badge bg-danger';
        statusBadge.textContent = 'Habis';
      } else if (currentStock <= threshold) {
        statusBadge.className = 'badge bg-warning';
        statusBadge.textContent = 'Menipis';
      } else {
        statusBadge.className = 'badge bg-success';
        statusBadge.textContent = 'Aman';
      }
      
      itemInfo.style.display = 'block';
    } else {
      itemInfo.style.display = 'none';
    }
  }

  function updateQuantityLabel() {
    const selectedType = document.querySelector('input[name="transaction_type"]:checked');
    const quantityLabel = document.getElementById('quantityLabel');
    const quantityHelp = document.getElementById('quantityHelp');
    
    if (selectedType) {
      switch (selectedType.value) {
        case 'IN':
          quantityLabel.textContent = 'Jumlah Masuk';
          quantityHelp.textContent = 'Masukkan jumlah stok yang akan ditambahkan';
          break;
        case 'OUT':
          quantityLabel.textContent = 'Jumlah Keluar';
          quantityHelp.textContent = 'Masukkan jumlah stok yang akan dikurangi';
          break;
        case 'ADJUSTMENT':
          quantityLabel.textContent = 'Stok Baru';
          quantityHelp.textContent = 'Masukkan jumlah stok setelah penyesuaian';
          break;
      }
    }
  }

  function validateQuantity() {
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    const selectedType = document.querySelector('input[name="transaction_type"]:checked');
    const stockWarning = document.getElementById('stockWarning');
    const warningText = document.getElementById('warningText');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!itemSelect.value || !selectedType || !quantityInput.value) {
      stockWarning.style.display = 'none';
      return;
    }
    
    const currentStock = parseFloat(selectedOption.dataset.stock);
    const quantity = parseFloat(quantityInput.value);
    
    if (selectedType.value === 'OUT' && quantity > currentStock) {
      stockWarning.style.display = 'block';
      stockWarning.className = 'alert alert-danger mt-2';
      warningText.textContent = `Stok tidak mencukupi! Stok saat ini: ${currentStock.toLocaleString('id-ID')}`;
      submitBtn.disabled = true;
    } else if (selectedType.value === 'OUT' && quantity === currentStock) {
      stockWarning.style.display = 'block';
      stockWarning.className = 'alert alert-warning mt-2';
      warningText.textContent = 'Transaksi ini akan menghabiskan seluruh stok item!';
      submitBtn.disabled = false;
    } else if (selectedType.value === 'OUT' && quantity > (currentStock * 0.8)) {
      stockWarning.style.display = 'block';
      stockWarning.className = 'alert alert-info mt-2';
      warningText.textContent = 'Transaksi ini akan mengurangi stok secara signifikan.';
      submitBtn.disabled = false;
    } else {
      stockWarning.style.display = 'none';
      submitBtn.disabled = false;
    }
  }

  function updatePreview() {
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    const selectedType = document.querySelector('input[name="transaction_type"]:checked');
    const quantity = quantityInput.value;
    const preview = document.getElementById('transactionPreview');
    
    if (!itemSelect.value) {
      preview.innerHTML = `
        <div class="text-center text-muted py-3">
          <i class="bx bx-info-circle" style="font-size: 32px;"></i>
          <p class="mt-2 mb-0">Pilih item untuk melihat preview</p>
        </div>
      `;
      return;
    }
    
    const data = selectedOption.dataset;
    const currentStock = parseFloat(data.stock);
    let newStock = currentStock;
    let typeText = '';
    let typeColor = '';
    let typeIcon = '';
    
    if (selectedType && quantity) {
      const qty = parseFloat(quantity);
      
      switch (selectedType.value) {
        case 'IN':
          newStock = currentStock + qty;
          typeText = 'Stok Masuk';
          typeColor = 'success';
          typeIcon = 'bx-plus-circle';
          break;
        case 'OUT':
          newStock = currentStock - qty;
          typeText = 'Stok Keluar';
          typeColor = 'danger';
          typeIcon = 'bx-minus-circle';
          break;
        case 'ADJUSTMENT':
          newStock = qty;
          typeText = 'Penyesuaian';
          typeColor = 'warning';
          typeIcon = 'bx-transfer';
          break;
      }
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
      
      ${selectedType && quantity ? `
      <div class="border rounded p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="badge bg-${typeColor}">
            <i class="bx ${typeIcon} me-1"></i>
            ${typeText}
          </span>
          <span class="fw-bold text-${typeColor}">
            ${selectedType.value === 'OUT' ? '-' : '+'}${parseFloat(quantity).toLocaleString('id-ID')} ${data.unit}
          </span>
        </div>
        
        <div class="row text-center">
          <div class="col-6">
            <small class="text-muted d-block">Stok Saat Ini</small>
            <span class="fw-bold">${currentStock.toLocaleString('id-ID')}</span>
          </div>
          <div class="col-6">
            <small class="text-muted d-block">Stok Setelah</small>
            <span class="fw-bold text-${newStock < 0 ? 'danger' : (newStock <= parseFloat(data.threshold) ? 'warning' : 'success')}">${newStock.toLocaleString('id-ID')}</span>
          </div>
        </div>
        
        ${newStock < 0 ? '<div class="alert alert-danger mt-2 p-2"><small><i class="bx bx-error-circle me-1"></i>Stok akan menjadi negatif!</small></div>' : ''}
        ${newStock === 0 ? '<div class="alert alert-warning mt-2 p-2"><small><i class="bx bx-error-circle me-1"></i>Stok akan habis!</small></div>' : ''}
        ${newStock > 0 && newStock <= parseFloat(data.threshold) ? '<div class="alert alert-info mt-2 p-2"><small><i class="bx bx-info-circle me-1"></i>Stok akan menipis!</small></div>' : ''}
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
          <div class="col-6">
            <small class="text-muted d-block">Stok Saat Ini:</small>
            <small class="fw-semibold text-primary">${currentStock.toLocaleString('id-ID')}</small>
          </div>
          <div class="col-6">
            <small class="text-muted d-block">Minimum:</small>
            <small class="fw-semibold text-warning">${parseFloat(data.threshold).toLocaleString('id-ID')}</small>
          </div>
        </div>
      </div>
    `;
  }

  // Copy transaction from URL parameter
  const urlParams = new URLSearchParams(window.location.search);
  const copyId = urlParams.get('copy');
  if (copyId) {
    // You can implement AJAX call to get transaction data and prefill form
    console.log('Copy transaction ID:', copyId);
  }

  // Form validation
  document.getElementById('transactionForm').addEventListener('submit', function(e) {
    if (!itemSelect.value || !document.querySelector('input[name="transaction_type"]:checked') || !quantityInput.value) {
      e.preventDefault();
      alert('Mohon lengkapi semua field yang wajib diisi');
      return false;
    }
    
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    const selectedType = document.querySelector('input[name="transaction_type"]:checked');
    const currentStock = parseFloat(selectedOption.dataset.stock);
    const quantity = parseFloat(quantityInput.value);
    
    if (selectedType.value === 'OUT' && quantity > currentStock) {
      e.preventDefault();
      alert('Stok tidak mencukupi untuk transaksi ini!');
      return false;
    }
  });

  // Reset form
  document.querySelector('button[type="reset"]').addEventListener('click', function() {
    setTimeout(() => {
      document.getElementById('itemInfo').style.display = 'none';
      document.getElementById('stockWarning').style.display = 'none';
      updatePreview();
    }, 10);
  });

  // Initialize
  updateItemInfo();
  updateQuantityLabel();
  updatePreview();
});
</script>
@endpush

@push('styles')
<style>
.card-radio {
  position: relative;
}

.card-radio .form-check-input {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 2;
}

.card-radio .card {
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  border: 2px solid transparent;
}

.card-radio .card:hover {
  border-color: rgba(105, 108, 255, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card-radio input[type="radio"]:checked + .card-radio-label .card {
  border-color: #696cff;
  background-color: rgba(105, 108, 255, 0.1);
}

.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  font-size: 18px;
}

.list-group-item {
  transition: background-color 0.15s ease-in-out;
}

.list-group-item:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.form-control:focus, .form-select:focus {
  border-color: #696cff;
  box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

.alert {
  font-size: 0.875rem;
}

#transactionPreview .border {
  background-color: #f8f9fa;
}
</style>
@endpush
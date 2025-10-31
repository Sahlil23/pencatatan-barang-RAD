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
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header">
        <ul class="nav nav-tabs" role="tablist" id="transactionTabs">
          <li class="nav-item">
            <a class="nav-link active" id="single-tab" data-bs-toggle="tab" href="#single" role="tab">
              <i class="bx bx-plus me-1"></i>Single Transaction
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="multiple-tab" data-bs-toggle="tab" href="#multiple" role="tab">
              <i class="bx bx-list-plus me-1"></i>Multiple Transactions
            </a>
          </li>
        </ul>
      </div>
      
      <div class="tab-content">
        <!-- Single Transaction Tab (existing form) -->
        <div class="tab-pane fade show active" id="single" role="tabpanel">
          <div class="row">
            <!-- Main Form -->
            <div class="col-xl-8">
              <div class="card-body">
                <form action="{{ route('stock-transactions.store') }}" method="POST" id="transactionForm">
                  @csrf
                  
                  <!-- Existing single form content tetap sama -->
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
                              @foreach($items as $item)
                              <option value="{{ $item->item_name }} ({{ $item->sku }})" 
                                      data-id="{{ $item->id }}"
                                      data-name="{{ $item->item_name }}"
                                      data-sku="{{ $item->sku }}"
                                      data-unit="{{ $item->unit }}"
                                      data-stock="{{ $item->current_stock }}"
                                      data-threshold="{{ $item->low_stock_threshold }}"
                                      data-category="{{ $item->category->category_name ?? 'Tidak ada' }}"
                                      data-status-color="{{ $item->stock_status_color }}"
                                      data-supplier="{{ $item->supplier->supplier_name ?? '' }}">
                                  {{ $item->item_name }} ({{ $item->sku }}) - Stok: {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
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

                  <div class="mb-4">
                  <label class="form-label" for="supplier_id">Supplier</label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-store"></i></span>
                    <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                      <option value="">Pilih Supplier (Opsional)</option>
                      @foreach($suppliers as $supplier)
                      <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}
                              data-contact="{{ $supplier->contact_person }}"
                              data-phone="{{ $supplier->phone }}"
                              data-address="{{ $supplier->address }}">
                        {{ $supplier->supplier_name }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                  @error('supplier_id')
                    <div class="form-text text-danger">{{ $message }}</div>
                  @else
                    <div class="form-text">
                      Pilih supplier jika transaksi ini berkaitan dengan supplier tertentu
                      <a href="{{ route('suppliers.create') }}" target="_blank" class="text-decoration-none ms-2">
                        <i class="bx bx-plus me-1"></i>Tambah supplier baru
                      </a>
                    </div>
                  @enderror
                </div>

                  <!-- Selected Item Info -->
                  <div id="itemInfo" class="card mb-4" style="display: none;">
                    <!-- Existing item info content -->
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

            <!-- Sidebar (existing) untuk single transaction -->
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

              <!-- Existing sidebar content -->
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
                    <a href="#" class="btn btn-outline-warning btn-sm">
                      <i class="bx bx-error me-1"></i>
                      Item Stok Menipis
                    </a>
                    <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-info btn-sm">
                      <i class="bx bx-history me-1"></i>
                      Riwayat Transaksi
                    </a>
                    <a href="#" class="btn btn-outline-success btn-sm">
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
        </div>
        
        <!-- Multiple Transactions Tab -->
        <div class="tab-pane fade" id="multiple" role="tabpanel">
          <div class="card-body">
            <div class="alert alert-info">
              <i class="bx bx-info-circle me-2"></i>
              <strong>Multiple Transactions:</strong> Input beberapa transaksi sekaligus untuk efisiensi.
              Setiap baris akan divalidasi secara terpisahh.
            </div>
            
            <form action="{{ route('stock-transactions.store-multiple') }}" method="POST" id="multipleTransactionForm">
              @csrf
              
              <!-- Transaction Rows Container -->
              <div id="transactionRows">
                <!-- Initial row will be added by JavaScript -->
              </div>
              
              <!-- Add Row Button -->
              <div class="d-flex justify-content-between align-items-center mb-4">
                <button type="button" class="btn btn-outline-primary" onclick="addTransactionRow()">
                  <i class="bx bx-plus me-1"></i>Tambah Baris
                </button>
                <small class="text-muted">
                  <span id="rowCount">0</span> transaksi akan diproses
                </small>
              </div>
              
              <!-- Bulk Actions -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <button type="button" class="btn btn-outline-warning btn-sm" onclick="clearAllRows()">
                    <i class="bx bx-trash me-1"></i>Hapus Semua
                  </button>
                  <button type="button" class="btn btn-outline-info btn-sm ms-2" onclick="duplicateLastRow()">
                    <i class="bx bx-copy me-1"></i>Duplikat Baris Terakhir
                  </button>
                </div>
                <div class="col-md-6 text-end">
                  <button type="button" class="btn btn-outline-secondary btn-sm" onclick="validateAllRows()">
                    <i class="bx bx-check-circle me-1"></i>Validasi Semua
                  </button>
                </div>
              </div>
              
              <!-- Submit Buttons -->
              <div class="d-flex justify-content-between">
                <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">
                  <i class="bx bx-arrow-back me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-success" id="submitMultipleBtn" disabled>
                  <i class="bx bx-save me-1"></i>
                  Simpan <span id="totalTransactions">0</span> Transaksi
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Template untuk Transaction Row -->
<script type="text/template" id="transactionRowTemplate">
  <div class="transaction-row border rounded p-3 mb-3" data-row-id="{rowId}">
    <div class="row">
      <div class="col-md-3">
        <label class="form-label">Item <span class="text-danger">*</span></label>
        <input 
          type="text" 
          class="form-control item-search" 
          placeholder="Cari item..."
          data-row="{rowId}"
          autocomplete="off"
          list="items_list_{rowId}"
        >
        <input type="hidden" name="transactions[{rowId}][item_id]" class="item-id" data-row="{rowId}">
        <datalist id="items_list_{rowId}">
          @foreach($items as $item)
          <option value="{{ $item->item_name }} ({{ $item->sku }})" 
                  data-id="{{ $item->id }}"
                  data-name="{{ $item->item_name }}"
                  data-sku="{{ $item->sku }}"
                  data-unit="{{ $item->unit }}"
                  data-stock="{{ $item->current_stock }}"
                  data-threshold="{{ $item->low_stock_threshold }}"
                  data-category="{{ $item->category->category_name ?? 'Tidak ada' }}"
                  data-status-color="{{ $item->stock_status_color }}"
                  data-supplier="{{ $item->supplier->supplier_name ?? '' }}">
            {{ $item->item_name }} ({{ $item->sku }}) - Stok: {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
          </option>
          @endforeach
        </datalist>
      </div>
      
      <div class="col-md-2">
        <label class="form-label">Tipe <span class="text-danger">*</span></label>
        <select class="form-select transaction-type" name="transactions[{rowId}][transaction_type]" data-row="{rowId}" required>
          <option value="">Pilih...</option>
          <option value="IN">Masuk</option>
          <option value="OUT">Keluar</option>
          <option value="ADJUSTMENT">Penyesuaian</option>
        </select>
      </div>
      
      <div class="col-md-2">
        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
        <input 
          type="number" 
          class="form-control quantity" 
          name="transactions[{rowId}][quantity]" 
          placeholder="0" 
          step="0.01" 
          min="0.01" 
          data-row="{rowId}"
          required
        >
      </div>
      
      <div class="col-md-2">
        <label class="form-label">Supplier</label>
        <select class="form-select supplier" name="transactions[{rowId}][supplier_id]" data-row="{rowId}">
          <option value="">Pilih...</option>
          @foreach($suppliers as $supplier)
          <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
          @endforeach
        </select>
      </div>
      
      <div class="col-md-2">
        <label class="form-label">Tanggal</label>
        <input 
          type="datetime-local" 
          class="form-control" 
          name="transactions[{rowId}][transaction_date]" 
          value="{{ now()->format('Y-m-d\TH:i') }}"
        >
      </div>
      
      <div class="col-md-1">
        <label class="form-label">Aksi</label>
        <div class="d-flex gap-1">
          <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow({rowId})" title="Hapus">
            <i class="bx bx-trash"></i>
          </button>
          <button type="button" class="btn btn-outline-info btn-sm" onclick="duplicateRow({rowId})" title="Duplikat">
            <i class="bx bx-copy"></i>
          </button>
        </div>
      </div>
    </div>
    
    <div class="row mt-2">
      <div class="col-12">
        <label class="form-label">Catatan <span class="text-danger">*</span></label>
        <textarea 
          class="form-control notes" 
          name="transactions[{rowId}][notes]" 
          rows="2" 
          placeholder="Masukkan alasan atau keterangan..."
          data-row="{rowId}"
          required
        ></textarea>
      </div>
    </div>
    
    <!-- Row Info & Validation -->
    <div class="row mt-2">
      <div class="col-12">
        <div class="row-info d-none">
          <small class="text-muted">
            <span class="current-stock">Stok: -</span> | 
            <span class="unit">Unit: -</span> | 
            <span class="category">Kategori: -</span> | 
            <span class="supplier-info">Supplier: -</span>
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
// Existing single transaction JavaScript tetap sama
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
        updatePreview();
    } else {
        hiddenInput.value = '';
    }
});

document.addEventListener('DOMContentLoaded', function() {
  const itemSelect = document.getElementById('item_id');
  const quantityInput = document.getElementById('quantity');
  const typeRadios = document.querySelectorAll('input[name="transaction_type"]');
  
  // Add initial row when multiple tab is shown
  document.getElementById('multiple-tab')?.addEventListener('shown.bs.tab', function() {
      if (document.querySelectorAll('.transaction-row').length === 0) {
          addTransactionRow();
      }
  });

  // Existing single form functions...
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
    const input = document.getElementById('item_search').value;
    const datalist = document.getElementById('items_list');
    const hiddenInput = document.getElementById('item_id');
    const selectedType = document.querySelector('input[name="transaction_type"]:checked');
    const stockWarning = document.getElementById('stockWarning');
    const warningText = document.getElementById('warningText');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!hiddenInput.value || !selectedType || !quantityInput.value) {
      stockWarning.style.display = 'none';
      return;
    }
    
    const option = [...datalist.options].find(opt => opt.value === input);
    if (!option) return;
    
    const currentStock = parseFloat(option.dataset.stock);
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
  const input = document.getElementById('item_search').value;
  const datalist = document.getElementById('items_list');
  const hiddenInput = document.getElementById('item_id');
  const selectedType = document.querySelector('input[name="transaction_type"]:checked');
  const quantity = quantityInput.value;
  const supplierSelect = document.getElementById('supplier_id'); // TAMBAH INI
  const preview = document.getElementById('transactionPreview');
  
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
  let typeText = '';
  let typeColor = '';
  let typeIcon = '';
  
  // Supplier info
  const supplierInfo = supplierSelect.selectedOptions[0];
  const supplierName = supplierInfo && supplierInfo.value ? supplierInfo.text : 'Tanpa supplier';
  const hasSupplier = supplierInfo && supplierInfo.value;
  
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
      
      ${hasSupplier ? `
      <div class="d-flex align-items-center mb-2">
        <i class="bx bx-store text-success me-2"></i>
        <span class="text-success fw-semibold">${supplierName}</span>
      </div>
      ` : ''}
      
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
      <div class="row mb-2">
        <div class="col-6">
          <small class="text-muted d-block">Stok Saat Ini:</small>
          <small class="fw-semibold text-primary">${currentStock.toLocaleString('id-ID')}</small>
        </div>
        <div class="col-6">
          <small class="text-muted d-block">Minimum:</small>
          <small class="fw-semibold text-warning">${parseFloat(data.threshold).toLocaleString('id-ID')}</small>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <small class="text-muted d-block">Supplier:</small>
          <small class="fw-semibold ${hasSupplier ? 'text-success' : 'text-muted'}">${supplierName}</small>
        </div>
      </div>
    </div>
  `;
}

// TAMBAH event listener untuk supplier select
document.getElementById('supplier_id')?.addEventListener('change', updatePreview);


  // Event listeners for single form
  if (quantityInput) {
    quantityInput.addEventListener('input', function() {
      validateQuantity();
      updatePreview();
    });
  }

  typeRadios.forEach(radio => {
    radio.addEventListener('change', function() {
      updateQuantityLabel();
      validateQuantity();
      updatePreview();
    });
  });

  // Form validation
  document.getElementById('transactionForm')?.addEventListener('submit', function(e) {
    const hiddenInput = document.getElementById('item_id');
    if (!hiddenInput.value || !document.querySelector('input[name="transaction_type"]:checked') || !quantityInput.value) {
      e.preventDefault();
      alert('Mohon lengkapi semua field yang wajib diisi');
      return false;
    }
    
    const input = document.getElementById('item_search').value;
    const datalist = document.getElementById('items_list');
    const option = [...datalist.options].find(opt => opt.value === input);
    const selectedType = document.querySelector('input[name="transaction_type"]:checked');
    
    if (option && selectedType) {
      const currentStock = parseFloat(option.dataset.stock);
      const quantity = parseFloat(quantityInput.value);
      
      if (selectedType.value === 'OUT' && quantity > currentStock) {
        e.preventDefault();
        alert('Stok tidak mencukupi untuk transaksi ini!');
        return false;
      }
    }
  });

  // Reset form
  document.querySelector('button[type="reset"]')?.addEventListener('click', function() {
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

// Multiple Transaction Functions
let rowCounter = 0;
const maxRows = 50;

function addTransactionRow(data = null) {
    if (document.querySelectorAll('.transaction-row').length >= maxRows) {
        alert(`Maksimal ${maxRows} baris transaksi`);
        return;
    }
    
    rowCounter++;
    const rowId = rowCounter;
    
    // Get template
    const template = document.getElementById('transactionRowTemplate').innerHTML;
    const rowHtml = template.replace(/{rowId}/g, rowId);
    
    // Add to container
    document.getElementById('transactionRows').insertAdjacentHTML('beforeend', rowHtml);
    
    // Add event listeners
    setupRowEventListeners(rowId);
    
    updateRowCount();
    updateSubmitButton();
}

function setupRowEventListeners(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    
    // Item search
    row.querySelector('.item-search').addEventListener('input', function() {
        handleItemSearch(rowId);
    });
    
    // Transaction type change
    row.querySelector('.transaction-type').addEventListener('change', function() {
        validateRow(rowId);
    });
    
    // Quantity change
    row.querySelector('.quantity').addEventListener('input', function() {
        validateRow(rowId);
    });
    
    // Notes change
    row.querySelector('.notes').addEventListener('input', function() {
        validateRow(rowId);
    });
}

function handleItemSearch(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    const searchInput = row.querySelector('.item-search');
    const hiddenInput = row.querySelector('.item-id');
    const datalist = row.querySelector('datalist');
    
    const input = searchInput.value;
    const option = Array.from(datalist.options).find(opt => opt.value === input);
    
    if (option) {
        hiddenInput.value = option.dataset.id;
        updateRowInfo(rowId);
        validateRow(rowId);
    } else {
        hiddenInput.value = '';
        clearRowInfo(rowId);
    }
}

function updateRowInfo(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    const hiddenInput = row.querySelector('.item-id');
    const datalist = row.querySelector('datalist');
    
    if (hiddenInput.value) {
        const option = Array.from(datalist.options).find(opt => opt.dataset.id == hiddenInput.value);
        if (option) {
            const data = option.dataset;
            row.querySelector('.current-stock').textContent = `Stok: ${parseFloat(data.stock).toLocaleString('id-ID')}`;
            row.querySelector('.unit').textContent = `Unit: ${data.unit}`;
            row.querySelector('.category').textContent = `Kategori: ${data.category}`;
            row.querySelector('.row-info').classList.remove('d-none');
        }
    }
}

function clearRowInfo(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    row.querySelector('.row-info').classList.add('d-none');
}

function validateRow(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    const itemId = row.querySelector('.item-id').value;
    const type = row.querySelector('.transaction-type').value;
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const notes = row.querySelector('.notes').value.trim();
    
    const validationDiv = row.querySelector('.row-validation');
    const errorText = validationDiv.querySelector('.error-text');
    
    let errors = [];
    
    if (!itemId) {
        errors.push('Item harus dipilih');
    }
    
    if (!type) {
        errors.push('Tipe transaksi harus dipilih');
    }
    
    if (!quantity || quantity <= 0) {
        errors.push('Jumlah harus > 0');
    }
    
    if (!notes) {
        errors.push('Catatan harus diisi');
    }
    
    // Check stock for OUT transactions
    if (itemId && type === 'OUT' && quantity > 0) {
        const datalist = row.querySelector('datalist');
        const option = Array.from(datalist.options).find(opt => opt.dataset.id == itemId);
        if (option) {
            const currentStock = parseFloat(option.dataset.stock);
            if (quantity > currentStock) {
                errors.push(`Stok tidak cukup (${currentStock} tersedia)`);
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
    
    updateSubmitButton();
}

function removeRow(rowId) {
    if (document.querySelectorAll('.transaction-row').length <= 1) {
        alert('Minimal harus ada 1 baris transaksi');
        return;
    }
    
    document.querySelector(`[data-row-id="${rowId}"]`).remove();
    updateRowCount();
    updateSubmitButton();
}

function duplicateRow(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    const data = {
        item_id: row.querySelector('.item-id').value,
        transaction_type: row.querySelector('.transaction-type').value,
        quantity: row.querySelector('.quantity').value,
        transaction_date: row.querySelector('input[type="datetime-local"]').value,
        notes: row.querySelector('.notes').value
    };
    
    addTransactionRow(data);
}

function duplicateLastRow() {
    const rows = document.querySelectorAll('.transaction-row');
    if (rows.length > 0) {
        const lastRow = rows[rows.length - 1];
        const rowId = lastRow.dataset.rowId;
        duplicateRow(rowId);
    }
}

function clearAllRows() {
    if (confirm('Hapus semua baris transaksi?')) {
        document.getElementById('transactionRows').innerHTML = '';
        rowCounter = 0;
        addTransactionRow();
    }
}

function validateAllRows() {
    const rows = document.querySelectorAll('.transaction-row');
    rows.forEach(row => {
        const rowId = row.dataset.rowId;
        validateRow(rowId);
    });
    
    const invalidRows = document.querySelectorAll('.transaction-row.border-danger');
    if (invalidRows.length > 0) {
        alert(`${invalidRows.length} baris memiliki error. Perbaiki sebelum submit.`);
    } else {
        alert('Semua baris valid! Siap untuk disimpan.');
    }
}

function updateRowCount() {
    const count = document.querySelectorAll('.transaction-row').length;
    const rowCountEl = document.getElementById('rowCount');
    const totalTransactionsEl = document.getElementById('totalTransactions');
    
    if (rowCountEl) rowCountEl.textContent = count;
    if (totalTransactionsEl) totalTransactionsEl.textContent = count;
}

function updateSubmitButton() {
    const rows = document.querySelectorAll('.transaction-row');
    const invalidRows = document.querySelectorAll('.transaction-row.border-danger');
    const submitBtn = document.getElementById('submitMultipleBtn');
    
    if (!submitBtn) return;
    
    const hasValidRows = rows.length > 0 && invalidRows.length === 0;
    submitBtn.disabled = !hasValidRows;
    
    if (hasValidRows) {
        submitBtn.classList.remove('btn-secondary');
        submitBtn.classList.add('btn-success');
    } else {
        submitBtn.classList.remove('btn-success');
        submitBtn.classList.add('btn-secondary');
    }
}

// Form submit handler
document.getElementById('multipleTransactionForm')?.addEventListener('submit', function(e) {
    const invalidRows = document.querySelectorAll('.transaction-row.border-danger');
    if (invalidRows.length > 0) {
        e.preventDefault();
        alert('Ada baris yang belum valid. Perbaiki error terlebih dahulu.');
        return false;
    }
    
    // Show loading
    const submitBtn = document.getElementById('submitMultipleBtn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
    }
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

/* Other existing styles... */
</style>
@endpush
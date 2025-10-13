@extends('layouts.admin')

@section('title', 'Detail Item - ' . $item->item_name . ' - Chicking BJM')

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
        <li class="breadcrumb-item active" aria-current="page">{{ $item->item_name }}</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Header Section -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-4">
              <span class="avatar-initial rounded bg-label-{{ $item->stock_status_color }}" style="width: 60px; height: 60px; font-size: 24px;">
                <i class="bx bx-package"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-1">{{ $item->item_name }}</h4>
              <div class="d-flex align-items-center gap-3 mb-2">
                <span class="badge bg-label-dark">{{ $item->sku }}</span>
                <span class="badge bg-{{ $item->stock_status_color }}">{{ $item->stock_status }}</span>
                <span class="text-muted">
                  <i class="bx bx-cube me-1"></i>
                  {{ $item->unit }}
                </span>
              </div>
              <div class="d-flex align-items-center gap-3">
                @if($item->category)
                <span class="text-muted">
                  <i class="bx bx-category me-1"></i>
                  {{ $item->category->category_name }}
                </span>
                @endif
                @if($item->supplier)
                <span class="text-muted">
                  <i class="bx bx-store me-1"></i>
                  {{ $item->supplier->supplier_name }}
                </span>
                @endif
              </div>
            </div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('items.edit', $item->id) }}" class="btn btn-outline-primary">
              <i class="bx bx-edit-alt me-1"></i>
              Edit
            </a>
            <button type="button" class="btn btn-warning" onclick="showStockAdjustment()">
              <i class="bx bx-transfer me-1"></i>
              Sesuaikan Stok
            </button>
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Left Column - Item Details -->
  <div class="col-xl-8">
    <!-- Basic Information -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Informasi Dasar
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label text-muted">SKU</label>
              <div class="d-flex align-items-center">
                <span class="badge bg-label-dark me-2">{{ $item->sku }}</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('{{ $item->sku }}')">
                  <i class="bx bx-copy"></i>
                </button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label text-muted">Nama Item</label>
              <p class="fw-semibold mb-0">{{ $item->item_name }}</p>
            </div>
            <div class="mb-3">
              <label class="form-label text-muted">Unit</label>
              <p class="mb-0">
                <i class="bx bx-cube me-1"></i>
                {{ $item->unit }}
              </p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label text-muted">Kategori</label>
              @if($item->category)
              <div class="d-flex align-items-center">
                <i class="bx bx-category text-primary me-2"></i>
                <span>{{ $item->category->category_name }}</span>
                <a href="{{ route('categories.show', $item->category->id) }}" class="btn btn-sm btn-outline-primary ms-2">
                  <i class="bx bx-show"></i>
                </a>
              </div>
              @else
              <p class="text-muted mb-0">
                <i class="bx bx-category-alt me-1"></i>
                Tidak ada kategori
              </p>
              @endif
            </div>
            <div class="mb-3">
              <label class="form-label text-muted">Supplier</label>
              @if($item->supplier)
              <div class="d-flex align-items-center">
                <i class="bx bx-store text-success me-2"></i>
                <div class="flex-grow-1">
                  <span>{{ $item->supplier->supplier_name }}</span>
                  @if($item->supplier->contact_person)
                  <br><small class="text-muted">{{ $item->supplier->contact_person }}</small>
                  @endif
                </div>
                <a href="{{ route('suppliers.show', $item->supplier->id) }}" class="btn btn-sm btn-outline-success">
                  <i class="bx bx-show"></i>
                </a>
              </div>
              @else
              <p class="text-muted mb-0">
                <i class="bx bx-store-alt me-1"></i>
                Tidak ada supplier
              </p>
              @endif
            </div>
            <div class="mb-3">
              <label class="form-label text-muted">Dibuat pada</label>
              <p class="mb-0">
                <i class="bx bx-calendar me-1"></i>
                {{ $item->created_at->format('d/m/Y H:i') }}
                <small class="text-muted">({{ $item->created_at->diffForHumans() }})</small>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Information -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-box me-2"></i>
          Informasi Stok
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <div class="d-flex flex-column align-items-center text-center p-3 border rounded">
              <i class="bx bx-package text-{{ $item->stock_status_color }}" style="font-size: 32px;"></i>
              <h4 class="mt-2 mb-1 text-{{ $item->stock_status_color }}">
                {{ number_format($item->current_stock, 2) }}
              </h4>
              <span class="text-muted">{{ $item->unit }}</span>
              <small class="text-muted">Stok Saat Ini</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="d-flex flex-column align-items-center text-center p-3 border rounded">
              <i class="bx bx-error text-warning" style="font-size: 32px;"></i>
              <h4 class="mt-2 mb-1 text-warning">
                {{ number_format($item->low_stock_threshold, 2) }}
              </h4>
              <span class="text-muted">{{ $item->unit }}</span>
              <small class="text-muted">Batas Minimum</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="d-flex flex-column align-items-center text-center p-3 border rounded">
              <i class="bx bx-trending-up text-info" style="font-size: 32px;"></i>
              <h4 class="mt-2 mb-1 text-info">
                @php 
                  $percentage = $item->low_stock_threshold > 0 ? 
                    round(($item->current_stock / $item->low_stock_threshold) * 100, 1) : 0;
                @endphp
                {{ $percentage }}%
              </h4>
              <span class="text-muted">dari minimum</span>
              <small class="text-muted">Persentase Stok</small>
            </div>
          </div>
        </div>
        
        <!-- Stock Progress Bar -->
        <div class="mt-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted">Status Stok:</span>
            <span class="badge bg-{{ $item->stock_status_color }}">{{ $item->stock_status }}</span>
          </div>
          <div class="progress mb-2" style="height: 10px;">
            <div class="progress-bar bg-{{ $item->stock_status_color }}" 
                 style="width: {{ min(100, $percentage) }}%"
                 aria-valuenow="{{ $percentage }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <small class="text-muted">0</small>
            <small class="text-muted">{{ number_format($item->low_stock_threshold, 0) }} (Minimum)</small>
          </div>
        </div>

        @if($item->current_stock <= $item->low_stock_threshold)
        <div class="alert alert-{{ $item->current_stock <= 0 ? 'danger' : 'warning' }} mt-3" role="alert">
          <div class="d-flex align-items-center">
            <i class="bx {{ $item->current_stock <= 0 ? 'bx-x-circle' : 'bx-error-circle' }} me-2"></i>
            <div>
              <strong>{{ $item->current_stock <= 0 ? 'Stok Habis!' : 'Stok Menipis!' }}</strong>
              <p class="mb-0">
                {{ $item->current_stock <= 0 ? 
                   'Item ini sudah habis dan perlu segera di-restock.' : 
                   'Stok item ini sudah mencapai batas minimum. Pertimbangkan untuk melakukan restock.' }}
              </p>
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>

    <!-- Stock Transaction History -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-history me-2"></i>
          Riwayat Transaksi Stok
        </h5>
        <span class="badge bg-label-primary">{{ $item->stockTransactions()->count() }} Transaksi</span>
      </div>
      <div class="card-body">
        @if($item->stockTransactions && $item->stockTransactions->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th class="text-center">Jumlah</th>
                <th>Catatan</th>
                <th class="text-center">User</th>
              </tr>
            </thead>
            <tbody>
              @foreach($item->stockTransactions as $transaction)
              <tr>
                <td>
                  <div>
                    <span class="fw-semibold">{{ $transaction->transaction_date->format('d/m/Y') }}</span>
                    <br><small class="text-muted">{{ $transaction->transaction_date->format('H:i') }}</small>
                  </div>
                </td>
                <td>
                  <span class="badge bg-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }}">
                    <i class="bx {{ $transaction->transaction_type == 'IN' ? 'bx-plus' : ($transaction->transaction_type == 'OUT' ? 'bx-minus' : 'bx-transfer') }} me-1"></i>
                    {{ $transaction->transaction_type == 'IN' ? 'Masuk' : ($transaction->transaction_type == 'OUT' ? 'Keluar' : 'Penyesuaian') }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="fw-bold text-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }}">
                    {{ $transaction->transaction_type == 'OUT' ? '-' : '+' }}{{ number_format($transaction->quantity, 2) }}
                  </span>
                  <br><small class="text-muted">{{ $item->unit }}</small>
                </td>
                <td>
                  <span title="{{ $transaction->notes }}">
                    {{ Str::limit($transaction->notes, 50) }}
                  </span>
                </td>
                <td class="text-center">
                  @if($transaction->user)
                  <div class="d-flex align-items-center justify-content-center">
                    <div class="avatar avatar-xs me-2">
                      <span class="avatar-initial rounded-circle bg-label-primary">
                        {{ substr($transaction->user->name, 0, 1) }}
                      </span>
                    </div>
                    <span class="fw-semibold">{{ $transaction->user->name }}</span>
                  </div>
                  @else
                  <span class="text-muted">System</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        
        @if($item->stockTransactions()->count() > 10)
        <div class="text-center mt-3">
          <small class="text-muted">
            Menampilkan 10 transaksi terakhir dari {{ $item->stockTransactions()->count() }} total transaksi
          </small>
          <br>
          <a href="#" class="btn btn-outline-primary btn-sm mt-2" onclick="loadAllTransactions()">
            <i class="bx bx-show me-1"></i>
            Lihat Semua Transaksi
          </a>
        </div>
        @endif
        @else
        <div class="text-center py-4">
          <i class="bx bx-history text-muted" style="font-size: 48px;"></i>
          <h6 class="mt-2 text-muted">Belum Ada Transaksi</h6>
          <p class="text-muted">Transaksi stok akan muncul di sini setelah ada perubahan stok</p>
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Right Column - Quick Actions & Stats -->
  <div class="col-xl-4">
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
          <button type="button" class="btn btn-warning" onclick="showStockAdjustment()">
            <i class="bx bx-transfer me-1"></i>
            Sesuaikan Stok
          </button>
          <a href="{{ route('items.edit', $item->id) }}" class="btn btn-outline-primary">
            <i class="bx bx-edit-alt me-1"></i>
            Edit Item
          </a>
          @if($item->supplier)
          <a href="{{ route('suppliers.show', $item->supplier->id) }}" class="btn btn-outline-success">
            <i class="bx bx-store me-1"></i>
            Lihat Supplier
          </a>
          @endif
          @if($item->category)
          <a href="{{ route('categories.show', $item->category->id) }}" class="btn btn-outline-info">
            <i class="bx bx-category me-1"></i>
            Lihat Kategori
          </a>
          @endif
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
              <i class="bx bx-dots-horizontal me-1"></i>
              More Actions
            </button>
            <ul class="dropdown-menu w-100">
              <li>
                <a class="dropdown-item" href="#" onclick="printItemDetails()">
                  <i class="bx bx-printer me-1"></i>
                  Print Detail
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="#" onclick="exportItemData()">
                  <i class="bx bx-download me-1"></i>
                  Export Data
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger" 
                          onclick="return confirm('Apakah Anda yakin ingin menghapus item {{ $item->item_name }}?')"
                          {{ $item->stockTransactions()->count() > 0 ? 'disabled title="Tidak dapat menghapus item yang memiliki riwayat transaksi"' : '' }}>
                    <i class="bx bx-trash me-1"></i>
                    Hapus Item
                  </button>
                </form>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-bar-chart me-2"></i>
          Statistik
        </h6>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-muted">Total Transaksi:</span>
          <span class="badge bg-primary">{{ $item->stockTransactions()->count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-muted">Transaksi Masuk:</span>
          <span class="badge bg-success">{{ $item->stockTransactions()->where('transaction_type', 'IN')->count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-muted">Transaksi Keluar:</span>
          <span class="badge bg-danger">{{ $item->stockTransactions()->where('transaction_type', 'OUT')->count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-muted">Penyesuaian:</span>
          <span class="badge bg-warning">{{ $item->stockTransactions()->where('transaction_type', 'ADJUSTMENT')->count() }}</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-muted">Total Masuk:</span>
          <span class="fw-bold text-success">
            +{{ number_format($item->stockTransactions()->where('transaction_type', 'IN')->sum('quantity'), 2) }}
          </span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Total Keluar:</span>
          <span class="fw-bold text-danger">
            -{{ number_format($item->stockTransactions()->where('transaction_type', 'OUT')->sum('quantity'), 2) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Related Items -->
    @if($item->category)
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-package me-2"></i>
          Item Terkait
        </h6>
      </div>
      <div class="card-body">
        @php 
          $relatedItems = \App\Models\Item::where('category_id', $item->category_id)
                                         ->where('id', '!=', $item->id)
                                         ->limit(5)->get();
        @endphp
        
        @if($relatedItems->count() > 0)
        <div class="list-group list-group-flush">
          @foreach($relatedItems as $relatedItem)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ $relatedItem->stock_status_color }}">
                  <i class="bx bx-package"></i>
                </span>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">
                  <a href="{{ route('items.show', $relatedItem->id) }}" class="text-decoration-none">
                    {{ Str::limit($relatedItem->item_name, 20) }}
                  </a>
                </h6>
                <small class="text-muted">
                  {{ $relatedItem->sku }} • 
                  <span class="text-{{ $relatedItem->stock_status_color }}">
                    {{ number_format($relatedItem->current_stock, 0) }} {{ $relatedItem->unit }}
                  </span>
                </small>
              </div>
              <span class="badge bg-{{ $relatedItem->stock_status_color }}">
                {{ $relatedItem->stock_status }}
              </span>
            </div>
          </div>
          @endforeach
        </div>
        
        @if(\App\Models\Item::where('category_id', $item->category_id)->where('id', '!=', $item->id)->count() > 5)
        <div class="text-center mt-3">
          <a href="{{ route('items.index', ['category_id' => $item->category_id]) }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-show me-1"></i>
            Lihat Semua
          </a>
        </div>
        @endif
        @else
        <p class="text-muted mb-0">Tidak ada item lain dalam kategori ini</p>
        @endif
      </div>
    </div>
    @endif
  </div>
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
      <form action="{{ route('items.adjust-stock', $item->id) }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Item</label>
            <input type="text" class="form-control" value="{{ $item->item_name }}" readonly>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Stok Saat Ini</label>
            <input type="text" class="form-control" value="{{ number_format($item->current_stock, 2) }} {{ $item->unit }}" readonly>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Stock adjustment modal
  window.showStockAdjustment = function() {
    const modal = new bootstrap.Modal(document.getElementById('stockAdjustmentModal'));
    modal.show();
  };

  // Copy to clipboard
  window.copyToClipboard = function(text) {
    navigator.clipboard.writeText(text).then(function() {
      // Show success message
      const toast = document.createElement('div');
      toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
      toast.setAttribute('role', 'alert');
      toast.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">
            SKU berhasil disalin: ${text}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      `;
      document.body.appendChild(toast);
      const bsToast = new bootstrap.Toast(toast);
      bsToast.show();
      
      setTimeout(() => {
        document.body.removeChild(toast);
      }, 3000);
    });
  };

  // Print item details
  window.printItemDetails = function() {
    window.print();
  };

  // Export item data
  window.exportItemData = function() {
    const data = {
      sku: '{{ $item->sku }}',
      item_name: '{{ $item->item_name }}',
      category: '{{ $item->category->category_name ?? "Tidak ada" }}',
      supplier: '{{ $item->supplier->supplier_name ?? "Tidak ada" }}',
      unit: '{{ $item->unit }}',
      current_stock: '{{ $item->current_stock }}',
      low_stock_threshold: '{{ $item->low_stock_threshold }}',
      stock_status: '{{ $item->stock_status }}',
      created_at: '{{ $item->created_at->format("d/m/Y H:i") }}',
      updated_at: '{{ $item->updated_at->format("d/m/Y H:i") }}'
    };
    
    const jsonStr = JSON.stringify(data, null, 2);
    const blob = new Blob([jsonStr], { type: 'application/json' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'item_{{ $item->sku }}_' + new Date().toISOString().slice(0,10) + '.json');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Load all transactions (if implemented)
  window.loadAllTransactions = function() {
    // This would require an AJAX call to load all transactions
    alert('Fitur ini akan menampilkan semua transaksi dalam modal atau halaman terpisah');
  };
});
</script>
@endpush

@push('styles')
<style>
@media print {
  .btn, .breadcrumb, .card-header .btn, .dropdown, .modal {
    display: none !important;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
  }
  
  .table {
    font-size: 12px;
  }
}

.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.table-hover tbody tr:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.progress {
  background-color: #e9ecef;
}

.list-group-item {
  transition: background-color 0.15s ease-in-out;
}

.list-group-item:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.badge {
  font-size: 0.75em;
}

.avatar.avatar-xs {
  width: 24px;
  height: 24px;
  font-size: 10px;
}
</style>
@endpush
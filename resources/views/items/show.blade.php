@extends('layouts.admin')

@section('title', 'Detail Item - ' . $item->item_name . ' - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('items.index') }}">Item</a></li>
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
              <span class="avatar-initial rounded bg-label-primary" style="width: 60px; height: 60px; font-size: 24px;">
                <i class="bx bx-box"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-1">{{ $item->item_name }}</h4>
              <p class="mb-0 text-muted">
                <span class="badge bg-label-secondary me-2">{{ $item->sku }}</span>
                <i class="bx bx-cube me-1"></i>{{ $item->unit }}
              </p>
            </div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('items.edit', $item->id) }}" class="btn btn-outline-primary">
              <i class="bx bx-edit me-1"></i>Edit
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#stockAdjustmentModal">
              <i class="bx bx-transfer me-1"></i>Sesuaikan Stok
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Item Info -->
  <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
    <!-- Basic Info -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Informasi Item
        </h5>
      </div>
      <div class="card-body">
        <div class="info-container">
          <ul class="list-unstyled">
            <li class="mb-3">
              <span class="fw-medium text-muted d-block">SKU</span>
              <span class="fw-semibold">{{ $item->sku }}</span>
            </li>
            <li class="mb-3">
              <span class="fw-medium text-muted d-block">Kategori</span>
              @if($item->category)
                <span class="fw-semibold">{{ $item->category->category_name }}</span>
              @else
                <span class="text-muted">Tidak ada kategori</span>
              @endif
            </li>
            <li class="mb-3">
              <span class="fw-medium text-muted d-block">Supplier</span>
              @if($item->supplier)
                <span class="fw-semibold">{{ $item->supplier->supplier_name }}</span>
                @if($item->supplier->contact_person)
                  <br><small class="text-muted">{{ $item->supplier->contact_person }}</small>
                @endif
              @else
                <span class="text-muted">Tidak ada supplier</span>
              @endif
            </li>
            <li class="mb-3">
              <span class="fw-medium text-muted d-block">Unit</span>
              <span class="fw-semibold">{{ $item->unit }}</span>
            </li>
            <li class="mb-0">
              <span class="fw-medium text-muted d-block">Batas Minimum Stok</span>
              <span class="fw-semibold">{{ number_format($item->low_stock_threshold, 0) }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Stock Status Card -->
    @php
      $balance = $item->currentBalance;
      $currentStock = $balance ? $balance->closing_stock : $item->current_stock;
      $stockStatus = $balance ? $item->stock_status_monthly : $item->stock_status;
      $stockStatusColor = $balance ? $item->stock_status_color_monthly : $item->stock_status_color;
    @endphp
    
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-signal-3 me-2"></i>
          Status Stok
        </h5>
      </div>
      <div class="card-body text-center">
        <div class="mb-3">
          <h2 class="text-{{ $stockStatusColor }} mb-1">{{ number_format($currentStock, 0) }}</h2>
          <span class="badge bg-{{ $stockStatusColor }}">{{ $stockStatus }}</span>
        </div>
        
        @if($balance)
        <div class="progress mb-3" style="height: 10px;">
          @php $percentage = $item->low_stock_threshold > 0 ? min(100, ($currentStock / $item->low_stock_threshold) * 100) : 0; @endphp
          <div class="progress-bar bg-{{ $stockStatusColor }}" style="width: {{ $percentage }}%"></div>
        </div>
        <p class="text-muted mb-0">
          Minimum: {{ number_format($item->low_stock_threshold, 0) }} {{ $item->unit }}
        </p>
        @endif
      </div>
    </div>
  </div>

  <!-- Monthly Balance -->
  <div class="col-xl-8 col-lg-7 col-md-7 order-0">
    @if($balance)
    <!-- Monthly Balance Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-calendar me-2"></i>
          Monthly Balance - {{ $balance->formatted_period }}
        </h5>
      </div>
      <div class="card-body">
        <!-- Balance Summary -->
        <div class="row text-center mb-4">
          <div class="col-md-3 col-6 mb-3">
            <div class="border rounded p-3">
              <h4 class="text-info mb-1">{{ number_format($balance->opening_stock, 0) }}</h4>
              <span class="text-muted">Stok Awal</span>
              <br><small class="text-muted">{{ now()->startOfMonth()->format('d M') }}</small>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <div class="border rounded p-3">
              <h4 class="text-success mb-1">+{{ number_format($balance->stock_in, 0) }}</h4>
              <span class="text-muted">Stok Masuk</span>
              <br><small class="text-muted">Bulan ini</small>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <div class="border rounded p-3">
              <h4 class="text-danger mb-1">-{{ number_format($balance->stock_out, 0) }}</h4>
              <span class="text-muted">Stok Keluar</span>
              <br><small class="text-muted">Bulan ini</small>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <div class="border rounded p-3">
              <h4 class="text-{{ $stockStatusColor }} mb-1">{{ number_format($balance->closing_stock, 0) }}</h4>
              <span class="text-muted">Stok Akhir</span>
              <br><small class="text-muted">Saat ini</small>
            </div>
          </div>
        </div>

        <!-- Net Change -->
        <div class="row">
          <div class="col-12">
            <div class="card bg-light-secondary">
              <div class="card-body text-center">
                <h5 class="mb-2">
                  <i class="bx bx-transfer me-2"></i>
                  Perubahan Bersih
                </h5>
                @php $netChange = $balance->net_change; @endphp
                @if($netChange > 0)
                  <h3 class="text-success mb-1">
                    <i class="bx bx-up-arrow-alt"></i>
                    +{{ number_format($netChange, 0) }}
                  </h3>
                  <span class="text-success">Naik dari awal bulan</span>
                @elseif($netChange < 0)
                  <h3 class="text-danger mb-1">
                    <i class="bx bx-down-arrow-alt"></i>
                    {{ number_format($netChange, 0) }}
                  </h3>
                  <span class="text-danger">Turun dari awal bulan</span>
                @else
                  <h3 class="text-muted mb-1">
                    <i class="bx bx-minus"></i>
                    0
                  </h3>
                  <span class="text-muted">Tidak ada perubahan</span>
                @endif
                
                @if($balance->adjustments != 0)
                <div class="mt-2">
                  <small class="text-warning">
                    <i class="bx bx-edit me-1"></i>
                    Penyesuaian: {{ $balance->adjustments > 0 ? '+' : '' }}{{ number_format($balance->adjustments, 0) }}
                  </small>
                </div>
                @endif
              </div>
            </div>
          </div>
        </div>

        <!-- Formula Calculation -->
        <div class="row mt-3">
          <div class="col-12">
            <div class="alert alert-info mb-0">
              <h6 class="alert-heading mb-2">
                <i class="bx bx-calculator me-2"></i>
                Formula Perhitungan
              </h6>
              <p class="mb-0">
                <strong>Stok Akhir</strong> = 
                {{ number_format($balance->opening_stock, 0) }} (awal) + 
                {{ number_format($balance->stock_in, 0) }} (masuk) - 
                {{ number_format($balance->stock_out, 0) }} (keluar) 
                @if($balance->adjustments != 0)
                  {{ $balance->adjustments > 0 ? '+' : '' }} {{ number_format($balance->adjustments, 0) }} (penyesuaian)
                @endif
                = <strong>{{ number_format($balance->closing_stock, 0) }}</strong>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    @else
    <!-- No Monthly Balance -->
    <div class="card mb-4">
      <div class="card-body text-center py-5">
        <i class="bx bx-calendar-x" style="font-size: 48px; color: #ddd;"></i>
        <h5 class="mt-3 text-muted">Monthly Balance Belum Tersedia</h5>
        <p class="text-muted mb-3">
          Item ini masih menggunakan current stock lama. 
          <br>Buat monthly balance untuk tracking yang lebih detail.
        </p>
        <button class="btn btn-primary" onclick="createMonthlyBalance({{ $item->id }})">
          <i class="bx bx-plus me-1"></i>
          Buat Monthly Balance
        </button>
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
          Sesuaikan Stok - {{ $item->item_name }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('items.adjust-stock', $item->id) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-info">
            <h6 class="alert-heading">
              <i class="bx bx-info-circle me-2"></i>
              Informasi Stok Saat Ini
            </h6>
            <p class="mb-0">
              <strong>Stok Tersedia:</strong> {{ number_format($currentStock, 0) }} {{ $item->unit }}
              @if($balance)
              <br><strong>Sistem:</strong> Monthly Balance ({{ $balance->formatted_period }})
              @else
              <br><strong>Sistem:</strong> Current Stock (Legacy)
              @endif
            </p>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Tipe Penyesuaian <span class="text-danger">*</span></label>
            <select class="form-select" name="adjustment_type" required>
              <option value="">Pilih tipe penyesuaian</option>
              <option value="add">Tambah Stok</option>
              <option value="reduce">Kurangi Stok</option>
              <option value="set">Set Stok (Atur Ulang)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" required>
            <div class="form-text">Masukkan angka positif</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Catatan <span class="text-danger">*</span></label>
            <textarea class="form-control" name="notes" rows="3" placeholder="Alasan penyesuaian stok..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
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
  // Auto focus pada modal
  const modal = document.getElementById('stockAdjustmentModal');
  modal.addEventListener('shown.bs.modal', function() {
    document.querySelector('[name="adjustment_type"]').focus();
  });
});

// Create monthly balance for legacy items
function createMonthlyBalance(itemId) {
  if (confirm('Buat monthly balance untuk item ini? Ini akan menggunakan current stock sebagai opening stock.')) {
    fetch(`/items/${itemId}/create-monthly-balance`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Terjadi kesalahan saat membuat monthly balance');
    });
  }
}
</script>
@endpush

@push('styles')
<style>
@media print {
  .no-print { display: none !important; }
  .card { border: 1px solid #dee2e6 !important; }
}

.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.table-hover tbody tr:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.progress {
  background-color: #e9ecef;
}

.info-container li {
  border-bottom: 1px solid #f0f0f0;
  padding-bottom: 0.75rem;
}

.info-container li:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.border {
  border: 1px solid #dee2e6 !important;
}

.bg-light-secondary {
  background-color: #f8f9fa !important;
}
</style>
@endpush
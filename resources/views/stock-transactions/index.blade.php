@extends('layouts.admin')

@section('title', 'Transaksi Stok - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Transaksi Stok</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card border-success">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="Stock In" class="rounded" />
          </div>
          <div class="dropdown">
            <span class="badge bg-success">IN</span>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1 text-success">Stok Masuk</span>
        @php 
          $todayStockIn = \App\Models\StockTransaction::where('transaction_type', 'IN')
                          ->whereDate('created_at', today())->count();
        @endphp
        <h3 class="card-title mb-2 text-success">{{ $todayStockIn }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-up-arrow-alt"></i> Hari ini
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card border-danger">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Stock Out" class="rounded" />
          </div>
          <div class="dropdown">
            <span class="badge bg-danger">OUT</span>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1 text-danger">Stok Keluar</span>
        @php 
          $todayStockOut = \App\Models\StockTransaction::where('transaction_type', 'OUT')
                           ->whereDate('created_at', today())->count();
        @endphp
        <h3 class="card-title mb-2 text-danger">{{ $todayStockOut }}</h3>
        <small class="text-danger fw-semibold">
          <i class="bx bx-down-arrow-alt"></i> Hari ini
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card border-warning">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-warning.png') }}" alt="Adjustments" class="rounded" />
          </div>
          <div class="dropdown">
            <span class="badge bg-warning">ADJ</span>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1 text-warning">Penyesuaian</span>
        @php 
          $todayAdjustments = \App\Models\StockTransaction::where('transaction_type', 'ADJUSTMENT')
                              ->whereDate('created_at', today())->count();
        @endphp
        <h3 class="card-title mb-2 text-warning">{{ $todayAdjustments }}</h3>
        <small class="text-warning fw-semibold">
          <i class="bx bx-transfer-alt"></i> Hari ini
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Total Transactions" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Transaksi</span>
        <h3 class="card-title mb-2">{{ $transactions->total() }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-history"></i> Semua waktu
        </small>
      </div>
    </div>
  </div>
</div>

<!-- Filter & Actions -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bx bx-history me-2"></i>
      Riwayat Transaksi Stok
    </h5>
    <div class="d-flex gap-2">
      <a href="{{ route('stock-transactions.report') }}" class="btn btn-outline-info btn-sm">
        <i class="bx bx-bar-chart me-1"></i>
        Laporan
      </a>
      <a href="{{ route('stock-transactions.create') }}" class="btn btn-primary btn-sm">
        <i class="bx bx-plus me-1"></i>
        Tambah Transaksi
      </a>
    </div>
  </div>
  
  <div class="card-body">
    <!-- Filter Form -->
    <form method="GET" action="{{ route('stock-transactions.index') }}" class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">Dari Tanggal</label>
        <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">Sampai Tanggal</label>
        <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
      </div>
      <div class="col-md-2">
        <label class="form-label">Tipe Transaksi</label>
        <select class="form-select" name="transaction_type">
          <option value="">Semua Tipe</option>
          <option value="IN" {{ request('transaction_type') == 'IN' ? 'selected' : '' }}>Stok Masuk</option>
          <option value="OUT" {{ request('transaction_type') == 'OUT' ? 'selected' : '' }}>Stok Keluar</option>
          <option value="ADJUSTMENT" {{ request('transaction_type') == 'ADJUSTMENT' ? 'selected' : '' }}>Penyesuaian</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Item</label>
        <select class="form-select" name="item_id">
          <option value="">Semua Item</option>
          @foreach($items as $item)
          <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
            {{ $item->item_name }}
          </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-1">
        <label class="form-label">&nbsp;</label>
        <div class="d-flex gap-1">
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bx bx-search"></i>
          </button>
          <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-reset"></i>
          </a>
        </div>
      </div>
    </form>

    <!-- Active Filters Display -->
    @if(request()->hasAny(['start_date', 'end_date', 'transaction_type', 'item_id']))
    <div class="d-flex flex-wrap gap-2 mb-3">
      <span class="text-muted me-2">Filter aktif:</span>
      @if(request('start_date'))
        <span class="badge bg-label-primary">
          <i class="bx bx-calendar me-1"></i>
          Dari: {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }}
          <a href="{{ request()->fullUrlWithQuery(['start_date' => null]) }}" class="text-decoration-none ms-1">×</a>
        </span>
      @endif
      @if(request('end_date'))
        <span class="badge bg-label-primary">
          <i class="bx bx-calendar me-1"></i>
          Sampai: {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
          <a href="{{ request()->fullUrlWithQuery(['end_date' => null]) }}" class="text-decoration-none ms-1">×</a>
        </span>
      @endif
      @if(request('transaction_type'))
        <span class="badge bg-label-{{ request('transaction_type') == 'IN' ? 'success' : (request('transaction_type') == 'OUT' ? 'danger' : 'warning') }}">
          <i class="bx bx-tag me-1"></i>
          {{ request('transaction_type') == 'IN' ? 'Stok Masuk' : (request('transaction_type') == 'OUT' ? 'Stok Keluar' : 'Penyesuaian') }}
          <a href="{{ request()->fullUrlWithQuery(['transaction_type' => null]) }}" class="text-decoration-none ms-1">×</a>
        </span>
      @endif
      @if(request('item_id'))
        @php $selectedItem = $items->find(request('item_id')); @endphp
        @if($selectedItem)
        <span class="badge bg-label-info">
          <i class="bx bx-package me-1"></i>
          {{ $selectedItem->item_name }}
          <a href="{{ request()->fullUrlWithQuery(['item_id' => null]) }}" class="text-decoration-none ms-1">×</a>
        </span>
        @endif
      @endif
    </div>
    @endif
  </div>
</div>

<!-- Transactions Table -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h6 class="mb-0">
      Menampilkan {{ $transactions->count() }} dari {{ $transactions->total() }} transaksi
    </h6>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" onclick="exportTransactions()">
        <i class="bx bx-download me-1"></i>
        Export CSV
      </button>
      <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
        <i class="bx bx-printer me-1"></i>
        Print
      </button>
    </div>
  </div>
  
  @if($transactions->count() > 0)
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th style="width: 120px;">
            <i class="bx bx-calendar me-1"></i>
            Tanggal
          </th>
          <th style="width: 100px;">
            <i class="bx bx-tag me-1"></i>
            Tipe
          </th>
          <th>
            <i class="bx bx-package me-1"></i>
            Item
          </th>
          <th class="text-center" style="width: 120px;">
            <i class="bx bx-package me-1"></i>
            Jumlah
          </th>
          <th style="width: 200px;">
            <i class="bx bx-note me-1"></i>
            Catatan
          </th>
          <th class="text-center" style="width: 120px;">
            <i class="bx bx-user me-1"></i>
            User
          </th>
          <th class="text-center" style="width: 80px;">
            <i class="bx bx-cog me-1"></i>
            Aksi
          </th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach ($transactions as $transaction)
        <tr class="transaction-row" data-type="{{ $transaction->transaction_type }}">
          <td>
            <div>
              <span class="fw-semibold">{{ $transaction->transaction_date->format('d/m/Y') }}</span>
              <br><small class="text-muted">{{ $transaction->transaction_date->format('H:i') }}</small>
            </div>
          </td>
          <td>
            <span class="badge bg-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }}">
              <i class="bx {{ $transaction->transaction_type == 'IN' ? 'bx-plus' : ($transaction->transaction_type == 'OUT' ? 'bx-minus' : 'bx-transfer') }} me-1"></i>
              @if($transaction->transaction_type == 'IN')
                Masuk
              @elseif($transaction->transaction_type == 'OUT')
                Keluar
              @else
                Adjust
              @endif
            </span>
          </td>
          <td>
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ $transaction->item->stock_status_color ?? 'primary' }}">
                  <i class="bx bx-package"></i>
                </span>
              </div>
              <div>
                <a href="{{ route('items.show', $transaction->item->id) }}" class="fw-semibold text-decoration-none">
                  {{ $transaction->item->item_name }}
                </a>
                <br><small class="text-muted">
                  {{ $transaction->item->sku }}
                  @if($transaction->item->category)
                    • {{ $transaction->item->category->category_name }}
                  @endif
                </small>
              </div>
            </div>
          </td>
          <td class="text-center">
            <span class="fw-bold text-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }}">
              {{ $transaction->transaction_type == 'OUT' ? '-' : '+' }}{{ number_format($transaction->quantity, 2) }}
            </span>
            <br><small class="text-muted">{{ $transaction->item->unit }}</small>
          </td>
          <td>
            <span title="{{ $transaction->notes }}">
              {{ Str::limit($transaction->notes, 40) }}
            </span>
            @if(strlen($transaction->notes) > 40)
              <button class="btn btn-sm btn-link p-0" onclick="showFullNotes('{{ addslashes($transaction->notes) }}')">
                <i class="bx bx-show"></i>
              </button>
            @endif
          </td>
          <td class="text-center">
            @if($transaction->user)
            <div class="d-flex align-items-center justify-content-center">
              <div class="avatar avatar-xs me-2">
                <span class="avatar-initial rounded-circle bg-label-primary">
                  {{ substr($transaction->user->name, 0, 1) }}
                </span>
              </div>
              <div class="text-start">
                <span class="fw-semibold">{{ $transaction->user->name }}</span>
                <br><small class="text-muted">{{ $transaction->user->role ?? 'User' }}</small>
              </div>
            </div>
            @else
            <span class="text-muted">
              <i class="bx bx-bot"></i>
              System
            </span>
            @endif
          </td>
          <td class="text-center">
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('stock-transactions.show', $transaction->id) }}">
                  <i class="bx bx-show me-1"></i> 
                  Lihat Detail
                </a>
                <a class="dropdown-item" href="{{ route('items.show', $transaction->item->id) }}">
                  <i class="bx bx-package me-1"></i> 
                  Lihat Item
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" onclick="copyTransaction({{ $transaction->id }})">
                  <i class="bx bx-copy me-1"></i> 
                  Duplikasi
                </a>
              </div>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  
  <!-- Simple Pagination -->
  <x-simple-pagination :items="$transactions" type="transaksi" />

  @else
  <!-- Empty State -->
  <div class="card-body text-center py-5">
    <div class="d-flex flex-column align-items-center">
      <i class="bx bx-history text-muted" style="font-size: 64px;"></i>
      <h5 class="mt-3">Tidak Ada Transaksi</h5>
      <p class="text-muted mb-4">
        @if(request()->hasAny(['start_date', 'end_date', 'transaction_type', 'item_id']))
          Tidak ada transaksi yang sesuai dengan filter yang dipilih.<br>
          Coba ubah kriteria pencarian atau hapus filter.
        @else
          Belum ada transaksi stok yang tercatat.<br>
          Mulai dengan menambahkan transaksi pertama.
        @endif
      </p>
      <div class="d-flex gap-2">
        @if(request()->hasAny(['start_date', 'end_date', 'transaction_type', 'item_id']))
        <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">
          <i class="bx bx-reset me-1"></i>
          Hapus Filter
        </a>
        @endif
        <a href="{{ route('stock-transactions.create') }}" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i>
          Tambah Transaksi
        </a>
      </div>
    </div>
  </div>
  @endif
</div>

<!-- Full Notes Modal -->
<div class="modal fade" id="fullNotesModal" tabindex="-1" aria-labelledby="fullNotesModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="fullNotesModalLabel">
          <i class="bx bx-note me-2"></i>
          Catatan Transaksi
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="fullNotesContent"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Export function for transactions
function exportData() {
  const headers = ['Tanggal', 'Tipe', 'Item', 'SKU', 'Jumlah', 'Catatan', 'User'];
  const rows = [
    @foreach($transactions as $transaction)
    ['{{ $transaction->transaction_date->format("d/m/Y H:i") }}', '{{ $transaction->transaction_type }}', '{{ addslashes($transaction->item->item_name) }}', '{{ $transaction->item->sku }}', '{{ $transaction->quantity }}', '{{ addslashes($transaction->notes) }}', '{{ addslashes($transaction->user->full_name ?? "System") }}'],
    @endforeach
  ];
  
  downloadCSV('stock_transactions', headers, rows);
}

// Other existing functions...
function showFullNotes(notes) {
  document.getElementById('fullNotesContent').textContent = notes;
  const modal = new bootstrap.Modal(document.getElementById('fullNotesModal'));
  modal.show();
}
</script>
@endpush

@push('styles')
<style>
@media print {
  .btn, .breadcrumb, .card-header .btn, .dropdown, .modal, .card-footer {
    display: none !important;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
  }
  
  .table {
    font-size: 12px;
  }
  
  .badge {
    border: 1px solid #000;
    background: #fff !important;
    color: #000 !important;
  }
}

.transaction-row:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
}

.avatar.avatar-xs {
  width: 24px;
  height: 24px;
  font-size: 10px;
}

.card.border-success {
  border-color: #71dd37 !important;
}

.card.border-danger {
  border-color: #ff3e1d !important;
}

.card.border-warning {
  border-color: #ffab00 !important;
}

.table th {
  font-weight: 600;
  background-color: #f8f9fa;
}

.badge {
  font-size: 0.75em;
}

.btn-link {
  text-decoration: none !important;
}
</style>
@endpush
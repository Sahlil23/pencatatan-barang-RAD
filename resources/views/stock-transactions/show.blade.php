@extends('layouts.admin')

@section('title', 'Detail Transaksi Stok - Chicking BJM')

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
        <li class="breadcrumb-item active" aria-current="page">Detail Transaksi</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Transaction Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-4">
              <span class="avatar-initial rounded bg-label-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }}">
                <i class="bx {{ $transaction->transaction_type == 'IN' ? 'bx-plus-circle' : ($transaction->transaction_type == 'OUT' ? 'bx-minus-circle' : 'bx-transfer') }}" style="font-size: 24px;"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-1">
                Transaksi {{ $transaction->transaction_type_text }}
              </h4>
              <div class="d-flex align-items-center gap-3 mb-2">
                <span class="badge bg-{{ $transaction->transaction_type_color }}">
                  {{ $transaction->transaction_type }}
                </span>
                <span class="text-muted">
                  <i class="bx bx-calendar me-1"></i>
                  {{ $transaction->transaction_date->format('d/m/Y H:i') }}
                </span>
                @if($transaction->user)
                <span class="text-muted">
                  <i class="bx bx-user me-1"></i>
                  {{ $transaction->user->name }}
                </span>
                @endif
              </div>
              <p class="text-muted mb-0">
                <i class="bx bx-note me-1"></i>
                {{ $transaction->notes }}
              </p>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="d-flex gap-2">
            <a href="{{ route('items.show', $transaction->item) }}" class="btn btn-outline-primary">
              <i class="bx bx-package me-1"></i>
              Lihat Item
            </a>
            @if($transaction->supplier)
            <a href="{{ route('suppliers.show', $transaction->supplier) }}" class="btn btn-outline-success">
              <i class="bx bx-store me-1"></i>
              Lihat Supplier
            </a>
            @endif
            <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">
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
  <!-- Transaction Details -->
  <div class="col-xl-8 mb-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Detail Transaksi
        </h5>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <!-- Item Information -->
          <div class="col-md-6">
            <h6 class="text-muted mb-3">Informasi Item</h6>
            <div class="d-flex align-items-center mb-3">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ $transaction->item->stock_status_color }}">
                  <i class="bx bx-package"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-1">
                  <a href="{{ route('items.show', $transaction->item) }}" class="text-decoration-none">
                    {{ $transaction->item->item_name }}
                  </a>
                </h6>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-label-dark">{{ $transaction->item->sku }}</span>
                  <span class="badge bg-label-secondary">{{ $transaction->item->category->category_name ?? 'Tidak ada kategori' }}</span>
                </div>
              </div>
            </div>
            
            <div class="list-group list-group-flush">
              <div class="list-group-item px-0 py-2 border-0">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Unit:</span>
                  <span class="fw-semibold">{{ $transaction->item->unit }}</span>
                </div>
              </div>
              <div class="list-group-item px-0 py-2 border-0">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Stok Saat Ini:</span>
                  <span class="fw-semibold text-{{ $transaction->item->stock_status_color }}">
                    {{ number_format($transaction->item->current_stock, 2) }}
                  </span>
                </div>
              </div>
              <div class="list-group-item px-0 py-2 border-0">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Batas Minimum:</span>
                  <span class="fw-semibold">{{ number_format($transaction->item->low_stock_threshold, 2) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Supplier Information -->
          <div class="col-md-6">
            <h6 class="text-muted mb-3">Informasi Supplier</h6>
            @if($transaction->supplier)
            <div class="d-flex align-items-center mb-3">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="bx bx-store"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-1">
                  <a href="{{ route('suppliers.show', $transaction->supplier) }}" class="text-decoration-none">
                    {{ $transaction->supplier->supplier_name }}
                  </a>
                </h6>
                @if($transaction->supplier->contact_person)
                <small class="text-muted">{{ $transaction->supplier->contact_person }}</small>
                @endif
              </div>
            </div>
            
            <div class="list-group list-group-flush">
              @if($transaction->supplier->phone)
              <div class="list-group-item px-0 py-2 border-0">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Telepon:</span>
                  <div>
                    <span class="fw-semibold">{{ $transaction->supplier->phone }}</span>
                    <a href="tel:{{ $transaction->supplier->phone }}" class="btn btn-outline-success btn-xs ms-2">
                      <i class="bx bx-phone"></i>
                    </a>
                  </div>
                </div>
              </div>
              @endif
              @if($transaction->supplier->address)
              <div class="list-group-item px-0 py-2 border-0">
                <div>
                  <span class="text-muted d-block mb-1">Alamat:</span>
                  <p class="mb-0">{{ $transaction->supplier->address }}</p>
                </div>
              </div>
              @endif
              <div class="list-group-item px-0 py-2 border-0">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Total Items:</span>
                  <span class="fw-semibold">{{ $transaction->supplier->items()->count() }}</span>
                </div>
              </div>
              <div class="list-group-item px-0 py-2 border-0">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Total Transaksi:</span>
                  <span class="fw-semibold">{{ $transaction->supplier->stockTransactions()->count() }}</span>
                </div>
              </div>
            </div>
            @else
            <div class="text-center py-4">
              <i class="bx bx-store text-muted" style="font-size: 48px;"></i>
              <h6 class="mt-2 text-muted">Tanpa Supplier</h6>
              <p class="text-muted mb-0">Transaksi ini tidak dikaitkan dengan supplier manapun</p>
            </div>
            @endif
          </div>
        </div>

        <!-- Transaction Impact -->
        <div class="border-top pt-4">
          <h6 class="text-muted mb-3">Dampak Transaksi</h6>
          
          @php
            $balance = $transaction->item->currentBalance;
            $beforeStock = 0;
            $afterStock = 0;
            
            if ($balance) {
              switch ($transaction->transaction_type) {
                case 'IN':
                  $beforeStock = $balance->closing_stock - $transaction->quantity;
                  $afterStock = $balance->closing_stock;
                  break;
                case 'OUT':
                  $beforeStock = $balance->closing_stock + $transaction->quantity;
                  $afterStock = $balance->closing_stock;
                  break;
                case 'ADJUSTMENT':
                  // Untuk adjustment, kita perlu melihat history
                  $beforeStock = $balance->closing_stock; // Simplified
                  $afterStock = $balance->closing_stock;
                  break;
              }
            }
          @endphp
          
          <div class="alert alert-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }}" role="alert">
            <div class="d-flex align-items-center mb-2">
              <i class="bx {{ $transaction->transaction_type == 'IN' ? 'bx-plus-circle' : ($transaction->transaction_type == 'OUT' ? 'bx-minus-circle' : 'bx-transfer') }} me-2" style="font-size: 20px;"></i>
              <h6 class="mb-0">{{ $transaction->transaction_type_text }}</h6>
            </div>
            
            <div class="row">
              <div class="col-md-4">
                <small class="d-block">Jumlah Transaksi:</small>
                <strong class="text-{{ $transaction->transaction_type == 'IN' ? 'success' : ($transaction->transaction_type == 'OUT' ? 'danger' : 'warning') }}">
                  {{ $transaction->formatted_quantity }} {{ $transaction->item->unit }}
                </strong>
              </div>
              @if($balance)
              <div class="col-md-4">
                <small class="d-block">Stok Sebelum:</small>
                <strong>{{ number_format($beforeStock, 2) }} {{ $transaction->item->unit }}</strong>
              </div>
              <div class="col-md-4">
                <small class="d-block">Stok Sesudah:</small>
                <strong class="text-{{ $afterStock <= 0 ? 'danger' : ($afterStock <= $transaction->item->low_stock_threshold ? 'warning' : 'success') }}">
                  {{ number_format($afterStock, 2) }} {{ $transaction->item->unit }}
                </strong>
              </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Transaction Notes -->
        <div class="border-top pt-4">
          <h6 class="text-muted mb-3">Catatan Transaksi</h6>
          <div class="alert alert-light">
            <i class="bx bx-note me-2"></i>
            {{ $transaction->notes }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar Information -->
  <div class="col-xl-4">
    <!-- Transaction Summary -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-chart me-2"></i>
          Ringkasan Transaksi
        </h6>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">ID Transaksi:</span>
              <span class="fw-semibold">#{{ $transaction->id }}</span>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Tipe:</span>
              <span class="badge bg-{{ $transaction->transaction_type_color }}">{{ $transaction->transaction_type_text }}</span>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Tanggal:</span>
              <span class="fw-semibold">{{ $transaction->transaction_date->format('d/m/Y H:i') }}</span>
            </div>
          </div>
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Dibuat:</span>
              <span class="fw-semibold">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
            </div>
          </div>
          @if($transaction->user)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Oleh User:</span>
              <span class="fw-semibold">{{ $transaction->user->name }}</span>
            </div>
          </div>
          @endif
          @if($transaction->supplier)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Supplier:</span>
              <span class="fw-semibold text-success">{{ $transaction->supplier->supplier_name }}</span>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Related Transactions -->
    @php
      $relatedTransactions = $transaction->item->stockTransactions()
                           ->where('id', '!=', $transaction->id)
                           ->with(['supplier', 'user'])
                           ->latest()
                           ->take(5)
                           ->get();
    @endphp
    
    @if($relatedTransactions->count() > 0)
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-history me-2"></i>
          Transaksi Terkait
        </h6>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          @foreach($relatedTransactions as $related)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <span class="badge bg-{{ $related->transaction_type_color }} me-2">
                  {{ $related->transaction_type }}
                </span>
                <div>
                  <small class="fw-semibold">{{ $related->formatted_quantity }}</small>
                  <br><small class="text-muted">{{ $related->created_at->diffForHumans() }}</small>
                  @if($related->supplier)
                  <br><small class="text-success">{{ $related->supplier->supplier_name }}</small>
                  @endif
                </div>
              </div>
              <a href="{{ route('stock-transactions.show', $related) }}" class="btn btn-outline-primary btn-sm">
                <i class="bx bx-show"></i>
              </a>
            </div>
          </div>
          @endforeach
        </div>
        
        @if($transaction->item->stockTransactions()->count() > 6)
        <div class="text-center mt-3">
          <a href="{{ route('stock-transactions.index', ['item_id' => $transaction->item->id]) }}" class="btn btn-outline-info btn-sm">
            <i class="bx bx-history me-1"></i>
            Lihat Semua Transaksi Item
          </a>
        </div>
        @endif
      </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-flash me-2"></i>
          Quick Actions
        </h6>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('stock-transactions.create', ['item' => $transaction->item->id]) }}" class="btn btn-primary btn-sm">
            <i class="bx bx-plus me-1"></i>
            Transaksi Baru Item Ini
          </a>
          @if($transaction->supplier)
          <a href="{{ route('stock-transactions.create', ['supplier' => $transaction->supplier->id]) }}" class="btn btn-outline-success btn-sm">
            <i class="bx bx-store me-1"></i>
            Transaksi Dengan Supplier Ini
          </a>
          @endif
          <a href="{{ route('items.show', $transaction->item) }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-package me-1"></i>
            Detail Item
          </a>
          @if($transaction->supplier)
          <a href="{{ route('suppliers.show', $transaction->supplier) }}" class="btn btn-outline-success btn-sm">
            <i class="bx bx-store me-1"></i>
            Detail Supplier
          </a>
          @endif
          <a href="{{ route('stock-transactions.index', ['item_id' => $transaction->item->id]) }}" class="btn btn-outline-info btn-sm">
            <i class="bx bx-history me-1"></i>
            Riwayat Transaksi Item
          </a>
          @if($transaction->supplier)
          <a href="{{ route('stock-transactions.index', ['supplier_id' => $transaction->supplier->id]) }}" class="btn btn-outline-success btn-sm">
            <i class="bx bx-history me-1"></i>
            Riwayat Transaksi Supplier
          </a>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  font-size: 18px;
}

.btn-xs {
  padding: 0.125rem 0.25rem;
  font-size: 0.75rem;
  line-height: 1;
  border-radius: 0.25rem;
}

.list-group-item {
  transition: background-color 0.15s ease-in-out;
}

.list-group-item:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.alert {
  border-left: 4px solid;
}

.alert-success {
  border-left-color: #71dd37;
}

.alert-danger {
  border-left-color: #ff3e1d;
}

.alert-warning {
  border-left-color: #ffab00;
}

.alert-light {
  border-left-color: #8592a3;
}
</style>
@endpush
@extends('layouts.admin')

@section('title', 'Transaksi Stok Central Warehouse')

@section('content')
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <form class="row g-3" method="GET">
          <div class="col-md-3">
            <label class="form-label">Warehouse</label>
            <select class="form-select" name="warehouse_id">
              <option value="{{ request('warehouse_id') }}">{{ request('warehouse_name') }}</option>
              @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>
                  {{ $warehouse->warehouse_name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tipe Transaksi</label>
            <select class="form-select" name="transaction_type">
              <option value="">Semua</option>
              @foreach($transactionTypes as $key => $label)
                <option value="{{ $key }}" @selected(request('transaction_type') === $key)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" class="form-control" name="date_start" value="{{ request('date_start') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Tanggal Akhir</label>
            <input type="date" class="form-control" name="date_end" value="{{ request('date_end') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Kata Kunci</label>
            <input type="text" class="form-control" name="search" placeholder="Referensi / Item" value="{{ request('search') }}">
          </div>
          <div class="col-12 d-flex justify-content-end">
            <a href="{{ route('central-warehouse.transactions') }}" class="btn btn-outline-secondary me-2">Reset</a>
            <button class="btn btn-primary">Filter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-3">
    <div class="card h-100">
      <div class="card-body">
        <span class="fw-semibold d-block text-muted mb-1">Total Transaksi</span>
        <h3 class="mb-0">{{ number_format($summary['total_transactions']) }}</h3>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 mb-3">
    <div class="card h-100">
      <div class="card-body">
        <span class="fw-semibold d-block text-muted mb-1">Total Masuk</span>
        <h3 class="text-success mb-0">{{ number_format($summary['total_quantity_in'], 2) }}</h3>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 mb-3">
    <div class="card h-100">
      <div class="card-body">
        <span class="fw-semibold d-block text-muted mb-1">Total Keluar</span>
        <h3 class="text-danger mb-0">{{ number_format($summary['total_quantity_out'], 2) }}</h3>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 mb-3">
    <div class="card h-100">
      <div class="card-body">
        <span class="fw-semibold d-block text-muted mb-1">Total Nilai</span>
        <h3 class="text-primary mb-0">Rp {{ number_format($summary['total_value'], 0, ',', '.') }}</h3>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <thead class="table-light">
        <tr>
          <th>Tanggal</th>
          <th>Referensi</th>
          <th>Item</th>
          <th>Warehouse</th>
          <th>Tipe</th>
          <th>Qty</th>
          <th>Total</th>
          <th>Petugas</th>
        </tr>
      </thead>
      <tbody>
        @forelse($transactions as $transaction)
          <tr>
            <td>{{ $transaction->transaction_date->format('d M Y H:i') }}</td>
            <td class="fw-semibold">{{ $transaction->reference_no }}</td>
            <td>
              <div class="d-flex flex-column">
                <span>{{ $transaction->item->item_name ?? '-' }}</span>
                <small class="text-muted">{{ $transaction->item->sku ?? $transaction->item->sku ?? '' }}</small>
              </div>
            </td>
            <td>{{ $transaction->warehouse->warehouse_name ?? '-' }}</td>
            <td>{!! $transaction->type_badge !!}</td>
            <td class="text-end">
              <span class="{{ $transaction->is_incoming ? 'text-success' : 'text-danger' }}">
                {{ number_format($transaction->quantity, 2) }}
              </span>
            </td>
            <td class="text-end">Rp {{ number_format($transaction->total_cost, 0, ',', '.') }}</td>
            <td>
              <div class="d-flex flex-column">
                <span>{{ $transaction->user->full_name ?? '-' }}</span>
                @if($transaction->supplier)
                  <small class="text-muted">Sup: {{ $transaction->supplier->supplier_name }}</small>
                @endif
                @if($transaction->targetBranch)
                  <small class="text-muted">Cabang: {{ $transaction->targetBranch->branch_name }}</small>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-4">Tidak ada transaksi untuk filter ini.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <x-simple-pagination :items="$transactions" type="transaction" />

</div>
@endsection
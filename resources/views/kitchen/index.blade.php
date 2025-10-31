<!-- -- Active: 1746074663200@@127.0.0.1@3306 -->
@extends('layouts.admin')

@section('title', 'Stock Dapur - Chicking BJM')

@section('content')
<div class="row">
    <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Item Dapur</li>
      </ol>
    </nav>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Item</span>
                        <h4 class="mb-3">{{ number_format($stats['total_items']) }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-primary">
                            <span class="avatar-title bg-primary rounded-circle">
                                <i class="bx bx-package text-white font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Stock Tersedia</span>
                        <h4 class="mb-3 text-success">{{ number_format($stats['available_items']) }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-success">
                            <span class="avatar-title bg-success rounded-circle">
                                <i class="bx bx-check-circle text-white font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Stock Menipis</span>
                        <h4 class="mb-3 text-warning">{{ number_format($stats['low_stock_items']) }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-warning">
                            <span class="avatar-title bg-warning rounded-circle">
                                <i class="bx bx-error text-white font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Stock Habis</span>
                        <h4 class="mb-3 text-danger">{{ number_format($stats['out_of_stock_items']) }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-danger">
                            <span class="avatar-title bg-danger rounded-circle">
                                <i class="bx bx-x-circle text-white font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Daftar Stock Dapur</h4>
                    </div>
                    <div class="col-auto">
                        <div class="d-flex gap-2">
                            <a href="{{ route('kitchen.transfer') }}" class="btn btn-primary">
                                <i class="bx bx-transfer me-1"></i> Transfer dari Gudang
                            </a>
                            <a href="{{ route('kitchen.usage') }}" class="btn btn-danger">
                                <i class="bx bx-minus me-1"></i> Catat Penggunaan
                            </a>
                            <a href="{{ route('kitchen.adjustment') }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Penyesuaian
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Filters -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Pencarian</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                               placeholder="Nama item atau SKU...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="category_id">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Stock</label>
                        <select class="form-select" name="stock_status">
                            <option value="">Semua Status</option>
                            <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>
                                Stock Tersedia
                            </option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>
                                Stock Menipis
                            </option>
                            <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>
                                Stock Habis
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('kitchen.index') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh me-1"></i> Reset
                        </a>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>SKU</th>
                                <th>Nama Item</th>
                                <th>Kategori</th>
                                <th>Unit</th>
                                <th>Stock Dapur</th>
                                <th>Status</th>
                                <th>Last Update</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                @php
                                    $kitchenStock = $item->current_kitchen_stock;
                                    $status = $item->kitchen_stock_status;
                                    $statusColor = $item->kitchen_stock_status_color;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $item->sku }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $item->item_name }}</strong>
                                    </td>
                                    <td>{{ $item->category ? $item->category->category_name : '-' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $item->unit }}</span>
                                    </td>
                                    <td>
                                        <h5 class="text-{{ $statusColor }} mb-0">
                                            {{ number_format($kitchenStock, 1) }}
                                        </h5>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusColor }}">{{ $status }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $item->currentKitchenBalance?->updated_at?->format('d/m/Y H:i') ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                Aksi
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('kitchen.transfer') }}?item_id={{ $item->id }}">
                                                        <i class="bx bx-transfer me-1"></i> Transfer
                                                    </a>
                                                </li>
                                                @if($kitchenStock > 0)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('kitchen.usage') }}?item_id={{ $item->id }}">
                                                        <i class="bx bx-minus me-1"></i> Catat Penggunaan
                                                    </a>
                                                </li>
                                                @endif
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('kitchen.adjustment') }}?item_id={{ $item->id }}">
                                                        <i class="bx bx-edit me-1"></i> Penyesuaian
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bx bx-package font-size-24 d-block mb-2"></i>
                                            Tidak ada data stock dapur
                                        </div>
                                        <a href="{{ route('kitchen.transfer') }}" class="btn btn-primary btn-sm mt-2">
                                            <i class="bx bx-plus me-1"></i> Transfer Item Pertama
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($items->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">🚀 Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('kitchen.transfer') }}" class="btn btn-outline-primary w-100">
                            <i class="bx bx-transfer d-block font-size-20 mb-1"></i>
                            Transfer dari Gudang
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('kitchen.usage') }}" class="btn btn-outline-danger w-100">
                            <i class="bx bx-minus d-block font-size-20 mb-1"></i>
                            Catat Penggunaan
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('kitchen.transactions') }}" class="btn btn-outline-info w-100">
                            <i class="bx bx-list-ul d-block font-size-20 mb-1"></i>
                            History Transaksi
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('kitchen.report') }}" class="btn btn-outline-success w-100">
                            <i class="bx bx-bar-chart d-block font-size-20 mb-1"></i>
                            Laporan Bulanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto refresh setiap 5 menit
setTimeout(function() {
    location.reload();
}, 300000);

// Alert untuk low stock
@if($stats['low_stock_items'] > 0 || $stats['out_of_stock_items'] > 0)
    @if($stats['out_of_stock_items'] > 0)
        Swal.fire({
            icon: 'error',
            title: 'Peringatan Stock Habis!',
            html: '{{ $stats["out_of_stock_items"] }} item di dapur sudah habis.<br>Segera lakukan transfer dari gudang.',
            confirmButtonText: 'Transfer Sekarang',
            showCancelButton: true,
            cancelButtonText: 'Nanti'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("kitchen.transfer") }}';
            }
        });
    @elseif($stats['low_stock_items'] > 0)
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan Stock Menipis!',
            html: '{{ $stats["low_stock_items"] }} item di dapur stock-nya menipis.<br>Pertimbangkan untuk transfer dari gudang.',
            confirmButtonText: 'Transfer Sekarang',
            showCancelButton: true,
            cancelButtonText: 'Nanti'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("kitchen.transfer") }}';
            }
        });
    @endif
@endif
</script>
@endpush
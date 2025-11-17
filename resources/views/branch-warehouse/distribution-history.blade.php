@extends('layouts.admin')

@section('title', 'Riwayat Distribusi - ' . $warehouse->warehouse_name)

@section('content')
{{-- Breadcrumb --}}
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('branch-warehouse.index') }}">Branch Warehouse</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('branch-warehouse.show', $warehouse->id) }}">{{ $warehouse->warehouse_name }}</a>
                </li>
                <li class="breadcrumb-item active">Riwayat Distribusi</li>
            </ol>
        </nav>
    </div>
</div>


<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="text-white mb-2">
                            <i class='bx bx-package me-2'></i>{{ $warehouse->warehouse_name }}
                        </h4>
                        <p class="mb-0 opacity-75">
                            <i class='bx bx-map me-1'></i>{{ $warehouse->address }}
                            @if($warehouse->branch)
                                <span class="ms-3">
                                    <i class='bx bx-building me-1'></i>{{ $warehouse->branch->branch_name }}
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('branch-warehouse.show', $warehouse->id) }}" class="btn btn-light">
                            <i class='bx bx-arrow-back me-1'></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-filter me-2'></i>Filter Riwayat Distribusi
                </h5>
            </div>
            <div class="card-body">
                <form class="row g-3" method="GET">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" 
                               class="form-control" 
                               name="date_start" 
                               value="{{ request('date_start', now()->subDays(30)->format('Y-m-d')) }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" 
                               class="form-control" 
                               name="date_end" 
                               value="{{ request('date_end', now()->format('Y-m-d')) }}">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Cari</label>
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Referensi / Catatan" 
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Per Halaman</label>
                        <select class="form-select" name="per_page">
                            <option value="20" @selected(request('per_page', 20) == 20)>20</option>
                            <option value="50" @selected(request('per_page') == 50)>50</option>
                            <option value="100" @selected(request('per_page') == 100)>100</option>
                        </select>
                    </div>
                    
                    <div class="col-12 d-flex justify-content-end">
                        <a href="{{ route('branch-warehouse.distributions', $warehouse->id) }}" 
                           class="btn btn-outline-secondary me-2">
                            <i class='bx bx-reset me-1'></i>Reset
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-search me-1'></i>Filter
                        </button>
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
                <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class='bx bx-package fs-4'></i>
                        </span>
                    </div>
                    <div>
                        <span class="fw-semibold d-block text-muted mb-1">Total Distribusi</span>
                        <h4 class="mb-0">{{ number_format($distributions->total()) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class='bx bx-trending-up fs-4'></i>
                        </span>
                    </div>
                    <div>
                        <span class="fw-semibold d-block text-muted mb-1">Total Quantity</span>
                        <h4 class="mb-0">{{ number_format($distributions->sum('quantity'), 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class='bx bx-calendar fs-4'></i>
                        </span>
                    </div>
                    <div>
                        <span class="fw-semibold d-block text-muted mb-1">Periode</span>
                        <h6 class="mb-0">
                            {{ request('date_start', now()->subDays(30)->format('d/m/Y')) }}
                            <br>
                            <small class="text-muted">s/d</small>
                            {{ request('date_end', now()->format('d/m/Y')) }}
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class='bx bx-box fs-4'></i>
                        </span>
                    </div>
                    <div>
                        <span class="fw-semibold d-block text-muted mb-1">Unique Items</span>
                        <h4 class="mb-0">{{ $distributions->pluck('item_id')->unique()->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class='bx bx-list-ul me-2'></i>Daftar Distribusi
        </h5>
        <div>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class='bx bx-printer me-1'></i>Print
            </button>
            <a href="{{ route('branch-warehouse.distribute-form', $warehouse->id) }}" 
               class="btn btn-sm btn-primary">
                <i class='bx bx-plus me-1'></i>Distribusi Baru
            </a>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th width="5%">#</th>
                    <th width="12%">Tanggal</th>
                    <th width="15%">Referensi</th>
                    <th width="15%">Type</th>
                    <th width="25%">Item</th>
                    <th width="10%" class="text-end">Quantity</th>
                    <th width="20%">Catatan</th>
                    <th width="13%">Petugas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($distributions as $index => $distribution)
                    <tr>
                        <td>{{ $distributions->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold">{{ $distribution->transaction_date->format('d M Y') }}</span>
                                <small class="text-muted">{{ $distribution->transaction_date->format('H:i') }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-primary">{{ $distribution->reference_no }}</span>
                        </td>
                        <td>
                            @php
                            $typeConfig = [
                                'IN' => ['color' => 'success', 'icon' => 'bx-import', 'label' => 'IN'],
                                'OUT' => ['color' => 'danger', 'icon' => 'bx-export', 'label' => 'OUT'],
                                'RECEIVE_FROM_CENTRAL' => ['color' => 'info', 'icon' => 'bx-import', 'label' => 'From Central'],
                                'TRANSFER_TO_KITCHEN' => ['color' => 'primary', 'icon' => 'bx-send', 'label' => 'To Kitchen'],
                                'TRANSFER_TO_CENTRAL' => ['color' => 'warning', 'icon' => 'bx-export', 'label' => 'To Central'],
                                'WASTAGE' => ['color' => 'danger', 'icon' => 'bx-trash', 'label' => 'Waste'],
                                'ADJUSTMENT_IN' => ['color' => 'info', 'icon' => 'bx-edit', 'label' => 'Adj. IN'],
                                'ADJUSTMENT_OUT' => ['color' => 'warning', 'icon' => 'bx-edit', 'label' => 'Adj. OUT'],
                            ];
                            $config = $typeConfig[$distribution->transaction_type] ?? ['color' => 'secondary', 'icon' => 'bx-transfer', 'label' => $distribution->transaction_type];
                            @endphp
                            <span class="badge bg-{{ $config['color'] }}">
                            <i class="bx {{ $config['icon'] }} me-1"></i>
                            {{ $config['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold">{{ $distribution->item->item_name ?? '-' }}</span>
                                <small class="text-muted">
                                    <i class='bx bx-barcode me-1'></i>{{ $distribution->item->sku ?? '-' }}
                                    <span class="ms-2">
                                        <i class='bx bx-package me-1'></i>{{ $distribution->item->unit ?? '-' }}
                                    </span>
                                </small>
                            </div>
                        </td>
                        <td class="text-end">
                            <span class="badge bg-label-danger fs-6">
                                {{ number_format($distribution->quantity, 2) }}
                            </span>
                        </td>
                        <td>
                            @if($distribution->notes)
                                <small class="text-muted" title="{{ $distribution->notes }}">
                                    {{ Str::limit($distribution->notes, 50) }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold">{{ $distribution->user->full_name ?? '-' }}</span>
                                <small class="text-muted">{{ $distribution->user->role ?? '-' }}</small>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class='bx bx-box fs-1 text-muted mb-3'></i>
                                <h5 class="text-muted">Tidak ada riwayat distribusi</h5>
                                <p class="text-muted mb-3">Belum ada distribusi untuk periode ini</p>
                                <a href="{{ route('branch-warehouse.show-distribution-form', $warehouse->id) }}" 
                                   class="btn btn-primary">
                                    <i class='bx bx-plus me-1'></i>Buat Distribusi
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
<x-simple-pagination :items="$distributions" type="distribution" />
</div>
<!-- @if($distributions->isNotEmpty())
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class='bx bx-pie-chart-alt me-2'></i>Top 5 Items Terdistribusi
                </h5>
            </div>
            <div class="card-body">
                @php
                    $topItems = $distributions->groupBy('item_id')
                        ->map(function($group) {
                            return [
                                'item' => $group->first()->item,
                                'total_qty' => $group->sum('quantity'),
                                'count' => $group->count()
                            ];
                        })
                        ->sortByDesc('total_qty')
                        ->take(5);
                @endphp
                
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Total Qty</th>
                                <th class="text-end">Frekuensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topItems as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item['item']->item_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $item['item']->sku }}</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-label-primary">
                                            {{ number_format($item['total_qty'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-label-info">
                                            {{ $item['count'] }}x
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif -->

@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #696cff 0%, #5a5cff 100%);
    }
    
    @media print {
        .card-header .btn,
        .breadcrumb,
        .card-footer,
        nav {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto submit form on date change
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Optional: Auto-submit on date change
            // this.closest('form').submit();
        });
    });
    
    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
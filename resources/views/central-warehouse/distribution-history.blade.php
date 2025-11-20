@extends('layouts.admin')

@section('title', 'Riwayat Distribusi Barang')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form class="row g-3" method="GET" action="{{ route('central-warehouse.distribution-history') }}">
                    {{-- Filter Gudang Tujuan (Cabang) --}}
                    <div class="col-md-3">
                        <label class="form-label">Cabang Tujuan</label>
                        <select class="form-select" name="branch_id">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>
                                    {{ $branch->warehouse_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Tanggal Mulai --}}
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                    </div>

                    {{-- Filter Tanggal Akhir --}}
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                    </div>

                    {{-- Filter Pencarian --}}
                    <div class="col-md-3">
                        <label class="form-label">Pencarian</label>
                        <input type="text" class="form-control" name="search" placeholder="No Ref / Nama Barang" value="{{ request('search') }}">
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-flex w-100 gap-2">
                            <a href="{{ route('central-warehouse.distribution-history') }}" class="btn btn-outline-secondary w-50">Reset</a>
                            <button type="submit" class="btn btn-primary w-50">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-transparent py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Riwayat Distribusi</h5>
            <!-- {{-- <a href="#" class="btn btn-sm btn-success"><i class="bi bi-file-excel"></i> Export Excel</a> --}} -->
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Referensi</th>
                    <th>Tujuan (Cabang)</th>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Status</th>
                    <th>Petugas</th>
                    <th>Petugas Branch</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $history)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($history->transaction_date)->format('d M Y') }}
                            <br>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($history->transaction_date)->format('H:i') }}</small>
                        </td>

                        <td>
                            <span class="fw-semibold text-primary">{{ $history->reference_no }}</span>
                        </td>

                        <td>
                            {{ $history->branchWarehouse->warehouse_name ?? '-' }}
                        </td>

                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-medium">{{ $history->item->item_name ?? '-' }}</span>
                                <small class="text-muted">{{ $history->item->item_code ?? '' }}</small>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="fw-bold">{{ number_format($history->quantity, 0, ',', '.') }}</span>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $history->item->unit->name ?? 'Unit' }}</small>
                        </td>

                        <td class="text-center">
                            @php
                                $statusClass = match($history->status) {
                                    'COMPLETED' => 'success', 
                                    'PENDING' => 'warning',   
                                    'CANCELLED' => 'danger',  
                                    default => 'secondary'
                                };
                                
                                $statusLabel = match($history->status) {
                                    'COMPLETED' => 'Diterima',
                                    'PENDING' => 'PENDING',
                                    'CANCELLED' => 'Dibatalkan',
                                    default => $history->status
                                };
                            @endphp
                            <span class="badge bg-soft-{{ $statusClass }} text-{{ $statusClass }} rounded-pill">
                                {{ $statusLabel }}
                            </span>
                        </td>

                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    {{ $history->user->full_name ?? 'System' }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    {{ $history->approver->full_name ?? '-' }}
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($history->notes)
                                <span class="d-inline-block text-truncate" style="max-width: 150px;" data-bs-toggle="tooltip" title="{{ $history->notes }}">
                                    {{ $history->notes }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-3"></i>
                                Tidak ada riwayat distribusi yang ditemukan untuk filter ini.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-3 border-top-0">
            <x-simple-pagination :items="$histories" type="histories" />
    </div>
</div>
@endsection
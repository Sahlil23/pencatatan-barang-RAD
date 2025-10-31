@extends('layouts.admin')

@section('title', 'Transaksi Stock Dapur - Chicking BJM')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('kitchen.index') }}">Stock Dapur</a></li>
                    <li class="breadcrumb-item active">Transaksi</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="bx bx-filter-alt me-2"></i>
                        Filter Transaksi
                    </h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
                        <i class="bx bx-reset me-1"></i>Reset Filter
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('kitchen.transactions') }}" id="filterForm">
                    <div class="row g-3">
                        <!-- Date Range -->
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="{{ request('start_date') }}">
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="{{ request('end_date') }}">
                        </div>
                        
                        <!-- Transaction Type -->
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label">Jenis Transaksi</label>
                            <select class="form-select" name="transaction_type">
                                <option value="">Semua Jenis</option>
                                <option value="TRANSFER_IN" {{ request('transaction_type') == 'TRANSFER_IN' ? 'selected' : '' }}>
                                    Transfer Masuk
                                </option>
                                <option value="USAGE" {{ request('transaction_type') == 'USAGE' ? 'selected' : '' }}>
                                    Penggunaan
                                </option>
                                <option value="ADJUSTMENT" {{ request('transaction_type') == 'ADJUSTMENT' ? 'selected' : '' }}>
                                    Penyesuaian
                                </option>
                            </select>
                        </div>
                        
                        <!-- Item Filter -->
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label">Item</label>
                            <select class="form-select" name="item_id">
                                <option value="">Semua Item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->item_name }} ({{ $item->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Search -->
                        <div class="col-xl-6 col-lg-8 col-md-12">
                            <label class="form-label">Pencarian</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari berdasarkan keterangan, nama item, atau SKU..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Quick Date Filters -->
                        <div class="col-xl-6 col-lg-4 col-md-12">
                            <label class="form-label">Filter Cepat</label>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm quick-filter" data-days="1">
                                    Hari Ini
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm quick-filter" data-days="7">
                                    7 Hari
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm quick-filter" data-days="30">
                                    30 Hari
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm quick-filter" data-month="current">
                                    Bulan Ini
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        Daftar Transaksi
                        @if($transactions->total() > 0)
                            <span class="badge bg-primary ms-2">{{ $transactions->total() }} transaksi</span>
                        @endif
                    </h5>
                    <div class="d-flex gap-2">
                        <!-- Export Buttons -->
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-download me-1"></i>Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportToExcel()">
                                    <i class="bx bx-file-excel me-2"></i>Excel
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportToPDF()">
                                    <i class="bx bx-file-pdf me-2"></i>PDF
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="printTransactions()">
                                    <i class="bx bx-printer me-2"></i>Print
                                </a></li>
                            </ul>
                        </div>
                        
                        <!-- Action Buttons -->
                        <a href="{{ route('kitchen.transfer') }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-transfer me-1"></i>Transfer
                        </a>
                        <a href="{{ route('kitchen.usage') }}" class="btn btn-danger btn-sm">
                            <i class="bx bx-minus me-1"></i>Penggunaan
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                @if($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="12%">Tanggal</th>
                                    <th width="15%">Item</th>
                                    <th width="10%">Jenis</th>
                                    <th width="10%" class="text-end">Quantity</th>
                                    <th width="25%">Keterangan</th>
                                    <th width="13%">User</th>
                                    <th width="10%" class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $index => $transaction)
                                    <tr>
                                        <td class="text-muted">
                                            {{ $transactions->firstItem() + $index }}
                                        </td>
                                        
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">
                                                    {{ $transaction->transaction_date->format('d/m/Y') }}
                                                </span>
                                                <small class="text-muted">
                                                    {{ $transaction->transaction_date->format('H:i') }}
                                                </small>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <span class="avatar-initial rounded bg-label-{{ $transaction->item->kitchen_stock_status_color ?? 'primary' }}">
                                                        <i class="bx bx-package"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ Str::limit($transaction->item->item_name, 20) }}</h6>
                                                    <small class="text-muted">
                                                        {{ $transaction->item->sku }}
                                                        @if($transaction->item->category)
                                                            • {{ $transaction->item->category->category_name }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            @php
                                                $typeConfig = [
                                                    'TRANSFER_IN' => ['badge' => 'success', 'icon' => 'bx-transfer', 'text' => 'Transfer Masuk'],
                                                    'USAGE' => ['badge' => 'danger', 'icon' => 'bx-minus', 'text' => 'Penggunaan'],
                                                    'ADJUSTMENT' => ['badge' => 'warning', 'icon' => 'bx-edit-alt', 'text' => 'Penyesuaian']
                                                ];
                                                $config = $typeConfig[$transaction->transaction_type] ?? ['badge' => 'secondary', 'icon' => 'bx-help-circle', 'text' => $transaction->transaction_type];
                                            @endphp
                                            <span class="badge bg-{{ $config['badge'] }}">
                                                <i class="bx {{ $config['icon'] }} me-1"></i>
                                                {{ $config['text'] }}
                                            </span>
                                        </td>
                                        
                                        <td class="text-end">
                                            <div class="d-flex flex-column align-items-end">
                                                <span class="fw-bold text-{{ $transaction->transaction_type == 'USAGE' ? 'danger' : 'success' }}">
                                                    {{ $transaction->transaction_type == 'USAGE' ? '-' : '+' }}{{ number_format($transaction->quantity, 1) }}
                                                </span>
                                                <small class="text-muted">{{ $transaction->item->unit }}</small>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $transaction->notes }}">
                                                {{ $transaction->notes ?: '-' }}
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs flex-shrink-0 me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-info">
                                                        {{ strtoupper(substr($transaction->user->name ?? 'System', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <small class="fw-semibold">{{ $transaction->user->name ?? 'System' }}</small>
                                                    <br><small class="text-muted">{{ $transaction->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <button class="dropdown-item" onclick="showTransactionDetail({{ $transaction->id }})">
                                                            <i class="bx bx-show me-2"></i>Lihat Detail
                                                        </button>
                                                    </li>
                                                    @if($transaction->warehouse_transaction_id)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('stock-transactions.show', $transaction->warehouse_transaction_id) }}">
                                                                <i class="bx bx-link-external me-2"></i>Transaksi Gudang
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li><hr class="dropdown-divider"></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <p class="text-muted mb-0">
                                Menampilkan {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} 
                                dari {{ $transactions->total() }} transaksi
                            </p>
                        </div>
                        <div>
                            {{ $transactions->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bx bx-receipt" style="font-size: 64px; color: #ddd;"></i>
                        </div>
                        <h5 class="text-muted">Tidak Ada Transaksi</h5>
                        <p class="text-muted mb-4">
                            @if(request()->hasAny(['start_date', 'end_date', 'transaction_type', 'item_id', 'search']))
                                Tidak ada transaksi yang sesuai dengan filter yang dipilih.
                                <br><a href="{{ route('kitchen.transactions') }}" class="text-primary">Reset filter</a> untuk melihat semua transaksi.
                            @else
                                Belum ada transaksi stock dapur yang tercatat.
                            @endif
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('kitchen.transfer') }}" class="btn btn-primary">
                                <i class="bx bx-transfer me-1"></i>Transfer Stock
                            </a>
                            <a href="{{ route('kitchen.usage') }}" class="btn btn-danger">
                                <i class="bx bx-minus me-1"></i>Catat Penggunaan
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Transaction Detail Modal -->
<div class="modal fade" id="transactionDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transactionDetailContent">
                <!-- Content will be loaded dynamically -->
                <div class="d-flex justify-content-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="bx bx-error-circle text-danger" style="font-size: 64px;"></i>
                    <h5 class="mt-3">Hapus Transaksi?</h5>
                    <p class="text-muted">
                        Tindakan ini tidak dapat dibatalkan. Transaksi akan dihapus secara permanen 
                        dan stock akan dikembalikan ke kondisi sebelum transaksi.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bx bx-trash me-1"></i>Hapus Transaksi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick date filters
    document.querySelectorAll('.quick-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            const days = this.dataset.days;
            const month = this.dataset.month;
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');
            
            const today = new Date();
            const todayStr = today.toISOString().split('T')[0];
            
            if (days) {
                const startDate = new Date();
                startDate.setDate(today.getDate() - parseInt(days) + 1);
                const startDateStr = startDate.toISOString().split('T')[0];
                
                startDateInput.value = startDateStr;
                endDateInput.value = todayStr;
            } else if (month === 'current') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                const firstDayStr = firstDay.toISOString().split('T')[0];
                
                startDateInput.value = firstDayStr;
                endDateInput.value = todayStr;
            }
            
            // Submit form
            document.getElementById('filterForm').submit();
        });
    });
    
    // Reset filters
    document.getElementById('resetFilters').addEventListener('click', function() {
        window.location.href = '{{ route("kitchen.transactions") }}';
    });
    
    // Auto submit on filter change
    document.querySelectorAll('#filterForm select').forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});

// Show transaction detail
function showTransactionDetail(transactionId) {
    const modal = new bootstrap.Modal(document.getElementById('transactionDetailModal'));
    const content = document.getElementById('transactionDetailContent');
    
    // Show loading
    content.innerHTML = `
        <div class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch transaction detail
    fetch(`/kitchen/transactions/${transactionId}/detail`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i>
                        Gagal memuat detail transaksi: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    Terjadi kesalahan saat memuat detail transaksi.
                </div>
            `;
        });
}

// Show delete confirmation
let deleteTransactionId = null;
function showDeleteConfirmation(transactionId) {
    deleteTransactionId = transactionId;
    const modal = new bootstrap.Modal(document.getElementById('deleteTransactionModal'));
    modal.show();
}

// Confirm delete
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!deleteTransactionId) return;
    
    const btn = this;
    const originalText = btn.innerHTML;
    
    // Show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus...';
    
    // Send delete request
    fetch(`/kitchen/transactions/${deleteTransactionId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('deleteTransactionModal')).hide();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                confirmButtonText: 'OK'
            }).then(() => {
                // Reload page
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message,
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan saat menghapus transaksi.',
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// Export functions
function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    window.open(`{{ route('kitchen.transactions') }}?${params.toString()}`, '_blank');
}

function exportToPDF() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'pdf');
    window.open(`{{ route('kitchen.transactions') }}?${params.toString()}`, '_blank');
}

function printTransactions() {
    const params = new URLSearchParams(window.location.search);
    params.set('print', '1');
    window.open(`{{ route('kitchen.transactions') }}?${params.toString()}`, '_blank');
}
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session("success") }}',
        confirmButtonText: 'OK'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session("error") }}',
        confirmButtonText: 'OK'
    });
</script>
@endif
@endpush

@push('styles')
<style>
.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    font-size: 16px;
}

.avatar-xs .avatar-initial {
    width: 24px;
    height: 24px;
    font-size: 10px;
}

.btn-group .btn {
    white-space: nowrap;
}

.quick-filter.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dropdown-menu {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-content {
    box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.175);
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .avatar-initial {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
}
</style>
@endpush
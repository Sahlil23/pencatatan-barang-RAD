@extends('layouts.admin')

@section('title', 'Transactions - ' . $warehouse->warehouse_name)

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
          <a href="{{ route('outlet-warehouse.index') }}">Outlet Warehouse</a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ route('outlet-warehouse.show', $warehouse->id) }}">{{ $warehouse->warehouse_name }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Transactions</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h4 class="mb-2">
              <i class="bx bx-history me-2 text-primary"></i>
              Transaction History
            </h4>
            <div class="text-muted">
              <span class="me-3">
                <i class="bx bx-store me-1"></i>
                {{ $warehouse->warehouse_name }}
              </span>
              @if($warehouse->branch)
              <span class="me-3">
                <i class="bx bx-building me-1"></i>
                {{ $warehouse->branch->branch_name }}
              </span>
              @endif
              <span class="badge bg-label-info">
                {{ $transactions->total() }} Records
              </span>
            </div>
          </div>
          <a href="{{ route('outlet-warehouse.show', $warehouse->id) }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i>Back
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-filter me-2"></i>Filter Transactions
        </h6>
      </div>
      <div class="card-body">
        <form method="GET" id="filterForm">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Start Date</label>
              <input type="date" 
                     class="form-control" 
                     name="start_date" 
                     value="{{ $startDate }}"
                     onchange="document.getElementById('filterForm').submit()">
            </div>
            <div class="col-md-3">
              <label class="form-label">End Date</label>
              <input type="date" 
                     class="form-control" 
                     name="end_date" 
                     value="{{ $endDate }}"
                     max="{{ now()->format('Y-m-d') }}"
                     onchange="document.getElementById('filterForm').submit()">
            </div>
            <div class="col-md-3">
              <label class="form-label">Transaction Type</label>
              <select class="form-select" name="type" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Types</option>
                <option value="RECEIVE_FROM_BRANCH" {{ request('type') == 'RECEIVE_FROM_BRANCH' ? 'selected' : '' }}>
                  Receive from Branch
                </option>
                <option value="DISTRIBUTE_TO_KITCHEN" {{ request('type') == 'DISTRIBUTE_TO_KITCHEN' ? 'selected' : '' }}>
                  Distribute to Kitchen
                </option>
                <option value="ADJUSTMENT_OUT" {{ request('type') == 'ADJUSTMENT_OUT' ? 'selected' : '' }}>
                  Adjustment Out
                </option>
                <option value="ADJUSTMENT_IN" {{ request('type') == 'ADJUSTMENT_IN' ? 'selected' : '' }}>
                  Adjustment In
                </option>
                <option value="RETURN_FROM_KITCHEN" {{ request('type') == 'RETURN_FROM_KITCHEN' ? 'selected' : '' }}>
                  Return from Outlet
                </option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">&nbsp;</label>
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                  <i class="bx bx-search me-1"></i>Filter
                </button>
                <a href="{{ route('outlet-warehouse.transactions', $warehouse->id) }}" class="btn btn-outline-secondary">
                  <i class="bx bx-reset"></i>
                </a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Transaction Statistics -->
<div class="row mb-4">
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-download fs-4"></i>
            </span>
          </div>
          <div>
            <small class="text-muted d-block">Total IN</small>
            <h5 class="mb-0 text-success">
              {{ number_format($transactions->where('quantity', '>', 0)->sum('quantity'), 2) }}
            </h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-upload fs-4"></i>
            </span>
          </div>
          <div>
            <small class="text-muted d-block">Total OUT</small>
            <h5 class="mb-0 text-warning">
              {{ number_format(abs($transactions->where('quantity', '<', 0)->sum('quantity')), 2) }}
            </h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-info">
              <i class="bx bx-receipt fs-4"></i>
            </span>
          </div>
          <div>
            <small class="text-muted d-block">Transactions</small>
            <h5 class="mb-0">{{ $transactions->total() }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-package fs-4"></i>
            </span>
          </div>
          <div>
            <small class="text-muted d-block">Unique Items</small>
            <h5 class="mb-0">{{ $transactions->unique('item_id')->count() }}</h5>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Transactions Table -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-list-ul me-2"></i>
          Transaction Details
        </h5>
        <!-- <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-success" onclick="exportToExcel()">
            <i class="bx bx-spreadsheet me-1"></i>Export Excel
          </button>
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportToPDF()">
            <i class="bx bx-file-pdf me-1"></i>Export PDF
          </button>
        </div> -->
      </div>
      <div class="card-body">
        @if($transactions->isEmpty())
          <div class="alert alert-info mb-0">
            <i class="bx bx-info-circle me-2"></i>
            No transactions found for the selected period.
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-hover" id="transactionsTable">
              <thead class="table-light">
                <tr>
                  <th width="10%">Date/Time</th>
                  <th width="12%">Reference</th>
                  <th width="10%">Type</th>
                  <th width="15%">Item</th>
                  <th width="8%" class="text-end">Quantity</th>
                  <th width="10%" class="text-center">Status</th>
                  <th width="12%">User</th>
                  <th width="23%">Notes</th>
                </tr>
              </thead>
              <tbody>
                @foreach($transactions as $transaction)
                <tr>
                  <td>
                    <strong>{{ $transaction->transaction_date->format('d M Y') }}</strong>
                    <br>
                    <small class="text-muted">{{ $transaction->transaction_date->format('H:i:s') }}</small>
                  </td>
                  <td>
                    <span class="badge bg-label-secondary">
                      {{ $transaction->reference_no }}
                    </span>
                    @if($transaction->document_no)
                    <br>
                    <small class="text-muted">{{ $transaction->document_no }}</small>
                    @endif
                  </td>
                  <td>
                    @php
                      $typeConfig = [
                        'RECEIVE_FROM_BRANCH' => ['bg-success', 'bx-download', 'Receive'],
                        'DISTRIBUTE_TO_KITCHEN' => ['bg-warning', 'bx-upload', 'Distribute'],
                        'ADJUSTMENT_OUT' => ['bg-info', 'bx-edit-alt', 'Adjustment Out'],
                        'ADJUSTMENT_IN' => ['bg-info', 'bx-edit-alt', 'Adjustment In'],
                        'RETURN_FROM_KITCHEN' => ['bg-primary', 'bx-undo', 'Return'],
                      ];
                      $config = $typeConfig[$transaction->transaction_type] ?? ['bg-secondary', 'bx-circle', 'Other'];
                    @endphp
                    <span class="badge {{ $config[0] }}">
                      <i class="bx {{ $config[1] }} me-1"></i>
                      {{ $config[2] }}
                    </span>
                  </td>
                  <td>
                    <strong>{{ $transaction->item->item_name ?? 'N/A' }}</strong>
                    <br>
                    <small class="text-muted">
                      <i class="bx bx-barcode me-1"></i>{{ $transaction->item->sku ?? '-' }}
                    </small>
                    @if($transaction->item)
                    <br>
                    <small class="text-muted">{{ $transaction->item->unit ?? 'Unit' }}</small>
                    @endif
                  </td>
                  <td class="text-end">
                    @if($transaction->quantity >= 0)
                      <strong class="text-success">
                        +{{ number_format($transaction->quantity, 3) }}
                      </strong>
                    @else
                      <strong class="text-danger">
                        {{ number_format($transaction->quantity, 3) }}
                      </strong>
                    @endif
                  </td>
                  <td class="text-center">
                    @php
                      $statusConfig = [
                        'COMPLETED' => ['bg-success', 'Completed'],
                        'PENDING' => ['bg-warning', 'Pending'],
                        'CANCELLED' => ['bg-danger', 'Cancelled'],
                      ];
                      $status = $statusConfig[$transaction->status] ?? ['bg-secondary', $transaction->status];
                    @endphp
                    <span class="badge {{ $status[0] }}">
                      {{ $status[1] }}
                    </span>
                  </td>
                  <td>
                    @if($transaction->user)
                      <div class="d-flex align-items-center">
                        <!-- <div class="avatar avatar-xs me-2">
                          <span class="avatar-initial rounded-circle bg-label-primary">
                            {{ strtoupper(substr($transaction->user->full_name, 0, 1)) }}
                          </span>
                        </div> -->
                        <div>
                          <small class="fw-semibold">{{ $transaction->user->full_name }}</small>
                          <br>
                          <small class="text-muted">{{ $transaction->user->email }}</small>
                        </div>
                      </div>
                    @else
                      <small class="text-muted">System</small>
                    @endif
                  </td>
                  <td>
                    <small>{{ Str::limit($transaction->notes, 60) }}</small>
                    @if(strlen($transaction->notes) > 60)
                      <button type="button" 
                              class="btn btn-xs btn-link p-0" 
                              onclick="showFullNotes('{{ addslashes($transaction->notes) }}')">
                        Read more
                      </button>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
              <tfoot class="table-secondary">
                <tr>
                  <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                  <td class="text-end fw-bold">
                    <span class="text-success">
                      +{{ number_format($transactions->where('quantity', '>', 0)->sum('quantity'), 3) }}
                    </span>
                    <br>
                    <span class="text-danger">
                      {{ number_format($transactions->where('quantity', '<', 0)->sum('quantity'), 3) }}
                    </span>
                  </td>
                  <td colspan="3"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Pagination -->
          <div class="mt-3">
            {{ $transactions->appends(request()->query())->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Full Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Transaction Notes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="fullNotes" class="mb-0"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function showFullNotes(notes) {
  document.getElementById('fullNotes').textContent = notes;
  const modal = new bootstrap.Modal(document.getElementById('notesModal'));
  modal.show();
}

function exportToExcel() {
  const startDate = '{{ $startDate }}';
  const endDate = '{{ $endDate }}';
  const type = '{{ request("type") }}';
  
  let url = '{{ route("outlet-warehouse.transactions", $warehouse->id) }}?export=excel';
  url += `&start_date=${startDate}&end_date=${endDate}`;
  if (type) url += `&type=${type}`;
  
  window.location.href = url;
}

function exportToPDF() {
  const startDate = '{{ $startDate }}';
  const endDate = '{{ $endDate }}';
  const type = '{{ request("type") }}';
  
  let url = '{{ route("outlet-warehouse.transactions", $warehouse->id) }}?export=pdf';
  url += `&start_date=${startDate}&end_date=${endDate}`;
  if (type) url += `&type=${type}`;
  
  window.open(url, '_blank');
}

// Print function
function printTransactions() {
  window.print();
}

// Initialize tooltips if Bootstrap is available
document.addEventListener('DOMContentLoaded', function() {
  if (typeof bootstrap !== 'undefined') {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }
});
</script>
@endpush

@push('styles')
<style>
.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
}

.avatar-xs .avatar-initial {
  width: 24px;
  height: 24px;
  font-size: 0.75rem;
}

.table-hover tbody tr:hover {
  background-color: rgba(67, 89, 113, 0.05);
}

.badge {
  font-weight: 500;
  padding: 0.375rem 0.75rem;
}

.text-success {
  color: #28c76f !important;
}

.text-warning {
  color: #ff9f43 !important;
}

.text-danger {
  color: #ea5455 !important;
}

/* Print styles */
@media print {
  .card-header .btn,
  .breadcrumb,
  .pagination,
  nav {
    display: none !important;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
  }
  
  table {
    font-size: 12px;
  }
}

.btn-xs {
  padding: 0.125rem 0.25rem;
  font-size: 0.75rem;
}
</style>
@endpush
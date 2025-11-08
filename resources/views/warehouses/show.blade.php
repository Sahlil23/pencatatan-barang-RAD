@extends('layouts.admin')

@section('title', 'Detail Warehouse - Chicking BJM')

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
          <a href="{{ route('warehouses.index') }}">Warehouses</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ $warehouse->warehouse_name }}
        </li>
      </ol>
    </nav>
  </div>
</div>

<!-- Warehouse Header Info -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="d-flex align-items-start">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-{{ $warehouse->isCentral() ? 'primary' : 'info' }}" style="width: 56px; height: 56px; font-size: 24px;">
                <i class="bx {{ $warehouse->isCentral() ? 'bx-buildings' : 'bx-building' }}"></i>
              </span>
            </div>
            <div>
              <h5 class="mb-0">{{ $warehouse->warehouse_name }}</h5>
              <small class="text-muted">
                <i class="bx bx-hash me-1"></i>{{ $warehouse->warehouse_code }}
              </small>
              <div class="mt-2">
                <span class="badge bg-label-{{ $warehouse->isCentral() ? 'primary' : 'info' }}">
                  <i class="bx bx-{{ $warehouse->isCentral() ? 'buildings' : 'building' }} me-1"></i>
                  {{ ucfirst($warehouse->warehouse_type) }} Warehouse
                </span>
                <span class="badge bg-label-{{ $warehouse->status === 'ACTIVE' ? 'success' : 'warning' }}">
                  <i class="bx bx-{{ $warehouse->status === 'ACTIVE' ? 'check-circle' : 'error-circle' }} me-1"></i>
                  {{ ucfirst($warehouse->status) }}
                </span>
              </div>
            </div>
          </div>
          <div class="text-end">
            <div class="dropdown">
              <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bx bx-cog me-1"></i>
                Actions
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="{{ route('warehouses.edit', $warehouse->id) }}">
                  <i class="bx bx-edit-alt me-1"></i>
                  Edit Warehouse
                </a>
                @if($warehouse->isCentral())
                  <a class="dropdown-item" href="{{ route('central-warehouse.index') }}">
                    <i class="bx bx-package me-1"></i>
                    Manage Stock
                  </a>
                @else
                  <a class="dropdown-item" href="{{ route('branch-warehouse.show', $warehouse->id) }}">
                    <i class="bx bx-package me-1"></i>
                    Manage Stock
                  </a>
                @endif
                <div class="dropdown-divider"></div>
                @if($warehouse->status === 'ACTIVE')
                  <a class="dropdown-item text-warning" href="#" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                    <i class="bx bx-pause-circle me-1"></i>
                    Deactivate
                  </a>
                @else
                  <a class="dropdown-item text-success" href="#" data-bs-toggle="modal" data-bs-target="#activateModal">
                    <i class="bx bx-play-circle me-1"></i>
                    Activate
                  </a>
                @endif
              </div>
            </div>
          </div>
        </div>

        <!-- Warehouse Details Grid -->
        <div class="row mt-4">
          <div class="col-md-3">
            <small class="text-muted d-block">Branch</small>
            <h6 class="mb-0">
              @if($warehouse->branch)
                <span class="badge bg-label-primary">
                  <i class="bx bx-building me-1"></i>
                  {{ $warehouse->branch->branch_name }}
                </span>
              @else
                <span class="text-muted">Central / No Branch</span>
              @endif
            </h6>
          </div>
          <div class="col-md-3">
            <small class="text-muted d-block">Manager</small>
            <h6 class="mb-0">
              @if($warehouse->manager_name)
                <i class="bx bx-user me-1"></i>
                {{ $warehouse->manager_name }}
              @else
                <span class="text-muted">-</span>
              @endif
            </h6>
          </div>
          <div class="col-md-3">
            <small class="text-muted d-block">Contact</small>
            <h6 class="mb-0">
              @if($warehouse->phone)
                <i class="bx bx-phone me-1"></i>
                {{ $warehouse->phone }}
              @else
                <span class="text-muted">-</span>
              @endif
            </h6>
          </div>
          <div class="col-md-3">
            <small class="text-muted d-block">Capacity</small>
            <h6 class="mb-0">
              @if($warehouse->capacity_m2 || $warehouse->capacity_volume)
              @else
                <span class="text-muted">-</span>
              @endif
            </h6>
          </div>
        </div>

        <!-- Address Row -->
        <div class="row mt-3">
          <div class="col-12">
            <small class="text-muted d-block">Address</small>
            <h6 class="mb-0">
              <i class="bx bx-map me-1"></i>
              {{ $warehouse->address ?? '-' }}
            </h6>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Performance Metrics Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Items</span>
        <h3 class="card-title mb-2">{{ $metrics['total_items'] ?? 0 }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-package me-1"></i>Item Types
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="stock value" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Stock Value</span>
        <h3 class="card-title text-success mb-2">Rp {{ number_format($metrics['total_stock_value'] ?? 0, 0, ',', '.') }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-money me-1"></i>Total Inventory
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/chart.png') }}" alt="transactions" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Monthly Transactions</span>
        <h3 class="card-title text-primary mb-2">{{ $metrics['monthly_transactions'] ?? 0 }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-transfer me-1"></i>This Month
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-warning.png') }}" alt="low stock" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Low Stock Items</span>
        <h3 class="card-title text-warning mb-2">{{ $metrics['low_stock_items'] ?? 0 }}</h3>
        <small class="text-warning fw-semibold">
          <i class="bx bx-error-circle me-1"></i>Need Attention
        </small>
      </div>
    </div>
  </div>
</div>

<!-- Utilization & Capacity -->
@if($warehouse->capacity_volume)
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-bar-chart me-2"></i>
          Warehouse Utilization
        </h5>
        <span class="badge bg-label-{{ $warehouse->status }}">
          {{ $warehouse->status }}
        </span>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <div class="mb-2">
              <small class="text-muted">Capacity Usage</small>
            </div>
            @php
              $percentage = (float) ($metrics['capacity'] ?? 0);
              $color = 'success';
              if ($percentage >= 90) $color = 'danger';
              elseif ($percentage >= 70) $color = 'warning';
              elseif ($percentage >= 50) $color = 'info';
            @endphp
            <div class="progress" style="height: 30px;">
              <div class="progress-bar bg-{{ $color }}" role="progressbar" 
                   style="width: {{ $percentage }}%;" 
                   aria-valuenow="{{ $percentage }}" 
                   aria-valuemin="0" 
                   aria-valuemax="100">
                {{ number_format($percentage, 1) }}%
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-2">
              <small class="text-muted">Capacity Info</small>
            </div>
            <div>
              <strong>Used:</strong> {{ number_format($percentage, 1) }}%
              <br>
              <strong>Capacity:</strong> {{ $warehouse->capacity_info ?? '-' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Warehouse Information Tabs -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
          <li class="nav-item">
            <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#info" role="tab">
              <i class="bx bx-info-circle me-1"></i>
              Information
            </button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#settings" role="tab">
              <i class="bx bx-cog me-1"></i>
              Settings
            </button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#activity" role="tab">
              <i class="bx bx-history me-1"></i>
              Activity Log
            </button>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content p-0">
          <!-- Information Tab -->
          <div class="tab-pane fade show active" id="info" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-borderless">
                <tbody>
                  <tr>
                    <td class="text-muted" style="width: 200px;">
                      <i class="bx bx-hash me-2"></i>Warehouse Code
                    </td>
                    <td><strong>{{ $warehouse->warehouse_code }}</strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-building me-2"></i>Warehouse Name
                    </td>
                    <td><strong>{{ $warehouse->warehouse_name }}</strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-category me-2"></i>Type
                    </td>
                    <td>{!! $warehouse->warehouse_type !!}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-check-circle me-2"></i>Status
                    </td>
                    <td>{!! $warehouse->status !!}</td>
                  </tr>
                  @if($warehouse->branch)
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-store me-2"></i>Branch
                    </td>
                    <td>
                      <a href="#">
                        {{ $warehouse->branch->branch_name }}
                      </a>
                    </td>
                  </tr>
                  @endif
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-map me-2"></i>Address
                    </td>
                    <td>{{ $warehouse->address }}</td>
                  </tr>
                  @if($warehouse->coverage_area)
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-map-alt me-2"></i>Coverage Area
                    </td>
                    <td>{{ $warehouse->coverage_area }}</td>
                  </tr>
                  @endif
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-user me-2"></i>Manager
                    </td>
                    <td>{{ $warehouse->manager_name }}</td>
                  </tr>
                  @if($warehouse->email)
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-envelope me-2"></i>Email
                    </td>
                    <td>
                      <a href="mailto:{{ $warehouse->email }}">{{ $warehouse->email }}</a>
                    </td>
                  </tr>
                  @endif
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-cube me-2"></i>Capacity
                    </td>
                    <td>{{ $warehouse->capacity_volume }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-calendar me-2"></i>Created At
                    </td>
                    <td>{{ $warehouse->created_at->format('d M Y H:i') }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">
                      <i class="bx bx-calendar-edit me-2"></i>Last Updated
                    </td>
                    <td>{{ $warehouse->updated_at->format('d M Y H:i') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Settings Tab -->
          <div class="tab-pane fade" id="settings" role="tabpanel">
            <div class="alert alert-info">
              <i class="bx bx-info-circle me-2"></i>
              Warehouse settings can be configured here. Contact system administrator for more options.
            </div>
            
            @if($warehouse->settings)
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Setting</th>
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($warehouse->settings as $key => $value)
                    <tr>
                      <td><code>{{ $key }}</code></td>
                      <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <p class="text-muted">No custom settings configured.</p>
            @endif
          </div>

          <!-- Activity Log Tab -->
          <div class="tab-pane fade" id="activity" role="tabpanel">
            <div class="alert alert-info">
              <i class="bx bx-history me-2"></i>
              Activity log feature coming soon. This will show warehouse stock movements, updates, and transactions.
            </div>
            
            <div class="text-center py-5">
              <i class="bx bx-history" style="font-size: 64px; color: #ddd;"></i>
              <p class="text-muted mt-3">Activity tracking will be available here</p>
              <a href="{{ $warehouse->isCentral() ? route('central-warehouse.index') : route('branch-warehouse.show', $warehouse->id) }}" class="btn btn-primary btn-sm">
                <i class="bx bx-package me-1"></i>
                View Stock Transactions
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Deactivate Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bx bx-pause-circle me-2"></i>
          Deactivate Warehouse
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="#" method="POST">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="INACTIVE">
        <div class="modal-body">
          <p>Are you sure you want to deactivate <strong>{{ $warehouse->warehouse_name }}</strong>?</p>
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            This warehouse will not be available for stock operations until reactivated.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class="bx bx-pause-circle me-1"></i>
            Deactivate
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Activate Modal -->
<div class="modal fade" id="activateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bx bx-play-circle me-2"></i>
          Activate Warehouse
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="#" method="POST">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="ACTIVE">
        <div class="modal-body">
          <p>Are you sure you want to activate <strong>{{ $warehouse->warehouse_name }}</strong>?</p>
          <div class="alert alert-success">
            <i class="bx bx-check-circle me-2"></i>
            This warehouse will be available for stock operations.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="bx bx-play-circle me-1"></i>
            Activate
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
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Auto-hide alerts
  const alerts = document.querySelectorAll('.alert-dismissible');
  alerts.forEach(alert => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });
});
</script>
@endpush

@push('styles')
<style>
  .card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.1);
  }

  .table-borderless td {
    padding: 0.75rem 0.5rem;
  }

  .nav-tabs .nav-link {
    color: #697a8d;
  }

  .nav-tabs .nav-link.active {
    color: #696cff;
    font-weight: 500;
  }

  .progress {
    border-radius: 0.375rem;
  }

  .progress-bar {
    font-size: 14px;
    font-weight: 600;
    line-height: 30px;
  }
</style>
@endpush
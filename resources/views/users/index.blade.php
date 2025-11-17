@extends('layouts.admin')

@section('title', 'Manage Users - Chicking BJM')

@push('styles')
<style>
.user-avatar {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-weight: 600;
  font-size: 14px;
}
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">Manage Users</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <div class="avatar-initial bg-primary rounded">
              <i class="bx bx-user fs-4"></i>
            </div>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Users</span>
        <h3 class="card-title mb-2">{{ $users->total() }}</h3>
        <small class="text-muted">Semua pengguna sistem</small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <div class="avatar-initial bg-success rounded">
              <i class="bx bx-check-circle fs-4"></i>
            </div>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Active Users</span>
        <h3 class="card-title mb-2">
          {{ \App\Models\User::where('status', 'ACTIVE')->count() }}
        </h3>
        <small class="text-success">Pengguna aktif</small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <div class="avatar-initial bg-danger rounded">
              <i class="bx bx-crown fs-4"></i>
            </div>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Managers</span>
        <h3 class="card-title mb-2">
          {{ \App\Models\User::whereIn('role', ['central_manager', 'branch_manager', 'outlet_manager'])->count() }}
        </h3>
        <small class="text-danger">Manager users</small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <div class="avatar-initial bg-info rounded">
              <i class="bx bx-user-check fs-4"></i>
            </div>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Staff Users</span>
        <h3 class="card-title mb-2">
          {{ \App\Models\User::whereIn('role', ['central_staff', 'branch_staff', 'outlet_staff'])->count() }}
        </h3>
        <small class="text-info">Staff users</small>
      </div>
    </div>
  </div>
</div>

<!-- Main Table Card -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bx bx-user me-2"></i>
      Daftar Users
      <span class="badge bg-label-primary ms-2">{{ $users->total() }}</span>
    </h5>
    @can('create', App\Models\User::class)
    <a href="{{ route('users.create') }}" class="btn btn-primary">
      <i class="bx bx-plus me-1"></i>
      Tambah User
    </a>
    @endcan
  </div>

  <!-- Filters -->
  <div class="card-body border-bottom">
    <form method="GET" action="{{ route('users.index') }}" class="row g-3">
      <!-- Search -->
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" class="form-control" name="search" 
                 placeholder="Cari nama, username, email..." 
                 value="{{ request('search') }}">
        </div>
      </div>

      <!-- Role Filter -->
      <div class="col-md-2">
        <select name="role" class="form-select">
          <option value="">Semua Role</option>
          @foreach($roles as $key => $value)
            <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>
              {{ $value }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Status Filter -->
      <div class="col-md-2">
        <select name="status" class="form-select">
          <option value="">Semua Status</option>
          @foreach($statuses as $key => $value)
            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
              {{ $value }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Branch Filter (Super Admin & Central Manager) -->
      @if(auth()->user()->isSuperAdmin() || auth()->user()->isCentralManager())
      <div class="col-md-2">
        <select name="branch_id" class="form-select">
          <option value="">Semua Cabang</option>
          @foreach(\App\Models\Branch::where('status', 'active')->orderBy('branch_name')->get() as $branch)
            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
              {{ $branch->branch_name }}
            </option>
          @endforeach
        </select>
      </div>
      @endif

      <!-- Buttons -->
      <div class="col-md-2">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-outline-primary flex-fill">
            <i class="bx bx-filter-alt me-1"></i>
            Filter
          </button>
          <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-reset"></i>
          </a>
        </div>
      </div>
    </form>
  </div>

  <!-- Table -->
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead>
        <tr>
          <th width="5%">#</th>
          <th width="25%">User</th>
          <th width="15%">Username</th>
          <th width="15%">Role</th>
          <th width="12%">Branch</th>
          <th width="10%">Status</th>
          <th width="13%">Last Login</th>
          <th width="5%" class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $index => $user)
        <tr>
          <td>{{ $users->firstItem() + $index }}</td>
          <td>
            <div class="d-flex align-items-center">
              <div class="user-avatar me-3 bg-label-{{ $user->isManager() ? 'danger' : 'info' }}">
                {{ strtoupper(substr($user->full_name, 0, 2)) }}
              </div>
              <div>
                <div class="fw-semibold">{{ $user->full_name }}</div>
                @if($user->email)
                <small class="text-muted">
                  <i class="bx bx-envelope me-1"></i>{{ $user->email }}
                </small>
                @endif
                @if($user->phone)
                <small class="text-muted d-block">
                  <i class="bx bx-phone me-1"></i>{{ $user->phone }}
                </small>
                @endif
              </div>
            </div>
          </td>
          <td>
            <code>{{ $user->username }}</code>
          </td>
          <td>
            @php
              $roleColors = [
                'super_admin' => 'danger',
                'central_manager' => 'primary',
                'central_staff' => 'info',
                'branch_manager' => 'warning',
                'branch_staff' => 'secondary',
                'outlet_manager' => 'success',
                'outlet_staff' => 'dark',
              ];
              $roleColor = $roleColors[$user->role] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $roleColor }}">
              {{ $user->role }}
            </span>
          </td>
          <td>
            @if($user->branch)
              <small class="text-muted">
                <i class="bx bx-building me-1"></i>
                {{ Str::limit($user->branch->branch_name, 15) }}
              </small>
            @else
              <span class="text-muted">-</span>
            @endif
            @if($user->warehouse)
              <small class="d-block text-muted">
                <i class="bx bx-store me-1"></i>
                {{ Str::limit($user->warehouse->warehouse_name, 15) }}
              </small>
            @endif
          </td>
          <td>
            @php
              $statusColors = [
                'ACTIVE' => 'success',
                'INACTIVE' => 'secondary',
                'SUSPENDED' => 'danger'
              ];
              $statusColor = $statusColors[$user->status] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $statusColor }}">
              {{ $user->status }}
            </span>
          </td>
          <td>
            @if($user->last_login_at)
              <small class="text-muted">
                <i class="bx bx-time-five me-1"></i>
                {{ $user->last_login_at->diffForHumans() }}
              </small>
              <small class="d-block text-muted">
                {{ $user->last_login_at->format('d/m/Y H:i') }}
              </small>
            @else
              <span class="text-muted">Belum login</span>
            @endif
          </td>
          <td class="text-center">
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" 
                      data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <!-- View -->
                @can('view', $user)
                <a class="dropdown-item" href="{{ route('users.show', $user) }}">
                  <i class="bx bx-show me-2"></i> Detail
                </a>
                @endcan

                <!-- Edit -->
                @can('update', $user)
                <a class="dropdown-item" href="{{ route('users.edit', $user) }}">
                  <i class="bx bx-edit me-2"></i> Edit
                </a>
                @endcan
                
                @if($user->id !== auth()->id())
                  <div class="dropdown-divider"></div>
                  
                  <!-- Delete -->
                  @can('delete', $user)
                  <div class="dropdown-divider"></div>
                  <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger" 
                            onclick="return confirm('⚠️ Yakin ingin menghapus user {{ $user->username }}?\n\nData yang terkait dengan user ini mungkin akan terpengaruh.')">
                      <i class="bx bx-trash me-2"></i> Hapus
                    </button>
                  </form>
                  @endcan
                @else
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-muted disabled">
                    <i class="bx bx-info-circle me-2"></i> Anda tidak bisa mengubah data sendiri
                  </a>
                @endif
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="text-center py-5">
            <div class="d-flex flex-column align-items-center">
              <i class="bx bx-user-x display-3 text-muted mb-3"></i>
              <h5 class="text-muted">Tidak ada users yang ditemukan</h5>
              <p class="text-muted">
                @if(request()->hasAny(['search', 'role', 'status', 'branch_id']))
                  Coba ubah filter pencarian Anda
                @else
                  Belum ada user yang terdaftar
                @endif
              </p>
              @if(request()->hasAny(['search', 'role', 'status', 'branch_id']))
              <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-reset me-1"></i>
                Reset Filter
              </a>
              @endif
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <x-simple-pagination :items="$users" type="user" />
</div>
@endsection

@push('scripts')
<script>
// Auto-submit form on role/status/branch change
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form[action="{{ route('users.index') }}"]');
  const selects = form.querySelectorAll('select[name="role"], select[name="status"], select[name="branch_id"]');
  
  selects.forEach(select => {
    select.addEventListener('change', function() {
      form.submit();
    });
  });
});

// Export function (optional)
function exportData() {
  const headers = ['No', 'Nama Lengkap', 'Username', 'Email', 'Phone', 'Role', 'Branch', 'Warehouse', 'Status', 'Last Login'];
  const rows = [
    @foreach($users as $index => $user)
    [
      '{{ $users->firstItem() + $index }}',
      '{{ addslashes($user->full_name) }}',
      '{{ $user->username }}',
      '{{ $user->email ?? "N/A" }}',
      '{{ $user->phone ?? "N/A" }}',
      '{{ $user->role }}',
      '{{ $user->branch ? addslashes($user->branch->branch_name) : "N/A" }}',
      '{{ $user->warehouse ? addslashes($user->warehouse->warehouse_name) : "N/A" }}',
      '{{ $user->status }}',
      '{{ $user->last_login_at ? $user->last_login_at->format("d/m/Y H:i") : "Belum login" }}'
    ],
    @endforeach
  ];
  
  downloadCSV('users_{{ date("Y-m-d") }}', headers, rows);
}

// Confirmation for status change
document.querySelectorAll('form[action*="change-status"]').forEach(form => {
  form.addEventListener('submit', function(e) {
    const confirmed = confirm('Apakah Anda yakin ingin mengubah status user ini?');
    if (!confirmed) {
      e.preventDefault();
    }
  });
});
</script>
@endpush
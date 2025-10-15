@extends('layouts.admin')

@section('title', 'Manage Users - Chicking BJM')

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
        <h3 class="card-title mb-2">{{ $stats['total_users'] }}</h3>
        <small class="text-muted">Semua pengguna</small>
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
        <h3 class="card-title mb-2">{{ $stats['active_users'] }}</h3>
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
        <span class="fw-semibold d-block mb-1">Administrators</span>
        <h3 class="card-title mb-2">{{ $stats['admin_users'] }}</h3>
        <small class="text-danger">Admin users</small>
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
        <h3 class="card-title mb-2">{{ $stats['staff_users'] }}</h3>
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
    </h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
      <i class="bx bx-plus me-1"></i>
      Tambah User
    </button>
  </div>

  <!-- Filters -->
  <div class="card-body border-bottom">
    <form method="GET" action="{{ route('users.index') }}" class="row g-3">
      <div class="col-md-4">
        <input type="text" class="form-control" name="search" 
               placeholder="Cari nama, username, atau email..." 
               value="{{ request('search') }}">
      </div>
      <div class="col-md-3">
        <select name="role" class="form-select">
          <option value="">Semua Role</option>
          @foreach(App\Models\User::getRoles() as $key => $value)
            <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>
              {{ $value }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">Semua Status</option>
          @foreach(App\Models\User::getStatuses() as $key => $value)
            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
              {{ $value }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-outline-primary w-100">
          <i class="bx bx-search me-1"></i>
          Filter
        </button>
      </div>
    </form>
  </div>

  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Username</th>
          <th>Role</th>
          <th>Status</th>
          <th>Last Login</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $index => $user)
        <tr>
          <td>{{ $users->firstItem() + $index }}</td>
          <td>
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm me-3">
                <div class="avatar-initial bg-{{ $user->isAdmin() ? 'danger' : 'info' }} rounded-circle">
                  {{ strtoupper(substr($user->full_name, 0, 2)) }}
                </div>
              </div>
              <div>
                <div class="fw-semibold">{{ $user->full_name }}</div>
                @if($user->email)
                <small class="text-muted">{{ $user->email }}</small>
                @endif
              </div>
            </div>
          </td>
          <td>
            <code>{{ $user->username }}</code>
          </td>
          <td>
            <span class="badge bg-{{ $user->isAdmin() ? 'danger' : 'info' }}">
              {{ $user->getRoleName() }}
            </span>
          </td>
          <td>
            <span class="badge bg-{{ $user->isActive() ? 'success' : 'secondary' }}">
              {{ $user->getStatusName() }}
            </span>
          </td>
          <td>
            @if($user->last_login_at)
              {{ $user->last_login_at->format('d/m/Y H:i') }}
            @else
              <span class="text-muted">Belum login</span>
            @endif
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('users.show', $user) }}">
                  <i class="bx bx-show me-2"></i> Detail
                </a>
                <a class="dropdown-item" href="{{ route('users.edit', $user) }}">
                  <i class="bx bx-edit me-2"></i> Edit
                </a>
                
                @if($user->id !== Auth::id())
                <div class="dropdown-divider"></div>
                <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="dropdown-item">
                    <i class="bx bx-{{ $user->isActive() ? 'user-x' : 'user-check' }} me-2"></i>
                    {{ $user->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                </form>
                
                <form action="{{ route('users.reset-password', $user) }}" method="POST" class="d-inline">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="dropdown-item" 
                          onclick="return confirm('Reset password ke default?')">
                    <i class="bx bx-key me-2"></i> Reset Password
                  </button>
                </form>
                
                <div class="dropdown-divider"></div>
                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger" 
                          onclick="return confirm('Yakin ingin menghapus user ini?')">
                    <i class="bx bx-trash me-2"></i> Hapus
                  </button>
                </form>
                @endif
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center py-4">
            <i class="bx bx-user-x display-4 text-muted"></i>
            <p class="text-muted mt-2">Tidak ada users yang ditemukan</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <x-simple-pagination :items="$users" type="users" />
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bx bx-user-plus me-2"></i>
          Tambah User Baru
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="full_name" class="form-label">Nama Lengkap *</label>
              <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="username" class="form-label">Username *</label>
              <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="col-md-6 mb-3">
              <label for="phone" class="form-label">Telepon</label>
              <input type="text" class="form-control" id="phone" name="phone">
            </div>
            <div class="col-12 mb-3">
              <label for="role" class="form-label">Role *</label>
              <select class="form-select" id="role" name="role" required>
                <option value="">Pilih Role</option>
                @foreach(App\Models\User::getRoles() as $key => $value)
                  <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="password" class="form-label">Password *</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="password_confirmation" class="form-label">Konfirmasi Password *</label>
              <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i> Batal
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Simpan User
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Export function
function exportData() {
  const headers = ['No', 'Nama Lengkap', 'Username', 'Email', 'Role', 'Status', 'Last Login'];
  const rows = [
    @foreach($users as $index => $user)
    ['{{ $users->firstItem() + $index }}', '{{ addslashes($user->full_name) }}', '{{ $user->username }}', '{{ $user->email ?? "N/A" }}', '{{ $user->getRoleName() }}', '{{ $user->getStatusName() }}', '{{ $user->last_login_at ? $user->last_login_at->format("d/m/Y H:i") : "Belum login" }}'],
    @endforeach
  ];
  
  downloadCSV('users', headers, rows);
}

// Reset form when modal is hidden
document.getElementById('createUserModal').addEventListener('hidden.bs.modal', function() {
  this.querySelector('form').reset();
});
</script>
@endpush
@extends('layouts.admin')

@section('title', 'Edit User - ' . $user->full_name . ' - Chicking BJM')

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
          <a href="{{ route('users.index') }}">Users</a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ route('users.show', $user) }}">{{ $user->full_name }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit User: {{ $user->full_name }}</h5>
        <small class="text-muted float-end">Form edit informasi user</small>
      </div>
      <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST" id="editUserForm">
          @csrf
          @method('PUT')
          
          <!-- Full Name -->
          <div class="mb-3">
            <label class="form-label" for="full_name">Nama Lengkap <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-user"></i></span>
              <input
                type="text"
                class="form-control @error('full_name') is-invalid @enderror"
                id="full_name"
                name="full_name"
                placeholder="Masukkan nama lengkap"
                value="{{ old('full_name', $user->full_name) }}"
                required
              />
            </div>
            @error('full_name')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Nama lengkap pengguna</div>
            @enderror
          </div>

          <!-- Username -->
          <div class="mb-3">
            <label class="form-label" for="username">Username <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-user-circle"></i></span>
              <input
                type="text"
                class="form-control @error('username') is-invalid @enderror"
                id="username"
                name="username"
                placeholder="Masukkan username"
                value="{{ old('username', $user->username) }}"
                required
              />
            </div>
            @error('username')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Username untuk login (harus unik)</div>
            @enderror
          </div>

          <!-- Email -->
          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-envelope"></i></span>
              <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                placeholder="Masukkan email"
                value="{{ old('email', $user->email) }}"
              />
            </div>
            @error('email')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Email opsional untuk komunikasi</div>
            @enderror
          </div>

          <!-- Phone -->
          <div class="mb-3">
            <label class="form-label" for="phone">No. Telepon</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-phone"></i></span>
              <input
                type="text"
                class="form-control @error('phone') is-invalid @enderror"
                id="phone"
                name="phone"
                placeholder="Contoh: 081234567890"
                value="{{ old('phone', $user->phone) }}"
              />
            </div>
            @error('phone')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Nomor telepon untuk komunikasi</div>
            @enderror
          </div>

          <!-- Role and Status Row -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-key"></i></span>
                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                  <option value="">Pilih Role</option>
                  <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                    Administrator
                  </option>
                  <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>
                    Staff
                  </option>
                </select>
              </div>
              @error('role')
                <div class="form-text text-danger">{{ $message }}</div>
              @else
                <div class="form-text">Tingkat akses pengguna</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-check-circle"></i></span>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                  <option value="">Pilih Status</option>
                  <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>
                    Aktif
                  </option>
                  <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>
                    Tidak Aktif
                  </option>
                </select>
              </div>
              @error('status')
                <div class="form-text text-danger">{{ $message }}</div>
              @else
                <div class="form-text">Status akun pengguna</div>
              @enderror
            </div>
          </div>

          <!-- Password Section -->
          <div class="card border mb-4">
            <div class="card-header">
              <h6 class="mb-0">
                <i class="bx bx-lock me-2"></i>
                Ubah Password
              </h6>
              <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
            </div>
            <div class="card-body">
              <!-- New Password -->
              <div class="mb-3">
                <label class="form-label" for="password">Password Baru</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                  <input
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    placeholder="Masukkan password baru"
                  />
                  <span class="input-group-text cursor-pointer" id="togglePassword">
                    <i class="bx bx-hide" id="togglePasswordIcon"></i>
                  </span>
                </div>
                @error('password')
                  <div class="form-text text-danger">{{ $message }}</div>
                @else
                  <div class="form-text">Password minimal 6 karakter</div>
                @enderror
              </div>

              <!-- Confirm Password -->
              <div class="mb-3">
                <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                  <input
                    type="password"
                    class="form-control"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Ulangi password baru"
                  />
                  <span class="input-group-text cursor-pointer" id="togglePasswordConfirm">
                    <i class="bx bx-hide" id="togglePasswordConfirmIcon"></i>
                  </span>
                </div>
                <div class="form-text">Ulangi password untuk konfirmasi</div>
              </div>

              <!-- Password Generator -->
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="generatePassword()">
                  <i class="bx bx-refresh me-1"></i>
                  Generate Password
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm" onclick="useDefaultPassword()">
                  <i class="bx bx-key me-1"></i>
                  Use Default
                </button>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex justify-content-between">
            <div class="d-flex gap-2">
              <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>
                Kembali
              </a>
              <a href="{{ route('users.index') }}" class="btn btn-outline-info">
                <i class="bx bx-list-ul me-1"></i>
                Daftar User
              </a>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
              <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="bx bx-save me-1"></i>
                Update User
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- User Info Card -->
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Informasi User
        </h5>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-4">
          <div class="avatar avatar-lg me-3">
            <span class="avatar-initial rounded-circle bg-label-primary">
              {{ strtoupper(substr($user->full_name, 0, 2)) }}
            </span>
          </div>
          <div>
            <h6 class="mb-0">{{ $user->full_name }}</h6>
            <small class="text-muted">{{ '@' . $user->username }}</small>
          </div>
        </div>
        
        <div class="info-container">
          <ul class="list-unstyled">
            <li class="mb-3">
              <div class="d-flex justify-content-between">
                <span class="fw-medium">Role:</span>
                <span class="badge bg-{{ $user->role == 'admin' ? 'primary' : 'info' }}">
                  {{ $user->getRoleName() }}
                </span>
              </div>
            </li>
            <li class="mb-3">
              <div class="d-flex justify-content-between">
                <span class="fw-medium">Status:</span>
                <span class="badge bg-{{ $user->status == 'active' ? 'success' : 'secondary' }}">
                  {{ $user->getStatusName() }}
                </span>
              </div>
            </li>
            <li class="mb-3">
              <span class="fw-medium">ID User:</span>
              <span class="text-muted">#{{ $user->id }}</span>
            </li>
            <li class="mb-3">
              <span class="fw-medium">Dibuat:</span>
              <span class="text-muted">{{ $user->created_at->format('d M Y') }}</span>
            </li>
            <li class="mb-3">
              <span class="fw-medium">Login Terakhir:</span>
              <span class="text-muted">
                {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Belum pernah' }}
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-cog me-2"></i>
          Quick Actions
        </h6>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <button type="button" class="btn btn-outline-warning btn-sm" onclick="resetUserPassword()">
            <i class="bx bx-key me-1"></i>
            Reset Password
          </button>
          <button type="button" class="btn btn-outline-{{ $user->status == 'active' ? 'secondary' : 'success' }} btn-sm" 
                  onclick="toggleUserStatus()">
            <i class="bx bx-{{ $user->status == 'active' ? 'user-x' : 'user-check' }} me-1"></i>
            {{ $user->status == 'active' ? 'Nonaktifkan' : 'Aktifkan' }} User
          </button>
          @if($user->id !== Auth::id())
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete()">
              <i class="bx bx-trash me-1"></i>
              Hapus User
            </button>
          @endif
        </div>
      </div>
    </div>

    <!-- Activity Summary -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-chart me-2"></i>
          Activity Summary
        </h6>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Stock Transactions:</span>
          <span class="badge bg-primary">{{ $user->stockTransactions()->count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">This Month:</span>
          <span class="badge bg-info">{{ $user->stockTransactions()->whereMonth('created_at', now()->month)->count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Today:</span>
          <span class="badge bg-success">{{ $user->stockTransactions()->whereDate('created_at', today())->count() }}</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modals -->
<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reset Password User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin mereset password user <strong>"{{ $user->full_name }}"</strong>?</p>
        <div class="alert alert-warning">
          <i class="bx bx-error-circle me-2"></i>
          Password akan direset ke: <strong>password123</strong>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <form action="{{ route('users.reset-password', $user) }}" method="POST" class="d-inline">
          @csrf
          @method('PATCH')
          <button type="submit" class="btn btn-warning">Ya, Reset Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Toggle Status Modal -->
<div class="modal fade" id="toggleStatusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ $user->status == 'active' ? 'Nonaktifkan' : 'Aktifkan' }} User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin {{ $user->status == 'active' ? 'menonaktifkan' : 'mengaktifkan' }} user <strong>"{{ $user->full_name }}"</strong>?</p>
        @if($user->status == 'active')
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            User tidak akan bisa login setelah dinonaktifkan
          </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
          @csrf
          @method('PATCH')
          <button type="submit" class="btn btn-{{ $user->status == 'active' ? 'warning' : 'success' }}">
            Ya, {{ $user->status == 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
@if($user->id !== Auth::id())
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus user <strong>"{{ $user->full_name }}"</strong>?</p>
        @if($user->stockTransactions()->exists())
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Peringatan:</strong> User ini memiliki riwayat transaksi dan tidak dapat dihapus.
          </div>
        @else
          <div class="alert alert-danger">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Semua data terkait akan dihapus permanen.
          </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        @if(!$user->stockTransactions()->exists())
          <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Ya, Hapus User</button>
          </form>
        @else
          <button type="button" class="btn btn-danger" disabled>Tidak Dapat Dihapus</button>
        @endif
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Store original values for reset
  const originalData = {
    full_name: '{{ $user->full_name }}',
    username: '{{ $user->username }}',
    email: '{{ $user->email }}',
    phone: '{{ $user->phone }}',
    role: '{{ $user->role }}',
    status: '{{ $user->status }}'
  };

  // Password toggle functionality
  const togglePassword = document.getElementById('togglePassword');
  const password = document.getElementById('password');
  const togglePasswordIcon = document.getElementById('togglePasswordIcon');

  togglePassword.addEventListener('click', function() {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    togglePasswordIcon.classList.toggle('bx-hide');
    togglePasswordIcon.classList.toggle('bx-show');
  });

  // Confirm password toggle
  const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
  const passwordConfirm = document.getElementById('password_confirmation');
  const togglePasswordConfirmIcon = document.getElementById('togglePasswordConfirmIcon');

  togglePasswordConfirm.addEventListener('click', function() {
    const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordConfirm.setAttribute('type', type);
    togglePasswordConfirmIcon.classList.toggle('bx-hide');
    togglePasswordConfirmIcon.classList.toggle('bx-show');
  });

  // Generate random password
  window.generatePassword = function() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let password = '';
    for (let i = 0; i < 8; i++) {
      password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    document.getElementById('password').value = password;
    document.getElementById('password_confirmation').value = password;
    
    // Show generated password
    alert('Password yang dihasilkan: ' + password + '\nSilakan catat password ini!');
  };

  // Use default password
  window.useDefaultPassword = function() {
    const defaultPassword = 'password123';
    document.getElementById('password').value = defaultPassword;
    document.getElementById('password_confirmation').value = defaultPassword;
  };

  // Reset form to original values
  window.resetForm = function() {
    if (confirm('Apakah Anda yakin ingin mengembalikan form ke data asli?')) {
      document.getElementById('full_name').value = originalData.full_name;
      document.getElementById('username').value = originalData.username;
      document.getElementById('email').value = originalData.email;
      document.getElementById('phone').value = originalData.phone;
      document.getElementById('role').value = originalData.role;
      document.getElementById('status').value = originalData.status;
      document.getElementById('password').value = '';
      document.getElementById('password_confirmation').value = '';
      
      // Remove validation classes
      document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }
  };

  // Quick actions
  window.resetUserPassword = function() {
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
  };

  window.toggleUserStatus = function() {
    const modal = new bootstrap.Modal(document.getElementById('toggleStatusModal'));
    modal.show();
  };

  window.confirmDelete = function() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
  };

  // Form submission
  const form = document.getElementById('editUserForm');
  form.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirmation').value;
    
    if (password && password !== passwordConfirm) {
      e.preventDefault();
      alert('Password dan konfirmasi password tidak cocok!');
      return false;
    }
    
    // Show loading
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Mengupdate...';
    submitBtn.disabled = true;
  });

  // Real-time password match validation
  const passwordInputs = [document.getElementById('password'), document.getElementById('password_confirmation')];
  passwordInputs.forEach(input => {
    input.addEventListener('input', function() {
      const password = document.getElementById('password').value;
      const passwordConfirm = document.getElementById('password_confirmation').value;
      
      if (password && passwordConfirm) {
        if (password === passwordConfirm) {
          passwordInputs.forEach(inp => {
            inp.classList.remove('is-invalid');
            inp.classList.add('is-valid');
          });
        } else {
          passwordInputs.forEach(inp => {
            inp.classList.remove('is-valid');
            inp.classList.add('is-invalid');
          });
        }
      } else {
        passwordInputs.forEach(inp => {
          inp.classList.remove('is-valid', 'is-invalid');
        });
      }
    });
  });

  // Auto-focus first field if there are validation errors
  @if($errors->any())
    const firstErrorField = document.querySelector('.is-invalid');
    if (firstErrorField) {
      firstErrorField.focus();
    }
  @endif
});
</script>
@endpush

@push('styles')
<style>
.avatar-lg {
  width: 4rem;
  height: 4rem;
}

.avatar-lg .avatar-initial {
  font-size: 1.5rem;
}

.info-container .list-unstyled li {
  padding: 0.375rem 0;
}

.cursor-pointer {
  cursor: pointer;
}

.form-control.is-valid {
  border-color: #28c76f;
}

.form-control.is-valid:focus {
  border-color: #28c76f;
  box-shadow: 0 0 0 0.2rem rgba(40, 199, 111, 0.25);
}

.form-control.is-invalid {
  border-color: #ea5455;
}

.form-control.is-invalid:focus {
  border-color: #ea5455;
  box-shadow: 0 0 0 0.2rem rgba(234, 84, 85, 0.25);
}

.card.border {
  border: 1px solid #d9dee3 !important;
}

@media (max-width: 768px) {
  .d-flex.gap-2 {
    flex-direction: column;
    gap: 0.5rem !important;
  }
  
  .col-md-6 {
    margin-bottom: 1rem;
  }
}
</style>
@endpush
@extends('layouts.admin')

@section('title', 'My Profile - Chicking BJM')

@push('styles')
<style>
.profile-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 0.75rem;
  color: white;
  padding: 2rem;
  margin-bottom: 1.5rem;
}

.avatar-upload {
  position: relative;
  display: inline-block;
}

.avatar-upload .avatar-edit {
  position: absolute;
  right: 12px;
  bottom: 0;
  z-index: 1;
}

.avatar-upload .avatar-edit input {
  display: none;
}

.avatar-upload .avatar-edit label {
  display: inline-block;
  width: 34px;
  height: 34px;
  margin-bottom: 0;
  border-radius: 100%;
  background: #696cff;
  border: 1px solid transparent;
  cursor: pointer;
  font-weight: normal;
  transition: all 0.2s ease-in-out;
}

.avatar-upload .avatar-edit label:hover {
  background: #5a5fe7;
  border-color: #fff;
}

.avatar-upload .avatar-edit label:after {
  content: "\f040";
  font-family: 'boxicons';
  color: #fff;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 16px;
}

.info-item {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 0.5rem;
  border-left: 4px solid #696cff;
}

.info-item i {
  font-size: 1.2rem;
  margin-right: 1rem;
  color: #696cff;
  width: 24px;
  text-align: center;
}

.activity-item {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #eee;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
  font-size: 1.1rem;
}

.activity-login { background: rgba(40, 199, 111, 0.1); color: #28c76f; }
.activity-update { background: rgba(105, 108, 255, 0.1); color: #696cff; }
.activity-logout { background: rgba(234, 84, 85, 0.1); color: #ea5455; }
.activity-transaction { background: rgba(255, 159, 67, 0.1); color: #ff9f43; }
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
        <li class="breadcrumb-item active">My Profile</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <!-- Profile Header -->
  <div class="col-12">
    <div class="profile-header">
      <div class="row align-items-center">
        <div class="col-md-3 text-center">
          <div class="avatar-upload">
            <div class="avatar-preview">
              <div class="avatar avatar-xl">
                <span class="avatar-initial rounded-circle" style="background: rgba(255,255,255,0.2); color: white; font-size: 3rem;">
                  {{ strtoupper(substr($user->full_name, 0, 2)) }}
                </span>
              </div>
            </div>
            <div class="avatar-edit">
              <input type="file" id="imageUpload" accept=".png, .jpg, .jpeg" />
              <label for="imageUpload" title="Change Profile Picture"></label>
            </div>
          </div>
        </div>
        <div class="col-md-9">
          <h3 class="mb-2">{{ $user->full_name }}</h3>
          <p class="mb-2 opacity-75">
            <i class="bx bx-briefcase me-2"></i>
            {{ $user->getRoleName() }}
          </p>
          <p class="mb-2 opacity-75">
            <i class="bx bx-user-circle me-2"></i>
            {{ $user->username }}
          </p>
          <p class="mb-0 opacity-75">
            <i class="bx bx-calendar me-2"></i>
            Bergabung sejak {{ $user->created_at->format('d F Y') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Profile Information -->
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-user me-2"></i>
          Informasi Profile
        </h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
          <i class="bx bx-edit me-1"></i>
          Edit Profile
        </button>
      </div>
      <div class="card-body">
        <!-- Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bx bx-check-circle me-2"></i>
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bx bx-error-circle me-2"></i>
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Profile Info -->
        <div class="row">
          <div class="col-md-6">
            <div class="info-item">
              <i class="bx bx-user"></i>
              <div>
                <small class="text-muted">Nama Lengkap</small>
                <div class="fw-semibold">{{ $user->full_name }}</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item">
              <i class="bx bx-id-card"></i>
              <div>
                <small class="text-muted">Username</small>
                <div class="fw-semibold">{{ $user->username }}</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item">
              <i class="bx bx-envelope"></i>
              <div>
                <small class="text-muted">Email</small>
                <div class="fw-semibold">{{ $user->email ?: 'Belum diisi' }}</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item">
              <i class="bx bx-phone"></i>
              <div>
                <small class="text-muted">No. Telepon</small>
                <div class="fw-semibold">{{ $user->phone ?: 'Belum diisi' }}</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item">
              <i class="bx bx-briefcase"></i>
              <div>
                <small class="text-muted">Role</small>
                <div class="fw-semibold">
                  <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'info' }}">
                    {{ $user->getRoleName() }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item">
              <i class="bx bx-shield-check"></i>
              <div>
                <small class="text-muted">Status Akun</small>
                <div class="fw-semibold">
                  <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                    {{ $user->getStatusName() }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item">
              <i class="bx bx-calendar"></i>
              <div>
                <small class="text-muted">Tanggal Bergabung</small>
                <div class="fw-semibold">{{ $user->created_at->format('d F Y, H:i') }}</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item">
              <i class="bx bx-time"></i>
              <div>
                <small class="text-muted">Terakhir Login</small>
                <div class="fw-semibold">
                  {{ $user->last_login_at ? $user->last_login_at->format('d F Y, H:i') : 'Belum pernah login' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Profile Stats & Activity -->
  <div class="col-md-4">
    <!-- Quick Stats -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-bar-chart me-2"></i>
          Statistik Aktivitas
        </h6>
      </div>
      <div class="card-body">
        @php
          $userTransactions = $user->stockTransactions();
          $totalTransactions = $userTransactions->count();
          $todayTransactions = $userTransactions->whereDate('created_at', today())->count();
          $thisMonthTransactions = $userTransactions->whereMonth('created_at', now()->month)
                                                  ->whereYear('created_at', now()->year)
                                                  ->count();
          $lastLoginDays = $user->last_login_at ? $user->last_login_at->diffInDays(now()) : null;
        @endphp
        
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <small class="text-muted">Total Transaksi</small>
            <div class="h5 mb-0">{{ number_format($totalTransactions) }}</div>
          </div>
          <div class="avatar bg-success">
            <i class="bx bx-transfer"></i>
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <small class="text-muted">Transaksi Bulan Ini</small>
            <div class="h5 mb-0">{{ number_format($thisMonthTransactions) }}</div>
          </div>
          <div class="avatar bg-primary">
            <i class="bx bx-calendar-check"></i>
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <small class="text-muted">Transaksi Hari Ini</small>
            <div class="h5 mb-0">{{ number_format($todayTransactions) }}</div>
          </div>
          <div class="avatar bg-info">
            <i class="bx bx-trending-up"></i>
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <small class="text-muted">Terakhir Login</small>
            <div class="h5 mb-0">
              @if($user->last_login_at)
                @if($lastLoginDays == 0)
                  Hari ini
                @elseif($lastLoginDays == 1)
                  Kemarin
                @else
                  {{ $lastLoginDays }} hari lalu
                @endif
              @else
                Belum pernah
              @endif
            </div>
          </div>
          <div class="avatar bg-warning">
            <i class="bx bx-time"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-history me-2"></i>
          Aktivitas Terbaru
        </h6>
      </div>
      <div class="card-body p-0">
        @php
          $recentTransactions = $user->stockTransactions()
                                   ->with('item')
                                   ->latest()
                                   ->take(5)
                                   ->get();
        @endphp
        
        @forelse($recentTransactions as $transaction)
          <div class="activity-item">
            <div class="activity-icon activity-transaction">
              <i class="bx bx-{{ $transaction->transaction_type === 'IN' ? 'plus' : ($transaction->transaction_type === 'OUT' ? 'minus' : 'edit') }}"></i>
            </div>
            <div>
              <div class="fw-semibold">
                {{ $transaction->transaction_type_text }} - {{ $transaction->item->item_name }}
              </div>
              <small class="text-muted">
                {{ $transaction->formatted_quantity }} {{ $transaction->item->unit }} • 
                {{ $transaction->created_at->diffForHumans() }}
              </small>
            </div>
          </div>
        @empty
          <div class="activity-item text-center">
            <div class="w-100">
              <i class="bx bx-inbox" style="font-size: 2rem; color: #ddd;"></i>
              <div class="fw-semibold text-muted">Belum ada aktivitas</div>
              <small class="text-muted">Aktivitas transaksi akan muncul di sini</small>
            </div>
          </div>
        @endforelse
        
        @if($recentTransactions->count() > 0)
          <div class="activity-item text-center border-top">
            <div class="w-100">
              <a href="{{ route('stock-transactions.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-list-ul me-1"></i>
                Lihat Semua Transaksi
              </a>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bx bx-edit me-2"></i>
          Edit Profile
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row">
            <!-- Full Name -->
            <div class="col-md-6 mb-3">
              <label for="full_name" class="form-label">Nama Lengkap *</label>
              <input type="text" class="form-control" id="full_name" name="full_name" 
                     value="{{ old('full_name', $user->full_name) }}" required>
            </div>

            <!-- Username -->
            <div class="col-md-6 mb-3">
              <label for="username" class="form-label">Username *</label>
              <input type="text" class="form-control" id="username" name="username" 
                     value="{{ old('username', $user->username) }}" required>
            </div>

            <!-- Email -->
            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" 
                     value="{{ old('email', $user->email) }}">
            </div>

            <!-- Phone -->
            <div class="col-md-6 mb-3">
              <label for="phone" class="form-label">No. Telepon</label>
              <input type="text" class="form-control" id="phone" name="phone" 
                     value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890">
            </div>

            <!-- Current Password -->
            <div class="col-12 mb-3">
              <label for="current_password" class="form-label">Password Saat Ini</label>
              <input type="password" class="form-control" id="current_password" name="current_password"
                     placeholder="Kosongkan jika tidak ingin mengubah password">
              <small class="text-muted">Wajib diisi jika ingin mengubah password</small>
            </div>

            <!-- New Password -->
            <div class="col-md-6 mb-3">
              <label for="password" class="form-label">Password Baru</label>
              <input type="password" class="form-control" id="password" name="password"
                     placeholder="Minimal 6 karakter">
            </div>

            <!-- Confirm Password -->
            <div class="col-md-6 mb-3">
              <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
              <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                     placeholder="Ulangi password baru">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>
            Batal
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Image upload preview (future implementation)
document.getElementById('imageUpload').addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      // Future: implement avatar upload
      console.log('File selected:', file.name);
    };
    reader.readAsDataURL(file);
  }
});

// Form validation
document.querySelector('#editProfileModal form').addEventListener('submit', function(e) {
  const password = document.getElementById('password').value;
  const passwordConfirm = document.getElementById('password_confirmation').value;
  const currentPassword = document.getElementById('current_password').value;
  
  if (password && !currentPassword) {
    e.preventDefault();
    alert('Password saat ini wajib diisi jika ingin mengubah password');
    document.getElementById('current_password').focus();
    return false;
  }
  
  if (password !== passwordConfirm) {
    e.preventDefault();
    alert('Konfirmasi password tidak cocok');
    document.getElementById('password_confirmation').focus();
    return false;
  }
});

// Auto close alerts
setTimeout(function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    const bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 5000);
</script>
@endpush
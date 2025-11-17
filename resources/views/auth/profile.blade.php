{{-- filepath: d:\xampp\htdocs\Chicking-BJM\resources\views\auth\profile.blade.php --}}

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
            {{-- Future: Avatar upload feature
            <div class="avatar-edit">
              <input type="file" id="imageUpload" accept=".png, .jpg, .jpeg" />
              <label for="imageUpload" title="Change Profile Picture"></label>
            </div>
            --}}
          </div>
        </div>
        <div class="col-md-9">
          <h3 class="mb-2 text-white">{{ $user->full_name }}</h3>
          <p class="mb-2 opacity-75">
            <i class="bx bx-briefcase me-2"></i>
            <span class="badge bg-white text-primary">{{ $user->role }}</span>
          </p>
          <p class="mb-2 opacity-75">
            <i class="bx bx-user-circle me-2"></i>
            {{ $user->username }}
          </p>
          
          {{-- ✅ Show Branch Info --}}
          @if($user->branch)
          <p class="mb-2 opacity-75">
            <i class="bx bx-building me-2"></i>
            {{ $user->branch->branch_name }}
          </p>
          @endif
          
          {{-- ✅ Show Warehouse Info --}}
          @if($user->warehouse)
          <p class="mb-2 opacity-75">
            <i class="bx bx-store me-2"></i>
            {{ $user->warehouse->warehouse_name }}
            <span class="badge badge-sm bg-black bg-opacity-25 ms-2">
              {{ ucfirst($user->warehouse->warehouse_type) }}
            </span>
          </p>
          @endif
          
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
                  <span class="badge bg-{{ $user->status === 'ACTIVE' ? 'success' : 'secondary' }}">
                    {{ $user->status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          {{-- ✅ Branch Info --}}
          @if($user->branch)
          <div class="col-md-6">
            <div class="info-item">
              <i class="bx bx-building"></i>
              <div>
                <small class="text-muted">Cabang</small>
                <div class="fw-semibold">{{ $user->branch->branch_name }}</div>
                @if($user->branch->location)
                <small class="text-muted">{{ $user->branch->location }}</small>
                @endif
              </div>
            </div>
          </div>
          @endif
          
          {{-- ✅ Warehouse Info --}}
          @if($user->warehouse)
          <div class="col-md-6">
            <div class="info-item">
              <i class="bx bx-store"></i>
              <div>
                <small class="text-muted">Warehouse Assignment</small>
                <div class="fw-semibold">
                  {{ $user->warehouse->warehouse_name }}
                  <span class="badge badge-sm bg-label-primary ms-1">
                    {{ ucfirst($user->warehouse->warehouse_type) }}
                  </span>
                </div>
                @if($user->warehouse->location)
                <small class="text-muted">{{ $user->warehouse->location }}</small>
                @endif
              </div>
            </div>
          </div>
          @endif
          
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
                @if($user->last_login_ip)
                <small class="text-muted">IP: {{ $user->last_login_ip }}</small>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ✅ Access & Permissions Card --}}
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-key me-2"></i>
          Akses & Permissions
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          {{-- Accessible Warehouses --}}
          @if($user->getAccessibleWarehouses()->count() > 0)
          <div class="col-12 mb-3">
            <h6 class="text-muted mb-2">
              <i class="bx bx-building me-1"></i>
              Warehouse Access
            </h6>
            <div class="d-flex flex-wrap gap-2">
              @foreach($user->getAccessibleWarehouses() as $warehouse)
                @php
                  $accessLevel = $user->getWarehouseAccessLevel($warehouse->id);
                  $accessBadge = $accessLevel === 'full' ? 'success' : 'info';
                  $accessIcon = $accessLevel === 'full' ? 'bx-check-shield' : 'bx-show';
                @endphp
                <span class="badge bg-label-{{ $accessBadge }}">
                  <i class="bx {{ $accessIcon }} me-1"></i>
                  {{ $warehouse->warehouse_name }}
                  <small class="opacity-75">({{ $accessLevel }})</small>
                </span>
              @endforeach
            </div>
          </div>
          @endif

          {{-- Permissions Summary --}}
          
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
          // ✅ Get transactions based on warehouse type
          $totalTransactions = 0;
          $todayTransactions = 0;
          $thisMonthTransactions = 0;

          if ($user->warehouse) {
            switch ($user->warehouse->warehouse_type) {
              case 'central':
                $totalTransactions = \App\Models\CentralStockTransaction::where('user_id', $user->id)->count();
                $todayTransactions = \App\Models\CentralStockTransaction::where('user_id', $user->id)
                  ->whereDate('transaction_date', today())->count();
                $thisMonthTransactions = \App\Models\CentralStockTransaction::where('user_id', $user->id)
                  ->whereMonth('transaction_date', now()->month)
                  ->whereYear('transaction_date', now()->year)->count();
                break;
              case 'branch':
                $totalTransactions = \App\Models\BranchStockTransaction::where('user_id', $user->id)->count();
                $todayTransactions = \App\Models\BranchStockTransaction::where('user_id', $user->id)
                  ->whereDate('transaction_date', today())->count();
                $thisMonthTransactions = \App\Models\BranchStockTransaction::where('user_id', $user->id)
                  ->whereMonth('transaction_date', now()->month)
                  ->whereYear('transaction_date', now()->year)->count();
                break;
              case 'outlet':
                $outletTrans = \App\Models\OutletStockTransaction::where('user_id', $user->id)->count();
                $kitchenTrans = \App\Models\KitchenStockTransaction::where('user_id', $user->id)->count();
                $totalTransactions = $outletTrans + $kitchenTrans;
                
                $todayTransactions = \App\Models\OutletStockTransaction::where('user_id', $user->id)
                  ->whereDate('transaction_date', today())->count()
                  + \App\Models\KitchenStockTransaction::where('user_id', $user->id)
                  ->whereDate('transaction_date', today())->count();
                
                $thisMonthTransactions = \App\Models\OutletStockTransaction::where('user_id', $user->id)
                  ->whereMonth('transaction_date', now()->month)
                  ->whereYear('transaction_date', now()->year)->count()
                  + \App\Models\KitchenStockTransaction::where('user_id', $user->id)
                  ->whereMonth('transaction_date', now()->month)
                  ->whereYear('transaction_date', now()->year)->count();
                break;
            }
          }

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
          // ✅ Get recent transactions based on warehouse type
          $recentTransactions = collect();
          
          if ($user->warehouse) {
            switch ($user->warehouse->warehouse_type) {
              case 'central':
                $recentTransactions = \App\Models\CentralStockTransaction::where('user_id', $user->id)
                  ->with('item')
                  ->latest('transaction_date')
                  ->take(5)
                  ->get();
                break;
              case 'branch':
                $recentTransactions = \App\Models\BranchStockTransaction::where('user_id', $user->id)
                  ->with('item')
                  ->latest('transaction_date')
                  ->take(5)
                  ->get();
                break;
              case 'outlet':
                $outlet = \App\Models\OutletStockTransaction::where('user_id', $user->id)
                  ->with('item')
                  ->latest('transaction_date')
                  ->take(3)
                  ->get();
                $kitchen = \App\Models\KitchenStockTransaction::where('user_id', $user->id)
                  ->with('item')
                  ->latest('transaction_date')
                  ->take(2)
                  ->get();
                $recentTransactions = $outlet->merge($kitchen)->sortByDesc('transaction_date')->take(5);
                break;
            }
          }
        @endphp
        
        @forelse($recentTransactions as $transaction)
          <div class="activity-item">
            <div class="activity-icon activity-transaction">
              <i class="bx bx-{{ $transaction->transaction_type === 'IN' ? 'import' : ($transaction->transaction_type === 'OUT' ? 'export' : 'adjust') }}"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold">
                {{ $transaction->transaction_type }} - {{ Str::limit($transaction->item->item_name, 25) }}
              </div>
              <small class="text-muted">
                {{ number_format($transaction->quantity, 2) }} {{ $transaction->item->unit }} • 
                {{ $transaction->transaction_date->diffForHumans() }}
              </small>
            </div>
          </div>
        @empty
          <div class="activity-item text-center py-5">
            <div class="w-100">
              <i class="bx bx-inbox" style="font-size: 2rem; color: #ddd;"></i>
              <div class="fw-semibold text-muted mt-2">Belum ada aktivitas</div>
              <small class="text-muted">Aktivitas transaksi akan muncul di sini</small>
            </div>
          </div>
        @endforelse
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
      <form action="{{ route('profile.update') }}" method="POST" id="editProfileForm">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row">
            <!-- Full Name -->
            <div class="col-md-6 mb-3">
              <label for="full_name" class="form-label">
                Nama Lengkap <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                     id="full_name" name="full_name" 
                     value="{{ old('full_name', $user->full_name) }}" required>
              @error('full_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Username -->
            <div class="col-md-6 mb-3">
              <label for="username" class="form-label">
                Username <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control @error('username') is-invalid @enderror" 
                     id="username" name="username" 
                     value="{{ old('username', $user->username) }}" required>
              @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Email -->
            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control @error('email') is-invalid @enderror" 
                     id="email" name="email" 
                     value="{{ old('email', $user->email) }}">
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Phone -->
            <div class="col-md-6 mb-3">
              <label for="phone" class="form-label">No. Telepon</label>
              <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                     id="phone" name="phone" 
                     value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890">
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
              <div class="alert alert-info">
                <i class="bx bx-info-circle me-2"></i>
                <small>Untuk mengubah password, isi field di bawah ini. Kosongkan jika tidak ingin mengubah password.</small>
              </div>
            </div>

            <!-- Current Password -->
            <div class="col-12 mb-3">
              <label for="current_password" class="form-label">Password Saat Ini</label>
              <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                     id="current_password" name="current_password"
                     placeholder="Wajib diisi jika ingin mengubah password">
              @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- New Password -->
            <div class="col-md-6 mb-3">
              <label for="password" class="form-label">Password Baru</label>
              <input type="password" class="form-control @error('password') is-invalid @enderror" 
                     id="password" name="password"
                     placeholder="Minimal 6 karakter">
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Confirm Password -->
            <div class="col-md-6 mb-3">
              <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
              <input type="password" class="form-control" 
                     id="password_confirmation" name="password_confirmation"
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
// Form validation
document.getElementById('editProfileForm').addEventListener('submit', function(e) {
  const password = document.getElementById('password').value;
  const passwordConfirm = document.getElementById('password_confirmation').value;
  const currentPassword = document.getElementById('current_password').value;
  
  if (password && !currentPassword) {
    e.preventDefault();
    alert('⚠️ Password saat ini wajib diisi jika ingin mengubah password');
    document.getElementById('current_password').focus();
    return false;
  }
  
  if (password && password.length < 6) {
    e.preventDefault();
    alert('⚠️ Password baru minimal 6 karakter');
    document.getElementById('password').focus();
    return false;
  }
  
  if (password !== passwordConfirm) {
    e.preventDefault();
    alert('⚠️ Konfirmasi password tidak cocok');
    document.getElementById('password_confirmation').focus();
    return false;
  }
});

// Auto close alerts
setTimeout(function() {
  const alerts = document.querySelectorAll('.alert-dismissible');
  alerts.forEach(alert => {
    const bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 5000);

// Show edit modal if there are errors
@if($errors->any())
  const editModal = new bootstrap.Modal(document.getElementById('editProfileModal'));
  editModal.show();
@endif
</script>
@endpush
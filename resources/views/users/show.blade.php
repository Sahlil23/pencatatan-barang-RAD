{{-- filepath: d:\xampp\htdocs\Chicking-BJM\resources\views\users\show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail User - ' . $user->full_name . ' - Chicking BJM')

@push('styles')
<style>
.user-avatar-section {
    padding: 2rem 0 1rem;
}

.avatar-xl {
    width: 6rem;
    height: 6rem;
}

.avatar-xl .avatar-initial {
    font-size: 2.5rem;
}

.info-container .list-unstyled li {
    padding: 0.375rem 0;
}

.timeline {
    position: relative;
    padding-left: 1.5rem;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 2rem;
    width: 2px;
    height: calc(100% - 1rem);
    background-color: #ddd;
}

.timeline-indicator-advanced {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: white;
    border: 2px solid;
}

.timeline-indicator-success {
    border-color: #28c76f;
    color: #28c76f;
}

.timeline-indicator-info {
    border-color: #00cfe8;
    color: #00cfe8;
}

.timeline-indicator-warning {
    border-color: #ff9f43;
    color: #ff9f43;
}

.timeline-indicator-primary {
    border-color: #696cff;
    color: #696cff;
}

.timeline-event {
    margin-left: 1rem;
}

.nav-pills .nav-link.active {
    background-color: #696cff;
}

.table td {
    vertical-align: middle;
}

.user-stat-card {
    transition: transform 0.2s;
}

.user-stat-card:hover {
    transform: translateY(-5px);
}

@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .col-md-3 {
        margin-bottom: 0.5rem;
    }
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
        <li class="breadcrumb-item">
          <a href="{{ route('users.index') }}">Users</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ $user->full_name }}</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <!-- User Information -->
  <div class="col-xl-4 col-lg-5">
    <div class="card mb-4">
      <div class="card-body">
        <div class="user-avatar-section">
          <div class="d-flex align-items-center flex-column">
            <div class="avatar avatar-xl mx-auto mb-3">
              <span class="avatar-initial rounded-circle bg-label-{{ $user->isManager() ? 'danger' : 'primary' }}" style="font-size: 2.5rem;">
                {{ strtoupper(substr($user->full_name, 0, 2)) }}
              </span>
            </div>
            <div class="user-info text-center">
              <h4 class="mb-2">{{ $user->full_name }}</h4>
              <div class="mb-2">
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
                <span class="badge bg-{{ $roleColor }} me-2">
                  {{ $user->role }}
                </span>
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
              </div>
              <small class="text-muted">
                <i class="bx bx-user me-1"></i>
                {{ $user->username }}
              </small>
            </div>
          </div>
        </div>
        
        <!-- User Stats -->
        <div class="d-flex justify-content-around flex-wrap mt-4 pt-3 pb-2">
          <div class="d-flex align-items-start me-4 mt-3 gap-3">
            <span class="badge bg-label-primary p-2 rounded">
              <i class="bx bx-building bx-sm"></i>
            </span>
            <div>
              <h5 class="mb-0">
                @if($user->branch)
                  {{ $user->branch->branch_name }}
                @else
                  <span class="text-muted">-</span>
                @endif
              </h5>
              <span>Branch</span>
            </div>
          </div>
          <div class="d-flex align-items-start mt-3 gap-3">
            <span class="badge bg-label-success p-2 rounded">
              <i class="bx bx-calendar bx-sm"></i>
            </span>
            <div>
              <h5 class="mb-0">{{ $user->created_at->format('M Y') }}</h5>
              <span>Bergabung</span>
            </div>
          </div>
        </div>
        
        <!-- User Details -->
        <h5 class="pb-2 border-bottom mb-4 mt-4">Detail Informasi</h5>
        <div class="info-container">
          <ul class="list-unstyled">
            <li class="mb-3">
              <span class="fw-medium me-2">ID User:</span>
              <span>#{{ $user->id }}</span>
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">Username:</span>
              <code>{{ $user->username }}</code>
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">Email:</span>
              @if($user->email)
                <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                  {{ $user->email }}
                </a>
              @else
                <span class="text-muted">Belum diisi</span>
              @endif
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">No. Telepon:</span>
              @if($user->phone)
                <a href="tel:{{ $user->phone }}" class="text-decoration-none">
                  {{ $user->phone }}
                </a>
              @else
                <span class="text-muted">Belum diisi</span>
              @endif
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">Warehouse:</span>
              @if($user->warehouse)
                <div class="d-flex align-items-center">
                  <span class="badge bg-label-info me-2">{{ ucfirst($user->warehouse->warehouse_type) }}</span>
                  {{ $user->warehouse->warehouse_name }}
                </div>
              @else
                <span class="text-muted">Tidak ada warehouse</span>
              @endif
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">Dibuat:</span>
              <span>{{ $user->created_at->format('d M Y, H:i') }}</span>
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">Terakhir Update:</span>
              <span>{{ $user->updated_at->format('d M Y, H:i') }}</span>
            </li>
            <li class="mb-3">
              <span class="fw-medium me-2">Terakhir Login:</span>
              <span>
                @if($user->last_login_at)
                  {{ $user->last_login_at->format('d M Y, H:i') }}
                  <small class="text-muted d-block">({{ $user->last_login_at->diffForHumans() }})</small>
                @else
                  <span class="text-muted">Belum pernah login</span>
                @endif
              </span>
            </li>
          </ul>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex justify-content-center gap-2 pt-3">
          @can('update', $user)
          <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
            <i class="bx bx-edit-alt me-1"></i>
            Edit
          </a>
          @endcan
          
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bx bx-dots-vertical-rounded me-1"></i>
              Actions
            </button>
            <div class="dropdown-menu">
              @can('resetPassword', $user)
              <a class="dropdown-item" href="#">
                <i class="bx bx-key me-2"></i>
                Reset Password
              </a>
              @endcan
              
              @can('changeStatus', $user)
              <form action="#" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $user->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE' }}">
                <button type="submit" class="dropdown-item" onclick="return confirm('Ubah status user ini?')">
                  <i class="bx bx-{{ $user->status === 'ACTIVE' ? 'user-x' : 'user-check' }} me-2"></i>
                  {{ $user->status === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </form>
              @endcan
              
              @if($user->id !== Auth::id())
                @can('delete', $user)
                <div class="dropdown-divider"></div>
                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger" 
                          onclick="return confirm('⚠️ Yakin ingin menghapus user {{ $user->username }}?')">
                    <i class="bx bx-trash me-2"></i>
                    Hapus User
                  </button>
                </form>
                @endcan
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats Card -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="bx bx-chart me-2"></i>
          Quick Stats
        </h5>
      </div>
      <div class="card-body">
        <div class="row text-center">
          <div class="col-4">
            <div class="d-flex flex-column user-stat-card">
              <div class="avatar mx-auto mb-2">
                <span class="avatar-initial bg-success rounded">
                  <i class="bx bx-time-five"></i>
                </span>
              </div>
              <span class="fw-medium">{{ $stats['days_active'] }}</span>
              <small class="text-muted">Hari Aktif</small>
            </div>
          </div>
          <div class="col-4">
            <div class="d-flex flex-column user-stat-card">
              <div class="avatar mx-auto mb-2">
                <span class="avatar-initial bg-info rounded">
                  <i class="bx bx-calendar"></i>
                </span>
              </div>
              <span class="fw-medium">{{ $user->created_at->format('M Y') }}</span>
              <small class="text-muted">Bergabung</small>
            </div>
          </div>
          <div class="col-4">
            <div class="d-flex flex-column user-stat-card">
              <div class="avatar mx-auto mb-2">
                <span class="avatar-initial bg-{{ $user->status === 'ACTIVE' ? 'success' : 'secondary' }} rounded">
                  <i class="bx bx-user-{{ $user->status === 'ACTIVE' ? 'check' : 'x' }}"></i>
                </span>
              </div>
              <span class="fw-medium">{{ $user->status }}</span>
              <small class="text-muted">Status</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- User Activity & Timeline -->
  <div class="col-xl-8 col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-history me-2"></i>
          Activity Timeline
        </h5>
      </div>
      <div class="card-body">
        <div class="timeline">
          <!-- User Created -->
          <div class="timeline-item">
            <div class="timeline-indicator-advanced timeline-indicator-success">
              <i class="bx bx-user-plus"></i>
            </div>
            <div class="timeline-event">
              <div class="timeline-header border-bottom mb-3">
                <h6 class="mb-0">User Created</h6>
                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
              </div>
              <p class="mb-2">
                User <strong>{{ $user->full_name }}</strong> dibuat dengan role 
                <span class="badge bg-{{ $roleColor }}">{{ $user->role }}</span>
              </p>
              <small class="text-muted">
                <i class="bx bx-calendar me-1"></i>
                {{ $user->created_at->format('d F Y, H:i') }}
              </small>
            </div>
          </div>
          
          <!-- Last Login -->
          @if($user->last_login_at)
          <div class="timeline-item">
            <div class="timeline-indicator-advanced timeline-indicator-info">
              <i class="bx bx-log-in"></i>
            </div>
            <div class="timeline-event">
              <div class="timeline-header border-bottom mb-3">
                <h6 class="mb-0">Last Login</h6>
                <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
              </div>
              <p class="mb-2">Login terakhir</p>
              <small class="text-muted">
                <i class="bx bx-calendar me-1"></i>
                {{ $user->last_login_at->format('d F Y, H:i') }}
              </small>
            </div>
          </div>
          @endif
          
          <!-- Profile Updated -->
          @if($user->updated_at->ne($user->created_at))
          <div class="timeline-item">
            <div class="timeline-indicator-advanced timeline-indicator-warning">
              <i class="bx bx-edit"></i>
            </div>
            <div class="timeline-event">
              <div class="timeline-header border-bottom mb-3">
                <h6 class="mb-0">Profile Updated</h6>
                <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
              </div>
              <p class="mb-2">Informasi profile diperbarui</p>
              <small class="text-muted">
                <i class="bx bx-calendar me-1"></i>
                {{ $user->updated_at->format('d F Y, H:i') }}
              </small>
            </div>
          </div>
          @endif

          <!-- End Timeline Indicator -->
          <div class="timeline-item">
            <div class="timeline-indicator-advanced timeline-indicator-primary">
              <i class="bx bx-check"></i>
            </div>
            <div class="timeline-event">
              <p class="text-muted mb-0">Up to date</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Account Overview -->
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Account Overview
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Performance Metrics -->
          <div class="col-md-6 mb-4">
            <h6 class="text-muted mb-3">Account Information</h6>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span>User ID</span>
              <span class="text-muted">#{{ $user->id }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span>Lama Bergabung</span>
              <span class="text-muted">{{ $user->created_at->diffForHumans() }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span>Status Akun</span>
              <span class="badge bg-{{ $statusColor }}">
                {{ $user->status }}
              </span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span>Role Level</span>
              <span class="badge bg-{{ $roleColor }}">
                {{ $user->role }}
              </span>
            </div>
          </div>
          
          <!-- Assignment Info -->
          <div class="col-md-6 mb-4">
            <h6 class="text-muted mb-3">Assignment</h6>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span>Branch</span>
              <span class="text-muted">
                @if($user->branch)
                  {{ $user->branch->branch_name }}
                @else
                  <em>No branch</em>
                @endif
              </span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span>Warehouse</span>
              <span class="text-muted">
                @if($user->warehouse)
                  {{ $user->warehouse->warehouse_name }}
                @else
                  <em>No warehouse</em>
                @endif
              </span>
            </div>
            @if($user->warehouse)
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span>Warehouse Type</span>
              <span class="badge bg-label-info">
                {{ ucfirst($user->warehouse->warehouse_type) }}
              </span>
            </div>
            @endif
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span>Can Manage Users</span>
              <span class="badge bg-{{ $user->can_manage_users ? 'success' : 'secondary' }}">
                {{ $user->can_manage_users ? 'Yes' : 'No' }}
              </span>
            </div>
          </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="border-top pt-4">
          <h6 class="text-muted mb-3">Quick Actions</h6>
          <div class="d-flex gap-2 flex-wrap">
            @can('update', $user)
            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
              <i class="bx bx-edit-alt me-1"></i>
              Edit User
            </a>
            @endcan
            
            @can('resetPassword', $user)
            <a href="#" class="btn btn-outline-warning btn-sm">
              <i class="bx bx-key me-1"></i>
              Reset Password
            </a>
            @endcan
            
            @can('changeStatus', $user)
            <form action="#" method="POST" class="d-inline">
              @csrf
              @method('PATCH')
              <input type="hidden" name="status" value="{{ $user->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE' }}">
              <button type="submit" class="btn btn-outline-{{ $user->status === 'ACTIVE' ? 'secondary' : 'success' }} btn-sm" 
                      onclick="return confirm('Ubah status user ini?')">
                <i class="bx bx-{{ $user->status === 'ACTIVE' ? 'user-x' : 'user-check' }} me-1"></i>
                {{ $user->status === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan' }}
              </button>
            </form>
            @endcan
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Add smooth scroll behavior
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  // Animate timeline items on scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, observerOptions);

  document.querySelectorAll('.timeline-item').forEach(item => {
    item.style.opacity = '0';
    item.style.transform = 'translateY(20px)';
    item.style.transition = 'all 0.5s ease';
    observer.observe(item);
  });
});
</script>
@endpush
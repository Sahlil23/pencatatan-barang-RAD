@extends('layouts.admin')

@section('title', 'Detail User - ' . $user->full_name . ' - Chicking BJM')

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
              <span class="avatar-initial rounded-circle bg-label-primary" style="font-size: 2.5rem;">
                {{ strtoupper(substr($user->full_name, 0, 2)) }}
              </span>
            </div>
            <div class="user-info text-center">
              <h4 class="mb-2">{{ $user->full_name }}</h4>
              <div class="mb-2">
                <span class="badge bg-label-{{ $user->role === 'admin' ? 'danger' : 'info' }} me-2">
                  {{ $user->getRoleName() }}
                </span>
                <span class="badge bg-label-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                  {{ $user->getStatusName() }}
                </span>
              </div>
              <small class="text-muted"> {{ $user->username }}</small>
            </div>
          </div>
        </div>
        
        <!-- User Stats -->
        <div class="d-flex justify-content-around flex-wrap mt-4 pt-3 pb-2">
          <div class="d-flex align-items-start me-4 mt-3 gap-3">
            <span class="badge bg-label-primary p-2 rounded">
              <i class="bx bx-transfer bx-sm"></i>
            </span>
            <div>
              <h5 class="mb-0">{{ $user->stockTransactions->count() }}</h5>
              <span>Total Transaksi</span>
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
        <h5 class="pb-2 border-bottom mb-4">Detail Informasi</h5>
        <div class="info-container">
          <ul class="list-unstyled">
            <li class="mb-3">
              <span class="fw-medium me-2">ID User:</span>
              <span>#{{ $user->id }}</span>
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
                  <small class="text-muted">({{ $user->last_login_at->diffForHumans() }})</small>
                @else
                  <span class="text-muted">Belum pernah login</span>
                @endif
              </span>
            </li>
          </ul>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex justify-content-center pt-3">
          <a href="{{ route('users.edit', $user) }}" class="btn btn-primary me-3">
            <i class="bx bx-edit-alt me-1"></i>
            Edit User
          </a>
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bx bx-dots-vertical-rounded me-1"></i>
              Actions
            </button>
            <div class="dropdown-menu">
              <button class="dropdown-item" onclick="resetPassword()">
                <i class="bx bx-key me-2"></i>
                Reset Password
              </button>
              <button class="dropdown-item" onclick="toggleStatus()">
                <i class="bx bx-{{ $user->status === 'active' ? 'user-x' : 'user-check' }} me-2"></i>
                {{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }} User
              </button>
              @if($user->id !== Auth::id())
                <div class="dropdown-divider"></div>
                <button class="dropdown-item text-danger" onclick="confirmDelete()">
                  <i class="bx bx-trash me-2"></i>
                  Hapus User
                </button>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Activity Summary Card -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="bx bx-chart me-2"></i>
          Ringkasan Aktivitas
        </h5>
      </div>
      <div class="card-body">
        @php
          $totalTransactions = $user->stockTransactions->count();
          $todayTransactions = $user->stockTransactions->where('created_at', '>=', today())->count();
          $thisMonthTransactions = $user->stockTransactions->filter(function($t) {
            return $t->created_at->month === now()->month && $t->created_at->year === now()->year;
          })->count();
          
          $inTransactions = $user->stockTransactions->where('transaction_type', 'IN')->count();
          $outTransactions = $user->stockTransactions->where('transaction_type', 'OUT')->count();
          $adjustmentTransactions = $user->stockTransactions->where('transaction_type', 'ADJUSTMENT')->count();
        @endphp
        
        <div class="row text-center">
          <div class="col-4">
            <div class="d-flex flex-column">
              <div class="avatar mx-auto mb-2">
                <span class="avatar-initial bg-success rounded">
                  <i class="bx bx-trending-up"></i>
                </span>
              </div>
              <span class="fw-medium">{{ $todayTransactions }}</span>
              <small class="text-muted">Hari Ini</small>
            </div>
          </div>
          <div class="col-4">
            <div class="d-flex flex-column">
              <div class="avatar mx-auto mb-2">
                <span class="avatar-initial bg-info rounded">
                  <i class="bx bx-calendar"></i>
                </span>
              </div>
              <span class="fw-medium">{{ $thisMonthTransactions }}</span>
              <small class="text-muted">Bulan Ini</small>
            </div>
          </div>
          <div class="col-4">
            <div class="d-flex flex-column">
              <div class="avatar mx-auto mb-2">
                <span class="avatar-initial bg-primary rounded">
                  <i class="bx bx-bar-chart"></i>
                </span>
              </div>
              <span class="fw-medium">{{ $totalTransactions }}</span>
              <small class="text-muted">Total</small>
            </div>
          </div>
        </div>
        
        <hr>
        
        <!-- Transaction Type Breakdown -->
        <div class="mt-4">
          <small class="text-muted d-block mb-3">Jenis Transaksi</small>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="d-flex align-items-center">
              <i class="bx bx-plus-circle text-success me-2"></i>
              Stock In
            </span>
            <span class="badge bg-success">{{ $inTransactions }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="d-flex align-items-center">
              <i class="bx bx-minus-circle text-danger me-2"></i>
              Stock Out
            </span>
            <span class="badge bg-danger">{{ $outTransactions }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="d-flex align-items-center">
              <i class="bx bx-edit-alt text-warning me-2"></i>
              Adjustment
            </span>
            <span class="badge bg-warning">{{ $adjustmentTransactions }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- User Activity & Transactions -->
  <div class="col-xl-8 col-lg-7">
    <div class="nav-align-top mb-4">
      <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item">
          <button
            type="button"
            class="nav-link active"
            role="tab"
            data-bs-toggle="tab"
            data-bs-target="#navs-pills-transactions"
            aria-controls="navs-pills-transactions"
            aria-selected="true"
          >
            <i class="bx bx-transfer me-1"></i>
            Transaksi ({{ $user->stockTransactions->count() }})
          </button>
        </li>
        <li class="nav-item">
          <button
            type="button"
            class="nav-link"
            role="tab"
            data-bs-toggle="tab"
            data-bs-target="#navs-pills-activity"
            aria-controls="navs-pills-activity"
            aria-selected="false"
          >
            <i class="bx bx-history me-1"></i>
            Activity Log
          </button>
        </li>
        <li class="nav-item">
          <button
            type="button"
            class="nav-link"
            role="tab"
            data-bs-toggle="tab"
            data-bs-target="#navs-pills-overview"
            aria-controls="navs-pills-overview"
            aria-selected="false"
          >
            <i class="bx bx-chart me-1"></i>
            Overview
          </button>
        </li>
      </ul>
      <div class="tab-content">
        <!-- Transactions Tab -->
        <div class="tab-pane fade show active" id="navs-pills-transactions">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Riwayat Transaksi</h5>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="exportTransactions()">
                  <i class="bx bx-export me-1"></i>
                  Export
                </button>
                @if(Auth::user()->isAdmin())
                  <a href="{{ route('stock-transactions.create') }}?user_id={{ $user->id }}" class="btn btn-sm btn-primary">
                    <i class="bx bx-plus me-1"></i>
                    Add Transaction
                  </a>
                @endif
              </div>
            </div>
            
            <!-- Filters -->
            <div class="card-body border-bottom">
              <div class="row g-3">
                <div class="col-md-3">
                  <select class="form-select" id="filterType">
                    <option value="">Semua Jenis</option>
                    <option value="IN">Stock In</option>
                    <option value="OUT">Stock Out</option>
                    <option value="ADJUSTMENT">Adjustment</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <input type="date" class="form-control" id="filterStartDate" placeholder="Tanggal Mulai">
                </div>
                <div class="col-md-3">
                  <input type="date" class="form-control" id="filterEndDate" placeholder="Tanggal Akhir">
                </div>
                <div class="col-md-3">
                  <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                    <i class="bx bx-x me-1"></i>
                    Clear
                  </button>
                </div>
              </div>
            </div>

            <div class="table-responsive text-nowrap">
              <table class="table table-hover" id="transactionsTable">
                <thead>
                  <tr>
                    <th>Tanggal</th>
                    <th>Item</th>
                    <th>Jenis</th>
                    <th>Quantity</th>
                    <th>Notes</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                  @forelse($user->stockTransactions->sortByDesc('created_at') as $transaction)
                    <tr data-type="{{ $transaction->transaction_type }}" data-date="{{ $transaction->created_at->format('Y-m-d') }}">
                      <td>
                        <div class="d-flex flex-column">
                          <span class="fw-medium">{{ $transaction->created_at->format('d M Y') }}</span>
                          <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial bg-label-primary rounded">
                              {{ strtoupper(substr($transaction->item->item_name, 0, 2)) }}
                            </span>
                          </div>
                          <div class="d-flex flex-column">
                            <h6 class="mb-0">{{ $transaction->item->item_name }}</h6>
                            <small class="text-muted">{{ $transaction->item->sku }}</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        @if($transaction->transaction_type === 'IN')
                          <span class="badge bg-success">
                            <i class="bx bx-plus me-1"></i>Stock In
                          </span>
                        @elseif($transaction->transaction_type === 'OUT')
                          <span class="badge bg-danger">
                            <i class="bx bx-minus me-1"></i>Stock Out
                          </span>
                        @else
                          <span class="badge bg-warning">
                            <i class="bx bx-edit-alt me-1"></i>Adjustment
                          </span>
                        @endif
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <span class="fw-medium me-2">
                            {{ $transaction->transaction_type === 'OUT' ? '-' : '+' }}{{ number_format($transaction->quantity, 2) }}
                          </span>
                          <small class="text-muted">{{ $transaction->item->unit }}</small>
                        </div>
                      </td>
                      <td>
                        <span class="text-truncate" style="max-width: 150px;">
                          {{ $transaction->notes ?: '-' }}
                        </span>
                      </td>
                      <td>
                        <div class="dropdown">
                          <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('stock-transactions.show', $transaction) }}">
                              <i class="bx bx-show me-1"></i> View Details
                            </a>
                          </div>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                          <i class="bx bx-package" style="font-size: 3rem; color: #ddd;"></i>
                          <h6 class="mt-2 mb-1">Belum Ada Transaksi</h6>
                          <p class="text-muted mb-3">User ini belum melakukan transaksi apapun.</p>
                          @if(Auth::user()->isAdmin())
                            <a href="{{ route('stock-transactions.create') }}?user_id={{ $user->id }}" class="btn btn-primary">
                              <i class="bx bx-plus me-1"></i>
                              Tambah Transaksi Pertama
                            </a>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Activity Log Tab -->
        <div class="tab-pane fade" id="navs-pills-activity">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Activity Log</h5>
            </div>
            <div class="card-body">
              <div class="timeline">
                <div class="timeline-item">
                  <div class="timeline-indicator-advanced timeline-indicator-success">
                    <i class="bx bx-user-plus"></i>
                  </div>
                  <div class="timeline-event">
                    <div class="timeline-header border-bottom mb-3">
                      <h6 class="mb-0">User Created</h6>
                      <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-2">User {{ $user->full_name }} dibuat dengan role {{ $user->getRoleName() }}</p>
                  </div>
                </div>
                
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
                    <p class="mb-2">Login terakhir pada {{ $user->last_login_at->format('d F Y, H:i') }}</p>
                  </div>
                </div>
                @endif
                
                @if($user->updated_at != $user->created_at)
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
                  </div>
                </div>
                @endif
                
                @foreach($user->stockTransactions->take(5) as $transaction)
                <div class="timeline-item">
                  <div class="timeline-indicator-advanced timeline-indicator-primary">
                    <i class="bx bx-{{ $transaction->transaction_type === 'IN' ? 'plus' : ($transaction->transaction_type === 'OUT' ? 'minus' : 'edit') }}"></i>
                  </div>
                  <div class="timeline-event">
                    <div class="timeline-header border-bottom mb-3">
                      <h6 class="mb-0">{{ $transaction->transaction_type_text }}</h6>
                      <small class="text-muted">{{ $transaction->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-2">
                      {{ $transaction->transaction_type === 'OUT' ? 'Mengurangi' : 'Menambah' }} 
                      stock {{ $transaction->item->item_name }} sebanyak 
                      {{ number_format($transaction->quantity, 2) }} {{ $transaction->item->unit }}
                    </p>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <!-- Overview Tab -->
        <div class="tab-pane fade" id="navs-pills-overview">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">User Overview</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Performance Metrics -->
                <div class="col-md-6 mb-4">
                  <h6 class="text-muted mb-3">Performance Metrics</h6>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Aktifitas Hari Ini</span>
                    <span class="badge bg-primary">{{ $todayTransactions }}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Rata-rata Harian</span>
                    <span class="badge bg-info">
                      {{ $user->created_at->diffInDays(now()) > 0 ? round($totalTransactions / $user->created_at->diffInDays(now()), 1) : $totalTransactions }}
                    </span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Status Akun</span>
                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                      {{ $user->getStatusName() }}
                    </span>
                  </div>
                </div>
                
                <!-- Account Info -->
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
                    <span>Total Transaksi</span>
                    <span class="text-muted">{{ number_format($totalTransactions) }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Quick Actions -->
              <div class="border-top pt-4">
                <h6 class="text-muted mb-3">Quick Actions</h6>
                <div class="d-flex gap-2 flex-wrap">
                  <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-edit-alt me-1"></i>
                    Edit User
                  </a>
                  <button class="btn btn-outline-warning btn-sm" onclick="resetPassword()">
                    <i class="bx bx-key me-1"></i>
                    Reset Password
                  </button>
                  <button class="btn btn-outline-{{ $user->status === 'active' ? 'secondary' : 'success' }} btn-sm" onclick="toggleStatus()">
                    <i class="bx bx-{{ $user->status === 'active' ? 'user-x' : 'user-check' }} me-1"></i>
                    {{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                  @if(Auth::user()->isAdmin())
                    <a href="{{ route('stock-transactions.create') }}?user_id={{ $user->id }}" class="btn btn-outline-info btn-sm">
                      <i class="bx bx-plus me-1"></i>
                      Add Transaction
                    </a>
                  @endif
                </div>
              </div>
            </div>
          </div>
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
        <h5 class="modal-title">{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }} User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin {{ $user->status === 'active' ? 'menonaktifkan' : 'mengaktifkan' }} user <strong>"{{ $user->full_name }}"</strong>?</p>
        @if($user->status === 'active')
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
          <button type="submit" class="btn btn-{{ $user->status === 'active' ? 'warning' : 'success' }}">
            Ya, {{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
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
    // Filter functionality
    const filterType = document.getElementById('filterType');
    const filterStartDate = document.getElementById('filterStartDate');
    const filterEndDate = document.getElementById('filterEndDate');
    const tableRows = document.querySelectorAll('#transactionsTable tbody tr[data-type]');

    function filterTransactions() {
        const selectedType = filterType.value;
        const startDate = filterStartDate.value;
        const endDate = filterEndDate.value;

        tableRows.forEach(row => {
            const rowType = row.dataset.type;
            const rowDate = row.dataset.date;

            const matchesType = !selectedType || rowType === selectedType;
            const matchesStartDate = !startDate || rowDate >= startDate;
            const matchesEndDate = !endDate || rowDate <= endDate;

            if (matchesType && matchesStartDate && matchesEndDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterType.addEventListener('change', filterTransactions);
    filterStartDate.addEventListener('change', filterTransactions);
    filterEndDate.addEventListener('change', filterTransactions);

    // Clear filters
    window.clearFilters = function() {
        filterType.value = '';
        filterStartDate.value = '';
        filterEndDate.value = '';
        filterTransactions();
    };

    // Modal functions
    window.resetPassword = function() {
        const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
        modal.show();
    };

    window.toggleStatus = function() {
        const modal = new bootstrap.Modal(document.getElementById('toggleStatusModal'));
        modal.show();
    };

    window.confirmDelete = function() {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    };

    // Export function
    window.exportTransactions = function() {
        window.open('/users/{{ $user->id }}/export-transactions', '_blank');
    };
});
</script>
@endpush

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
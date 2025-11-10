@extends('layouts.admin')

@section('title', 'Edit Warehouse - Chicking BJM')

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
          <a href="{{ route('warehouses.index') }}">Data Warehouse</a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ route('warehouses.show', $warehouse->id) }}">{{ $warehouse->warehouse_name }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Page Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h4 class="card-title mb-2">
              <i class="bx bx-edit me-2"></i>
              Edit Warehouse: {{ $warehouse->warehouse_name }}
            </h4>
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-label-secondary">{{ $warehouse->warehouse_code }}</span>
              <span class="badge bg-{{ $warehouse->warehouse_type === 'central' ? 'primary' : 'info' }}">
                {{ ucfirst($warehouse->warehouse_type) }}
              </span>
              <span class="badge bg-{{ $warehouse->status === 'ACTIVE' ? 'success' : ($warehouse->status === 'INACTIVE' ? 'warning' : 'danger') }}">
                {{ $warehouse->status }}
              </span>
            </div>
            <p class="card-text text-muted mb-0">
              Ubah informasi warehouse di form di bawah ini
            </p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-outline-info">
              <i class="bx bx-show me-1"></i>
              View Details
            </a>
            <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Form -->
<form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST" id="editWarehouseForm">
  @csrf
  @method('PUT')
  
  <div class="row">
    <!-- Left Column - Main Information -->
    <div class="col-xl-8">
      <!-- Basic Information -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bx bx-info-circle me-2"></i>
            Informasi Dasar
          </h5>
          <small class="text-muted">Wajib diisi</small>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Warehouse Type -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="warehouse_type">
                Tipe Warehouse <span class="text-danger">*</span>
              </label>
              <select class="form-select @error('warehouse_type') is-invalid @enderror" 
                      id="warehouse_type" name="warehouse_type" required>
                <option value="">Pilih tipe warehouse...</option>
                <option value="central" {{ old('warehouse_type', $warehouse->warehouse_type) == 'central' ? 'selected' : '' }}>
                  Central Warehouse
                </option>
                <option value="branch" {{ old('warehouse_type', $warehouse->warehouse_type) == 'branch' ? 'selected' : '' }}>
                  Branch Warehouse
                </option>
                <option value="outlet" {{ old('warehouse_type', $warehouse->warehouse_type) == 'outlet' ? 'selected' : '' }}>
                  Outlet Warehouse
                </option>
              </select>
              @error('warehouse_type')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">
                <i class="bx bx-info-circle me-1"></i>
                Central untuk distribusi utama, Branch untuk cabang tertentu
              </div>
            </div>

            <!-- Branch (Conditional) -->
            <div class="col-md-6 mb-3" id="branch_container" style="display: {{ old('warehouse_type', $warehouse->warehouse_type) == 'branch' ? 'block' : 'none' }};">
              <label class="form-label" for="branch_id">
                Cabang <span class="text-danger">*</span>
              </label>
              <select class="form-select @error('branch_id') is-invalid @enderror" 
                      id="branch_id" name="branch_id">
                <option value="">Pilih cabang...</option>
                @if(isset($branches) && $branches->count() > 0)
                  @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $warehouse->branch_id) == $branch->id ? 'selected' : '' }}>
                      {{ $branch->branch_name }} ({{ $branch->branch_code }}) - {{ $branch->city }}
                    </option>
                  @endforeach
                @endif
              </select>
              @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">
                <i class="bx bx-info-circle me-1"></i>
                Pilih cabang untuk branch warehouse
              </div>
            </div>

            <!-- Warehouse Code -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="warehouse_code">
                Kode Warehouse <span class="text-danger">*</span>
              </label>
              <input type="text" 
                     class="form-control @error('warehouse_code') is-invalid @enderror" 
                     id="warehouse_code" 
                     name="warehouse_code" 
                     value="{{ old('warehouse_code', $warehouse->warehouse_code) }}"
                     required>
              @error('warehouse_code')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">
                <i class="bx bx-info-circle me-1"></i>
                Kode warehouse harus unik dalam sistem
              </div>
            </div>

            <!-- Warehouse Name -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="warehouse_name">
                Nama Warehouse <span class="text-danger">*</span>
              </label>
              <input type="text" 
                     class="form-control @error('warehouse_name') is-invalid @enderror" 
                     id="warehouse_name" 
                     name="warehouse_name" 
                     value="{{ old('warehouse_name', $warehouse->warehouse_name) }}"
                     required>
              @error('warehouse_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Address -->
            <div class="col-12 mb-3">
              <label class="form-label" for="address">
                Alamat Lengkap <span class="text-danger">*</span>
              </label>
              <textarea class="form-control @error('address') is-invalid @enderror" 
                        id="address" 
                        name="address" 
                        rows="3" 
                        required>{{ old('address', $warehouse->address) }}</textarea>
              @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Status -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="status">
                Status <span class="text-danger">*</span>
              </label>
              <select class="form-select @error('status') is-invalid @enderror" 
                      id="status" name="status" required>
                <option value="ACTIVE" {{ old('status', $warehouse->status) == 'ACTIVE' ? 'selected' : '' }}>
                  ACTIVE
                </option>
                <option value="INACTIVE" {{ old('status', $warehouse->status) == 'INACTIVE' ? 'selected' : '' }}>
                  INACTIVE
                </option>
                <option value="MAINTENANCE" {{ old('status', $warehouse->status) == 'MAINTENANCE' ? 'selected' : '' }}>
                  MAINTENANCE
                </option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Capacity Information -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bx bx-cube me-2"></i>
            Kapasitas Warehouse
          </h5>
          <small class="text-muted">Opsional</small>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Capacity M2 -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="capacity_m2">
                Kapasitas Area (m²)
              </label>
              <div class="input-group">
                <input type="number" 
                       class="form-control @error('capacity_m2') is-invalid @enderror" 
                       id="capacity_m2" 
                       name="capacity_m2" 
                       value="{{ old('capacity_m2', $warehouse->capacity_m2) }}"
                       step="0.01"
                       min="0">
                <span class="input-group-text">m²</span>
              </div>
              @error('capacity_m2')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Capacity Volume -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="capacity_volume">
                Kapasitas Volume (m³)
              </label>
              <div class="input-group">
                <input type="number" 
                       class="form-control @error('capacity_volume') is-invalid @enderror" 
                       id="capacity_volume" 
                       name="capacity_volume" 
                       value="{{ old('capacity_volume', $warehouse->capacity_volume) }}"
                       step="0.01"
                       min="0">
                <span class="input-group-text">m³</span>
              </div>
              @error('capacity_volume')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Total Capacity -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="capacity">
                Kapasitas Total
              </label>
              <div class="input-group">
                <input type="number" 
                       class="form-control @error('capacity') is-invalid @enderror" 
                       id="capacity" 
                       name="capacity" 
                       value="{{ old('capacity', $warehouse->capacity) }}"
                       step="0.01"
                       min="0">
                <span class="input-group-text">unit</span>
              </div>
              @error('capacity')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Coverage Area -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="coverage_area">
                Area Coverage
              </label>
              <textarea class="form-control @error('coverage_area') is-invalid @enderror" 
                        id="coverage_area" 
                        name="coverage_area" 
                        rows="3">{{ old('coverage_area', $warehouse->coverage_area) }}</textarea>
              @error('coverage_area')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Information -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bx bx-phone me-2"></i>
            Informasi Kontak
          </h5>
          <small class="text-muted">Opsional</small>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Manager Name -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="manager_name">
                Nama Manager
              </label>
              <input type="text" 
                     class="form-control @error('manager_name') is-invalid @enderror" 
                     id="manager_name" 
                     name="manager_name" 
                     value="{{ old('manager_name', $warehouse->manager_name) }}">
              @error('manager_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Phone -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="phone">
                Nomor Telepon
              </label>
              <input type="text" 
                     class="form-control @error('phone') is-invalid @enderror" 
                     id="phone" 
                     name="phone" 
                     value="{{ old('phone', $warehouse->phone) }}">
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Email -->
            <div class="col-md-12 mb-3">
              <label class="form-label" for="email">
                Email
              </label>
              <input type="email" 
                     class="form-control @error('email') is-invalid @enderror" 
                     id="email" 
                     name="email" 
                     value="{{ old('email', $warehouse->email) }}">
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Change Log -->
      <div class="card mb-4">
        <div class="card-header">
          <h6 class="mb-0">
            <i class="bx bx-history me-2"></i>
            Riwayat Perubahan
          </h6>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <small class="text-muted">Dibuat:</small>
              <div class="fw-semibold">{{ $warehouse->created_at->format('d M Y H:i') }}</div>
            </div>
            <div class="col-md-6">
              <small class="text-muted">Terakhir diubah:</small>
              <div class="fw-semibold">{{ $warehouse->updated_at->format('d M Y H:i') }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column - Preview & Actions -->
    <div class="col-xl-4">
      <!-- Current Info Card -->
      <div class="card mb-4">
        <div class="card-header">
          <h6 class="mb-0">
            <i class="bx bx-info-circle me-2"></i>
            Informasi Saat Ini
          </h6>
        </div>
        <div class="card-body">
          <div class="text-center mb-3">
            <div class="avatar avatar-lg mx-auto mb-3">
              <span class="avatar-initial rounded bg-label-{{ $warehouse->warehouse_type === 'central' ? 'primary' : 'info' }}">
                <i class="bx {{ $warehouse->warehouse_type === 'central' ? 'bx-store' : 'bx-home' }}" style="font-size: 24px;"></i>
              </span>
            </div>
            <h6 class="mb-1">{{ $warehouse->warehouse_name }}</h6>
            <span class="badge bg-label-secondary">{{ $warehouse->warehouse_code }}</span>
          </div>

          <ul class="list-unstyled mb-0">
            <li class="mb-2">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">Tipe:</span>
                <span class="badge bg-{{ $warehouse->warehouse_type === 'central' ? 'primary' : 'info' }}">
                  {{ ucfirst($warehouse->warehouse_type) }}
                </span>
              </div>
            </li>
            @if($warehouse->branch)
            <li class="mb-2">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">Cabang:</span>
                <span>{{ $warehouse->branch->branch_name }}</span>
              </div>
            </li>
            @endif
            <li class="mb-2">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">Status:</span>
                <span class="badge bg-{{ $warehouse->status === 'ACTIVE' ? 'success' : ($warehouse->status === 'INACTIVE' ? 'warning' : 'danger') }}">
                  {{ $warehouse->status }}
                </span>
              </div>
            </li>
            <li class="mb-2">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">Manager:</span>
                <span>{{ $warehouse->manager_name ?: '-' }}</span>
              </div>
            </li>
            <li>
              <div class="d-flex justify-content-between">
                <span class="fw-semibold">Kontak:</span>
                <span>{{ $warehouse->phone ?: '-' }}</span>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Preview Changes -->
      <div class="card mb-4" id="changesPreview" style="display: none;">
        <div class="card-header">
          <h6 class="mb-0">
            <i class="bx bx-show me-2"></i>
            Preview Perubahan
          </h6>
        </div>
        <div class="card-body">
          <div id="changesList"></div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="card">
        <div class="card-body">
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary" id="submit-btn">
              <i class="bx bx-check me-1"></i>
              Update Warehouse
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
              <i class="bx bx-reset me-1"></i>
              Reset Perubahan
            </button>
            <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-outline-info">
              <i class="bx bx-show me-1"></i>
              View Details
            </a>
            <a href="{{ route('warehouses.index') }}" class="btn btn-outline-danger">
              <i class="bx bx-x me-1"></i>
              Batal
            </a>
          </div>
          
          <hr class="my-3">
          
          <!-- Danger Zone -->
          <div class="text-center">
            <small class="text-danger">
              <i class="bx bx-error-circle me-1"></i>
              Danger Zone
            </small>
            <div class="mt-2">
              <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bx bx-trash me-1"></i>
                Hapus Warehouse
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger" id="deleteModalLabel">
          <i class="bx bx-error-circle me-2"></i>
          Konfirmasi Hapus Warehouse
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger">
          <h6 class="alert-heading">⚠️ Peringatan!</h6>
          <p class="mb-0">
            Apakah Anda yakin ingin menghapus warehouse <strong>{{ $warehouse->warehouse_name }}</strong>?
          </p>
        </div>
        
        <ul class="list-unstyled mb-3">
          <li class="mb-2">
            <i class="bx bx-check text-success me-2"></i>
            Data warehouse akan di-soft delete
          </li>
          <li class="mb-2">
            <i class="bx bx-check text-success me-2"></i>
            Data dapat dipulihkan jika diperlukan
          </li>
          <li class="mb-2">
            <i class="bx bx-x text-danger me-2"></i>
            Stock aktif harus ditransfer terlebih dahulu
          </li>
        </ul>
        
        <p class="text-muted mb-0">
          <strong>Catatan:</strong> Warehouse dengan stock aktif tidak dapat dihapus.
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>
          Batal
        </button>
        <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">
            <i class="bx bx-trash me-1"></i>
            Ya, Hapus Warehouse
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Store original values for comparison
  const originalValues = {
    warehouse_type: '{{ $warehouse->warehouse_type }}',
    branch_id: '{{ $warehouse->branch_id }}',
    warehouse_code: '{{ $warehouse->warehouse_code }}',
    warehouse_name: '{{ $warehouse->warehouse_name }}',
    address: `{{ str_replace(["\r", "\n", "\r\n"], ' ', $warehouse->address) }}`,
    status: '{{ $warehouse->status }}',
    capacity_m2: '{{ $warehouse->capacity_m2 }}',
    capacity_volume: '{{ $warehouse->capacity_volume }}',
    capacity: '{{ $warehouse->capacity }}',
    coverage_area: `{{ str_replace(["\r", "\n", "\r\n"], ' ', $warehouse->coverage_area) }}`,
    manager_name: '{{ $warehouse->manager_name }}',
    phone: '{{ $warehouse->phone }}',
    email: '{{ $warehouse->email }}'
  };

  // Form elements
  const warehouseType = document.getElementById('warehouse_type');
  const branchContainer = document.getElementById('branch_container');
  const branchSelect = document.getElementById('branch_id');
  const submitBtn = document.getElementById('submit-btn');
  const changesPreview = document.getElementById('changesPreview');
  const changesList = document.getElementById('changesList');

  // Show/hide branch field based on warehouse type
  warehouseType.addEventListener('change', function() {
    if (this.value === 'branch') {
      branchContainer.style.display = 'block';
      branchSelect.required = true;
    } else {
      branchContainer.style.display = 'none';
      branchSelect.required = false;
      branchSelect.value = '';
    }
    checkForChanges();
  });

  // Monitor all form changes
  const formElements = document.querySelectorAll('#editWarehouseForm input, #editWarehouseForm select, #editWarehouseForm textarea');
  formElements.forEach(element => {
    element.addEventListener('input', checkForChanges);
    element.addEventListener('change', checkForChanges);
  });

  function checkForChanges() {
    const changes = [];
    
    // Check each field for changes
    Object.keys(originalValues).forEach(key => {
      const element = document.getElementById(key);
      if (element) {
        const currentValue = element.value.trim();
        const originalValue = originalValues[key].trim();
        
        if (currentValue !== originalValue) {
          changes.push({
            field: key,
            original: originalValue || '-',
            current: currentValue || '-',
            label: getFieldLabel(key)
          });
        }
      }
    });

    // Update UI based on changes
    if (changes.length > 0) {
      changesPreview.style.display = 'block';
      updateChangesPreview(changes);
      submitBtn.innerHTML = '<i class="bx bx-check me-1"></i> Update Warehouse (' + changes.length + ' perubahan)';
      submitBtn.classList.remove('btn-primary');
      submitBtn.classList.add('btn-warning');
    } else {
      changesPreview.style.display = 'none';
      submitBtn.innerHTML = '<i class="bx bx-check me-1"></i> Update Warehouse';
      submitBtn.classList.remove('btn-warning');
      submitBtn.classList.add('btn-primary');
    }
  }

  function updateChangesPreview(changes) {
    let html = '<small class="text-muted mb-2 d-block">Field yang akan diubah:</small>';
    
    changes.forEach(change => {
      html += `
        <div class="mb-3 p-2 border rounded">
          <div class="fw-semibold text-primary mb-1">${change.label}</div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-label-secondary">${change.original}</span>
            <i class="bx bx-right-arrow-alt text-muted"></i>
            <span class="badge bg-label-warning">${change.current}</span>
          </div>
        </div>
      `;
    });
    
    changesList.innerHTML = html;
  }

  function getFieldLabel(key) {
    const labels = {
      warehouse_type: 'Tipe Warehouse',
      branch_id: 'Cabang',
      warehouse_code: 'Kode Warehouse',
      warehouse_name: 'Nama Warehouse',
      address: 'Alamat',
      status: 'Status',
      capacity_m2: 'Kapasitas M²',
      capacity_volume: 'Kapasitas Volume',
      capacity: 'Kapasitas Total',
      coverage_area: 'Area Coverage',
      manager_name: 'Nama Manager',
      phone: 'Nomor Telepon',
      email: 'Email'
    };
    return labels[key] || key;
  }

  // Initial check
  checkForChanges();
});

function resetForm() {
  if (confirm('Apakah Anda yakin ingin mereset semua perubahan? Semua perubahan yang belum disimpan akan hilang.')) {
    // Reset form to original values
    location.reload();
  }
}

// Form submission with loading state
document.getElementById('editWarehouseForm').addEventListener('submit', function() {
  const submitBtn = document.getElementById('submit-btn');
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan perubahan...';
});
</script>
@endpush

@push('styles')
<style>
.avatar {
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-lg {
  width: 60px;
  height: 60px;
}

.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  color: #fff;
  border-radius: 0.375rem;
}

.card:hover {
  transform: translateY(-2px);
  transition: transform 0.2s ease-in-out;
  box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.1);
}

.form-control:focus, .form-select:focus {
  border-color: #696cff;
  box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

/* Changes preview styling */
#changesPreview {
  border-left: 4px solid #ffab00;
}

#changesPreview .card-header {
  background-color: rgba(255, 171, 0, 0.1);
}

/* Danger zone styling */
.text-danger {
  color: #ff3e1d !important;
}

/* Badge enhancements */
.badge {
  font-size: 0.7em;
}

/* Form validation states */
.is-valid {
  border-color: #71dd37;
}

.is-invalid {
  border-color: #ff3e1d;
}

/* Loading states */
.spinner-border-sm {
  width: 1rem;
  height: 1rem;
  border-width: 0.1em;
}

/* Help text styling */
.form-text {
  font-size: 0.775em;
  color: #a7acb1;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
  .col-xl-4 .card {
    position: relative;
    top: auto;
  }
}
</style>
@endpush
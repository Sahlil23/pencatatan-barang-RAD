@extends('layouts.admin')

@section('title', 'Tambah Warehouse - Chicking BJM')

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
        <li class="breadcrumb-item active" aria-current="page">Tambah Warehouse</li>
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
              <i class="bx bx-plus me-2"></i>
              Tambah Warehouse Baru
            </h4>
            <p class="card-text text-muted mb-0">
              Isi form di bawah untuk menambahkan warehouse baru ke sistem
            </p>
          </div>
          <div class="d-flex gap-2">
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

<!-- Create Form -->
<form action="{{ route('warehouses.store') }}" method="POST" id="createWarehouseForm">
  @csrf
  
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
                  <option value="central" {{ old('warehouse_type') == 'central' ? 'selected' : '' }}>
                    Central Warehouse
                  </option>
                  <option value="branch" {{ old('warehouse_type') == 'branch' ? 'selected' : '' }}>
                    Branch Warehouse
                  </option>
                  <option value="outlet" {{ old('warehouse_type') == 'outlet' ? 'selected' : '' }}>
                    Outlet Warehouse
                  </option>
                </select>
                @error('warehouse_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                  <i class="bx bx-info-circle me-1"></i>
                  <strong>Central:</strong> Distribusi utama | 
                  <strong>Branch:</strong> Per cabang | 
                  <strong>Outlet:</strong> Per outlet/toko
                </div>
              </div>

            <!-- Branch (Conditional) -->
            <div class="col-md-6 mb-3" id="branch_container" style="display: none;">
              <label class="form-label" for="branch_id">
                Cabang <span class="text-danger" id="branch_required">*</span>
              </label>
              <select class="form-select @error('branch_id') is-invalid @enderror" 
                      id="branch_id" name="branch_id">
                <option value="">Pilih cabang...</option>
                @if(isset($branches))
                  @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                      {{ $branch->branch_name }} ({{ $branch->branch_code }})
                    </option>
                  @endforeach
                @endif
              </select>
              @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">
                <i class="bx bx-info-circle me-1"></i>
                <span id="branch_help_text">Pilih cabang untuk warehouse ini</span>
              </div>
            </div>

            <!-- Warehouse Code -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="warehouse_code">
                Kode Warehouse
              </label>
              <input type="text" 
                     class="form-control @error('warehouse_code') is-invalid @enderror" 
                     id="warehouse_code" 
                     name="warehouse_code" 
                     value="{{ old('warehouse_code') }}"
                     placeholder="WH-CTR-001 / WH-BR-001g">
              @error('warehouse_code')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <!-- <div class="form-text">
                <i class="bx bx-info-circle me-1"></i>
                Kosongkan untuk generate otomatis (WH-CTR-001 / WH-BR-001)
              </div> -->
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
                     value="{{ old('warehouse_name') }}"
                     placeholder="Contoh: Gudang Central Jakarta"
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
                        placeholder="Masukkan alamat lengkap warehouse..."
                        required>{{ old('address') }}</textarea>
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
                <option value="ACTIVE" {{ old('status', 'ACTIVE') == 'ACTIVE' ? 'selected' : '' }}>
                ACTIVE
                </option>
                <option value="INACTIVE" {{ old('status') == 'INACTIVE' ? 'selected' : '' }}>
                INACTIVE
                </option>
                <option value="MAINTENANCE" {{ old('status') == 'MAINTENANCE' ? 'selected' : '' }}>
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
                       value="{{ old('capacity_m2') }}"
                       placeholder="0"
                       step="0.01"
                       min="0">
                <span class="input-group-text">m²</span>
              </div>
              @error('capacity_m2')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">
                <i class="bx bx-info-circle me-1"></i>
                Luas lantai warehouse dalam meter persegi
              </div>
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
                       value="{{ old('capacity_volume') }}"
                       placeholder="0"
                       step="0.01"
                       min="0">
                <span class="input-group-text">m³</span>
              </div>
              @error('capacity_volume')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">
                <i class="bx bx-info-circle me-1"></i>
                Volume ruang penyimpanan dalam meter kubik
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Manager Information -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bx bx-user me-2"></i>
            Informasi Manager
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
                     value="{{ old('manager_name') }}"
                     placeholder="Nama manager gudang">
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
                     value="{{ old('phone') }}"
                     placeholder="Nomor telepon gudang">
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Email -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="email">
                Email
              </label>
              <input type="email" 
                     class="form-control @error('email') is-invalid @enderror" 
                     id="email" 
                     name="email" 
                     value="{{ old('email') }}"
                     placeholder="email@warehouse.com">
              @error('email')
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
                        rows="2" 
                        placeholder="Area yang dicakup gudang ini...">{{ old('coverage_area') }}</textarea>
              @error('coverage_area')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            <!-- </div> -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column - Summary & Actions -->
    <div class="col-xl-4">
      <!-- Preview Card -->
      <div class="card mb-4" id="warehousePreview">
        <div class="card-header">
          <h6 class="mb-0">
            <i class="bx bx-show me-2"></i>
            Preview Warehouse
          </h6>
        </div>
        <div class="card-body">
          <div class="text-center mb-3">
            <div class="avatar avatar-lg mx-auto mb-3">
              <span class="avatar-initial rounded bg-label-primary" id="preview-icon">
                <i class="bx bx-buildings" style="font-size: 24px;"></i>
              </span>
            </div>
            <h6 class="mb-1" id="preview-name">Nama Warehouse</h6>
            <span class="badge bg-label-secondary" id="preview-code">Kode akan di-generate</span>
          </div>

          <div class="info-container">
            <ul class="list-unstyled mb-0">
              <li class="mb-2">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Tipe:</span>
                  <span class="badge bg-label-info" id="preview-type">-</span>
                </div>
              </li>
              <li class="mb-2">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Status:</span>
                  <span class="badge bg-label-success" id="preview-status">Active</span>
                </div>
              </li>
              <li class="mb-2" id="preview-branch-container" style="display: none;">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Cabang:</span>
                  <span id="preview-branch">-</span>
                </div>
              </li>
              <li class="mb-2">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Area:</span>
                  <span id="preview-area">-</span>
                </div>
              </li>
              <li class="mb-2">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Volume:</span>
                  <span id="preview-volume">-</span>
                </div>
              </li>
              <li>
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">PIC:</span>
                  <span id="preview-pic">-</span>
                </div>
              </li>
              <li>
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Email:</span>
                  <span id="preview-email">-</span>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Validation Info -->
      <div class="card mb-4">
        <div class="card-header">
          <h6 class="mb-0">
            <i class="bx bx-check-shield me-2"></i>
            Validasi Form
          </h6>
        </div>
        <div class="card-body">
          <div class="validation-item mb-2">
            <i class="bx bx-x text-danger me-2" id="val-type"></i>
            <span class="text-muted">Tipe warehouse dipilih</span>
          </div>
          <div class="validation-item mb-2">
            <i class="bx bx-x text-danger me-2" id="val-name"></i>
            <span class="text-muted">Nama warehouse diisi</span>
          </div>
          <div class="validation-item mb-2">
            <i class="bx bx-x text-danger me-2" id="val-address"></i>
            <span class="text-muted">Alamat diisi</span>
          </div>
          <div class="validation-item mb-2" id="val-branch-item" style="display: none;">
            <i class="bx bx-x text-danger me-2" id="val-branch"></i>
            <span class="text-muted">Cabang dipilih (untuk branch)</span>
          </div>
          
          <hr class="my-3">
          
          <div class="text-center">
            <div class="progress mb-2" style="height: 8px;">
              <div class="progress-bar bg-primary" id="validation-progress" style="width: 0%"></div>
            </div>
            <small class="text-muted">
              <span id="validation-text">0% Complete</span>
            </small>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="card">
        <div class="card-body">
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
              <i class="bx bx-check me-1"></i>
              Simpan Warehouse
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
              <i class="bx bx-reset me-1"></i>
              Reset Form
            </button>
            <a href="{{ route('warehouses.index') }}" class="btn btn-outline-danger">
              <i class="bx bx-x me-1"></i>
              Batal
            </a>
          </div>
          
          <hr class="my-3">
          
          <div class="text-center">
            <small class="text-muted">
              <i class="bx bx-info-circle me-1"></i>
              Form akan divalidasi secara real-time
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="helpModalLabel">
          <i class="bx bx-help-circle me-2"></i>
          Bantuan - Tambah Warehouse
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6>Tipe Warehouse:</h6>
            <ul class="list-unstyled">
              <li class="mb-2">
                <strong>Central:</strong> Gudang pusat untuk distribusi ke semua cabang
              </li>
              <li class="mb-2">
                <strong>Branch:</strong> Gudang cabang untuk operasional harian
              </li>
            </ul>
            
            <h6 class="mt-3">Kode Warehouse:</h6>
            <ul class="list-unstyled">
              <li class="mb-2">
                <strong>Format Central:</strong> WH-CTR-001, WH-CTR-002, dst.
              </li>
              <li class="mb-2">
                <strong>Format Branch:</strong> WH-BR-001, WH-JKT-001, dst.
              </li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6>Status Warehouse:</h6>
            <ul class="list-unstyled">
              <li class="mb-2">
                <strong>Active:</strong> Warehouse beroperasi normal
              </li>
              <li class="mb-2">
                <strong>Inactive:</strong> Warehouse tidak beroperasi
              </li>
              <li class="mb-2">
                <strong>Maintenance:</strong> Warehouse dalam perbaikan
              </li>
            </ul>
            
            <h6 class="mt-3">Tips:</h6>
            <ul class="list-unstyled">
              <li class="mb-2">• Gunakan nama yang jelas dan mudah diidentifikasi</li>
              <li class="mb-2">• Isi kapasitas untuk tracking utilisasi</li>
              <li class="mb-2">• PIC membantu komunikasi operasional</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Form elements
  const warehouseType = document.getElementById('warehouse_type');
  const branchContainer = document.getElementById('branch_container');
  const branchSelect = document.getElementById('branch_id');
  const branchRequired = document.getElementById('branch_required');
  const branchHelpText = document.getElementById('branch_help_text');
  const warehouseName = document.getElementById('warehouse_name');
  const warehouseCode = document.getElementById('warehouse_code');
  const address = document.getElementById('address');
  const capacityM2 = document.getElementById('capacity_m2');
  const capacityVolume = document.getElementById('capacity_volume');
  const managerName = document.getElementById('manager_name');
  const phone = document.getElementById('phone');
  const email = document.getElementById('email');
  const status = document.getElementById('status');
  
  // Preview elements
  const previewIcon = document.getElementById('preview-icon');
  const previewName = document.getElementById('preview-name');
  const previewCode = document.getElementById('preview-code');
  const previewType = document.getElementById('preview-type');
  const previewStatus = document.getElementById('preview-status');
  const previewBranchContainer = document.getElementById('preview-branch-container');
  const previewBranch = document.getElementById('preview-branch');
  const previewArea = document.getElementById('preview-area');
  const previewVolume = document.getElementById('preview-volume');
  
  // Validation elements
  const submitBtn = document.getElementById('submit-btn');
  const validationProgress = document.getElementById('validation-progress');
  const validationText = document.getElementById('validation-text');
  
  // ✅ Show/hide branch field based on warehouse type
  warehouseType.addEventListener('change', function() {
    if (this.value === 'branch' || this.value === 'outlet') {
      branchContainer.style.display = 'block';
      branchSelect.required = true;
      branchRequired.style.display = 'inline';
      document.getElementById('val-branch-item').style.display = 'block';
      
      // ✅ Update help text based on type
      if (this.value === 'outlet') {
        branchHelpText.textContent = 'Pilih cabang induk untuk outlet ini';
      } else {
        branchHelpText.textContent = 'Pilih cabang untuk warehouse ini';
      }
    } else {
      branchContainer.style.display = 'none';
      branchSelect.required = false;
      branchSelect.value = '';
      branchRequired.style.display = 'none';
      document.getElementById('val-branch-item').style.display = 'none';
    }
    updatePreview();
    validateForm();
  });
  
  // Real-time preview updates
  [warehouseType, branchSelect, warehouseName, warehouseCode, address, capacityM2, capacityVolume, managerName, phone, email, status].forEach(element => {
    element.addEventListener('input', updatePreview);
    element.addEventListener('change', updatePreview);
  });
  
  // Real-time validation
  [warehouseType, warehouseName, address, branchSelect].forEach(element => {
    element.addEventListener('input', validateForm);
    element.addEventListener('change', validateForm);
  });
  
  // ✅ Update preview function with outlet support
  function updatePreview() {
    // Update icon and type based on warehouse type
    if (warehouseType.value === 'central') {
      previewIcon.innerHTML = '<i class="bx bx-buildings" style="font-size: 24px;"></i>';
      previewIcon.className = 'avatar-initial rounded bg-label-primary';
      previewType.textContent = 'Central';
      previewType.className = 'badge bg-primary';
    } else if (warehouseType.value === 'branch') {
      previewIcon.innerHTML = '<i class="bx bx-building" style="font-size: 24px;"></i>';
      previewIcon.className = 'avatar-initial rounded bg-label-info';
      previewType.textContent = 'Branch';
      previewType.className = 'badge bg-info';
    } else if (warehouseType.value === 'outlet') {  // ✅ ADD outlet
      previewIcon.innerHTML = '<i class="bx bx-store" style="font-size: 24px;"></i>';
      previewIcon.className = 'avatar-initial rounded bg-label-success';
      previewType.textContent = 'Outlet';
      previewType.className = 'badge bg-success';
    } else {
      previewIcon.innerHTML = '<i class="bx bx-package" style="font-size: 24px;"></i>';
      previewIcon.className = 'avatar-initial rounded bg-label-secondary';
      previewType.textContent = '-';
      previewType.className = 'badge bg-label-secondary';
    }
    
    // Update name
    previewName.textContent = warehouseName.value || 'Nama Warehouse';
    
    // ✅ Update code preview with outlet support
    if (warehouseCode.value) {
      previewCode.textContent = warehouseCode.value;
    } else if (warehouseType.value) {
      if (warehouseType.value === 'central') {
        previewCode.textContent = 'WH-CENTRAL-XXX (auto)';
      } else if (warehouseType.value === 'outlet') {  // ✅ ADD
        previewCode.textContent = 'WH-OUT-XXX (auto)';
      } else {
        previewCode.textContent = 'WH-BR-XXX (auto)';
      }
    } else {
      previewCode.textContent = 'Kode akan di-generate';
    }
    
    // Update status
    const statusValue = status.value || 'ACTIVE';
    const statusColors = {
      'ACTIVE': 'success',
      'INACTIVE': 'warning',
      'MAINTENANCE': 'danger'
    };
    previewStatus.textContent = statusValue;
    previewStatus.className = `badge bg-${statusColors[statusValue] || 'secondary'}`;
    
    // ✅ Update branch (for both branch and outlet)
    if ((warehouseType.value === 'branch' || warehouseType.value === 'outlet') && branchSelect.value) {
      previewBranchContainer.style.display = 'block';
      const branchText = branchSelect.options[branchSelect.selectedIndex].text;
      previewBranch.innerHTML = `<span class="badge bg-label-primary"><i class="bx bx-building me-1"></i>${branchText}</span>`;
    } else {
      previewBranchContainer.style.display = 'none';
    }
    
    // Update capacity
    previewArea.textContent = capacityM2.value ? `${capacityM2.value} m²` : '-';
    previewVolume.textContent = capacityVolume.value ? `${capacityVolume.value} m³` : '-';
    
    // Update manager info
    let managerInfo = '-';
    if (managerName.value) {
      managerInfo = managerName.value;
      if (phone.value) {
        managerInfo += ' (' + phone.value + ')';
      }
    }
    document.getElementById('preview-pic').textContent = managerInfo;
    
    // Update email
    document.getElementById('preview-email').textContent = email.value || '-';
  }
  
  function validateForm() {
    let valid = 0;
    let total = 3; // Base required fields
    
    // Validate warehouse type
    if (warehouseType.value) {
      document.getElementById('val-type').className = 'bx bx-check text-success me-2';
      valid++;
    } else {
      document.getElementById('val-type').className = 'bx bx-x text-danger me-2';
    }
    
    // Validate warehouse name
    if (warehouseName.value.trim()) {
      document.getElementById('val-name').className = 'bx bx-check text-success me-2';
      valid++;
    } else {
      document.getElementById('val-name').className = 'bx bx-x text-danger me-2';
    }
    
    // Validate address
    if (address.value.trim()) {
      document.getElementById('val-address').className = 'bx bx-check text-success me-2';
      valid++;
    } else {
      document.getElementById('val-address').className = 'bx bx-x text-danger me-2';
    }
    
    // ✅ Validate branch (if branch OR outlet type)
    if (warehouseType.value === 'branch' || warehouseType.value === 'outlet') {
      total = 4;
      if (branchSelect.value) {
        document.getElementById('val-branch').className = 'bx bx-check text-success me-2';
        valid++;
      } else {
        document.getElementById('val-branch').className = 'bx bx-x text-danger me-2';
      }
    }
    
    // Update progress
    const percentage = Math.round((valid / total) * 100);
    validationProgress.style.width = percentage + '%';
    validationText.textContent = percentage + '% Complete';
    
    // Enable/disable submit button
    if (valid === total) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bx bx-check me-1"></i> Simpan Warehouse';
    } else {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bx bx-x me-1"></i> Lengkapi Form';
    }
  }
  
  // Initial validation
  validateForm();
});

function resetForm() {
  if (confirm('Apakah Anda yakin ingin mereset form? Semua data yang diisi akan hilang.')) {
    document.getElementById('createWarehouseForm').reset();
    document.getElementById('branch_container').style.display = 'none';
    document.getElementById('val-branch-item').style.display = 'none';
    
    // Reset preview
    document.getElementById('preview-name').textContent = 'Nama Warehouse';
    document.getElementById('preview-code').textContent = 'Kode akan di-generate';
    document.getElementById('preview-type').textContent = '-';
    document.getElementById('preview-type').className = 'badge bg-label-secondary';
    document.getElementById('preview-status').textContent = 'ACTIVE';
    document.getElementById('preview-status').className = 'badge bg-success';
    document.getElementById('preview-area').textContent = '-';
    document.getElementById('preview-volume').textContent = '-';
    document.getElementById('preview-pic').textContent = '-';
    document.getElementById('preview-email').textContent = '-';
    document.getElementById('preview-branch-container').style.display = 'none';
    
    // Reset icon
    const previewIcon = document.getElementById('preview-icon');
    previewIcon.innerHTML = '<i class="bx bx-package" style="font-size: 24px;"></i>';
    previewIcon.className = 'avatar-initial rounded bg-label-secondary';
    
    // Reset validation
    validateForm();
  }
}

// Form submission with loading state
document.getElementById('createWarehouseForm').addEventListener('submit', function() {
  const submitBtn = document.getElementById('submit-btn');
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
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

.info-container ul li {
  padding: 0.25rem 0;
}

.validation-item {
  display: flex;
  align-items: center;
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

.progress {
  border-radius: 0.5rem;
}

.progress-bar {
  transition: width 0.3s ease;
}

#warehousePreview {
  position: sticky;
  top: 20px;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
  #warehousePreview {
    position: relative;
    top: auto;
  }
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

/* Badge enhancements */
.badge {
  font-size: 0.7em;
}

/* Help text styling */
.form-text {
  font-size: 0.775em;
  color: #a7acb1;
}
</style>
@endpush
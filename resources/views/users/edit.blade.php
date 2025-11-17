@extends('layouts.admin')

@section('title', 'Edit User - ' . $user->full_name . ' - Chicking BJM')

@push('styles')
<style>
.role-card {
  border: 2px solid #e7e7ff;
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1rem;
  transition: all 0.3s;
  cursor: pointer;
}
.role-card:hover {
  border-color: #696cff;
  background: #f8f9ff;
}
.role-card.selected {
  border-color: #696cff;
  background: #f0f2ff;
}
.role-card input[type="radio"] {
  transform: scale(1.2);
}

/* Branch auto-select visual feedback */
.border-success {
  border-color: #28a745 !important;
  transition: border-color 0.3s ease;
}

#branch_id {
  cursor: default;
}

#warehouse_id:focus {
  border-color: #696cff;
  box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

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

@media (max-width: 768px) {
  .d-flex.gap-2 {
    flex-direction: column;
    gap: 0.5rem !important;
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
        <li class="breadcrumb-item">
          <a href="{{ route('users.show', $user) }}">{{ $user->full_name }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <!-- Form -->
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-edit me-2"></i>
          Edit User: {{ $user->full_name }}
        </h5>
        <small class="text-muted">Form edit informasi user</small>
      </div>
      <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST" id="editUserForm">
          @csrf
          @method('PUT')
          
          <!-- Role Selection -->
          <div class="mb-4">
            <label class="form-label">
              Role <span class="text-danger">*</span>
            </label>
            <div class="row">
              @foreach($availableRoles as $roleKey => $roleName)
                <div class="col-md-6 mb-3">
                  <label class="role-card {{ old('role', $user->role) == $roleKey ? 'selected' : '' }}">
                    <div class="d-flex align-items-center">
                      <input type="radio" name="role" value="{{ $roleKey }}" 
                             class="form-check-input me-3" 
                             {{ old('role', $user->role) == $roleKey ? 'checked' : '' }}
                             required>
                      <div>
                        <div class="fw-semibold">{{ $roleName }}</div>
                        <small class="text-muted">
                          @if(Str::contains($roleKey, 'staff'))
                            Staff level - Tidak bisa membuat user lain
                          @elseif(Str::contains($roleKey, 'manager'))
                            Manager level - Bisa membuat staff
                          @else
                            Administrator - Full access
                          @endif
                        </small>
                      </div>
                    </div>
                  </label>
                </div>
              @endforeach
            </div>
            @error('role')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <hr class="my-4">

          <!-- Basic Information -->
          <h6 class="mb-3">Informasi Dasar</h6>
          
          <div class="row">
            <!-- Full Name -->
            <div class="col-md-6 mb-3">
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
            <div class="col-md-6 mb-3">
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
            <div class="col-md-6 mb-3">
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
            <div class="col-md-6 mb-3">
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
          </div>

          <hr class="my-4">

          <!-- Password Section -->
          <h6 class="mb-3">
            <i class="bx bx-lock me-2"></i>
            Ubah Password
          </h6>
          <div class="alert alert-info mb-3">
            <i class="bx bx-info-circle me-2"></i>
            Kosongkan jika tidak ingin mengubah password
          </div>
          
          <div class="row">
            <!-- New Password -->
            <div class="col-md-6 mb-3">
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
            <div class="col-md-6 mb-3">
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
          </div>

          <!-- Password Generator -->
          <div class="d-flex gap-2 mb-4">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="generatePassword()">
              <i class="bx bx-refresh me-1"></i>
              Generate Password
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm" onclick="useDefaultPassword()">
              <i class="bx bx-key me-1"></i>
              Use Default
            </button>
          </div>

          <hr class="my-4">

          <!-- Assignment -->
          <h6 class="mb-3">Assignment</h6>

          <div class="row" id="manualAssignmentContainer">
            <!-- Warehouse - SELECT FIRST -->
            <div class="col-md-6 mb-3">
              <label for="warehouse_id" class="form-label">
                Warehouse
                <span class="text-danger" id="warehouse_required_indicator" style="display: none;">*</span>
              </label>
              <select name="warehouse_id" id="warehouse_id" 
                      class="form-select @error('warehouse_id') is-invalid @enderror">
                <option value="">Pilih Warehouse</option>
                @foreach($warehouses as $warehouse)
                  <option value="{{ $warehouse->id }}" 
                          data-type="{{ $warehouse->warehouse_type }}"
                          data-branch="{{ $warehouse->branch_id }}"
                          {{ old('warehouse_id', $user->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                    {{ $warehouse->warehouse_name }} 
                    ({{ ucfirst($warehouse->warehouse_type) }})
                    @if($warehouse->branch)
                      - {{ $warehouse->branch->branch_name }}
                    @endif
                  </option>
                @endforeach
              </select>
              <small class="text-muted" id="warehouse_help_text">
                Pilih warehouse terlebih dahulu
              </small>
              @error('warehouse_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Branch - AUTO-SELECTED -->
            <div class="col-md-6 mb-3" style="display: none;">
              <label for="branch_id" class="form-label">
                Branch
                <span class="badge bg-info ms-2">Auto</span>
              </label>
              <select name="branch_id" id="branch_id" 
                      class="form-select @error('branch_id') is-invalid @enderror"
                      style="background-color: #f8f9fa;">
                <option value="">Branch otomatis terisi dari warehouse</option>
                @foreach($branches as $branch)
                  <option value="{{ $branch->id }}" 
                          {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                    {{ $branch->branch_name }}
                  </option>
                @endforeach
              </select>
              <small class="text-muted" id="branch_help_text">
                <i class="bx bx-info-circle"></i>
                Otomatis dipilih berdasarkan warehouse
              </small>
              @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <hr class="my-4">

          <!-- Status -->
          <h6 class="mb-3">Status Akun</h6>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-check-circle"></i></span>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                  @foreach($statuses as $key => $value)
                    <option value="{{ $key }}" {{ old('status', $user->status) == $key ? 'selected' : '' }}>
                      {{ $value }}
                    </option>
                  @endforeach
                </select>
              </div>
              @error('status')
                <div class="form-text text-danger">{{ $message }}</div>
              @else
                <div class="form-text">Status akun pengguna</div>
              @enderror
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="d-flex gap-2">
              <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>
                Kembali
              </a>
              <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
            </div>
            <button type="submit" class="btn btn-primary" id="submitBtn">
              <i class="bx bx-save me-1"></i>
              Update User
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- User Info Card -->
  <div class="col-lg-4">
    <div class="card sticky-top" style="top: 100px;">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Preview User
        </h6>
      </div>
      <div class="card-body">
        <div class="text-center mb-3">
          <div class="avatar avatar-xl mx-auto" id="previewAvatar">
            <span class="avatar-initial rounded-circle bg-label-primary" id="previewInitial">
              {{ strtoupper(substr($user->full_name, 0, 2)) }}
            </span>
          </div>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Nama Lengkap</small>
          <div class="fw-semibold" id="previewName">{{ $user->full_name }}</div>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Username</small>
          <code id="previewUsername">{{ $user->username }}</code>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Role</small>
          <span class="badge bg-primary" id="previewRole">{{ $user->role }}</span>
        </div>

        <div class="mb-3" id="previewWarehouseContainer">
          <small class="text-muted d-block">Warehouse</small>
          <div id="previewWarehouse">
            {{ $user->warehouse ? $user->warehouse->warehouse_name : '-' }}
          </div>
        </div>

        <div class="mb-3" id="previewBranchContainer">
          <small class="text-muted d-block">Branch</small>
          <div id="previewBranch">
            {{ $user->branch ? $user->branch->branch_name : '-' }}
          </div>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Status</small>
          <span class="badge bg-{{ $user->status === 'ACTIVE' ? 'success' : 'secondary' }}" id="previewStatus">
            {{ $user->status }}
          </span>
        </div>

        <hr>

        <div class="info-container">
          <ul class="list-unstyled mb-0">
            <li class="mb-2">
              <small class="text-muted">ID User:</small>
              <span class="float-end">#{{ $user->id }}</span>
            </li>
            <li class="mb-2">
              <small class="text-muted">Dibuat:</small>
              <span class="float-end">{{ $user->created_at->format('d M Y') }}</span>
            </li>
            <li class="mb-2">
              <small class="text-muted">Login Terakhir:</small>
              <span class="float-end">
                {{ $user->last_login_at ? $user->last_login_at->format('d M Y') : 'Belum pernah' }}
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
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
    status: '{{ $user->status }}',
    warehouse_id: '{{ $user->warehouse_id }}',
    branch_id: '{{ $user->branch_id }}'
  };

  // Form elements
  const form = document.getElementById('editUserForm');
  const fullNameInput = document.getElementById('full_name');
  const usernameInput = document.getElementById('username');
  const roleInputs = document.querySelectorAll('input[name="role"]');
  const branchSelect = document.getElementById('branch_id');
  const warehouseSelect = document.getElementById('warehouse_id');
  const statusSelect = document.getElementById('status');
  const passwordInput = document.getElementById('password');
  const passwordConfirmInput = document.getElementById('password_confirmation');

  // Preview elements
  const previewInitial = document.getElementById('previewInitial');
  const previewName = document.getElementById('previewName');
  const previewUsername = document.getElementById('previewUsername');
  const previewRole = document.getElementById('previewRole');
  const previewBranch = document.getElementById('previewBranch');
  const previewWarehouse = document.getElementById('previewWarehouse');
  const previewStatus = document.getElementById('previewStatus');
  const previewBranchContainer = document.getElementById('previewBranchContainer');
  const previewWarehouseContainer = document.getElementById('previewWarehouseContainer');

  // Password toggle functionality
  const togglePassword = document.getElementById('togglePassword');
  const togglePasswordIcon = document.getElementById('togglePasswordIcon');

  if (togglePassword) {
    togglePassword.addEventListener('click', function() {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      togglePasswordIcon.classList.toggle('bx-hide');
      togglePasswordIcon.classList.toggle('bx-show');
    });
  }

  // Confirm password toggle
  const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
  const togglePasswordConfirmIcon = document.getElementById('togglePasswordConfirmIcon');

  if (togglePasswordConfirm) {
    togglePasswordConfirm.addEventListener('click', function() {
      const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordConfirmInput.setAttribute('type', type);
      togglePasswordConfirmIcon.classList.toggle('bx-hide');
      togglePasswordConfirmIcon.classList.toggle('bx-show');
    });
  }

  // ✅ AUTO-SELECT BRANCH WHEN WAREHOUSE CHANGES
  if (warehouseSelect && branchSelect) {
    warehouseSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const warehouseBranchId = selectedOption.getAttribute('data-branch');
      
      if (warehouseBranchId) {
        branchSelect.value = warehouseBranchId;
        branchSelect.classList.add('border-success');
        setTimeout(() => {
          branchSelect.classList.remove('border-success');
        }, 1000);
      } else {
        branchSelect.value = '';
      }
      
      updatePreview();
    });
  }

  // Role card selection
  document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', function() {
      document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
      this.classList.add('selected');
      const roleRadio = this.querySelector('input[type="radio"]');
      roleRadio.checked = true;
      updateRequiredFields(roleRadio.value);
      updatePreview();
      filterWarehouseOptions(roleRadio.value);
    });
  });

  // Real-time preview
  if (fullNameInput) fullNameInput.addEventListener('input', updatePreview);
  if (usernameInput) usernameInput.addEventListener('input', updatePreview);
  
  roleInputs.forEach(input => {
    input.addEventListener('change', function() {
      updateRequiredFields(this.value);
      updatePreview();
      filterWarehouseOptions(this.value);
    });
  });

  if (branchSelect) branchSelect.addEventListener('change', updatePreview);
  if (statusSelect) statusSelect.addEventListener('change', updatePreview);

  // Generate random password
  window.generatePassword = function() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let password = '';
    for (let i = 0; i < 8; i++) {
      password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    passwordInput.value = password;
    passwordConfirmInput.value = password;
    alert('Password yang dihasilkan: ' + password + '\nSilakan catat password ini!');
  };

  // Use default password
  window.useDefaultPassword = function() {
    const defaultPassword = 'password123';
    passwordInput.value = defaultPassword;
    passwordConfirmInput.value = defaultPassword;
  };

  // Reset form to original values
  window.resetForm = function() {
    if (confirm('Apakah Anda yakin ingin mengembalikan form ke data asli?')) {
      fullNameInput.value = originalData.full_name;
      usernameInput.value = originalData.username;
      document.getElementById('email').value = originalData.email;
      document.getElementById('phone').value = originalData.phone;
      
      // Reset role
      document.querySelectorAll('input[name="role"]').forEach(radio => {
        if (radio.value === originalData.role) {
          radio.checked = true;
          radio.closest('.role-card').classList.add('selected');
        } else {
          radio.closest('.role-card').classList.remove('selected');
        }
      });
      
      statusSelect.value = originalData.status;
      
      if (warehouseSelect) warehouseSelect.value = originalData.warehouse_id;
      if (branchSelect) branchSelect.value = originalData.branch_id;
      
      passwordInput.value = '';
      passwordConfirmInput.value = '';
      
      document.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
        el.classList.remove('is-invalid', 'is-valid');
      });
      
      updatePreview();
    }
  };

  // Dynamic required fields based on role
  function updateRequiredFields(role) {
    const branchRequired = document.getElementById('branch_required_indicator');
    const warehouseRequired = document.getElementById('warehouse_required_indicator');
    const branchHelpText = document.getElementById('branch_help_text');
    const warehouseHelpText = document.getElementById('warehouse_help_text');
    const assignmentContainer = document.getElementById('manualAssignmentContainer');

    if (!warehouseSelect || !branchSelect) return;

    // Reset
    branchSelect.required = false;
    warehouseSelect.required = false;
    if (branchRequired) branchRequired.style.display = 'none';
    if (warehouseRequired) warehouseRequired.style.display = 'none';

    // Super Admin: nothing required, hide assignment
    if (role === 'super_admin') {
      if (branchHelpText) branchHelpText.textContent = 'Optional - Auto-selected dari warehouse';
      if (warehouseHelpText) warehouseHelpText.textContent = 'Optional';
      
      if (assignmentContainer) {
        assignmentContainer.style.display = 'none';
        warehouseSelect.value = '';
        branchSelect.value = '';
      }
      return;
    }

    // Show assignment container for non-super-admin
    if (assignmentContainer) {
      assignmentContainer.style.display = '';
    }

    // Central roles
    if (role === 'central_manager' || role === 'central_staff') {
      warehouseSelect.required = true;
      if (warehouseRequired) warehouseRequired.style.display = 'inline';
      if (branchHelpText) branchHelpText.textContent = 'Auto-selected dari warehouse';
      if (warehouseHelpText) warehouseHelpText.textContent = 'Required - Must be Central Warehouse';
      return;
    }

    // Branch roles
    if (role === 'branch_manager' || role === 'branch_staff') {
      warehouseSelect.required = true;
      if (warehouseRequired) warehouseRequired.style.display = 'inline';
      if (branchHelpText) branchHelpText.textContent = 'Auto-selected dari warehouse';
      if (warehouseHelpText) warehouseHelpText.textContent = 'Required - Must be Branch Warehouse';
      return;
    }

    // Outlet roles
    if (role === 'outlet_manager' || role === 'outlet_staff') {
      warehouseSelect.required = true;
      if (warehouseRequired) warehouseRequired.style.display = 'inline';
      if (branchHelpText) branchHelpText.textContent = 'Auto-selected dari warehouse';
      if (warehouseHelpText) warehouseHelpText.textContent = 'Required - Must be Outlet Warehouse';
      return;
    }
  }

  function updatePreview() {
    const name = fullNameInput.value || 'User Name';
    const initials = name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) || 'UN';
    previewInitial.textContent = initials;
    previewName.textContent = name;
    previewUsername.textContent = usernameInput.value || '-';

    const selectedRole = document.querySelector('input[name="role"]:checked');
    if (selectedRole) {
      const roleLabel = selectedRole.closest('.role-card').querySelector('.fw-semibold').textContent;
      previewRole.textContent = roleLabel;
    }

    if (branchSelect && previewBranch) {
      if (branchSelect.value) {
        const branchText = branchSelect.options[branchSelect.selectedIndex].text;
        previewBranch.textContent = branchText;
        if (previewBranchContainer) previewBranchContainer.style.display = 'block';
      } else {
        if (previewBranchContainer) previewBranchContainer.style.display = 'none';
      }
    }

    if (warehouseSelect && previewWarehouse) {
      if (warehouseSelect.value) {
        const warehouseText = warehouseSelect.options[warehouseSelect.selectedIndex].text;
        previewWarehouse.textContent = warehouseText;
        if (previewWarehouseContainer) previewWarehouseContainer.style.display = 'block';
      } else {
        if (previewWarehouseContainer) previewWarehouseContainer.style.display = 'none';
      }
    }

    const statusText = statusSelect.options[statusSelect.selectedIndex].text;
    previewStatus.textContent = statusText;
    previewStatus.className = statusSelect.value === 'ACTIVE' ? 'badge bg-success' : 'badge bg-secondary';
  }

  function filterWarehouseOptions(role) {
    if (!warehouseSelect) return;

    let targetType = null;
    if (role.includes('central')) {
      targetType = 'central';
    } else if (role.includes('branch')) {
      targetType = 'branch';
    } else if (role.includes('outlet')) {
      targetType = 'outlet';
    }

    const currentSelectedOption = warehouseSelect.options[warehouseSelect.selectedIndex];
    if (currentSelectedOption && 
        currentSelectedOption.value !== "" && 
        currentSelectedOption.getAttribute('data-type') !== targetType && 
        targetType !== null) {
      warehouseSelect.value = "";
    }

    warehouseSelect.querySelectorAll('option').forEach(option => {
      const optionValue = option.value;
      const optionType = option.getAttribute('data-type');

      if (optionValue === "") {
        option.style.display = 'block';
        return;
      }

      if (targetType === null || optionType === targetType) {
        option.style.display = 'block';
      } else {
        option.style.display = 'none';
      }
    });

    updatePreview();
  }

  // Form submission
  form.addEventListener('submit', function(e) {
    const password = passwordInput.value;
    const passwordConfirm = passwordConfirmInput.value;
    
    if (password && password !== passwordConfirm) {
      e.preventDefault();
      alert('⚠️ Password dan Konfirmasi Password tidak sama!');
      passwordConfirmInput.focus();
      return false;
    }
    
    if (password && password.length < 6) {
      e.preventDefault();
      alert('⚠️ Password minimal 6 karakter!');
      passwordInput.focus();
      return false;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Mengupdate...';
    submitBtn.disabled = true;
  });

  // Real-time password match validation
  const passwordInputs = [passwordInput, passwordConfirmInput];
  passwordInputs.forEach(input => {
    input.addEventListener('input', function() {
      const password = passwordInput.value;
      const passwordConfirm = passwordConfirmInput.value;
      
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

  // Initial setup
  const selectedRole = document.querySelector('input[name="role"]:checked');
  if (selectedRole) {
    updateRequiredFields(selectedRole.value);
    filterWarehouseOptions(selectedRole.value);
  }
  
  updatePreview();

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

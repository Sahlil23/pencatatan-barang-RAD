@extends('layouts.admin')

@section('title', 'Tambah User - Chicking BJM')

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
        <li class="breadcrumb-item active">Tambah User</li>
      </ol>
    </nav>
  </div>
</div>



<div class="row">
  <!-- Form -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-user-plus me-2"></i>
          Form Tambah User Baru
        </h5>
      </div>

      <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST" id="createUserForm">
          @csrf

          <!-- Role Selection -->
          <div class="mb-4">
            <label class="form-label">
              Pilih Role <span class="text-danger">*</span>
            </label>
            <div class="row">
              @foreach($availableRoles as $roleKey => $roleName)
                <div class="col-md-6 mb-3">
                  <label class="role-card {{ old('role') == $roleKey ? 'selected' : '' }}">
                    <div class="d-flex align-items-center">
                      <input type="radio" name="role" value="{{ $roleKey }}" 
                             class="form-check-input me-3" 
                             {{ old('role') == $roleKey ? 'checked' : '' }}
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
              <label for="full_name" class="form-label">
                Nama Lengkap <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                     id="full_name" name="full_name" 
                     value="{{ old('full_name') }}" 
                     placeholder="Contoh: John Doe"
                     required>
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
                     value="{{ old('username') }}" 
                     placeholder="Contoh: johndoe"
                     required>
              <small class="text-muted">Username harus unik dan akan digunakan untuk login</small>
              @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Email -->
            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control @error('email') is-invalid @enderror" 
                     id="email" name="email" 
                     value="{{ old('email') }}" 
                     placeholder="Contoh: john@example.com">
              <small class="text-muted">Optional - untuk notifikasi sistem</small>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Phone -->
            <div class="col-md-6 mb-3">
              <label for="phone" class="form-label">No. Telepon</label>
              <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                     id="phone" name="phone" 
                     value="{{ old('phone') }}" 
                     placeholder="Contoh: 081234567890">
              <small class="text-muted">Optional - untuk kontak darurat</small>
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <hr class="my-4">

          <!-- Password -->
          <h6 class="mb-3">Keamanan</h6>
          
          <div class="row">
            <!-- Password -->
            <div class="col-md-6 mb-3">
              <label for="password" class="form-label">
                Password <span class="text-danger">*</span>
              </label>
              <input type="password" class="form-control @error('password') is-invalid @enderror" 
                     id="password" name="password" 
                     placeholder="Minimal 6 karakter"
                     required>
              <small class="text-muted">Password minimal 6 karakter</small>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="col-md-6 mb-3">
              <label for="password_confirmation" class="form-label">
                Konfirmasi Password <span class="text-danger">*</span>
              </label>
              <input type="password" class="form-control" 
                     id="password_confirmation" name="password_confirmation" 
                     placeholder="Ulangi password"
                     required>
              <small class="text-muted">Harus sama dengan password</small>
            </div>
          </div>

          <hr class="my-4">

          <!-- Assignment -->
     

            <div class="row" id="manualAssignmentContainer">  
              <!-- Warehouse - SELECT FIRST -->
              <div class="col-md-6 mb-3">
                <label for="warehouse_id" class="form-label">
                  Warehouse
                  <span class="text-danger" id="warehouse_required_indicator" style="display: none;">*</span>
                </label>
                <select name="warehouse_id" id="warehouse_id" 
                        class="form-select @error('warehouse_id') is-invalid @enderror">
                  <option value="">Pilih Warehouse Terlebih Dahulu</option>
                  @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" 
                            data-type="{{ $warehouse->warehouse_type }}"
                            data-branch="{{ $warehouse->branch_id }}"
                            {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
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
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
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
              <hr class="my-4">
            </div>
         


          <!-- Status -->
          <h6 class="mb-3">Status</h6>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="status" class="form-label">
                Status Akun <span class="text-danger">*</span>
              </label>
              <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach($statuses as $key => $value)
                  <option value="{{ $key }}" {{ old('status', 'ACTIVE') == $key ? 'selected' : '' }}>
                    {{ $value }}
                  </option>
                @endforeach
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Actions -->
          <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
              <i class="bx bx-save me-1"></i>
              Simpan User
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Preview -->
  <div class="col-lg-4">
    <div class="card sticky-top" style="top: 100px;">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-show me-2"></i>
          Preview User
        </h6>
      </div>
      <div class="card-body">
        <div class="text-center mb-3">
          <div class="avatar avatar-xl mx-auto" id="previewAvatar">
            <span class="avatar-initial rounded-circle bg-label-primary" id="previewInitial">
              UN
            </span>
          </div>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Nama Lengkap</small>
          <div class="fw-semibold" id="previewName">-</div>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Username</small>
          <code id="previewUsername">-</code>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Role</small>
          <span class="badge bg-primary" id="previewRole">-</span>
        </div>

        @if(!$isLimitedCreator)
        <div class="mb-3" id="previewBranchContainer" style="display: none;">
          <small class="text-muted d-block">Branch</small>
          <div id="previewBranch">-</div>
        </div>

        <div class="mb-3" id="previewWarehouseContainer" style="display: none;">
          <small class="text-muted d-block">Warehouse</small>
          <div id="previewWarehouse">-</div>
        </div>
        @else
        <div class="mb-3">
          <small class="text-muted d-block">Branch</small>
          <div>
            @if($defaultBranchId)
              @php $branch = \App\Models\Branch::find($defaultBranchId); @endphp
              {{ $branch->branch_name ?? 'N/A' }}
            @else
              -
            @endif
          </div>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Warehouse</small>
          <div>
            @if($defaultWarehouseId)
              @php $warehouse = \App\Models\Warehouse::find($defaultWarehouseId); @endphp
              {{ $warehouse->warehouse_name ?? 'N/A' }}
            @else
              -
            @endif
          </div>
        </div>
        @endif

        <div class="mb-3">
          <small class="text-muted d-block">Status</small>
          <span class="badge bg-success" id="previewStatus">Active</span>
        </div>

        @if(!auth()->user()->isSuperAdmin())
        <div class="alert alert-info alert-sm mt-3">
          <small>
            <i class="bx bx-info-circle me-1"></i>
            User akan di-assign ke warehouse & branch Anda
          </small>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Form elements
  const form = document.getElementById('createUserForm');
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

  // ✅ AUTO-SELECT BRANCH WHEN WAREHOUSE CHANGES
  if (warehouseSelect && branchSelect) {
    warehouseSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const warehouseBranchId = selectedOption.getAttribute('data-branch');
      
      if (warehouseBranchId) {
        // Auto-select branch
        branchSelect.value = warehouseBranchId;
        
        // Show visual feedback
        branchSelect.classList.add('border-success');
        setTimeout(() => {
          branchSelect.classList.remove('border-success');
        }, 1000);
        
        console.log('Branch auto-selected:', warehouseBranchId);
      } else {
        // Clear branch if warehouse has no branch
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
      this.querySelector('input[type="radio"]').checked = true;
      updateRequiredFields(this.querySelector('input[type="radio"]').value);

      const roleRadio = this.querySelector('input[type="radio"]');
      roleRadio.checked = true;
      const selectedRoleValue = roleRadio.value;

      updateRequiredFields(selectedRoleValue);
      updatePreview();

      filterWarehouseOptions(selectedRoleValue);
    });
  });

  // Real-time preview
  if (fullNameInput) {
    fullNameInput.addEventListener('input', updatePreview);
  }
  if (usernameInput) {
    usernameInput.addEventListener('input', updatePreview);
  }
  
  roleInputs.forEach(input => {
    input.addEventListener('change', function() {
      updateRequiredFields(this.value);
      updatePreview();
    });
  });

  if (branchSelect) {
    branchSelect.addEventListener('change', updatePreview);
  }
  if (statusSelect) {
    statusSelect.addEventListener('change', updatePreview);
  }

  // ✅ Dynamic required fields based on role
  function updateRequiredFields(role) {
    const branchRequired = document.getElementById('branch_required_indicator');
    const warehouseRequired = document.getElementById('warehouse_required_indicator');
    const branchHelpText = document.getElementById('branch_help_text');
    const warehouseHelpText = document.getElementById('warehouse_help_text');
    const assignmentContainer = document.getElementById('manualAssignmentContainer');

    // Reset
    if (branchSelect) branchSelect.required = false;
    if (warehouseSelect) warehouseSelect.required = false;
    if (branchRequired) branchRequired.style.display = 'none';
    if (warehouseRequired) warehouseRequired.style.display = 'none';

    // Super Admin: nothing required
    if (role === 'super_admin') {
      if (branchHelpText) branchHelpText.textContent = 'Optional - Auto-selected dari warehouse';
      if (warehouseHelpText) warehouseHelpText.textContent = 'Optional';

      
      if (assignmentContainer) {
        // 1. Sembunyikan seluruh baris
        assignmentContainer.style.display = 'none'; 
        
        // 2. (PENTING) Kosongkan nilainya agar tidak terkirim
        if (warehouseSelect) warehouseSelect.value = null;
        if (branchSelect) branchSelect.value = null;
      }
      return; // Selesai untuk super_admin
    }

    if (assignmentContainer) {
      assignmentContainer.style.display = ''; // Revert ke default (tampil)
    }

    // Central roles: warehouse required (central type), branch auto-set
    if (role === 'central_manager' || role === 'central_staff') {
      if (warehouseSelect) {
        warehouseSelect.required = true;
        warehouseRequired.style.display = 'inline';
      }
      if (branchHelpText) branchHelpText.textContent = 'Auto-selected dari warehouse';
      if (warehouseHelpText) warehouseHelpText.textContent = 'Required - Must be Central Warehouse';
      return;
    }

    // Branch roles: warehouse + branch required (branch auto-set)
    if (role === 'branch_manager' || role === 'branch_staff') {
      if (warehouseSelect) {
        warehouseSelect.required = true;
        warehouseRequired.style.display = 'inline';
      }
      if (branchHelpText) branchHelpText.textContent = 'Auto-selected dari warehouse';
      if (warehouseHelpText) warehouseHelpText.textContent = 'Required - Must be Branch Warehouse';
      return;
    }

    // Outlet roles: warehouse + branch required (branch auto-set)
    if (role === 'outlet_manager' || role === 'outlet_staff') {
      if (warehouseSelect) {
        warehouseSelect.required = true;
        warehouseRequired.style.display = 'inline';
      }
      if (branchHelpText) branchHelpText.textContent = 'Auto-selected dari warehouse';
      if (warehouseHelpText) warehouseHelpText.textContent = 'Required - Must be Outlet Warehouse';
      return;
    }
  }

  function updatePreview() {
    // Update initial
    const name = fullNameInput.value || 'User Name';
    const initials = name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) || 'UN';
    previewInitial.textContent = initials;

    // Update name
    previewName.textContent = name;

    // Update username
    previewUsername.textContent = usernameInput.value || '-';

    // Update role
    const selectedRole = document.querySelector('input[name="role"]:checked');
    if (selectedRole) {
      const roleLabel = selectedRole.closest('.role-card').querySelector('.fw-semibold').textContent;
      previewRole.textContent = roleLabel;
      updateRequiredFields(selectedRole.value);
      filterWarehouseOptions(selectedRole.value);
    } else {
      previewRole.textContent = '-';
    }

    // Update branch (if not limited creator)
    if (branchSelect) {
      if (branchSelect.value) {
        const branchText = branchSelect.options[branchSelect.selectedIndex].text;
        previewBranch.textContent = branchText;
        previewBranchContainer.style.display = 'block';
      } else {
        previewBranchContainer.style.display = 'none';
      }
    }

    // Update warehouse (if not limited creator)
    if (warehouseSelect) {
      if (warehouseSelect.value) {
        const warehouseText = warehouseSelect.options[warehouseSelect.selectedIndex].text;
        previewWarehouse.textContent = warehouseText;
        previewWarehouseContainer.style.display = 'block';
      } else {
        previewWarehouseContainer.style.display = 'none';
      }
    }

    // Update status
    const statusText = statusSelect.options[statusSelect.selectedIndex].text;
    previewStatus.textContent = statusText;
    previewStatus.className = statusSelect.value === 'ACTIVE' ? 'badge bg-success' : 'badge bg-secondary';
  }

  // Form validation
  form.addEventListener('submit', function(e) {
    // Password confirmation check
    if (passwordInput.value !== passwordConfirmInput.value) {
      e.preventDefault();
      alert('⚠️ Password dan Konfirmasi Password tidak sama!');
      passwordConfirmInput.focus();
      return false;
    }

    // Password length check
    if (passwordInput.value.length < 6) {
      e.preventDefault();
      alert('⚠️ Password minimal 6 karakter!');
      passwordInput.focus();
      return false;
    }

    // Disable submit button
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
  });

  // Initial check
  const selectedRole = document.querySelector('input[name="role"]:checked');
  if (selectedRole) {
    updateRequiredFields(selectedRole.value);
  }

  // Initial preview
  updatePreview();

  function filterWarehouseOptions(role) {
   const warehouseSelect = document.getElementById('warehouse_id');
   
   // 1. Keluar jika ini bukan tampilan Super Admin (dropdown-nya tidak ada)
   if (!warehouseSelect) {
       return;
   }

   // 2. Tentukan tipe warehouse yang dicari berdasarkan nama role
   let targetType = null; // null = tampilkan semua (untuk super_admin)
   if (role.includes('central')) {
       targetType = 'central';
   } else if (role.includes('branch')) {
       targetType = 'branch';
   } else if (role.includes('outlet')) {
       targetType = 'outlet';
   }
   
   // 3. Reset pilihan warehouse jika yang sedang dipilih akan disembunyikan
   const currentSelectedOption = warehouseSelect.options[warehouseSelect.selectedIndex];
   if (currentSelectedOption && 
      currentSelectedOption.value !== "" && 
      currentSelectedOption.getAttribute('data-type') !== targetType && 
      targetType !== null) {
       warehouseSelect.value = ""; // Reset ke "Pilih Warehouse"
   }

   // 4. Loop semua <option> dan tampilkan/sembunyikan
   warehouseSelect.querySelectorAll('option').forEach(option => {
       const optionValue = option.value;
       const optionType = option.getAttribute('data-type');

       // Selalu tampilkan placeholder ("Pilih Warehouse...")
       if (optionValue === "") {
      option.style.display = 'block';
      return;
       }

       // Tampilkan jika Tipe Target adalah null (Super Admin) ATAU jika Tipe Cocok
       if (targetType === null || optionType === targetType) {
      option.style.display = 'block';
       } else {
      // Sembunyikan jika tidak cocok
      option.style.display = 'none';
       }
   });

   // 5. Panggil updatePreview untuk sinkronisasi, jaga-jaga jika warehouse di-reset
   updatePreview();
    }
    
});
</script>
@endpush
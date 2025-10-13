@extends('layouts.admin')

@section('title', 'Tambah Supplier - Chicking BJM')

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
          <a href="{{ route('suppliers.index') }}">Supplier</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Supplier</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tambah Supplier Baru</h5>
        <small class="text-muted float-end">Form input data supplier</small>
      </div>
      <div class="card-body">
        <form action="{{ route('suppliers.store') }}" method="POST">
          @csrf
          
          <!-- Supplier Name -->
          <div class="mb-3">
            <label class="form-label" for="supplier_name">Nama Supplier <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-store"></i></span>
              <input
                type="text"
                class="form-control @error('supplier_name') is-invalid @enderror"
                id="supplier_name"
                name="supplier_name"
                placeholder="Masukkan nama supplier"
                value="{{ old('supplier_name') }}"
                required
              />
            </div>
            @error('supplier_name')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Nama supplier harus unik dan maksimal 150 karakter</div>
            @enderror
          </div>

          <!-- Contact Person -->
          <div class="mb-3">
            <label class="form-label" for="contact_person">Contact Person</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-user"></i></span>
              <input
                type="text"
                class="form-control @error('contact_person') is-invalid @enderror"
                id="contact_person"
                name="contact_person"
                placeholder="Masukkan nama contact person"
                value="{{ old('contact_person') }}"
              />
            </div>
            @error('contact_person')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Nama orang yang dapat dihubungi (opsional)</div>
            @enderror
          </div>

          <!-- Phone -->
          <div class="mb-3">
            <label class="form-label" for="phone">Nomor Telepon</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-phone"></i></span>
              <input
                type="tel"
                class="form-control @error('phone') is-invalid @enderror"
                id="phone"
                name="phone"
                placeholder="Contoh: 0812-3456-7890"
                value="{{ old('phone') }}"
              />
            </div>
            @error('phone')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Nomor telepon yang dapat dihubungi (opsional)</div>
            @enderror
          </div>

          <!-- Address -->
          <div class="mb-3">
            <label class="form-label" for="address">Alamat</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-map"></i></span>
              <textarea
                id="address"
                name="address"
                class="form-control @error('address') is-invalid @enderror"
                placeholder="Masukkan alamat lengkap supplier"
                rows="4"
              >{{ old('address') }}</textarea>
            </div>
            @error('address')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Alamat lengkap supplier untuk pengiriman dan korespondensi</div>
            @enderror
          </div>

          <!-- Action Buttons -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
            <div>
              <button type="reset" class="btn btn-outline-warning me-2">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i>
                Simpan Supplier
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Info Card -->
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-info-circle me-2"></i>
          Informasi
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info" role="alert">
          <h6 class="alert-heading">Tips Supplier:</h6>
          <ul class="mb-0">
            <li>Nama supplier harus jelas dan mudah diingat</li>
            <li>Contact person memudahkan komunikasi</li>
            <li>Nomor telepon sebaiknya aktif</li>
            <li>Alamat lengkap penting untuk pengiriman</li>
          </ul>
        </div>
        
        <hr>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Total Supplier:</span>
          <span class="badge bg-primary">{{ App\Models\Supplier::count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Dengan Kontak:</span>
          <span class="badge bg-success">{{ App\Models\Supplier::whereNotNull('contact_person')->count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Dengan Phone:</span>
          <span class="badge bg-info">{{ App\Models\Supplier::whereNotNull('phone')->count() }}</span>
        </div>
      </div>
    </div>

    <!-- Preview Card -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-show me-2"></i>
          Preview Supplier
        </h6>
      </div>
      <div class="card-body">
        <div class="preview-content">
          <div class="d-flex align-items-center mb-3">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-store"></i>
              </span>
            </div>
            <div>
              <h6 class="mb-0" id="preview-name">Nama Supplier</h6>
              <small class="text-muted" id="preview-contact">Contact Person</small>
            </div>
          </div>
          
          <div class="border rounded p-3">
            <div class="row mb-2">
              <div class="col-4">
                <small class="text-muted">Telepon:</small>
              </div>
              <div class="col-8">
                <small id="preview-phone">Tidak ada</small>
              </div>
            </div>
            <div class="row">
              <div class="col-4">
                <small class="text-muted">Alamat:</small>
              </div>
              <div class="col-8">
                <small id="preview-address">Tidak ada alamat</small>
              </div>
            </div>
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
  // Elements
  const supplierNameInput = document.getElementById('supplier_name');
  const contactPersonInput = document.getElementById('contact_person');
  const phoneInput = document.getElementById('phone');
  const addressInput = document.getElementById('address');
  
  // Preview elements
  const previewName = document.getElementById('preview-name');
  const previewContact = document.getElementById('preview-contact');
  const previewPhone = document.getElementById('preview-phone');
  const previewAddress = document.getElementById('preview-address');

  // Update preview
  function updatePreview() {
    const name = supplierNameInput.value || 'Nama Supplier';
    const contact = contactPersonInput.value || 'Contact Person';
    const phone = phoneInput.value || 'Tidak ada';
    const address = addressInput.value || 'Tidak ada alamat';
    
    previewName.textContent = name;
    previewContact.textContent = contact;
    previewPhone.textContent = phone;
    previewAddress.textContent = address;
  }

  // Event listeners
  supplierNameInput.addEventListener('input', updatePreview);
  contactPersonInput.addEventListener('input', updatePreview);
  phoneInput.addEventListener('input', updatePreview);
  addressInput.addEventListener('input', updatePreview);

  // Initialize
  updatePreview();

  // Phone number formatting
  phoneInput.addEventListener('input', function() {
    let value = this.value.replace(/\D/g, ''); // Remove non-digits
    if (value.startsWith('0')) {
      // Format: 0812-3456-7890
      value = value.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
    }
    this.value = value;
    updatePreview();
  });

  // Form validation
  const form = document.querySelector('form');
  form.addEventListener('submit', function(e) {
    const supplierName = supplierNameInput.value.trim();
    
    if (!supplierName) {
      e.preventDefault();
      supplierNameInput.focus();
      supplierNameInput.classList.add('is-invalid');
      
      // Show error message
      let errorDiv = supplierNameInput.parentNode.parentNode.querySelector('.text-danger');
      if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'form-text text-danger';
        supplierNameInput.parentNode.parentNode.appendChild(errorDiv);
      }
      errorDiv.textContent = 'Nama supplier wajib diisi';
      
      return false;
    }
  });

  // Remove validation error on input
  supplierNameInput.addEventListener('input', function() {
    this.classList.remove('is-invalid');
    const errorDiv = this.parentNode.parentNode.querySelector('.text-danger');
    if (errorDiv && errorDiv.textContent === 'Nama supplier wajib diisi') {
      errorDiv.remove();
    }
  });
});
</script>
@endpush
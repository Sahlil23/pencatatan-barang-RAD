@extends('layouts.admin')

@section('title', 'Edit Supplier - Chicking BJM')

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
        <li class="breadcrumb-item active" aria-current="page">Edit Supplier</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Supplier</h5>
        <small class="text-muted float-end">Form edit data supplier</small>
      </div>
      <div class="card-body">
        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
          @csrf
          @method('PUT')
          
          <!-- Supplier ID Info -->
          <div class="mb-3">
            <label class="form-label">ID Supplier</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-hash"></i></span>
              <input
                type="text"
                class="form-control"
                value="#{{ $supplier->id }}"
                readonly
              />
            </div>
            <div class="form-text">ID supplier tidak dapat diubah</div>
          </div>

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
                value="{{ old('supplier_name', $supplier->supplier_name) }}"
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
                value="{{ old('contact_person', $supplier->contact_person) }}"
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
                value="{{ old('phone', $supplier->phone) }}"
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
              >{{ old('address', $supplier->address) }}</textarea>
            </div>
            @error('address')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Alamat lengkap supplier untuk pengiriman dan korespondensi</div>
            @enderror
          </div>

          <!-- Created/Updated Info -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Dibuat pada</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                <input
                  type="text"
                  class="form-control"
                  value="{{ $supplier->created_at->format('d/m/Y H:i') }}"
                  readonly
                />
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Terakhir diupdate</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-time"></i></span>
                <input
                  type="text"
                  class="form-control"
                  value="{{ $supplier->updated_at->format('d/m/Y H:i') }}"
                  readonly
                />
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
            <div>
              <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-outline-info me-2">
                <i class="bx bx-show me-1"></i>
                Lihat Detail
              </a>
              <button type="reset" class="btn btn-outline-warning me-2">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i>
                Update Supplier
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
          Informasi Supplier
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-warning" role="alert">
          <h6 class="alert-heading">
            <i class="bx bx-error-circle me-1"></i>
            Perhatian!
          </h6>
          <ul class="mb-0">
            <li>Perubahan data supplier akan mempengaruhi semua item terkait</li>
            <li>Pastikan informasi kontak tetap valid</li>
            <li>Supplier ini memiliki <strong>{{ $supplier->items->count() }} item</strong> terkait</li>
          </ul>
        </div>

        <hr>

        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">ID Supplier:</span>
          <span class="badge bg-primary">#{{ $supplier->id }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Total Item:</span>
          <span class="badge bg-info">{{ $supplier->items->count() }} Item</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Dibuat:</span>
          <span class="text-muted">{{ $supplier->created_at->diffForHumans() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Update terakhir:</span>
          <span class="text-muted">{{ $supplier->updated_at->diffForHumans() }}</span>
        </div>
      </div>
    </div>

    <!-- Preview Card -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-show me-2"></i>
          Preview Perubahan
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
            <div class="flex-grow-1">
              <h6 class="mb-0" id="preview-name">{{ $supplier->supplier_name }}</h6>
              <small class="text-muted" id="preview-contact">{{ $supplier->contact_person ?: 'Contact Person' }}</small>
            </div>
          </div>
          
          <!-- Comparison View -->
          <div class="border rounded p-3">
            <h6 class="text-muted mb-2">Perbandingan:</h6>
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Nama Asli:</small>
                <small class="fw-semibold" id="original-name">{{ $supplier->supplier_name }}</small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Nama Baru:</small>
                <small class="fw-semibold text-primary" id="new-name">{{ $supplier->supplier_name }}</small>
              </div>
            </div>
            <div class="row">
              <div class="col-6">
                <small class="text-muted d-block">Kontak Asli:</small>
                <small class="fw-semibold" id="original-contact">{{ $supplier->contact_person ?: 'Tidak ada' }}</small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Kontak Baru:</small>
                <small class="fw-semibold text-primary" id="new-contact">{{ $supplier->contact_person ?: 'Tidak ada' }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Related Items -->
    @if($supplier->items->count() > 0)
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-package me-2"></i>
          Item Terkait ({{ $supplier->items->count() }})
        </h6>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          @foreach($supplier->items->take(5) as $item)
          <div class="list-group-item px-0 py-2 border-0">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="bx bx-box"></i>
                </span>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">{{ Str::limit($item->item_name, 20) }}</h6>
                <small class="text-muted">{{ $item->sku }}</small>
              </div>
              <div class="text-end">
                <small class="text-muted">{{ $item->current_stock }} {{ $item->unit }}</small>
              </div>
            </div>
          </div>
          @endforeach
          
          @if($supplier->items->count() > 5)
          <div class="list-group-item px-0 py-2 border-0 text-center">
            <small class="text-muted">Dan {{ $supplier->items->count() - 5 }} item lainnya...</small>
          </div>
          @endif
        </div>
      </div>
    </div>
    @endif
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
  const newName = document.getElementById('new-name');
  const newContact = document.getElementById('new-contact');

  // Original values
  const originalName = "{{ $supplier->supplier_name }}";
  const originalContact = "{{ $supplier->contact_person ?? '' }}";

  // Update preview
  function updatePreview() {
    const name = supplierNameInput.value || originalName;
    const contact = contactPersonInput.value || 'Contact Person';
    
    previewName.textContent = name;
    previewContact.textContent = contact;
    newName.textContent = name;
    newContact.textContent = contact;
    
    // Highlight changes
    if (name !== originalName) {
      newName.classList.add('text-warning');
      newName.classList.remove('text-primary');
    } else {
      newName.classList.add('text-primary');
      newName.classList.remove('text-warning');
    }
    
    if (contactPersonInput.value !== originalContact) {
      newContact.classList.add('text-warning');
      newContact.classList.remove('text-primary');
    } else {
      newContact.classList.add('text-primary');
      newContact.classList.remove('text-warning');
    }
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

    // Confirm if no changes made
    if (supplierName === originalName && 
        contactPersonInput.value === originalContact &&
        phoneInput.value === "{{ $supplier->phone ?? '' }}" &&
        addressInput.value === "{{ str_replace(["\r", "\n"], ["\\r", "\\n"], $supplier->address ?? '') }}") {
      if (!confirm('Tidak ada perubahan yang dibuat. Apakah Anda yakin ingin melanjutkan?')) {
        e.preventDefault();
        return false;
      }
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

  // Reset button functionality
  const resetButton = document.querySelector('button[type="reset"]');
  resetButton.addEventListener('click', function() {
    setTimeout(() => {
      updatePreview();
    }, 10);
  });
});
</script>
@endpush
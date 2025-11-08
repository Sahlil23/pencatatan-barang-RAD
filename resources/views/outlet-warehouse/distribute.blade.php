@extends('layouts.admin')

@section('title', 'Distribusi ke Kitchen - Outlet Warehouse')

@section('content')
<div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('outlet-warehouse.index') }}">Outlet Warehouse</a></li>
        <li class="breadcrumb-item"><a href="{{ route('outlet-warehouse.index', ['warehouse_id' => $warehouse->id]) }}">{{ $warehouse->warehouse_name }}</a></li>
        <li class="breadcrumb-item active">Distribusi ke Kitchen</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bx bx-export me-2"></i>
      Distribusi Stock ke Kitchen
    </h5>
    <a href="{{ route('outlet-warehouse.index', ['warehouse_id' => $warehouse->id]) }}" class="btn btn-sm btn-outline-secondary">
      <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
  </div>

  <div class="card-body">
    <!-- Warehouse Info -->
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="alert alert-info mb-0">
          <div class="d-flex align-items-center">
            <i class="bx bx-store fs-4 me-3"></i>
            <div>
              <h6 class="mb-1">{{ $warehouse->warehouse_name }}</h6>
              <small class="text-muted">
                {{ $warehouse->warehouse_code }} • {{ $warehouse->branch->branch_name ?? '-' }}
              </small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="alert alert-success mb-0">
          <div class="d-flex align-items-center">
            <i class="bx bx-restaurant fs-4 me-3"></i>
            <div>
              <h6 class="mb-1">Kitchen Tujuan</h6>
              <small class="text-muted">
                Kitchen {{ $warehouse->warehouse_name }}
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <form action="{{ route('outlet-warehouse.distribute.store', $warehouse->id) }}" 
          method="POST" 
          id="distributeForm"
          onsubmit="console.log('Form onsubmit fired')">
      @csrf

      <!-- Notes (Optional) -->
      <div class="row mb-4">
        <div class="col-12">
          <label class="form-label">Catatan Distribusi (Opsional)</label>
          <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Catatan tambahan untuk distribusi ini...">{{ old('notes') }}</textarea>
          @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

    <!-- Items Table -->
    <div class="table-responsive">
      <table class="table table-hover" id="itemsTable">
        <thead class="table-light">
          <tr>
            <th width="50">
              <input type="checkbox" id="selectAll" class="form-check-input">
            </th>
            <th>Item</th>
            <th class="text-end">Stock Tersedia</th>
            <th width="150">Quantity</th>
            <th width="200">Catatan Item</th>
          </tr>
        </thead>
        <tbody>
          @forelse($stockItems as $stock)
            <tr>
              <td>
                <input type="checkbox" name="items[{{ $loop->index }}][selected]" value="1" class="form-check-input item-checkbox">
                <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $stock->item_id }}">
              </td>
              <td>
                <div>
                  <strong>{{ $stock->item->item_name }}</strong>
                  <div class="text-muted small">{{ $stock->item->sku }}</div>
                  @if($stock->item->category)
                    <span class="badge bg-label-secondary">{{ $stock->item->category->category_name }}</span>
                  @endif
                </div>
              </td>
              <td class="text-end">
                <span class="badge bg-label-primary">
                  {{ rtrim(rtrim(number_format($stock->closing_stock, 3, '.', ','), '0'), '.') }}
                  {{ $stock->item->unit }}
                </span>
              </td>
              <td>
                <input type="number" 
                       name="items[{{ $loop->index }}][quantity]" 
                       class="form-control form-control-sm quantity-input" 
                       step="0.001" 
                       min="0.001" 
                       max="{{ $stock->closing_stock }}"
                       placeholder="0"
                       data-available="{{ $stock->closing_stock }}">
                <small class="text-muted">Maks: {{ rtrim(rtrim(number_format($stock->closing_stock, 3, '.', ','), '0'), '.') }}</small>
              </td>
              <td>
                <input type="text" 
                       name="items[{{ $loop->index }}][notes]" 
                       class="form-control form-control-sm" 
                       placeholder="Catatan...">
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                Tidak ada stock tersedia untuk didistribusikan
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($stockItems->isNotEmpty())
      <div class="mt-4 d-flex justify-content-between align-items-center">
        <div>
          <span class="text-muted">Total dipilih: </span>
          <strong id="selectedCount">0</strong> item
        </div>
        <div>
          <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
            <i class="bx bx-x me-1"></i> Batal
          </button>
          <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="bx bx-send me-1"></i> Kirim ke Kitchen
          </button>
        </div>
      </div>
    @endif
  </form>
</div>
</div>
@endsection

@push('styles')
<style>
  .required::after {
    content: " *";
    color: red;
  }
  .table tbody tr:hover {
    background-color: rgba(105, 108, 255, 0.04);
  }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const selectAll = document.getElementById('selectAll');
  const itemCheckboxes = document.querySelectorAll('.item-checkbox');
  const quantityInputs = document.querySelectorAll('.quantity-input');
  const submitBtn = document.getElementById('submitBtn');
  const selectedCountEl = document.getElementById('selectedCount');
  const form = document.getElementById('distributeForm');

  // Select All
  if (selectAll) {
    selectAll.addEventListener('change', function() {
      itemCheckboxes.forEach(cb => {
        cb.checked = this.checked;
        const row = cb.closest('tr');
        const qtyInput = row.querySelector('.quantity-input');
        if (this.checked && !qtyInput.value) {
          qtyInput.value = qtyInput.getAttribute('max');
        }
      });
      updateSelectedCount();
    });
  }

  // Individual checkbox
  itemCheckboxes.forEach(cb => {
    cb.addEventListener('change', function() {
      const row = this.closest('tr');
      const qtyInput = row.querySelector('.quantity-input');
      
      if (this.checked && !qtyInput.value) {
        qtyInput.value = qtyInput.getAttribute('max');
      } else if (!this.checked) {
        qtyInput.value = '';
      }
      
      updateSelectedCount();
    });
  });

  // Quantity input change
  quantityInputs.forEach(input => {
    input.addEventListener('input', function() {
      const row = this.closest('tr');
      const checkbox = row.querySelector('.item-checkbox');
      const available = parseFloat(this.getAttribute('data-available'));
      const value = parseFloat(this.value);

      if (value > 0) {
        checkbox.checked = true;
      } else {
        checkbox.checked = false;
      }

      // Validate max
      if (value > available) {
        this.value = available;
        alert(`Quantity tidak boleh melebihi stock tersedia (${available})`);
      }

      updateSelectedCount();
    });
  });

  // Update selected count
  function updateSelectedCount() {
    const checked = document.querySelectorAll('.item-checkbox:checked').length;
    if (selectedCountEl) {
      selectedCountEl.textContent = checked;
    }
    if (submitBtn) {
      submitBtn.disabled = checked === 0;
    }
  }

  // ✅ FIX: Form validation - Don't prevent submit if valid
  if (form) {
    form.addEventListener('submit', function(e) {
      console.log('Form submit triggered'); // ✅ Debug log
      
      const checkedItems = document.querySelectorAll('.item-checkbox:checked');
      
      // Validate: At least 1 item selected
      if (checkedItems.length === 0) {
        e.preventDefault();
        alert('Pilih minimal 1 item untuk didistribusikan!');
        console.log('Validation failed: No items selected');
        return false;
      }

      // Validate: All selected items have quantity
      let valid = true;
      let invalidItems = [];
      
      checkedItems.forEach(cb => {
        const row = cb.closest('tr');
        const qtyInput = row.querySelector('.quantity-input');
        const qty = parseFloat(qtyInput.value);
        
        if (!qty || qty <= 0) {
          valid = false;
          qtyInput.classList.add('is-invalid');
          const itemName = row.querySelector('strong').textContent;
          invalidItems.push(itemName);
        } else {
          qtyInput.classList.remove('is-invalid');
        }
      });

      if (!valid) {
        e.preventDefault();
        alert('Isi quantity untuk semua item yang dipilih!\n\nItem yang belum diisi:\n' + invalidItems.join('\n'));
        console.log('Validation failed: Invalid quantities', invalidItems);
        return false;
      }

      // ✅ Confirm before submit
      const confirmMsg = `Kirim ${checkedItems.length} item ke kitchen?\n\nPastikan data sudah benar!`;
      if (!confirm(confirmMsg)) {
        e.preventDefault();
        console.log('User cancelled submission');
        return false;
      }

      // ✅ Disable submit button to prevent double-click
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
      }

      // ✅ Let form submit naturally (don't prevent default here)
      console.log('Form validation passed, submitting...');
      // return true; // Let it submit
    });
  }

  // Initial count
  updateSelectedCount();
});
</script>
@endpush
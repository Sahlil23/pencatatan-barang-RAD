{{-- resources/views/admin/backup/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Database Backup - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Database Backup</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Action Cards -->
<div class="row mb-4">
  <div class="col-md-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="bx bx-download text-primary" style="font-size: 3rem;"></i>
        <h5 class="card-title mt-3">Create Backup</h5>
        <p class="card-text">Buat backup database untuk keamanan data</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
          <i class="bx bx-plus me-1"></i>
          Create Backup
        </button>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="bx bx-upload text-success" style="font-size: 3rem;"></i>
        <h5 class="card-title mt-3">Restore Database</h5>
        <p class="card-text">Restore database dari file backup</p>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#restoreModal">
          <i class="bx bx-upload me-1"></i>
          Restore
        </button>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="bx bx-time text-info" style="font-size: 3rem;"></i>
        <h5 class="card-title mt-3">Auto Backup</h5>
        <p class="card-text">Backup otomatis setiap hari jam 02:00</p>
        <span class="badge bg-success">Active</span>
      </div>
    </div>
  </div>
</div>

<!-- Backup List -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Backup</h5>
    <button class="btn btn-outline-primary btn-sm" onclick="refreshBackupList()">
      <i class="bx bx-refresh me-1"></i>
      Refresh
    </button>
  </div>
  <div class="card-body">
    @if($backups->count() > 0)
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Filename</th>
            <th>Type</th>
            <th>Size</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="backupTableBody">
          @foreach($backups as $backup)
          <tr>
            <td>
              <i class="bx bx-file text-primary me-2"></i>
              {{ $backup['filename'] }}
            </td>
            <td>
              <span class="badge bg-info">{{ $backup['type'] }}</span>
            </td>
            <td>{{ $backup['size'] }}</td>
            <td>{{ $backup['created_at']->format('d M Y H:i') }}</td>
            <td>
              <div class="btn-group" role="group">
                <a href="{{ route('backup.download', $backup['filename']) }}" 
                   class="btn btn-outline-primary btn-sm" title="Download">
                  <i class="bx bx-download"></i>
                </a>
                <button class="btn btn-outline-danger btn-sm" 
                        onclick="deleteBackup('{{ $backup['filename'] }}')" title="Delete">
                  <i class="bx bx-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <div class="text-center py-4">
      <i class="bx bx-folder-open text-muted" style="font-size: 4rem;"></i>
      <h5 class="text-muted mt-3">Belum ada backup</h5>
      <p class="text-muted">Buat backup pertama untuk mengamankan data Anda</p>
    </div>
    @endif
  </div>
</div>

<!-- Create Backup Modal -->
<div class="modal fade" id="createBackupModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create Database Backup</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="createBackupForm">
          @csrf
          <div class="mb-3">
            <label class="form-label">Backup Type</label>
            <select class="form-select" name="type" required>
              <option value="full">Full Backup (Structure + Data)</option>
              <option value="structure-only">Structure Only</option>
              <option value="data-only">Data Only</option>
            </select>
            <div class="form-text">
              <strong>Full:</strong> Lengkap struktur dan data<br>
              <strong>Structure:</strong> Hanya struktur tabel<br>
              <strong>Data:</strong> Hanya data tanpa struktur
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="createBackupBtn">
          <i class="bx bx-download me-1"></i>
          Create Backup
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Restore Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Restore Database</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Peringatan!</strong> Proses restore akan mengganti seluruh data yang ada.
            Pastikan Anda sudah membuat backup terlebih dahulu.
          </div>
          
          <div class="mb-3">
            <label class="form-label">Select Backup File</label>
            <input type="file" class="form-control" name="backup_file" 
                   accept=".sql,.zip" required>
            <div class="form-text">
              Upload file backup (.sql atau .zip)
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin restore? Data sekarang akan terganti!')">
            <i class="bx bx-upload me-1"></i>
            Restore Database
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('createBackupBtn').addEventListener('click', function() {
  const form = document.getElementById('createBackupForm');
  const formData = new FormData(form);
  const btn = this;
  
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creating...';
  
  fetch('{{ route("backup.create") }}', {
    method: 'POST',
    body: formData,
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Close modal
      bootstrap.Modal.getInstance(document.getElementById('createBackupModal')).hide();
      
      // Show success message
      showAlert('success', data.message);
      
      // Refresh backup list
      refreshBackupList();
      
      // Offer download
      if (confirm('Backup berhasil dibuat! Download sekarang?')) {
        window.open(data.download_url, '_blank');
      }
    } else {
      showAlert('error', data.message);
    }
  })
  .catch(error => {
    showAlert('error', 'Terjadi kesalahan: ' + error.message);
  })
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bx bx-download me-1"></i>Create Backup';
  });
});

function deleteBackup(filename) {
    if (!confirm('Yakin ingin menghapus backup ini?')) return;
    
    // Gunakan template literal untuk URL yang benar
    const deleteUrl = `/backup/delete/${encodeURIComponent(filename)}`;
    
    fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            refreshBackupList();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat menghapus backup');
    });
}

function refreshBackupList() {
  window.location.reload();
}

function showAlert(type, message) {
  const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
  const alertHtml = `
    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  
  // Insert at top of content
  document.querySelector('.card').insertAdjacentHTML('beforebegin', alertHtml);
}
</script>
@endpush

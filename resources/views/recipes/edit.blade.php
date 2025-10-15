@extends('layouts.admin')

@section('title', 'Edit Resep - ' . $recipe->name . ' - Chicking BJM')

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
          <a href="{{ route('recipes.index') }}">Resep</a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ route('recipes.show', $recipe->slug) }}">{{ $recipe->name }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Resep: {{ $recipe->name }}</h5>
        <small class="text-muted float-end">Form edit resep makanan</small>
      </div>
      <div class="card-body">
        <form action="{{ route('recipes.update', $recipe) }}" method="POST" enctype="multipart/form-data" id="recipeForm">
          @csrf
          @method('PUT')
          
          <!-- Recipe Name -->
          <div class="mb-3">
            <label class="form-label" for="name">Nama Resep <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-restaurant"></i></span>
              <input
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="name"
                name="name"
                placeholder="Contoh: Ayam Goreng Tepung Crispy"
                value="{{ old('name', $recipe->name) }}"
                required
              />
            </div>
            @error('name')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Nama resep maksimal 255 karakter</div>
            @enderror
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label class="form-label" for="description">Deskripsi Resep</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-text"></i></span>
              <textarea
                class="form-control @error('description') is-invalid @enderror"
                id="description"
                name="description"
                rows="3"
                placeholder="Deskripsi singkat tentang resep ini..."
              >{{ old('description', $recipe->description) }}</textarea>
            </div>
            @error('description')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Deskripsi singkat untuk menarik pembaca</div>
            @enderror
          </div>

          <!-- Current Image Display -->
          @if($recipe->image)
          <div class="mb-3">
            <label class="form-label">Gambar Saat Ini</label>
            <div class="current-image-container">
              <img src="{{ asset('storage/' . $recipe->image) }}" alt="{{ $recipe->name }}" class="img-thumbnail" style="max-width: 200px; height: 150px; object-fit: cover;">
              <div class="mt-2">
                <small class="text-muted">Gambar saat ini. Upload gambar baru untuk menggantinya.</small>
              </div>
            </div>
          </div>
          @endif

          <!-- Image Upload -->
          <div class="mb-3">
            <label class="form-label" for="image">{{ $recipe->image ? 'Ganti Gambar Makanan' : 'Gambar Makanan' }}</label>
            <div class="input-group">
              <input
                type="file"
                class="form-control @error('image') is-invalid @enderror"
                id="image"
                name="image"
                accept="image/jpeg,image/png,image/jpg,image/gif"
              />
              <label class="input-group-text" for="image">
                <i class="bx bx-image"></i>
              </label>
            </div>
            @error('image')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Format: JPEG, PNG, JPG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah gambar.</div>
            @enderror
            
            <!-- Image Preview -->
            <div id="imagePreview" class="mt-3" style="display: none;">
              <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; height: 150px; object-fit: cover;">
              <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeImage()">
                <i class="bx bx-trash"></i> Hapus
              </button>
            </div>
          </div>

          <!-- Time and Servings Row -->
          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label" for="prep_time">Waktu Persiapan (menit) <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-time"></i></span>
                <input
                  type="number"
                  class="form-control @error('prep_time') is-invalid @enderror"
                  id="prep_time"
                  name="prep_time"
                  placeholder="30"
                  value="{{ old('prep_time', $recipe->prep_time) }}"
                  min="0"
                  required
                />
              </div>
              @error('prep_time')
                <div class="form-text text-danger">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label" for="cook_time">Waktu Memasak (menit) <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-timer"></i></span>
                <input
                  type="number"
                  class="form-control @error('cook_time') is-invalid @enderror"
                  id="cook_time"
                  name="cook_time"
                  placeholder="20"
                  value="{{ old('cook_time', $recipe->cook_time) }}"
                  min="0"
                  required
                />
              </div>
              @error('cook_time')
                <div class="form-text text-danger">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label" for="servings">Porsi <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-group"></i></span>
                <input
                  type="number"
                  class="form-control @error('servings') is-invalid @enderror"
                  id="servings"
                  name="servings"
                  placeholder="4"
                  value="{{ old('servings', $recipe->servings) }}"
                  min="1"
                  required
                />
              </div>
              @error('servings')
                <div class="form-text text-danger">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label" for="difficulty">Tingkat Kesulitan <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-tachometer"></i></span>
                <select class="form-select @error('difficulty') is-invalid @enderror" id="difficulty" name="difficulty" required>
                  <option value="">Pilih Tingkat</option>
                  <option value="mudah" {{ old('difficulty', $recipe->difficulty) == 'mudah' ? 'selected' : '' }}>Mudah</option>
                  <option value="sedang" {{ old('difficulty', $recipe->difficulty) == 'sedang' ? 'selected' : '' }}>Sedang</option>
                  <option value="sulit" {{ old('difficulty', $recipe->difficulty) == 'sulit' ? 'selected' : '' }}>Sulit</option>
                </select>
              </div>
              @error('difficulty')
                <div class="form-text text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Ingredients Section -->
          <div class="mb-4">
            <label class="form-label">Bahan-bahan <span class="text-danger">*</span></label>
            <div id="ingredientsContainer">
              @php
                $ingredients = old('ingredients', $recipe->ingredients);
              @endphp
              @if($ingredients)
                @foreach($ingredients as $index => $ingredient)
                <div class="ingredient-item mb-2">
                  <div class="input-group">
                    <span class="input-group-text">{{ $index + 1 }}</span>
                    <input
                      type="text"
                      class="form-control @error('ingredients.'.$index) is-invalid @enderror"
                      name="ingredients[]"
                      placeholder="Contoh: 1 ekor ayam (potong 8 bagian)"
                      value="{{ $ingredient }}"
                      required
                    />
                    <button type="button" class="btn btn-outline-danger" onclick="removeIngredient(this)">
                      <i class="bx bx-trash"></i>
                    </button>
                  </div>
                  @error('ingredients.'.$index)
                    <div class="form-text text-danger">{{ $message }}</div>
                  @enderror
                </div>
                @endforeach
              @else
                <div class="ingredient-item mb-2">
                  <div class="input-group">
                    <span class="input-group-text">1</span>
                    <input
                      type="text"
                      class="form-control"
                      name="ingredients[]"
                      placeholder="Contoh: 1 ekor ayam (potong 8 bagian)"
                      required
                    />
                    <button type="button" class="btn btn-outline-danger" onclick="removeIngredient(this)">
                      <i class="bx bx-trash"></i>
                    </button>
                  </div>
                </div>
              @endif
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addIngredient()">
              <i class="bx bx-plus me-1"></i>
              Tambah Bahan
            </button>
            @error('ingredients')
              <div class="form-text text-danger">{{ $message }}</div>
            @endif
          </div>

          <!-- Instructions Section -->
          <div class="mb-4">
            <label class="form-label">Cara Membuat <span class="text-danger">*</span></label>
            <div id="instructionsContainer">
              @php
                $instructions = old('instructions', $recipe->instructions);
              @endphp
              @if($instructions)
                @foreach($instructions as $index => $instruction)
                <div class="instruction-item mb-2">
                  <div class="input-group">
                    <span class="input-group-text">{{ $index + 1 }}</span>
                    <textarea
                      class="form-control @error('instructions.'.$index) is-invalid @enderror"
                      name="instructions[]"
                      rows="2"
                      placeholder="Langkah {{ $index + 1 }}: Jelaskan cara membuat secara detail..."
                      required
                    >{{ $instruction }}</textarea>
                    <button type="button" class="btn btn-outline-danger" onclick="removeInstruction(this)">
                      <i class="bx bx-trash"></i>
                    </button>
                  </div>
                  @error('instructions.'.$index)
                    <div class="form-text text-danger">{{ $message }}</div>
                  @enderror
                </div>
                @endforeach
              @else
                <div class="instruction-item mb-2">
                  <div class="input-group">
                    <span class="input-group-text">1</span>
                    <textarea
                      class="form-control"
                      name="instructions[]"
                      rows="2"
                      placeholder="Langkah 1: Jelaskan cara membuat secara detail..."
                      required
                    ></textarea>
                    <button type="button" class="btn btn-outline-danger" onclick="removeInstruction(this)">
                      <i class="bx bx-trash"></i>
                    </button>
                  </div>
                </div>
              @endif
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addInstruction()">
              <i class="bx bx-plus me-1"></i>
              Tambah Langkah
            </button>
            @error('instructions')
              <div class="form-text text-danger">{{ $message }}</div>
            @endif
          </div>

          <!-- Notes -->
          <div class="mb-3">
            <label class="form-label" for="notes">Catatan Tambahan</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-note"></i></span>
              <textarea
                class="form-control @error('notes') is-invalid @enderror"
                id="notes"
                name="notes"
                rows="3"
                placeholder="Tips dan trik untuk resep ini..."
              >{{ old('notes', $recipe->notes) }}</textarea>
            </div>
            @error('notes')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Tips atau catatan penting untuk resep</div>
            @enderror
          </div>

          <!-- Status -->
          <div class="mb-3">
            <label class="form-label" for="status">Status Publikasi <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-show"></i></span>
              <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="published" {{ old('status', $recipe->status) == 'published' ? 'selected' : '' }}>Publikasikan</option>
                <option value="draft" {{ old('status', $recipe->status) == 'draft' ? 'selected' : '' }}>Simpan sebagai Draft</option>
              </select>
            </div>
            @error('status')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Draft tidak akan tampil di halaman publik</div>
            @enderror
          </div>

          <!-- Action Buttons -->
          <div class="d-flex justify-content-between">
            <div class="d-flex gap-2">
              <a href="{{ route('recipes.show', $recipe->slug) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>
                Kembali
              </a>
              <a href="{{ route('recipes.index') }}" class="btn btn-outline-info">
                <i class="bx bx-list-ul me-1"></i>
                Daftar Resep
              </a>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-warning" onclick="resetToOriginal()">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
              <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="bx bx-save me-1"></i>
                Update Resep
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
          Preview Resep
        </h5>
      </div>
      <div class="card-body">
        <div class="preview-content">
          <!-- Preview Image -->
          <div class="preview-image-container mb-3">
            <div id="previewImageCard" class="text-center p-4 border rounded" style="background: #f8f9fa;">
              @if($recipe->image)
                <img src="{{ asset('storage/' . $recipe->image) }}" alt="Preview" style="width: 100%; height: 120px; object-fit: cover; border-radius: 6px;">
              @else
                <i class="bx bx-image" style="font-size: 48px; color: #ddd;"></i>
                <p class="text-muted mt-2 mb-0">Belum ada gambar</p>
              @endif
            </div>
          </div>
          
          <!-- Preview Info -->
          <div class="d-flex align-items-center mb-3">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-restaurant"></i>
              </span>
            </div>
            <div class="flex-grow-1">
              <h6 class="mb-0" id="preview-name">{{ $recipe->name }}</h6>
              <small class="text-muted" id="preview-description">{{ $recipe->description ?: 'Deskripsi resep' }}</small>
            </div>
          </div>
          
          <div class="border rounded p-3">
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Persiapan:</small>
                <small class="fw-semibold" id="preview-prep">{{ $recipe->prep_time }} menit</small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Memasak:</small>
                <small class="fw-semibold" id="preview-cook">{{ $recipe->cook_time }} menit</small>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Porsi:</small>
                <small class="fw-semibold text-primary" id="preview-servings">{{ $recipe->servings }} orang</small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Kesulitan:</small>
                <span class="badge bg-{{ $recipe->difficulty_badge }}" id="preview-difficulty">{{ ucfirst($recipe->difficulty) }}</span>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-12">
                <small class="text-muted d-block">Total Waktu:</small>
                <small class="fw-semibold text-success" id="preview-total">{{ $recipe->total_time }} menit</small>
              </div>
            </div>
            <div class="text-center mt-3">
              <span class="badge bg-{{ $recipe->status == 'published' ? 'success' : 'warning' }}" id="preview-status">
                {{ $recipe->status == 'published' ? 'Dipublikasikan' : 'Draft' }}
              </span>
            </div>
          </div>
          
          <!-- Ingredients Count -->
          <div class="mt-3">
            <small class="text-muted d-block">Bahan-bahan:</small>
            <small class="fw-semibold" id="preview-ingredients-count">{{ count($recipe->ingredients) }} bahan</small>
          </div>
          
          <!-- Instructions Count -->
          <div class="mt-2">
            <small class="text-muted d-block">Langkah-langkah:</small>
            <small class="fw-semibold" id="preview-instructions-count">{{ count($recipe->instructions) }} langkah</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Recipe Info Card -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-data me-2"></i>
          Informasi Resep
        </h6>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">ID Resep:</span>
          <span class="badge bg-secondary">#{{ $recipe->id }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Slug:</span>
          <small class="text-primary">{{ $recipe->slug }}</small>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Dibuat:</span>
          <small>{{ $recipe->created_at->format('d M Y') }}</small>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Diupdate:</span>
          <small>{{ $recipe->updated_at->format('d M Y') }}</small>
        </div>
      </div>
    </div>

    <!-- Actions Card -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-cog me-2"></i>
          Aksi Lainnya
        </h6>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('recipes.show', $recipe->slug) }}" class="btn btn-outline-info btn-sm">
            <i class="bx bx-show me-1"></i>
            Lihat Resep
          </a>
          <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete()">
            <i class="bx bx-trash me-1"></i>
            Hapus Resep
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Resep</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus resep <strong>"{{ $recipe->name }}"</strong>?</p>
        <div class="alert alert-warning">
          <i class="bx bx-error-circle me-2"></i>
          <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Resep dan gambar yang terkait akan dihapus permanen.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <form action="{{ route('recipes.destroy', $recipe) }}" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Ya, Hapus Resep</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  let ingredientCount = document.querySelectorAll('.ingredient-item').length;
  let instructionCount = document.querySelectorAll('.instruction-item').length;

  // Store original values for reset
  const originalData = {
    name: '{{ $recipe->name }}',
    description: '{{ $recipe->description }}',
    prep_time: {{ $recipe->prep_time }},
    cook_time: {{ $recipe->cook_time }},
    servings: {{ $recipe->servings }},
    difficulty: '{{ $recipe->difficulty }}',
    status: '{{ $recipe->status }}',
    notes: '{{ $recipe->notes }}',
    ingredients: @json($recipe->ingredients),
    instructions: @json($recipe->instructions)
  };

  // Add ingredient function
  window.addIngredient = function() {
    ingredientCount++;
    const container = document.getElementById('ingredientsContainer');
    const div = document.createElement('div');
    div.className = 'ingredient-item mb-2';
    div.innerHTML = `
      <div class="input-group">
        <span class="input-group-text">${ingredientCount}</span>
        <input
          type="text"
          class="form-control"
          name="ingredients[]"
          placeholder="Contoh: 1 ekor ayam (potong 8 bagian)"
          required
        />
        <button type="button" class="btn btn-outline-danger" onclick="removeIngredient(this)">
          <i class="bx bx-trash"></i>
        </button>
      </div>
    `;
    container.appendChild(div);
    updatePreview();
    updateIngredientNumbers();
  };

  // Remove ingredient function
  window.removeIngredient = function(button) {
    if (document.querySelectorAll('.ingredient-item').length > 1) {
      button.closest('.ingredient-item').remove();
      updateIngredientNumbers();
      updatePreview();
    } else {
      alert('Minimal harus ada 1 bahan');
    }
  };

  // Add instruction function
  window.addInstruction = function() {
    instructionCount++;
    const container = document.getElementById('instructionsContainer');
    const div = document.createElement('div');
    div.className = 'instruction-item mb-2';
    div.innerHTML = `
      <div class="input-group">
        <span class="input-group-text">${instructionCount}</span>
        <textarea
          class="form-control"
          name="instructions[]"
          rows="2"
          placeholder="Langkah ${instructionCount}: Jelaskan cara membuat secara detail..."
          required
        ></textarea>
        <button type="button" class="btn btn-outline-danger" onclick="removeInstruction(this)">
          <i class="bx bx-trash"></i>
        </button>
      </div>
    `;
    container.appendChild(div);
    updatePreview();
    updateInstructionNumbers();
  };

  // Remove instruction function
  window.removeInstruction = function(button) {
    if (document.querySelectorAll('.instruction-item').length > 1) {
      button.closest('.instruction-item').remove();
      updateInstructionNumbers();
      updatePreview();
    } else {
      alert('Minimal harus ada 1 langkah');
    }
  };

  // Update ingredient numbers
  function updateIngredientNumbers() {
    const items = document.querySelectorAll('.ingredient-item');
    items.forEach((item, index) => {
      const span = item.querySelector('.input-group-text');
      span.textContent = index + 1;
    });
    ingredientCount = items.length;
  }

  // Update instruction numbers
  function updateInstructionNumbers() {
    const items = document.querySelectorAll('.instruction-item');
    items.forEach((item, index) => {
      const span = item.querySelector('.input-group-text');
      span.textContent = index + 1;
      const textarea = item.querySelector('textarea');
      const placeholder = textarea.getAttribute('placeholder');
      if (placeholder.includes('Langkah')) {
        textarea.setAttribute('placeholder', `Langkah ${index + 1}: Jelaskan cara membuat secara detail...`);
      }
    });
    instructionCount = items.length;
  }

  // Image preview
  const imageInput = document.getElementById('image');
  const imagePreview = document.getElementById('imagePreview');
  const previewImg = document.getElementById('previewImg');
  const previewImageCard = document.getElementById('previewImageCard');

  imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        previewImg.src = e.target.result;
        imagePreview.style.display = 'block';
        
        // Update preview card
        previewImageCard.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 120px; object-fit: cover; border-radius: 6px;">`;
      };
      reader.readAsDataURL(file);
    }
  });

  // Remove image
  window.removeImage = function() {
    imageInput.value = '';
    imagePreview.style.display = 'none';
    @if($recipe->image)
      previewImageCard.innerHTML = `<img src="{{ asset('storage/' . $recipe->image) }}" alt="Current" style="width: 100%; height: 120px; object-fit: cover; border-radius: 6px;">`;
    @else
      previewImageCard.innerHTML = `
        <i class="bx bx-image" style="font-size: 48px; color: #ddd;"></i>
        <p class="text-muted mt-2 mb-0">Belum ada gambar</p>
      `;
    @endif
  };

  // Reset to original values
  window.resetToOriginal = function() {
    if (confirm('Apakah Anda yakin ingin mengembalikan ke data asli? Semua perubahan akan hilang.')) {
      // Reset form fields
      document.getElementById('name').value = originalData.name;
      document.getElementById('description').value = originalData.description;
      document.getElementById('prep_time').value = originalData.prep_time;
      document.getElementById('cook_time').value = originalData.cook_time;
      document.getElementById('servings').value = originalData.servings;
      document.getElementById('difficulty').value = originalData.difficulty;
      document.getElementById('status').value = originalData.status;
      document.getElementById('notes').value = originalData.notes;
      document.getElementById('image').value = '';
      
      // Reset image preview
      if (imagePreview) imagePreview.style.display = 'none';
      
      // Reset ingredients
      const ingredientsContainer = document.getElementById('ingredientsContainer');
      ingredientsContainer.innerHTML = '';
      originalData.ingredients.forEach((ingredient, index) => {
        const div = document.createElement('div');
        div.className = 'ingredient-item mb-2';
        div.innerHTML = `
          <div class="input-group">
            <span class="input-group-text">${index + 1}</span>
            <input
              type="text"
              class="form-control"
              name="ingredients[]"
              placeholder="Contoh: 1 ekor ayam (potong 8 bagian)"
              value="${ingredient}"
              required
            />
            <button type="button" class="btn btn-outline-danger" onclick="removeIngredient(this)">
              <i class="bx bx-trash"></i>
            </button>
          </div>
        `;
        ingredientsContainer.appendChild(div);
      });
      
      // Reset instructions
      const instructionsContainer = document.getElementById('instructionsContainer');
      instructionsContainer.innerHTML = '';
      originalData.instructions.forEach((instruction, index) => {
        const div = document.createElement('div');
        div.className = 'instruction-item mb-2';
        div.innerHTML = `
          <div class="input-group">
            <span class="input-group-text">${index + 1}</span>
            <textarea
              class="form-control"
              name="instructions[]"
              rows="2"
              placeholder="Langkah ${index + 1}: Jelaskan cara membuat secara detail..."
              required
            >${instruction}</textarea>
            <button type="button" class="btn btn-outline-danger" onclick="removeInstruction(this)">
              <i class="bx bx-trash"></i>
            </button>
          </div>
        `;
        instructionsContainer.appendChild(div);
      });
      
      updatePreview();
      updateIngredientNumbers();
      updateInstructionNumbers();
    }
  };

  // Update preview
  function updatePreview() {
    const name = document.getElementById('name').value || 'Nama Resep';
    const description = document.getElementById('description').value || 'Deskripsi resep';
    const prepTime = parseInt(document.getElementById('prep_time').value) || 30;
    const cookTime = parseInt(document.getElementById('cook_time').value) || 20;
    const servings = parseInt(document.getElementById('servings').value) || 4;
    const difficulty = document.getElementById('difficulty').value;
    const status = document.getElementById('status').value;
    
    const ingredientsCount = document.querySelectorAll('.ingredient-item').length;
    const instructionsCount = document.querySelectorAll('.instruction-item').length;
    
    document.getElementById('preview-name').textContent = name;
    document.getElementById('preview-description').textContent = description;
    document.getElementById('preview-prep').textContent = prepTime + ' menit';
    document.getElementById('preview-cook').textContent = cookTime + ' menit';
    document.getElementById('preview-servings').textContent = servings + ' orang';
    document.getElementById('preview-total').textContent = (prepTime + cookTime) + ' menit';
    document.getElementById('preview-ingredients-count').textContent = ingredientsCount + ' bahan';
    document.getElementById('preview-instructions-count').textContent = instructionsCount + ' langkah';
    
    // Difficulty badge
    const difficultyBadge = document.getElementById('preview-difficulty');
    if (difficulty) {
      const difficultyColors = {
        'mudah': 'bg-success',
        'sedang': 'bg-warning', 
        'sulit': 'bg-danger'
      };
      difficultyBadge.textContent = difficulty.charAt(0).toUpperCase() + difficulty.slice(1);
      difficultyBadge.className = `badge ${difficultyColors[difficulty]}`;
    } else {
      difficultyBadge.textContent = 'Belum dipilih';
      difficultyBadge.className = 'badge bg-secondary';
    }
    
    // Status badge
    const statusBadge = document.getElementById('preview-status');
    if (status === 'published') {
      statusBadge.textContent = 'Dipublikasikan';
      statusBadge.className = 'badge bg-success';
    } else {
      statusBadge.textContent = 'Draft';
      statusBadge.className = 'badge bg-warning';
    }
  }

  // Confirm delete
  window.confirmDelete = function() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
  };

  // Event listeners for preview
  document.getElementById('name').addEventListener('input', updatePreview);
  document.getElementById('description').addEventListener('input', updatePreview);
  document.getElementById('prep_time').addEventListener('input', updatePreview);
  document.getElementById('cook_time').addEventListener('input', updatePreview);
  document.getElementById('servings').addEventListener('input', updatePreview);
  document.getElementById('difficulty').addEventListener('change', updatePreview);
  document.getElementById('status').addEventListener('change', updatePreview);

  // Form validation
  const form = document.getElementById('recipeForm');
  form.addEventListener('submit', function(e) {
    const ingredients = document.querySelectorAll('input[name="ingredients[]"]');
    const instructions = document.querySelectorAll('textarea[name="instructions[]"]');
    
    let hasEmptyIngredient = false;
    let hasEmptyInstruction = false;
    
    ingredients.forEach(input => {
      if (!input.value.trim()) {
        hasEmptyIngredient = true;
        input.classList.add('is-invalid');
      } else {
        input.classList.remove('is-invalid');
      }
    });
    
    instructions.forEach(textarea => {
      if (!textarea.value.trim()) {
        hasEmptyInstruction = true;
        textarea.classList.add('is-invalid');
      } else {
        textarea.classList.remove('is-invalid');
      }
    });
    
    if (hasEmptyIngredient) {
      e.preventDefault();
      alert('Mohon lengkapi semua bahan-bahan');
      return false;
    }
    
    if (hasEmptyInstruction) {
      e.preventDefault();
      alert('Mohon lengkapi semua langkah-langkah');
      return false;
    }
    
    // Show loading
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Mengupdate...';
    submitBtn.disabled = true;
  });

  // Initialize
  updatePreview();
  updateIngredientNumbers();
  updateInstructionNumbers();
});
</script>
@endpush

@push('styles')
<style>
.ingredient-item, .instruction-item {
  position: relative;
}

.ingredient-item .input-group-text,
.instruction-item .input-group-text {
  background-color: #696cff;
  color: white;
  font-weight: 600;
  min-width: 40px;
  justify-content: center;
}

.preview-image-container {
  height: 120px;
}

.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  font-size: 18px;
}

.form-control:focus {
  border-color: #696cff;
  box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
}

.current-image-container img {
  border: 2px solid #e7e7ff;
}

@media (max-width: 768px) {
  .ingredient-item .input-group,
  .instruction-item .input-group {
    flex-wrap: nowrap;
  }
  
  .ingredient-item .btn,
  .instruction-item .btn {
    min-width: 40px;
  }
  
  .d-flex.gap-2 {
    flex-direction: column;
    gap: 0.5rem !important;
  }
}
</style>
@endpush
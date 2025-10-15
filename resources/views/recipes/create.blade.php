@extends('layouts.admin')

@section('title', 'Tambah Resep - Chicking BJM')

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
        <li class="breadcrumb-item active" aria-current="page">Tambah Resep</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tambah Resep Baru</h5>
        <small class="text-muted float-end">Form input resep makanan</small>
      </div>
      <div class="card-body">
        <form action="{{ route('recipes.store') }}" method="POST" enctype="multipart/form-data" id="recipeForm">
          @csrf
          
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
                value="{{ old('name') }}"
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
              >{{ old('description') }}</textarea>
            </div>
            @error('description')
              <div class="form-text text-danger">{{ $message }}</div>
            @else
              <div class="form-text">Deskripsi singkat untuk menarik pembaca</div>
            @enderror
          </div>

          <!-- Image Upload -->
          <div class="mb-3">
            <label class="form-label" for="image">Gambar Makanan</label>
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
              <div class="form-text">Format: JPEG, PNG, JPG, GIF. Maksimal 2MB</div>
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
                  value="{{ old('prep_time', 30) }}"
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
                  value="{{ old('cook_time', 20) }}"
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
                  value="{{ old('servings', 4) }}"
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
                  <option value="mudah" {{ old('difficulty') == 'mudah' ? 'selected' : '' }}>Mudah</option>
                  <option value="sedang" {{ old('difficulty') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                  <option value="sulit" {{ old('difficulty') == 'sulit' ? 'selected' : '' }}>Sulit</option>
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
              @if(old('ingredients'))
                @foreach(old('ingredients') as $index => $ingredient)
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
              @if(old('instructions'))
                @foreach(old('instructions') as $index => $instruction)
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
              >{{ old('notes') }}</textarea>
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
                <option value="published" {{ old('status') == 'published' ? 'selected' : 'selected' }}>Publikasikan</option>
                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Simpan sebagai Draft</option>
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
            <a href="{{ route('recipes.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
            <div>
              <button type="reset" class="btn btn-outline-warning me-2">
                <i class="bx bx-reset me-1"></i>
                Reset
              </button>
              <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="bx bx-save me-1"></i>
                Simpan Resep
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
              <i class="bx bx-image" style="font-size: 48px; color: #ddd;"></i>
              <p class="text-muted mt-2 mb-0">Belum ada gambar</p>
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
              <h6 class="mb-0" id="preview-name">Nama Resep</h6>
              <small class="text-muted" id="preview-description">Deskripsi resep</small>
            </div>
          </div>
          
          <div class="border rounded p-3">
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Persiapan:</small>
                <small class="fw-semibold" id="preview-prep">30 menit</small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Memasak:</small>
                <small class="fw-semibold" id="preview-cook">20 menit</small>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-6">
                <small class="text-muted d-block">Porsi:</small>
                <small class="fw-semibold text-primary" id="preview-servings">4 orang</small>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Kesulitan:</small>
                <span class="badge bg-secondary" id="preview-difficulty">Belum dipilih</span>
              </div>
            </div>
            <div class="row mb-2">
              <div class="col-12">
                <small class="text-muted d-block">Total Waktu:</small>
                <small class="fw-semibold text-success" id="preview-total">50 menit</small>
              </div>
            </div>
            <div class="text-center mt-3">
              <span class="badge bg-info" id="preview-status">Akan Dipublikasikan</span>
            </div>
          </div>
          
          <!-- Ingredients Count -->
          <div class="mt-3">
            <small class="text-muted d-block">Bahan-bahan:</small>
            <small class="fw-semibold" id="preview-ingredients-count">0 bahan</small>
          </div>
          
          <!-- Instructions Count -->
          <div class="mt-2">
            <small class="text-muted d-block">Langkah-langkah:</small>
            <small class="fw-semibold" id="preview-instructions-count">0 langkah</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Tips Card -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-bulb me-2"></i>
          Tips Membuat Resep
        </h6>
      </div>
      <div class="card-body">
        <div class="alert alert-info" role="alert">
          <h6 class="alert-heading">Tips Resep Berkualitas:</h6>
          <ul class="mb-0 small">
            <li>Gunakan nama yang menarik dan deskriptif</li>
            <li>Upload foto makanan yang menggugah selera</li>
            <li>Tulis bahan dengan ukuran yang jelas</li>
            <li>Buat langkah-langkah yang mudah diikuti</li>
            <li>Tambahkan tips khusus di catatan</li>
            <li>Sesuaikan tingkat kesulitan dengan resep</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Stats Card -->
    <div class="card mt-4">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-chart me-2"></i>
          Statistik Resep
        </h6>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Total Resep:</span>
          <span class="badge bg-primary">{{ App\Models\Recipe::count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Resep Mudah:</span>
          <span class="badge bg-success">{{ App\Models\Recipe::where('difficulty', 'mudah')->count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted">Resep Sedang:</span>
          <span class="badge bg-warning">{{ App\Models\Recipe::where('difficulty', 'sedang')->count() }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">Resep Sulit:</span>
          <span class="badge bg-danger">{{ App\Models\Recipe::where('difficulty', 'sulit')->count() }}</span>
        </div>
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
    previewImageCard.innerHTML = `
      <i class="bx bx-image" style="font-size: 48px; color: #ddd;"></i>
      <p class="text-muted mt-2 mb-0">Belum ada gambar</p>
    `;
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
    
    const ingredientsCount = document.querySelectorAll('.ingredient-item input[value!=""]').length;
    const instructionsCount = document.querySelectorAll('.instruction-item textarea:not([value=""])').length;
    
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
      statusBadge.textContent = 'Akan Dipublikasikan';
      statusBadge.className = 'badge bg-success';
    } else {
      statusBadge.textContent = 'Simpan sebagai Draft';
      statusBadge.className = 'badge bg-warning';
    }
  }

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
    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Menyimpan...';
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

@media (max-width: 768px) {
  .ingredient-item .input-group,
  .instruction-item .input-group {
    flex-wrap: nowrap;
  }
  
  .ingredient-item .btn,
  .instruction-item .btn {
    min-width: 40px;
  }
}
</style>
@endpush
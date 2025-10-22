@extends('layouts.admin')

@section('title', $recipe->name . ' - Chicking BJM')

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
          <a href="{{ route('recipes.index') }}">Recipes</a>
        </li>
        <li class="breadcrumb-item active">{{ $recipe->name }}</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Recipe Header -->
<div class="row mb-4">
  <div class="col-lg-8 col-md-7">
    <div class="recipe-hero">
      @if($recipe->image)
        <img src="{{ Storage::url($recipe->image) }}" alt="{{ $recipe->name }}" class="recipe-main-image">
      @else
        <div class="recipe-placeholder">
          <i class="bx bx-image"></i>
          <span>No Image</span>
        </div>
      @endif
      <div class="recipe-hero-overlay">
        <div class="recipe-hero-content">
          <h1>{{ $recipe->name }}</h1>
          <p class="mb-0">{{ $recipe->description }}</p>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-4 col-md-5">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-3">
          <i class="bx bx-info-circle me-2"></i>
          Info Resep
        </h5>
        <div class="recipe-stats">
          <div class="stat-item">
            <div class="stat-icon">
              <i class="bx bx-time"></i>
            </div>
            <div>
              <div class="stat-label">Prep Time</div>
              <div class="stat-value">{{ $recipe->prep_time }} menit</div>
            </div>
          </div>
          <div class="stat-item">
            <div class="stat-icon">
              <i class="bx bx-timer"></i>
            </div>
            <div>
              <div class="stat-label">Cook Time</div>
              <div class="stat-value">{{ $recipe->cook_time }} menit</div>
            </div>
          </div>
          <div class="stat-item">
            <div class="stat-icon">
              <i class="bx bx-group"></i>
            </div>
            <div>
              <div class="stat-label">Servings</div>
              <div class="stat-value">{{ $recipe->servings }} porsi</div>
            </div>
          </div>
          <div class="stat-item">
            <div class="stat-icon">
              <i class="bx bx-star"></i>
            </div>
            <div>
              <div class="stat-label">Difficulty</div>
              <div class="stat-value">
                <span class="badge bg-{{ $recipe->difficulty_badge }}">
                  {{ ucfirst($recipe->difficulty) }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="d-grid gap-2 mt-3">
          <button class="btn btn-primary" onclick="printRecipe()">
            <i class="bx bx-printer me-1"></i>
            Print Recipe
          </button>
          <a href="{{ route('recipes.edit', $recipe) }}" class="btn btn-outline-secondary">
            <i class="bx bx-edit me-1"></i>
            Edit Recipe
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recipe Content -->
<div class="row">
  <!-- Ingredients -->
  <div class="col-lg-4 col-md-12 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-list-ul me-2"></i>
          Bahan-bahan
        </h5>
      </div>
      <div class="card-body">
        @php
          $ingredients = $recipe->ingredients ?? [];
          if (is_string($ingredients)) {
              $ingredients = json_decode($ingredients, true) ?? [];
          }
        @endphp

        @if(!empty($ingredients) && is_array($ingredients))
          <div class="ingredients-list">
            @foreach($ingredients as $ingredient)
              <div class="ingredient-item">
                <label class="form-check-label">
                  <input type="checkbox" class="form-check-input me-2">
                  {{ $ingredient }}
                </label>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-muted text-center py-4">
            <i class="bx bx-info-circle" style="font-size: 2rem;"></i>
            <p class="mt-2">Belum ada bahan yang ditambahkan</p>
          </div>
        @endif
      </div>
    </div>
  </div>
  
  <!-- Instructions -->
  <div class="col-lg-8 col-md-12 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-receipt me-2"></i>
          Cara Membuat
        </h5>
      </div>
      <div class="card-body">
        @php
          $instructions = $recipe->instructions ?? [];
          if (is_string($instructions)) {
              $instructions = json_decode($instructions, true) ?? [];
          }
        @endphp

        @if(!empty($instructions) && is_array($instructions))
          <div class="instructions-list">
            @foreach($instructions as $index => $instruction)
              <div class="instruction-step">
                <div class="step-number">
                  {{ $index + 1 }}
                </div>
                <div class="step-content">
                  <p class="mb-0">{{ $instruction }}</p>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-muted text-center py-4">
            <i class="bx bx-info-circle" style="font-size: 2rem;"></i>
            <p class="mt-2">Belum ada instruksi yang ditambahkan</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Notes -->
@if($recipe->notes)
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-note me-2"></i>
          Catatan
        </h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $recipe->notes }}</p>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Related Recipes -->
@if($relatedRecipes->count() > 0)
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bx bx-food-menu me-2"></i>
          Resep Terkait
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          @foreach($relatedRecipes as $related)
            <div class="col-md-4 mb-3">
              <div class="recipe-card-small">
                <a href="{{ route('recipes.show', $related->slug) }}" class="text-decoration-none">
                  @if($related->image)
                    <img src="{{ Storage::url($related->image) }}" alt="{{ $related->name }}" class="recipe-image-small">
                  @else
                    <div class="placeholder-small">
                      <i class="bx bx-image"></i>
                    </div>
                  @endif
                  <div class="p-3">
                    <h6 class="mb-1">{{ $related->name }}</h6>
                    <small class="text-muted">
                      <i class="bx bx-time me-1"></i>
                      {{ $related->total_time }} menit
                    </small>
                  </div>
                </a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function printRecipe() {
  window.print();
}

// Check off ingredients
document.addEventListener('DOMContentLoaded', function() {
  const checkboxes = document.querySelectorAll('.ingredient-item input[type="checkbox"]');
  
  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      const label = this.parentElement;
      if (this.checked) {
        label.style.textDecoration = 'line-through';
        label.style.opacity = '0.6';
      } else {
        label.style.textDecoration = 'none';
        label.style.opacity = '1';
      }
    });
  });
});
</script>
@endpush

@push('styles')
<style>
.recipe-hero {
  position: relative;
  height: 400px;
  border-radius: 0.75rem;
  overflow: hidden;
}

.recipe-main-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.recipe-placeholder {
  width: 100%;
  height: 100%;
  background: #f8f9fa;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #6c757d;
}

.recipe-placeholder i {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.recipe-hero-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(transparent, rgba(0,0,0,0.8));
  color: white;
  padding: 2rem;
}

.recipe-hero-content h1 {
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.recipe-stats {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(105, 108, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon i {
  color: #696cff;
  font-size: 1.2rem;
}

.stat-label {
  font-size: 0.875rem;
  color: #6c757d;
}

.stat-value {
  font-weight: 600;
}

.ingredient-section {
  border-left: 3px solid #696cff;
  padding-left: 1rem;
}

.ingredients-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.ingredient-item {
  padding: 0.5rem;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
}

.ingredient-item:hover {
  background-color: #f8f9fa;
}

.ingredient-item input:checked + label {
  text-decoration: line-through;
  opacity: 0.6;
}

.instructions-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.instruction-step {
  display: flex;
  gap: 1rem;
}

.step-number {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #696cff;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  flex-shrink: 0;
}

.step-content {
  flex: 1;
  padding-top: 0.25rem;
}

.recipe-card-small {
  border: 1px solid #dee2e6;
  border-radius: 0.5rem;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
  height: 100%;
}

.recipe-card-small:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.recipe-image-small {
  width: 100%;
  height: 150px;
  object-fit: cover;
}

.placeholder-small {
  width: 100%;
  height: 150px;
  background: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6c757d;
}

.placeholder-small i {
  font-size: 2rem;
}

@media print {
  .btn, .breadcrumb, .card-header {
    display: none !important;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
  }
}

@media (max-width: 768px) {
  .recipe-hero {
    height: 250px;
  }
  
  .recipe-hero-content h1 {
    font-size: 1.75rem;
  }
  
  .stat-item {
    font-size: 0.875rem;
  }
}
</style>
@endpush
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
          <a href="{{ route('recipes.index') }}">Menu Resep</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ $recipe->name }}</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Recipe Header -->
<div class="row mb-4">
  <div class="col-lg-8 col-md-7">
    <div class="card">
      <div class="recipe-hero">
        @if($recipe->image)
          <img src="{{ asset('storage/' . $recipe->image) }}" class="recipe-main-image" alt="{{ $recipe->name }}">
        @else
          <div class="recipe-placeholder d-flex align-items-center justify-content-center">
            <i class="bx bx-image" style="font-size: 96px; color: #ddd;"></i>
          </div>
        @endif
        <div class="recipe-hero-overlay">
          <div class="recipe-hero-content">
            <span class="badge bg-{{ $recipe->difficulty_badge }} mb-2 fs-6">
              {{ ucfirst($recipe->difficulty) }}
            </span>
            <h1 class="text-white mb-2">{{ $recipe->name }}</h1>
            <p class="text-white-50 mb-0">{{ $recipe->description }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-4 col-md-5">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title mb-4">
          <i class="bx bx-info-circle me-2"></i>
          Informasi Resep
        </h5>
        
        <!-- Recipe Stats -->
        <div class="recipe-stats">
          <div class="stat-item">
            <div class="stat-icon">
              <i class="bx bx-time"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Waktu Persiapan</div>
              <div class="stat-value">{{ $recipe->prep_time }} menit</div>
            </div>
          </div>
          
          <div class="stat-item">
            <div class="stat-icon">
              <i class="bx bx-timer"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Waktu Memasak</div>
              <div class="stat-value">{{ $recipe->cook_time }} menit</div>
            </div>
          </div>
          
          <div class="stat-item">
            <div class="stat-icon">
              <i class="bx bx-group"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Porsi</div>
              <div class="stat-value">{{ $recipe->servings }} orang</div>
            </div>
          </div>
          
          <div class="stat-item">
            <div class="stat-icon">
              <i class="bx bx-alarm"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Total Waktu</div>
              <div class="stat-value text-primary fw-bold">{{ $recipe->total_time }} menit</div>
            </div>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-grid gap-2 mt-4">
          <button class="btn btn-outline-primary" onclick="printRecipe()">
            <i class="bx bx-printer me-1"></i>
            Print Resep
          </button>
          <a href="{{ route('recipes.edit', $recipe) }}" class="btn btn-outline-secondary">
            <i class="bx bx-edit me-1"></i>
            Edit Resep
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
        <div class="ingredients-list">
          @foreach($recipe->ingredients as $ingredient)
          <div class="ingredient-item">
            <label class="form-check-label">
              <input type="checkbox" class="form-check-input me-2">
              {{ $ingredient }}
            </label>
          </div>
          @endforeach
        </div>
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
        <div class="instructions-list">
          @foreach($recipe->instructions as $index => $instruction)
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
          Catatan Tambahan
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info mb-0">
          <i class="bx bx-info-circle me-2"></i>
          {{ $recipe->notes }}
        </div>
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
          Resep Serupa
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          @foreach($relatedRecipes as $relatedRecipe)
          <div class="col-lg-4 col-md-6 mb-3">
            <div class="card recipe-card-small">
              <div class="row g-0">
                <div class="col-4">
                  @if($relatedRecipe->image)
                    <img src="{{ asset('storage/' . $relatedRecipe->image) }}" class="img-fluid rounded-start h-100" alt="{{ $relatedRecipe->name }}" style="object-fit: cover;">
                  @else
                    <div class="placeholder-small d-flex align-items-center justify-content-center h-100">
                      <i class="bx bx-image"></i>
                    </div>
                  @endif
                </div>
                <div class="col-8">
                  <div class="card-body py-2 px-3">
                    <h6 class="card-title mb-1">{{ $relatedRecipe->name }}</h6>
                    <small class="text-muted">
                      <i class="bx bx-time me-1"></i>
                      {{ $relatedRecipe->total_time }} menit
                    </small>
                    <div class="mt-2">
                      <a href="{{ route('recipes.show', $relatedRecipe->slug) }}" class="btn btn-sm btn-outline-primary">
                        Lihat
                      </a>
                    </div>
                  </div>
                </div>
              </div>
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
</script>
@endpush

@push('styles')
<style>
.recipe-hero {
  position: relative;
  height: 400px;
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
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.recipe-hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0) 100%);
  display: flex;
  align-items: flex-end;
  padding: 2rem;
}

.recipe-hero-content h1 {
  font-size: 2.5rem;
  font-weight: 700;
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
  padding: 0.75rem;
  background: rgba(105, 108, 255, 0.1);
  border-radius: 8px;
}

.stat-icon {
  width: 40px;
  height: 40px;
  background: rgba(105, 108, 255, 0.2);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.75rem;
}

.stat-icon i {
  font-size: 20px;
  color: #696cff;
}

.stat-label {
  font-size: 0.875rem;
  color: #6c757d;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-weight: 600;
  font-size: 1rem;
}

.ingredients-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.ingredient-item {
  padding: 0.5rem;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.ingredient-item:hover {
  background-color: rgba(105, 108, 255, 0.05);
}

.ingredient-item input:checked + label {
  text-decoration: line-through;
  color: #6c757d;
}

.instructions-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.instruction-step {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.step-number {
  width: 32px;
  height: 32px;
  background: #696cff;
  color: white;
  border-radius: 50%;
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
  height: 100px;
  transition: transform 0.2s ease;
}

.recipe-card-small:hover {
  transform: translateY(-2px);
}

.placeholder-small {
  background: #f8f9fa;
  color: #dee2e6;
}

@media print {
  .breadcrumb, .btn, .card-header .btn {
    display: none !important;
  }
  
  .recipe-hero {
    height: 200px;
  }
  
  .recipe-hero-content h1 {
    font-size: 1.5rem;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
  }
}

@media (max-width: 768px) {
  .recipe-hero {
    height: 300px;
  }
  
  .recipe-hero-content {
    padding: 1rem;
  }
  
  .recipe-hero-content h1 {
    font-size: 1.75rem;
  }
  
  .stat-item {
    padding: 0.5rem;
  }
  
  .stat-icon {
    width: 32px;
    height: 32px;
    margin-right: 0.5rem;
  }
}
</style>
@endpush
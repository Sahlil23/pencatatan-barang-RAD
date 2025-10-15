@extends('layouts.admin')

@section('title', 'Menu Resep - Chicking BJM')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('beranda') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Menu Resep</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body text-center">
        <h2 class="card-title mb-2">
          <i class="bx bx-restaurant me-2"></i>
          Menu Resep Chicking BJM
        </h2>
        <p class="text-muted mb-0">
          Koleksi resep makanan lezat dengan panduan lengkap cara membuatnya
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Filter dan Search -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('recipes.index') }}" class="row g-3">
      <!-- Search -->
      <div class="col-md-4">
        <label class="form-label">Cari Resep</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nama resep...">
        </div>
      </div>
      
      <!-- Difficulty Filter -->
      <div class="col-md-3">
        <label class="form-label">Tingkat Kesulitan</label>
        <select class="form-select" name="difficulty">
          <option value="">Semua Tingkat</option>
          <option value="mudah" {{ request('difficulty') == 'mudah' ? 'selected' : '' }}>Mudah</option>
          <option value="sedang" {{ request('difficulty') == 'sedang' ? 'selected' : '' }}>Sedang</option>
          <option value="sulit" {{ request('difficulty') == 'sulit' ? 'selected' : '' }}>Sulit</option>
        </select>
      </div>
      
      <!-- Sort -->
      <div class="col-md-3">
        <label class="form-label">Urutkan</label>
        <select class="form-select" name="sort">
          <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nama (A-Z)</option>
          <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
          <option value="prep_time" {{ request('sort') == 'prep_time' ? 'selected' : '' }}>Waktu Persiapan</option>
          <option value="total_time" {{ request('sort') == 'total_time' ? 'selected' : '' }}>Total Waktu</option>
        </select>
      </div>
      
      <!-- Actions -->
      <div class="col-md-2">
        <label class="form-label">&nbsp;</label>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill">
            <i class="bx bx-filter me-1"></i>
            Filter
          </button>
          <a href="{{ route('recipes.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-reset"></i>
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Recipe Cards -->
<div class="row">
  @forelse ($recipes as $recipe)
  <div class="col-lg-4 col-md-6 mb-4">
    <div class="card h-100 recipe-card">
      <!-- Recipe Image -->
      <div class="card-img-container">
        @if($recipe->image)
          <img src="{{ asset('storage/' . $recipe->image) }}" class="card-img-top" alt="{{ $recipe->name }}">
        @else
          <div class="placeholder-img d-flex align-items-center justify-content-center">
            <i class="bx bx-image" style="font-size: 48px; color: #ddd;"></i>
          </div>
        @endif
        <div class="card-overlay">
          <div class="recipe-meta">
            <span class="badge bg-{{ $recipe->difficulty_badge }} mb-2">
              {{ ucfirst($recipe->difficulty) }}
            </span>
            <div class="time-info text-white">
              <small>
                <i class="bx bx-time me-1"></i>
                {{ $recipe->total_time }} menit
              </small>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-2">{{ $recipe->name }}</h5>
        <p class="card-text text-muted flex-grow-1">
          {{ Str::limit($recipe->description, 100) }}
        </p>
        
        <!-- Recipe Info -->
        <div class="recipe-info mb-3">
          <div class="row text-center">
            <div class="col-4">
              <small class="text-muted">Persiapan</small>
              <div class="fw-semibold">{{ $recipe->prep_time }}m</div>
            </div>
            <div class="col-4">
              <small class="text-muted">Memasak</small>
              <div class="fw-semibold">{{ $recipe->cook_time }}m</div>
            </div>
            <div class="col-4">
              <small class="text-muted">Porsi</small>
              <div class="fw-semibold">{{ $recipe->servings }}</div>
            </div>
          </div>
        </div>
        
        <!-- Action Button -->
        <a href="{{ route('recipes.show', $recipe->slug) }}" class="btn btn-primary">
          <i class="bx bx-book-open me-1"></i>
          Lihat Resep
        </a>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="bx bx-restaurant" style="font-size: 64px; color: #ddd;"></i>
        <h5 class="mt-3 text-muted">Belum ada resep</h5>
        <p class="text-muted">Mulai tambahkan resep makanan favorit Anda</p>
        <a href="{{ route('recipes.create') }}" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i>
          Tambah Resep
        </a>
      </div>
    </div>
  </div>
  @endforelse
</div>

<!-- Pagination -->
@if($recipes->hasPages())
<div class="d-flex justify-content-center mt-4">
  {{ $recipes->links() }}
</div>
@endif

<!-- Floating Add Button -->
<a href="{{ route('recipes.create') }}" class="btn btn-primary btn-floating">
  <i class="bx bx-plus"></i>
</a>
@endsection

@push('styles')
<style>
.recipe-card {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  border: none;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.recipe-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.card-img-container {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.card-img-top {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.recipe-card:hover .card-img-top {
  transform: scale(1.05);
}

.placeholder-img {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0) 50%);
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1rem;
}

.recipe-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.recipe-info {
  background: rgba(105, 108, 255, 0.1);
  border-radius: 8px;
  padding: 0.75rem;
}

.btn-floating {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  box-shadow: 0 4px 12px rgba(105, 108, 255, 0.4);
  z-index: 1000;
}

.btn-floating:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(105, 108, 255, 0.6);
}

@media (max-width: 768px) {
  .card-img-container {
    height: 180px;
  }
  
  .btn-floating {
    bottom: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    font-size: 20px;
  }
}
</style>
@endpush
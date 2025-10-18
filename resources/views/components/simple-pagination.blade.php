@props(['items', 'type' => 'item'])

@php
  // Check if items is paginated or collection
  $isPaginated = method_exists($items, 'total');
  $totalItems = $isPaginated ? $items->total() : $items->count();
  $hasItems = $totalItems > 0;
@endphp

@if($hasItems)
<div class="card-footer bg-light">
  <div class="row align-items-center">
    <!-- Info Text -->
    <div class="col-md-6 col-12 mb-2 mb-md-0">
      <small class="text-muted">
        @if($isPaginated)
          Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} 
          dari {{ $items->total() }} {{ $type }}
        @else
          Menampilkan {{ $items->count() }} {{ $type }}
        @endif
      </small>
    </div>
    
    <!-- Pagination & Export -->
    <div class="col-md-6 col-12">
      <div class="d-flex justify-content-md-end justify-content-center align-items-center gap-3">
        <!-- Pagination Links (Only for paginated items) -->
        @if($isPaginated && $items->hasPages())
        <div class="pagination-wrapper">
          {{ $items->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
        @endif
        
        <!-- Export Buttons -->
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" onclick="window.print()" title="Print">
            <i class="bx bx-printer"></i>
            <span class="d-none d-sm-inline ms-1">Print</span>
          </button>
          <button class="btn btn-outline-success btn-sm" onclick="exportToExcel()" title="Export Excel">
            <i class="bx bx-download"></i>
            <span class="d-none d-sm-inline ms-1">Excel</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
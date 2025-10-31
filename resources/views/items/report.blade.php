@extends('layouts.admin')

@section('title', 'Laporan Stock Akhir - Chicking BJM')

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
          <a href="{{ route('items.index') }}">Item</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Laporan Stock</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Header Laporan dengan Period Selector -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h4 class="card-title mb-2">
              <i class="bx bx-chart me-2"></i>
              Laporan Stock Item - {{ Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}
            </h4>
            <p class="text-muted mb-0">
              Total Item: {{ $totalItems }} | 
              Total Stok: {{ number_format($totalStockValue, 0) }} Unit
            </p>
          </div>
          <div class="col-md-4">
            <form method="GET" action="{{ route('items.report') }}" id="periodForm">
              <!-- Preserve existing filters -->
              @foreach(request()->except(['period']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
              @endforeach
              
              <div class="d-flex gap-2">
                <select name="period" class="form-select" onchange="document.getElementById('periodForm').submit()">
                  @foreach($availablePeriods as $period)
                    <option value="{{ $period['value'] }}" {{ $selectedPeriod == $period['value'] ? 'selected' : '' }}>
                      {{ $period['label'] }}
                    </option>
                  @endforeach
                </select>
                <a href="{{ route('items.compare-months') }}" class="btn btn-outline-info">
                  <i class="bx bx-transfer-alt me-1"></i>
                  Compare
                </a>
              </div>
            </form>
          </div>
        </div>
        
        <!-- Period comparison alert -->
        @if($previousSummary)
        <div class="alert alert-info mt-3 mb-0">
          <div class="row text-center">
            <div class="col-md-3">
              <small class="text-muted">Vs {{ Carbon\Carbon::create($prevYear, $prevMonth, 1)->format('M Y') }}</small>
              @php $stockChange = ($currentSummary->total_closing_stock ?? 0) - ($previousSummary->total_closing_stock ?? 0); @endphp
              <h6 class="mb-0 text-{{ $stockChange >= 0 ? 'success' : 'danger' }}">
                {{ $stockChange >= 0 ? '+' : '' }}{{ number_format($stockChange, 0) }} Unit
              </h6>
            </div>
            <div class="col-md-3">
              <small class="text-muted">Stock In</small>
              @php $inChange = ($currentSummary->total_stock_in ?? 0) - ($previousSummary->total_stock_in ?? 0); @endphp
              <h6 class="mb-0 text-{{ $inChange >= 0 ? 'success' : 'danger' }}">
                {{ $inChange >= 0 ? '+' : '' }}{{ number_format($inChange, 0) }}
              </h6>
            </div>
            <div class="col-md-3">
              <small class="text-muted">Stock Out</small>
              @php $outChange = ($currentSummary->total_stock_out ?? 0) - ($previousSummary->total_stock_out ?? 0); @endphp
              <h6 class="mb-0 text-{{ $outChange <= 0 ? 'success' : 'warning' }}">
                {{ $outChange >= 0 ? '+' : '' }}{{ number_format($outChange, 0) }}
              </h6>
            </div>
            <div class="col-md-3">
              <small class="text-muted">Adjustments</small>
              @php $adjChange = ($currentSummary->total_adjustments ?? 0) - ($previousSummary->total_adjustments ?? 0); @endphp
              <h6 class="mb-0 text-info">
                {{ $adjChange >= 0 ? '+' : '' }}{{ number_format($adjChange, 0) }}
              </h6>
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Statistik Ringkasan dengan Previous Month Comparison -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <div class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-package"></i>
            </div>
          </div>
          <div>
            <span class="fw-semibold d-block mb-1">Total Item</span>
            <h3 class="card-title mb-0">{{ $totalItems }}</h3>
            @if($previousSummary)
              @php $itemChange = ($currentSummary->total_items ?? 0) - ($previousSummary->total_items ?? 0); @endphp
              <small class="text-{{ $itemChange >= 0 ? 'success' : 'danger' }}">
                {{ $itemChange >= 0 ? '+' : '' }}{{ $itemChange }} vs bulan lalu
              </small>
            @else
              <small class="text-muted">Item aktif</small>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <div class="avatar-initial rounded bg-label-success">
              <i class="bx bx-box"></i>
            </div>
          </div>
          <div>
            <span class="fw-semibold d-block mb-1">Total Stok</span>
            <h3 class="card-title mb-0">{{ number_format($totalStockValue, 0) }}</h3>
            @if($previousSummary)
              @php $stockChange = ($currentSummary->total_closing_stock ?? 0) - ($previousSummary->total_closing_stock ?? 0); @endphp
              <small class="text-{{ $stockChange >= 0 ? 'success' : 'danger' }}">
                {{ $stockChange >= 0 ? '+' : '' }}{{ number_format($stockChange, 0) }} vs bulan lalu
              </small>
            @else
              <small class="text-muted">Unit tersedia</small>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <div class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-error"></i>
            </div>
          </div>
          <div>
            <span class="fw-semibold d-block mb-1">Stok Menipis</span>
            <h3 class="card-title mb-0 {{ $lowStockItems > 0 ? 'text-warning' : 'text-success' }}">{{ $lowStockItems }}</h3>
            <small class="text-muted">Perlu perhatian</small>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <div class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-x"></i>
            </div>
          </div>
          <div>
            <span class="fw-semibold d-block mb-1">Stok Habis</span>
            <h3 class="card-title mb-0 {{ $outOfStockItems > 0 ? 'text-danger' : 'text-success' }}">{{ $outOfStockItems }}</h3>
            <small class="text-muted">Tidak tersedia</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Monthly Balance Summary untuk periode yang dipilih -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">
      <i class="bx bx-calendar me-2"></i>
      Summary Monthly Balance - {{ Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}
    </h5>
  </div>
  <div class="card-body">
    <div class="row text-center">
      @php
        $totalOpeningStock = $currentSummary->total_opening_stock ?? 0;
        $totalStockIn = $currentSummary->total_stock_in ?? 0;
        $totalStockOut = $currentSummary->total_stock_out ?? 0;
        $totalClosingStock = $currentSummary->total_closing_stock ?? 0;
        $netChange = $totalClosingStock - $totalOpeningStock;
      @endphp
      
      <div class="col-md-3 col-6 mb-3">
        <div class="border rounded p-3">
          <h4 class="text-info mb-1">{{ number_format($totalOpeningStock, 0) }}</h4>
          <span class="text-muted">Total Stok Awal</span>
          <br><small class="text-muted">{{ Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('d M') }}</small>
          @if($previousSummary)
            @php $prevChange = $totalOpeningStock - ($previousSummary->total_opening_stock ?? 0); @endphp
            <br><small class="text-{{ $prevChange >= 0 ? 'success' : 'danger' }}">
              {{ $prevChange >= 0 ? '+' : '' }}{{ number_format($prevChange, 0) }}
            </small>
          @endif
        </div>
      </div>
      
      <div class="col-md-3 col-6 mb-3">
        <div class="border rounded p-3">
          <h4 class="text-success mb-1">+{{ number_format($totalStockIn, 0) }}</h4>
          <span class="text-muted">Total Stok Masuk</span>
          <br><small class="text-muted">Bulan ini</small>
          @if($previousSummary)
            @php $prevChange = $totalStockIn - ($previousSummary->total_stock_in ?? 0); @endphp
            <br><small class="text-{{ $prevChange >= 0 ? 'success' : 'danger' }}">
              {{ $prevChange >= 0 ? '+' : '' }}{{ number_format($prevChange, 0) }}
            </small>
          @endif
        </div>
      </div>
      
      <div class="col-md-3 col-6 mb-3">
        <div class="border rounded p-3">
          <h4 class="text-danger mb-1">-{{ number_format($totalStockOut, 0) }}</h4>
          <span class="text-muted">Total Stok Keluar</span>
          <br><small class="text-muted">Bulan ini</small>
          @if($previousSummary)
            @php $prevChange = $totalStockOut - ($previousSummary->total_stock_out ?? 0); @endphp
            <br><small class="text-{{ $prevChange <= 0 ? 'success' : 'warning' }}">
              {{ $prevChange >= 0 ? '+' : '' }}{{ number_format($prevChange, 0) }}
            </small>
          @endif
        </div>
      </div>
      
      <div class="col-md-3 col-6 mb-3">
        <div class="border rounded p-3">
          <h4 class="text-primary mb-1">{{ number_format($totalClosingStock, 0) }}</h4>
          <span class="text-muted">Total Stok Akhir</span>
          <br><small class="text-muted">Periode ini</small>
          @if($previousSummary)
            @php $prevChange = $totalClosingStock - ($previousSummary->total_closing_stock ?? 0); @endphp
            <br><small class="text-{{ $prevChange >= 0 ? 'success' : 'danger' }}">
              {{ $prevChange >= 0 ? '+' : '' }}{{ number_format($prevChange, 0) }}
            </small>
          @endif
        </div>
      </div>
    </div>
    
    <!-- Net Change Display dengan comparison -->
    <div class="row mt-3">
      <div class="col-12">
        <div class="alert alert-{{ $netChange >= 0 ? 'success' : 'warning' }} text-center">
          <h5 class="mb-2">
            <i class="bx bx-transfer me-2"></i>
            Perubahan Bersih {{ Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}
          </h5>
          @if($netChange > 0)
            <h3 class="text-success mb-1">
              <i class="bx bx-up-arrow-alt"></i>
              +{{ number_format($netChange, 0) }} Unit
            </h3>
            <span class="text-success">Stok bertambah dari awal bulan</span>
          @elseif($netChange < 0)
            <h3 class="text-danger mb-1">
              <i class="bx bx-down-arrow-alt"></i>
              {{ number_format($netChange, 0) }} Unit
            </h3>
            <span class="text-danger">Stok berkurang dari awal bulan</span>
          @else
            <h3 class="text-muted mb-1">
              <i class="bx bx-minus"></i>
              0 Unit
            </h3>
            <span class="text-muted">Tidak ada perubahan stok</span>
          @endif
          
          @if($previousSummary)
            @php 
              $prevNetChange = ($previousSummary->total_closing_stock ?? 0) - ($previousSummary->total_opening_stock ?? 0);
              $netChangeComparison = $netChange - $prevNetChange;
            @endphp
            <div class="mt-2">
              <small class="text-muted">
                Vs bulan lalu: 
                <span class="text-{{ $netChangeComparison >= 0 ? 'success' : 'danger' }}">
                  {{ $netChangeComparison >= 0 ? '+' : '' }}{{ number_format($netChangeComparison, 0) }}
                </span>
              </small>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filter dan Pengaturan Laporan -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">
      <i class="bx bx-filter me-2"></i>
      Filter Laporan
    </h5>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('items.report') }}" class="row g-3">
      <!-- Hidden period -->
      <input type="hidden" name="period" value="{{ $selectedPeriod }}">
      
      <!-- Pencarian -->
      <div class="col-md-3">
        <label class="form-label">Cari Item</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nama atau SKU...">
        </div>
      </div>
      
      <!-- Filter Kategori -->
      <div class="col-md-2">
        <label class="form-label">Kategori</label>
        <select class="form-select" name="category_id">
          <option value="">Semua</option>
          @foreach($categories as $category)
          <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
            {{ $category->category_name }}
          </option>
          @endforeach
        </select>
      </div>
            
      <!-- Filter Status Stok -->
      <div class="col-md-2">
        <label class="form-label">Status Stok</label>
        <select class="form-select" name="stock_status">
          <option value="">Semua</option>
          <option value="in" {{ request('stock_status') == 'in' ? 'selected' : '' }}>Stok Tersedia</option>
          <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stok Menipis</option>
          <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Stok Habis</option>
        </select>
      </div>
      
      <!-- Urutkan Berdasarkan -->
      <div class="col-md-2">
        <label class="form-label">Urutkan</label>
        <select class="form-select" name="sort_by">
          <option value="item_name" {{ request('sort_by') == 'item_name' ? 'selected' : '' }}>Nama Item</option>
          <option value="sku" {{ request('sort_by') == 'sku' ? 'selected' : '' }}>SKU</option>
          <option value="category" {{ request('sort_by') == 'category' ? 'selected' : '' }}>Kategori</option>
          <option value="stock" {{ request('sort_by') == 'stock' ? 'selected' : '' }}>Jumlah Stok</option>
        </select>
      </div>
      
      <!-- Urutan -->
      <div class="col-md-1">
        <label class="form-label">Urutan</label>
        <select class="form-select" name="sort_order">
          <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>A-Z</option>
          <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Z-A</option>
        </select>
      </div>
      
      <!-- Tombol Aksi -->
      <div class="col-12">
        <div class="d-flex gap-2 flex-wrap">
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-filter me-1"></i>
            Terapkan Filter
          </button>
          <a href="{{ route('items.report', ['period' => $selectedPeriod]) }}" class="btn btn-outline-secondary">
            <i class="bx bx-reset me-1"></i>
            Reset
          </a>
          <button type="button" class="btn btn-success" onclick="exportToExcel()">
            <i class="bx bx-download me-1"></i>
            Export Excel
          </button>
          <button type="button" class="btn btn-info" onclick="openPrintWindow()">
            <i class="bx bx-printer me-1"></i>
            Print
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Tabel Laporan dengan Previous Month Comparison -->
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">
      <i class="bx bx-table me-2"></i>
      Detail Laporan Stock - {{ Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}
      @if(request()->hasAny(['search', 'category_id', 'supplier_id', 'stock_status']))
        <span class="badge bg-label-primary ms-2">Filtered</span>
      @endif
    </h5>
  </div>
  
  <div class="table-responsive">
    <table class="table table-striped table-hover" id="reportTable">
      <thead class="table-dark">
        <tr>
          <th style="width: 50px;">No</th>
          <th>SKU</th>
          <th>Nama Item</th>
          <th>Kategori</th>
          <th>Unit</th>
          <th class="text-center">Stok Awal</th>
          <th class="text-center">Stok Masuk</th>
          <th class="text-center">Stok Keluar</th>
          <th class="text-center">Stok Akhir</th>
          <th class="text-center">Min Stock</th>
          <th class="text-center">Selisih</th>
          <th class="text-center">vs Bulan Lalu</th>
          <th class="text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $index => $item)
        @php
          $balance = $item->monthlyBalances->first();
          $previousBalance = null;
          
          // Cari previous balance manual jika tidak ada dari relationship
          if (!$balance) {
            $balance = App\Models\MonthlyStockBalance::getBalance($item->id, $selectedYear, $selectedMonth);
          }
          
          $previousBalance = App\Models\MonthlyStockBalance::getPreviousMonthBalance($item->id, $selectedYear, $selectedMonth);
          
          $openingStock = $balance ? $balance->opening_stock : 0;
          $stockIn = $balance ? $balance->stock_in : 0;
          $stockOut = $balance ? $balance->stock_out : 0;
          $closingStock = $balance ? $balance->closing_stock : $item->current_stock;
          $difference = $closingStock - $openingStock;
          
          // Previous month comparison
          $prevClosingStock = $previousBalance ? $previousBalance->closing_stock : 0;
          $monthlyChange = $closingStock - $prevClosingStock;
          
          // Status calculation
          if ($closingStock <= 0) {
            $stockStatus = 'Habis';
            $stockStatusColor = 'danger';
          } elseif ($closingStock <= $item->low_stock_threshold) {
            $stockStatus = 'Menipis';
            $stockStatusColor = 'warning';
          } else {
            $stockStatus = 'Tersedia';
            $stockStatusColor = 'success';
          }
          
          $minDifference = $closingStock - $item->low_stock_threshold;
        @endphp
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>
            <span class="badge bg-label-secondary">{{ $item->sku }}</span>
          </td>
          <td>
            <div>
              <strong>{{ $item->item_name }}</strong>
              @if($item->supplier)
              <br><small class="text-muted">{{ $item->supplier->supplier_name }}</small>
              @endif
            </div>
          </td>
          <td>
            {{ $item->category->category_name ?? '-' }}
          </td>
          <td>
            <span class="badge bg-label-info">{{ $item->unit }}</span>
          </td>
          <td class="text-center">
            @if($balance)
            <span class="fw-bold text-info">{{ number_format($openingStock, 0) }}</span>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td class="text-center">
            @if($balance)
            <span class="fw-bold text-success">+{{ number_format($stockIn, 0) }}</span>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td class="text-center">
            @if($balance)
            <span class="fw-bold text-danger">-{{ number_format($stockOut, 0) }}</span>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td class="text-center">
            <strong class="text-{{ $stockStatusColor }}">
              {{ number_format($closingStock, 0) }}
            </strong>
          </td>
          <td class="text-center">
            <span class="text-muted">{{ number_format($item->low_stock_threshold, 0) }}</span>
          </td>
          <td class="text-center">
            @if($balance)
            <div>
              <!-- Selisih dari awal bulan -->
              <span class="fw-bold text-{{ $difference >= 0 ? 'success' : 'danger' }}">
                @if($difference > 0)
                  <i class="bx bx-up-arrow-alt"></i>+{{ number_format($difference, 0) }}
                @elseif($difference < 0)
                  <i class="bx bx-down-arrow-alt"></i>{{ number_format($difference, 0) }}
                @else
                  <i class="bx bx-minus"></i>0
                @endif
              </span>
              <br>
              <!-- Selisih dari minimum -->
              <small class="text-{{ $minDifference >= 0 ? 'success' : 'danger' }}">
                Min: {{ $minDifference >= 0 ? '+' : '' }}{{ number_format($minDifference, 0) }}
              </small>
            </div>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td class="text-center">
            @if($previousBalance)
            <div>
              <span class="fw-bold text-{{ $monthlyChange >= 0 ? 'success' : 'danger' }}">
                @if($monthlyChange > 0)
                  <i class="bx bx-up-arrow-alt"></i>+{{ number_format($monthlyChange, 0) }}
                @elseif($monthlyChange < 0)
                  <i class="bx bx-down-arrow-alt"></i>{{ number_format($monthlyChange, 0) }}
                @else
                  <i class="bx bx-minus"></i>0
                @endif
              </span>
              <br><small class="text-muted">{{ number_format($prevClosingStock, 0) }}</small>
            </div>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td class="text-center">
            <span class="badge bg-{{ $stockStatusColor }}">
              {{ $stockStatus }}
            </span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="13" class="text-center py-4">
            <div class="d-flex flex-column align-items-center">
              <i class="bx bx-chart" style="font-size: 48px; color: #ddd;"></i>
              <h6 class="mt-2 text-muted">Tidak ada data untuk ditampilkan</h6>
              <p class="text-muted">
                @if(request()->hasAny(['search', 'category_id', 'supplier_id', 'stock_status']))
                  Coba ubah filter atau kata kunci pencarian
                @else
                  Belum ada item untuk dilaporkan pada periode ini
                @endif
              </p>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
      @if($items->count() > 0)
      <tfoot class="table-light">
        <tr>
          <th colspan="5" class="text-end">Total:</th>
          <th class="text-center">{{ number_format($totalOpeningStock, 0) }}</th>
          <th class="text-center text-success">+{{ number_format($totalStockIn, 0) }}</th>
          <th class="text-center text-danger">-{{ number_format($totalStockOut, 0) }}</th>
          <th class="text-center">{{ number_format($totalClosingStock, 0) }}</th>
          <th class="text-center">{{ number_format($items->sum('low_stock_threshold'), 0) }}</th>
          <th class="text-center">
            <span class="fw-bold text-{{ $netChange >= 0 ? 'success' : 'danger' }}">
              {{ $netChange >= 0 ? '+' : '' }}{{ number_format($netChange, 0) }}
            </span>
          </th>
          <th class="text-center">
            @if($previousSummary)
              @php $totalMonthlyChange = $totalClosingStock - ($previousSummary->total_closing_stock ?? 0); @endphp
              <span class="fw-bold text-{{ $totalMonthlyChange >= 0 ? 'success' : 'danger' }}">
                {{ $totalMonthlyChange >= 0 ? '+' : '' }}{{ number_format($totalMonthlyChange, 0) }}
              </span>
            @else
              <span class="text-muted">-</span>
            @endif
          </th>
          <th></th>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>

<!-- Info Footer -->
<div class="row mt-4">
  <div class="col-12">
    <div class="alert alert-info">
      <h6 class="alert-heading">
        <i class="bx bx-info-circle me-2"></i>
        Keterangan Laporan
      </h6>
      <div class="row">
        <div class="col-md-6">
          <ul class="mb-0">
            <li><strong>Stok Awal:</strong> Stok di awal periode {{ Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}</li>
            <li><strong>Stok Masuk:</strong> Total stok yang masuk dalam periode ini</li>
            <li><strong>Stok Keluar:</strong> Total stok yang keluar dalam periode ini</li>
            <li><strong>Stok Akhir:</strong> Stok pada akhir periode</li>
          </ul>
        </div>
        <div class="col-md-6">
          <ul class="mb-0">
            <li><strong>Selisih:</strong> Perubahan stok dari awal periode dan dari minimum</li>
            <li><strong>vs Bulan Lalu:</strong> Perbandingan dengan {{ Carbon\Carbon::create($prevYear, $prevMonth, 1)->format('F Y') }}</li>
            <li><strong>Status:</strong> Kondisi stok berdasarkan minimum threshold</li>
            <li><strong>Period Selector:</strong> Pilih bulan untuk melihat data historis</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Export ke Excel
function exportToExcel() {
  const table = document.getElementById('reportTable');
  const ws = XLSX.utils.table_to_sheet(table);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Laporan Stock');
  
  // Set column widths
  const wscols = [
    {wch: 5},   // No
    {wch: 12},  // SKU
    {wch: 25},  // Nama Item
    {wch: 15},  // Kategori
    {wch: 8},   // Unit
    {wch: 10},  // Stok Awal
    {wch: 10},  // Stok Masuk
    {wch: 10},  // Stok Keluar
    {wch: 10},  // Stok Akhir
    {wch: 10},  // Min Stock
    {wch: 10},  // Selisih
    {wch: 12},  // vs Bulan Lalu
    {wch: 10}   // Status
  ];
  ws['!cols'] = wscols;
  
  const filename = 'laporan_stock_{{ $selectedYear }}_{{ str_pad($selectedMonth, 2, "0", STR_PAD_LEFT) }}_' + new Date().toISOString().slice(0,10).replace(/-/g, '') + '.xlsx';
  XLSX.writeFile(wb, filename);
}

function openPrintWindow() {
  const printUrl = '{{ route("items.print-report") }}' + window.location.search;
  
  // Buka di tab baru
  const printWindow = window.open(printUrl, '_blank');
  
  // Auto print setelah page load (optional)
  printWindow.onload = function() {
    setTimeout(() => {
      printWindow.print();
    }, 1000);
  };
}

// Load library XLSX untuk export Excel
const script = document.createElement('script');
script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
document.head.appendChild(script);
</script>
@endpush

@push('styles')
<style>
@media print {
  .btn, .breadcrumb, .card-header .d-flex .btn, .filter-section, .alert {
    display: none !important;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
  }
  
  .table {
    font-size: 10px;
  }
  
  .table th, .table td {
    padding: 3px !important;
    border: 1px solid #000 !important;
  }
  
  .badge {
    background-color: #f8f9fa !important;
    color: #000 !important;
    border: 1px solid #000 !important;
  }
}

.table th {
  font-weight: 600;
  font-size: 0.875rem;
  vertical-align: middle;
}

.table td {
  vertical-align: middle;
}

.badge {
  font-size: 0.75rem;
}

.border {
  border: 1px solid #dee2e6 !important;
}

.table-hover tbody tr:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

/* Status colors */
.text-success { color: #71dd37 !important; }
.text-danger { color: #ff3e1d !important; }
.text-warning { color: #ffab00 !important; }
.text-info { color: #03c3ec !important; }

/* Progress indicators */
.progress {
  height: 4px;
}

/* Responsive table scroll */
.table-responsive {
  min-height: 400px;
}

/* Footer totals styling */
tfoot th {
  background-color: #f8f9fa !important;
  font-weight: 600;
  border-top: 2px solid #dee2e6;
}

/* Period selector styling */
#periodForm select {
  min-width: 150px;
}
</style>
@endpush
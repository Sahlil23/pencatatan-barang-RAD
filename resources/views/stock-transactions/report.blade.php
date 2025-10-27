@extends('layouts.admin')

@section('title', 'Laporan Transaksi Stok - Chicking BJM')

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
          <a href="{{ route('stock-transactions.index') }}">Transaksi Stok</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Laporan</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Report Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h4 class="mb-1">
              <i class="bx bx-bar-chart me-2"></i>
              Laporan Transaksi Stok
            </h4>
            <p class="text-muted mb-0">
              Periode: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
              <span class="badge bg-label-primary ms-2">{{ $endDate->diffInDays($startDate) + 1 }} hari</span>
            </p>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="openPrintWindow()">
              <i class="bx bx-printer me-1"></i>
              Print
            </button>
            <button class="btn btn-outline-success btn-sm" onclick="exportReport()">
              <i class="bx bx-download me-1"></i>
              Export Excel
            </button>
            <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-primary btn-sm">
              <i class="bx bx-arrow-back me-1"></i>
              Kembali
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
  <div class="card-header">
    <h6 class="mb-0">
      <i class="bx bx-calendar me-2"></i>
      Filter Periode
    </h6>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('stock-transactions.report') }}" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Dari Tanggal</label>
        <input type="date" class="form-control" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
      </div>
      <div class="col-md-4">
        <label class="form-label">Sampai Tanggal</label>
        <input type="date" class="form-control" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
      </div>
      <div class="col-md-4">
        <label class="form-label">&nbsp;</label>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-search me-1"></i>
            Filter
          </button>
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bx bx-calendar me-1"></i>
              Quick Filter
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="?start_date={{ now()->startOfWeek()->format('Y-m-d') }}&end_date={{ now()->endOfWeek()->format('Y-m-d') }}">Minggu Ini</a></li>
              <li><a class="dropdown-item" href="?start_date={{ now()->startOfMonth()->format('Y-m-d') }}&end_date={{ now()->endOfMonth()->format('Y-m-d') }}">Bulan Ini</a></li>
              <li><a class="dropdown-item" href="?start_date={{ now()->startOfQuarter()->format('Y-m-d') }}&end_date={{ now()->endOfQuarter()->format('Y-m-d') }}">Quarter Ini</a></li>
              <li><a class="dropdown-item" href="?start_date={{ now()->startOfYear()->format('Y-m-d') }}&end_date={{ now()->endOfYear()->format('Y-m-d') }}">Tahun Ini</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="?start_date={{ now()->subWeek()->startOfWeek()->format('Y-m-d') }}&end_date={{ now()->subWeek()->endOfWeek()->format('Y-m-d') }}">Minggu Lalu</a></li>
              <li><a class="dropdown-item" href="?start_date={{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}&end_date={{ now()->subMonth()->endOfMonth()->format('Y-m-d') }}">Bulan Lalu</a></li>
            </ul>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card border-success">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="Stock In" class="rounded" />
          </div>
          <div class="dropdown">
            <span class="badge bg-success">IN</span>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1 text-success">Total Stok Masuk</span>
        <h3 class="card-title mb-2 text-success">{{ number_format($stockIn, 2) }}</h3>
        <small class="text-success fw-semibold">
          <i class="bx bx-up-arrow-alt"></i> 
          @php 
            $stockInCount = \App\Models\StockTransaction::stockIn()
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->count();
          @endphp
          {{ $stockInCount }} transaksi
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card border-danger">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Stock Out" class="rounded" />
          </div>
          <div class="dropdown">
            <span class="badge bg-danger">OUT</span>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1 text-danger">Total Stok Keluar</span>
        <h3 class="card-title mb-2 text-danger">{{ number_format($stockOut, 2) }}</h3>
        <small class="text-danger fw-semibold">
          <i class="bx bx-down-arrow-alt"></i> 
          @php 
            $stockOutCount = \App\Models\StockTransaction::stockOut()
                           ->whereBetween('created_at', [$startDate, $endDate])
                           ->count();
          @endphp
          {{ $stockOutCount }} transaksi
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card border-warning">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/cc-warning.png') }}" alt="Net Change" class="rounded" />
          </div>
          <div class="dropdown">
            <span class="badge bg-warning">NET</span>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1 text-warning">Perubahan Bersih</span>
        @php $netChange = $stockIn - $stockOut; @endphp
        <h3 class="card-title mb-2 text-{{ $netChange >= 0 ? 'success' : 'danger' }}">
          {{ $netChange >= 0 ? '+' : '' }}{{ number_format($netChange, 2) }}
        </h3>
        <small class="text-warning fw-semibold">
          <i class="bx {{ $netChange >= 0 ? 'bx-trending-up' : 'bx-trending-down' }}"></i> 
          {{ $netChange >= 0 ? 'Surplus' : 'Defisit' }}
        </small>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Total Transactions" class="rounded" />
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Transaksi</span>
        @php 
          $totalTransactions = \App\Models\StockTransaction::whereBetween('created_at', [$startDate, $endDate])->count();
        @endphp
        <h3 class="card-title mb-2">{{ number_format($totalTransactions) }}</h3>
        <small class="text-primary fw-semibold">
          <i class="bx bx-history"></i> 
          Dalam {{ $endDate->diffInDays($startDate) + 1 }} hari
        </small>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Daily Transactions Chart -->
  <div class="col-lg-8 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="bx bx-line-chart me-2"></i>
          Tren Transaksi Harian
        </h5>
        <div class="dropdown">
          <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bx bx-cog me-1"></i>
            Opsi
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="toggleChart('quantity')">Tampilkan Kuantitas</a></li>
            <li><a class="dropdown-item" href="#" onclick="toggleChart('count')">Tampilkan Jumlah Transaksi</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#" onclick="downloadChart()">Download Chart</a></li>
          </ul>
        </div>
      </div>
      <div class="card-body">
        <canvas id="dailyChart" width="400" height="200"></canvas>
      </div>
    </div>
  </div>

  <!-- Top Items -->
  <div class="col-lg-4 mb-4">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="bx bx-trending-up me-2"></i>
          Top 10 Item Aktif
        </h6>
      </div>
      <div class="card-body">
        @if($topItems->count() > 0)
        <div class="list-group list-group-flush">
          @foreach($topItems as $index => $topItem)
          <div class="list-group-item px-0 py-3 border-0">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ $index < 3 ? 'primary' : 'secondary' }}">
                  {{ $index + 1 }}
                </span>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">
                  <a href="{{ route('items.show', $topItem->item->id) }}" class="text-decoration-none">
                    {{ Str::limit($topItem->item->item_name, 25) }}
                  </a>
                </h6>
                <div class="d-flex align-items-center gap-2 mt-1">
                  <small class="text-muted">{{ $topItem->item->sku }}</small>
                  <span class="badge bg-label-info">{{ $topItem->transaction_count }} transaksi</span>
                </div>
                <div class="progress mt-2" style="height: 4px;">
                  @php 
                    $maxQuantity = $topItems->max('total_quantity');
                    $percentage = $maxQuantity > 0 ? ($topItem->total_quantity / $maxQuantity) * 100 : 0;
                  @endphp
                  <div class="progress-bar bg-{{ $index < 3 ? 'primary' : 'secondary' }}" 
                       style="width: {{ $percentage }}%"></div>
                </div>
              </div>
              <div class="text-end">
                <span class="fw-bold text-primary">{{ number_format($topItem->total_quantity, 0) }}</span>
                <br><small class="text-muted">{{ $topItem->item->unit }}</small>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="text-center py-3">
          <i class="bx bx-package text-muted" style="font-size: 32px;"></i>
          <p class="text-muted mt-2 mb-0">Tidak ada data item dalam periode ini</p>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Daily Transactions Table -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bx bx-calendar me-2"></i>
      Ringkasan Transaksi Harian
    </h5>
    <span class="badge bg-label-primary">{{ $dailyTransactions->count() }} hari</span>
  </div>
  
  @if($dailyTransactions->count() > 0)
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>
            <i class="bx bx-calendar me-1"></i>
            Tanggal
          </th>
          <th class="text-center">
            <i class="bx bx-plus-circle text-success me-1"></i>
            Stok Masuk
          </th>
          <th class="text-center">
            <i class="bx bx-minus-circle text-danger me-1"></i>
            Stok Keluar
          </th>
          <th class="text-center">
            <i class="bx bx-transfer text-warning me-1"></i>
            Penyesuaian
          </th>
          <th class="text-center">
            <i class="bx bx-calculator me-1"></i>
            Total Transaksi
          </th>
          <th class="text-center">
            <i class="bx bx-trending-up me-1"></i>
            Net Change
          </th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($dailyTransactions as $date => $transactions)
        @php
          $dateTransactions = $transactions->keyBy('transaction_type');
          $stockInQty = $dateTransactions->get('IN')->total_quantity ?? 0;
          $stockOutQty = $dateTransactions->get('OUT')->total_quantity ?? 0;
          $adjustmentQty = $dateTransactions->get('ADJUSTMENT')->total_quantity ?? 0;
          $stockInCount = $dateTransactions->get('IN')->transaction_count ?? 0;
          $stockOutCount = $dateTransactions->get('OUT')->transaction_count ?? 0;
          $adjustmentCount = $dateTransactions->get('ADJUSTMENT')->transaction_count ?? 0;
          $totalCount = $stockInCount + $stockOutCount + $adjustmentCount;
          $netChange = $stockInQty - $stockOutQty;
        @endphp
        <tr>
          <td>
            <div>
              <span class="fw-semibold">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
              <br><small class="text-muted">{{ \Carbon\Carbon::parse($date)->format('l') }}</small>
            </div>
          </td>
          <td class="text-center">
            @if($stockInQty > 0)
            <div>
              <span class="fw-bold text-success">{{ number_format($stockInQty, 0) }}</span>
              <br><small class="text-muted">{{ $stockInCount }} transaksi</small>
            </div>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td class="text-center">
            @if($stockOutQty > 0)
            <div>
              <span class="fw-bold text-danger">{{ number_format($stockOutQty, 0) }}</span>
              <br><small class="text-muted">{{ $stockOutCount }} transaksi</small>
            </div>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td class="text-center">
            @if($adjustmentQty > 0)
            <div>
              <span class="fw-bold text-warning">{{ number_format($adjustmentQty, 0) }}</span>
              <br><small class="text-muted">{{ $adjustmentCount }} transaksi</small>
            </div>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td class="text-center">
            <span class="badge bg-label-primary">{{ $totalCount }}</span>
          </td>
          <td class="text-center">
            <span class="fw-bold text-{{ $netChange >= 0 ? 'success' : 'danger' }}">
              {{ $netChange >= 0 ? '+' : '' }}{{ number_format($netChange, 0) }}
            </span>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  
  <!-- Summary Footer -->
  <div class="card-footer">
    <div class="row text-center">
      <div class="col-md-3">
        <div class="d-flex flex-column">
          <span class="text-muted small">Rata-rata Harian</span>
          <span class="fw-bold">{{ number_format($totalTransactions / max(1, $dailyTransactions->count()), 1) }} transaksi</span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="d-flex flex-column">
          <span class="text-muted small">Hari Paling Aktif</span>
          @php
            $maxDayTransactions = 0;
            $maxDay = '';
            foreach($dailyTransactions as $date => $transactions) {
              $dayTotal = $transactions->sum('transaction_count');
              if ($dayTotal > $maxDayTransactions) {
                $maxDayTransactions = $dayTotal;
                $maxDay = $date;
              }
            }
          @endphp
          <span class="fw-bold">{{ $maxDay ? \Carbon\Carbon::parse($maxDay)->format('d/m/Y') : '-' }}</span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="d-flex flex-column">
          <span class="text-muted small">Total Stok Masuk</span>
          <span class="fw-bold text-success">{{ number_format($stockIn, 0) }}</span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="d-flex flex-column">
          <span class="text-muted small">Total Stok Keluar</span>
          <span class="fw-bold text-danger">{{ number_format($stockOut, 0) }}</span>
        </div>
      </div>
    </div>
  </div>
  @else
  <!-- Empty State -->
  <div class="card-body text-center py-5">
    <div class="d-flex flex-column align-items-center">
      <i class="bx bx-calendar-x text-muted" style="font-size: 64px;"></i>
      <h5 class="mt-3">Tidak Ada Data</h5>
      <p class="text-muted mb-4">
        Tidak ada transaksi dalam periode {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}.<br>
        Coba ubah rentang tanggal atau periksa data transaksi.
      </p>
      <div class="d-flex gap-2">
        <a href="{{ route('stock-transactions.create') }}" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i>
          Tambah Transaksi
        </a>
        <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">
          <i class="bx bx-history me-1"></i>
          Lihat Semua Transaksi
        </a>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function openPrintWindow() {
    const currentUrl = window.location.href;
    const printUrl = '{{ route("stock-transactions.print-report") }}' + window.location.search;
    
    // Buka di tab baru
    const printWindow = window.open(printUrl, '_blank');
    
    // Auto print setelah page load (optional)
    printWindow.onload = function() {
      setTimeout(() => {
        printWindow.print();
      }, 500);
    };
  }
document.addEventListener('DOMContentLoaded', function() {
  // Daily transactions chart
  const ctx = document.getElementById('dailyChart').getContext('2d');
  
  const dailyData = @json($dailyTransactions);
  const dates = Object.keys(dailyData).reverse();
  
  const stockInData = dates.map(date => {
    const dayData = dailyData[date].find(t => t.transaction_type === 'IN');
    return dayData ? parseFloat(dayData.total_quantity) : 0;
  });
  
  const stockOutData = dates.map(date => {
    const dayData = dailyData[date].find(t => t.transaction_type === 'OUT');
    return dayData ? parseFloat(dayData.total_quantity) : 0;
  });
  
  const formattedDates = dates.map(date => {
    return new Date(date).toLocaleDateString('id-ID', { 
      day: '2-digit', 
      month: '2-digit' 
    });
  });

  const dailyChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: formattedDates,
      datasets: [{
        label: 'Stok Masuk',
        data: stockInData,
        borderColor: '#71dd37',
        backgroundColor: 'rgba(113, 221, 55, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }, {
        label: 'Stok Keluar',
        data: stockOutData,
        borderColor: '#ff3e1d',
        backgroundColor: 'rgba(255, 62, 29, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          mode: 'index',
          intersect: false,
          callbacks: {
            label: function(context) {
              return context.dataset.label + ': ' + context.parsed.y.toLocaleString('id-ID');
            }
          }
        }
      },
      scales: {
        x: {
          display: true,
          title: {
            display: true,
            text: 'Tanggal'
          }
        },
        y: {
          display: true,
          title: {
            display: true,
            text: 'Kuantitas'
          },
          beginAtZero: true
        }
      },
      interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
      }
    }
  });

  // Export report function
  window.exportReport = function() {
    // Create CSV content
    let csvContent = 'Laporan Transaksi Stok\n';
    csvContent += 'Periode: {{ $startDate->format("d/m/Y") }} - {{ $endDate->format("d/m/Y") }}\n\n';
    csvContent += 'RINGKASAN\n';
    csvContent += 'Total Stok Masuk,{{ $stockIn }}\n';
    csvContent += 'Total Stok Keluar,{{ $stockOut }}\n';
    csvContent += 'Perubahan Bersih,{{ $netChange }}\n';
    csvContent += 'Total Transaksi,{{ $totalTransactions }}\n\n';
    
    csvContent += 'TRANSAKSI HARIAN\n';
    csvContent += 'Tanggal,Stok Masuk,Stok Keluar,Penyesuaian,Total Transaksi,Net Change\n';
    
    @foreach($dailyTransactions as $date => $transactions)
    @php
      $dateTransactions = $transactions->keyBy('transaction_type');
      $stockInQty = $dateTransactions->get('IN')->total_quantity ?? 0;
      $stockOutQty = $dateTransactions->get('OUT')->total_quantity ?? 0;
      $adjustmentQty = $dateTransactions->get('ADJUSTMENT')->total_quantity ?? 0;
      $totalCount = $transactions->sum('transaction_count');
      $netChange = $stockInQty - $stockOutQty;
    @endphp
    csvContent += '"{{ \Carbon\Carbon::parse($date)->format("d/m/Y") }}",{{ $stockInQty }},{{ $stockOutQty }},{{ $adjustmentQty }},{{ $totalCount }},{{ $netChange }}\n';
    @endforeach
    
    csvContent += '\nTOP ITEMS\n';
    csvContent += 'Ranking,Item,SKU,Total Kuantitas,Jumlah Transaksi\n';
    @foreach($topItems as $index => $topItem)
    csvContent += '{{ $index + 1 }},"{{ $topItem->item->item_name }}","{{ $topItem->item->sku }}",{{ $topItem->total_quantity }},{{ $topItem->transaction_count }}\n';
    @endforeach
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'laporan_transaksi_stok_{{ $startDate->format("Y-m-d") }}_{{ $endDate->format("Y-m-d") }}.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Toggle chart view
  window.toggleChart = function(type) {
    // Implementation for toggling between quantity and count view
    console.log('Toggle chart to:', type);
  };

  // Download chart
  window.downloadChart = function() {
    const canvas = document.getElementById('dailyChart');
    const link = document.createElement('a');
    link.download = 'chart_transaksi_stok.png';
    link.href = canvas.toDataURL();
    link.click();
  };
});
</script>
@endpush

@push('styles')
<style>
@media print {
  .btn, .breadcrumb, .card-header .btn, .dropdown, .modal {
    display: none !important;
  }
  
  .card {
    border: none !important;
    box-shadow: none !important;
    break-inside: avoid;
  }
  
  .table {
    font-size: 12px;
  }
  
  .row {
    break-inside: avoid;
  }
  
  #dailyChart {
    max-height: 300px !important;
  }
}

.card.border-success {
  border-color: #71dd37 !important;
}

.card.border-danger {
  border-color: #ff3e1d !important;
}

.card.border-warning {
  border-color: #ffab00 !important;
}

.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  font-size: 14px;
  font-weight: 600;
}

.list-group-item {
  transition: background-color 0.15s ease-in-out;
}

.list-group-item:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.progress {
  background-color: #e9ecef;
}

.table th {
  font-weight: 600;
  background-color: #f8f9fa;
}

#dailyChart {
  height: 300px !important;
}

.card-footer .row > div {
  border-right: 1px solid #e9ecef;
  padding: 1rem;
}

.card-footer .row > div:last-child {
  border-right: none;
}

@media (max-width: 768px) {
  .card-footer .row > div {
    border-right: none;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 0.5rem;
  }
  
  .card-footer .row > div:last-child {
    border-bottom: none;
    margin-bottom: 0;
  }
}
</style>
@endpush
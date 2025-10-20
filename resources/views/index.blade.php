@extends('layouts.admin')
@section('content')
<div class="row">
  <div class="col-lg-8 mb-4 order-0">
    <div class="card">
      <div class="d-flex align-items-end row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary">Welcome to Chicking BJM! 🎉</h5>
            <p class="mb-4">
              Sistem inventory terintegrasi untuk mengelola stok dengan mudah. 
              Hari ini ada <span class="fw-bold">{{ $todayStockIn + $todayStockOut }}</span> transaksi stok.
            </p>
            <a href="{{ route('stock-transactions.index') }}" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img
              src="{{ asset('assets/img/man-with-laptop.png') }}"

              height="140"
              alt="View Badge User"
              data-app-dark-img="illustrations/man-with-laptop-dark.png"
              data-app-light-img="illustrations/man-with-laptop-light.png"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Statistics Cards -->
  <div class="col-lg-4 order-1">
    <div class="row">
      <div class="col-lg-6 col-md-12 col-6 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="Total Items" class="rounded" />
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Total Items</span>
            <h3 class="card-title mb-2">{{ number_format($totalItems) }}</h3>
            <small class="text-primary fw-semibold">
              <i class="bx bx-package"></i> Items
            </small>
          </div>
        </div>
      </div>
      <div class="col-lg-6 col-md-12 col-6 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Low Stock" class="rounded" />
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Low Stock</span>
            <h3 class="card-title text-nowrap mb-1 {{ $lowStockItems > 0 ? 'text-warning' : 'text-success' }}">
              {{ $lowStockItems }}
            </h3>
            <small class="{{ $lowStockItems > 0 ? 'text-warning' : 'text-success' }} fw-semibold">
              <i class="bx {{ $lowStockItems > 0 ? 'bx-error' : 'bx-check' }}"></i> 
              {{ $lowStockItems > 0 ? 'Perlu Perhatian' : 'Stok Aman' }}
            </small>
          </div>
        </div>
      </div>
      <div class="col-lg-6 col-md-12 col-6 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="Categories" class="rounded" />
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Categories</span>
            <h3 class="card-title mb-2">{{ $totalCategories }}</h3>
            <small class="text-info fw-semibold">
              <i class="bx bx-category"></i> Total
            </small>
          </div>
        </div>
      </div>
      <div class="col-lg-6 col-md-12 col-6 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Suppliers" class="rounded" />
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Suppliers</span>
            <h3 class="card-title mb-2">{{ $totalSuppliers }}</h3>
            <small class="text-secondary fw-semibold">
              <i class="bx bx-group"></i> Active
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Second Row - Charts (REMOVE EXTRA MARGIN) -->
<div class="row">
  <!-- Stock by Category Chart -->
  <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between pb-0">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Stock by Category</h5>
          <small class="text-muted">{{ $stockByCategory->sum('items_sum_current_stock') }} Total Stock</small>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex flex-column align-items-center gap-1">
            <h2 class="mb-2">{{ number_format($stockByCategory->sum('items_sum_current_stock')) }}</h2>
            <span>Total Stock</span>
          </div>
          <div id="stockByCategoryChart"></div>
        </div>
        <ul class="p-0 m-0">
          @foreach($stockByCategory->take(4) as $category)
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-{{ ['primary', 'success', 'info', 'warning'][$loop->index % 4] }}">

                <i class="bx bx-package"></i>
              </span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">{{ $category->category_name }}</h6>
                <small class="text-muted">{{ $category->description ?? 'No description' }}</small>
              </div>
              <div class="user-progress">
                <small class="fw-semibold">{{ number_format($category->items_sum_current_stock ?? 0) }}</small>
              </div>
            </div>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
  <!--/ Stock by Category -->

  <!-- Today's Stock Movement -->
  <div class="col-md-6 col-lg-4 order-1 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <ul class="nav nav-pills" role="tablist">
          <li class="nav-item">
            <button
              type="button"
              class="nav-link active"
              role="tab"
              data-bs-toggle="tab"
              data-bs-target="#navs-tabs-line-card-stock-today"
              aria-controls="navs-tabs-line-card-stock-today"
              aria-selected="true"
            >
              Today's Stock
            </button>
          </li>
          <li class="nav-item">
            <button 
              type="button" 
              class="nav-link" 
              role="tab"
              data-bs-toggle="tab"
              data-bs-target="#navs-tabs-line-card-stock-month"
              aria-controls="navs-tabs-line-card-stock-month"
              aria-selected="false"
            >
              This Month
            </button>
          </li>
        </ul>
      </div>
      <div class="card-body px-0">
        <div class="tab-content p-0">
          <!-- Today's Stock Tab -->
          <div class="tab-pane fade show active" id="navs-tabs-line-card-stock-today" role="tabpanel">
            <div class="d-flex p-4 pt-3">
              <div class="avatar flex-shrink-0 me-3">
                <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="Stock" />
              </div>
              <div>
                <small class="text-muted d-block">Stock Movement Today</small>
                <div class="d-flex align-items-center">
                  <h6 class="mb-0 me-1">{{ $todayStockIn - $todayStockOut }}</h6>
                  <small class="{{ ($todayStockIn - $todayStockOut) >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                    <i class="bx {{ ($todayStockIn - $todayStockOut) >= 0 ? 'bx-chevron-up' : 'bx-chevron-down' }}"></i>
                    {{ $todayStockIn + $todayStockOut > 0 ? number_format((($todayStockIn - $todayStockOut) / ($todayStockIn + $todayStockOut)) * 100, 1) : 0 }}%
                  </small>
                </div>
              </div>
            </div>
            <div id="stockMovementChartToday"></div>
            <div class="d-flex justify-content-center pt-4 gap-4">
              <div class="d-flex flex-column align-items-center">
                <h6 class="mb-0 text-success">+{{ number_format($todayStockIn) }}</h6>
                <small class="text-muted">Stock In</small>
              </div>
              <div class="d-flex flex-column align-items-center">
                <h6 class="mb-0 text-danger">-{{ number_format($todayStockOut) }}</h6>
                <small class="text-muted">Stock Out</small>
              </div>
            </div>
          </div>

          <!-- This Month Tab -->
          <div class="tab-pane fade" id="navs-tabs-line-card-stock-month" role="tabpanel">
            <div class="d-flex p-4 pt-3">
              <div class="avatar flex-shrink-0 me-3">
                <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="Stock" />
              </div>
              <div>
                <small class="text-muted d-block">Stock Movement This Month</small>
                <div class="d-flex align-items-center">
                  <h6 class="mb-0 me-1">{{ $monthStockIn - $monthStockOut }}</h6>
                  <small class="{{ ($monthStockIn - $monthStockOut) >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                    <i class="bx {{ ($monthStockIn - $monthStockOut) >= 0 ? 'bx-chevron-up' : 'bx-chevron-down' }}"></i>
                    {{ $monthStockIn + $monthStockOut > 0 ? number_format((($monthStockIn - $monthStockOut) / ($monthStockIn + $monthStockOut)) * 100, 1) : 0 }}%
                  </small>
                </div>
              </div>
            </div>
            <div id="stockMovementChartMonth"></div>
            <div class="d-flex justify-content-center pt-4 gap-4">
              <div class="d-flex flex-column align-items-center">
                <h6 class="mb-0 text-success">+{{ number_format($monthStockIn) }}</h6>
                <small class="text-muted">Stock In</small>
              </div>
              <div class="d-flex flex-column align-items-center">
                <h6 class="mb-0 text-danger">-{{ number_format($monthStockOut) }}</h6>
                <small class="text-muted">Stock Out</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Today's Stock Movement -->


  <!-- Recent Transactions -->
  <div class="col-md-6 col-lg-4 order-2 mb-4">
    <div class="card h-100">  
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0 me-2">Recent Transactions</h5>
        <div class="dropdown">
          <button
            class="btn p-0"
            type="button"
            id="transactionID"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            <i class="bx bx-dots-vertical-rounded"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
            <a class="dropdown-item" href="{{ route('stock-transactions.index') }}">View All</a>
            <a class="dropdown-item" href="javascript:void(0);">Export</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <ul class="p-0 m-0">
          @forelse($recentTransactions as $transaction)
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-{{ $transaction->transaction_type == 'IN' ? 'success' : 'danger' }}">
                <i class="bx {{ $transaction->transaction_type == 'IN' ? 'bx-plus' : 'bx-minus' }}"></i>
              </span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="text-muted d-block mb-1">{{ $transaction->item->category->category_name ?? 'No Category' }}</small>
                <h6 class="mb-0">{{ Str::limit($transaction->item->item_name, 20) }}</h6>
                <small class="text-muted">{{ $transaction->created_at->diffForHumans() }}</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0 {{ $transaction->transaction_type == 'IN' ? 'text-success' : 'text-danger' }}">
                  {{ $transaction->transaction_type == 'IN' ? '+' : '-' }}{{ number_format($transaction->quantity) }}
                </h6>
                <span class="text-muted">{{ $transaction->item->unit }}</span>
              </div>
            </div>
          </li>
          @empty
          <li class="text-center py-4">
            <small class="text-muted">No recent transactions</small>
          </li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>
  <!--/ Recent Transactions -->
</div>

<!-- Low Stock Alert -->
@if($lowStockItems > 0)
<div class="row">
  <div class="col-12 mb-4">
    <div class="card border-warning">
      <div class="card-header bg-warning text-white">
        <h5 class="mb-0">
          <i class="bx bx-error-circle me-2"></i>
          Low Stock Alert
        </h5>
      </div>
      <div class="card-body">
        <p class="mb-3">Berikut adalah item dengan stok menipis yang perlu segera diisi ulang:</p>
        <div class="row">
          @foreach($lowStockItemsList as $item)
          <div class="col-md-6 col-lg-4 mb-3">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
              <i class="bx bx-error-circle me-2"></i>
              <div>
                <strong>{{ $item->item_name }}</strong><br>
                <small>
                  Stock: {{ $item->current_stock }} {{ $item->unit }} 
                  (Min: {{ $item->low_stock_threshold }})
                </small>
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
<!-- Custom Charts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Stock by Category Chart
  @if(isset($stockByCategory) && $stockByCategory->count() > 0)
  const stockByCategoryChart = document.querySelector('#stockByCategoryChart');
  if (stockByCategoryChart) {
    const stockByCategoryConfig = {
      series: [{{ $stockByCategory->pluck('items_sum_current_stock')->implode(',') }}],
      labels: {!! $stockByCategory->pluck('category_name')->toJson() !!},
      chart: {
        height: 165,
        type: 'donut'
      },
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        width: 2
      },
      colors: ['#696cff', '#28c76f', '#ff9f43', '#ea5455']
    };

    const stockChart = new ApexCharts(stockByCategoryChart, stockByCategoryConfig);
    stockChart.render();
  }
  @endif

  // Stock Movement Chart - Today
  const stockMovementChartToday = document.querySelector('#stockMovementChartToday');
  if (stockMovementChartToday) {
    const stockMovementConfigToday = {
      series: [{
        name: 'Stock In',
        data: [{{ $todayStockIn }}]
      }, {
        name: 'Stock Out',
        data: [{{ $todayStockOut }}]
      }],
      chart: {
        height: 140,
        type: 'bar',
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '55%',
          endingShape: 'rounded'
        },
      },
      dataLabels: {
        enabled: false
      },
      colors: ['#28c76f', '#ea5455'],
      xaxis: {
        categories: ['Today'],
        labels: {
          show: false
        }
      },
      grid: {
        show: false
      }
    };

    const movementChartToday = new ApexCharts(stockMovementChartToday, stockMovementConfigToday);
    movementChartToday.render();
  }

  // Stock Movement Chart - This Month
  const stockMovementChartMonth = document.querySelector('#stockMovementChartMonth');
  if (stockMovementChartMonth) {
    const stockMovementConfigMonth = {
      series: [{
        name: 'Stock In',
        data: {!! json_encode($monthlyStockIn) !!}
      }, {
        name: 'Stock Out',
        data: {!! json_encode($monthlyStockOut) !!}
      }],
      chart: {
        height: 140,
        type: 'area',
        toolbar: {
          show: false
        }
      },
      dataLabels: {
        enabled: false
      },
      colors: ['#28c76f', '#ea5455'],
      stroke: {
        curve: 'smooth',
        width: 2
      },
      fill: {
        type: 'gradient',
        gradient: {
          shade: 'light',
          type: 'vertical',
          opacityFrom: 0.4,
          opacityTo: 0.1,
        }
      },
      xaxis: {
        categories: {!! json_encode($monthlyDates) !!},
        labels: {
          show: true,
          rotate: -45,
          style: {
            fontSize: '10px'
          }
        }
      },
      yaxis: {
        labels: {
          formatter: function(value) {
            return value.toFixed(0);
          }
        }
      },
      grid: {
        show: false
      },
      tooltip: {
        x: {
          format: 'dd/MM/yyyy'
        }
      }
    };

    const movementChartMonth = new ApexCharts(stockMovementChartMonth, stockMovementConfigMonth);
    movementChartMonth.render();
  }
});
</script>
<!-- Custom Charts -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Stock by Category Chart
    @if(isset($stockByCategory) && $stockByCategory->count() > 0)
    const stockByCategoryChart = document.querySelector('#stockByCategoryChart');
    if (stockByCategoryChart) {
      const stockByCategoryConfig = {
        series: [{{ $stockByCategory->pluck('items_sum_current_stock')->implode(',') }}],
        labels: {!! $stockByCategory->pluck('category_name')->toJson() !!},
        chart: {
          height: 165,
          type: 'donut'
        },
        legend: {
          show: false
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          width: 2
        },
        colors: ['#696cff', '#28c76f', '#ff9f43', '#ea5455']
      };
      
      const stockChart = new ApexCharts(stockByCategoryChart, stockByCategoryConfig);
      stockChart.render();
    }
    @endif

    // Stock Movement Chart
    const stockMovementChart = document.querySelector('#stockMovementChart');
    if (stockMovementChart) {
      const stockMovementConfig = {
        series: [{
          name: 'Stock In',
          data: [{{ $todayStockIn }}]
        }, {
          name: 'Stock Out', 
          data: [{{ $todayStockOut }}]
        }],
        chart: {
          height: 140,
          type: 'bar',
          toolbar: {
            show: false
          }
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded'
          },
        },
        dataLabels: {
          enabled: false
        },
        colors: ['#28c76f', '#ea5455'],
        xaxis: {
          categories: ['Today'],
          labels: {
            show: false
          }
        },
        grid: {
          show: false
        }
      };
      
      const movementChart = new ApexCharts(stockMovementChart, stockMovementConfig);
      movementChart.render();
    }
  });
</script>

@endsection


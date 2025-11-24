@extends('layouts.admin')

@section('title', 'Daily Sales Report')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <h4 class="mb-4">Daily Sales Report</h4>

      <!-- Filter Form -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('sales-report.index') }}">
            <div class="row g-3">
              <!-- Outlet Filter -->
              <div class="col-md-4">
                <label for="outlet_id" class="form-label">Outlet</label>
                <select class="form-select" id="outlet_id" name="outlet_id">
                  <option value="">Semua Outlet</option>
                  @foreach($accessibleOutlets as $outlet)
                    <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                      {{ $outlet->warehouse_name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- Start Date Filter -->
              <div class="col-md-3">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
              </div>

              <!-- End Date Filter -->
              <div class="col-md-3">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
              </div>

              <!-- Submit Button -->
              <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bx bx-search-alt-2 me-1"></i> Filter
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Summary -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <h6 class="card-title">Total Sales</h6>
              <h3 class="text-primary">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <h6 class="card-title">Total Cash</h6>
              <h3 class="text-success">Rp {{ number_format($summary['total_cash'], 0, ',', '.') }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <h6 class="card-title">Total Digital</h6>
              <h3 class="text-info">Rp {{ number_format($summary['total_digital'], 0, ',', '.') }}</h3>
            </div>
          </div>
        </div>
      </div>

      <!-- Sales Report Table -->
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr>
                  <th>Tanggal</th>
                  <th>Outlet</th>
                  <th>Total Sales</th>
                  <th>Pelapor</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($reports as $report)
                  <tr>
                    <td>{{ $report->report_date->format('d M Y') }}</td>
                    <td>{{ $report->outletWarehouse->warehouse_name ?? '-' }}</td>
                    <td>Rp {{ number_format($report->total_sales, 0, ',', '.') }}</td>
                    <td>{{ $report->createdBy->full_name ?? 'N/A' }}</td>
                    <td>
                      <a href="{{ route('sales-report.show', $report->id) }}" class="btn btn-sm btn-info">
                        <i class="bx bx-show-alt"></i> Detail
                      </a>
                      <a href="{{ route('sales-report.edit', $report->id) }}" class="btn btn-sm btn-warning">
                        <i class="bx bx-edit-alt"></i> Edit
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center">Tidak ada laporan yang ditemukan.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="mt-3">
            {{ $reports->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
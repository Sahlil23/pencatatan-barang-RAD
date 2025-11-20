@extends('layouts.admin')

@section('title', 'Detail Laporan Sales - ' . $report->report_date->format('d M Y'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Header & Navigation --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Laporan /</span> Detail Laporan
            </h4>
            <p class="text-muted mb-0">
                {{ $report->outletWarehouse->warehouse_name }} &bull; {{ $report->report_date->format('l, d F Y') }}
            </p>
        </div>
        <a href="{{ route('sales-report.index') }}" class="btn btn-secondary">
            <i class='bx bx-arrow-back me-1'></i> Kembali
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Target Sales</small>
                    <h5 class="mb-0">Rp {{ number_format($report->target_sales, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card h-100 bg-primary text-white">
                <div class="card-body">
                    <small class="text-white d-block mb-1 opacity-75">Total Sales</small>
                    <h4 class="mb-0 text-white">Rp {{ number_format($report->total_sales, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-success d-block mb-1 fw-semibold">Total Cash</small>
                    <h5 class="mb-0">Rp {{ number_format($report->payment_cash, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-info d-block mb-1 fw-semibold">Total Digital</small>
                    {{-- Menggunakan Accessor yang kita buat di Model, atau hitung manual --}}
                    <h5 class="mb-0">Rp {{ number_format($report->total_digital_payment, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-primary">
        <div class="card-header bg-label-primary d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary"><i class='bx bx-stats me-2'></i>Performance Analysis</h5>
            <small class="text-muted">
                Comparing: <strong>{{ $report->report_date->format('d M') }}</strong> vs 
                <strong>{{ $lastWeekReport ? $lastWeekReport->report_date->format('d M') : 'No Data' }}</strong>
            </small>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr class="table-light">
                        <th>Metric</th>
                        <th class="text-end">Today ({{ $report->report_date->format('d M') }})</th>
                        <th class="text-end">Last Week ({{ $lastWeekReport ? $lastWeekReport->report_date->format('d M') : '-' }})</th>
                        <th class="text-center">Growth</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        function renderRow($label, $today, $lastWeek) {
                            // Hitung Growth
                            $growth = 0;
                            $isPositive = true;
                            $textClass = 'text-muted';
                            $icon = 'bx-minus';
                            
                            if ($lastWeek > 0) {
                                $growth = (($today - $lastWeek) / $lastWeek) * 100;
                                $isPositive = $growth >= 0;
                                $textClass = $isPositive ? 'text-success' : 'text-danger';
                                $icon = $isPositive ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt';
                            } elseif ($today > 0) {
                                $growth = 100; // Dari 0 ke Ada = 100%
                                $textClass = 'text-success';
                                $icon = 'bx-up-arrow-alt';
                            }
                            
                            // Format Rupiah
                            $todayFmt = 'Rp ' . number_format($today, 0, ',', '.');
                            $lastWeekFmt = $lastWeek ? 'Rp ' . number_format($lastWeek, 0, ',', '.') : '-';
                            $growthFmt = number_format(abs($growth)) . '%';
                            
                            echo "
                            <tr>
                                <td><strong>{$label}</strong></td>
                                <td class='text-end'>{$todayFmt}</td>
                                <td class='text-end text-muted'>{$lastWeekFmt}</td>
                                <td class='text-center {$textClass} fw-bold'>
                                    <i class='bx {$icon}'></i> {$growthFmt}
                                </td>
                            </tr>";
                        }
                        
                        // Siapkan Data Last Week (Handle jika null)
                        $lw_target = $lastWeekReport->target_sales ?? 0;
                        $lw_opening = $lastWeekReport->sales_shift_opening ?? 0;
                        $lw_closing = $lastWeekReport->sales_shift_closing ?? 0;
                        $lw_midnight = $lastWeekReport->sales_shift_midnight ?? 0;
                        $lw_total = $lastWeekReport->total_sales ?? 0;
                    @endphp

                    @php renderRow('Target Sales', $report->target_sales, $lw_target) @endphp
                    @php renderRow('Shift Opening', $report->sales_shift_opening, $lw_opening) @endphp
                    @php renderRow('Shift Closing', $report->sales_shift_closing, $lw_closing) @endphp
                    @php renderRow('Shift Midnight', $report->sales_shift_midnight, $lw_midnight) @endphp
                    
                    <tr class="table-secondary">
                        <td><strong>TOTAL SALES</strong></td>
                        <td class="text-end fw-bold text-primary">Rp {{ number_format($report->total_sales, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($lw_total, 0, ',', '.') }}</td>
                        <td class="text-center fw-bold">
                            @php
                                $growth = ($lw_total > 0) ? (($report->total_sales - $lw_total) / $lw_total) * 100 : 0;
                                $color = $growth >= 0 ? 'text-success' : 'text-danger';
                                $arrow = $growth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt';
                            @endphp
                            <span class="{{ $color }}">
                                <i class='bx {{ $arrow }}'></i> {{ number_format(abs($growth)) }}%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        {{-- Kolom Kiri: Rincian Sales & Payment --}}
        <div class="col-lg-8">
            
            {{-- Sales Per Shift --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bx-time-five me-2'></i>Sales Per Shift</h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead>
                            <tr class="table-light">
                                <th>Shift Opening</th>
                                <th>Shift Closing</th>
                                <th>Shift Midnight</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Rp {{ number_format($report->sales_shift_opening, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($report->sales_shift_closing, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($report->sales_shift_midnight, 0, ',', '.') }}</td>
                                <td class="fw-bold">Rp {{ number_format($report->total_sales, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Payment Details --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bx-wallet me-2'></i>Rincian Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Cash</span>
                                    <span class="fw-bold">Rp {{ number_format($report->payment_cash, 0, ',', '.') }}</span>
                                </li>
                                @php $digital = $report->payment_details_digital; @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>EDC Mandiri</span>
                                    <span>Rp {{ number_format($digital['edc_mandiri'] ?? 0, 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>EDC BCA</span>
                                    <span>Rp {{ number_format($digital['edc_bca'] ?? 0, 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>EDC BRI</span>
                                    <span>Rp {{ number_format($digital['edc_bri'] ?? 0, 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>EDC BNI</span>
                                    <span>Rp {{ number_format($digital['edc_bni'] ?? 0, 0, ',', '.') }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>QR Mandiri</span>
                                    <span>Rp {{ number_format($digital['qr_mandiri'] ?? 0, 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>QR BCA</span>
                                    <span>Rp {{ number_format($digital['qr_bca'] ?? 0, 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>QR BRI</span>
                                    <span>Rp {{ number_format($digital['qr_bri'] ?? 0, 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>QR BNI</span>
                                    <span>Rp {{ number_format($digital['qr_bni'] ?? 0, 0, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center text-danger">
                                    <span>Void / Refund</span>
                                    <span>(Rp {{ number_format($report->payment_void_refund, 0, ',', '.') }})</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delivery Platforms --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bxs-truck me-2'></i>Delivery Platforms</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Platform</th>
                                <th>Sales Today</th>
                                <th>TC Today</th>
                                <th>MTD Sales</th>
                                <th>MTD TC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $delivery = $report->delivery_platforms; @endphp
                            <tr>
                                <td class="fw-bold text-success">GoFood</td>
                                <td>Rp {{ number_format($delivery['gofood']['sales'] ?? 0, 0, ',', '.') }}</td>
                                <td>{{ $delivery['gofood']['tc'] ?? 0 }}</td>
                                <td>Rp {{ number_format($delivery['gofood']['mtd_sales'] ?? 0, 0, ',', '.') }}</td>
                                <td>{{ $delivery['gofood']['mtd_tc'] ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-success">GrabFood</td>
                                <td>Rp {{ number_format($delivery['grabfood']['sales'] ?? 0, 0, ',', '.') }}</td>
                                <td>{{ $delivery['grabfood']['tc'] ?? 0 }}</td>
                                <td>Rp {{ number_format($delivery['grabfood']['mtd_sales'] ?? 0, 0, ',', '.') }}</td>
                                <td>{{ $delivery['grabfood']['mtd_tc'] ?? 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Info Tambahan --}}
        <div class="col-lg-4">
            
            {{-- Statistik Agregat --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistik Bulanan & Tamu</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">MTD Sales (Month to Date)</label>
                        <h5 class="mb-0">Rp {{ number_format($report->mtd_sales, 0, ',', '.') }}</h5>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label text-muted">Guest Today</label>
                            <h5 class="mb-0">{{ $report->guest_count_today }}</h5>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted">MTD Guest</label>
                            <h5 class="mb-0">{{ $report->mtd_guest_count }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Staff --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Staff on Duty</h5>
                </div>
                <ul class="list-group list-group-flush">
                    @php $staff = $report->staff_on_duty; @endphp
                    <li class="list-group-item">
                        <small class="text-muted d-block">Opening</small>
                        <strong>{{ $staff['opening'] ?? '-' }}</strong>
                    </li>
                    <li class="list-group-item">
                        <small class="text-muted d-block">Closing</small>
                        <strong>{{ $staff['closing'] ?? '-' }}</strong>
                    </li>
                    <li class="list-group-item">
                        <small class="text-muted d-block">Midnight</small>
                        <strong>{{ $staff['midnight'] ?? '-' }}</strong>
                    </li>
                    <li class="list-group-item bg-light">
                        <small class="text-muted d-block">Dibuat Oleh</small>
                        <div class="d-flex align-items-center mt-1">
                            <div class="avatar avatar-xs me-2">
                                <span class="avatar-initial rounded-circle bg-secondary text-white">
                                    {{ substr($report->createdBy->full_name ?? 'U', 0, 1) }}
                                </span>
                            </div>
                            <span>{{ $report->createdBy->full_name ?? 'Unknown' }}</span>
                        </div>
                        <small class="text-muted d-block mt-1">{{ $report->created_at->format('d M Y H:i') }}</small>
                    </li>
                </ul>
            </div>

            {{-- Notes --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Catatan Operasional</h5>
                </div>
                <div class="card-body">
                    @php $notes = $report->notes; @endphp
                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary">Item Fast Moving</label>
                        <p class="mb-0 bg-light p-2 rounded">{{ $notes['fast_moving_items'] ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="form-label fw-bold text-danger">Kejadian Penting</label>
                        <p class="mb-0 bg-light p-2 rounded" style="white-space: pre-line">{{ $notes['important_events'] ?? '-' }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
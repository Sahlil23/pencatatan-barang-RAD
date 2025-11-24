@extends('layouts.admin')

@section('title', 'Edit Laporan Sales Harian')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Laporan /</span> Edit Sales Harian
            </h4>
            <div class="text-muted small">
                {{ $report->outletWarehouse->warehouse_name }} - {{ $report->report_date->format('d M Y') }}
            </div>
        </div>
        <a href="{{ route('sales-report.show', $report->id) }}" class="btn btn-secondary">
            <i class='bx bx-arrow-back me-1'></i> Batal
        </a>
    </div>

    {{-- Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger mb-4 alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">Gagal Memperbarui Laporan:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Form Edit --}}
    <form action="{{ route('sales-report.update', $report->id) }}" method="POST" id="salesForm">
        @csrf
        @method('PUT') {{-- Wajib untuk Update --}}

        <div class="row">
            
            {{-- KOLOM KIRI --}}
            <div class="col-lg-8">
                
                {{-- 1. Identitas & Sales Shift --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class='bx bx-store me-2'></i>Informasi & Total Sales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Outlet <span class="text-danger">*</span></label>
                                <select name="outlet_warehouse_id" class="form-select" required>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}" 
                                            {{ (old('outlet_warehouse_id', $report->outlet_warehouse_id) == $outlet->id) ? 'selected' : '' }}>
                                            {{ $outlet->warehouse_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Laporan <span class="text-danger">*</span></label>
                                <input type="date" name="report_date" class="form-control" 
                                       value="{{ old('report_date', $report->report_date->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Target Sales Today</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">Rp</span>
                                    {{-- Menggunakan + 0 untuk menghilangkan desimal .00 jika bulat --}}
                                    <input type="text" name="target_sales" class="form-control rupiah-input" 
                                           value="{{ old('target_sales', $report->target_sales + 0) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6"></div>

                            {{-- Shift Inputs --}}
                            <div class="col-md-4">
                                <label class="form-label">Shift Opening</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="sales_shift_opening" class="form-control rupiah-input calc-sales" 
                                           value="{{ old('sales_shift_opening', $report->sales_shift_opening + 0) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shift Closing</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="sales_shift_closing" class="form-control rupiah-input calc-sales" 
                                           value="{{ old('sales_shift_closing', $report->sales_shift_closing + 0) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shift Midnight</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="sales_shift_midnight" class="form-control rupiah-input calc-sales" 
                                           value="{{ old('sales_shift_midnight', $report->sales_shift_midnight + 0) }}">
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="alert alert-primary d-flex align-items-center justify-content-between">
                                    <span class="fw-bold">Total Sales (Calculated):</span>
                                    <div class="input-group w-50">
                                        <span class="input-group-text bg-white border-0 fw-bold">Rp</span>
                                        {{-- Readonly, dihitung otomatis oleh JS --}}
                                        <input type="text" name="total_sales" id="total_sales" 
                                               class="form-control border-0 bg-white fw-bold fs-4 text-end text-primary" 
                                               value="{{ old('total_sales', $report->total_sales + 0) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Rincian Pembayaran --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <h5 class="mb-0"><i class='bx bx-wallet me-2'></i>Rincian Pembayaran</h5>
                        <small id="balance-status" class="badge bg-secondary">Checking...</small>
                    </div>
                    <div class="card-body pt-4">
                        
                        {{-- Ambil data JSON Digital --}}
                        @php $digital = $report->payment_details_digital ?? []; @endphp

                        {{-- Cash --}}
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold">CASH</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="payment_cash" class="form-control rupiah-input calc-payment" 
                                           value="{{ old('payment_cash', $report->payment_cash + 0) }}">
                                </div>
                            </div>
                        </div>
                        <hr>

                        {{-- EDC Machines --}}
                        <h6 class="text-muted small text-uppercase mb-3">EDC Machine</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">EDC Mandiri</label>
                                <input type="text" name="edc_mandiri" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('edc_mandiri', $digital['edc_mandiri'] ?? 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">EDC BCA</label>
                                <input type="text" name="edc_bca" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('edc_bca', $digital['edc_bca'] ?? 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">EDC BRI</label>
                                <input type="text" name="edc_bri" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('edc_bri', $digital['edc_bri'] ?? 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">EDC BNI</label>
                                <input type="text" name="edc_bni" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('edc_bni', $digital['edc_bni'] ?? 0) }}">
                            </div>
                        </div>

                        {{-- QRIS --}}
                        <h6 class="text-muted small text-uppercase mb-3">QRIS</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">QR Mandiri</label>
                                <input type="text" name="qr_mandiri" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('qr_mandiri', $digital['qr_mandiri'] ?? 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QR BCA</label>
                                <input type="text" name="qr_bca" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('qr_bca', $digital['qr_bca'] ?? 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QR BRI</label>
                                <input type="text" name="qr_bri" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('qr_bri', $digital['qr_bri'] ?? 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QR BNI</label>
                                <input type="text" name="qr_bni" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('qr_bni', $digital['qr_bni'] ?? 0) }}">
                            </div>
                        </div>

                        {{-- Others --}}
                        <h6 class="text-muted small text-uppercase mb-3">Lainnya</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Transfer Mandiri</label>
                                <input type="text" name="transfer_mandiri" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('transfer_mandiri', $digital['transfer_mandiri'] ?? 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gobizz Wallet</label>
                                <input type="text" name="gobizz_wallet" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('gobizz_wallet', $digital['gobizz_wallet'] ?? 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Compliment</label>
                                <input type="text" name="compliment" class="form-control rupiah-input calc-payment" 
                                       value="{{ old('compliment', $digital['compliment'] ?? 0) }}">
                            </div>
                        </div>

                        <hr>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-danger">VOID / REFUND</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-danger text-white">Rp</span>
                                    <input type="text" name="payment_void_refund" class="form-control rupiah-input border-danger text-danger" 
                                           value="{{ old('payment_void_refund', $report->payment_void_refund + 0) }}">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                         <span class="fw-semibold">Total Pembayaran (Input):</span>
                         <h4 class="mb-0" id="total_payment_display">Rp 0</h4>
                    </div>
                </div>

                {{-- 3. Delivery Platforms --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0"><i class='bx bxs-truck me-2'></i>Delivery Online</h5></div>
                    <div class="card-body">
                        {{-- Ambil Data JSON Delivery --}}
                        @php $delivery = $report->delivery_platforms ?? []; @endphp

                        <div class="row g-3">
                            {{-- GoFood --}}
                            <div class="col-md-6 border-end">
                                <h6 class="text-success fw-bold">GO FOOD</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Sales Today</label>
                                    {{-- Tambahkan class 'calc-delivery-sales' --}}
                                    <input type="text" name="gofood_sales" class="form-control rupiah-input calc-delivery-sales" 
                                           value="{{ old('gofood_sales', $delivery['gofood']['sales'] ?? 0) }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">TC Today</label>
                                    <input type="text" name="gofood_tc" class="form-control form-control-sm" 
                                           value="{{ old('gofood_tc', $delivery['gofood']['tc'] ?? 0) }}">
                                </div>
                            </div>

                            {{-- GrabFood --}}
                            <div class="col-md-6">
                                <h6 class="text-success fw-bold">GRAB FOOD</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Sales Today</label>
                                    {{-- Tambahkan class 'calc-delivery-sales' --}}
                                    <input type="text" name="grabfood_sales" class="form-control rupiah-input calc-delivery-sales" 
                                           value="{{ old('grabfood_sales', $delivery['grabfood']['sales'] ?? 0) }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">TC Today</label>
                                    <input type="number" name="grabfood_tc" class="form-control form-control-sm" 
                                           value="{{ old('grabfood_tc', $delivery['grabfood']['tc'] ?? 0) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN --}}
            <div class="col-lg-4">
                
                {{-- 4. Agregat --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Statistik</h5></div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Dine-In Guest Count (Today)</label>
                                <input type="number" name="guest_count_today" class="form-control" value="{{ old('guest_count_today') }}">
                                <div class="form-text small">Hanya masukkan jumlah tamu Dine-in. Total TC akan otomatis ditambah dengan Delivery.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. Staff --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Staff</h5></div>
                    <div class="card-body">
                        @php $staff = $report->staff_on_duty ?? []; @endphp
                        <div class="mb-3">
                            <label class="form-label">Opening</label>
                            <input type="text" name="staff_opening" class="form-control" 
                                   value="{{ old('staff_opening', $staff['opening'] ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Closing</label>
                            <input type="text" name="staff_closing" class="form-control" 
                                   value="{{ old('staff_closing', $staff['closing'] ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Midnight</label>
                            <input type="text" name="staff_midnight" class="form-control" 
                                   value="{{ old('staff_midnight', $staff['midnight'] ?? '') }}">
                        </div>
                    </div>
                </div>

                {{-- 6. Notes --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Catatan</h5></div>
                    <div class="card-body">
                        @php $notes = $report->notes ?? []; @endphp
                        <div class="mb-3">
                            <label class="form-label fw-bold text-primary">Item Fast Moving</label>
                            <textarea name="fast_moving_items" class="form-control" rows="3">{{ old('fast_moving_items', $notes['fast_moving_items'] ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Kejadian Penting</label>
                            <textarea name="important_events" class="form-control" rows="3">{{ old('important_events', $notes['important_events'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class='bx bx-save me-1'></i> Update Laporan
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

{{-- Gunakan Script yang Sama Persis dengan Create --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selektor Input
        const shiftInputs = document.querySelectorAll('.calc-sales');
        const paymentInputs = document.querySelectorAll('.calc-payment');
        const deliverySalesInputs = document.querySelectorAll('.calc-delivery-sales'); // Input Delivery
        
        const totalSalesInput = document.getElementById('total_sales');
        const totalPaymentDisplay = document.getElementById('total_payment_display');
        const balanceStatus = document.getElementById('balance-status');
        const submitBtn = document.getElementById('submitBtn');

        // Format Rupiah
        const formatRupiah = (num) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
        };

        // 1. Hitung Total Sales (Shift)
        const calculateTotalSales = () => {
            let total = 0;
            shiftInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            totalSalesInput.value = total;
            checkBalance();
        };

        // 2. Hitung Total Settlement (Cash + Digital + Delivery Sales)
        const calculateTotalPayment = () => {
            let total = 0;
            
            // A. Hitung Pembayaran Biasa (Cash/EDC/QR)
            paymentInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            
            // B. Tambahkan Sales Delivery (GoFood/GrabFood)
            deliverySalesInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            totalPaymentDisplay.innerText = formatRupiah(total);
            return total;
        };

        // 3. Cek Balance (Sales vs Payment+Delivery)
        const checkBalance = () => {
            const sales = parseFloat(totalSalesInput.value) || 0;
            const totalSettlement = calculateTotalPayment();
            const diff = Math.abs(sales - totalSettlement);

            if (diff <= 100) { // Toleransi Rp 100
                balanceStatus.className = 'badge bg-success';
                balanceStatus.innerHTML = '<i class="bx bx-check"></i> BALANCED';
                submitBtn.disabled = false;
            } else {
                balanceStatus.className = 'badge bg-danger';
                balanceStatus.innerHTML = `NOT BALANCED (Selisih: ${formatRupiah(sales - totalSettlement)})`;
                // submitBtn.disabled = true; 
            }
        };

        // Event Listeners
        shiftInputs.forEach(input => input.addEventListener('input', calculateTotalSales));
        paymentInputs.forEach(input => input.addEventListener('input', checkBalance));
        deliverySalesInputs.forEach(input => input.addEventListener('input', checkBalance));

        // Init (Penting untuk Edit: Hitung ulang saat halaman dimuat)
        calculateTotalSales();
    });
</script>
@endpush
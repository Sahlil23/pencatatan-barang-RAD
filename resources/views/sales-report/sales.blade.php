@extends('layouts.admin')

@section('title', 'Input Laporan Sales Harian')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Laporan /</span> Input Sales Harian
        </h4>
        <a href="{{ route('sales-report.index') }}" class="btn btn-secondary">
            <i class='bx bx-arrow-back me-1'></i> Kembali
        </a>
    </div>

    {{-- Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger mb-4 alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">Gagal Menyimpan Laporan:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('sales-report.store') }}" method="POST" id="salesForm">
        @csrf

        <div class="row">
            
            {{-- KOLOM KIRI: Data Utama --}}
            <div class="col-lg-8">
                
                {{-- 1. Identitas & Sales Shift --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class='bx bx-store me-2'></i>Informasi & Total Sales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Pilih Outlet <span class="text-danger">*</span></label>
                                <select name="outlet_warehouse_id" class="form-select" required autofocus>
                                    <option value="">-- Pilih Outlet --</option>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}" {{ old('outlet_warehouse_id') == $outlet->id ? 'selected' : '' }}>
                                            {{ $outlet->warehouse_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Laporan <span class="text-danger">*</span></label>
                                <input type="date" name="report_date" class="form-control" value="{{ old('report_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Target Sales Today</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="target_sales" class="form-control" placeholder="0" value="{{ old('target_sales') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{-- Spacer --}}
                            </div>

                            {{-- Shift Inputs --}}
                            <div class="col-md-4">
                                <label class="form-label">Shift Opening</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="sales_shift_opening" class="form-control calc-sales" placeholder="0" value="{{ old('sales_shift_opening') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shift Closing</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="sales_shift_closing" class="form-control calc-sales" placeholder="0" value="{{ old('sales_shift_closing') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shift Midnight</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="sales_shift_midnight" class="form-control calc-sales" placeholder="0" value="{{ old('sales_shift_midnight') }}">
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="alert alert-primary d-flex align-items-center justify-content-between">
                                    <span class="fw-bold">Total Sales (Calculated):</span>
                                    <div class="input-group w-50">
                                        <span class="input-group-text bg-white border-0 fw-bold">Rp</span>
                                        {{-- Readonly agar tidak diedit manual, harus dari shift --}}
                                        <input type="number" name="total_sales" id="total_sales" class="form-control border-0 bg-white fw-bold fs-4 text-end text-primary" 
                                               value="{{ old('total_sales', 0) }}" readonly>
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
                        
                        {{-- Cash --}}
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold">CASH</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="payment_cash" class="form-control calc-payment" placeholder="0" value="{{ old('payment_cash') }}">
                                </div>
                            </div>
                        </div>
                        <hr>

                        {{-- EDC Machines --}}
                        <h6 class="text-muted small text-uppercase mb-3">EDC Machine</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">EDC Mandiri</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="edc_mandiri" class="form-control calc-payment" value="{{ old('edc_mandiri') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">EDC BCA</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="edc_bca" class="form-control calc-payment" value="{{ old('edc_bca') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">EDC BRI</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="edc_bri" class="form-control calc-payment" value="{{ old('edc_bri') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">EDC BNI</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="edc_bni" class="form-control calc-payment" value="{{ old('edc_bni') }}">
                                </div>
                            </div>
                        </div>

                        {{-- QRIS --}}
                        <h6 class="text-muted small text-uppercase mb-3">QRIS</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">QR Mandiri</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="qr_mandiri" class="form-control calc-payment" value="{{ old('qr_mandiri') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QR BCA</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="qr_bca" class="form-control calc-payment" value="{{ old('qr_bca') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QR BRI</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="qr_bri" class="form-control calc-payment" value="{{ old('qr_bri') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QR BNI</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="qr_bni" class="form-control calc-payment" value="{{ old('qr_bni') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Others --}}
                        <h6 class="text-muted small text-uppercase mb-3">Lainnya</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Transfer Mandiri</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="transfer_mandiri" class="form-control calc-payment" value="{{ old('transfer_mandiri') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gobizz Wallet</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="gobizz_wallet" class="form-control calc-payment" value="{{ old('gobizz_wallet') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Compliment</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="compliment" class="form-control calc-payment" value="{{ old('compliment') }}">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-danger">VOID / REFUND</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-danger text-white">Rp</span>
                                    <input type="number" name="payment_void_refund" class="form-control border-danger text-danger" placeholder="0" value="{{ old('payment_void_refund') }}">
                                </div>
                                <div class="form-text">Hanya sebagai catatan, tidak mempengaruhi total pembayaran.</div>
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
                    <div class="card-header">
                        <h5 class="mb-0"><i class='bx bxs-truck me-2'></i>Delivery Online</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- GoFood --}}
                            <div class="col-md-6 border-end">
                                <h6 class="text-success fw-bold"><i class='bx bxl-go-lang me-1'></i>GO FOOD</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Sales Today</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="gofood_sales" class="form-control" value="{{ old('gofood_sales') }}">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">TC Today</label>
                                    <input type="number" name="gofood_tc" class="form-control form-control-sm" value="{{ old('gofood_tc') }}">
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">MTD Sales</label>
                                        <input type="number" name="gofood_mtd_sales" class="form-control form-control-sm" value="{{ old('gofood_mtd_sales') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">MTD TC</label>
                                        <input type="number" name="gofood_mtd_tc" class="form-control form-control-sm" value="{{ old('gofood_mtd_tc') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- GrabFood --}}
                            <div class="col-md-6">
                                <h6 class="text-success fw-bold"><i class='bx bxs-car me-1'></i>GRAB FOOD</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Sales Today</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="grabfood_sales" class="form-control" value="{{ old('grabfood_sales') }}">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">TC Today</label>
                                    <input type="number" name="grabfood_tc" class="form-control form-control-sm" value="{{ old('grabfood_tc') }}">
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">MTD Sales</label>
                                        <input type="number" name="grabfood_mtd_sales" class="form-control form-control-sm" value="{{ old('grabfood_mtd_sales') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">MTD TC</label>
                                        <input type="number" name="grabfood_mtd_tc" class="form-control form-control-sm" value="{{ old('grabfood_mtd_tc') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN: Data Tambahan --}}
            <div class="col-lg-4">
                
                {{-- 4. Agregat --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Statistik & Tamu</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">MTD Sales (Month To Date)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="mtd_sales" class="form-control" value="{{ old('mtd_sales') }}">
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Guest Today</label>
                                <input type="number" name="guest_count_today" class="form-control" value="{{ old('guest_count_today') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">MTD Guest</label>
                                <input type="number" name="mtd_guest_count" class="form-control" value="{{ old('mtd_guest_count') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. Staff --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Staff on Duty</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Opening (Nama)</label>
                            <input type="text" name="staff_opening" class="form-control" placeholder="Contoh: Nuriah" value="{{ old('staff_opening') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Closing (Nama)</label>
                            <input type="text" name="staff_closing" class="form-control" placeholder="Contoh: Diva" value="{{ old('staff_closing') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Midnight (Nama)</label>
                            <input type="text" name="staff_midnight" class="form-control" placeholder="Kosongkan jika tidak ada" value="{{ old('staff_midnight') }}">
                        </div>
                    </div>
                </div>

                {{-- 6. Notes --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Catatan Operasional</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-primary">Item Fast Moving</label>
                            <textarea name="fast_moving_items" class="form-control" rows="3" placeholder="List item terlaris hari ini...">{{ old('fast_moving_items') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Kejadian Penting</label>
                            <textarea name="important_events" class="form-control" rows="3" placeholder="Komplain, kerusakan alat, dll...">{{ old('important_events') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class='bx bx-save me-1'></i> Simpan Laporan
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const shiftInputs = document.querySelectorAll('.calc-sales');
        const paymentInputs = document.querySelectorAll('.calc-payment');
        const totalSalesInput = document.getElementById('total_sales');
        const totalPaymentDisplay = document.getElementById('total_payment_display');
        const balanceStatus = document.getElementById('balance-status');
        const submitBtn = document.getElementById('submitBtn');

        // Fungsi format rupiah sederhana untuk display
        const formatRupiah = (num) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
        };

        // 1. Hitung Total Sales (Shift Opening + Closing + Midnight)
        const calculateTotalSales = () => {
            let total = 0;
            shiftInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            totalSalesInput.value = total; // Set nilai ke input hidden/readonly
            checkBalance(); // Cek ulang balance
        };

        // 2. Hitung Total Pembayaran (Cash + Semua Digital)
        const calculateTotalPayment = () => {
            let total = 0;
            paymentInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            totalPaymentDisplay.innerText = formatRupiah(total);
            return total;
        };

        // 3. Cek apakah Sales == Payment
        const checkBalance = () => {
            const sales = parseFloat(totalSalesInput.value) || 0;
            const payments = calculateTotalPayment();
            const diff = Math.abs(sales - payments);

            if (diff <= 100) { // Toleransi kecil
                balanceStatus.className = 'badge bg-success';
                balanceStatus.innerHTML = '<i class="bx bx-check"></i> BALANCED';
                submitBtn.disabled = false;
            } else {
                balanceStatus.className = 'badge bg-danger';
                balanceStatus.innerHTML = `NOT BALANCED (Selisih: ${formatRupiah(sales - payments)})`;
                // Opsional: Disable tombol submit jika tidak balance
                // submitBtn.disabled = true; 
            }
        };

        // Pasang Event Listener
        shiftInputs.forEach(input => input.addEventListener('input', calculateTotalSales));
        paymentInputs.forEach(input => input.addEventListener('input', checkBalance));

        // Jalankan sekali saat load (untuk mode edit/old input)
        calculateTotalSales();
    });
</script>
@endpush
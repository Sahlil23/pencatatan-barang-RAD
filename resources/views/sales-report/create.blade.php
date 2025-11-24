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
                                    <input  type="text" name="target_sales" class="form-control rupiah-input" placeholder="0" value="{{ old('target_sales') }}" required>
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
                                    <input type="text" name="sales_shift_opening" class="form-control calc-sales rupiah-input" placeholder="0" value="{{ old('sales_shift_opening') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shift Closing</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="sales_shift_closing" class="form-control calc-sales rupiah-input" placeholder="0" value="{{ old('sales_shift_closing') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shift Midnight</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="sales_shift_midnight" class="form-control calc-sales rupiah-input" placeholder="0" value="{{ old('sales_shift_midnight') }}">
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="alert alert-primary d-flex align-items-center justify-content-between">
                                    <span class="fw-bold">Total Sales (Calculated):</span>
                                    <div class="input-group w-50">
                                        <span class="input-group-text bg-white border-0 fw-bold">Rp</span>
                                        {{-- Readonly agar tidak diedit manual, harus dari shift --}}
                                        <input type="text" name="total_sales" id="total_sales" class="form-control border-0 bg-white fw-bold fs-4 text-end text-primary rupiah-input" 
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
                                    <input type="text" name="payment_cash" class="form-control calc-payment rupiah-input" placeholder="0" value="{{ old('payment_cash') }}">
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
                                    <input type="text" name="edc_mandiri" class="form-control calc-payment rupiah-input" value="{{ old('edc_mandiri') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">EDC BCA</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="edc_bca" class="form-control calc-payment rupiah-input" value="{{ old('edc_bca') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">EDC BRI</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="edc_bri" class="form-control calc-payment rupiah-input" value="{{ old('edc_bri') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">EDC BNI</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="edc_bni" class="form-control calc-payment rupiah-input" value="{{ old('edc_bni') }}">
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
                                    <input type="text" name="qr_mandiri" class="form-control calc-payment rupiah-input" value="{{ old('qr_mandiri') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QR BCA</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="qr_bca" class="form-control calc-payment rupiah-input" value="{{ old('qr_bca') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QR BRI</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="qr_bri" class="form-control calc-payment rupiah-input" value="{{ old('qr_bri') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QR BNI</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="qr_bni" class="form-control calc-payment rupiah-input" value="{{ old('qr_bni') }}">
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
                                    <input type="text" name="transfer_mandiri" class="form-control calc-payment rupiah-input" value="{{ old('transfer_mandiri') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gobizz Wallet</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="gobizz_wallet" class="form-control calc-payment rupiah-input" value="{{ old('gobizz_wallet') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Compliment</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="compliment" class="form-control calc-payment rupiah-input" value="{{ old('compliment') }}">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label text-danger">VOID / REFUND</label>
                            <div class="col-sm-9">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-danger text-white">Rp</span>
                                    <input type="text" name="payment_void_refund" class="form-control border-danger text-danger rupiah-input" placeholder="0" value="{{ old('payment_void_refund') }}">
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
                                        <input type="text" name="gofood_sales" class="form-control calc-delivery-sales rupiah-input" value="{{ old('gofood_sales') }}">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">TC Today</label>
                                    <input type="text" name="gofood_tc" class="form-control form-control-sm" value="{{ old('gofood_tc') }}">
                                </div>
                            </div>

                            {{-- GrabFood --}}
                            <div class="col-md-6">
                                <h6 class="text-success fw-bold"><i class='bx bxs-car me-1'></i>GRAB FOOD</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Sales Today</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="grabfood_sales" class="form-control calc--delivery-sales rupiah-input" value="{{ old('grabfood_sales') }}">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">TC Today</label>
                                    <input type="number" name="grabfood_tc" class="form-control form-control-sm" value="{{ old('grabfood_tc') }}">
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

                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Dine-In Guest Count (Today)</label>
                                <input type="text" name="guest_count_today" class="form-control " value="{{ old('guest_count_today') }}">
                                <div class="form-text small">Hanya masukkan jumlah tamu Dine-in. Total TC akan otomatis ditambah dengan Delivery.</div>
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
        // --- 1. LOGIKA FORMAT RUPIAH ---
        
        const rupiahInputs = document.querySelectorAll('.rupiah-input');

        // Fungsi Format: 1000000 -> 1.000.000
        const formatRupiah = (angka, prefix) => {
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
        };

        // Pasang event listener ke semua input rupiah
        rupiahInputs.forEach(input => {
            // Format saat user mengetik
            input.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
            
            // Format saat halaman dimuat (untuk Edit / Old Input)
            if (input.value) {
                input.value = formatRupiah(input.value);
            }
        });

        // Fungsi Un-Format: 1.000.000 -> 1000000 (Untuk kalkulasi JS)
        const cleanNumber = (val) => {
            if (!val) return 0;
            return parseFloat(val.toString().replace(/\./g, '')) || 0;
        };


        // --- 2. LOGIKA KALKULASI (Disesuaikan dengan Format Baru) ---

        const shiftInputs = document.querySelectorAll('.calc-sales');
        const paymentInputs = document.querySelectorAll('.calc-payment');
        const deliverySalesInputs = document.querySelectorAll('.calc-delivery-sales');
        
        const totalSalesInput = document.getElementById('total_sales'); // Ini hidden input asli
        const totalSalesDisplay = document.getElementById('total_sales_display'); // Buat input dummy untuk display jika perlu
        const totalPaymentDisplay = document.getElementById('total_payment_display');
        const balanceStatus = document.getElementById('balance-status');
        const submitBtn = document.getElementById('submitBtn');

        const formatDisplay = (num) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
        };

        // Hitung Total Sales
        const calculateTotalSales = () => {
            let total = 0;
            shiftInputs.forEach(input => {
                total += cleanNumber(input.value); // Gunakan cleanNumber
            });
                     
            
            if(totalSalesInput) {
                // Jika total_sales input type="text" dan class="rupiah-input"
                totalSalesInput.value = formatRupiah(total.toString());
            }
            
            checkBalance();
        };

        // Hitung Total Payment
        const calculateTotalPayment = () => {
            let total = 0;
            paymentInputs.forEach(input => {
                total += cleanNumber(input.value);
            });
            deliverySalesInputs.forEach(input => {
                total += cleanNumber(input.value);
            });

            totalPaymentDisplay.innerText = formatDisplay(total);
            return total;
        };

        // Cek Balance
        const checkBalance = () => {
            // Ambil nilai Total Sales (bersihkan titiknya dulu)
            const sales = cleanNumber(totalSalesInput.value);
            const totalSettlement = calculateTotalPayment();
            const diff = Math.abs(sales - totalSettlement);

            if (diff <= 100) {
                balanceStatus.className = 'badge bg-success';
                balanceStatus.innerHTML = '<i class="bx bx-check"></i> BALANCED';
                submitBtn.disabled = false;
            } else {
                balanceStatus.className = 'badge bg-danger';
                balanceStatus.innerHTML = `NOT BALANCED (Selisih: ${formatDisplay(sales - totalSettlement)})`;
            }
        };

        const allCalcInputs = [...shiftInputs, ...paymentInputs, ...deliverySalesInputs];
        allCalcInputs.forEach(input => {
            input.addEventListener('input', function() {
                // Kalkulasi ulang
                if (this.classList.contains('calc-sales')) calculateTotalSales();
                else checkBalance();
            });
        });

        // Init
        calculateTotalSales();
    });
</script>
@endpush
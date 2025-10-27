<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Stok - {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</title>
    <style>
        /* Print-specific styles */
        @page {
            size: A4;
            margin: 0.5in;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .print-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .report-header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border: 2px solid #2c3e50;
        }

        .company-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .company-address {
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .report-date {
            font-size: 12px;
            color: #666;
        }
        
        /* Period Info */
        .period-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .period-info h4 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .period-dates {
            font-size: 12px;
            color: #666;
        }
        
        /* Statistics */
        .statistics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            background: #fff;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        
        .stat-card.success .stat-value {
            color: #27ae60;
        }
        
        .stat-card.danger .stat-value {
            color: #e74c3c;
        }
        
        .stat-card.warning .stat-value {
            color: #f39c12;
        }
        
        .stat-card.info .stat-value {
            color: #3498db;
        }
        
        /* Tables */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .report-table th,
        .report-table td {
            border: 1px solid #dee2e6;
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
        }
        
        .report-table th {
            background: #2c3e50;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .report-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }
        
        .badge-success {
            background: #27ae60;
        }
        
        .badge-danger {
            background: #e74c3c;
        }
        
        .badge-warning {
            background: #f39c12;
        }
        
        .badge-info {
            background: #3498db;
        }
        
        .badge-secondary {
            background: #95a5a6;
        }
        
        .text-success {
            color: #27ae60 !important;
            font-weight: bold;
        }
        
        .text-danger {
            color: #e74c3c !important;
            font-weight: bold;
        }
        
        .text-warning {
            color: #f39c12 !important;
            font-weight: bold;
        }
        
        /* Section titles */
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin: 30px 0 15px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #3498db;
        }
        
        /* Top items grid */
        .top-items-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .top-item {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            background: #f8f9fa;
        }
        
        .top-item-rank {
            font-size: 14px;
            font-weight: bold;
            color: #3498db;
        }
        
        .top-item-name {
            font-size: 11px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .top-item-stats {
            font-size: 10px;
            color: #666;
        }
        
        /* Footer */
        .report-footer {
            border-top: 2px solid #2c3e50;
            padding-top: 20px;
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .signature-section {
            text-align: center;
        }
        
        .signature-line {
            width: 200px;
            height: 60px;
            border-bottom: 1px solid #333;
            margin: 0 auto 10px;
        }
        
        .signature-label {
            font-size: 11px;
            color: #666;
        }
        
        .print-info {
            font-size: 10px;
            color: #999;
            text-align: center;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        /* Page break */
        .page-break {
            page-break-before: always;
        }
        
        /* No print elements */
        .no-print {
            display: none;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .print-container {
                padding: 0;
            }
        }
        
        @media screen {
            .print-container {
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                margin: 20px auto;
            }
            
            .print-actions {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                display: flex;
                gap: 10px;
            }
            
            .btn {
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                font-weight: bold;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            
            .btn-primary {
                background: #3498db;
                color: white;
            }
            
            .btn-secondary {
                background: #95a5a6;
                color: white;
            }
            
            .btn:hover {
                opacity: 0.9;
                transform: translateY(-1px);
            }
        }
    </style>
</head>
<body>
    <!-- Print Actions (Only visible on screen) -->
    <div class="print-actions no-print">
        <button class="btn btn-primary" onclick="window.print()">
            🖨️ Print
        </button>
        <a href="{{ route('stock-transactions.report', request()->query()) }}" class="btn btn-secondary">
            ← Kembali
        </a>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="report-header">
            <div class="company-logo">
                <img src="{{ asset('assets/img/chicking-logo-bjm.png') }}" alt="Chicking BJM Logo">
            </div>
            <div class="company-name">CHICKING BJM</div>
            <div class="company-address">
                Banjarmasin, Kalimantan Selatan<br>
                Telepon: (0511) 123-4567 | Email: info@chickingbjm.com
            </div>
            <div class="report-title">LAPORAN TRANSAKSI STOK</div>
            <div class="report-date">
                Dicetak pada: {{ now()->format('d F Y, H:i:s') }}
            </div>
        </div>

        <!-- Period Information -->
        <div class="period-info">
            <h4>Periode Laporan</h4>
            <div class="period-dates">
                {{ $startDate->format('d F Y') }} - {{ $endDate->format('d F Y') }}
                <br>
                <!-- <small>({{ $endDate->diffInDays($startDate) + 1 }} hari)</small> -->
            </div>
        </div>

        <!-- Statistics -->
        <div class="statistics">
            <div class="stat-card success">
                <div class="stat-value">{{ number_format($stockIn, 0) }}</div>
                <div class="stat-label">Total Stok Masuk</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-value">{{ number_format($stockOut, 0) }}</div>
                <div class="stat-label">Total Stok Keluar</div>
            </div>
            <div class="stat-card warning">
                @php $netChange = $stockIn - $stockOut; @endphp
                <div class="stat-value">{{ $netChange >= 0 ? '+' : '' }}{{ number_format($netChange, 0) }}</div>
                <div class="stat-label">Perubahan Bersih</div>
            </div>
            <div class="stat-card info">
                @php $totalTransactions = \App\Models\StockTransaction::whereBetween('created_at', [$startDate, $endDate])->count(); @endphp
                <div class="stat-value">{{ number_format($totalTransactions) }}</div>
                <div class="stat-label">Total Transaksi</div>
            </div>
        </div>

        <!-- Daily Transactions Table -->
        <div class="section-title">📊 Ringkasan Transaksi Harian</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 15%;">Stok Masuk</th>
                    <th style="width: 15%;">Stok Keluar</th>
                    <th style="width: 15%;">Penyesuaian</th>
                    <th style="width: 15%;">Total Transaksi</th>
                    <th style="width: 15%;">Net Change</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailyTransactions as $date => $transactions)
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
                    <td class="text-center">
                        <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong>
                        <br><small>{{ \Carbon\Carbon::parse($date)->format('l') }}</small>
                    </td>
                    <td class="text-center">
                        @if($stockInQty > 0)
                        <strong class="text-success">{{ number_format($stockInQty, 0) }}</strong>
                        <br><small>({{ $stockInCount }} transaksi)</small>
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($stockOutQty > 0)
                        <strong class="text-danger">{{ number_format($stockOutQty, 0) }}</strong>
                        <br><small>({{ $stockOutCount }} transaksi)</small>
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($adjustmentQty > 0)
                        <strong class="text-warning">{{ number_format($adjustmentQty, 0) }}</strong>
                        <br><small>({{ $adjustmentCount }} transaksi)</small>
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $totalCount }}</span>
                    </td>
                    <td class="text-center">
                        <strong class="text-{{ $netChange >= 0 ? 'success' : 'danger' }}">
                            {{ $netChange >= 0 ? '+' : '' }}{{ number_format($netChange, 0) }}
                        </strong>
                    </td>
                    <td class="text-center">
                        @if($netChange > 0)
                        <span class="badge badge-success">Surplus</span>
                        @elseif($netChange < 0)
                        <span class="badge badge-danger">Defisit</span>
                        @else
                        <span class="badge badge-secondary">Seimbang</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 30px;">
                        <strong>Tidak ada transaksi dalam periode ini</strong>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($dailyTransactions->count() > 0)
            <tfoot style="background: #f8f9fa; font-weight: bold;">
                <tr>
                    <td class="text-center"><strong>TOTAL</strong></td>
                    <td class="text-center"><strong class="text-success">{{ number_format($stockIn, 0) }}</strong></td>
                    <td class="text-center"><strong class="text-danger">{{ number_format($stockOut, 0) }}</strong></td>
                    <td class="text-center"><strong class="text-warning">{{ number_format($dailyTransactions->flatten()->where('transaction_type', 'ADJUSTMENT')->sum('total_quantity'), 0) }}</strong></td>
                    <td class="text-center"><strong>{{ number_format($totalTransactions) }}</strong></td>
                    <td class="text-center"><strong class="text-{{ $netChange >= 0 ? 'success' : 'danger' }}">{{ $netChange >= 0 ? '+' : '' }}{{ number_format($netChange, 0) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>

        @if($topItems->count() > 0)
        <!-- Page break for second page -->
        <div class="page-break"></div>

        <!-- Top Items Section -->
        <!-- <div class="section-title">🏆 Top 10 Item Paling Aktif</div>
        <div class="top-items-grid">
            @foreach($topItems->take(10) as $index => $topItem)
            <div class="top-item">
                <div class="top-item-rank">#{{ $index + 1 }}</div>
                <div class="top-item-name">{{ $topItem->item->item_name }}</div>
                <div class="top-item-stats">
                    SKU: {{ $topItem->item->sku }}<br>
                    Total Kuantitas: <strong>{{ number_format($topItem->total_quantity, 0) }}</strong><br>
                    Jumlah Transaksi: <strong>{{ $topItem->transaction_count }}</strong><br>
                    Unit: {{ $topItem->item->unit }}
                </div>
            </div>
            @endforeach
        </div> -->

        <!-- Detailed Top Items Table -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Rank</th>
                    <th style="width: 15%;">SKU</th>
                    <th style="width: 30%;">Nama Item</th>
                    <th style="width: 15%;">Kategori</th>
                    <th style="width: 12%;">Total Kuantitas</th>
                    <th style="width: 10%;">Transaksi</th>
                    <th style="width: 10%;">Unit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topItems as $index => $topItem)
                <tr>
                    <td class="text-center">
                        <span class="badge badge-{{ $index < 3 ? 'warning' : 'secondary' }}">
                            #{{ $index + 1 }}
                        </span>
                    </td>
                    <td>{{ $topItem->item->sku }}</td>
                    <td><strong>{{ $topItem->item->item_name }}</strong></td>
                    <td>{{ $topItem->item->category->category_name ?? '-' }}</td>
                    <td class="text-center">
                        <strong>{{ number_format($topItem->total_quantity, 0) }}</strong>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $topItem->transaction_count }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-secondary">{{ $topItem->item->unit }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Footer with Signatures -->
        <div class="report-footer">
            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-label">
                    <strong>Dibuat Oleh</strong><br>
                    Staff Inventory
                </div>
            </div>
            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-label">
                    <strong>Disetujui Oleh</strong><br>
                    Manager
                </div>
            </div>
        </div>

        <!-- Print Information -->
        <div class="print-info">
            Dokumen ini digenerate secara otomatis oleh Sistem Inventory Chicking BJM<br>
            {{ now()->format('d F Y, H:i:s') }} | User: {{ Auth::user()->name ?? 'System' }}
        </div>
    </div>

    <script>
        // Print function
        function printDocument() {
            window.print();
        }
        
        // Handle keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
{{-- resources/views/items/print-report.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stock Akhir - {{ now()->format('d/m/Y') }}</title>
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
        
        /* Filter Info */
        .filter-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .filter-info h4 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .filter-item {
            font-size: 11px;
        }
        
        .filter-label {
            font-weight: bold;
            color: #495057;
        }
        
        .filter-value {
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
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        
        .stat-card.warning .stat-value {
            color: #f39c12;
        }
        
        .stat-card.danger .stat-value {
            color: #e74c3c;
        }
        
        .stat-card.success .stat-value {
            color: #27ae60;
        }
        
        /* Table */
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
        
        .badge-primary {
            background: #3498db;
        }
        
        .badge-success {
            background: #27ae60;
        }
        
        .badge-warning {
            background: #f39c12;
        }
        
        .badge-danger {
            background: #e74c3c;
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
        <a href="{{ route('items.report', request()->query()) }}" class="btn btn-secondary">
            ← Kembali
        </a>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="report-header">
            <div class="company-logo">
                <img src="{{ asset('assets/img/chicking-logo-bjm.png') }}" alt="Chicking BJM Logo" style="width: 80px; height: 80px; object-fit: contain; border-radius: 50%;">
            </div>
            <div class="company-name">CHICKING BJM</div>
            <div class="company-address">
                Banjarmasin, Kalimantan Selatan<br>
                Telepon: (0511) 123-4567 | Email: info@chickingbjm.com
            </div>
            <div class="report-title">LAPORAN STOCK AKHIR ITEM</div>
            <div class="report-date">
                Dicetak pada: {{ now()->format('d F Y, H:i:s') }}
            </div>
        </div>


        <!-- Statistics -->
        <div class="statistics">
            <div class="stat-card">
                <div class="stat-value">{{ $totalItems }}</div>
                <div class="stat-label">Total Item</div>
            </div>
            <div class="stat-card success">
                <div class="stat-value">{{ number_format($totalStockValue) }}</div>
                <div class="stat-label">Total Stok</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-value">{{ $lowStockItems }}</div>
                <div class="stat-label">Stok Menipis</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-value">{{ $outOfStockItems }}</div>
                <div class="stat-label">Stok Habis</div>
            </div>
        </div>

        <!-- Data Table -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 12%;">SKU</th>
                    <th style="width: 25%;">Nama Item</th>
                    <th style="width: 15%;">Kategori</th>
                    <th style="width: 8%;">Unit</th>
                    <th style="width: 8%;">Stock Akhir</th>
                    <th style="width: 8%;">Min. Stock</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 7%;">Selisih</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $index => $item)
                @php
                    $selisih = $item->current_stock - $item->low_stock_threshold;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <span class="badge badge-secondary">{{ $item->sku }}</span>
                    </td>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                    </td>
                    <td>{{ $item->category->category_name ?? '-' }}</td>
                    <!-- <td>{{ $item->supplier->supplier_name ?? '-' }}</td> -->
                    <td class="text-center">
                        <span class="badge badge-primary">{{ $item->unit }}</span>
                    </td>
                    <td class="text-center">
                        <strong class="text-{{ $item->stock_status === 'Stok Tersedia' ? 'success' : ($item->stock_status === 'Stok Menipis' ? 'warning' : 'danger') }}">
                            {{ number_format($item->current_stock) }}
                        </strong>
                    </td>
                    <td class="text-center">{{ number_format($item->low_stock_threshold) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $item->stock_status === 'Stok Tersedia' ? 'success' : ($item->stock_status === 'Stok Menipis' ? 'warning' : 'danger') }}">
                            {{ $item->stock_status }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="text-{{ $selisih >= 0 ? 'success' : 'danger' }}">
                            {{ $selisih >= 0 ? '+' : '' }}{{ number_format($selisih) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 30px;">
                        <strong>Tidak ada data untuk ditampilkan</strong>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($items->count() > 0)
            <tfoot style="background: #f8f9fa; font-weight: bold;">
                <tr>
                    <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
                    <td class="text-center"><strong>{{ number_format($items->sum('current_stock')) }}</strong></td>
                    <td class="text-center"><strong>{{ number_format($items->sum('low_stock_threshold')) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>

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
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }
        
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
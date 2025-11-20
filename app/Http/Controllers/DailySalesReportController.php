<?php

namespace App\Http\Controllers;

use App\Models\DailySalesReport;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DailySalesReportController extends Controller
{
    /**
     * Menampilkan daftar laporan sales.
     */
    public function index(Request $request)
    {
        // 1. Ambil daftar outlet yang bisa diakses user ini
        // (Kita gunakan ini untuk dropdown filter dan membatasi query)
        $accessibleOutlets = auth()->user()->getAccessibleWarehousesByType('outlet');
        $accessibleOutletIds = $accessibleOutlets->pluck('id')->toArray();

        // Jika tidak punya akses ke outlet manapun
        if (empty($accessibleOutletIds)) {
             // Atau return view kosong
            return view('sales-report.index', [
                'reports' => collect([]), 
                'accessibleOutlets' => $accessibleOutlets,
                'summary' => ['total_sales' => 0, 'count' => 0]
            ]);
        }

        // 2. Mulai Query Builder
        $query = DailySalesReport::query()
            ->with(['outletWarehouse', 'createdBy']) // Eager load relasi biar cepat
            ->whereIn('outlet_warehouse_id', $accessibleOutletIds);

        // 3. Terapkan Filter

        // Filter by Outlet (Dropdown)
        if ($request->filled('outlet_id')) {
            $query->where('outlet_warehouse_id', $request->outlet_id);
        }

        // Filter by Date Range (Start Date)
        if ($request->filled('start_date')) {
            $query->whereDate('report_date', '>=', $request->start_date);
        }

        // Filter by Date Range (End Date)
        if ($request->filled('end_date')) {
            $query->whereDate('report_date', '<=', $request->end_date);
        }

        // 4. Hitung Statistik Ringkasan (Sebelum Pagination)
        // Kita clone query agar pagination tidak mengganggu perhitungan total
        $statsQuery = clone $query;
        $summary = [
            'total_sales' => $statsQuery->sum('total_sales'),
            'total_cash'  => $statsQuery->sum('payment_cash'),
            // Total Digital = Total Sales - Cash
            'total_digital' => $statsQuery->sum(DB::raw('total_sales - payment_cash')),
            'count'       => $statsQuery->count(),
        ];

        // 5. Sorting & Pagination
        $reports = $query->orderBy('report_date', 'desc') // Tanggal terbaru di atas
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query()); // Agar filter tetap ada saat pindah halaman

        // 6. Return View
        return view('sales-report.index', compact('reports', 'accessibleOutlets', 'summary'));
    }
    /**
     * Menampilkan detail satu laporan.
     */
    public function show($id)
    {
        $report = DailySalesReport::with(['outletWarehouse', 'createdBy'])
            ->findOrFail($id);

        // Validasi Akses (PENTING: Cegah user melihat laporan outlet lain)
        $this->validateWarehouseAccess($report->outlet_warehouse_id);

        $lastWeekDate = $report->report_date->copy()->subWeek();
        
        $lastWeekReport = DailySalesReport::where('outlet_warehouse_id', $report->outlet_warehouse_id)
            ->whereDate('report_date', $lastWeekDate)
            ->first();

        return view('sales-report.show', compact('report', 'lastWeekReport'));
    }

    /**
     * Menampilkan formulir input laporan.
     */
    public function create()
    {
        // Ambil daftar outlet yang boleh diakses user ini
        // (Menggunakan helper yang sudah kita buat sebelumnya di Model User)
        $outlets = auth()->user()->getAccessibleWarehousesByType('outlet');

        if ($outlets->isEmpty()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke outlet manapun.');
        }

        return view('sales-report.create', compact('outlets'));
    }

    /**
     * Menyimpan laporan ke database.
     */
    public function store(Request $request)
    {
        // --- 1. VALIDASI INPUT ---
        $validator = Validator::make($request->all(), [
            'outlet_warehouse_id' => 'required|exists:warehouses,id',
            'report_date' => 'required|date',
            
            // Sales
            'target_sales' => 'required|numeric|min:0',
            'total_sales' => 'required|numeric|min:0', // Angka Kunci
            'sales_shift_opening' => 'nullable|numeric|min:0',
            'sales_shift_closing' => 'nullable|numeric|min:0',
            'sales_shift_midnight' => 'nullable|numeric|min:0',

            // Payments (Cash)
            'payment_cash' => 'nullable|numeric|min:0',
            'payment_void_refund' => 'nullable|numeric|min:0',

            // Digital & Delivery (Akan disanitasi jadi 0 jika null)
            'edc_bni' => 'nullable|numeric', 'edc_bri' => 'nullable|numeric', 
            'edc_mandiri' => 'nullable|numeric', 'edc_bca' => 'nullable|numeric',
            'qr_bca' => 'nullable|numeric', 'qr_mandiri' => 'nullable|numeric',
            'qr_bri' => 'nullable|numeric', 'qr_bni' => 'nullable|numeric',
            'transfer_mandiri' => 'nullable|numeric', 'gobizz_wallet' => 'nullable|numeric',
            'compliment' => 'nullable|numeric',

            // Stats
            'mtd_sales' => 'nullable|numeric|min:0',
            'guest_count_today' => 'nullable|integer|min:0', // Ini Dine-in Input
            'mtd_guest_count' => 'nullable|integer|min:0',

            // Delivery Inputs
            'gofood_sales' => 'nullable|numeric', 'gofood_tc' => 'nullable|integer',
            'grabfood_sales' => 'nullable|numeric', 'grabfood_tc' => 'nullable|integer',

            // Text
            'fast_moving_items' => 'nullable|string',
            'important_events' => 'nullable|string',
            'staff_opening' => 'nullable|string',
            'staff_closing' => 'nullable|string',
            'staff_midnight' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Ubah semua input null menjadi 0 agar perhitungan aman
        $data = $this->sanitizeInput($request->all());

        // --- 2. VALIDASI LOGIKA BISNIS ---
        
        // A. Cek Hak Akses
        try {
            $this->validateWarehouseAccess($data['outlet_warehouse_id'], true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Akses ditolak ke outlet ini.');
        }

        // B. Cek Duplikat (Hanya 1 laporan per hari per outlet)
        $exists = DailySalesReport::where('outlet_warehouse_id', $data['outlet_warehouse_id'])
            ->where('report_date', $data['report_date'])
            ->exists();
        
        if ($exists) {
            return redirect()->back()->withInput()
                ->with('error', 'Laporan untuk tanggal ini sudah ada. Silakan edit laporan yang sudah ada.');
        }

        // C. Validasi Matematika (Balance Check)
        // Total Uang Fisik/Digital di Kasir
        $totalPaymentInHand = 
            $data['payment_cash'] +
            $data['edc_bni'] + $data['edc_bri'] + $data['edc_mandiri'] + $data['edc_bca'] +
            $data['qr_bca'] + $data['qr_mandiri'] + $data['qr_bri'] + $data['qr_bni'] +
            $data['transfer_mandiri'] + $data['gobizz_wallet'] + $data['compliment'];
        
        // Total Penjualan Delivery (Piutang)
        $totalDeliverySales = $data['gofood_sales'] + $data['grabfood_sales'];

        // Grand Total Settlement (Harus sama dengan Total Sales)
        $grandTotalSettlement = $totalPaymentInHand + $totalDeliverySales;

        if (abs($data['total_sales'] - $grandTotalSettlement) > 100) {
            return redirect()->back()->withInput()
                ->with('error', 
                    'TOTAL TIDAK BALANCE! Total Sales (Rp ' . number_format($data['total_sales']) . ') ' .
                    'tidak sama dengan Total (Pembayaran + Delivery) (Rp ' . number_format($grandTotalSettlement) . '). ' .
                    'Selisih: Rp ' . number_format($data['total_sales'] - $grandTotalSettlement)
                );
        }

        // --- 3. LOGIKA PERHITUNGAN OTOMATIS ---

        // Hitung Total TC (Guest Count) Hari Ini
        // Rumus: Input Dine-In (dari form) + TC GoFood + TC GrabFood
        $finalGuestCount = $data['guest_count_today'] + $data['gofood_tc'] + $data['grabfood_tc'];

        // --- 4. SIMPAN KE DATABASE ---
        try {
            DB::beginTransaction();

            DailySalesReport::create([
                // Identitas
                'outlet_warehouse_id' => $data['outlet_warehouse_id'],
                'created_by_user_id' => Auth::id(),
                'report_date' => $data['report_date'],
                
                // Angka Sales Utama
                'target_sales' => $data['target_sales'],
                'sales_shift_opening' => $data['sales_shift_opening'],
                'sales_shift_closing' => $data['sales_shift_closing'],
                'sales_shift_midnight' => $data['sales_shift_midnight'],
                'total_sales' => $data['total_sales'],
                
                // Angka Pembayaran Utama
                'payment_cash' => $data['payment_cash'],
                'payment_void_refund' => $data['payment_void_refund'],
                
                // Angka Statistik
                'mtd_sales' => $data['mtd_sales'],
                'guest_count_today' => $finalGuestCount, // ✅ Menggunakan Hasil Penjumlahan
                'mtd_guest_count' => $data['mtd_guest_count'],

                // JSON: Rincian Pembayaran Digital
                'payment_details_digital' => [
                    'edc_bni' => $data['edc_bni'],
                    'edc_bri' => $data['edc_bri'],
                    'edc_mandiri' => $data['edc_mandiri'],
                    'edc_bca' => $data['edc_bca'],
                    'qr_bca' => $data['qr_bca'],
                    'qr_mandiri' => $data['qr_mandiri'],
                    'qr_bri' => $data['qr_bri'],
                    'qr_bni' => $data['qr_bni'],
                    'transfer_mandiri' => $data['transfer_mandiri'],
                    'gobizz_wallet' => $data['gobizz_wallet'],
                    'compliment' => $data['compliment'],
                ],

                // JSON: Delivery Platforms
                'delivery_platforms' => [
                    'gofood' => [
                        'sales' => $data['gofood_sales'],
                        'tc' => $data['gofood_tc'],
                        'mtd_sales' => $data['gofood_mtd_sales'],
                        'mtd_tc' => $data['gofood_mtd_tc']
                    ],
                    'grabfood' => [
                        'sales' => $data['grabfood_sales'],
                        'tc' => $data['grabfood_tc'],
                        'mtd_sales' => $data['grabfood_mtd_sales'],
                        'mtd_tc' => $data['grabfood_mtd_tc']
                    ]
                ],

                // JSON: Staff
                'staff_on_duty' => [
                    'opening' => $data['staff_opening'],
                    'closing' => $data['staff_closing'],
                    'midnight' => $data['staff_midnight'],
                ],

                // JSON: Notes
                'notes' => [
                    'fast_moving_items' => $request->input('fast_moving_items'),
                    'important_events' => $request->input('important_events')
                ]
            ]);

            DB::commit();

            return redirect()->route('sales-report.index')
                ->with('success', 'Laporan Sales Harian berhasil disimpan! Total TC: ' . $finalGuestCount);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan daily sales report: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Helper untuk mengubah input null menjadi 0 agar perhitungan aman.
     */
    private function sanitizeInput(array $input)
    {
        // Daftar field yang harus numeric
        $numericFields = [
            'target_sales', 'sales_shift_opening', 'sales_shift_closing', 'sales_shift_midnight', 'total_sales',
            'payment_cash', 'payment_void_refund',
            'edc_bni', 'edc_bri', 'edc_mandiri', 'edc_bca',
            'qr_bca', 'qr_mandiri', 'qr_bri', 'qr_bni',
            'transfer_mandiri', 'gobizz_wallet', 'compliment',
            'mtd_sales', 'guest_count_today', 'mtd_guest_count',
            'gofood_sales', 'gofood_tc', 'gofood_mtd_sales', 'gofood_mtd_tc',
            'grabfood_sales', 'grabfood_tc', 'grabfood_mtd_sales', 'grabfood_mtd_tc'
        ];

        foreach ($numericFields as $field) {
            if (!isset($input[$field]) || $input[$field] === null) {
                $input[$field] = 0;
            }
        }

        return $input;
    }
}
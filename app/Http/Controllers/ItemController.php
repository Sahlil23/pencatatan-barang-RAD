<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\MonthlyStockBalance;
use Illuminate\Http\Request;
use App\Services\FonnteService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        // FIX: Load dengan cara yang benar untuk supplier
        $query = Item::with([
            'category', 
            'currentBalance',
            'stockTransactions' => function($q) {
                $q->whereNotNull('supplier_id')
                  ->with('supplier')
                  ->latest()
                  ->take(1);
            }
        ]);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // TAMBAH FILTER BY SUPPLIER (MELALUI STOCK TRANSACTIONS)
        if ($request->filled('supplier_id')) {
            $query->whereHas('stockTransactions', function($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }

        // Filter by stock status menggunakan monthly balance
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->outOfStock();
                    break;
                case 'in':
                    $query->inStock();
                    break;
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(15);
        
        // FIX: Process items untuk menambahkan supplier info
        $items->getCollection()->transform(function ($item) {
            $item->supplier_info = $item->getSupplierInfo();
            return $item;
        });
        
        $categories = Category::get();
        $suppliers = Supplier::whereHas('stockTransactions')->get(); // Hanya supplier yang memiliki transaksi

        return view('items.index', compact('items', 'categories', 'suppliers'));
    }

    public function create()
    {
        $categories = Category::get();
        // $suppliers = Supplier::all();
        return view('items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:50|unique:items',
            'item_name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            // 'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:20',
            'initial_stock' => 'required|numeric|min:0', // Ganti dari current_stock
            'low_stock_threshold' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            // Create item tanpa current_stock
            $item = Item::create($request->except('initial_stock'));

            // Create monthly balance untuk bulan ini dengan initial stock
            MonthlyStockBalance::create([
                'item_id' => $item->id,
                'year' => now()->year,
                'month' => now()->month,
                'opening_stock' => $request->initial_stock,
                'closing_stock' => $request->initial_stock,
                'stock_in' => 0,
                'stock_out' => 0,
                'adjustments' => 0
            ]);

            DB::commit();

            return redirect()->route('items.index')
                ->with('success', 'Item berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan item: ' . $e->getMessage());
        }
    }

    public function show(Item $item)
    {
        $item->load([
            'category', 
            'supplier', 
            'stockTransactions' => function($query) {
                $query->latest()->take(10);
            },
            'currentBalance'
        ]);
        
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $categories = Category::get();
        return view('items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'sku' => 'required|string|max:50|unique:items,sku,' . $item->id,
            'item_name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            // 'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:20',
            'low_stock_threshold' => 'required|numeric|min:0'
        ]);

        $item->update($request->all());

        return redirect()->route('items.index')
            ->with('success', 'Item berhasil diupdate!');
    }

    public function destroy(Item $item)
    {
        if ($item->stockTransactions()->count() > 0) {
            return redirect()->route('items.index')
                ->with('error', 'Item tidak dapat dihapus karena memiliki riwayat transaksi!');
        }

        DB::beginTransaction();
        try {
            // Hapus monthly balances
            $item->monthlyBalances()->delete();
            
            // Hapus item
            $item->delete();

            DB::commit();

            return redirect()->route('items.index')
                ->with('success', 'Item berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('items.index')
                ->with('error', 'Gagal menghapus item: ' . $e->getMessage());
        }
    }

    public function lowStock()
    {
        $items = Item::lowStock()->with(['category', 'currentBalance'])->get();
        return view('items.low-stock', compact('items'));
    }

    public function adjustStock(Request $request, Item $item)
    {
        $request->validate([
            'adjustment_type' => 'required|in:add,reduce,set',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'required|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id' 
        ]);

        DB::beginTransaction();
        try {
            $monthlyBalance = MonthlyStockBalance::getOrCreateBalance($item->id);

            switch ($request->adjustment_type) {
                case 'add':
                    $item->addStock($request->quantity, $request->notes, auth()->id(), $request->supplier_id);
                    $message = 'Stok berhasil ditambahkan!';
                    break;

                case 'reduce':
                    if ($monthlyBalance->closing_stock < $request->quantity) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', 'Stok tidak mencukupi! Stok saat ini: ' . $monthlyBalance->closing_stock);
                    }
                    $item->reduceStock($request->quantity, $request->notes, auth()->id(), $request->supplier_id);
                    $message = 'Stok berhasil dikurangi!';
                    break;

                case 'set':
                    $item->adjustStock($request->quantity, $request->notes, auth()->id(), $request->supplier_id);
                    $message = 'Stok berhasil disesuaikan!';
                    break;
            }

            DB::commit();

            return redirect()->route('items.show', $item)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal melakukan penyesuaian stok: ' . $e->getMessage());
        }
    }

    public function report(Request $request)
    {
        // Parse periode dari request (default bulan ini)
        $selectedPeriod = $request->get('period', now()->format('Y-m'));
        [$selectedYear, $selectedMonth] = explode('-', $selectedPeriod);
        $selectedYear = (int) $selectedYear;
        $selectedMonth = (int) $selectedMonth;

        // Periode sebelumnya untuk perbandingan
        $prevDate = Carbon::create($selectedYear, $selectedMonth, 1)->subMonth();
        $prevYear = $prevDate->year;
        $prevMonth = $prevDate->month;

        $query = Item::with([
            'category', 
            'monthlyBalances' => function($q) use ($selectedYear, $selectedMonth, $prevYear, $prevMonth) {
                $q->where(function($subQuery) use ($selectedYear, $selectedMonth, $prevYear, $prevMonth) {
                    $subQuery->where(function($current) use ($selectedYear, $selectedMonth) {
                        $current->where('year', $selectedYear)->where('month', $selectedMonth);
                    })
                    ->orWhere(function($previous) use ($prevYear, $prevMonth) {
                        $previous->where('year', $prevYear)->where('month', $prevMonth);
                    });
                });
            }
        ]);

        // Filter berdasarkan kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }


        // Filter berdasarkan status stok untuk periode yang dipilih
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->lowStockForPeriod($selectedYear, $selectedMonth);
                    break;
                case 'out':
                    $query->outOfStockForPeriod($selectedYear, $selectedMonth);
                    break;
                case 'in':
                    $query->inStockForPeriod($selectedYear, $selectedMonth);
                    break;
            }
        }

        // Pencarian berdasarkan nama item atau SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Urutkan berdasarkan pilihan user
        $sortBy = $request->get('sort_by', 'item_name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'stock') {
            // Sort berdasarkan closing stock dari monthly balance
            $query->leftJoin('monthly_stock_balances', function($join) use ($selectedYear, $selectedMonth) {
                $join->on('items.id', '=', 'monthly_stock_balances.item_id')
                     ->where('monthly_stock_balances.year', $selectedYear)
                     ->where('monthly_stock_balances.month', $selectedMonth);
            })
            ->orderBy('monthly_stock_balances.closing_stock', $sortOrder)
            ->select('items.*');
        } elseif ($sortBy === 'category') {
            $query->join('categories', 'items.category_id', '=', 'categories.id')
                  ->orderBy('categories.category_name', $sortOrder)
                  ->select('items.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $items = $query->get();
        
        // Process items untuk mendapatkan current dan previous balance
        $items = $items->map(function($item) use ($selectedYear, $selectedMonth, $prevYear, $prevMonth) {
            // Pisahkan current dan previous balance dari collection
            $currentBalance = $item->monthlyBalances->where('year', $selectedYear)
                                                   ->where('month', $selectedMonth)
                                                   ->first();
            
            $previousBalance = $item->monthlyBalances->where('year', $prevYear)
                                                    ->where('month', $prevMonth)
                                                    ->first();
            
            // Tambahkan sebagai property item
            $item->currentBalance = $currentBalance;
            $item->previousBalance = $previousBalance;
            
            return $item;
        });

        $categories = Category::all();
        $suppliers = Supplier::all();

        // Get available periods untuk dropdown
        $availablePeriods = MonthlyStockBalance::getAvailablePeriods();
        
        // Jika belum ada data monthly balance, tambahkan bulan ini
        if ($availablePeriods->isEmpty() || !$availablePeriods->contains('value', now()->format('Y-m'))) {
            $availablePeriods->prepend([
                'year' => now()->year,
                'month' => now()->month,
                'label' => now()->format('F Y'),
                'value' => now()->format('Y-m')
            ]);
        }

        // Summary untuk periode yang dipilih
        $currentSummary = MonthlyStockBalance::getPeriodSummary($selectedYear, $selectedMonth);
        $previousSummary = MonthlyStockBalance::getPeriodSummary($prevYear, $prevMonth);

        // Statistik untuk laporan
        $totalItems = $items->count();
        $totalStockValue = 0;
        $lowStockItems = 0;
        $outOfStockItems = 0;

        foreach ($items as $item) {
            $balance = $item->currentBalance;
            $stock = $balance ? $balance->closing_stock : 0;
            
            $totalStockValue += $stock;
            
            if ($stock <= 0) {
                $outOfStockItems++;
            } elseif ($stock <= $item->low_stock_threshold) {
                $lowStockItems++;
            }
        }

        return view('items.report', compact(
            'items', 'categories', 'suppliers', 'totalItems', 
            'totalStockValue', 'lowStockItems', 'outOfStockItems',
            'availablePeriods', 'selectedPeriod', 'selectedYear', 'selectedMonth',
            'currentSummary', 'previousSummary', 'prevYear', 'prevMonth'
        ));
    }
public function printReport(Request $request)
{
    // Parse periode dari request (default bulan ini)
    $selectedPeriod = $request->get('period', now()->format('Y-m'));
    [$selectedYear, $selectedMonth] = explode('-', $selectedPeriod);
    $selectedYear = (int) $selectedYear;
    $selectedMonth = (int) $selectedMonth;

    // Periode sebelumnya untuk perbandingan
    $prevDate = Carbon::create($selectedYear, $selectedMonth, 1)->subMonth();
    $prevYear = $prevDate->year;
    $prevMonth = $prevDate->month;

    $query = Item::with([
        'category', 
        'monthlyBalances' => function($q) use ($selectedYear, $selectedMonth, $prevYear, $prevMonth) {
            $q->where(function($subQuery) use ($selectedYear, $selectedMonth, $prevYear, $prevMonth) {
                $subQuery->where(function($current) use ($selectedYear, $selectedMonth) {
                    $current->where('year', $selectedYear)->where('month', $selectedMonth);
                })
                ->orWhere(function($previous) use ($prevYear, $prevMonth) {
                    $previous->where('year', $prevYear)->where('month', $prevMonth);
                });
            });
        }
    ]);

    // Filter berdasarkan kategori
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    // Filter berdasarkan status stok untuk periode yang dipilih
    if ($request->filled('stock_status')) {
        switch ($request->stock_status) {
            case 'low':
                $query->lowStockForPeriod($selectedYear, $selectedMonth);
                break;
            case 'out':
                $query->outOfStockForPeriod($selectedYear, $selectedMonth);
                break;
            case 'in':
                $query->inStockForPeriod($selectedYear, $selectedMonth);
                break;
        }
    }

    // Pencarian berdasarkan nama item atau SKU
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('item_name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    // Urutkan berdasarkan pilihan user
    $sortBy = $request->get('sort_by', 'item_name');
    $sortOrder = $request->get('sort_order', 'asc');
    
    if ($sortBy === 'stock') {
        // Sort berdasarkan closing stock dari monthly balance
        $query->leftJoin('monthly_stock_balances', function($join) use ($selectedYear, $selectedMonth) {
            $join->on('items.id', '=', 'monthly_stock_balances.item_id')
                 ->where('monthly_stock_balances.year', $selectedYear)
                 ->where('monthly_stock_balances.month', $selectedMonth);
        })
        ->orderBy('monthly_stock_balances.closing_stock', $sortOrder)
        ->select('items.*');
    } elseif ($sortBy === 'category') {
        $query->join('categories', 'items.category_id', '=', 'categories.id')
              ->orderBy('categories.category_name', $sortOrder)
              ->select('items.*');
    } else {
        $query->orderBy($sortBy, $sortOrder);
    }

    $items = $query->get();
    
    // Process items untuk mendapatkan current dan previous balance
    $items = $items->map(function($item) use ($selectedYear, $selectedMonth, $prevYear, $prevMonth) {
        // Pisahkan current dan previous balance dari collection
        $currentBalance = $item->monthlyBalances->where('year', $selectedYear)
                                               ->where('month', $selectedMonth)
                                               ->first();
        
        $previousBalance = $item->monthlyBalances->where('year', $prevYear)
                                                ->where('month', $prevMonth)
                                                ->first();
        
        // Tambahkan sebagai property item
        $item->currentBalance = $currentBalance;
        $item->previousBalance = $previousBalance;
        
        return $item;
    });

    $categories = Category::all();

    // Summary untuk periode yang dipilih
    $currentSummary = MonthlyStockBalance::getPeriodSummary($selectedYear, $selectedMonth);
    $previousSummary = MonthlyStockBalance::getPeriodSummary($prevYear, $prevMonth);

    // Statistik untuk laporan
    $totalItems = $items->count();
    $totalStockValue = 0;
    $lowStockItems = 0;
    $outOfStockItems = 0;

    foreach ($items as $item) {
        $balance = $item->currentBalance;
        $stock = $balance ? $balance->closing_stock : 0;
        
        $totalStockValue += $stock;
        
        if ($stock <= 0) {
            $outOfStockItems++;
        } elseif ($stock <= $item->low_stock_threshold) {
            $lowStockItems++;
        }
    }

    // Data untuk print
    $printData = [
        'items' => $items,
        'categories' => $categories,
        'totalItems' => $totalItems,
        'totalStockValue' => $totalStockValue,
        'lowStockItems' => $lowStockItems,
        'outOfStockItems' => $outOfStockItems,
        'selectedPeriod' => $selectedPeriod,
        'selectedYear' => $selectedYear,
        'selectedMonth' => $selectedMonth,
        'currentSummary' => $currentSummary,
        'previousSummary' => $previousSummary,
        'prevYear' => $prevYear,
        'prevMonth' => $prevMonth,
        'generatedAt' => now(),
        'generatedBy' => auth()->user(),
        'filters' => [
            'search' => $request->get('search'),
            'category_id' => $request->get('category_id'),
            'stock_status' => $request->get('stock_status'),
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ]
    ];

    return view('items.print-report', $printData);
}

    public function compareMonths(Request $request)
    {
        $currentPeriod = $request->get('current_period', now()->format('Y-m'));
        $comparePeriod = $request->get('compare_period', now()->subMonth()->format('Y-m'));

        [$currentYear, $currentMonth] = explode('-', $currentPeriod);
        [$compareYear, $compareMonth] = explode('-', $comparePeriod);

        // Get data untuk kedua periode
        $items = Item::with([
            'category',
            'monthlyBalances' => function($q) use ($currentYear, $currentMonth, $compareYear, $compareMonth) {
                $q->where(function($subQuery) use ($currentYear, $currentMonth, $compareYear, $compareMonth) {
                    $subQuery->where(function($current) use ($currentYear, $currentMonth) {
                        $current->where('year', $currentYear)->where('month', $currentMonth);
                    })
                    ->orWhere(function($compare) use ($compareYear, $compareMonth) {
                        $compare->where('year', $compareYear)->where('month', $compareMonth);
                    });
                });
            }
        ])->get();

        // Process items untuk mendapatkan current dan compare balance
        $items = $items->map(function($item) use ($currentYear, $currentMonth, $compareYear, $compareMonth) {
            $currentBalance = $item->monthlyBalances->where('year', $currentYear)
                                                   ->where('month', $currentMonth)
                                                   ->first();
            
            $compareBalance = $item->monthlyBalances->where('year', $compareYear)
                                                   ->where('month', $compareMonth)
                                                   ->first();
            
            $item->currentBalance = $currentBalance;
            $item->compareBalance = $compareBalance;
            
            return $item;
        });

        $availablePeriods = MonthlyStockBalance::getAvailablePeriods();

        // Statistik perbandingan
        $currentSummary = MonthlyStockBalance::getPeriodSummary($currentYear, $currentMonth);
        $compareSummary = MonthlyStockBalance::getPeriodSummary($compareYear, $compareMonth);

        return view('items.compare-months', compact(
            'items', 'availablePeriods', 'currentPeriod', 'comparePeriod',
            'currentSummary', 'compareSummary', 'currentYear', 'currentMonth',
            'compareYear', 'compareMonth'
        ));
    }

    public function monthlyHistory(Item $item)
    {
        $history = MonthlyStockBalance::getItemHistory($item->id, 12);
        
        // Chart data untuk grafik
        $chartData = $history->reverse()->map(function($balance) {
            return [
                'period' => $balance->formatted_period,
                'opening_stock' => $balance->opening_stock,
                'closing_stock' => $balance->closing_stock,
                'stock_in' => $balance->stock_in,
                'stock_out' => $balance->stock_out,
                'net_change' => $balance->net_change
            ];
        });

        return view('items.monthly-history', compact('item', 'history', 'chartData'));
    }

    // Method untuk create monthly balance - sudah tidak diperlukan karena selalu ada
    public function createMonthlyBalance(Item $item)
    {
        return response()->json([
            'success' => false,
            'message' => 'Monthly balance system sudah aktif untuk semua item'
        ]);
    }
}
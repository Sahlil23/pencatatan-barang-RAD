<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\MonthlyKitchenStockBalance;
use App\Models\KitchenStockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KitchenStockController extends Controller
{
    public function index(Request $request)
    {
        // Query items yang memiliki kitchen stock
        $query = Item::whereHas('monthlyKitchenBalances')
            ->with(['category', 'currentKitchenBalance']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'available':
                    $query->whereHas('currentKitchenBalance', function($q) {
                        $q->where('closing_stock', '>', 5); // Threshold sementara 5
                    });
                    break;
                case 'low':
                    $query->whereHas('currentKitchenBalance', function($q) {
                        $q->where('closing_stock', '<=', 5)
                          ->where('closing_stock', '>', 0);
                    });
                    break;
                case 'out':
                    $query->whereHas('currentKitchenBalance', function($q) {
                        $q->where('closing_stock', '<=', 0);
                    });
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
        $categories = Category::all();

        // Statistics
        $stats = [
            'total_items' => $items->total(),
            'available_items' => Item::whereHas('currentKitchenBalance', function($q) {
                $q->where('closing_stock', '>', 5);
            })->count(),
            'low_stock_items' => Item::whereHas('currentKitchenBalance', function($q) {
                $q->where('closing_stock', '<=', 5)->where('closing_stock', '>', 0);
            })->count(),
            'out_of_stock_items' => Item::whereHas('currentKitchenBalance', function($q) {
                $q->where('closing_stock', '<=', 0);
            })->count(),
        ];

        return view('kitchen.index', compact('items', 'categories', 'stats'));
    }

    public function transfer()
    {
        // Items yang ada di warehouse untuk ditransfer
        $warehouseItems = Item::with(['category', 'currentBalance'])
            ->whereHas('currentBalance', function($q) {
                $q->where('closing_stock', '>', 0);
            })
            ->get();

        $categories = Category::all();

        return view('kitchen.transfer', compact('warehouseItems', 'categories'));
    }

    public function processTransfer(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:255'
        ]);

        $item = Item::findOrFail($request->item_id);

        try {
            $result = $item->transferToKitchen(
                $request->quantity,
                $request->notes,
                auth()->id()
            );

            return redirect()->route('kitchen.index')->with('success', 
                "Berhasil transfer {$request->quantity} {$item->unit} {$item->item_name} ke dapur"
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Transfer gagal: ' . $e->getMessage());
        }
    }

    public function usage()
    {
        // Items yang ada di kitchen untuk digunakan
        $kitchenItems = Item::whereHas('currentKitchenBalance', function($q) {
                $q->where('closing_stock', '>', 0);
            })
            ->with(['category', 'currentKitchenBalance'])
            ->get();

        $categories = Category::all();

        return view('kitchen.usage', compact('kitchenItems', 'categories'));
    }

    public function processUsage(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:255'
        ]);

        $item = Item::findOrFail($request->item_id);

        try {
            $result = $item->useKitchenStock(
                $request->quantity,
                $request->notes,
                auth()->id()
            );

            return redirect()->route('kitchen.index')->with('success', 
                "Berhasil mencatat penggunaan {$request->quantity} {$item->unit} {$item->item_name}"
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Penggunaan gagal: ' . $e->getMessage());
        }
    }

    public function processMultipleUsage(Request $request)
    {
        $request->validate([
            'usages' => 'required|array|min:1|max:50',
            'usages.*.item_id' => 'required|exists:items,id',
            'usages.*.quantity' => 'required|numeric|min:0.1',
            'usages.*.usage_date' => 'nullable|date',
            'usages.*.notes' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $createdUsages = [];
            $errors = [];

            foreach ($request->usages as $index => $usageData) {
                $item = Item::find($usageData['item_id']);
                $kitchenBalance = MonthlyKitchenStockBalance::getOrCreateBalance($item->id);
                
                // Validasi stock untuk usage
                if ($usageData['quantity'] > $kitchenBalance->closing_stock) {
                    $errors[] = "Baris " . ($index + 1) . ": Stock {$item->item_name} tidak cukup ({$kitchenBalance->closing_stock} tersedia)";
                    continue;
                }

                // Create usage transaction
                $usage = KitchenStockTransaction::create([
                    'item_id' => $usageData['item_id'],
                    'user_id' => auth()->id(),
                    'transaction_type' => 'USAGE',
                    'quantity' => $usageData['quantity'],
                    'notes' => $usageData['notes'],
                    'transaction_date' => $usageData['usage_date'] ?? now(),
                    'warehouse_transaction_id' => null
                ]);

                // Update kitchen balance
                $kitchenBalance->updateMovement('USAGE', $usageData['quantity']);

                $createdUsages[] = $usage;
            }

            // Jika ada errors, rollback semua
            if (!empty($errors)) {
                DB::rollback();
                return redirect()->back()
                    ->with('error', 'Beberapa penggunaan gagal: ' . implode('; ', $errors))
                    ->withInput();
            }

            DB::commit();

            $count = count($createdUsages);
            return redirect()->route('kitchen.index')
                ->with('success', "{$count} penggunaan stock dapur berhasil dicatat!");

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal menyimpan penggunaan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function adjustment()
    {
        // Items yang ada di kitchen untuk disesuaikan
        $kitchenItems = Item::whereHas('monthlyKitchenBalances')
            ->with(['category', 'currentKitchenBalance'])
            ->get();

        $categories = Category::all();

        return view('kitchen.adjustment', compact('kitchenItems', 'categories'));
    }

    public function processAdjustment(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'new_quantity' => 'required|numeric|min:0',
            'notes' => 'required|string|max:255'
        ]);

        $item = Item::findOrFail($request->item_id);

        try {
            $result = $item->adjustKitchenStock(
                $request->new_quantity,
                $request->notes,
                auth()->id()
            );

            return redirect()->route('kitchen.index')->with('success', 
                "Berhasil menyesuaikan stock {$item->item_name} dari {$result['old_quantity']} menjadi {$result['new_quantity']}"
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Penyesuaian gagal: ' . $e->getMessage());
        }
    }

    public function transactions(Request $request)
    {
        $query = KitchenStockTransaction::with(['item', 'user'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        // Filter by transaction type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by item
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        $transactions = $query->paginate(20);
        $items = Item::whereHas('kitchenStockTransactions')->get();

        return view('kitchen.transactions', compact('transactions', 'items'));
    }

    public function report(Request $request)
    {
        // Parse periode dari request (default bulan ini)
        $selectedPeriod = $request->get('period', now()->format('Y-m'));
        [$selectedYear, $selectedMonth] = explode('-', $selectedPeriod);
        $selectedYear = (int) $selectedYear;
        $selectedMonth = (int) $selectedMonth;

        // Query items dengan kitchen balance
        $items = Item::whereHas('monthlyKitchenBalances', function($q) use ($selectedYear, $selectedMonth) {
                $q->where('year', $selectedYear)->where('month', $selectedMonth);
            })
            ->with(['category', 'monthlyKitchenBalances' => function($q) use ($selectedYear, $selectedMonth) {
                $q->where('year', $selectedYear)->where('month', $selectedMonth);
            }])
            ->get();

        // Get available periods
        $availablePeriods = MonthlyKitchenStockBalance::getAvailablePeriods();
        
        // Summary
        $summary = MonthlyKitchenStockBalance::getPeriodSummary($selectedYear, $selectedMonth);

        return view('kitchen.report', compact(
            'items', 'selectedPeriod', 'selectedYear', 'selectedMonth',
            'availablePeriods', 'summary'
        ));
    }

    public function printReport(Request $request)
    {
        // Parse periode dari request (default bulan ini)
        $selectedPeriod = $request->get('period', now()->format('Y-m'));
        [$selectedYear, $selectedMonth] = explode('-', $selectedPeriod);
        $selectedYear = (int) $selectedYear;
        $selectedMonth = (int) $selectedMonth;

        // Query items dengan kitchen balance
        $items = Item::whereHas('monthlyKitchenBalances', function($q) use ($selectedYear, $selectedMonth) {
                $q->where('year', $selectedYear)->where('month', $selectedMonth);
            })
            ->with(['category', 'monthlyKitchenBalances' => function($q) use ($selectedYear, $selectedMonth) {
                $q->where('year', $selectedYear)->where('month', $selectedMonth);
            }])
            ->get();

        // Summary
        $summary = MonthlyKitchenStockBalance::getPeriodSummary($selectedYear, $selectedMonth);

        $printData = [
            'items' => $items,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'summary' => $summary,
            'period_name' => \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y')
        ];

        return view('kitchen.print-report', $printData);
    }
}
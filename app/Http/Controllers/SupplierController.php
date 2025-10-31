<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SupplierController extends Controller
{
    public function index()
    {
        // UPDATE: Gunakan withCount untuk stockTransactions dan items melalui stockTransactions
        $suppliers = Supplier::withCount([
            'stockTransactions',
            'stockTransactions as active_transactions_count' => function($q) {
                $q->where('created_at', '>=', now()->subMonths(3));
            },
            'items'
        ])
        ->with(['stockTransactions' => function($q) {
            $q->latest()->take(1);
        }])
        ->latest()
        ->get();
        
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        Supplier::create($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan!');
    }

    public function show(Supplier $supplier)
    {
        // UPDATE: Load relasi melalui stockTransactions
        $supplier->load([
            'stockTransactions' => function($query) {
                $query->with(['item.category', 'user'])
                      ->latest()
                      ->take(20);
            },
            'items' => function($query) {
                $query->with(['category', 'currentBalance']);
            }
        ]);
        
        // Get performance stats
        $stats = $this->getSupplierStats($supplier);
        
        return view('suppliers.show', compact('supplier', 'stats'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil diupdate!');
    }

    public function destroy(Supplier $supplier)
    {
        // UPDATE: Check stockTransactions instead of items
        if ($supplier->stockTransactions()->count() > 0) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Supplier tidak dapat dihapus karena masih memiliki riwayat transaksi!');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus!');
    }

    /**
     * Get supplier performance statistics
     */
    private function getSupplierStats(Supplier $supplier)
    {
        // Overall stats
        $totalTransactions = $supplier->stockTransactions()->count();
        $totalItems = $supplier->items()->count();
        
        // Monthly stats (current month)
        $currentMonth = now();
        $monthlyStats = $supplier->stockTransactions()
            ->whereYear('transaction_date', $currentMonth->year)
            ->whereMonth('transaction_date', $currentMonth->month)
            ->selectRaw('
                transaction_type,
                COUNT(*) as count,
                SUM(quantity) as total_quantity
            ')
            ->groupBy('transaction_type')
            ->get()
            ->keyBy('transaction_type');

        // Recent activity (last 30 days)
        $recentActivity = $supplier->stockTransactions()
            ->where('transaction_date', '>=', now()->subDays(30))
            ->count();

        // Top items by transaction volume
        $topItems = $supplier->stockTransactions()
            ->selectRaw('
                item_id,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN transaction_type = "IN" THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN transaction_type = "OUT" THEN quantity ELSE 0 END) as total_out,
                MAX(transaction_date) as last_transaction
            ')
            ->with('item.category')
            ->groupBy('item_id')
            ->orderByDesc('transaction_count')
            ->take(10)
            ->get();

        // Performance by month (last 6 months)
        $monthlyPerformance = $supplier->stockTransactions()
            ->where('transaction_date', '>=', now()->subMonths(6))
            ->selectRaw('
                YEAR(transaction_date) as year,
                MONTH(transaction_date) as month,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN transaction_type = "IN" THEN quantity ELSE 0 END) as stock_in,
                SUM(CASE WHEN transaction_type = "OUT" THEN quantity ELSE 0 END) as stock_out
            ')
            ->groupByRaw('YEAR(transaction_date), MONTH(transaction_date)')
            ->orderByRaw('YEAR(transaction_date) DESC, MONTH(transaction_date) DESC')
            ->get();

        return [
            'total_transactions' => $totalTransactions,
            'total_items' => $totalItems,
            'monthly_stats' => $monthlyStats,
            'recent_activity' => $recentActivity,
            'top_items' => $topItems,
            'monthly_performance' => $monthlyPerformance,
            'first_transaction' => $supplier->stockTransactions()->oldest()->first(),
            'last_transaction' => $supplier->stockTransactions()->latest()->first(),
        ];
    }

    /**
     * Get supplier report
     */
    public function report(Request $request, Supplier $supplier)
    {
        $startDate = $request->filled('start_date') ? 
            Carbon::parse($request->start_date) : 
            Carbon::now()->startOfMonth();
            
        $endDate = $request->filled('end_date') ? 
            Carbon::parse($request->end_date) : 
            Carbon::now()->endOfMonth();

        $transactions = $supplier->stockTransactions()
            ->with(['item.category', 'user'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $summary = [
            'total_transactions' => $transactions->count(),
            'stock_in' => $transactions->where('transaction_type', 'IN')->sum('quantity'),
            'stock_out' => $transactions->where('transaction_type', 'OUT')->sum('quantity'),
            'adjustments' => $transactions->where('transaction_type', 'ADJUSTMENT')->sum('quantity'),
            'unique_items' => $transactions->unique('item_id')->count(),
        ];

        return view('suppliers.report', compact('supplier', 'transactions', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Export supplier items (melalui stock transactions)
     */
    public function exportItems(Supplier $supplier)
    {
        $items = $supplier->items()->with(['category', 'currentBalance'])->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="supplier-' . $supplier->id . '-items.csv"',
        ];

        $callback = function() use ($items, $supplier) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'SKU',
                'Item Name', 
                'Category',
                'Current Stock',
                'Unit',
                'Low Stock Threshold',
                'Status',
                'Last Transaction',
                'Total Transactions'
            ]);

            foreach ($items as $item) {
                $lastTransaction = $supplier->stockTransactions()
                    ->where('item_id', $item->id)
                    ->latest()
                    ->first();
                    
                $totalTransactions = $supplier->stockTransactions()
                    ->where('item_id', $item->id)
                    ->count();

                fputcsv($file, [
                    $item->sku,
                    $item->item_name,
                    $item->category ? $item->category->category_name : 'No Category',
                    $item->current_stock,
                    $item->unit,
                    $item->low_stock_threshold,
                    $item->stock_status,
                    $lastTransaction ? $lastTransaction->transaction_date->format('Y-m-d H:i:s') : 'Never',
                    $totalTransactions
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
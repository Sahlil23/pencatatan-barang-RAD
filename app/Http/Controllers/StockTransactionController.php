<?php

namespace App\Http\Controllers;

use App\Models\StockTransaction;
use App\Models\Item;
use App\Models\MonthlyStockBalance;
use App\Models\Supplier; // TAMBAH INI
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransaction::with(['item.category', 'supplier']); // TAMBAH supplier

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by transaction type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by item
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        // FILTER BY SUPPLIER
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $transactions = $query->latest()->paginate(20);
        $items = Item::select('id', 'item_name')->get();
        $suppliers = Supplier::all(); 

        return view('stock-transactions.index', compact('transactions', 'items', 'suppliers'));
    }

    public function create()
    {
        $items = Item::with('category')->get();
        $suppliers = Supplier::all(); // TAMBAH INI
        return view('stock-transactions.create', compact('items', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'supplier_id' => 'nullable|exists:suppliers,id', // TAMBAH INI
            'transaction_type' => 'required|in:IN,OUT,ADJUSTMENT',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'required|string|max:255',
            'transaction_date' => 'nullable|date'
        ]);

        $item = Item::findOrFail($request->item_id);
        $monthlyBalance = MonthlyStockBalance::getOrCreateBalance($item->id);
        
        if ($request->transaction_type === 'OUT' && $monthlyBalance->closing_stock < $request->quantity) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Stok tidak mencukupi! Stok saat ini: ' . $monthlyBalance->closing_stock);
        }

        DB::beginTransaction();
        try {
            // Create transaction WITH SUPPLIER
            StockTransaction::create([
                'item_id' => $request->item_id,
                'user_id' => auth()->id(),
                'supplier_id' => $request->supplier_id, // TAMBAH INI
                'transaction_type' => $request->transaction_type,
                'quantity' => $request->quantity,
                'notes' => $request->notes,
                'transaction_date' => $request->transaction_date ?? now()
            ]);

            // Update monthly balance
            switch ($request->transaction_type) {
                case 'IN':
                    $monthlyBalance->updateMovement('IN', $request->quantity);
                    break;
                case 'OUT':
                    $monthlyBalance->updateMovement('OUT', $request->quantity);
                    break;
                case 'ADJUSTMENT':
                    $difference = $request->quantity - $monthlyBalance->closing_stock;
                    $monthlyBalance->updateMovement('ADJUSTMENT', $difference);
                    break;
            }

            DB::commit();

            return redirect()->route('stock-transactions.index')
                ->with('success', 'Transaksi stok berhasil dicatat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function storeMultiple(Request $request)
    {
        $request->validate([
            'transactions' => 'required|array|min:1|max:50',
            'transactions.*.item_id' => 'required|exists:items,id',
            'transactions.*.transaction_type' => 'required|in:IN,OUT,ADJUSTMENT',
            'transactions.*.quantity' => 'required|numeric|min:0.01',
            'transactions.*.transaction_date' => 'nullable|date',
            'transactions.*.notes' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $createdTransactions = [];
            $errors = [];

            foreach ($request->transactions as $index => $transactionData) {
                $item = Item::find($transactionData['item_id']);
                $monthlyBalance = MonthlyStockBalance::getOrCreateBalance($item->id);
                
                // Validasi stok untuk OUT transactions menggunakan monthly balance
                if ($transactionData['transaction_type'] === 'OUT') {
                    if ($transactionData['quantity'] > $monthlyBalance->closing_stock) {
                        $errors[] = "Baris " . ($index + 1) . ": Stok {$item->item_name} tidak cukup ({$monthlyBalance->closing_stock} tersedia)";
                        continue;
                    }
                }

                // Create transaction
                $transaction = StockTransaction::create([
                    'item_id' => $transactionData['item_id'],
                    'user_id' => auth()->id(),
                    'transaction_type' => $transactionData['transaction_type'],
                    'quantity' => $transactionData['quantity'],
                    'notes' => $transactionData['notes'],
                    'transaction_date' => $transactionData['transaction_date'] ?? now()
                ]);

                // Update monthly balance dan current_stock
                switch ($transactionData['transaction_type']) {
                    case 'IN':
                        $monthlyBalance->updateMovement('IN', $transactionData['quantity']);
                        $item->increment('current_stock', $transactionData['quantity']);
                        break;
                    case 'OUT':
                        $monthlyBalance->updateMovement('OUT', $transactionData['quantity']);
                        $item->decrement('current_stock', $transactionData['quantity']);
                        break;
                    case 'ADJUSTMENT':
                        $difference = $transactionData['quantity'] - $monthlyBalance->closing_stock;
                        $monthlyBalance->updateMovement('ADJUSTMENT', $difference);
                        $item->update(['current_stock' => $transactionData['quantity']]);
                        break;
                }

                $createdTransactions[] = $transaction;
            }

            // Jika ada errors, rollback semua
            if (!empty($errors)) {
                DB::rollback();
                return redirect()->back()
                    ->with('error', 'Beberapa transaksi gagal: ' . implode('; ', $errors))
                    ->withInput();
            }

            DB::commit();

            $count = count($createdTransactions);
            return redirect()->route('stock-transactions.index')
                ->with('success', "{$count} transaksi stok berhasil dicatat!");

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(StockTransaction $stockTransaction)
    {
        $stockTransaction->load(['item.category']);
        return view('stock-transactions.show', compact('stockTransaction'));
    }

    public function report(Request $request)
    {
        $startDate = $request->filled('start_date') ? 
            Carbon::parse($request->start_date) : 
            Carbon::now()->startOfMonth();
            
        $endDate = $request->filled('end_date') ? 
            Carbon::parse($request->end_date) : 
            Carbon::now()->endOfMonth();

        // Stock In/Out Summary
        $stockIn = StockTransaction::stockIn()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('quantity');

        $stockOut = StockTransaction::stockOut()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('quantity');

        // Daily transactions
        $dailyTransactions = StockTransaction::selectRaw('
                DATE(created_at) as date,
                transaction_type,
                SUM(quantity) as total_quantity,
                COUNT(*) as transaction_count
            ')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date', 'transaction_type')
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('date');

        // Top items by transaction volume
        $topItems = StockTransaction::selectRaw('
                item_id,
                SUM(quantity) as total_quantity,
                COUNT(*) as transaction_count
            ')
            ->with('item.category')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('item_id')
            ->orderBy('total_quantity', 'desc')
            ->take(10)
            ->get();

        // Net change calculation
        $netChange = $stockIn - $stockOut;
        
        // Total transactions count
        $totalTransactions = StockTransaction::whereBetween('created_at', [$startDate, $endDate])->count();

        return view('stock-transactions.report', compact(
            'stockIn', 
            'stockOut', 
            'dailyTransactions', 
            'topItems',
            'startDate',
            'endDate',
            'netChange',
            'totalTransactions'
        ));
    }

    public function printReport(Request $request)
    {
        $startDate = $request->filled('start_date') ? 
            Carbon::parse($request->start_date) : 
            Carbon::now()->startOfMonth();
            
        $endDate = $request->filled('end_date') ? 
            Carbon::parse($request->end_date) : 
            Carbon::now()->endOfMonth();

        // Stock In/Out Summary
        $stockIn = StockTransaction::stockIn()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('quantity');

        $stockOut = StockTransaction::stockOut()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('quantity');

        // Daily transactions
        $dailyTransactions = StockTransaction::selectRaw('
                DATE(created_at) as date,
                transaction_type,
                SUM(quantity) as total_quantity,
                COUNT(*) as transaction_count
            ')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date', 'transaction_type')
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('date');

        // Top items by transaction volume
        $topItems = StockTransaction::selectRaw('
                item_id,
                SUM(quantity) as total_quantity,
                COUNT(*) as transaction_count
            ')
            ->with('item.category')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('item_id')
            ->orderBy('total_quantity', 'desc')
            ->take(10)
            ->get();

        return view('stock-transactions.print-report', compact(
            'stockIn', 
            'stockOut', 
            'dailyTransactions', 
            'topItems',
            'startDate',
            'endDate'
        ));
    }
}
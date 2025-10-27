<?php

namespace App\Http\Controllers;

use App\Models\StockTransaction;
use App\Models\Item;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransaction::with(['item.category']);

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

        $transactions = $query->latest()->paginate(20);
        $items = Item::select('id', 'item_name')->get();

        
        return view('stock-transactions.index', compact('transactions', 'items'));
    }

    public function create()
    {
        $items = Item::with('category')->get();
        return view('stock-transactions.create', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'transaction_type' => 'required|in:IN,OUT,ADJUSTMENT',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'required|string|max:255',
            'transaction_date' => 'nullable|date'
        ]);

        $item = Item::findOrFail($request->item_id);

        if ($request->transaction_type === 'OUT' && $item->current_stock < $request->quantity) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Stok tidak mencukupi! Stok saat ini: ' . $item->current_stock);
        }

        // Create transaction
        StockTransaction::create([
            'item_id' => $request->item_id,
            'user_id' => auth()->id(),
            'transaction_type' => $request->transaction_type,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'transaction_date' => $request->transaction_date ?? now()
        ]);

        // Update item stock
        switch ($request->transaction_type) {
            case 'IN':
                $item->increment('current_stock', $request->quantity);
                break;
            case 'OUT':
                $item->decrement('current_stock', $request->quantity);
                break;
            case 'ADJUSTMENT':
                // Untuk adjustment, quantity adalah nilai baru, bukan selisih
                $item->update(['current_stock' => $request->quantity]);
                break;
        }

        return redirect()->route('stock-transactions.index')
            ->with('success', 'Transaksi stok berhasil dicatat!');
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
                // Validasi stok untuk OUT transactions
                if ($transactionData['transaction_type'] === 'OUT') {
                    $item = Item::find($transactionData['item_id']);
                    if ($transactionData['quantity'] > $item->current_stock) {
                        $errors[] = "Baris " . ($index + 1) . ": Stok {$item->item_name} tidak cukup ({$item->current_stock} tersedia)";
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

                // Update item stock
                $item = Item::find($transactionData['item_id']);
                switch ($transactionData['transaction_type']) {
                    case 'IN':
                        $item->increment('current_stock', $transactionData['quantity']);
                        break;
                    case 'OUT':
                        $item->decrement('current_stock', $transactionData['quantity']);
                        break;
                    case 'ADJUSTMENT':
                        // Untuk adjustment, quantity adalah nilai baru
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

        return view('stock-transactions.report', compact(
            'stockIn', 
            'stockOut', 
            'dailyTransactions', 
            'topItems',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate halaman print profesional untuk laporan transaksi stok
     */
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
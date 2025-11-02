<?php
// filepath: app/Http/Controllers/CentralWarehouseController.php

namespace App\Http\Controllers;

use App\Models\CentralStockBalance;
use App\Models\CentralStockTransaction;
use App\Models\CentralToBranchWarehouseTransaction;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\BranchWarehouse;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CentralWarehouseController extends Controller
{
    // ========================================
    // 📊 DASHBOARD - NO AJAX
    // ========================================

    /**
     * Dashboard overview gudang pusat
     */
    public function index(Request $request)
    {
        try {
            // Basic stats
            $totalItems = CentralStockBalance::currentMonth()->count();
            $totalStock = CentralStockBalance::currentMonth()->sum('closing_stock');
            $lowStockItems = CentralStockBalance::currentMonth()->lowStock()->count();
            
            // Get warehouses
            $warehouses = Warehouse::where('warehouse_type', 'CENTRAL')->get();
            $items = Item::with('category')->get();

            // Build query untuk stock balances
            $query = CentralStockBalance::currentMonth()
                                       ->with(['item.category', 'warehouse'])
                                       ->orderBy('id', 'desc');

            // Apply filters
            if ($request->filled('warehouse_id')) {
                $query->where('warehouse_id', $request->warehouse_id);
            }

            if ($request->filled('category_id')) {
                $query->whereHas('item', function($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('item', function($q) use ($search) {
                    $q->where('item_name', 'LIKE', "%{$search}%")
                      ->orWhere('item_code', 'LIKE', "%{$search}%");
                });
            }

            // Paginate results
            $stockBalances = $query->paginate(25)->appends($request->query());

            return view('central-warehouse.index', compact(
                'totalItems',
                'totalStock', 
                'lowStockItems',
                'warehouses',
                'items',
                'stockBalances'
            ));

        } catch (\Exception $e) {
            Log::error('Central Warehouse Index Error: ' . $e->getMessage());
            return back()->with('error', 'Error loading dashboard: ' . $e->getMessage());
        }
    }

    // ========================================
    // 📝 STOCK RECEIPT (CREATE)
    // ========================================

    /**
     * Form untuk terima stock dari supplier
     */
    public function receiveStock()
    {
        $warehouses = Warehouse::where('warehouse_type', 'CENTRAL')->get();
        $items = Item::with('category')->get();
        $suppliers = Supplier::all();

        return view('central-warehouse.receive-stock', compact('warehouses', 'items', 'suppliers'));
    }

    /**
     * Process penerimaan stock
     */
    public function storeReceipt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $referenceNo = $this->generateReferenceNo('RCP');
            $transactions = [];

            foreach ($request->items as $itemData) {
                // Create transaction
                $transaction = CentralStockTransaction::create([
                    'item_id' => $itemData['item_id'],
                    'warehouse_id' => $request->warehouse_id,
                    'user_id' => auth()->id(),
                    'supplier_id' => $request->supplier_id,
                    'transaction_type' => 'PURCHASE',
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                    'total_cost' => $itemData['quantity'] * $itemData['unit_cost'],
                    'reference_no' => $referenceNo,
                    'notes' => $request->notes,
                    'transaction_date' => $request->transaction_date
                ]);

                // Update balance
                $balance = CentralStockBalance::getOrCreateBalance(
                    $itemData['item_id'],
                    $request->warehouse_id
                );
                $balance->updateFromTransaction($transaction);

                $transactions[] = $transaction;
            }

            DB::commit();

            Log::info('Stock receipt created', [
                'reference_no' => $referenceNo,
                'warehouse_id' => $request->warehouse_id,
                'supplier_id' => $request->supplier_id,
                'total_items' => count($transactions)
            ]);

            return redirect()->route('central-warehouse.index')
                           ->with('success', 'Stock received successfully. Reference: ' . $referenceNo);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Stock receipt error: ' . $e->getMessage());
            return back()->with('error', 'Error receiving stock: ' . $e->getMessage())->withInput();
        }
    }

    // ========================================
    // 📊 STOCK ADJUSTMENT (UPDATE)
    // ========================================

    /**
     * Form untuk adjustment stock
     */
    public function adjustStock($balanceId)
    {
        $balance = CentralStockBalance::with(['item', 'warehouse'])->findOrFail($balanceId);
        
        return view('central-warehouse.adjust-stock', compact('balance'));
    }

    /**
     * Process stock adjustment
     */
    public function storeAdjustment(Request $request, $balanceId)
    {
        $validator = Validator::make($request->all(), [
            'adjustment_type' => 'required|in:ADD,REDUCE',
            'quantity' => 'required|numeric|min:0.001',
            'reason' => 'required|string|max:500',
            'unit_cost' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $balance = CentralStockBalance::findOrFail($balanceId);
            
            // Validate reduction doesn't exceed available stock
            if ($request->adjustment_type === 'REDUCE' && $request->quantity > $balance->closing_stock) {
                throw new \Exception('Adjustment quantity exceeds available stock');
            }

            $adjustmentQuantity = $request->adjustment_type === 'ADD' ? 
                                 $request->quantity : -$request->quantity;

            // Create adjustment transaction
            $transaction = CentralStockTransaction::create([
                'item_id' => $balance->item_id,
                'warehouse_id' => $balance->warehouse_id,
                'user_id' => auth()->id(),
                'transaction_type' => 'ADJUSTMENT',
                'quantity' => $adjustmentQuantity,
                'unit_cost' => $request->unit_cost ?? 0,
                'total_cost' => abs($adjustmentQuantity) * ($request->unit_cost ?? 0),
                'reference_no' => $this->generateReferenceNo('ADJ'),
                'notes' => $request->reason,
                'transaction_date' => now()
            ]);

            // Update balance
            $balance->updateFromTransaction($transaction);

            DB::commit();

            Log::info('Stock adjustment completed', [
                'balance_id' => $balanceId,
                'adjustment_type' => $request->adjustment_type,
                'quantity' => $request->quantity,
                'reference_no' => $transaction->reference_no
            ]);

            return redirect()->route('central-warehouse.index')
                           ->with('success', 'Stock adjusted successfully. Reference: ' . $transaction->reference_no);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Stock adjustment error: ' . $e->getMessage());
            return back()->with('error', 'Error adjusting stock: ' . $e->getMessage())->withInput();
        }
    }

    // ========================================
    // 📤 DISTRIBUTION TO BRANCH (CREATE)
    // ========================================

    /**
     * Form untuk distribusi ke cabang
     */
    public function distributeStock($balanceId)
    {
        try {
            $balance = CentralStockBalance::with(['item', 'warehouse'])->findOrFail($balanceId);
            
            // ✅ FIXED: Use Warehouse model with correct filter and relations
            $branchWarehouses = Warehouse::where('warehouse_type', 'branch')
                                    ->where('status', 'ACTIVE')
                                    ->with(['branch' => function($query) {
                                        $query->select('id', 'branch_name', 'branch_code', 'city');
                                    }])
                                    ->orderBy('warehouse_name')
                                    ->get(['id', 'warehouse_name', 'warehouse_code', 'address', 'branch_id']);
            
            // Debug log
            \Log::info('Branch warehouses loaded for distribution', [
                'count' => $branchWarehouses->count(),
                'warehouses' => $branchWarehouses->map(function($w) {
                    return [
                        'id' => $w->id,
                        'name' => $w->warehouse_name,
                        'branch' => $w->branch ? $w->branch->branch_name : 'No Branch'
                    ];
                })->toArray()
            ]);
            
            return view('central-warehouse.distribute-stock', compact('balance', 'branchWarehouses'));

        } catch (\Exception $e) {
            Log::error('Distribute stock form error: ' . $e->getMessage());
            return back()->with('error', 'Error loading distribution form: ' . $e->getMessage());
        }
    }

    /**
     * Process distribusi stock ke cabang
     */
    public function storeDistribution(Request $request, $balanceId)
    {
        $validator = Validator::make($request->all(), [
            'branch_warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $balance = CentralStockBalance::findOrFail($balanceId);
            
            // ✅ Get branch warehouse with validation
            $branchWarehouse = Warehouse::where('id', $request->branch_warehouse_id)
                                  ->where('warehouse_type', 'branch')
                                  ->where('status', 'ACTIVE')
                                  ->firstOrFail();
        
            // Validate stock availability
            if ($request->quantity > $balance->closing_stock) {
                throw new \Exception('Distribution quantity exceeds available stock');
            }

            // ✅ FIXED: Create distribution transaction with only existing columns
            $transaction = CentralToBranchWarehouseTransaction::create([
                'central_warehouse_id' => $balance->warehouse_id,
                'branch_warehouse_id' => $request->branch_warehouse_id,
                'item_id' => $balance->item_id,
                'user_id' => auth()->id(),
                'transaction_type' => 'DISTRIBUTION',
                'quantity' => $request->quantity,
                'reference_no' => $this->generateReferenceNo('DIST'),
                'notes' => $request->notes,
                'transaction_date' => now()
                // ❌ Removed: 'unit_cost', 'total_cost', 'status'
            ]);

            // Create central stock out transaction
            $centralTransaction = CentralStockTransaction::create([
                'item_id' => $balance->item_id,
                'warehouse_id' => $balance->warehouse_id,
                'user_id' => auth()->id(),
                'transaction_type' => 'DISTRIBUTE_OUT',
                'quantity' => -$request->quantity,
                'unit_cost' => $balance->item->unit_cost ?? 0,
                'total_cost' => $request->quantity * ($balance->item->unit_cost ?? 0),
                'reference_no' => $transaction->reference_no,
                'notes' => 'Distribution to ' . $branchWarehouse->warehouse_name . ': ' . $request->notes,
                'transaction_date' => now()
            ]);

            // Update central balance
            $balance->updateFromTransaction($centralTransaction);

            DB::commit();

            Log::info('Stock distribution created', [
                'reference_no' => $transaction->reference_no,
                'central_warehouse_id' => $balance->warehouse_id,
                'branch_warehouse_id' => $request->branch_warehouse_id,
                'branch_warehouse_name' => $branchWarehouse->warehouse_name,
                'item_id' => $balance->item_id,
                'quantity' => $request->quantity
            ]);

            return redirect()->route('central-warehouse.index')
                           ->with('success', 'Stock distributed successfully to ' . $branchWarehouse->warehouse_name . '. Reference: ' . $transaction->reference_no);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Stock distribution error: ' . $e->getMessage());
            return back()->with('error', 'Error distributing stock: ' . $e->getMessage())->withInput();
        }
    }

    // ========================================
    // 👁️ VIEW STOCK DETAILS (READ)
    // ========================================

    /**
     * View detail stock balance
     */
    public function show($balanceId)
    {
        $balance = CentralStockBalance::with(['item.category', 'warehouse'])->findOrFail($balanceId);
        
        // Get recent transactions
        $transactions = CentralStockTransaction::where('item_id', $balance->item_id)
                                              ->where('warehouse_id', $balance->warehouse_id)
                                              ->whereYear('transaction_date', $balance->year)
                                              ->whereMonth('transaction_date', $balance->month)
                                              ->with(['user', 'supplier'])
                                              ->orderBy('transaction_date', 'desc')
                                              ->limit(20)
                                              ->get();

        return view('central-warehouse.show', compact('balance', 'transactions'));
    }

    // ========================================
    // 🔧 HELPER METHODS
    // ========================================

    /**
     * Generate reference number
     */
    private function generateReferenceNo($prefix)
    {
        $date = now()->format('ymd');
        $sequence = CentralStockTransaction::whereDate('created_at', today())->count() + 1;
        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
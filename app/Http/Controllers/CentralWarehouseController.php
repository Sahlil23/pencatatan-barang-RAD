<?php
// filepath: app/Http/Controllers/CentralWarehouseController.php

namespace App\Http\Controllers;

use App\Models\CentralStockBalance;
use App\Models\CentralStockTransaction;
use App\Models\CentralToBranchWarehouseTransaction;
use App\Models\BranchStockTransaction;
use App\Models\BranchWarehouseMonthlyBalance;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CentralWarehouseController extends Controller
{
    /**
     * Constructor - Apply middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('set.branch.context');
        
        // View access untuk read operations
        $this->middleware('check.warehouse.access:view')->only([
            'index', 
            'show', 
            'transactions'
        ]);
        
        // Write access untuk write operations
        $this->middleware('check.warehouse.access:write')->except([
            'index', 
            'show', 
            'transactions'
        ]);
    }

    // ========================================
    // 📊 DASHBOARD
    // ========================================

    /**
     * Dashboard overview gudang pusat
     */
    public function index(Request $request)
    {
        try {
            // Get current warehouse
            $warehouseId = $this->getWarehouseId($request);
            
            // Get central warehouses (accessible only)
            $warehouses = $this->getAccessibleWarehouses();
            
            // Filter only central type
            $centralWarehouses = $warehouses->where('warehouse_type', 'central');

            // If user has specific warehouse, use it; otherwise use first central warehouse
            if (!$warehouseId && $centralWarehouses->isNotEmpty()) {
                $warehouseId = $centralWarehouses->first()->id;
            }

            // Validate access
            if ($warehouseId) {
                $this->validateWarehouseAccess($warehouseId);
            }

            // Get warehouse display data
            $warehouseData = $warehouseId ? $this->getWarehouseDisplayData($warehouseId) : [];

            // Build query untuk stock balances
            $query = CentralStockBalance::currentMonth()
                ->with(['item.category', 'warehouse'])
                ->orderBy('id', 'desc');

            // Apply warehouse filter
            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            } else {
                // Filter by accessible warehouses
                $query = $this->applyWarehouseFilter($query, 'warehouse_id');
            }

            // Apply category filter
            if ($request->filled('category_id')) {
                $query->whereHas('item', function($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('item', function($q) use ($search) {
                    $q->where('item_name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }

            // Calculate stats based on filtered query
            $statsQuery = clone $query;
            $totalItems = (clone $statsQuery)->count();
            $totalStock = (clone $statsQuery)->sum('closing_stock');
            $lowStockItems = (clone $statsQuery)->lowStock()->count();

            // Get paginated results
            $perPage = $this->getPerPage($request, 25);
            $stockBalances = $query->paginate($perPage)->appends($request->query());

            // Get master data
            $items = Item::with('category')->get();
            $categories = Category::all();

            // Get common view data
            $commonData = $this->getCommonViewData($request);

            return view('central-warehouse.index', array_merge($commonData, [
                'totalItems' => $totalItems,
                'totalStock' => $totalStock,
                'lowStockItems' => $lowStockItems,
                'warehouses' => $centralWarehouses,
                'items' => $items,
                'categories' => $categories,
                'stockBalances' => $stockBalances,
                'currentWarehouseId' => $warehouseId,
                'isReadOnly' => $warehouseId ? ($warehouseData['isReadOnly'] ?? false) : false,
                'canWrite' => $warehouseId ? ($warehouseData['canWrite'] ?? false) : false,
            ]));

        } catch (\Exception $e) {
            Log::error('Central Warehouse Index Error: ' . $e->getMessage());
            return $this->errorResponse('Error loading dashboard: ' . $e->getMessage());
        }
    }

    // ========================================
    // 📝 STOCK RECEIPT (CREATE)
    // ========================================

    /**
     * Form untuk terima stock dari supplier
     */
    public function receiveStock(Request $request)
    {
        // Check permission
        $this->authorize('create', CentralStockTransaction::class);

        // Get writable warehouses
        $warehouses = $this->getAccessibleWarehouses(true)
            ->where('warehouse_type', 'central');

        if ($warehouses->isEmpty()) {
            return $this->errorResponse('You do not have access to any central warehouse.');
        }

        $items = Item::with('category')->where('is_active', true)->get();
        $suppliers = Supplier::where('status', 'ACTIVE')->get();

        $commonData = $this->getCommonViewData($request);

        return view('central-warehouse.receive-stock', array_merge($commonData, [
            'warehouses' => $warehouses,
            'items' => $items,
            'suppliers' => $suppliers
        ]));
    }

    /**
     * Process penerimaan stock
     */
    public function storeReceipt(Request $request)
    {
        // Validate access
        $this->validateWarehouseAccess($request->warehouse_id, true);

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        return $this->executeTransaction(
            function () use ($validated) {
                $referenceNo = $this->generateReferenceNo('RCP');
                $transactions = [];

                foreach ($validated['items'] as $itemData) {
                    // Create transaction
                    $transaction = CentralStockTransaction::create([
                        'item_id' => $itemData['item_id'],
                        'warehouse_id' => $validated['warehouse_id'],
                        'user_id' => $this->currentUser()->id,
                        'supplier_id' => $validated['supplier_id'],
                        'transaction_type' => 'PURCHASE',
                        'quantity' => $itemData['quantity'],
                        'unit_cost' => $itemData['unit_cost'],
                        'total_cost' => $itemData['quantity'] * $itemData['unit_cost'],
                        'reference_no' => $referenceNo,
                        'notes' => $validated['notes'] ?? null,
                        'transaction_date' => $validated['transaction_date']
                    ]);

                    // Update balance
                    $balance = CentralStockBalance::getOrCreateBalance(
                        $itemData['item_id'],
                        $validated['warehouse_id']
                    );
                    $balance->updateFromTransaction($transaction);

                    $transactions[] = $transaction;
                }

                // Log activity
                $this->logActivity('stock_receipt', 'CentralStockTransaction', null, [
                    'reference_no' => $referenceNo,
                    'warehouse_id' => $validated['warehouse_id'],
                    'supplier_id' => $validated['supplier_id'],
                    'total_items' => count($transactions)
                ]);

                return [
                    'reference_no' => $referenceNo,
                    'total_items' => count($transactions)
                ];
            },
            'Stock received successfully. Reference: ' . ($referenceNo ?? ''),
            'Failed to receive stock'
        );
    }

    // ========================================
    // 📊 STOCK ADJUSTMENT (UPDATE)
    // ========================================

    /**
     * Form untuk adjustment stock
     */
    public function adjustStock(Request $request, $balanceId)
    {
        $balance = CentralStockBalance::with(['item', 'warehouse'])->findOrFail($balanceId);
        
        // Validate access
        $this->validateWarehouseAccess($balance->warehouse_id, true);
        
        // Check policy
        $this->authorize('manageStock', $balance->warehouse);

        $commonData = $this->getCommonViewData($request);
        
        return view('central-warehouse.adjust-stock', array_merge($commonData, [
            'balance' => $balance
        ]));
    }

    /**
     * Process stock adjustment
     */
    public function storeAdjustment(Request $request, $balanceId)
    {
        $balance = CentralStockBalance::findOrFail($balanceId);
        
        // Validate access
        $this->validateWarehouseAccess($balance->warehouse_id, true);

        $validated = $request->validate([
            'adjustment_type' => 'required|in:ADD,REDUCE',
            'quantity' => 'required|numeric|min:0.001',
            'reason' => 'required|string|max:500',
            'unit_cost' => 'nullable|numeric|min:0'
        ]);

        // Additional validation
        if ($validated['adjustment_type'] === 'REDUCE' && $validated['quantity'] > $balance->closing_stock) {
            return $this->errorResponse('Adjustment quantity exceeds available stock (' . $balance->closing_stock . ')');
        }

        return $this->executeTransaction(
            function () use ($validated, $balance) {
                $adjustmentQuantity = $validated['adjustment_type'] === 'ADD' ? 
                                     $validated['quantity'] : -$validated['quantity'];

                $referenceNo = $this->generateReferenceNo('ADJ');

                // Create adjustment transaction
                $transaction = CentralStockTransaction::create([
                    'item_id' => $balance->item_id,
                    'warehouse_id' => $balance->warehouse_id,
                    'user_id' => $this->currentUser()->id,
                    'transaction_type' => 'ADJUSTMENT',
                    'quantity' => $adjustmentQuantity,
                    'unit_cost' => $validated['unit_cost'] ?? 0,
                    'total_cost' => abs($adjustmentQuantity) * ($validated['unit_cost'] ?? 0),
                    'reference_no' => $referenceNo,
                    'notes' => $validated['reason'],
                    'transaction_date' => now()
                ]);

                // Update balance
                $balance->updateFromTransaction($transaction);

                // Log activity
                $this->logActivity('stock_adjustment', 'CentralStockTransaction', $transaction->id, [
                    'balance_id' => $balance->id,
                    'adjustment_type' => $validated['adjustment_type'],
                    'quantity' => $validated['quantity'],
                    'reference_no' => $referenceNo
                ]);

                return ['reference_no' => $referenceNo];
            },
            'Stock adjusted successfully. Reference: ' . ($referenceNo ?? ''),
            'Failed to adjust stock'
        );
    }

    // ========================================
    // 📤 DISTRIBUTION TO BRANCH (CREATE)
    // ========================================

    /**
     * Form untuk distribusi ke cabang
     */
    public function distributeStock(Request $request, $balanceId)
    {
        try {
            $balance = CentralStockBalance::with(['item', 'warehouse'])->findOrFail($balanceId);
            
            // Validate access
            $this->validateWarehouseAccess($balance->warehouse_id, true);
            
            // Check policy
            $this->authorize('manageStock', $balance->warehouse);

            // Get accessible branch warehouses
            // Super admin & central users can see all branches
            // Others filtered by their access
            $branchWarehouses = Warehouse::where('warehouse_type', 'branch')
                ->where('status', 'ACTIVE')
                ->with(['branch' => function($query) {
                    $query->select('id', 'branch_name', 'branch_code', 'city');
                }])
                ->orderBy('warehouse_name')
                ->get(['id', 'warehouse_name', 'warehouse_code', 'address', 'branch_id']);

            // Filter by accessible branches if not super admin
            if (!$this->isSuperAdmin() && !$this->isCentralUser()) {
                $accessibleBranchIds = $this->getAccessibleBranches()->pluck('id');
                $branchWarehouses = $branchWarehouses->whereIn('branch_id', $accessibleBranchIds);
            }

            Log::info('Branch warehouses loaded for distribution', [
                'count' => $branchWarehouses->count(),
                'user_role' => $this->currentUser()->role,
                'warehouses' => $branchWarehouses->map(function($w) {
                    return [
                        'id' => $w->id,
                        'name' => $w->warehouse_name,
                        'branch' => $w->branch ? $w->branch->branch_name : 'No Branch'
                    ];
                })->toArray()
            ]);

            $commonData = $this->getCommonViewData($request);
            
            return view('central-warehouse.distribute-stock', array_merge($commonData, [
                'balance' => $balance,
                'branchWarehouses' => $branchWarehouses
            ]));

        } catch (\Exception $e) {
            Log::error('Distribute stock form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading distribution form: ' . $e->getMessage());
        }
    }

    /**
     * Process distribusi stock ke cabang
     */
    public function storeDistribution(Request $request, $balanceId)
    {
        $balance = CentralStockBalance::findOrFail($balanceId);
        
        // Validate access
        $this->validateWarehouseAccess($balance->warehouse_id, true);

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:500'
        ]);

        // Additional validation
        if ($validated['quantity'] > $balance->closing_stock) {
            return $this->errorResponse('Distribution quantity (' . $validated['quantity'] . ') exceeds available stock (' . $balance->closing_stock . ')');
        }

        // Validate branch warehouse
        $branchWarehouse = Warehouse::where('id', $validated['warehouse_id'])
            ->where('warehouse_type', 'branch')
            ->where('status', 'ACTIVE')
            ->firstOrFail();

        return $this->executeTransaction(
            function () use ($validated, $balance, $branchWarehouse) {
                $referenceNo = $this->generateReferenceNo('DIST');

                // 1. Create Central To Branch Transaction
                $distributionTransaction = CentralToBranchWarehouseTransaction::create([
                    'central_warehouse_id' => $balance->warehouse_id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'item_id' => $balance->item_id,
                    'user_id' => $this->currentUser()->id,
                    'transaction_type' => 'DISTRIBUTION',
                    'quantity' => $validated['quantity'],
                    'reference_no' => $referenceNo,
                    'notes' => $validated['notes'],
                    'transaction_date' => now()
                ]);

                // 2. Create Central Stock Transaction (OUT)
                $centralTransaction = CentralStockTransaction::create([
                    'item_id' => $balance->item_id,
                    'warehouse_id' => $balance->warehouse_id,
                    'user_id' => $this->currentUser()->id,
                    'transaction_type' => 'DISTRIBUTE_OUT',
                    'quantity' => -$validated['quantity'],
                    'unit_cost' => $balance->item->unit_cost ?? 0,
                    'total_cost' => $validated['quantity'] * ($balance->item->unit_cost ?? 0),
                    'reference_no' => $referenceNo,
                    'notes' => 'Distribution to ' . $branchWarehouse->warehouse_name . ': ' . $validated['notes'],
                    'transaction_date' => now()
                ]);

                // Update central balance
                $balance->updateFromTransaction($centralTransaction);

                // 3. Create Branch Stock Transaction (IN)
                $branchStockTransaction = BranchStockTransaction::create([
                    'branch_id' => $branchWarehouse->branch_id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'item_id' => $balance->item_id,
                    'transaction_type' => 'IN',
                    'quantity' => $validated['quantity'],
                    'reference_no' => $referenceNo,
                    'notes' => 'Received from Central Warehouse: ' . $validated['notes'],
                    'transaction_date' => now(),
                    'user_id' => $this->currentUser()->id,
                    'central_transaction_id' => $centralTransaction->id
                ]);

                // 4. Update Branch Warehouse Monthly Balance
                $branchBalance = BranchWarehouseMonthlyBalance::firstOrCreate(
                    [
                        'warehouse_id' => $validated['warehouse_id'],
                        'item_id' => $balance->item_id,
                        'month' => now()->month,
                        'year' => now()->year
                    ],
                    [
                        'opening_stock' => 0,
                        'closing_stock' => 0,
                        'stock_in' => 0,
                        'stock_out' => 0,
                        'adjustments' => 0,
                        'is_closed' => false
                    ]
                );

                // Update branch balance
                $branchBalance->stock_in += $validated['quantity'];
                $branchBalance->closing_stock += $validated['quantity'];
                $branchBalance->save();

                // Log activity
                $this->logActivity('stock_distribution', 'CentralToBranchWarehouseTransaction', $distributionTransaction->id, [
                    'reference_no' => $referenceNo,
                    'central_warehouse_id' => $balance->warehouse_id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'branch_id' => $branchWarehouse->branch_id,
                    'branch_warehouse_name' => $branchWarehouse->warehouse_name,
                    'item_id' => $balance->item_id,
                    'quantity' => $validated['quantity'],
                    'central_transaction_id' => $centralTransaction->id,
                    'branch_transaction_id' => $branchStockTransaction->id,
                    'branch_balance_id' => $branchBalance->id
                ]);

                return [
                    'reference_no' => $referenceNo,
                    'warehouse_name' => $branchWarehouse->warehouse_name
                ];
            },
            'Stock distributed successfully to ' . $branchWarehouse->warehouse_name . '. Reference: ' . ($referenceNo ?? ''),
            'Failed to distribute stock'
        );
    }

    // ========================================
    // 👁️ VIEW STOCK DETAILS (READ)
    // ========================================

    /**
     * View detail stock balance
     */
    public function show(Request $request, $balanceId)
    {
        $balance = CentralStockBalance::with(['item.category', 'warehouse'])->findOrFail($balanceId);
        
        // Validate access
        $this->validateWarehouseAccess($balance->warehouse_id);

        // Check policy
        $this->authorize('view', $balance->warehouse);

        // Get warehouse display data
        $warehouseData = $this->getWarehouseDisplayData($balance->warehouse_id);
        
        // Get recent transactions
        $transactionsQuery = CentralStockTransaction::where('item_id', $balance->item_id)
            ->where('warehouse_id', $balance->warehouse_id)
            ->whereYear('transaction_date', $balance->year)
            ->whereMonth('transaction_date', $balance->month)
            ->with(['user', 'supplier'])
            ->orderBy('transaction_date', 'desc');

        // Apply date range if provided
        $transactionsQuery = $this->applyDateRangeFilter($transactionsQuery, $request, 'transaction_date', 30);

        $transactions = $transactionsQuery->paginate(20);

        $commonData = $this->getCommonViewData($request);

        return view('central-warehouse.show', array_merge($commonData, [
            'balance' => $balance,
            'transactions' => $transactions,
            'isReadOnly' => $warehouseData['isReadOnly'],
            'canWrite' => $warehouseData['canWrite']
        ]));
    }

    // ========================================
    // 📋 TRANSACTIONS LIST
    // ========================================

    /**
     * Daftar transaksi stok central
     */
    public function transactions(Request $request)
    {
        try {
            $transactionTypes = [
                'PURCHASE' => 'Pembelian',
                'PURCHASE_RETURN' => 'Return Pembelian',
                'DISTRIBUTE_OUT' => 'Distribusi Keluar',
                'BRANCH_RETURN' => 'Return Cabang',
                'ADJUSTMENT' => 'Penyesuaian',
                'WASTE' => 'Barang Rusak',
            ];

            // Get accessible central warehouses
            $warehouses = $this->getAccessibleWarehouses()
                ->where('warehouse_type', 'central')
                ->where('status', 'ACTIVE');

            // Build query
            $query = CentralStockTransaction::with([
                'item.category',
                'warehouse',
                'user',
                'supplier'
            ])->orderBy('transaction_date', 'desc');

            // Apply warehouse filter (accessible only)
            if (!$this->isSuperAdmin()) {
                $query = $this->applyWarehouseFilter($query, 'warehouse_id');
            }

            // Apply specific warehouse filter from request
            if ($request->filled('warehouse_id')) {
                $this->validateWarehouseAccess($request->warehouse_id);
                $query->where('warehouse_id', $request->warehouse_id);
            }

            // Apply transaction type filter
            if ($request->filled('transaction_type')) {
                $query->where('transaction_type', $request->transaction_type);
            }

            // Apply date range filter
            $query = $this->applyDateRangeFilter($query, $request, 'transaction_date', 30);

            // Apply search filter
            $query = $this->applySearchFilter($query, $request, [
                'reference_no',
                'notes'
            ]);

            // Additional search for item
            if ($request->filled('search')) {
                $search = $request->search;
                $query->orWhereHas('item', function($itemQuery) use ($search) {
                    $itemQuery->where('item_name', 'like', "%{$search}%")
                             ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            // Calculate summary before pagination
            $summaryQuery = clone $query;
            $summary = [
                'total_transactions' => (clone $summaryQuery)->count(),
                'total_quantity_in' => (clone $summaryQuery)->where('quantity', '>', 0)->sum('quantity'),
                'total_quantity_out' => abs((clone $summaryQuery)->where('quantity', '<', 0)->sum('quantity')),
                'total_value' => (clone $summaryQuery)->sum('total_cost'),
            ];

            // Get paginated transactions
            $perPage = $this->getPerPage($request, 25);
            $transactions = $query->paginate($perPage)->appends($request->query());

            // Get common view data
            $commonData = $this->getCommonViewData($request);

            return view('central-warehouse.transactions', array_merge($commonData, [
                'transactions' => $transactions,
                'transactionTypes' => $transactionTypes,
                'warehouses' => $warehouses,
                'summary' => $summary
            ]));

        } catch (\Exception $e) {
            Log::error('Central warehouse transactions error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return $this->errorResponse('Failed to load transactions: ' . $e->getMessage());
        }
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
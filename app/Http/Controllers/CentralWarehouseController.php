<?php

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
        // $this->authorize('create', CentralStockTransaction::class);

        // Get writable warehouses
        $warehouses = $this->getAccessibleWarehouses(true)
            ->where('warehouse_type', 'central');

        if ($warehouses->isEmpty()) {
            return $this->errorResponse('You do not have access to any central warehouse.');
        }

        $items = Item::with('category')->where('status', 'ACTIVE')->get();
        $suppliers = Supplier::where('is_active', true)->get();

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
                // $this->logActivity('stock_receipt', 'CentralStockTransaction', null, [
                //     'reference_no' => $referenceNo,
                //     'warehouse_id' => $validated['warehouse_id'],
                //     'supplier_id' => $validated['supplier_id'],
                //     'total_items' => count($transactions)
                // ]);

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
                // $this->logActivity('stock_adjustment', 'CentralStockTransaction', $transaction->id, [
                //     'balance_id' => $balance->id,
                //     'adjustment_type' => $validated['adjustment_type'],
                //     'quantity' => $validated['quantity'],
                //     'reference_no' => $referenceNo
                // ]);

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
    public function distributeStock(Request $request, $balanceId = null)
    {
        try {
            // Get warehouse ID from balance if provided
            $warehouseId = null;
            $selectedItem = null;
            
            if ($balanceId) {
                $balance = CentralStockBalance::with(['item', 'warehouse'])->findOrFail($balanceId);
                $warehouseId = $balance->warehouse_id;
                $selectedItem = $balance;
                
                // Validate access
                $this->validateWarehouseAccess($warehouseId, true);
            }

            // Get accessible central warehouses
            $centralWarehouses = Warehouse::where('warehouse_type', 'central')
                ->where('status', 'ACTIVE')
                ->orderBy('warehouse_name')
                ->get();

            // Filter by accessible warehouses if not super admin
            if (!$this->isSuperAdmin() && !$this->isCentralLevel()) {
                $accessibleWarehouseIds = $this->getAccessibleWarehouseIds();
                $centralWarehouses = $centralWarehouses->whereIn('id', $accessibleWarehouseIds);
            }

            // Get accessible branch warehouses
            $branchWarehouses = Warehouse::where('warehouse_type', 'branch')
                ->where('status', 'ACTIVE')
                ->with(['branch' => function($query) {
                    $query->select('id', 'branch_name', 'branch_code', 'city');
                }])
                ->orderBy('warehouse_name')
                ->get(['id', 'warehouse_name', 'warehouse_code', 'address', 'branch_id']);

            // Filter by accessible branches if not super admin
            if (!$this->isSuperAdmin() && !$this->isCentralLevel()) {
                $accessibleBranchIds = $this->getAccessibleBranchIds();
                $branchWarehouses = $branchWarehouses->whereIn('branch_id', $accessibleBranchIds);
            }

            // Get all items with current stock
            $items = Item::where('status', 'ACTIVE')
                ->with(['category'])
                ->orderBy('item_name')
                ->get();

            $commonData = $this->getCommonViewData($request);
            
            return view('central-warehouse.distribute-stock', array_merge($commonData, [
                'centralWarehouses' => $centralWarehouses,
                'branchWarehouses' => $branchWarehouses,
                'items' => $items,
                'selectedWarehouseId' => $warehouseId,
                'selectedItem' => $selectedItem,
            ]));

        } catch (\Exception $e) {
            Log::error('Distribute stock form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading distribution form: ' . $e->getMessage());
        }
    }

    /**
     * Process distribusi stock ke cabang
     */
    public function storeDistribution(Request $request, $balanceId = null)
    {
        // ✅ Debug log
        Log::info('=== DISTRIBUTION START ===', [
            'balance_id' => $balanceId,
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax(),
        ]);

        try {
            $validated = $request->validate([
                'source_warehouse_id' => 'required|exists:warehouses,id',
                'destination_warehouse_id' => 'required|exists:warehouses,id',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.notes' => 'nullable|string|max:255',
                'items.*.selected' => 'nullable',
                'general_notes' => 'nullable|string|max:500'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        // Validate warehouse access
        $this->validateWarehouseAccess($validated['source_warehouse_id'], true);

        // Validate source is central warehouse
        $sourceWarehouse = Warehouse::where('id', $validated['source_warehouse_id'])
            ->where('warehouse_type', 'central')
            ->where('status', 'ACTIVE')
            ->firstOrFail();

        // Validate destination is branch warehouse
        $destinationWarehouse = Warehouse::where('id', $validated['destination_warehouse_id'])
            ->where('warehouse_type', 'branch')
            ->where('status', 'ACTIVE')
            ->firstOrFail();

        // Filter selected items
        $selectedItems = collect($validated['items'])
            ->filter(function($item) {
                return isset($item['quantity']) && $item['quantity'] > 0;
            })
            ->values()
            ->toArray();

        Log::info('Filtered items', [
            'total_submitted' => count($validated['items']),
            'filtered_count' => count($selectedItems)
        ]);

        if (empty($selectedItems)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items selected for distribution'
                ], 400);
            }
            
            return redirect()->back()
                ->with('error', 'No items selected for distribution')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $referenceNo = $this->generateReferenceNo('DIST');
            $successCount = 0;
            $errors = [];
            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');
            
            // ✅ FIX: Get general_notes safely with default empty string
            $generalNotes = $validated['general_notes'] ?? '';

            foreach ($selectedItems as $itemData) {
                try {
                    $itemId = $itemData['item_id'];
                    $quantity = $itemData['quantity'];
                    $itemNotes = $itemData['notes'] ?? '';

                    // 1. Get current balance
                    $balance = CentralStockBalance::where('warehouse_id', $validated['source_warehouse_id'])
                        ->where('item_id', $itemId)
                        ->where('month', $currentMonth)
                        ->where('year', $currentYear)
                        ->first();

                    if (!$balance || $balance->closing_stock < $quantity) {
                        $available = $balance ? $balance->closing_stock : 0;
                        $item = Item::find($itemId);
                        $errors[] = "{$item->item_name}: Insufficient stock (Available: {$available}, Requested: {$quantity})";
                        continue;
                    }

                    // ✅ FIX: Build notes safely
                    $transactionNotes = $itemNotes;
                    if (!empty($generalNotes)) {
                        $transactionNotes .= ($transactionNotes ? ' | ' : '') . $generalNotes;
                    }

                    // 2. Create Central To Branch Transaction
                    CentralToBranchWarehouseTransaction::create([
                        'central_warehouse_id' => $validated['source_warehouse_id'],
                        'warehouse_id' => $validated['destination_warehouse_id'],
                        'item_id' => $itemId,
                        'user_id' => $this->currentUser()->id,
                        'transaction_type' => 'DISTRIBUTION',
                        'quantity' => $quantity,
                        'reference_no' => $referenceNo,
                        'notes' => $transactionNotes,
                        'transaction_date' => now(),
                        'status' => 'PENDING'
                    ]);

                    // ✅ FIX: Build central transaction notes safely
                    $centralNotes = "Distribution to {$destinationWarehouse->warehouse_name}";
                    if (!empty($itemNotes)) {
                        $centralNotes .= " | {$itemNotes}";
                    }
                    if (!empty($generalNotes)) {
                        $centralNotes .= " | {$generalNotes}";
                    }

                    // 3. Create Central Stock Transaction (OUT)
                    $centralTransaction = CentralStockTransaction::create([
                        'item_id' => $itemId,
                        'warehouse_id' => $validated['source_warehouse_id'],
                        'user_id' => $this->currentUser()->id,
                        'transaction_type' => 'DISTRIBUTE_OUT',
                        'quantity' => -$quantity,
                        'unit_cost' => $balance->item->unit_cost ?? 0,
                        'total_cost' => $quantity * ($balance->item->unit_cost ?? 0),
                        'reference_no' => $referenceNo,
                        'notes' => $centralNotes,
                        'transaction_date' => now()
                    ]);

                    // Update central balance
                    $balance->updateFromTransaction($centralTransaction);

                    // ✅ FIX: Build branch transaction notes safely
                    $branchNotes = "Received from {$sourceWarehouse->warehouse_name}";
                    if (!empty($itemNotes)) {
                        $branchNotes .= " | {$itemNotes}";
                    }

                    // 4. Create Branch Stock Transaction (IN)
                    // BranchStockTransaction::create([
                    //     'branch_id' => $destinationWarehouse->branch_id,
                    //     'warehouse_id' => $validated['destination_warehouse_id'],
                    //     'item_id' => $itemId,
                    //     'transaction_type' => 'IN',
                    //     'quantity' => $quantity,
                    //     'reference_no' => $referenceNo,
                    //     'notes' => $branchNotes,
                    //     'transaction_date' => now(),
                    //     'user_id' => $this->currentUser()->id,
                    //     'central_transaction_id' => $centralTransaction->id
                    // ]);

                    // 5. Update Branch Warehouse Monthly Balance
                    // $branchBalance = BranchWarehouseMonthlyBalance::firstOrCreate(
                    //     [
                    //         'warehouse_id' => $validated['destination_warehouse_id'],
                    //         'item_id' => $itemId,
                    //         'month' => $currentMonth,
                    //         'year' => $currentYear
                    //     ],
                    //     [
                    //         'opening_stock' => 0,
                    //         'closing_stock' => 0,
                    //         'stock_in' => 0,
                    //         'stock_out' => 0,
                    //         'adjustments' => 0,
                    //         'is_closed' => false
                    //     ]
                    // );

                    // $branchBalance->stock_in = (float)$branchBalance->stock_in + (float)$quantity;
                    // $branchBalance->closing_stock = (float)$branchBalance->opening_stock 
                    //     + (float)$branchBalance->stock_in 
                    //     - (float)$branchBalance->stock_out 
                    //     + (float)$branchBalance->adjustments;
                    // $branchBalance->save();

                    $successCount++;

                } catch (\Exception $e) {
                    Log::error("Error distributing item {$itemId}: " . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    $item = Item::find($itemId);
                    $errors[] = "{$item->item_name}: " . $e->getMessage();
                }
            }

            if ($successCount === 0) {
                DB::rollBack();
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No items were distributed successfully',
                        'errors' => $errors
                    ], 400);
                }
                
                return redirect()->back()
                    ->with('error', 'No items were distributed successfully. Errors: ' . implode('; ', $errors))
                    ->withInput();
            }

            DB::commit();

            // Log activity
            // $this->logActivity('stock_distribution', 'CentralToBranchWarehouseTransaction', null, [
            //     'reference_no' => $referenceNo,
            //     'source_warehouse' => $sourceWarehouse->warehouse_name,
            //     'destination_warehouse' => $destinationWarehouse->warehouse_name,
            //     'items_count' => $successCount
            // ]);

            $message = "✅ {$successCount} items distributed successfully to {$destinationWarehouse->warehouse_name}. Reference: {$referenceNo}";
            
            if (!empty($errors)) {
                $message .= "\n\n⚠️ Warnings:\n" . implode("\n", $errors);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'reference_no' => $referenceNo,
                        'success_count' => $successCount,
                        'errors' => $errors
                    ]
                ]);
            }

            return redirect()->route('central-warehouse.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Distribution error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to distribute stock: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to distribute stock: ' . $e->getMessage())
                ->withInput();
        }
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

    /**
     * Get warehouse items with current stock (AJAX)
     */
    public function getWarehouseItems($warehouseId)
    {
        try {
            // Validate access
            $this->validateWarehouseAccess($warehouseId);

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            // Get items with stock
            $items = CentralStockBalance::where('warehouse_id', $warehouseId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('closing_stock', '>', 0)
                ->with(['item.category'])
                ->get()
                ->map(function($balance) {
                    return [
                        'item_id' => $balance->item_id,
                        'item_name' => $balance->item->item_name,
                        'sku' => $balance->item->sku,
                        'category_name' => $balance->item->category->category_name ?? 'N/A',
                        'unit' => $balance->item->unit,
                        'closing_stock' => $balance->closing_stock,
                        'unit_cost' => $balance->item->unit_cost ?? 0,
                    ];
                });

            return response()->json([
                'success' => true,
                'items' => $items
            ]);

        } catch (\Exception $e) {
            Log::error('Get warehouse items error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
<?php
// filepath: app/Http/Controllers/OutletWarehouseController.php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Item;
use App\Models\Branch;
use App\Models\OutletWarehouseMonthlyBalance;
use App\Models\OutletStockTransaction;
use App\Models\OutletWarehouseToKitchenTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OutletWarehouseController extends Controller
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
            'transactions',
            'getAvailableStock'
        ]);
        
        // Write access untuk write operations
        $this->middleware('check.warehouse.access:write')->except([
            'index',
            'show',
            'transactions',
            'getAvailableStock'
        ]);
    }

    // ============================================================
    // 📊 DASHBOARD & STOCK LIST
    // ============================================================

    /**
     * Display outlet warehouses dashboard
     */
    public function index(Request $request)
    {
        try {
            // Get current branch context
            $branchId = $this->getBranchId($request);
            $currentBranch = $this->getCurrentBranch($request);

            // Build warehouses query
            $query = Warehouse::where('warehouse_type', 'outlet')
                ->where('status', 'ACTIVE')
                ->with('branch');

            // Apply warehouse filter (accessible only)
            if (!$this->isSuperAdmin()) {
                $query = $this->applyWarehouseFilter($query, 'id');
            }

            // Apply current branch filter if branch is selected
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            // Apply search filter
            $query = $this->applySearchFilter($query, $request, [
                'warehouse_name',
                'warehouse_code',
                'address'
            ]);

            $warehouses = $query->orderBy('warehouse_name')->get();

            // Get selected warehouse
            $warehouseId = $request->input('warehouse_id');
            $selectedWarehouse = null;
            
            if ($warehouseId) {
                $selectedWarehouse = $warehouses->firstWhere('id', $warehouseId);
                // Validate access
                $this->validateWarehouseAccess($warehouseId);
            }
            
            if (!$selectedWarehouse && $warehouses->isNotEmpty()) {
                $selectedWarehouse = $warehouses->first();
            }

            // Initialize data
            $stockSummary = ['total_items' => 0, 'total_stock_value' => 0, 'low_stock_items' => 0];
            $distributionStats = ['today' => 0, 'this_week' => 0, 'this_month' => 0];
            $lowStockItems = collect();
            $pendingDistributions = collect();
            $recentTransactions = collect();

            // Load data if warehouse selected
            if ($selectedWarehouse) {
                $currentMonth = (int)date('m');
                $currentYear = (int)date('Y');

                $stockSummary = [
                    'total_items' => OutletWarehouseMonthlyBalance::where('warehouse_id', $selectedWarehouse->id)
                        ->where('month', $currentMonth)
                        ->where('year', $currentYear)
                        ->count(),
                    
                    'total_stock_value' => OutletWarehouseMonthlyBalance::where('warehouse_id', $selectedWarehouse->id)
                        ->where('month', $currentMonth)
                        ->where('year', $currentYear)
                        ->with('item')
                        ->get()
                        ->sum(fn($b) => $b->closing_stock * ($b->item->unit_cost ?? 0)),
                    
                    'low_stock_items' => OutletWarehouseMonthlyBalance::where('warehouse_id', $selectedWarehouse->id)
                        ->where('month', $currentMonth)
                        ->where('year', $currentYear)
                        ->whereHas('item', function($q) {
                            $q->whereColumn('outlet_warehouse_monthly_balances.closing_stock', '<', 'items.low_stock_threshold');
                        })
                        ->count()
                ];

                $lowStockItems = OutletWarehouseMonthlyBalance::where('warehouse_id', $selectedWarehouse->id)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->whereHas('item', function($q) {
                        $q->whereColumn('outlet_warehouse_monthly_balances.closing_stock', '<', 'items.low_stock_threshold');
                    })
                    ->with(['item.category'])
                    ->orderBy('closing_stock', 'asc')
                    ->limit(10)
                    ->get();

                $pendingDistributions = OutletWarehouseToKitchenTransaction::where('outlet_warehouse_id', $selectedWarehouse->id)
                    ->whereIn('status', ['PENDING', 'PREPARED', 'IN_TRANSIT'])
                    ->with(['item']) 
                    ->orderBy('transaction_date', 'desc')
                    ->limit(10)
                    ->get();

                $recentTransactions = OutletStockTransaction::where('outlet_warehouse_id', $selectedWarehouse->id)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->with(['item', 'user'])
                    ->get();

                $distributionStats = [
                    'today' => OutletWarehouseToKitchenTransaction::where('outlet_warehouse_id', $selectedWarehouse->id)
                        ->whereDate('transaction_date', now())
                        ->count(),
                    'this_week' => OutletWarehouseToKitchenTransaction::where('outlet_warehouse_id', $selectedWarehouse->id)
                        ->whereBetween('transaction_date', [now()->startOfWeek(), now()])
                        ->count(),
                    'this_month' => OutletWarehouseToKitchenTransaction::where('outlet_warehouse_id', $selectedWarehouse->id)
                        ->whereMonth('transaction_date', now()->month)
                        ->whereYear('transaction_date', now()->year)
                        ->count(),
                ];
            }

            // Get common view data
            $commonData = $this->getCommonViewData($request);

            return view('outlet-warehouse.index', array_merge($commonData, [
                'warehouses' => $warehouses,
                'selectedWarehouse' => $selectedWarehouse,
                'stockSummary' => $stockSummary,
                'distributionStats' => $distributionStats,
                'lowStockItems' => $lowStockItems,
                'pendingDistributions' => $pendingDistributions,
                'recentTransactions' => $recentTransactions
            ]));

        } catch (\Exception $e) {
            Log::error('Outlet warehouse list error: ' . $e->getMessage());
            return $this->errorResponse('Error loading outlet warehouses: ' . $e->getMessage());
        }
    }

    /**
     * Show warehouse detail with current stock
     */
    public function show(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'outlet')
                ->where('id', $warehouseId)
                ->with('branch')
                ->firstOrFail();

            // Validate access
            $this->validateWarehouseAccess($warehouseId);

            // Check policy
            $this->authorize('view', $warehouse);

            // Get warehouse display data
            $warehouseData = $this->getWarehouseDisplayData($warehouseId);

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            // Build stock balance query
            $stockBalanceQuery = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->with('item.category');

            // Apply search filter
            $stockBalanceQuery = $this->applySearchFilter($stockBalanceQuery, $request, []);
            
            if ($request->filled('search')) {
                $search = $request->search;
                $stockBalanceQuery->whereHas('item', function($q) use ($search) {
                    $q->where('item_name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }

            $perPage = $this->getPerPage($request, 20);
            $stockBalance = $stockBalanceQuery->paginate($perPage);

            // Get recent transactions
            $recentTransactionsQuery = OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->with(['item', 'user']);

            $recentTransactionsQuery = $this->applyDateRangeFilter(
                $recentTransactionsQuery,
                $request,
                'transaction_date',
                30
            );

            $recentTransactions = $recentTransactionsQuery->limit(10)->get();

            // Calculate stats
            $stats = [
                'total_items' => OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->count(),
                
                'total_stock' => OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->sum('closing_stock'),
                
                'total_received' => OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->sum('received_from_branch_warehouse'),
                
                'total_distributed' => OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->sum('distributed_to_kitchen'),
                
                'low_stock_items' => OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->whereHas('item', function($q) {
                        $q->whereColumn('outlet_warehouse_monthly_balances.closing_stock', '<', 'items.low_stock_threshold');
                    })
                    ->count()
            ];

            $commonData = $this->getCommonViewData($request);

            return view('outlet-warehouse.show', array_merge($commonData, [
                'warehouse' => $warehouse,
                'stockBalance' => $stockBalance,
                'recentTransactions' => $recentTransactions,
                'stats' => $stats,
                'isReadOnly' => $warehouseData['isReadOnly'],
                'canWrite' => $warehouseData['canWrite']
            ]));

        } catch (\Exception $e) {
            Log::error('Outlet warehouse detail error: ' . $e->getMessage());
            return $this->errorResponse('Error loading warehouse detail: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 📝 RECEIVE STOCK FROM BRANCH
    // ============================================================

    /**
     * Show receive stock form
     */
    public function receiveCreate(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'outlet')
                ->where('id', $warehouseId)
                ->firstOrFail();

            // Validate access
            $this->validateWarehouseAccess($warehouseId, true);

            // Check policy
            $this->authorize('manageStock', $warehouse);

            $items = Item::where('status', 'ACTIVE')
                ->with('category')
                ->orderBy('item_name')
                ->get();

            $commonData = $this->getCommonViewData($request);

            return view('outlet-warehouse.receive', array_merge($commonData, [
                'warehouse' => $warehouse,
                'items' => $items
            ]));

        } catch (\Exception $e) {
            Log::error('Receive form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading receive form: ' . $e->getMessage());
        }
    }

    /**
     * Store receive stock from branch
     */
    public function receiveStore(Request $request, $warehouseId)
    {
        // Validate access
        $this->validateWarehouseAccess($warehouseId, true);

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.batch_no' => 'nullable|string|max:50',
            'items.*.notes' => 'nullable|string|max:255',
            'transaction_date' => 'required|date|before_or_equal:today',
            'branch_warehouse_transaction_id' => 'nullable|exists:branch_stock_transactions,id',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->executeTransaction(
            function () use ($request, $warehouseId) {
                $warehouse = Warehouse::where('warehouse_type', 'outlet')
                    ->where('id', $warehouseId)
                    ->firstOrFail();

                $successCount = 0;
                $errors = [];

                foreach ($request->items as $itemData) {
                    try {
                        $data = [
                            'outlet_warehouse_id' => $warehouseId,
                            'item_id' => $itemData['item_id'],
                            'quantity' => $itemData['quantity'],
                            'unit_cost' => $itemData['unit_cost'] ?? 0,
                            'batch_no' => $itemData['batch_no'] ?? null,
                            'notes' => $itemData['notes'] ?? $request->notes,
                            'transaction_date' => $request->transaction_date,
                            'branch_warehouse_transaction_id' => $request->branch_warehouse_transaction_id,
                            'user_id' => $this->currentUser()->id,
                            'year' => (int)date('Y', strtotime($request->transaction_date)),
                            'month' => (int)date('m', strtotime($request->transaction_date)),
                            'status' => OutletStockTransaction::STATUS_COMPLETED,
                            'document_no' => $this->generateReferenceNo('RCV'),
                        ];

                        $transaction = OutletStockTransaction::createReceiveFromBranch($data);

                        if ($transaction) {
                            $successCount++;
                        } else {
                            $item = Item::find($itemData['item_id']);
                            $errors[] = "Failed to receive: {$item->item_name}";
                        }

                    } catch (\Exception $e) {
                        $item = Item::find($itemData['item_id']);
                        $errors[] = "{$item->item_name}: " . $e->getMessage();
                    }
                }

                // Log activity
                // $this->logActivity('receive_stock', 'OutletStockTransaction', null, [
                //     'warehouse_id' => $warehouseId,
                //     'success_count' => $successCount,
                //     'errors' => $errors
                // ]);

                return [
                    'warehouse_name' => $warehouse->warehouse_name,
                    'success_count' => $successCount,
                    'errors' => $errors
                ];
            },
            // ✅ FIX: Use callback function
            function($result) {
                $message = "✅ Successfully received {$result['success_count']} items";
                
                if (!empty($result['errors'])) {
                    $message .= " | ⚠️ " . implode(', ', $result['errors']);
                }
                
                return $message;
            },
            '❌ Failed to receive stock'
        );
    }

    // ============================================================
    // 🔧 STOCK ADJUSTMENT
    // ============================================================

    /**
     * Show adjustment form
     */
    public function adjustmentCreate(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'outlet')
                ->where('id', $warehouseId)
                ->with('branch')
                ->firstOrFail();

            // Validate access
            $this->validateWarehouseAccess($warehouseId, true);

            // Check policy
            $this->authorize('manageStock', $warehouse);

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            $stockItems = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('closing_stock', '>', 0)
                ->with('item.category')
                ->orderBy('closing_stock', 'asc')
                ->get();

            $commonData = $this->getCommonViewData($request);

            return view('outlet-warehouse.adjustment', array_merge($commonData, [
                'warehouse' => $warehouse,
                'stockItems' => $stockItems
            ]));

        } catch (\Exception $e) {
            Log::error('Adjustment form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading form: ' . $e->getMessage());
        }
    }

    /**
     * Store adjustment
     */
    public function adjustmentStore(Request $request, $warehouseId)
    {
        // Validate access
        $this->validateWarehouseAccess($warehouseId, true);

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'adjustment_type' => 'required|in:IN,OUT',
            'quantity' => 'required|numeric|min:0.001',
            'reason' => 'required|string|max:500',
            'transaction_date' => 'required|date|before_or_equal:today'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->executeTransaction(
            function () use ($request, $warehouseId) {
                $warehouse = Warehouse::where('warehouse_type', 'outlet')
                    ->where('id', $warehouseId)
                    ->firstOrFail();

                // Validate stock for OUT adjustment
                if ($request->adjustment_type === 'OUT') {
                    $currentMonth = (int)date('m', strtotime($request->transaction_date));
                    $currentYear = (int)date('Y', strtotime($request->transaction_date));

                    $balance = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                        ->where('item_id', $request->item_id)
                        ->where('month', $currentMonth)
                        ->where('year', $currentYear)
                        ->first();

                    if (!$balance || $balance->closing_stock < $request->quantity) {
                        $available = $balance ? $balance->closing_stock : 0;
                        throw new \Exception("Stock tidak mencukupi. Tersedia: {$available}");
                    }
                }

                $quantity = $request->adjustment_type === 'IN' 
                    ? $request->quantity 
                    : -$request->quantity;

                $data = [
                    'outlet_warehouse_id' => $warehouseId,
                    'item_id' => $request->item_id,
                    'quantity' => $quantity,
                    'notes' => $request->reason,
                    'transaction_date' => $request->transaction_date,
                    'user_id' => $this->currentUser()->id,
                ];

                $transaction = OutletStockTransaction::createAdjustment($data);

                if (!$transaction) {
                    throw new \Exception('Failed to create adjustment transaction');
                }

                // Log activity
                // $this->logActivity('stock_adjustment', 'OutletStockTransaction', $transaction->id, [
                //     'warehouse_id' => $warehouseId,
                //     'item_id' => $request->item_id,
                //     'type' => $request->adjustment_type,
                //     'quantity' => $request->quantity
                // ]);

                return $transaction;
            },
            '✅ Adjustment berhasil disimpan',
            '❌ Gagal melakukan adjustment'
        );
    }

    // ============================================================
    // 📤 DISTRIBUTION TO KITCHEN
    // ============================================================

    /**
     * Show distribution form
     */
    public function distributeCreate(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'outlet')
                ->where('id', $warehouseId)
                ->with('branch')
                ->firstOrFail();

            // Validate access
            $this->validateWarehouseAccess($warehouseId, true);

            // Check policy
            $this->authorize('manageStock', $warehouse);

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            $stockItems = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('closing_stock', '>', 0)
                ->with('item.category')
                ->orderBy('item_id')
                ->get();

            $commonData = $this->getCommonViewData($request);

            return view('outlet-warehouse.distribute', array_merge($commonData, [
                'warehouse' => $warehouse,
                'stockItems' => $stockItems
            ]));

        } catch (\Exception $e) {
            Log::error('Distribution form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading form: ' . $e->getMessage());
        }
    }

    /**
     * Store distribution to kitchen
     */
    public function distributeStore(Request $request, $warehouseId)
    {
        // Validate access
        $this->validateWarehouseAccess($warehouseId, true);

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.notes' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $selectedItems = collect($request->input('items', []))
            ->filter(function($item) {
                return isset($item['selected']) && $item['selected'] && ($item['quantity'] ?? 0) > 0;
            })
            ->values()
            ->toArray();

        if (empty($selectedItems)) {
            return $this->errorResponse('Tidak ada item yang dipilih');
        }

        return $this->executeTransaction(
            function () use ($request, $warehouseId, $selectedItems) {
                $warehouse = Warehouse::where('warehouse_type', 'outlet')
                    ->where('id', $warehouseId)
                    ->with('branch')
                    ->firstOrFail();

                if (!$warehouse->branch_id) {
                    throw new \Exception('Warehouse ini tidak memiliki branch_id. Tidak bisa distribusi ke kitchen.');
                }

                $currentMonth = (int)date('m');
                $currentYear = (int)date('Y'); 
                $referenceNo = $this->generateReferenceNo('DIST-KIT');
                $successCount = 0;
                $errors = [];

                foreach ($selectedItems as $item) {
                    try {
                        $itemId = $item['item_id'];
                        $quantity = $item['quantity'];
                        $itemNotes = $item['notes'] ?? '';

                        // Validate stock
                        $balance = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                            ->where('item_id', $itemId)
                            ->where('month', $currentMonth)
                            ->where('year', $currentYear)
                            ->first();

                        if (!$balance || $balance->closing_stock < $quantity) {
                            $available = $balance ? $balance->closing_stock : 0;
                            $errors[] = "Item ID {$itemId}: Stock tidak cukup (Tersedia: {$available})";
                            continue;
                        }

                        // Create outlet warehouse OUT transaction
                        $outletTransaction = OutletStockTransaction::create([
                            'outlet_warehouse_id' => $warehouseId,
                            'item_id' => $itemId,
                            'transaction_type' => 'DISTRIBUTE_TO_KITCHEN',
                            'quantity' => -$quantity,
                            'reference_no' => $referenceNo,
                            'notes' => "Distribusi ke kitchen" 
                                . ($itemNotes ? " | {$itemNotes}" : '') 
                                . ($request->notes ? " | {$request->notes}" : ''),
                            'transaction_date' => now(),
                            'user_id' => $this->currentUser()->id,
                            'year' => $currentYear,
                            'month' => $currentMonth,
                            'status' => 'COMPLETED',
                        ]);

                        // Update outlet balance
                        $balance->distributed_to_kitchen = (float)$balance->distributed_to_kitchen + (float)$quantity;
                        $balance->closing_stock = (float)$balance->opening_stock 
                            + (float)$balance->received_from_branch_warehouse
                            + (float)$balance->received_return_from_kitchen
                            - (float)$balance->distributed_to_kitchen
                            - (float)$balance->transfer_out
                            + (float)$balance->adjustments;
                        
                        $balance->save();

                        // Create kitchen stock IN transaction
                        $kitchenData = [
                            'branch_id' => $warehouse->branch_id,
                            'outlet_warehouse_id' => $warehouseId,
                            'item_id' => $itemId,
                            'quantity' => $quantity,
                            'user_id' => $this->currentUser()->id,
                            'outlet_warehouse_transaction_id' => $outletTransaction->id,
                            'unit_cost' => $balance->item->unit_cost ?? 0,
                            'notes' => "Terima dari outlet warehouse" . ($itemNotes ? " | {$itemNotes}" : ''),
                            'transaction_date' => now(),
                            'year' => $currentYear,
                            'month' => $currentMonth,
                            'status' => 'COMPLETED',
                            'reference_no' => $referenceNo,
                        ];

                        $kitchenTransaction = \App\Models\KitchenStockTransaction::createReceiveFromOutletWarehouse($kitchenData);

                        if (!$kitchenTransaction) {
                            throw new \Exception("Failed to create kitchen transaction");
                        }

                        $successCount++;

                    } catch (\Exception $e) {
                        Log::error("Error processing item {$itemId}: " . $e->getMessage());
                        $errors[] = "Item ID {$itemId}: " . $e->getMessage();
                    }
                }

                if ($successCount === 0) {
                    throw new \Exception("Tidak ada item yang berhasil didistribusikan. Errors: " . implode('; ', $errors));
                }

                // Log activity
                // $this->logActivity('distribute_to_kitchen', 'OutletStockTransaction', null, [
                //     'warehouse_id' => $warehouseId,
                //     'reference_no' => $referenceNo,
                //     'success_count' => $successCount,
                //     'errors' => $errors
                // ]);

                return [
                    'warehouse_name' => $warehouse->warehouse_name,
                    'reference_no' => $referenceNo,
                    'success_count' => $successCount,
                    'errors' => $errors
                ];
            },
            // ✅ FIX: Use callback function to access $result
            function($result) {
                $message = "{$result['success_count']} items distributed successfully to kitchen. Reference: {$result['reference_no']}";
                
                if (!empty($result['errors'])) {
                    $message .= "\n\nWarnings:\n" . implode("\n", $result['errors']);
                }
                
                return $message;
            },
            'Failed to distribute to kitchen'
        );
    }

    // ============================================================
    // 📜 TRANSACTIONS & REPORTS
    // ============================================================

    /**
     * Display transaction history
     */
    public function transactions(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'outlet')
                ->where('id', $warehouseId)
                ->firstOrFail();

            // Validate access
            $this->validateWarehouseAccess($warehouseId);

            // ✅ Get date range from request or use defaults
            $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            // Build query
            $transactionsQuery = OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                ->with(['item', 'user'])
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc');

            // ✅ Apply date range filter manually
            if ($startDate) {
                $transactionsQuery->whereDate('transaction_date', '>=', $startDate);
            }
            if ($endDate) {
                $transactionsQuery->whereDate('transaction_date', '<=', $endDate);
            }

            // ✅ Apply transaction type filter
            if ($request->filled('type')) {
                $transactionsQuery->where('transaction_type', $request->type);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $transactionsQuery->where(function($q) use ($search) {
                    $q->where('reference_no', 'LIKE', "%{$search}%")
                      ->orWhere('document_no', 'LIKE', "%{$search}%")
                      ->orWhere('notes', 'LIKE', "%{$search}%");
                });
            }

            $perPage = $this->getPerPage($request, 50);
            $transactions = $transactionsQuery->paginate($perPage);

            $commonData = $this->getCommonViewData($request);

            return view('outlet-warehouse.transactions', array_merge($commonData, [
                'warehouse' => $warehouse,
                'transactions' => $transactions,
                'startDate' => $startDate,     
                'endDate' => $endDate,         
            ]));

        } catch (\Exception $e) {
            Log::error('Transactions error: ' . $e->getMessage());
            return $this->errorResponse('Error loading transactions: ' . $e->getMessage());
        }
    }

    /**
     * Get available stock (API endpoint)
     */
    public function getAvailableStock(Request $request, $warehouseId)
    {
        try {
            // Validate access
            $this->validateWarehouseAccess($warehouseId);

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            $stock = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('closing_stock', '>', 0)
                ->with('item:id,sku,item_name,unit,unit_cost')
                ->get()
                ->map(function($balance) {
                    return [
                        'item_id' => $balance->item_id,
                        'item_name' => $balance->item->item_name,
                        'sku' => $balance->item->sku,
                        'unit' => $balance->item->unit,
                        'available_stock' => rtrim(rtrim(number_format($balance->closing_stock, 3, '.', ','), '0'), '.'),
                        'unit_cost' => $balance->item->unit_cost,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $stock,
                'can_write' => $this->canWrite($warehouseId)
            ]);

        } catch (\Exception $e) {
            Log::error('Get stock error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // 🔧 HELPER METHODS
    // ============================================================

    /**
     * Generate reference number
     */
    private function generateReferenceNo($prefix = 'TRX')
    {
        $date = date('ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$date}-{$random}";
    }
}
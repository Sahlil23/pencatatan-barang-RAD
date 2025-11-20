<?php
// filepath: d:\xampp\htdocs\Chicking-BJM\app\Http\Controllers\BranchWarehouseController.php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\BranchStockTransaction;  
use App\Models\BranchWarehouseMonthlyBalance;
use App\Models\BranchWarehouseToOutletTransaction;
use App\Models\Item;
use App\Models\Branch;
use App\Models\StockPeriod;
use App\Models\MonthlyKitchenStockBalance;
use App\Models\CentralToBranchWarehouseTransaction;
use App\Models\CentralStockBalance;
use App\Models\CentralStockTransaction;
use App\Models\OutletWarehouseMonthlyBalance;
use App\Models\OutletStockTransaction;  
use App\Models\OutletWarehouseToKitchenTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BranchWarehouseController extends Controller
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
            'getCurrentStock',
            'getTransactionSummary',
            'distributionHistory'
        ]);
        
        // Write access untuk write operations
        $this->middleware('check.warehouse.access:write')->except([
            'index',
            'show',
            'getCurrentStock',
            'getTransactionSummary',
            'distributionHistory'
        ]);
    }

    // ========================================
    // 📊 VIEW STOCK - DASHBOARD
    // ========================================

    /**
     * Display list of branch warehouses with current stock
     */
    public function index(Request $request)
    {
        try {
            // Get accessible branch warehouses
            $warehousesQuery = Warehouse::where('warehouse_type', 'branch')
                ->where('status', 'ACTIVE')
                ->with(['branch' => function($query) {
                    $query->select('id', 'branch_name', 'branch_code', 'city');
                }])
                ->orderBy('warehouse_name');

            // Apply warehouse filter (for non-super-admin)
            if (!$this->isSuperAdmin()) {
                $warehousesQuery = $this->applyWarehouseFilter($warehousesQuery, 'id');
            }

            // Apply current branch filter
            $warehousesQuery = $this->applyCurrentBranchFilter($warehousesQuery, $request, 'branch_id');

            // Apply search filter
            $warehousesQuery = $this->applySearchFilter($warehousesQuery, $request, [
                'warehouse_name',
                'warehouse_code',
                'address'
            ]);

            $perPage = $this->getPerPage($request, 15);
            $warehouses = $warehousesQuery->paginate($perPage);

            $currentPeriod = StockPeriod::where('year', (int)date('Y'))
                ->where('month', (int)date('m'))
                ->first();

            $warehouseStats = [];

            // $totalitems = Item::whereRelation('BranchWarehouseMonthlyBalance', 'warehouse_type', 'branch')
            //     ->where('status', 'ACTIVE')
            //     ->count();

            // $totalitems = Item::whereHas('BranchWarehouseMonthlyBalance', function ($query) {
            // })->count();
            
            foreach ($warehouses as $warehouse) {
                // Calculate stats
                $balance = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouse->id)
                    ->where('month', (int)date('m'))
                    ->where('year', (int)date('Y'))
                    ->selectRaw('COUNT(DISTINCT item_id) as total_items, 
                                SUM(closing_stock) as total_stock')
                    ->first();

                // Calculate total value
                $totalValue = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouse->id)
                    ->where('month', (int)date('m'))
                    ->where('year', (int)date('Y'))
                    ->with('item')
                    ->get()
                    ->sum(function($balance) {
                        return $balance->closing_stock * ($balance->item->unit_cost ?? 0);
                    });

                $warehouseStats[$warehouse->id] = [
                    'total_items' => $balance->total_items ?? 0,
                    'total_stock' => $balance->total_stock ?? 0,
                    'total_value' => $totalValue,
                    'can_write' => $this->canWrite($warehouse->id),
                    'is_read_only' => $this->isReadOnly($warehouse->id)
                ];
            }

            $commonData = $this->getCommonViewData($request);

            return view('branch-warehouse.index', array_merge($commonData, [
                'warehouses' => $warehouses,
                'warehouseStats' => $warehouseStats,
                'currentPeriod' => $currentPeriod,
                // 'totalItems' => $totalitems
            ]));

        } catch (\Exception $e) {
            Log::error('Branch warehouse list error: ' . $e->getMessage());
            return $this->errorResponse('Error loading branch warehouses: ' . $e->getMessage());
        }
    }

    /**
     * Show warehouse detail with current stock
     */
    public function show(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                ->where('id', $warehouseId)
                ->with('branch')
                ->firstOrFail();

            // Validate access
            $this->validateWarehouseAccess($warehouseId);

            // Check policy
            $this->authorize('view', $warehouse);

        $pendingDistributions = CentralToBranchWarehouseTransaction::where('warehouse_id', $warehouseId)
            ->where('status', 'PENDING')
            // ->orWhere('status', 'REJECTED')
            ->with([
                'item.category',
                'centralWarehouse',
                'user'
            ])
            ->orderBy('transaction_date', 'desc')
            ->get();

            // Get warehouse display data
            $warehouseData = $this->getWarehouseDisplayData($warehouseId);

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            // Get stock balance
            $stockBalanceQuery = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->with('item.category');

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $stockBalanceQuery->whereHas('item', function($q) use ($search) {
                    $q->where('item_name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }

            // Apply category filter
            if ($request->filled('category_id')) {
                $stockBalanceQuery->whereHas('item', function($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            $stockBalance = $stockBalanceQuery->paginate(20);

            // Get recent transactions
            $recentTransactionsQuery = BranchStockTransaction::where('warehouse_id', $warehouseId)
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
                'total_items' => BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->count(),
                
                'total_stock' => BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->sum('closing_stock'),
                
                'total_value' => BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->with('item')
                    ->get()
                    ->sum(function($balance) {
                        return $balance->closing_stock * ($balance->item->unit_cost ?? 0);
                    }),
                
                'total_in' => BranchStockTransaction::where('warehouse_id', $warehouseId)
                    ->whereIn('transaction_type', ['IN', 'ADJUSTMENT'])
                    ->whereMonth('transaction_date', $currentMonth)
                    ->whereYear('transaction_date', $currentYear)
                    ->sum('quantity'),
                
                'total_out' => BranchStockTransaction::where('warehouse_id', $warehouseId)
                    ->where('transaction_type', 'OUT')
                    ->whereMonth('transaction_date', $currentMonth)
                    ->whereYear('transaction_date', $currentYear)
                    ->sum('quantity')
            ];

            $commonData = $this->getCommonViewData($request);

            return view('branch-warehouse.show', array_merge($commonData, [
                'warehouse' => $warehouse,
                'stockBalance' => $stockBalance,
                'recentTransactions' => $recentTransactions,
                'stats' => $stats,
                'isReadOnly' => $warehouseData['isReadOnly'],
                'canWrite' => $warehouseData['canWrite'],
                'pendingDistribution' => $pendingDistributions
            ]));

        } catch (\Exception $e) {
            Log::error('Branch warehouse detail error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return $this->errorResponse('Error loading warehouse detail: ' . $e->getMessage());
        }
    }

    /**
     * Get current stock for warehouse (API endpoint)
     */
    public function getCurrentStock(Request $request, $warehouseId)
    {
        try {
            // Validate access
            $this->validateWarehouseAccess($warehouseId);

            $currentMonth = date('m');
            $currentYear = date('Y');

            $stockBalance = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->with('item:id,sku,item_name,unit')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $stockBalance,
                'count' => $stockBalance->count(),
                'total_stock' => $stockBalance->sum('closing_stock'),
                'can_write' => $this->canWrite($warehouseId)
            ]);

        } catch (\Exception $e) {
            Log::error('Get current stock error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // 📝 STOCK RECEIPT (RECEIVE FROM CENTRAL)
    // ========================================

    /**
     * Show form to receive stock from central warehouse
     */
    public function showReceiveStockForm(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                ->where('id', $warehouseId)
                ->firstOrFail();

            // Validate access
            $this->validateWarehouseAccess($warehouseId, true);

            // Check policy
            $this->authorize('manageStock', $warehouse);

            // Get pending central to branch transactions
            $pendingReceive = CentralToBranchWarehouseTransaction::where('warehouse_id', $warehouseId)
                ->with(['item', 'user'])
                ->orderBy('transaction_date', 'desc')
                ->paginate(10);

            // Get all active items
            $items = Item::where('status', 'ACTIVE')
                ->orderBy('item_name')
                ->get(['id', 'sku', 'item_name', 'unit']);

            $commonData = $this->getCommonViewData($request);

            return view('branch-warehouse.receive-stock', array_merge($commonData, [
                'warehouse' => $warehouse,
                'pendingReceive' => $pendingReceive,
                'items' => $items
            ]));

        } catch (\Exception $e) {
            Log::error('Receive stock form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading receive form: ' . $e->getMessage());
        }
    }

    /**
     * Store received stock from central warehouse
     */
    public function storeReceiveStock(Request $request, $warehouseId)
    {
        // Validate access
        $this->validateBranchAccess($warehouseId, true);

        $validator = Validator::make($request->all(), [
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->executeTransaction(
            function () use ($request, $warehouseId) {
                $warehouse = Warehouse::where('warehouse_type', 'branch')
                    ->where('id', $warehouseId)
                    ->firstOrFail();

                $referenceNo = $this->generateReferenceNo('RCV');
                $currentMonth = (int)date('m');
                $currentYear = (int)date('Y');
                $successCount = 0;

                foreach ($request->items as $itemData) {
                    // Create stock transaction
                    $branchTransaction = BranchStockTransaction::create([
                        'branch_id' => $warehouse->branch_id,
                        'warehouse_id' => $warehouseId,
                        'item_id' => $itemData['item_id'],
                        'transaction_type' => 'IN',
                        'quantity' => $itemData['quantity'],
                        'reference_no' => $referenceNo,
                        'notes' => 'Received from central' . ($request->notes ? " | {$request->notes}" : ''),
                        'transaction_date' => $request->transaction_date,
                        'user_id' => $this->currentUser()->id
                    ]);

                    // Update or create monthly balance
                    $balance = BranchWarehouseMonthlyBalance::firstOrCreate(
                        [
                            'warehouse_id' => $warehouseId,
                            'item_id' => $itemData['item_id'],
                            'month' => $currentMonth,
                            'year' => $currentYear
                        ],
                        [
                            'opening_stock' => 0,
                            'stock_in' => 0,
                            'stock_out' => 0,
                            'adjustments' => 0,
                            'closing_stock' => 0,
                            'is_closed' => false
                        ]
                    );

                    // Update balance
                    $balance->stock_in = (float)$balance->stock_in + (float)$itemData['quantity'];
                    $balance->closing_stock = (float)$balance->opening_stock 
                        + (float)$balance->stock_in 
                        - (float)$balance->stock_out 
                        + (float)$balance->adjustments;
                    $balance->save();

                    $successCount++;
                }

                // Log activity
                // $this->logActivity('receive_stock', 'BranchStockTransaction', null, [
                //     'warehouse_id' => $warehouseId,
                //     'reference_no' => $referenceNo,
                //     'total_items' => $successCount
                // ]);

                return [
                    'reference_no' => $referenceNo,
                    'success_count' => $successCount
                ];
            },
            // ✅ FIX: Use callback function instead of direct variable
            function($result) {
                return $result['success_count'] . ' items received successfully. Reference: ' . $result['reference_no'];
            },
            'Failed to receive stock'
        );
    }

    // ========================================
    // 🔧 STOCK ADJUSTMENT
    // ========================================

    /**
     * Show form for stock adjustment
     */
    public function showAdjustmentForm(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                ->where('id', $warehouseId)
                ->firstOrFail();

            // Validate access
            $this->validateWarehouseAccess($warehouseId, true);

            // Check policy
            $this->authorize('manageStock', $warehouse);

            // Get current stock items
            $currentMonth = date('m');
            $currentYear = date('Y');

            $stockItems = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('closing_stock', '>', 0)
                ->with('item')
                ->get();

            $adjustmentTypes = [
                'ADD' => 'Tambah Stock',
                'REDUCE' => 'Kurangi Stock',
                'WASTE' => 'Rusak/Expired',
                'DAMAGE' => 'Kerusakan',
                'RETURN_SUPPLIER' => 'Return ke Supplier'
            ];

            $commonData = $this->getCommonViewData($request);

            return view('branch-warehouse.adjust-stock', array_merge($commonData, [
                'warehouse' => $warehouse,
                'stockItems' => $stockItems,
                'adjustmentTypes' => $adjustmentTypes
            ]));

        } catch (\Exception $e) {
            Log::error('Adjustment form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading adjustment form: ' . $e->getMessage());
        }
    }

    /**
     * Store stock adjustment
     */
    public function storeAdjustment(Request $request, $warehouseId)
    {
        // Validate access
        $this->validateWarehouseAccess($warehouseId, true);

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'adjustment_type' => 'required|in:ADD,REDUCE,WASTE,DAMAGE,RETURN_SUPPLIER',
            'quantity' => 'required|numeric|min:0.001',
            'reason' => 'required|string|min:10|max:500'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->executeTransaction(
            function () use ($request, $warehouseId) {
                $warehouse = Warehouse::where('warehouse_type', 'branch')
                    ->where('id', $warehouseId)
                    ->firstOrFail();

                $currentMonth = (int)date('m');
                $currentYear = (int)date('Y');

                // Get or create balance
                $balance = BranchWarehouseMonthlyBalance::firstOrCreate(
                    [
                        'warehouse_id' => $warehouseId,
                        'item_id' => $request->item_id,
                        'month' => $currentMonth,
                        'year' => $currentYear
                    ],
                    [
                        'opening_stock' => 0,
                        'stock_in' => 0,
                        'stock_out' => 0,
                        'adjustments' => 0,
                        'closing_stock' => 0,
                        'is_closed' => false
                    ]
                );

                // Validation: Only validate stock for REDUCE operations
                if (in_array($request->adjustment_type, ['REDUCE', 'WASTE', 'DAMAGE', 'RETURN_SUPPLIER'])) {
                    if ($balance->closing_stock < $request->quantity) {
                        throw new \Exception("Stock tidak mencukupi. Tersedia: {$balance->closing_stock}, Diminta: {$request->quantity}");
                    }
                }

                // Generate reference
                $referenceNo = $this->generateReferenceNo('ADJ');

                // Determine transaction type
                $transactionType = match($request->adjustment_type) {
                    'ADD' => 'IN',
                    'REDUCE' => 'OUT',
                    'WASTE' => 'WASTAGE',
                    'DAMAGE' => 'WASTAGE',
                    'RETURN_SUPPLIER' => 'TRANSFER_TO_CENTRAL',
                    default => 'OUT'
                };

                // Create stock transaction
                $transaction = BranchStockTransaction::create([
                    'branch_id' => $warehouse->branch_id,
                    'warehouse_id' => $warehouseId,
                    'item_id' => $request->item_id,
                    'transaction_type' => $transactionType,
                    'quantity' => abs($request->quantity),
                    'reference_no' => $referenceNo,
                    'notes' => $request->adjustment_type . ': ' . $request->reason,
                    'transaction_date' => now(),
                    'user_id' => $this->currentUser()->id
                ]);

                // Update balance
                if ($request->adjustment_type === 'ADD') {
                    $balance->adjustments = (float)$balance->adjustments + (float)$request->quantity;
                } else {
                    $balance->stock_out = (float)$balance->stock_out + (float)$request->quantity;
                }

                // Recalculate closing stock
                $balance->closing_stock = (float)$balance->opening_stock 
                    + (float)$balance->stock_in 
                    - (float)$balance->stock_out 
                    + (float)$balance->adjustments;
                
                $balance->save();

                // Log activity
                // $this->logActivity('stock_adjustment', 'BranchStockTransaction', $transaction->id, [
                //     'warehouse_id' => $warehouseId,
                //     'item_id' => $request->item_id,
                //     'type' => $request->adjustment_type,
                //     'quantity' => $request->quantity,
                //     'reference_no' => $referenceNo
                // ]);

                return [
                    'reference_no' => $referenceNo,
                    'adjustment_type' => $request->adjustment_type
                ];
            },
            // ✅ FIX: Use callback function
            function($result) {
                return 'Stock adjusted successfully. Reference: ' . $result['reference_no'];
            },
            'Failed to adjust stock'
        );
    }

    // ========================================
    // 📤 DISTRIBUTION TO OUTLET
    // ========================================

    /**
     * Show form for distribution to outlets
     */
    public function showDistributionForm(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                ->where('id', $warehouseId)
                ->with('branch')
                ->firstOrFail();

            // Validate access
            $this->validateWarehouseAccess($warehouseId, true);

            // Check policy
            $this->authorize('manageStock', $warehouse);

            // Get current stock
            $currentMonth = date('m');
            $currentYear = date('Y');

            $stockItems = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('closing_stock', '>', 0)
                ->with('item')
                ->orderBy('item_id')
                ->get();

            // Get outlets
            $outlets = Warehouse::where('warehouse_type', 'outlet')
                ->where('status', 'ACTIVE')
                ->orderBy('warehouse_name')
                ->get(['id', 'warehouse_name', 'warehouse_code', 'address']);

            $commonData = $this->getCommonViewData($request);

            return view('branch-warehouse.distribute', array_merge($commonData, [
                'warehouse' => $warehouse,
                'stockItems' => $stockItems,
                'outlets' => $outlets
            ]));

        } catch (\Exception $e) {
            Log::error('Distribution form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading distribution form: ' . $e->getMessage());
        }
    }

    /**
     * Store distribution to outlet
     */
    public function storeDistribution(Request $request, $warehouseId)
    {
        // Validate access
        $this->validateWarehouseAccess($warehouseId, true);

        // Custom validation rules
        $rules = [
            'outlet_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
        ];

        // Validate selected items
        foreach ($request->input('items', []) as $index => $item) {
            if (isset($item['selected']) && $item['selected']) {
                $rules["items.{$index}.item_id"] = 'required|exists:items,id';
                $rules["items.{$index}.quantity"] = 'required|numeric|min:0.001';
                $rules["items.{$index}.item_notes"] = 'nullable|string|max:255';
            }
        }

        $validator = Validator::make($request->all(), $rules, [
            'outlet_id.required' => 'Outlet warehouse harus dipilih',
            'outlet_id.exists' => 'Outlet warehouse tidak valid',
            'items.required' => 'Minimal 1 item harus dipilih',
            'items.*.quantity.required' => 'Quantity harus diisi',
            'items.*.quantity.min' => 'Quantity minimal 0.001',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        // Filter selected items
        $selectedItems = collect($request->input('items', []))
            ->filter(function($item) {
                return isset($item['selected']) && $item['selected'] && ($item['quantity'] ?? 0) > 0;
            })
            ->values()
            ->toArray();

        if (empty($selectedItems)) {
            return $this->errorResponse('Tidak ada item yang dipilih atau quantity tidak valid');
        }

        return $this->executeTransaction(
            function () use ($request, $warehouseId, $selectedItems) {
                
                $warehouse = Warehouse::where('warehouse_type', 'branch')
                    ->where('id', $warehouseId)
                    ->firstOrFail();

                $outlet = Warehouse::where('warehouse_type', 'outlet')
                    ->where('id', $request->outlet_id)
                    ->firstOrFail();

                $currentMonth = (int)date('m');
                $currentYear = (int)date('Y');
                $referenceNo = $this->generateReferenceNo('B2O-DIST');
                $successCount = 0;
                $errors = [];
                $currentUser = $this->currentUser();

                foreach ($selectedItems as $index => $item) {
                    try {
                        $itemId = $item['item_id'];
                        $quantity = $item['quantity'];
                        $itemNotes = $item['item_notes'] ?? '';

                        $branchBalance = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                            ->first();

                        if (!$branchBalance || $branchBalance->closing_stock < $quantity) {
                            $available = $branchBalance ? $branchBalance->closing_stock : 0;
                            $errors[] = "Item ID {$itemId}: Stock tidak mencukupi (Tersedia: {$available}, Diminta: {$quantity})";
                            continue;
                        }

                       
                        $branchTransaction = BranchStockTransaction::create([
                            'branch_id' => $warehouse->branch_id,
                            'warehouse_id' => $warehouseId,
                            'item_id' => $itemId,
                            'transaction_type' => 'OUT', 
                            'quantity' => $quantity,
                            'reference_no' => $referenceNo,
                            'notes' => "Distribusi ke outlet {$outlet->warehouse_name}" 
                                . ($itemNotes ? " | {$itemNotes}" : '') 
                                . ($request->notes ? " | {$request->notes}" : ''),
                            'transaction_date' => now(),
                            'user_id' => $currentUser->id
                        ]);

                        // 3. Update BRANCH balance (Sudah Benar)
                        $branchBalance->stock_out = (float)$branchBalance->stock_out + (float)$quantity;
                        $branchBalance->closing_stock = $branchBalance->opening_stock + $branchBalance->stock_in - $branchBalance->stock_out + $branchBalance->adjustments;
                        $branchBalance->save();

                        // 4. --- PERUBAHAN UTAMA ---
                        BranchWarehouseToOutletTransaction::create([
                            'branch_warehouse_id' => $warehouseId,
                            'outlet_warehouse_id' => $request->outlet_id,
                            'branch_id' => $warehouse->branch_id, // Anda punya kolom ini
                            'item_id' => $itemId,
                            'user_id' => $currentUser->id,
                            'transaction_type' => 'DISTRIBUTION', // Sesuai ENUM Anda
                            'quantity' => $quantity,
                            'reference_no' => $referenceNo,
                            'notes' => ($itemNotes ? " | {$itemNotes}" : '') 
                                . ($request->notes ? " | {$request->notes}" : ''),
                            'transaction_date' => now(),
                            'status' => 'PENDING' // Kolom baru dari migrasi
                        ]);

                        $successCount++;

                    } catch (\Exception $e) {
                        throw $e;
                    }
                }

                if ($successCount === 0) {
                    throw new \Exception("Tidak ada item yang berhasil didistribusikan. Errors: " . json_encode($errors));
                }

                // ... (return data) ...
                return [
                    'reference_no' => $referenceNo,
                    'outlet_name' => $outlet->warehouse_name,
                    'success_count' => $successCount,
                    'errors' => $errors
                ];
            },
            function($result) {
                // Ubah pesan sukses
                $message = $result['success_count'] . ' items distribution request created for ' . $result['outlet_name'] . '. Waiting for approval. Ref: ' . $result['reference_no'];
                // ... (warning handling) ...
                return $message;
            },
            'Failed to create distribution request'
        );
    }

    /**
     * Show distribution history
     */
    public function distributionHistory(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                ->where('id', $warehouseId)
                ->firstOrFail();

            // Validate access
            $this->validateWarehouseAccess($warehouseId);

            $distributionsQuery = BranchStockTransaction::where('warehouse_id', $warehouseId)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->with(['item', 'user']);

            // Apply date range filter
            $distributionsQuery = $this->applyDateRangeFilter($distributionsQuery, $request, 'transaction_date', 30);

            // Apply search filter
            $distributionsQuery = $this->applySearchFilter($distributionsQuery, $request, [
                'reference_no',
                'notes'
            ]);

            $perPage = $this->getPerPage($request, 20);
            $distributions = $distributionsQuery->paginate($perPage);

            $commonData = $this->getCommonViewData($request);

            return view('branch-warehouse.distribution-history', array_merge($commonData, [
                'warehouse' => $warehouse,
                'distributions' => $distributions
            ]));

        } catch (\Exception $e) {
            Log::error('Distribution history error: ' . $e->getMessage());
            return $this->errorResponse('Error loading distribution history: ' . $e->getMessage());
        }
    }

    // ========================================
    // 📊 REPORTS & SUMMARIES
    // ========================================

    /**
     * Get transaction summary for date range
     */
    public function getTransactionSummary(Request $request, $warehouseId)
    {
        try {
            // Validate access
            $this->validateWarehouseAccess($warehouseId);

            $dateRange = $this->getDateRange($request, 30);

            $transactions = BranchStockTransaction::where('warehouse_id', $warehouseId)
                ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
                ->select('transaction_type', DB::raw('SUM(quantity) as total_quantity'))
                ->groupBy('transaction_type')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'period' => "{$dateRange['start']->format('Y-m-d')} to {$dateRange['end']->format('Y-m-d')}"
            ]);

        } catch (\Exception $e) {
            Log::error('Get transaction summary error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // 🔧 HELPER METHODS
    // ========================================

    /**
     * Generate reference number
     */
    private function generateReferenceNo($type)
    {
        $date = date('ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "{$type}-{$date}-{$random}";
    }

        public function pendingDistributions(Request $request, $warehouseId)
        {
            try {
                // Validate warehouse
                $warehouse = Warehouse::where('id', $warehouseId)
                    ->where('warehouse_type', 'branch')
                    ->where('status', 'ACTIVE')
                    ->firstOrFail();

                // Validate access
                $this->validateWarehouseAccess($warehouseId);

                // Get pending distributions
                $distributions = CentralToBranchWarehouseTransaction::where('warehouse_id', $warehouseId)
                    ->where('status', 'PENDING')
                    ->with([
                        'item.category',
                        'centralWarehouse',
                        'user'
                    ])
                    ->orderBy('transaction_date', 'desc')
                    ->get()
                    ->groupBy('reference_no'); // Group by reference number

                // Calculate summary
                $summary = [
                    'total_references' => $distributions->count(),
                    'total_items' => $distributions->flatten()->count(),
                    'total_quantity' => $distributions->flatten()->sum('quantity'),
                ];

                $commonData = $this->getCommonViewData($request);

                return view('branch-warehouse.pending-distributions', array_merge($commonData, [
                    'warehouse' => $warehouse,
                    'distributions' => $distributions,
                    'summary' => $summary
                ]));

            } catch (\Exception $e) {
                Log::error('Pending distributions error: ' . $e->getMessage());
                return $this->errorResponse('Failed to load pending distributions: ' . $e->getMessage());
            }
        }

    public function approveDistribution(Request $request)
    {
        Log::info('=== APPROVE DISTRIBUTION START ===', [
            'request_data' => $request->all(),
            'user_id' => $this->currentUser()->id,
        ]);

        try {
            // ✅ Validate request
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string',
                'type' => 'required|in:all,selected',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|exists:central_to_branch_warehouse_transactions,id',
                'items.*.selected' => 'nullable',
                'items.*.approved_quantity' => 'required|numeric|min:0.001',
                'items.*.notes' => 'nullable|string|max:255',
                'general_notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $referenceNo = $request->reference_no;
            $type = $request->type;
            $successCount = 0;
            $errors = [];
            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');
            $currentUser = $this->currentUser();
            $warehouseId = null;

            // Process each item
            foreach ($request->items as $itemData) {
                try {
                    // Skip if type is 'selected' and item is not selected
                    if ($type === 'selected' && !isset($itemData['selected'])) {
                        continue;
                    }

                    $distributionId = $itemData['id'];
                    $approvedQty = (float)$itemData['approved_quantity'];
                    $itemNotes = $itemData['notes'] ?? '';

                    // Get distribution
                    $distribution = CentralToBranchWarehouseTransaction::with([
                        'item',
                        'centralWarehouse',
                        'branchWarehouse'
                    ])->findOrFail($distributionId);

                    // Store warehouse ID for redirect
                    if (!$warehouseId) {
                        $warehouseId = $distribution->warehouse_id;
                    }

                    // Validate status
                    if ($distribution->status !== 'PENDING') {
                        $errors[] = "Item {$distribution->item->item_name}: Already processed (Status: {$distribution->status})";
                        continue;
                    }

                    // Validate quantity
                    if ($approvedQty > $distribution->quantity) {
                        $errors[] = "Item {$distribution->item->item_name}: Approved quantity ({$approvedQty}) exceeds requested quantity ({$distribution->quantity})";
                        continue;
                    }

                    // Skip if approved quantity is 0
                    if ($approvedQty <= 0) {
                        $errors[] = "Item {$distribution->item->item_name}: Approved quantity must be greater than 0";
                        continue;
                    }

                    // Validate warehouse access
                    $this->validateWarehouseAccess($distribution->warehouse_id, true);

                    // ✅ Get warehouse using Warehouse model
                    $branchWarehouse = Warehouse::where('id', $distribution->warehouse_id)
                        ->where('warehouse_type', 'branch')
                        ->firstOrFail();

                    // 1. Update distribution status to APPROVED
                    $distribution->update([
                        'status' => 'APPROVED',
                        'approved_by' => $currentUser->id,
                        'approved_at'=> now(),
                    ]);

                    // 2. Create Branch Stock Transaction (IN)
                    $branchTransaction = BranchStockTransaction::create([
                        'branch_id' => $branchWarehouse->branch_id,
                        'warehouse_id' => $distribution->warehouse_id,
                        'item_id' => $distribution->item_id,
                        'transaction_type' => 'IN',
                        'quantity' => $approvedQty,
                        'reference_no' => $distribution->reference_no,
                        'notes' => "Approved - Received from {$distribution->centralWarehouse->warehouse_name}" . 
                                   ($itemNotes ? " | {$itemNotes}" : '') .
                                   ($request->general_notes ? " | {$request->general_notes}" : ''),
                        'transaction_date' => now(),
                        'user_id' => $currentUser->id,
                    ]);

                    // 3. Update Branch Warehouse Monthly Balance
                    // ✅ FIX: Use correct column names based on BranchWarehouseMonthlyBalance model
                    $branchBalance = BranchWarehouseMonthlyBalance::firstOrCreate(
                        [
                            'warehouse_id' => $distribution->warehouse_id,
                            'item_id' => $distribution->item_id,
                            'month' => $currentMonth,
                            'year' => $currentYear
                        ],
                        [
                            'opening_stock' => 0,
                            'stock_in' => 0, // ✅ Use stock_in
                            'stock_out' => 0,
                            'adjustments' => 0,
                            'closing_stock' => 0,
                            'is_closed' => false
                        ]
                    );

                    // ✅ FIX: Update balance using correct column name
                    $branchBalance->stock_in = (float)$branchBalance->stock_in + (float)$approvedQty;
                    $branchBalance->closing_stock = (float)$branchBalance->opening_stock 
                        + (float)$branchBalance->stock_in 
                        - (float)$branchBalance->stock_out 
                        + (float)$branchBalance->adjustments;
                
                    $branchBalance->save();

                    // ✅ Handle partial approval - return remaining to central if needed
                    if ($approvedQty < $distribution->quantity) {
                        $remainingQty = $distribution->quantity - $approvedQty;
                        
                        // Return stock to central warehouse
                        $centralBalance = CentralStockBalance::where('warehouse_id', $distribution->central_warehouse_id)
                            ->where('item_id', $distribution->item_id)
                            ->where('month', $currentMonth)
                            ->where('year', $currentYear)
                            ->first();

                        if ($centralBalance) {
                            // Create return transaction
                            $returnTransaction = CentralStockTransaction::create([
                                'item_id' => $distribution->item_id,
                                'warehouse_id' => $distribution->central_warehouse_id,
                                'user_id' => $currentUser->id,
                                'transaction_type' => 'BRANCH_RETURN',
                                'quantity' => $remainingQty,
                                'unit_cost' => $distribution->item->unit_cost ?? 0,
                                'total_cost' => $remainingQty * ($distribution->item->unit_cost ?? 0),
                                'reference_no' => $distribution->reference_no . '-RET',
                                'notes' => "Partial approval - returned from {$branchWarehouse->warehouse_name}. Approved: {$approvedQty}, Returned: {$remainingQty}",
                                'transaction_date' => now()
                            ]);

                            // Update central balance (use correct column names based on CentralStockBalance model)
                            $centralBalance->stock_in = (float)($centralBalance->stock_in ?? 0) + (float)$remainingQty;
                            $centralBalance->closing_stock = (float)$centralBalance->opening_stock 
                                + (float)($centralBalance->stock_in ?? 0)
                                - (float)($centralBalance->stock_out ?? 0)
                                + (float)($centralBalance->adjustments ?? 0);
                            $centralBalance->save();

                            Log::info('Partial approval - stock returned to central', [
                                'distribution_id' => $distribution->id,
                                'approved' => $approvedQty,
                                'returned' => $remainingQty,
                                'return_transaction_id' => $returnTransaction->id
                            ]);
                        }
                    }

                    $successCount++;

                    Log::info('Distribution item approved', [
                        'distribution_id' => $distribution->id,
                        'item' => $distribution->item->item_name,
                        'approved_qty' => $approvedQty
                    ]);

                } catch (\Exception $e) {
                    Log::error('Error approving distribution item: ' . $e->getMessage(), [
                        'distribution_id' => $distributionId ?? null,
                        'trace' => $e->getTraceAsString()
                    ]);
                    $errors[] = "Distribution ID {$distributionId}: " . $e->getMessage();
                }
            }

            if ($successCount === 0) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->with('error', 'No items were approved successfully. Errors: ' . implode('; ', $errors))
                    ->withInput();
            }

            DB::commit();

            $message = "✅ {$successCount} item(s) approved successfully!";
            if (!empty($errors)) {
                $message .= "\n\n⚠️ Warnings:\n" . implode("\n", $errors);
            }

            Log::info('=== APPROVE DISTRIBUTION SUCCESS ===', [
                'reference_no' => $referenceNo,
                'success_count' => $successCount
            ]);

            return redirect()
                ->route('branch-warehouse.pending-distributions', $warehouseId)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve distribution error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to approve distribution: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Reject pending distribution
     */
    public function rejectDistribution(Request $request)
    {
        Log::info('=== REJECT DISTRIBUTION START ===', [
            'request_data' => $request->all(),
            'user_id' => $this->currentUser()->id,
        ]);

        try {
            // ✅ Validate request
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string',
                'rejection_reason' => 'required|string|max:255',
                'rejection_notes' => 'nullable|string|max:500'
            ], [
                'reference_no.required' => 'Reference number is required',
                'rejection_reason.required' => 'Rejection reason is required',
                'rejection_reason.max' => 'Rejection reason cannot exceed 255 characters',
                'rejection_notes.max' => 'Rejection notes cannot exceed 500 characters'
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $referenceNo = $request->reference_no;
            $rejectionReason = $request->rejection_reason;
            $rejectionNotes = $request->rejection_notes;
            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');
            $currentUser = $this->currentUser();
            $successCount = 0;
            $errors = [];
            $warehouseId = null;

            // Get all distributions with this reference number
            $distributions = CentralToBranchWarehouseTransaction::where('reference_no', $referenceNo)
                ->where('status', 'PENDING')
                ->with([
                    'item',
                    'centralWarehouse',
                    'branchWarehouse'
                ])
                ->get();

            if ($distributions->isEmpty()) {
                throw new \Exception("No pending distributions found with reference: {$referenceNo}");
            }

            // Process each distribution
            foreach ($distributions as $distribution) {
                try {
                    // Store warehouse ID for redirect
                    if (!$warehouseId) {
                        $warehouseId = $distribution->warehouse_id;
                    }

                    // Validate warehouse access
                    $this->validateWarehouseAccess($distribution->warehouse_id, true);

                    // Get warehouse
                    $branchWarehouse = Warehouse::where('id', $distribution->warehouse_id)
                        ->where('warehouse_type', 'branch')
                        ->firstOrFail();

                    // 1. Update distribution status to REJECTED
                    $distribution->update([
                        'status' => 'REJECTED',
                        'notes' => ($distribution->notes ? $distribution->notes . ' | ' : '') 
                            . "REJECTED: {$rejectionReason}" 
                            . ($rejectionNotes ? " - {$rejectionNotes}" : '')
                    ]);

                    // 2. Return stock to central warehouse
                    $centralBalance = CentralStockBalance::where('warehouse_id', $distribution->central_warehouse_id)
                        ->where('item_id', $distribution->item_id)
                        ->where('month', $currentMonth)
                        ->where('year', $currentYear)
                        ->first();

                    if ($centralBalance) {
                        // Create return transaction to central
                        $returnTransaction = CentralStockTransaction::create([
                            'item_id' => $distribution->item_id,
                            'warehouse_id' => $distribution->central_warehouse_id,
                            'user_id' => $currentUser->id,
                            'transaction_type' => 'BRANCH_RETURN',
                            'quantity' => $distribution->quantity,
                            'unit_cost' => $distribution->item->unit_cost ?? 0,
                            'total_cost' => $distribution->quantity * ($distribution->item->unit_cost ?? 0),
                            'reference_no' => $distribution->reference_no . '-REJ',
                            'notes' => "Distribution rejected by {$branchWarehouse->warehouse_name}. Reason: {$rejectionReason}" 
                                . ($rejectionNotes ? " | {$rejectionNotes}" : ''),
                            'transaction_date' => now()
                        ]);

                        // Update central balance - return stock
                        $centralBalance->stock_in = (float)($centralBalance->stock_in ?? 0) + (float)$distribution->quantity;
                        $centralBalance->closing_stock = (float)$centralBalance->opening_stock 
                            + (float)($centralBalance->stock_in ?? 0)
                            - (float)($centralBalance->stock_out ?? 0)
                            + (float)($centralBalance->adjustments ?? 0);
                        $centralBalance->save();

                        Log::info('Distribution rejected - stock returned to central', [
                            'distribution_id' => $distribution->id,
                            'item' => $distribution->item->item_name,
                            'quantity' => $distribution->quantity,
                            'return_transaction_id' => $returnTransaction->id
                        ]);
                    } else {
                        // Create central balance if not exists
                        $centralBalance = CentralStockBalance::create([
                            'warehouse_id' => $distribution->central_warehouse_id,
                            'item_id' => $distribution->item_id,
                            'month' => $currentMonth,
                            'year' => $currentYear,
                            'opening_stock' => 0,
                            'stock_in' => $distribution->quantity,
                            'stock_out' => 0,
                            'adjustments' => 0,
                            'closing_stock' => $distribution->quantity,
                            'is_closed' => false
                        ]);

                        // Create return transaction
                        CentralStockTransaction::create([
                            'item_id' => $distribution->item_id,
                            'warehouse_id' => $distribution->central_warehouse_id,
                            'user_id' => $currentUser->id,
                            'transaction_type' => 'BRANCH_RETURN',
                            'quantity' => $distribution->quantity,
                            'unit_cost' => $distribution->item->unit_cost ?? 0,
                            'total_cost' => $distribution->quantity * ($distribution->item->unit_cost ?? 0),
                            'reference_no' => $distribution->reference_no . '-REJ',
                            'notes' => "Distribution rejected by {$branchWarehouse->warehouse_name}. Reason: {$rejectionReason}" 
                                . ($rejectionNotes ? " | {$rejectionNotes}" : ''),
                            'transaction_date' => now()
                        ]);

                        Log::info('Distribution rejected - central balance created', [
                            'distribution_id' => $distribution->id,
                            'item' => $distribution->item->item_name,
                            'quantity' => $distribution->quantity
                        ]);
                    }

                    $successCount++;

                    Log::info('Distribution item rejected', [
                        'distribution_id' => $distribution->id,
                        'item' => $distribution->item->item_name,
                        'quantity' => $distribution->quantity,
                        'reason' => $rejectionReason
                    ]);

                } catch (\Exception $e) {
                    Log::error('Error rejecting distribution item: ' . $e->getMessage(), [
                        'distribution_id' => $distribution->id ?? null,
                        'trace' => $e->getTraceAsString()
                    ]);
                    $errors[] = "Distribution ID {$distribution->id}: " . $e->getMessage();
                }
            }

            if ($successCount === 0) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->with('error', 'No distributions were rejected. Errors: ' . implode('; ', $errors))
                    ->withInput();
            }

            DB::commit();

            $message = "❌ Distribution rejected successfully! {$successCount} item(s) returned to central warehouse.";
            if (!empty($errors)) {
                $message .= "\n\n⚠️ Warnings:\n" . implode("\n", $errors);
            }

            Log::info('=== REJECT DISTRIBUTION SUCCESS ===', [
                'reference_no' => $referenceNo,
                'success_count' => $successCount,
                'rejection_reason' => $rejectionReason
            ]);

            return redirect()
                ->route('branch-warehouse.pending-distributions', $warehouseId)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reject distribution error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to reject distribution: ' . $e->getMessage())
                ->withInput();
        }
    }
}
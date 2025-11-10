<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Branch;
use App\Models\MonthlyKitchenStockBalance;
use App\Models\KitchenStockTransaction;
use App\Models\BranchWarehouseMonthlyBalance;
use App\Models\OutletWarehouseMonthlyBalance;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class KitchenStockController extends Controller
{
    /**
     * Constructor - Apply middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('set.branch.context');
        
        // All operations require authentication
        // Kitchen stock is typically managed by outlet staff
    }

    // ============================================================
    // 📊 DASHBOARD
    // ============================================================

    /**
     * Display kitchen stock dashboard
     */
    public function index(Request $request)
    {
        try {
            // Get branch context
            $branchId = $this->getBranchId($request);
            $currentBranch = $this->getCurrentBranch($request);
            
            if (!$branchId) {
                return $this->errorResponse('Branch tidak ditemukan. Silakan pilih branch terlebih dahulu.');
            }

            // Validate branch access
            $this->validateBranchAccess($branchId);

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            // Build query
            $query = MonthlyKitchenStockBalance::where('branch_id', $branchId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->with(['item.category', 'branch']);

            // Apply category filter
            if ($request->filled('category_id')) {
                $query->whereHas('item', function($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            // Apply stock status filter
            if ($request->filled('stock_status')) {
                switch ($request->stock_status) {
                    case 'available':
                        $query->where('closing_stock', '>', 0);
                        break;
                    case 'low':
                        $query->whereHas('item', function($q) {
                            $q->whereColumn('monthly_kitchen_stock_balances.closing_stock', '<=', 'items.low_stock_threshold')
                              ->whereColumn('monthly_kitchen_stock_balances.closing_stock', '>', 0);
                        });
                        break;
                    case 'out':
                        $query->where('closing_stock', '<=', 0);
                        break;
                }
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('item', function($q) use ($search) {
                    $q->where('item_name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            $perPage = $this->getPerPage($request, 15);
            $stockBalances = $query->paginate($perPage);

            // Get master data
            $categories = Category::all();

            // Calculate statistics
            $stats = [
                'total_items' => MonthlyKitchenStockBalance::where('branch_id', $branchId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->count(),
                    
                'available_items' => MonthlyKitchenStockBalance::where('branch_id', $branchId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->where('closing_stock', '>', 0)
                    ->count(),
                    
                'low_stock_items' => MonthlyKitchenStockBalance::where('branch_id', $branchId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->whereHas('item', function($q) {
                        $q->whereColumn('monthly_kitchen_stock_balances.closing_stock', '<=', 'items.low_stock_threshold')
                          ->whereColumn('monthly_kitchen_stock_balances.closing_stock', '>', 0);
                    })
                    ->count(),
                    
                'out_of_stock_items' => MonthlyKitchenStockBalance::where('branch_id', $branchId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->where('closing_stock', '<=', 0)
                    ->count(),
                    
                'total_stock_value' => MonthlyKitchenStockBalance::where('branch_id', $branchId)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->with('item')
                    ->get()
                    ->sum(fn($b) => $b->closing_stock * ($b->item->unit_cost ?? 0)),
            ];

            // Get recent transactions
            $recentTransactionsQuery = KitchenStockTransaction::where('branch_id', $branchId)
                ->with(['item', 'user'])
                ->orderBy('transaction_date', 'desc');

            $recentTransactionsQuery = $this->applyDateRangeFilter($recentTransactionsQuery, $request, 'transaction_date', 7);
            $recentTransactions = $recentTransactionsQuery->limit(10)->get();

            // Get common view data
            $commonData = $this->getCommonViewData($request);

            return view('kitchen.index', array_merge($commonData, [
                'stockBalances' => $stockBalances,
                'categories' => $categories,
                'stats' => $stats,
                'recentTransactions' => $recentTransactions
            ]));

        } catch (\Exception $e) {
            Log::error('Kitchen stock index error: ' . $e->getMessage());
            return $this->errorResponse('Error loading kitchen stock: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 📥 RECEIVE FROM WAREHOUSE
    // ============================================================

    /**
     * Show receive from warehouse form
     */
    public function receiveCreate(Request $request)
    {
        try {
            $branchId = $this->getBranchId($request);
            $currentBranch = $this->getCurrentBranch($request);
            
            if (!$branchId) {
                return $this->errorResponse('Branch tidak ditemukan');
            }

            // Validate branch access
            $this->validateBranchAccess($branchId);

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            // Get available stock from branch warehouse
            $branchWarehouseStock = BranchWarehouseMonthlyBalance::where('branch_id', $branchId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('closing_stock', '>', 0)
                ->with('item.category')
                ->get();

            // Get outlet warehouses for this branch
            $outletWarehouses = Warehouse::where('warehouse_type', 'outlet')
                ->where('branch_id', $branchId)
                ->where('status', 'ACTIVE')
                ->get();

            // Get available stock from outlet warehouses
            $outletWarehouseStock = OutletWarehouseMonthlyBalance::whereIn('warehouse_id', $outletWarehouses->pluck('id'))
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('closing_stock', '>', 0)
                ->with(['item.category', 'warehouse'])
                ->get();

            $categories = Category::all();

            $commonData = $this->getCommonViewData($request);

            return view('kitchen.receive', array_merge($commonData, [
                'branchWarehouseStock' => $branchWarehouseStock,
                'outletWarehouseStock' => $outletWarehouseStock,
                'outletWarehouses' => $outletWarehouses,
                'categories' => $categories
            ]));

        } catch (\Exception $e) {
            Log::error('Kitchen receive create error: ' . $e->getMessage());
            return $this->errorResponse('Error loading form: ' . $e->getMessage());
        }
    }

    /**
     * Process receive from warehouse
     */
    public function receiveStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_type' => 'required|in:branch_warehouse,outlet_warehouse',
            'source_warehouse_id' => 'required_if:source_type,outlet_warehouse|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.notes' => 'nullable|string|max:255',
            'transaction_date' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->executeTransaction(
            function () use ($request) {
                $branchId = $this->currentUser()->branch_id;
                $sourceType = $request->source_type;
                $transactionDate = Carbon::parse($request->transaction_date);
                $year = $transactionDate->year;
                $month = $transactionDate->month;

                $createdTransactions = [];
                $errors = [];

                foreach ($request->items as $index => $itemData) {
                    try {
                        // Validate source stock
                        if ($sourceType === 'outlet_warehouse') {
                            $sourceStock = OutletWarehouseMonthlyBalance::where('warehouse_id', $request->source_warehouse_id)
                                ->where('item_id', $itemData['item_id'])
                                ->where('month', $month)
                                ->where('year', $year)
                                ->first();
                        } else {
                            $sourceStock = BranchWarehouseMonthlyBalance::where('branch_id', $branchId)
                                ->where('item_id', $itemData['item_id'])
                                ->where('month', $month)
                                ->where('year', $year)
                                ->first();
                        }

                        if (!$sourceStock || $sourceStock->closing_stock < $itemData['quantity']) {
                            $item = Item::find($itemData['item_id']);
                            $available = $sourceStock ? $sourceStock->closing_stock : 0;
                            $errors[] = "Row " . ($index + 1) . ": Insufficient stock for {$item->item_name} (Available: {$available})";
                            continue;
                        }

                        // Create kitchen transaction
                        if ($sourceType === 'outlet_warehouse') {
                            $transaction = KitchenStockTransaction::createReceiveFromOutletWarehouse([
                                'branch_id' => $branchId,
                                'item_id' => $itemData['item_id'],
                                'user_id' => $this->currentUser()->id,
                                'quantity' => $itemData['quantity'],
                                'notes' => $itemData['notes'] ?? 'Receive from outlet warehouse',
                                'transaction_date' => $transactionDate,
                                'year' => $year,
                                'month' => $month,
                            ]);
                        } else {
                            $transaction = KitchenStockTransaction::createReceiveFromWarehouse([
                                'branch_id' => $branchId,
                                'item_id' => $itemData['item_id'],
                                'user_id' => $this->currentUser()->id,
                                'quantity' => $itemData['quantity'],
                                'notes' => $itemData['notes'] ?? 'Receive from branch warehouse',
                                'transaction_date' => $transactionDate,
                                'year' => $year,
                                'month' => $month,
                            ]);
                        }

                        if (!$transaction) {
                            $errors[] = "Row " . ($index + 1) . ": Failed to create transaction";
                            continue;
                        }

                        // Update source stock
                        $sourceStock->updateMovement('distributed_to_kitchen', $itemData['quantity']);

                        $createdTransactions[] = $transaction;

                    } catch (\Exception $e) {
                        $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                    }
                }

                if (empty($createdTransactions)) {
                    throw new \Exception('No items were received. Errors: ' . implode('; ', $errors));
                }

                // Log activity
                // $this->logActivity('kitchen_receive', 'KitchenStockTransaction', null, [
                //     'branch_id' => $branchId,
                //     'source_type' => $sourceType,
                //     'count' => count($createdTransactions),
                //     'errors' => $errors
                // ]);

                return [
                    'count' => count($createdTransactions),
                    'errors' => $errors
                ];
            },
            count($createdTransactions) . ' items successfully received to kitchen!' . (!empty($errors) ? ' | Errors: ' . implode('; ', $errors) : ''),
            'Failed to receive items'
        );
    }

    // ============================================================
    // 📤 USAGE
    // ============================================================

    /**
     * Show usage form
     */
    public function usageCreate(Request $request)
    {
        try {
            $branchId = $this->getBranchId($request);
            
            if (!$branchId) {
                return $this->errorResponse('Branch tidak ditemukan');
            }

            // Validate branch access
            $this->validateBranchAccess($branchId);

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            $kitchenStock = MonthlyKitchenStockBalance::where('branch_id', $branchId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('closing_stock', '>', 0)
                ->with('item.category')
                ->get();

            $categories = Category::all();

            $usageTypes = [
                'USAGE_PRODUCTION' => 'Production',
                'USAGE_COOKING' => 'Cooking',
                'USAGE_PREPARATION' => 'Preparation',
            ];

            $commonData = $this->getCommonViewData($request);

            return view('kitchen.usage', array_merge($commonData, [
                'kitchenStock' => $kitchenStock,
                'categories' => $categories,
                'usageTypes' => $usageTypes
            ]));

        } catch (\Exception $e) {
            Log::error('Kitchen usage create error: ' . $e->getMessage());
            return $this->errorResponse('Error loading form: ' . $e->getMessage());
        }
    }

    /**
     * Process usage
     */
    public function usageStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.usage_type' => 'required|in:USAGE_PRODUCTION,USAGE_COOKING,USAGE_PREPARATION',
            'items.*.notes' => 'required|string|max:255',
            'transaction_date' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->executeTransaction(
            function () use ($request) {
                $branchId = $this->currentUser()->branch_id;
                $transactionDate = Carbon::parse($request->transaction_date);
                $year = $transactionDate->year;
                $month = $transactionDate->month;

                $createdUsages = [];
                $errors = [];

                foreach ($request->items as $index => $usageData) {
                    try {
                        $balance = MonthlyKitchenStockBalance::getOrCreateBalance(
                            $usageData['item_id'],
                            $branchId,
                            $year,
                            $month
                        );
                        
                        // Validate stock
                        if ($usageData['quantity'] > $balance->closing_stock) {
                            $item = Item::find($usageData['item_id']);
                            $errors[] = "Row " . ($index + 1) . ": Insufficient stock for {$item->item_name}";
                            continue;
                        }

                        // Create usage transaction
                        $transaction = KitchenStockTransaction::create([
                            'branch_id' => $branchId,
                            'item_id' => $usageData['item_id'],
                            'user_id' => $this->currentUser()->id,
                            'transaction_type' => $usageData['usage_type'],
                            'quantity' => $usageData['quantity'],
                            'notes' => $usageData['notes'],
                            'transaction_date' => $transactionDate,
                            'year' => $year,
                            'month' => $month,
                        ]);

                        // Update kitchen balance
                        $balance->updateMovement('usage', $usageData['quantity']);

                        $createdUsages[] = $transaction;

                    } catch (\Exception $e) {
                        $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                    }
                }

                if (empty($createdUsages)) {
                    throw new \Exception('No usages were recorded. Errors: ' . implode('; ', $errors));
                }

                // Log activity
                // $this->logActivity('kitchen_usage', 'KitchenStockTransaction', null, [
                //     'branch_id' => $branchId,
                //     'count' => count($createdUsages),
                //     'errors' => $errors
                // ]);

                return [
                    'count' => count($createdUsages),
                    'errors' => $errors
                ];
            },
            count($createdUsages) . ' kitchen stock usages recorded!' . (!empty($errors) ? ' | Errors: ' . implode('; ', $errors) : ''),
            'Failed to record usage'
        );
    }

    // ============================================================
    // 🔧 ADJUSTMENT
    // ============================================================

    /**
     * Show adjustment form
     */
    public function adjustmentCreate(Request $request)
    {
        try {
            $branchId = $this->getBranchId($request);
            
            if (!$branchId) {
                return $this->errorResponse('Branch tidak ditemukan');
            }

            // Validate branch access
            $this->validateBranchAccess($branchId);

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            $kitchenStock = MonthlyKitchenStockBalance::where('branch_id', $branchId)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->with('item.category')
                ->get();

            $categories = Category::all();

            $commonData = $this->getCommonViewData($request);

            return view('kitchen.adjustment', array_merge($commonData, [
                'kitchenStock' => $kitchenStock,
                'categories' => $categories
            ]));

        } catch (\Exception $e) {
            Log::error('Kitchen adjustment create error: ' . $e->getMessage());
            return $this->errorResponse('Error loading form: ' . $e->getMessage());
        }
    }

    /**
     * Process adjustment
     */
    public function adjustmentStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'adjustment_type' => 'required|in:IN,OUT',
            'quantity' => 'required|numeric|min:0.001',
            'reason' => 'required|string|min:10|max:500',
            'transaction_date' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->executeTransaction(
            function () use ($request) {
                $branchId = $this->currentUser()->branch_id;
                $transactionDate = Carbon::parse($request->transaction_date);
                $year = $transactionDate->year;
                $month = $transactionDate->month;

                $balance = MonthlyKitchenStockBalance::getOrCreateBalance(
                    $request->item_id,
                    $branchId,
                    $year,
                    $month
                );

                // Validate for OUT adjustment
                if ($request->adjustment_type === 'OUT' && $request->quantity > $balance->closing_stock) {
                    throw new \Exception('Adjustment quantity exceeds available stock (' . $balance->closing_stock . ')');
                }

                $adjustmentQuantity = $request->adjustment_type === 'IN' 
                    ? $request->quantity 
                    : -$request->quantity;

                // Create adjustment transaction
                $transaction = KitchenStockTransaction::createAdjustment(
                    $request->item_id,
                    $adjustmentQuantity,
                    $request->reason,
                    $this->currentUser()->id,
                    $branchId
                );

                // Log activity
                // $this->logActivity('kitchen_adjustment', 'KitchenStockTransaction', $transaction->id, [
                //     'branch_id' => $branchId,
                //     'item_id' => $request->item_id,
                //     'type' => $request->adjustment_type,
                //     'quantity' => $request->quantity
                // ]);

                $item = Item::find($request->item_id);
                $action = $request->adjustment_type === 'IN' ? 'increased' : 'decreased';

                return [
                    'item_name' => $item->item_name,
                    'action' => $action
                ];
            },
            "Stock for {$item_name} successfully {$action}!",
            'Failed to adjust stock'
        );
    }

    // ============================================================
    // 📜 TRANSACTIONS
    // ============================================================

    /**
     * Display transaction history
     */
    public function transactions(Request $request)
    {
        try {
            $branchId = $this->getBranchId($request);
            
            if (!$branchId) {
                return $this->errorResponse('Branch tidak ditemukan');
            }

            // Validate branch access
            $this->validateBranchAccess($branchId);

            $query = KitchenStockTransaction::where('branch_id', $branchId)
                ->with(['item.category', 'user', 'branchWarehouseTransaction', 'outletWarehouseTransaction'])
                ->orderBy('transaction_date', 'desc');

            // Apply date range filter
            $query = $this->applyDateRangeFilter($query, $request, 'transaction_date', 30);

            // Filter by transaction type
            if ($request->filled('transaction_type')) {
                $query->where('transaction_type', $request->transaction_type);
            }

            // Filter by item
            if ($request->filled('item_id')) {
                $query->where('item_id', $request->item_id);
            }

            // Apply search filter
            $query = $this->applySearchFilter($query, $request, [
                'reference_no',
                'notes'
            ]);

            // Additional search for item
            if ($request->filled('search')) {
                $search = $request->search;
                $query->orWhereHas('item', function($itemQ) use ($search) {
                    $itemQ->where('item_name', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%");
                });
            }

            $perPage = $this->getPerPage($request, 25);
            $transactions = $query->paginate($perPage);

            $items = Item::whereHas('kitchenStockTransactions')->get();
            $transactionTypes = KitchenStockTransaction::getTransactionTypes();

            // Calculate summary
            $summaryQuery = clone $query;
            $summary = [
                'total_transactions' => $transactions->total(),
                'total_in' => (clone $summaryQuery)->where('quantity', '>', 0)->sum('quantity'),
                'total_out' => abs((clone $summaryQuery)->where('quantity', '<', 0)->sum('quantity')),
            ];

            $commonData = $this->getCommonViewData($request);

            return view('kitchen.transactions', array_merge($commonData, [
                'transactions' => $transactions,
                'items' => $items,
                'transactionTypes' => $transactionTypes,
                'summary' => $summary
            ]));

        } catch (\Exception $e) {
            Log::error('Kitchen transactions error: ' . $e->getMessage());
            return $this->errorResponse('Error loading transactions: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 📊 REPORTS
    // ============================================================

    /**
     * Display monthly report
     */
    public function report(Request $request)
    {
        try {
            $branchId = $this->getBranchId($request);
            
            if (!$branchId) {
                return $this->errorResponse('Branch tidak ditemukan');
            }

            // Validate branch access
            $this->validateBranchAccess($branchId);
            
            $selectedPeriod = $request->get('period', now()->format('Y-m'));
            [$selectedYear, $selectedMonth] = explode('-', $selectedPeriod);
            $selectedYear = (int) $selectedYear;
            $selectedMonth = (int) $selectedMonth;

            $stockBalances = MonthlyKitchenStockBalance::where('branch_id', $branchId)
                ->where('year', $selectedYear)
                ->where('month', $selectedMonth)
                ->with(['item.category'])
                ->get();

            $availablePeriods = MonthlyKitchenStockBalance::where('branch_id', $branchId)
                ->selectRaw('DISTINCT year, month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                        'label' => Carbon::create($item->year, $item->month, 1)->format('F Y')
                    ];
                });

            $summary = MonthlyKitchenStockBalance::getBranchPeriodSummary(
                $branchId,
                $selectedYear,
                $selectedMonth
            );

            $commonData = $this->getCommonViewData($request);

            return view('kitchen.report', array_merge($commonData, [
                'stockBalances' => $stockBalances,
                'selectedPeriod' => $selectedPeriod,
                'selectedYear' => $selectedYear,
                'selectedMonth' => $selectedMonth,
                'availablePeriods' => $availablePeriods,
                'summary' => $summary
            ]));

        } catch (\Exception $e) {
            Log::error('Kitchen report error: ' . $e->getMessage());
            return $this->errorResponse('Error loading report: ' . $e->getMessage());
        }
    }
}
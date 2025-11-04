<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\BranchStockTransaction;
use App\Models\BranchWarehouseMonthlyBalance;
use App\Models\Item;
use App\Models\Branch;
use App\Models\StockPeriod;
use App\Models\KirchenStcokTransaction;
use App\Models\MonthlyKitchenStockBalance;
use App\models\CentralToBranchWarehouseTransaction;
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
     * ============================================================
     * 1. LIHAT STOCK - View Current Stock
     * ============================================================
     */

    /**
     * Display list of branch warehouses with current stock
     */
    public function index()
    {
        try {
            $warehouses = Warehouse::where('warehouse_type', 'branch')
                                ->where('status', 'ACTIVE')
                                ->with(['branch' => function($query) {
                                    $query->select('id', 'branch_name', 'branch_code', 'city');
                                }])
                                ->orderBy('warehouse_name')
                                ->paginate(15);

            $currentPeriod = StockPeriod::where('year', (int)date('Y'))
                                    ->where('month', (int)date('m'))
                                    ->first();

            $warehouseStats = [];
            
            foreach ($warehouses as $warehouse) {
                // ✅ Query menggunakan columns yang ada
                $balance = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouse->id)
                                                    ->where('month', (int)date('m'))
                                                    ->where('year', (int)date('Y'))
                                                    ->selectRaw('COUNT(DISTINCT item_id) as total_items, 
                                                                SUM(closing_stock) as total_stock')
                                                    ->first();

                // Calculate total_value: closing_stock (dari column stock_out - stock_in)
                // Atau gunakan closing_stock * unit_price dari item
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
                    'total_value' => $totalValue
                ];
            }

            return view('branch-warehouse.index', compact('warehouses', 'warehouseStats', 'currentPeriod'));

        } catch (\Exception $e) {
            Log::error('Branch warehouse list error: ' . $e->getMessage());
            return back()->with('error', 'Error loading branch warehouses: ' . $e->getMessage());
        }
    }

    /**
     * Show warehouse detail with current stock
     */
    public function show($warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                                 ->where('id', $warehouseId)
                                 ->with('branch')
                                 ->firstOrFail();

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            // ✅ FIXED: Use warehouse_id
            $stockBalance = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                                                        ->where('month', $currentMonth)
                                                        ->where('year', $currentYear)
                                                        ->with('item.category')
                                                        ->paginate(20);

            // ✅ FIXED: Use warehouse_id
            $recentTransactions = BranchStockTransaction::where('warehouse_id', $warehouseId)
                                                       ->orderBy('transaction_date', 'desc')
                                                       ->orderBy('created_at', 'desc')
                                                       ->limit(10)
                                                       ->with(['item', 'user'])
                                                       ->get();

            // ✅ FIXED: Calculate stats with warehouse_id
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
                                                             ->sum(DB::raw('closing_stock * 0')), // Set to 0 if no unit_cost
                
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

            return view('branch-warehouse.show', compact(
                'warehouse',
                'stockBalance',
                'recentTransactions',
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Branch warehouse detail error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error loading warehouse detail: ' . $e->getMessage());
        }
    }

    /**
     * Get current stock for warehouse (API endpoint)
     */
    public function getCurrentStock($warehouseId)
    {
        try {
            $currentMonth = date('m');
            $currentYear = date('Y');

            $stockBalance = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                                                        ->where('month', $currentMonth)
                                                        ->where('year', $currentYear)
                                                        ->with('item:id,item_code,item_name,unit_measurement')
                                                        ->get();

            return response()->json([
                'success' => true,
                'data' => $stockBalance,
                'count' => $stockBalance->count(),
                'total_stock' => $stockBalance->sum('closing_stock'),
                'total_value' => $stockBalance->sum('total_value')
            ]);

        } catch (\Exception $e) {
            Log::error('Get current stock error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * 2. MANAGEMENT STOCK
     * ============================================================
     */

    /**
     * Show form to receive stock from central warehouse
     */
    public function showReceiveStockForm($warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                                 ->where('id', $warehouseId)
                                 ->firstOrFail();

            // Get pending central to branch transactions
            $pendingReceive = CentralToBranchWarehouseTransaction::where('warehouse_id', $warehouseId)
                                                                //  ->where('status', 'PENDING')
                                                                 ->with(['item', 'user'])
                                                                 ->orderBy('transaction_date', 'desc')
                                                                 ->paginate(10);

            // Get all items for selection
            $items = Item::where('id', '>', 0)
                        ->orderBy('item_name')
                        ->get(['id', 'sku', 'item_name',]);

            return view('branch-warehouse.receive-stock', compact('warehouse', 'pendingReceive', 'items'));

        } catch (\Exception $e) {
            Log::error('Receive stock form error: ' . $e->getMessage());
            return back()->with('error', 'Error loading receive form: ' . $e->getMessage());
        }
    }

    /**
     * Store received stock from central warehouse
     */
    public function storeReceiveStock(Request $request, $warehouseId)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                                 ->where('id', $warehouseId)
                                 ->firstOrFail();

            $referenceNo = $this->generateReferenceNo('RCV');

            // Create stock transaction
            $branchTransaction = BranchStockTransaction::create([
                'branch_id' => $warehouse->branch_id,
                'warehouse_id' => $warehouseId,        // ✅ FIXED
                'item_id' => $request->item_id,
                'transaction_type' => 'IN',
                'quantity' => $request->quantity,
                'reference_no' => $referenceNo,
                'notes' => 'Received: ' . ($request->notes ?? ''),
                'transaction_date' => now(),
                'user_id' => auth()->id()
            ]);

            // ✅ FIXED: Update or create monthly balance with warehouse_id
            $balance = BranchWarehouseMonthlyBalance::firstOrCreate(
                [
                    'warehouse_id' => $warehouseId,    // ✅ FIXED
                    'item_id' => $request->item_id,
                    'month' => (int)date('m'),
                    'year' => (int)date('Y')
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
            $balance->stock_in = (float)$balance->stock_in + (float)$request->quantity;
            $balance->closing_stock = (float)$balance->opening_stock 
                                    + (float)$balance->stock_in 
                                    - (float)$balance->stock_out 
                                    + (float)$balance->adjustments;
            $balance->save();

            DB::commit();

            Log::info('Stock received at branch warehouse', [
                'warehouse_id' => $warehouseId,
                'item_id' => $request->item_id,
                'quantity' => $request->quantity,
                'reference_no' => $referenceNo
            ]);

            return redirect()->route('branch-warehouse.show', $warehouseId)
                           ->with('success', 'Stock received successfully. Reference: ' . $referenceNo);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Receive stock error: ' . $e->getMessage());
            return back()->with('error', 'Error receiving stock: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show form for stock adjustment (waste, damage, etc)
     */
    public function showAdjustmentForm($warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                                 ->where('id', $warehouseId)
                                 ->firstOrFail();

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
                'ADJUST' => 'Adjustment/Koreksi',
                'WASTE' => 'Rusak/Expired',
                'DAMAGE' => 'Kerusakan',
                'RETURN_SUPPLIER' => 'Return ke Supplier'
            ];

            return view('branch-warehouse.adjust-stock', compact('warehouse', 'stockItems', 'adjustmentTypes'));

        } catch (\Exception $e) {
            Log::error('Adjustment form error: ' . $e->getMessage());
            return back()->with('error', 'Error loading adjustment form: ' . $e->getMessage());
        }
    }

    /**
     * Store stock adjustment
     */
    public function storeAdjustment(Request $request, $warehouseId)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'adjustment_type' => 'required|in:ADJUST,WASTE,DAMAGE,RETURN_SUPPLIER',
            'quantity' => 'required|numeric|min:0.001',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                                 ->where('id', $warehouseId)
                                 ->firstOrFail();

            // Validate stock availability
            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');

            $balance = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                                                   ->where('item_id', $request->item_id)
                                                   ->where('month', $currentMonth)
                                                   ->where('year', $currentYear)
                                                   ->firstOrFail();

            if ($balance->closing_stock < $request->quantity) {
                throw new \Exception("Stock tidak mencukupi. Tersedia: {$balance->closing_stock}, Diminta: {$request->quantity}");
            }

            // Generate reference
            $referenceNo = $this->generateReferenceNo('ADJ');

            // ✅ FIXED: Map adjustment type to transaction type
            $transactionType = match($request->adjustment_type) {
                'ADJUST' => 'ADJUSTMENT_OUT',
                'WASTE' => 'WASTAGE',
                'DAMAGE' => 'WASTAGE',
                'RETURN_SUPPLIER' => 'TRANSFER_TO_CENTRAL',
                default => 'ADJUSTMENT_OUT'
            };

            // ✅ FIXED: Create stock transaction with warehouse_id
            $transaction = BranchStockTransaction::create([
                'branch_id' => $warehouse->branch_id,
                'warehouse_id' => $warehouseId,             // ✅ FIXED: warehouse_id
                'item_id' => $request->item_id,
                'transaction_type' => $transactionType,
                'quantity' => $request->quantity,
                'reference_no' => $referenceNo,
                'notes' => $request->adjustment_type . ': ' . ($request->reason ?? ''),
                'transaction_date' => now(),
                'user_id' => auth()->id()
            ]);

            // ✅ FIXED: Update balance with warehouse_id
            $balance->stock_out = (float)$balance->stock_out + (float)$request->quantity;
            $balance->closing_stock = (float)$balance->opening_stock + (float)$balance->stock_in - (float)$balance->stock_out + (float)$balance->adjustments;
            $balance->total_value = (float)$balance->closing_stock * (float)$balance->avg_unit_cost;
            $balance->save();

            DB::commit();

            Log::info('Stock adjustment created', [
                'warehouse_id' => $warehouseId,
                'item_id' => $request->item_id,
                'type' => $request->adjustment_type,
                'quantity' => $request->quantity,
                'reference_no' => $referenceNo
            ]);

            return redirect()->route('branch-warehouse.show', $warehouseId)
                           ->with('success', 'Stock adjustment recorded. Reference: ' . $referenceNo);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Stock adjustment error: ' . $e->getMessage());
            return back()->with('error', 'Error adjusting stock: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * ============================================================
     * 3. DISTRIBUSI KE OUTLET
     * ============================================================
     */

    /**
     * Show form for distribution to outlets
     */
    public function showDistributionForm($warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                                 ->where('id', $warehouseId)
                                 ->with('branch')
                                 ->firstOrFail();

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

            // Get outlets (branch warehouses with type outlet)
            // Or if you have a separate Outlet model, use that
            $outlets = Warehouse::where('warehouse_type', 'outlet')
                               ->where('status', 'ACTIVE')
                               ->orderBy('warehouse_name')
                               ->get(['id', 'warehouse_name', 'warehouse_code', 'address']);

            // Alternative: Get outlets via kitchen query if they're separate
            // $outlets = Kitchen::where('status', 'ACTIVE')->get();

            return view('branch-warehouse.distribute', compact('warehouse', 'stockItems', 'outlets'));

        } catch (\Exception $e) {
            Log::error('Distribution form error: ' . $e->getMessage());
            return back()->with('error', 'Error loading distribution form: ' . $e->getMessage());
        }
    }

    /**
     * Store distribution to outlet
     */
    public function storeDistribution(Request $request, $warehouseId)
    {
        // ✅ FIX: Custom validation rules - Only validate selected items
        $rules = [
            'outlet_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
        ];

        // ✅ NEW: Only validate selected items
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
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validasi gagal, periksa input Anda');
        }

        // ✅ Filter only selected items
        $selectedItems = collect($request->input('items', []))
            ->filter(function($item) {
                return isset($item['selected']) && $item['selected'] && ($item['quantity'] ?? 0) > 0;
            })
            ->values()
            ->toArray();

        if (empty($selectedItems)) {
            return back()
                ->with('error', 'Tidak ada item yang dipilih atau quantity tidak valid')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Get branch warehouse
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                             ->where('id', $warehouseId)
                             ->firstOrFail();

            // Validate outlet exists and is outlet type
            $outlet = Warehouse::where('warehouse_type', 'outlet')
                          ->where('id', $request->outlet_id)
                          ->firstOrFail();

            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');
            $referenceNo = $this->generateReferenceNo('DIST');
            $successCount = 0;
            $errors = [];

            // Process each selected item
            foreach ($selectedItems as $index => $item) {
                try {
                    $itemId = $item['item_id'];
                    $quantity = $item['quantity'];
                    $itemNotes = $item['item_notes'] ?? '';

                    // 1. Validate stock availability at BRANCH warehouse
                    $branchBalance = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                                                               ->where('item_id', $itemId)
                                                               ->where('month', $currentMonth)
                                                               ->where('year', $currentYear)
                                                               ->first();

                    if (!$branchBalance || $branchBalance->closing_stock < $quantity) {
                        $available = $branchBalance ? $branchBalance->closing_stock : 0;
                        $errors[] = "Item ID {$itemId}: Stock tidak mencukupi (Tersedia: {$available}, Diminta: {$quantity})";
                        continue;
                    }

                    // 2. Create BRANCH stock transaction (OUT)
                    $branchTransaction = BranchStockTransaction::create([
                        'branch_id' => $warehouse->branch_id,
                        'warehouse_id' => $warehouseId,
                        'item_id' => $itemId,
                        'transaction_type' => 'OUT',
                        'quantity' => $quantity,
                        'reference_no' => $referenceNo,
                        'notes' => "Distribusi ke outlet {$outlet->warehouse_name}" . ($itemNotes ? " | {$itemNotes}" : '') . ($request->notes ? " | {$request->notes}" : ''),
                        'transaction_date' => now(),
                        'user_id' => auth()->id()
                    ]);

                    // ✅ 3. Update BRANCH warehouse balance (OUT) - FIXED: Remove non-existent columns
                    $branchBalance->stock_out = (float)$branchBalance->stock_out + (float)$quantity;
                    
                    // ✅ Recalculate closing stock
                    $branchBalance->closing_stock = (float)$branchBalance->opening_stock 
                                              + (float)$branchBalance->stock_in 
                                              - (float)$branchBalance->stock_out 
                                              + (float)$branchBalance->adjustments;
                    
                    
                    $branchBalance->save();

                    // 4. Create OUTLET stock transaction (IN - RECEIVE_FROM_BRANCH)
                    $outletTransaction = OutletStockTransaction::createReceiveFromBranch([
                        'outlet_warehouse_id' => $request->outlet_id,
                        'item_id' => $itemId,
                        'quantity' => $quantity,
                        'user_id' => auth()->id(),
                        'branch_warehouse_transaction_id' => $branchTransaction->id,
                        'unit_cost' => 0, // ✅ FIXED: No avg_unit_cost in branch balance
                        'batch_no' => null,
                        'notes' => "Terima dari branch warehouse {$warehouse->warehouse_name}" . ($itemNotes ? " | {$itemNotes}" : ''),
                        'transaction_date' => now(),
                        'year' => $currentYear,
                        'month' => $currentMonth,
                        'status' => OutletStockTransaction::STATUS_COMPLETED,
                        'document_no' => $referenceNo,
                    ]);

                    if (!$outletTransaction) {
                        throw new \Exception("Gagal create outlet stock transaction untuk item ID {$itemId}");
                    }

                    $successCount++;

                    Log::info('Item distributed from branch to outlet', [
                        'item_id' => $itemId,
                        'quantity' => $quantity,
                        'from_warehouse' => $warehouseId,
                        'to_outlet' => $request->outlet_id,
                        'branch_transaction_id' => $branchTransaction->id,
                        'outlet_transaction_id' => $outletTransaction->id,
                    ]);

                } catch (\Exception $e) {
                    Log::error("Error processing item distribution: " . $e->getMessage(), [
                        'item_id' => $itemId ?? null,
                        'warehouse_id' => $warehouseId,
                        'outlet_id' => $request->outlet_id,
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $errors[] = "Item ID {$itemId}: " . $e->getMessage();
                }
            }

            // Check if any items were processed successfully
            if ($successCount === 0) {
                throw new \Exception("Tidak ada item yang berhasil didistribusikan. Errors: " . implode('; ', $errors));
            }

            DB::commit();

            $message = "✅ {$successCount} item berhasil didistribusikan ke outlet {$outlet->warehouse_name}. Reference: {$referenceNo}";
            
            if (!empty($errors)) {
                $message .= " | ⚠️ Errors: " . implode('; ', $errors);
            }

            Log::info('Stock distributed to outlet successfully', [
                'from_warehouse_id' => $warehouseId,
                'to_outlet_id' => $request->outlet_id,
                'reference_no' => $referenceNo,
                'success_count' => $successCount,
                'total_items' => count($selectedItems),
                'errors' => $errors,
            ]);

            return redirect()->route('branch-warehouse.show', $warehouseId)
                           ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Distribution to outlet error: ' . $e->getMessage(), [
                'warehouse_id' => $warehouseId,
                'outlet_id' => $request->outlet_id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()
                ->with('error', 'Error distributing stock: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show distribution history
     */
    public function distributionHistory($warehouseId)
    {
        try {
            $warehouse = Warehouse::where('warehouse_type', 'branch')
                                 ->where('id', $warehouseId)
                                 ->firstOrFail();

            $distributions = BranchWarehouseToOutletTransaction::where('warehouse_id', $warehouseId)
                                                               ->with(['item', 'user'])
                                                               ->orderBy('transaction_date', 'desc')
                                                               ->paginate(20);

            return view('branch-warehouse.distribution-history', compact('warehouse', 'distributions'));

        } catch (\Exception $e) {
            Log::error('Distribution history error: ' . $e->getMessage());
            return back()->with('error', 'Error loading distribution history: ' . $e->getMessage());
        }
    }

    /**
     * Get transaction summary for date range
     * ✅ FIXED: Use warehouse_id
     */
    public function getTransactionSummary(Request $request, $warehouseId)
    {
        try {
            $startDate = $request->start_date ? date('Y-m-d', strtotime($request->start_date)) : date('Y-m-01');
            $endDate = $request->end_date ? date('Y-m-d', strtotime($request->end_date)) : date('Y-m-t');

            $transactions = BranchStockTransaction::where('warehouse_id', $warehouseId)  // ✅ FIXED
                                                 ->whereBetween('transaction_date', [$startDate, $endDate])
                                                 ->select('transaction_type', DB::raw('SUM(quantity) as total_quantity'))
                                                 ->groupBy('transaction_type')
                                                 ->get();

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'period' => "{$startDate} to {$endDate}"
            ]);

        } catch (\Exception $e) {
            Log::error('Get transaction summary error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * UTILITY METHODS
     * ============================================================
     */

    /**
     * Generate reference number
     */
    private function generateReferenceNo($type)
    {
        $date = date('ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "{$type}-{$date}-{$random}";
    }

    /**
     * Show receive stock form
     */
    // public function showReceiveStockForm($warehouseId)
    // {
    //     $warehouse = Warehouse::findOrFail($warehouseId);
    //     $items = Item::where('is_active', true)->orderBy('item_name')->get();
        
    //     return view('branch-warehouse.receive-stock', compact('warehouse', 'items'));
    // }

    /**
     * Show adjustment form
     */
    // public function showAdjustmentForm($warehouseId)
    // {
    //     $warehouse = Warehouse::findOrFail($warehouseId);
        
    //     // ✅ FIXED: Get items with current stock using warehouse_id
    //     $items = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
    //                                          ->where('month', (int)date('m'))
    //                                          ->where('year', (int)date('Y'))
    //                                          ->with('item')
    //                                          ->get();
        
    //     return view('branch-warehouse.adjust-stock', compact('warehouse', 'items'));
    // }

    /**
     * Show distribution form
     */
    // public function showDistributionForm($warehouseId)
    // {
    //     $warehouse = Warehouse::findOrFail($warehouseId);
        
    //     // ✅ FIXED: Get items with stock using warehouse_id
    //     $items = BranchWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
    //                                          ->where('month', (int)date('m'))
    //                                          ->where('year', (int)date('Y'))
    //                                          ->where('closing_stock', '>', 0)
    //                                          ->with('item')
    //                                          ->get();
        
    //     $outlets = Warehouse::where('warehouse_type', 'outlet')
    //                        ->where('branch_id', $warehouse->branch_id)
    //                        ->where('status', 'ACTIVE')
    //                        ->get();
        
    //     return view('branch-warehouse.distribute', compact('warehouse', 'items', 'outlets'));
    // }
}
<?php
// filepath: app/Http/Controllers/BranchWarehouseController.php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Item;
use App\Models\Branch;
use App\Models\BranchWarehouseMonthlyBalance;     
use App\Models\BranchStockTransaction;           
use App\Models\OutletWarehouseMonthlyBalance;
use App\Models\OutletStockTransaction;
use App\Models\OutletWarehouseToKitchenTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BranchWarehouseController extends Controller
{
    // ============================================================
    // DASHBOARD & STOCK LIST
    // ============================================================

    /**
     * Dashboard branch warehouse
     */
    public function index(Request $request)
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            
            // ✅ FIX: Get branch warehouses (use status instead of is_active)
            $warehouses = Warehouse::branch()
                ->where('status', 'ACTIVE')
                ->with('branch')
                ->get();
            
            // Get selected warehouse
            $selectedWarehouse = $warehouseId 
                ? Warehouse::branch()->findOrFail($warehouseId) 
                : $warehouses->first();

            if (!$selectedWarehouse) {
                return view('branch-warehouse.index', [
                    'warehouses' => $warehouses,
                    'selectedWarehouse' => null,
                ])->with('info', 'Tidak ada branch warehouse yang tersedia');
            }

            $year = date('Y');
            $month = date('m');

            // Get stock summary
            $stockSummary = $selectedWarehouse->getCurrentMonthStockSummary();

            // Get low stock items
            $lowStockItems = $selectedWarehouse->getLowStockItems()->take(10);

            // Get pending distributions
            $pendingDistributions = $selectedWarehouse->getPendingKitchenDistributions()->take(10);

            // Get recent transactions
            $recentTransactions = OutletStockTransaction::where('outlet_warehouse_id', $selectedWarehouse->id)
                ->with(['item', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Get distribution stats
            $distributionStats = [
                'today' => OutletWarehouseToKitchenTransaction::where('outlet_warehouse_id', $selectedWarehouse->id)
                    ->whereDate('transaction_date', today())
                    ->count(),
                'this_week' => OutletWarehouseToKitchenTransaction::where('outlet_warehouse_id', $selectedWarehouse->id)
                    ->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'this_month' => OutletWarehouseToKitchenTransaction::where('outlet_warehouse_id', $selectedWarehouse->id)
                    ->whereYear('transaction_date', $year)
                    ->whereMonth('transaction_date', $month)
                    ->count(),
            ];

            return view('branch-warehouse.index', compact(
                'warehouses',
                'selectedWarehouse',
                'stockSummary',
                'lowStockItems',
                'pendingDistributions',
                'recentTransactions',
                'distributionStats'
            ));

        } catch (\Exception $e) {
            Log::error('Branch warehouse dashboard error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat dashboard');
        }
    }

    // ============================================================
    // STOCK MANAGEMENT
    // ============================================================

    /**
     * Display stock list
     */
    public function show(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('m'));
            $warehouseId = $request->input('warehouse_id');
            $search = $request->input('search');
            $categoryId = $request->input('category_id');
            $stockStatus = $request->input('stock_status');

            // ✅ FIX: Get branch warehouses
            $warehouses = Warehouse::branch()
                ->where('status', 'ACTIVE')
                ->with('branch')
                ->get();

            // Query stock balances
            $query = OutletWarehouseMonthlyBalance::with(['warehouse.branch', 'item.category'])
                ->where('year', $year)
                ->where('month', $month);

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            if ($search) {
                $query->whereHas('item', function($q) use ($search) {
                    $q->where('item_name', 'like', "%$search%")
                      ->orWhere('item_code', 'like', "%$search%");
                });
            }

            if ($categoryId) {
                $query->whereHas('item', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }

            if ($stockStatus) {
                switch ($stockStatus) {
                    case 'out_of_stock':
                        $query->where('closing_stock', '<=', 0);
                        break;
                    case 'low_stock':
                        $query->lowStock();
                        break;
                    case 'overstock':
                        $query->whereHas('item', function($q) {
                            $q->whereRaw('outlet_warehouse_monthly_balances.closing_stock > items.maximum_stock');
                        });
                        break;
                    case 'normal':
                        $query->whereHas('item', function($q) {
                            $q->whereRaw('outlet_warehouse_monthly_balances.closing_stock >= items.low_stock_threshold');
                        });
                        break;
                }
            }

            $balances = $query->orderBy('warehouse_id')
                             ->orderBy('item_id')
                             ->paginate(50);

            // Get summary
            $summaryQuery = OutletWarehouseMonthlyBalance::where('year', $year)
                ->where('month', $month);
            
            if ($warehouseId) {
                $summaryQuery->where('warehouse_id', $warehouseId);
            }

            $summaryBalances = $summaryQuery->with('item')->get();

            $summary = [
                'total_items' => $summaryBalances->count(),
                'total_stock_value' => $summaryBalances->sum('stock_value'),
                'low_stock_items' => $summaryBalances->where('is_low_stock', true)->count(),
                'out_of_stock_items' => $summaryBalances->where('closing_stock', '<=', 0)->count(),
                'total_received' => $summaryBalances->sum('received_from_branch_warehouse'),
                'total_distributed' => $summaryBalances->sum('distributed_to_kitchen'),
            ];

            return view('branch-warehouse.show', compact(
                'balances',
                'warehouses',
                'year',
                'month',
                'warehouseId',
                'search',
                'categoryId',
                'stockStatus',
                'summary'
            ));

        } catch (\Exception $e) {
            Log::error('Branch warehouse stock index error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data stock');
        }
    }

    /**
     * Show stock detail
     */
    public function stockShow($warehouseId, $itemId, Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('m'));

            $warehouse = Warehouse::branch()->findOrFail($warehouseId);
            $item = Item::findOrFail($itemId);

            // Get current month balance
            $balance = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->where('year', $year)
                ->where('month', $month)
                ->with(['warehouse.branch', 'item.category'])
                ->firstOrFail();

            // Get transactions
            $transactions = OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->where('year', $year)
                ->where('month', $month)
                ->with(['user', 'approvedByUser'])
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Get stock history (last 6 months)
            $history = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(6)
                ->get();

            return view('branch-warehouse.stock-show', compact(
                'warehouse',
                'item',
                'balance',
                'transactions',
                'history',
                'year',
                'month'
            ));

        } catch (\Exception $e) {
            Log::error('Branch warehouse stock show error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat detail stock');
        }
    }

    // ============================================================
    // RECEIVE STOCK FROM BRANCH
    // ============================================================

    /**
     * Show receive stock form
     */
    public function receiveCreate($warehouseId)
    {
        try {
            $warehouse = Warehouse::branch()->findOrFail($warehouseId);
            $items = Item::active()->with('category')->orderBy('item_name')->get();

            // Get pending distributions from branch warehouse
            $pendingDistributions = DB::table('branch_warehouse_to_outlet_transactions as bwot')
                ->join('warehouses as w', 'bwot.warehouse_id', '=', 'w.id')
                ->join('items as i', 'bwot.item_id', '=', 'i.id')
                ->where('bwot.outlet_warehouse_id', $warehouseId)
                ->whereIn('bwot.status', ['DELIVERED', 'IN_TRANSIT'])
                ->select(
                    'bwot.*',
                    'w.warehouse_name as branch_warehouse_name',
                    'i.item_name',
                    'i.item_code',
                    'i.unit'
                )
                ->orderBy('bwot.transaction_date', 'desc')
                ->get();

            return view('branch-warehouse.receive', compact(
                'warehouse',
                'items',
                'pendingDistributions'
            ));

        } catch (\Exception $e) {
            Log::error('Create receive form error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat form');
        }
    }

    /**
     * Store receive stock
     */
    public function receiveStore(Request $request, $warehouseId)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.batch_no' => 'nullable|string|max:50',
            'items.*.notes' => 'nullable|string|max:255',
            'transaction_date' => 'required|date|before_or_equal:today',
            'branch_warehouse_transaction_id' => 'nullable|exists:branch_warehouse_to_outlet_transactions,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $warehouse = Warehouse::branch()->findOrFail($warehouseId);
            $successCount = 0;
            $errors = [];

            foreach ($request->items as $itemData) {
                $data = [
                    'outlet_warehouse_id' => $warehouseId,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'] ?? null,
                    'batch_no' => $itemData['batch_no'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                    'transaction_date' => $request->transaction_date,
                    'branch_warehouse_transaction_id' => $request->branch_warehouse_transaction_id,
                    'user_id' => auth()->id(),
                ];

                $transaction = OutletStockTransaction::createReceiveFromBranch($data);

                if ($transaction) {
                    $successCount++;
                } else {
                    $item = Item::find($itemData['item_id']);
                    $errors[] = "Gagal menerima stock: {$item->item_name}";
                }
            }

            if ($successCount > 0) {
                DB::commit();

                $message = "✅ Berhasil menerima $successCount item";
                if (count($errors) > 0) {
                    $message .= ". ⚠️ " . implode(', ', $errors);
                }

                return redirect()
                    ->route('branch-warehouse.stock.index', ['warehouse_id' => $warehouseId])
                    ->with('success', $message);
            } else {
                DB::rollBack();
                return back()->withInput()->with('error', '❌ ' . implode(', ', $errors));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store receive stock error: ' . $e->getMessage());
            return back()->withInput()->with('error', '❌ Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ============================================================
    // STOCK ADJUSTMENT
    // ============================================================

    /**
     * Show adjustment form
     */
    public function adjustmentCreate($warehouseId)
    {
        try {
            $warehouse = Warehouse::branch()->findOrFail($warehouseId);
            $items = Item::active()->with('category')->orderBy('item_name')->get();

            // Get current stock
            $currentStock = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->currentPeriod()
                ->with('item')
                ->get()
                ->keyBy('item_id');

            return view('branch-warehouse.stock-adjustment', compact(
                'warehouse',
                'items',
                'currentStock'
            ));

        } catch (\Exception $e) {
            Log::error('Create adjustment form error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat form');
        }
    }

    /**
     * Store adjustment
     */
    public function adjustmentStore(Request $request, $warehouseId)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'adjustment_type' => 'required|in:addition,reduction',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'required|string|max:500',
            'transaction_date' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $warehouse = Warehouse::branch()->findOrFail($warehouseId);
            $quantity = $request->adjustment_type === 'addition' 
                      ? $request->quantity 
                      : -$request->quantity;

            $data = [
                'outlet_warehouse_id' => $warehouseId,
                'item_id' => $request->item_id,
                'quantity' => $quantity,
                'notes' => $request->notes,
                'transaction_date' => $request->transaction_date,
                'user_id' => auth()->id(),
            ];

            $transaction = OutletStockTransaction::createAdjustment($data);

            if ($transaction) {
                DB::commit();
                return redirect()
                    ->route('branch-warehouse.stock.index', ['warehouse_id' => $warehouseId])
                    ->with('success', '✅ Adjustment stock berhasil disimpan');
            } else {
                DB::rollBack();
                return back()->withInput()->with('error', '❌ Gagal melakukan adjustment');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store adjustment error: ' . $e->getMessage());
            return back()->withInput()->with('error', '❌ Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ============================================================
    // TRANSACTIONS
    // ============================================================

    /**
     * Show transactions list
     */
    public function transactions(Request $request, $warehouseId)
    {
        try {
            $warehouse = Warehouse::branch()->findOrFail($warehouseId);
            
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));
            $type = $request->input('type');
            $status = $request->input('status');
            $itemId = $request->input('item_id');

            $query = OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                ->with(['item', 'user', 'approvedByUser'])
                ->whereBetween('transaction_date', [$startDate, $endDate]);

            if ($type) {
                $query->byType($type);
            }

            if ($status) {
                $query->byStatus($status);
            }

            if ($itemId) {
                $query->where('item_id', $itemId);
            }

            $transactions = $query->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            // Summary
            $summary = [
                'total_transactions' => $query->count(),
                'total_stock_in' => (clone $query)->stockIn()->sum('quantity'),
                'total_stock_out' => (clone $query)->stockOut()->sum('quantity'),
                'total_value' => $query->sum('total_cost'),
            ];

            return view('branch-warehouse.stock-transactions', compact(
                'warehouse',
                'transactions',
                'startDate',
                'endDate',
                'type',
                'status',
                'itemId',
                'summary'
            ));

        } catch (\Exception $e) {
            Log::error('Transactions error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat transaksi');
        }
    }

    /**
     * Show transaction detail
     */
    public function transactionDetail($warehouseId, $transactionId)
    {
        try {
            $warehouse = Warehouse::branch()->findOrFail($warehouseId);
            $transaction = OutletStockTransaction::where('outlet_warehouse_id', $warehouseId)
                ->where('id', $transactionId)
                ->with([
                    'item.category',
                    'user',
                    'approvedByUser',
                    'branchWarehouseTransaction',
                    'outletToKitchenTransaction',
                    'monthlyBalance'
                ])
                ->firstOrFail();

            return view('branch-warehouse.stock-transaction-detail', compact(
                'warehouse',
                'transaction'
            ));

        } catch (\Exception $e) {
            Log::error('Transaction detail error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat detail');
        }
    }

    // ============================================================
    // DISTRIBUTION TO KITCHEN
    // ============================================================

    /**
     * Distribution list
     */
    public function distributionIndex(Request $request)
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $branchId = $request->input('branch_id');
            $status = $request->input('status');
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            // ✅ FIX: Get warehouses and branches
            $warehouses = Warehouse::branch()
                ->where('status', 'ACTIVE')
                ->with('branch')
                ->get();
                
            $branches = Branch::where('status', 'ACTIVE')
                ->orderBy('branch_name')
                ->get();

            $query = OutletWarehouseToKitchenTransaction::with([
                'outletWarehouse.branch',
                'branch',
                'item.category',
                'user'
            ])->whereBetween('transaction_date', [$startDate, $endDate]);

            if ($warehouseId) {
                $query->where('outlet_warehouse_id', $warehouseId);
            }

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $distributions = $query->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $summary = [
                'total' => $distributions->total(),
                'pending' => OutletWarehouseToKitchenTransaction::pending()
                    ->when($warehouseId, fn($q) => $q->where('outlet_warehouse_id', $warehouseId))
                    ->count(),
                'in_progress' => OutletWarehouseToKitchenTransaction::inProgress()
                    ->when($warehouseId, fn($q) => $q->where('outlet_warehouse_id', $warehouseId))
                    ->count(),
                'received' => OutletWarehouseToKitchenTransaction::received()
                    ->when($warehouseId, fn($q) => $q->where('outlet_warehouse_id', $warehouseId))
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->count(),
            ];

            return view('branch-warehouse.distribution.index', compact(
                'distributions',
                'warehouses',
                'branches',
                'warehouseId',
                'branchId',
                'status',
                'startDate',
                'endDate',
                'summary'
            ));

        } catch (\Exception $e) {
            Log::error('Distribution index error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Create distribution form
     */
    public function distributionCreate(Request $request)
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            
            // ✅ FIX: Get warehouses and branches
            $warehouses = Warehouse::branch()
                ->where('status', 'ACTIVE')
                ->with('branch')
                ->get();
                
            $selectedWarehouse = $warehouseId ? Warehouse::branch()->findOrFail($warehouseId) : null;
            
            $branches = Branch::where('status', 'ACTIVE')
                ->orderBy('branch_name')
                ->get();

            $availableStock = $selectedWarehouse
                ? OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                    ->currentPeriod()
                    ->with('item.category')
                    ->where('closing_stock', '>', 0)
                    ->get()
                : collect();

            return view('branch-warehouse.distribution.create', compact(
                'warehouses',
                'selectedWarehouse',
                'branches',
                'availableStock'
            ));

        } catch (\Exception $e) {
            Log::error('Create distribution form error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat form');
        }
    }

    /**
     * Store distribution
     */
    public function distributionStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'outlet_warehouse_id' => 'required|exists:warehouses,id',
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.batch_no' => 'nullable|string|max:50',
            'items.*.notes' => 'nullable|string|max:255',
            'transaction_date' => 'required|date|before_or_equal:today',
            'general_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $warehouse = Warehouse::branch()->findOrFail($request->outlet_warehouse_id);
            $branch = Branch::findOrFail($request->branch_id);
            
            $successCount = 0;
            $errors = [];

            foreach ($request->items as $itemData) {
                // Check stock
                $balance = OutletWarehouseMonthlyBalance::where('warehouse_id', $request->outlet_warehouse_id)
                    ->where('item_id', $itemData['item_id'])
                    ->currentPeriod()
                    ->first();

                if (!$balance || $balance->closing_stock < $itemData['quantity']) {
                    $item = Item::find($itemData['item_id']);
                    $available = $balance ? $balance->closing_stock : 0;
                    $errors[] = "Stock {$item->item_name} tidak cukup. Tersedia: {$available}";
                    continue;
                }

                $data = [
                    'outlet_warehouse_id' => $request->outlet_warehouse_id,
                    'branch_id' => $request->branch_id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'batch_no' => $itemData['batch_no'] ?? null,
                    'notes' => $itemData['notes'] ?? $request->general_notes,
                    'transaction_date' => $request->transaction_date,
                    'user_id' => auth()->id(),
                ];

                $transaction = OutletWarehouseToKitchenTransaction::createDistribution($data);

                if ($transaction) {
                    $successCount++;
                } else {
                    $item = Item::find($itemData['item_id']);
                    $errors[] = "Gagal distribusi: {$item->item_name}";
                }
            }

            if ($successCount > 0) {
                DB::commit();

                $message = "✅ Berhasil distribusi $successCount item ke {$branch->branch_name}";
                if (count($errors) > 0) {
                    $message .= ". ⚠️ " . implode(', ', $errors);
                }

                return redirect()
                    ->route('branch-warehouse.distribution-index')
                    ->with('success', $message);
            } else {
                DB::rollBack();
                return back()->withInput()->with('error', '❌ ' . implode(', ', $errors));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store distribution error: ' . $e->getMessage());
            return back()->withInput()->with('error', '❌ Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show distribution detail
     */
    public function distributionShow($id)
    {
        try {
            $distribution = OutletWarehouseToKitchenTransaction::with([
                'outletWarehouse.branch',
                'branch',
                'item.category',
                'user',
                'preparedByUser',
                'deliveredByUser',
                'receivedByUser',
                'outletStockTransaction',
                'kitchenStockTransaction'
            ])->findOrFail($id);

            return view('branch-warehouse.distribution-show', compact('distribution'));

        } catch (\Exception $e) {
            Log::error('Distribution show error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat detail');
        }
    }

    /**
     * Update distribution status
     */
    public function distributionUpdateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:PREPARED,IN_TRANSIT,DELIVERED,RECEIVED,CANCELLED',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $distribution = OutletWarehouseToKitchenTransaction::findOrFail($id);

            if ($request->status === 'RECEIVED') {
                if ($distribution->markAsReceived(auth()->id(), $request->notes)) {
                    DB::commit();
                    return back()->with('success', '✅ Distribusi berhasil diterima di kitchen');
                } else {
                    DB::rollBack();
                    return back()->with('error', '❌ Gagal update status');
                }
            } else {
                if ($distribution->updateStatus($request->status, $request->notes)) {
                    DB::commit();
                    return back()->with('success', '✅ Status berhasil diupdate');
                } else {
                    DB::rollBack();
                    return back()->with('error', '❌ Gagal update status');
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update distribution status error: ' . $e->getMessage());
            return back()->with('error', '❌ Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ============================================================
    // AJAX METHODS
    // ============================================================

    /**
     * Get available stock (AJAX)
     */
    public function getAvailableStock($warehouseId)
    {
        try {
            $stock = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->currentPeriod()
                ->with('item:id,item_code,item_name,unit,unit_cost,category_id')
                ->where('closing_stock', '>', 0)
                ->get()
                ->map(function($balance) {
                    return [
                        'item_id' => $balance->item_id,
                        'item_code' => $balance->item->item_code,
                        'item_name' => $balance->item->item_name,
                        'unit' => $balance->item->unit,
                        'available_stock' => $balance->closing_stock,
                        'unit_cost' => $balance->item->unit_cost,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $stock
            ]);

        } catch (\Exception $e) {
            Log::error('Get available stock error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * Get item stock info (AJAX)
     */
    public function getItemStockInfo($warehouseId, $itemId)
    {
        try {
            $balance = OutletWarehouseMonthlyBalance::where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->currentPeriod()
                ->with('item')
                ->first();

            if (!$balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'item_name' => $balance->item->item_name,
                    'item_code' => $balance->item->item_code,
                    'unit' => $balance->item->unit,
                    'current_stock' => $balance->closing_stock,
                    'low_stock_threshold' => $balance->item->low_stock_threshold,
                    // 'maximum_stock' => $balance->item->maximum_stock,
                    'unit_cost' => $balance->item->unit_cost,
                    'stock_status' => $balance->stock_status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get item stock info error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
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

        // ✅ Only validate selected items
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

            // ✅ Process each selected item
            foreach ($selectedItems as $index => $item) {
                try {
                    $itemId = $item['item_id'];
                    $quantity = $item['quantity'];
                    $itemNotes = $item['item_notes'] ?? '';

                    // ✅ 1. Validate stock availability at BRANCH warehouse
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

                    // ✅ 2. Create BRANCH stock transaction (OUT - TRANSFER_TO_BRANCH)
                    // FIXED: Use constant instead of string
                    $branchTransaction = BranchStockTransaction::create([
                        'branch_id' => $warehouse->branch_id,
                        'warehouse_id' => $warehouseId,
                        'item_id' => $itemId,
                        'transaction_type' => 'OUT', // ✅ FIXED!
                        'quantity' => $quantity,
                        'reference_no' => $referenceNo,
                        'notes' => "Distribusi ke outlet {$outlet->warehouse_name}" . ($itemNotes ? " | {$itemNotes}" : '') . ($request->notes ? " | {$request->notes}" : ''),
                        'transaction_date' => now(),
                        'user_id' => auth()->id()
                    ]);

                    $branchBalance->stock_out = (float)$branchBalance->stock_out + (float)$quantity;
                    // Recalculate closing stock
                    $branchBalance->closing_stock = (float)$branchBalance->opening_stock 
                                                + (float)$branchBalance->stock_in 
                                                - (float)$branchBalance->stock_out 
                                                + (float)$branchBalance->adjustments;

                    // Save
                    $branchBalance->save();

                    // ✅ 4. Create OUTLET stock transaction (IN - RECEIVE_FROM_BRANCH) using Model method
                    $outletTransaction = \App\Models\OutletStockTransaction::createReceiveFromBranch([
                        'outlet_warehouse_id' => $request->outlet_id,
                        'item_id' => $itemId,
                        'quantity' => $quantity,
                        'user_id' => auth()->id(),
                        'branch_warehouse_transaction_id' => $branchTransaction->id,
                        'unit_cost' => $branchBalance->avg_unit_cost ?? 0,
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

                    // ✅ 5. OutletWarehouseMonthlyBalance sudah di-update otomatis oleh OutletStockTransaction::createReceiveFromBranch()

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
}
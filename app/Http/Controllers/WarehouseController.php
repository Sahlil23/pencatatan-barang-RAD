<?php
// filepath: app/Http/Controllers/WarehouseController.php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('set.branch.context');
    }

    // ========================================
    // 📋 READ - INDEX (List all warehouses)
    // ========================================

    /**
     * Display a listing of warehouses
     */

public function index(Request $request)
{
    try {
        // ✅ AUTHORIZATION CHECK
        $this->authorize('viewAny', Warehouse::class);

        $query = Warehouse::with('branch');

        // ✅ APPLY WAREHOUSE FILTER (non-super admin)
        if (!$this->isSuperAdmin()) {
            $accessibleWarehouseIds = $this->currentUser()->getAccessibleWarehouseIds();
            
            Log::info('Warehouse access filter', [
                'user_id' => $this->currentUser()->id,
                'role' => $this->currentUser()->role,
                'accessible_warehouses' => $accessibleWarehouseIds
            ]);

            if (empty($accessibleWarehouseIds)) {
                return view('warehouses.index', [
                    'warehouses' => collect([]),
                    'message' => 'You do not have access to any warehouses.'
                ]);
            }

            $query->whereIn('id', $accessibleWarehouseIds);
        }

        // Apply filters
        $query->when($request->search, function ($query, $search) {
            $query->search($search);
        })
        ->when($request->status, function ($query, $status) {
            $query->where('status', $status);
        })
        ->when($request->type, function ($query, $type) {
            $query->where('warehouse_type', $type);
        });

        $warehouses = $query->orderBy('warehouse_name')->paginate(15);

        Log::info('Warehouses loaded', [
            'count' => $warehouses->count(),
            'user_role' => $this->currentUser()->role
        ]);

        return view('warehouses.index', compact('warehouses'));
        
    } catch (\Exception $e) {
        Log::error('Warehouse index error: ' . $e->getMessage());
        return $this->errorResponse('Error loading warehouses: ' . $e->getMessage());
    }
}

    // ========================================
    // 👁️ READ - SHOW (View single warehouse)
    // ========================================

    /**
     * Display the specified warehouse
     */
    public function show($id)
    {
        try {
            $warehouse = Warehouse::with(['branch'])->findOrFail($id);
            
            // ✅ AUTHORIZATION CHECK
            $this->authorize('view', $warehouse);
            
            // Get performance metrics
            $metrics = $warehouse->getPerformanceMetrics();
            
            // Get recent activity (based on warehouse type)
            $recentActivity = [];
            if ($warehouse->isCentral()) {
                $recentActivity = $warehouse->centralStockTransactions()
                    ->with(['item', 'user'])
                    ->latest()
                    ->take(10)
                    ->get();
            } else {
                $recentActivity = $warehouse->branchStockTransactions()
                    ->with(['item', 'user'])
                    ->latest()
                    ->take(10)
                    ->get();
            }

            return view('warehouses.show', compact('warehouse', 'metrics', 'recentActivity'));

        } catch (\Exception $e) {
            Log::error('Warehouse show error: ' . $e->getMessage());
            return $this->errorResponse('Error loading warehouse details: ' . $e->getMessage());
        }
    }

    // ========================================
    // ➕ CREATE - FORM & STORE
    // ========================================

    /**
     * Show the form for creating a new warehouse
     */
    public function create()
    {
        try {
            // ✅ AUTHORIZATION CHECK
            // $this->authorize('create', Warehouse::class);

            // Get accessible branches
            $accessibleBranchIds = $this->currentUser()->getAccessibleBranchIds();
            
            $branches = Branch::whereIn('id', $accessibleBranchIds)
                ->where('status', 'active')
                ->orderBy('branch_name')
                ->get();

            Log::info('Warehouse create form loaded', [
                'user_id' => $this->currentUser()->id,
                'accessible_branches' => $accessibleBranchIds
            ]);

            return view('warehouses.create', compact('branches'));

        } catch (\Exception $e) {
            Log::error('Warehouse create form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading create form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created warehouse
     */
    public function store(Request $request)
    {
        // ✅ AUTHORIZATION CHECK
        // $this->authorize('create', Warehouse::class);

        // Validation
        $validator = Validator::make($request->all(), Warehouse::validationRules(), Warehouse::validationMessages());

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        // ✅ Validate branch access for non-super-admin
        if (!$this->isSuperAdmin() && $request->filled('branch_id')) {
            $accessibleBranchIds = $this->currentUser()->getAccessibleBranchIds();
            
            if (!in_array($request->branch_id, $accessibleBranchIds)) {
                return $this->errorResponse('You do not have access to create warehouse in this branch');
            }
        }

        return $this->executeTransaction(
            function () use ($request) {
                // Auto-generate warehouse code if not provided
                $warehouseCode = $request->warehouse_code;
                if (!$warehouseCode) {
                    $branchCode = null;
                    if ($request->warehouse_type === 'branch' && $request->branch_id) {
                        $branch = Branch::find($request->branch_id);
                        $branchCode = $branch ? $branch->branch_code : 'BR';
                    }
                    $warehouseCode = Warehouse::generateWarehouseCode($request->warehouse_type, $branchCode);
                }

                $data = $request->all();
                $data['warehouse_code'] = $warehouseCode;

                if ($request->has('settings')) {
                    $data['settings'] = $request->settings;
                }

                $warehouse = Warehouse::create($data);

                // $this->logActivity('create_warehouse', 'Warehouse', $warehouse->id, [
                //     'warehouse_code' => $warehouse->warehouse_code,
                //     'warehouse_name' => $warehouse->warehouse_name,
                //     'warehouse_type' => $warehouse->warehouse_type
                // ]);

                Log::info('Warehouse created successfully', [
                    'warehouse_id' => $warehouse->id,
                    'warehouse_code' => $warehouse->warehouse_code,
                    'warehouse_type' => $warehouse->warehouse_type
                ]);

                return $warehouse;
            },
            'Warehouse created successfully',
            'Failed to create warehouse'
        );
    }

    // ========================================
    // ✏️ UPDATE - EDIT FORM & UPDATE
    // ========================================

    /**
     * Show the form for editing the specified warehouse
     */
    public function edit($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            
            // ✅ AUTHORIZATION CHECK
            $this->authorize('update', $warehouse);

            // Get accessible branches
            $accessibleBranchIds = $this->currentUser()->getAccessibleBranchIds();
            
            $branches = Branch::whereIn('id', $accessibleBranchIds)
                ->where('status', 'active')
                ->orderBy('branch_name')
                ->get();

            return view('warehouses.edit', compact('warehouse', 'branches'));

        } catch (\Exception $e) {
            Log::error('Warehouse edit form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading edit form: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified warehouse
     */
    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        // ✅ AUTHORIZATION CHECK
        $this->authorize('update', $warehouse);

        // Validation
        $validator = Validator::make($request->all(), Warehouse::validationRules($id), Warehouse::validationMessages());

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->executeTransaction(
            function () use ($request, $warehouse) {
                // $oldData = $warehouse->only(['warehouse_code', 'warehouse_name', 'status']);
                
                $data = $request->all();
                if ($request->has('settings')) {
                    $data['settings'] = $request->settings;
                }

                $warehouse->update($data);

                // $this->logActivity('update_warehouse', 'Warehouse', $warehouse->id, [
                //     'old' => $oldData,
                //     'new' => $data
                // ]);

                Log::info('Warehouse updated successfully', [
                    'warehouse_id' => $warehouse->id,
                    'changes' => $warehouse->getChanges()
                ]);

                return $warehouse;
            },
            'Warehouse updated successfully',
            'Failed to update warehouse'
        );
    }

    // ========================================
    // 🗑️ DELETE - DESTROY
    // ========================================

    /**
     * Remove the specified warehouse (Soft Delete)
     */
    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        // ✅ AUTHORIZATION CHECK
        $this->authorize('delete', $warehouse);

        // Check if warehouse has active stock
        $hasActiveStock = false;
        
        if ($warehouse->isCentral()) {
            $hasActiveStock = $warehouse->centralStockBalances()
                ->where('closing_stock', '>', 0)
                ->exists();
        } else {
            $hasActiveStock = $warehouse->branchStockBalances()
                ->where('closing_stock', '>', 0)
                ->exists();
        }

        if ($hasActiveStock) {
            return $this->errorResponse('Cannot delete warehouse with active stock. Please transfer or clear stock first.');
        }

        return $this->executeTransaction(
            function () use ($warehouse) {
                $warehouseId = $warehouse->id;
                $warehouseData = [
                    'code' => $warehouse->warehouse_code,
                    'name' => $warehouse->warehouse_name,
                    'type' => $warehouse->warehouse_type
                ];

                $warehouse->delete();

                // $this->logActivity('delete_warehouse', 'Warehouse', $warehouseId, $warehouseData);

                Log::info('Warehouse deleted successfully', $warehouseData);

                return true;
            },
            'Warehouse deleted successfully',
            'Failed to delete warehouse'
        );
    }

    // ========================================
    // 🔄 ADDITIONAL ACTIONS
    // ========================================

    /**
     * Change warehouse status (Active/Inactive/Maintenance)
     */
    public function changeStatus(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        // ✅ AUTHORIZATION CHECK
        $this->authorize('update', $warehouse);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,maintenance',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->executeTransaction(
            function () use ($request, $warehouse) {
                $oldStatus = $warehouse->status;
                
                $warehouse->update([
                    'status' => $request->status
                ]);

                // $this->logActivity('change_warehouse_status', 'Warehouse', $warehouse->id, [
                //     'old_status' => $oldStatus,
                //     'new_status' => $request->status,
                //     'reason' => $request->reason
                // ]);

                Log::info('Warehouse status changed', [
                    'warehouse_id' => $warehouse->id,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status
                ]);

                return true;
            },
            'Warehouse status updated successfully',
            'Failed to update warehouse status'
        );
    }

    // ========================================
    // 📊 UTILITY METHODS
    // ========================================

    /**
     * Export warehouses data
     */
    public function export(Request $request)
    {
        try {
            $query = Warehouse::with('branch');

            // Apply same filters as index
            if ($request->filled('search')) {
                $query->search($request->search);
            }
            if ($request->filled('warehouse_type')) {
                $query->where('warehouse_type', $request->warehouse_type);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $warehouses = $query->get();

            // Simple CSV export
            $filename = 'warehouses_' . now()->format('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($warehouses) {
                $file = fopen('php://output', 'w');
                
                // Header
                fputcsv($file, [
                    'Code', 'Name', 'Type', 'Branch', 'Address', 
                    'PIC Name', 'PIC Phone', 'Capacity M2', 'Capacity Volume', 'Status'
                ]);

                // Data
                foreach ($warehouses as $warehouse) {
                    fputcsv($file, [
                        $warehouse->warehouse_code,
                        $warehouse->warehouse_name,
                        ucfirst($warehouse->warehouse_type),
                        $warehouse->branch ? $warehouse->branch->branch_name : '-',
                        $warehouse->address,
                        $warehouse->pic_name ?: '-',
                        $warehouse->pic_phone ?: '-',
                        $warehouse->capacity_m2 ?: '-',
                        $warehouse->capacity_volume ?: '-',
                        ucfirst($warehouse->status)
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Warehouse export error: ' . $e->getMessage());
            return back()->with('error', 'Error exporting warehouses: ' . $e->getMessage());
        }
    }

    /**
     * Get warehouse untuk AJAX calls
     */
    public function getWarehouses(Request $request)
    {
        try {
            $query = Warehouse::active();

            // ✅ APPLY ACCESS FILTER
            if (!$this->isSuperAdmin()) {
                $accessibleWarehouseIds = $this->currentUser()->getAccessibleWarehouseIds();
                $query->whereIn('id', $accessibleWarehouseIds);
            }

            if ($request->filled('type')) {
                $query->where('warehouse_type', $request->type);
            }

            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            $warehouses = $query->get(['id', 'warehouse_code', 'warehouse_name', 'warehouse_type']);

            return response()->json([
                'success' => true,
                'data' => $warehouses
            ]);

        } catch (\Exception $e) {
            Log::error('Get warehouses AJAX error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading warehouses'
            ], 500);
        }
    }
}
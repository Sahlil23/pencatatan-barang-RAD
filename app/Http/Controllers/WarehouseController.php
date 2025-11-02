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
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    // ========================================
    // 📋 READ - INDEX (List all warehouses)
    // ========================================

    /**
     * Display a listing of warehouses
     */

public function index(Request $request)
{
    try {
        // Simplified query without complex relationships
        $warehouses = Warehouse::query()
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->type, function ($query, $type) {
                $query->where('warehouse_type', $type);
            })
            ->orderBy('warehouse_name')
            ->paginate(15);

        return view('warehouses.index', compact('warehouses'));
        
    } catch (\Exception $e) {
        Log::error('Warehouse index error: ' . $e->getMessage());
        
        // Return empty collection on error
        $warehouses = new \Illuminate\Pagination\LengthAwarePaginator(
            [],
            0,
            15,
            1,
            ['path' => request()->url()]
        );
        
        return view('warehouses.index', compact('warehouses'))
            ->with('error', 'Error loading warehouses: ' . $e->getMessage());
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
            return back()->with('error', 'Error loading warehouse details: ' . $e->getMessage());
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
            // Debug: Check raw data
            $branchCount = \DB::table('branches')->count();
            \Log::info('Branch count check: ' . $branchCount);
            
            // Debug: Check status values in database
            $statusCheck = \DB::table('branches')
                ->select('status', \DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();
            \Log::info('Branch status breakdown: ' . $statusCheck->toJson());
            
            // ✅ TRY ELOQUENT QUERY FIRST
            try {
                $branches = Branch::orderBy('branch_name')
                                ->get(['id', 'branch_code', 'branch_name', 'city', 'status']);
                
                \Log::info('Eloquent query result', [
                    'count' => $branches->count(),
                    'first_3' => $branches->take(3)->toArray()
                ]);
                
                // If Eloquent fails, use raw query
                if ($branches->isEmpty()) {
                    \Log::warning('Eloquent query returned empty, trying raw query');
                    
                    $branchesRaw = \DB::table('branches')
                        ->whereNull('deleted_at') // Handle soft deletes manually
                        ->orderBy('branch_name')
                        ->get(['id', 'branch_code', 'branch_name', 'city', 'status']);
                    
                    // Convert to collection for compatibility
                    $branches = $branchesRaw->map(function($branch) {
                        return (object) [
                            'id' => $branch->id,
                            'branch_code' => $branch->branch_code,
                            'branch_name' => $branch->branch_name,
                            'city' => $branch->city,
                            'status' => $branch->status
                        ];
                    });
                    
                    \Log::info('Raw query result', [
                        'count' => $branches->count(),
                        'first_3' => $branches->take(3)->toArray()
                    ]);
                }
                
            } catch (\Exception $modelError) {
                \Log::error('Branch model error: ' . $modelError->getMessage());
                
                // Fallback to raw query
                $branchesRaw = \DB::table('branches')
                    ->whereNull('deleted_at')
                    ->orderBy('branch_name')
                    ->get(['id', 'branch_code', 'branch_name', 'city', 'status']);
                
                $branches = $branchesRaw->map(function($branch) {
                    return (object) [
                        'id' => $branch->id,
                        'branch_code' => $branch->branch_code,
                        'branch_name' => $branch->branch_name,
                        'city' => $branch->city,
                        'status' => $branch->status
                    ];
                });
            }
            
            \Log::info('Final branches for view', [
                'count' => $branches->count(),
                'status_values' => $branches->pluck('status')->unique()->toArray()
            ]);
            
            return view('warehouses.create', compact('branches'));

        } catch (\Exception $e) {
            Log::error('Warehouse create form error: ' . $e->getMessage());
            
            // Emergency fallback
            $branches = collect([]);
            return view('warehouses.create', compact('branches'))
                ->with('warning', 'Could not load branches: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created warehouse
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), Warehouse::validationRules(), Warehouse::validationMessages());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Auto-generate warehouse code jika tidak diisi
            $warehouseCode = $request->warehouse_code;
            if (!$warehouseCode) {
                $branchCode = null;
                if ($request->warehouse_type === 'branch' && $request->branch_id) {
                    $branch = Branch::find($request->branch_id);
                    $branchCode = $branch ? $branch->branch_code : 'BR';
                }
                $warehouseCode = Warehouse::generateWarehouseCode($request->warehouse_type, $branchCode);
            }

            // Prepare data
            $data = $request->all();
            $data['warehouse_code'] = $warehouseCode;

            // Handle settings
            if ($request->has('settings')) {
                $data['settings'] = $request->settings;
            }

            // Create warehouse
            $warehouse = Warehouse::create($data);

            DB::commit();

            Log::info('Warehouse created successfully', [
                'warehouse_id' => $warehouse->id,
                'warehouse_code' => $warehouse->warehouse_code,
                'warehouse_type' => $warehouse->warehouse_type
            ]);

            return redirect()->route('warehouses.index')
                           ->with('success', 'Warehouse created successfully: ' . $warehouse->warehouse_name);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Warehouse creation error: ' . $e->getMessage());
            return back()->with('error', 'Error creating warehouse: ' . $e->getMessage())->withInput();
        }
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
            $branches = Branch::orderBy('branch_name')->get();

            return view('warehouses.edit', compact('warehouse', 'branches'));

        } catch (\Exception $e) {
            Log::error('Warehouse edit form error: ' . $e->getMessage());
            return back()->with('error', 'Error loading edit form: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified warehouse
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), Warehouse::validationRules($id), Warehouse::validationMessages());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $warehouse = Warehouse::findOrFail($id);

            // Prepare data
            $data = $request->all();

            // Handle settings
            if ($request->has('settings')) {
                $data['settings'] = $request->settings;
            }

            // Update warehouse
            $warehouse->update($data);

            DB::commit();

            Log::info('Warehouse updated successfully', [
                'warehouse_id' => $warehouse->id,
                'warehouse_code' => $warehouse->warehouse_code,
                'changes' => $warehouse->getChanges()
            ]);

            return redirect()->route('warehouses.index')
                           ->with('success', 'Warehouse updated successfully: ' . $warehouse->warehouse_name);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Warehouse update error: ' . $e->getMessage());
            return back()->with('error', 'Error updating warehouse: ' . $e->getMessage())->withInput();
        }
    }

    // ========================================
    // 🗑️ DELETE - DESTROY
    // ========================================

    /**
     * Remove the specified warehouse (Soft Delete)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $warehouse = Warehouse::findOrFail($id);

            // Check if warehouse has active stock atau transactions
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
                return back()->with('warning', 'Cannot delete warehouse with active stock. Please transfer or clear stock first.');
            }

            // Store info untuk log
            $warehouseInfo = [
                'id' => $warehouse->id,
                'code' => $warehouse->warehouse_code,
                'name' => $warehouse->warehouse_name,
                'type' => $warehouse->warehouse_type
            ];

            // Soft delete
            $warehouse->delete();

            DB::commit();

            Log::info('Warehouse deleted successfully', $warehouseInfo);

            return redirect()->route('warehouses.index')
                           ->with('success', 'Warehouse deleted successfully: ' . $warehouseInfo['name']);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Warehouse deletion error: ' . $e->getMessage());
            return back()->with('error', 'Error deleting warehouse: ' . $e->getMessage());
        }
    }

    // ========================================
    // 🔄 ADDITIONAL CRUD ACTIONS
    // ========================================

    /**
     * Restore soft deleted warehouse
     */
    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $warehouse = Warehouse::withTrashed()->findOrFail($id);
            
            if (!$warehouse->trashed()) {
                return back()->with('warning', 'Warehouse is not deleted.');
            }

            $warehouse->restore();

            DB::commit();

            Log::info('Warehouse restored successfully', [
                'warehouse_id' => $warehouse->id,
                'warehouse_code' => $warehouse->warehouse_code
            ]);

            return redirect()->route('warehouses.index')
                           ->with('success', 'Warehouse restored successfully: ' . $warehouse->warehouse_name);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Warehouse restore error: ' . $e->getMessage());
            return back()->with('error', 'Error restoring warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete warehouse
     */
    public function forceDestroy($id)
    {
        DB::beginTransaction();
        try {
            $warehouse = Warehouse::withTrashed()->findOrFail($id);

            // Double check - no related data
            $hasRelatedData = $warehouse->centralStockBalances()->exists() ||
                             $warehouse->centralStockTransactions()->exists() ||
                             $warehouse->branchStockBalances()->exists() ||
                             $warehouse->branchStockTransactions()->exists();

            if ($hasRelatedData) {
                return back()->with('error', 'Cannot permanently delete warehouse with related data.');
            }

            $warehouseName = $warehouse->warehouse_name;
            $warehouse->forceDelete();

            DB::commit();

            Log::warning('Warehouse permanently deleted', [
                'warehouse_id' => $id,
                'warehouse_name' => $warehouseName
            ]);

            return redirect()->route('warehouses.index')
                           ->with('success', 'Warehouse permanently deleted: ' . $warehouseName);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Warehouse force delete error: ' . $e->getMessage());
            return back()->with('error', 'Error permanently deleting warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Change warehouse status (Active/Inactive/Maintenance)
     */
    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,maintenance',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $warehouse = Warehouse::findOrFail($id);
            $oldStatus = $warehouse->status;
            
            $warehouse->update([
                'status' => $request->status
            ]);

            DB::commit();

            Log::info('Warehouse status changed', [
                'warehouse_id' => $warehouse->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'reason' => $request->reason
            ]);

            return back()->with('success', 'Warehouse status updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Warehouse status change error: ' . $e->getMessage());
            return back()->with('error', 'Error updating warehouse status: ' . $e->getMessage());
        }
    }

    /**
     * Bulk actions untuk multiple warehouses
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete,activate,deactivate,maintenance',
            'warehouse_ids' => 'required|array|min:1',
            'warehouse_ids.*' => 'exists:warehouses,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $warehouses = Warehouse::whereIn('id', $request->warehouse_ids)->get();
            $count = 0;

            foreach ($warehouses as $warehouse) {
                switch ($request->action) {
                    case 'delete':
                        // Check stock before delete
                        $hasStock = $warehouse->isCentral() ? 
                            $warehouse->centralStockBalances()->where('closing_stock', '>', 0)->exists() :
                            $warehouse->branchStockBalances()->where('closing_stock', '>', 0)->exists();
                        
                        if (!$hasStock) {
                            $warehouse->delete();
                            $count++;
                        }
                        break;
                    case 'activate':
                        $warehouse->update(['status' => 'active']);
                        $count++;
                        break;
                    case 'deactivate':
                        $warehouse->update(['status' => 'inactive']);
                        $count++;
                        break;
                    case 'maintenance':
                        $warehouse->update(['status' => 'maintenance']);
                        $count++;
                        break;
                }
            }

            DB::commit();

            Log::info('Bulk warehouse action completed', [
                'action' => $request->action,
                'warehouses_affected' => $count,
                'total_selected' => count($request->warehouse_ids)
            ]);

            return back()->with('success', "Bulk action completed successfully. {$count} warehouses affected.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk warehouse action error: ' . $e->getMessage());
            return back()->with('error', 'Error performing bulk action: ' . $e->getMessage());
        }
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
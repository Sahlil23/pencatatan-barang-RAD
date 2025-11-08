<?php
// filepath: app/Http/Controllers/ItemController.php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    // ========================================
    // 📋 READ - INDEX (List all items)
    // ========================================

    /**
     * Display a listing of items (Data Master only)
     */
    public function index(Request $request)
    {
        try {
            $query = Item::with('category')->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('item_name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Paginate results
            $items = $query->paginate(15)->appends($request->query());
            
            // Get data for filters
            $categories = Category::orderBy('category_name')->get();

            // Statistics
            $stats = [
                'total' => Item::count(),
                'by_category' => Item::selectRaw('categories.category_name, COUNT(*) as count')
                    ->join('categories', 'items.category_id', '=', 'categories.id')
                    ->groupBy('categories.id', 'categories.category_name')
                    ->get()
            ];

            return view('items.index', compact('items', 'categories', 'stats'));

        } catch (\Exception $e) {
            Log::error('Item index error: ' . $e->getMessage());
            return back()->with('error', 'Error loading items: ' . $e->getMessage());
        }
    }

    // ========================================
    // 👁️ READ - SHOW (View single item)
    // ========================================

    /**
     * Display the specified item (Data Master only)
     */
    public function show($id)
    {
        try {
            $item = Item::with('category')->findOrFail($id);

            // Item detail information only - no stock data
            $itemInfo = [
                'created_date' => $item->created_at,
                'updated_date' => $item->updated_at,
                'category_info' => $item->category,
                'unit_info' => $item->unit,
                'threshold_info' => $item->low_stock_threshold,
                'cost_info' => $item->unit_cost
            ];

            return view('items.show', compact('item', 'itemInfo'));

        } catch (\Exception $e) {
            Log::error('Item show error: ' . $e->getMessage());
            return back()->with('error', 'Error loading item details: ' . $e->getMessage());
        }
    }

    // ========================================
    // ➕ CREATE - FORM & STORE
    // ========================================

    /**
     * Show the form for creating a new item
     */
    public function create()
    {
        try {
            $categories = Category::orderBy('category_name')->get();
            
            return view('items.create', compact('categories'));

        } catch (\Exception $e) {
            Log::error('Item create form error: ' . $e->getMessage());
            return back()->with('error', 'Error loading create form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created item
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), Item::validationRules(), Item::validationMessages());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Auto-generate item code if not provided
            $itemCode = $request->sku;
            if (!$itemCode) {
                $categoryCode = Category::find($request->category_id)->category_code ?? 'ITM';
                $itemCode = $this->generateItemCode($categoryCode);
            }

            // Prepare data
            $data = $request->all();
            $data['sku'] = $itemCode;
            $data['status'] = $data['status'] ?? 'ACTIVE';

            // Create item (only master data)
            $item = Item::create($data);

            DB::commit();

            Log::info('Item created successfully', [
                'item_id' => $item->id,
                'sku' => $item->sku,
                'item_name' => $item->item_name
            ]);

            return redirect()->route('items.index')
                           ->with('success', 'Item created successfully: ' . $item->item_name);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Item creation error: ' . $e->getMessage());
            return back()->with('error', 'Error creating item: ' . $e->getMessage())->withInput();
        }
    }

    // ========================================
    // ✏️ UPDATE - EDIT FORM & UPDATE
    // ========================================

    /**
     * Show the form for editing the specified item
     */
    public function edit($id)
    {
        try {
            $item = Item::findOrFail($id);
            $categories = Category::orderBy('category_name')->get();

            return view('items.edit', compact('item', 'categories'));

        } catch (\Exception $e) {
            Log::error('Item edit form error: ' . $e->getMessage());
            return back()->with('error', 'Error loading edit form: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified item
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), Item::validationRules($id), Item::validationMessages());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $item = Item::findOrFail($id);

            // Update item (only master data)
            $item->update($request->all());

            DB::commit();

            Log::info('Item updated successfully', [
                'item_id' => $item->id,
                'sku' => $item->sku,
                'changes' => $item->getChanges()
            ]);

            return redirect()->route('items.index')
                           ->with('success', 'Item updated successfully: ' . $item->item_name);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Item update error: ' . $e->getMessage());
            return back()->with('error', 'Error updating item: ' . $e->getMessage())->withInput();
        }
    }

    // ========================================
    // 🗑️ DELETE - DESTROY
    // ========================================

    /**
     * Remove the specified item
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $item = Item::findOrFail($id);

            // Check if item has related data (central stock, branch stock, transactions)
            $hasRelatedData = $item->centralStockBalances()->exists() ||
                             $item->centralStockTransactions()->exists() ||
                             $item->branchMonthlyBalances()->exists() ||
                             $item->branchStockTransactions()->exists();

            if ($hasRelatedData) {
                return back()->with('warning', 'Cannot delete item with existing stock records or transactions. Set status to INACTIVE instead.');
            }

            // Store info for log
            $itemInfo = [
                'id' => $item->id,
                'code' => $item->sku,
                'name' => $item->item_name,
                'category' => $item->category->category_name ?? 'Unknown'
            ];

            // Delete item
            $item->delete();

            DB::commit();

            Log::info('Item deleted successfully', $itemInfo);

            return redirect()->route('items.index')
                           ->with('success', 'Item deleted successfully: ' . $itemInfo['name']);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Item deletion error: ' . $e->getMessage());
            return back()->with('error', 'Error deleting item: ' . $e->getMessage());
        }
    }

    // ========================================
    // 🔄 ADDITIONAL CRUD ACTIONS
    // ========================================

    /**
     * Change item status (Active/Inactive)
     */
    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:ACTIVE,INACTIVE',  
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $item = Item::findOrFail($id);
            $oldStatus = $item->status;
            
            $item->update([
                'status' => $request->status
            ]);

            DB::commit();

            Log::info('Item status changed', [
                'item_id' => $item->id,
                'sku' => $item->sku,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'reason' => $request->reason
            ]);

            return back()->with('success', 'Item status updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Item status change error: ' . $e->getMessage());
            return back()->with('error', 'Error updating item status: ' . $e->getMessage());
        }
    }

    /**
     * Bulk actions for multiple items
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete,activate,deactivate',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:items,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $items = Item::whereIn('id', $request->item_ids)->get();
            $count = 0;

            foreach ($items as $item) {
                switch ($request->action) {
                    case 'delete':
                        // Check related data before delete
                        $hasRelatedData = $item->centralStockBalances()->exists() ||
                                         $item->centralStockTransactions()->exists() ||
                                         $item->branchMonthlyBalances()->exists() ||
                                         $item->branchStockTransactions()->exists();
                        
                        if (!$hasRelatedData) {
                            $item->delete();
                            $count++;
                        }
                        break;
                    case 'activate':
                        $item->update(['status' => 'ACTIVE']);
                        $count++;
                        break;
                    case 'deactivate':
                        $item->update(['status' => 'INACTIVE']);
                        $count++;
                        break;
                }
            }

            DB::commit();

            Log::info('Bulk item action completed', [
                'action' => $request->action,
                'items_affected' => $count,
                'total_selected' => count($request->item_ids)
            ]);

            return back()->with('success', "Bulk action completed successfully. {$count} items affected.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk item action error: ' . $e->getMessage());
            return back()->with('error', 'Error performing bulk action: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate item
     */
    public function duplicate($id)
    {
        DB::beginTransaction();
        try {
            $originalItem = Item::findOrFail($id);
            
            // Generate new item code
            $categoryCode = $originalItem->category->category_code ?? 'ITM';
            $newItemCode = $this->generateItemCode($categoryCode);
            
            // Create duplicate with new code
            $duplicateItem = $originalItem->replicate();
            $duplicateItem->sku = $newItemCode;
            $duplicateItem->item_name = $originalItem->item_name . ' (Copy)';
            $duplicateItem->status = 'INACTIVE'; // Set as inactive by default
            $duplicateItem->save();

            DB::commit();

            Log::info('Item duplicated successfully', [
                'original_id' => $originalItem->id,
                'original_code' => $originalItem->sku,
                'duplicate_id' => $duplicateItem->id,
                'duplicate_code' => $duplicateItem->sku
            ]);

            return redirect()->route('items.edit', $duplicateItem->id)
                           ->with('success', 'Item duplicated successfully. Please update the details.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Item duplication error: ' . $e->getMessage());
            return back()->with('error', 'Error duplicating item: ' . $e->getMessage());
        }
    }

    // ========================================
    // 📊 UTILITY METHODS
    // ========================================

    /**
     * Export items data
     */
    public function export(Request $request)
    {
        try {
            $query = Item::with('category');

            // Apply same filters as index
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('item_name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $items = $query->get();

            // Simple CSV export
            $filename = 'items_master_' . now()->format('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($items) {
                $file = fopen('php://output', 'w');
                
                // Header
                fputcsv($file, [
                    'Item Code', 'Item Name', 'Category', 'Unit', 
                    'Low Stock Threshold', 'Unit Cost', 'Status', 
                    'Created Date', 'Updated Date'
                ]);

                // Data
                foreach ($items as $item) {
                    fputcsv($file, [
                        $item->sku,
                        $item->item_name,
                        $item->category ? $item->category->category_name : '-',
                        $item->unit,
                        $item->low_stock_threshold,
                        $item->unit_cost ?: '-',
                        $item->status,
                        $item->created_at->format('Y-m-d H:i:s'),
                        $item->updated_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Item export error: ' . $e->getMessage());
            return back()->with('error', 'Error exporting items: ' . $e->getMessage());
        }
    }

    /**
     * Get items for AJAX calls
     */
    public function getItems(Request $request)
    {
        try {
            $query = Item::where('status', 'ACTIVE');

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('item_name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }

            $items = $query->with('category')
                          ->get(['id', 'sku', 'item_name', 'category_id', 'unit', 'low_stock_threshold', 'unit_cost']);

            return response()->json([
                'success' => true,
                'data' => $items
            ]);

        } catch (\Exception $e) {
            Log::error('Get items AJAX error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading items'
            ], 500);
        }
    }

    /**
     * Import items from CSV
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $file = $request->file('import_file');
            $path = $file->getRealPath();
            $data = array_map('str_getcsv', file($path));
            
            // Remove header
            $header = array_shift($data);
            
            $imported = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                try {
                    if (count($row) < 4) continue; // Skip incomplete rows
                    
                    // Find or create category
                    $category = Category::firstOrCreate(
                        ['category_name' => $row[2]],
                        ['category_code' => strtoupper(substr($row[2], 0, 3))]
                    );

                    // Create item
                    Item::create([
                        'sku' => $row[0],
                        'item_name' => $row[1],
                        'category_id' => $category->id,
                        'unit' => $row[3] ?? 'pcs',
                        'low_stock_threshold' => isset($row[4]) ? floatval($row[4]) : 0,
                        'unit_cost' => isset($row[5]) ? floatval($row[5]) : null,
                        'status' => isset($row[6]) ? strtoupper($row[6]) : 'ACTIVE'
                    ]);
                    
                    $imported++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Import completed. {$imported} items imported successfully.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " errors occurred.";
            }

            Log::info('Items import completed', [
                'imported_count' => $imported,
                'error_count' => count($errors)
            ]);

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Items import error: ' . $e->getMessage());
            return back()->with('error', 'Error importing items: ' . $e->getMessage());
        }
    }

    // ========================================
    // 🔧 HELPER METHODS
    // ========================================

    /**
     * Generate unique item code
     */
    private function generateItemCode($categoryCode)
    {
        $prefix = strtoupper(substr($categoryCode, 0, 3));
        $lastItem = Item::where('sku', 'LIKE', $prefix . '%')
                       ->orderBy('sku', 'desc')
                       ->first();

        if ($lastItem) {
            $lastNumber = intval(substr($lastItem->sku, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get item statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_items' => Item::count(),
                'active_items' => Item::where('status', 'ACTIVE')->count(),
                'inactive_items' => Item::where('status', 'INACTIVE')->count(),
                'by_category' => Item::selectRaw('categories.category_name, COUNT(*) as count')
                    ->join('categories', 'items.category_id', '=', 'categories.id')
                    ->groupBy('categories.id', 'categories.category_name')
                    ->orderBy('count', 'desc')
                    ->get(),
                'recent_items' => Item::with('category')
                    ->latest()
                    ->take(5)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Get item stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics'
            ], 500);
        }
    }
}
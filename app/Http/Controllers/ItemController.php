<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['category', 'supplier']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->outOfStock();
                    break;
                case 'in':
                    $query->inStock();
                    break;
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(15);
        $categories = Category::get();

        return view('items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = Category::get();
        $suppliers = Supplier::all();
        return view('items.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:50|unique:items',
            'item_name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:20',
            'current_stock' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0'
        ]);

        Item::create($request->all());

        return redirect()->route('items.index')
            ->with('success', 'Item berhasil ditambahkan!');
    }

    public function show(Item $item)
    {
        $item->load(['category', 'supplier', 'stockTransactions' => function($query) {
            $query->latest()->take(10);
        }]);
        
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $categories = Category::get();
        $suppliers = Supplier::all();
        return view('items.edit', compact('item', 'categories', 'suppliers'));
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'sku' => 'required|string|max:50|unique:items,sku,' . $item->id,
            'item_name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:20',
            'low_stock_threshold' => 'required|numeric|min:0'
        ]);

        $item->update($request->except('current_stock')); // Stock tidak bisa diubah langsung

        return redirect()->route('items.index')
            ->with('success', 'Item berhasil diupdate!');
    }

    public function destroy(Item $item)
    {
        if ($item->stockTransactions()->count() > 0) {
            return redirect()->route('items.index')
                ->with('error', 'Item tidak dapat dihapus karena memiliki riwayat transaksi!');
        }

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item berhasil dihapus!');
    }

    public function lowStock()
    {
        $items = Item::lowStock()->with(['category', 'supplier'])->get();
        return view('items.low-stock', compact('items'));
    }

    public function adjustStock(Request $request, Item $item)
    {
        $request->validate([
            'adjustment_type' => 'required|in:add,reduce',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'required|string|max:255'
        ]);

        if ($request->adjustment_type === 'add') {
            $item->addStock($request->quantity, $request->notes);
            $message = 'Stok berhasil ditambahkan!';
        } else {
            if ($item->reduceStock($request->quantity, $request->notes)) {
                $message = 'Stok berhasil dikurangi!';
            } else {
                return redirect()->back()
                    ->with('error', 'Stok tidak mencukupi!');
            }
        }

        return redirect()->route('items.show', $item)
            ->with('success', $message);
    }

    public function report(Request $request)
    {
        $query = Item::with(['category', 'supplier']);

        // Filter berdasarkan kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter berdasarkan supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter berdasarkan status stok
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->outOfStock();
                    break;
                case 'in':
                    $query->inStock();
                    break;
            }
        }

        // Pencarian berdasarkan nama item atau SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Urutkan berdasarkan pilihan user
        $sortBy = $request->get('sort_by', 'item_name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'stock') {
            $query->orderBy('current_stock', $sortOrder);
        } elseif ($sortBy === 'category') {
            $query->join('categories', 'items.category_id', '=', 'categories.id')
                  ->orderBy('categories.category_name', $sortOrder)
                  ->select('items.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $items = $query->get();
        $categories = Category::all();
        $suppliers = Supplier::all();

        // Statistik untuk laporan
        $totalItems = $items->count();
        $totalStockValue = $items->sum('current_stock');
        $lowStockItems = $items->filter(function($item) {
            return $item->current_stock <= $item->low_stock_threshold && $item->current_stock > 0;
        })->count();
        $outOfStockItems = $items->filter(function($item) {
            return $item->current_stock <= 0;
        })->count();

        return view('items.report', compact(
            'items', 'categories', 'suppliers', 'totalItems', 
            'totalStockValue', 'lowStockItems', 'outOfStockItems'
        ));
    }
}
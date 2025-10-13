<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('items')->latest()->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        Supplier::create($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan!');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['items' => function($query) {
            $query->with('category');
        }]);
        
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil diupdate!');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->items()->count() > 0) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Supplier tidak dapat dihapus karena masih memiliki item!');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus!');
    }
}
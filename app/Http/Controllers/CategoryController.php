<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('items')->latest()->get();
        // dd($categories);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:150|unique:categories',
            'description' => 'nullable|string',
            // 'is_active' => 'boolean'
        ]);

        Category::create($request->all());

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function show(Category $category)
    {
        $category->load(['items' => function($query) {
            $query->with('supplier');
        }]);
        
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'category_name' => 'required|string|max:150|unique:categories,category_name,' . $category->id,
            'description' => 'nullable|string',
            // 'is_active' => 'boolean'
        ]);

        $category->update($request->all());

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diupdate!');
    }

    public function destroy(Category $category)
    {
        if ($category->items()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki item!');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus!');
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        
        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('categories.index')
            ->with('success', "Kategori berhasil {$status}!");
    }
}
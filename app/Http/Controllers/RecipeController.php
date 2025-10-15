<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::published();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Sort options
        $sortBy = $request->get('sort', 'name');
        switch ($sortBy) {
            case 'newest':
                $query->latest();
                break;
            case 'prep_time':
                $query->orderBy('prep_time');
                break;
            case 'total_time':
                $query->orderByRaw('(prep_time + cook_time)');
                break;
            default:
                $query->orderBy('name');
        }

        $recipes = $query->paginate(12);

        return view('recipes.index', compact('recipes'));
    }

    public function show($slug)
    {
        $recipe = Recipe::where('slug', $slug)->published()->firstOrFail();
        
        // Related recipes (same difficulty, excluding current)
        $relatedRecipes = Recipe::published()
            ->where('difficulty', $recipe->difficulty)
            ->where('id', '!=', $recipe->id)
            ->limit(3)
            ->get();

        return view('recipes.show', compact('recipe', 'relatedRecipes'));
    }

    public function create()
    {
        return view('recipes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'prep_time' => 'required|integer|min:0',
            'cook_time' => 'required|integer|min:0',
            'servings' => 'required|integer|min:1',
            'difficulty' => 'required|in:mudah,sedang,sulit',
            'ingredients' => 'required|array|min:1',
            'ingredients.*' => 'required|string',
            'instructions' => 'required|array|min:1',
            'instructions.*' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,published'
        ]);

        $data = $request->all();

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('recipes', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        Recipe::create($data);

        return redirect()->route('recipes.index')
            ->with('success', 'Resep berhasil ditambahkan!');
    }

    public function edit(Recipe $recipe)
    {
        return view('recipes.edit', compact('recipe'));
    }

    public function update(Request $request, Recipe $recipe)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'prep_time' => 'required|integer|min:0',
            'cook_time' => 'required|integer|min:0',
            'servings' => 'required|integer|min:1',
            'difficulty' => 'required|in:mudah,sedang,sulit',
            'ingredients' => 'required|array|min:1',
            'ingredients.*' => 'required|string',
            'instructions' => 'required|array|min:1',
            'instructions.*' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,published'
        ]);

        $data = $request->all();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                Storage::disk('public')->delete($recipe->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('recipes', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $recipe->update($data);

        return redirect()->route('recipes.index')
            ->with('success', 'Resep berhasil diupdate!');
    }

    public function destroy(Recipe $recipe)
    {
        // Delete image
        if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
            Storage::disk('public')->delete($recipe->image);
        }

        $recipe->delete();

        return redirect()->route('recipes.index')
            ->with('success', 'Resep berhasil dihapus!');
    }
}
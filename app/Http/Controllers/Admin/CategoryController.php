<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['products', 'subcategories'])
            ->with(['subcategories' => function ($q) {
                $q->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:rice,livestock'],
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $allowedUnits = $validated['type'] === 'rice' ? ['kg', 'kwintal', 'ton'] : ['ekor'];

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $validated['type'],
            'allowed_units' => json_encode($allowedUnits),
            'sort_order' => (Category::max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:rice,livestock'],
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $allowedUnits = $validated['type'] === 'rice' ? ['kg', 'kwintal', 'ton'] : ['ekor'];

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $validated['type'],
            'allowed_units' => json_encode($allowedUnits),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk mitra.');
        }

        $category->subcategories()->delete();
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    public function storeSubcategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:100'],
            'min_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'max_weight_kg' => ['nullable', 'numeric', 'min:0'],
        ]);

        $label = !empty($validated['label']) ? $validated['label'] : $validated['name'];

        $category->subcategories()->create([
            'name' => $validated['name'],
            'label' => $label,
            'min_weight_kg' => $validated['min_weight_kg'] ?? null,
            'max_weight_kg' => $validated['max_weight_kg'] ?? null,
            'sort_order' => ($category->subcategories()->max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        return back()->with('success', "Subkategori / menu '{$validated['name']}' berhasil ditambahkan ke kategori {$category->name}.");
    }

    public function updateSubcategory(Request $request, Category $category, Subcategory $subcategory)
    {
        if ($subcategory->category_id !== $category->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:100'],
            'min_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'max_weight_kg' => ['nullable', 'numeric', 'min:0'],
        ]);

        $label = !empty($validated['label']) ? $validated['label'] : $validated['name'];

        $subcategory->update([
            'name' => $validated['name'],
            'label' => $label,
            'min_weight_kg' => $validated['min_weight_kg'] ?? null,
            'max_weight_kg' => $validated['max_weight_kg'] ?? null,
        ]);

        return back()->with('success', "Subkategori / menu '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroySubcategory(Category $category, Subcategory $subcategory)
    {
        if ($subcategory->category_id !== $category->id) {
            abort(403);
        }

        if ($subcategory->products()->count() > 0) {
            return back()->with('error', 'Subkategori tidak dapat dihapus karena masih digunakan oleh produk mitra.');
        }

        $subcategory->delete();

        return back()->with('success', 'Subkategori berhasil dihapus.');
    }
}

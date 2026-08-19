<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->with('parent')
            ->withCount(['children', 'products'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::orderBy('name')->get();

        return view('categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('organization_id', Auth::user()->organization_id)],
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['name']);

        Category::create($validated);

        return redirect()->route('category.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Category $category)
    {
        $parents = Category::where('id', '!=', $category->id)->orderBy('name')->get();

        return view('categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('organization_id', Auth::user()->organization_id),
                Rule::notIn([$category->id]),
            ],
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if (! empty($validated['slug']) && $validated['slug'] !== $category->slug) {
            $validated['slug'] = $this->uniqueSlug($validated['slug'], $category->id);
        } else {
            unset($validated['slug']);
        }

        $category->update($validated);

        return redirect()->route('category.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'No se puede eliminar: la categoría tiene subcategorías. Reasignalas o eliminalas primero.');
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay productos asignados a esta categoría.');
        }

        $category->delete();

        return redirect()->route('category.index')->with('success', 'Categoría eliminada correctamente.');
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        $slug = $base;
        $suffix = 2;

        while (
            Category::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}

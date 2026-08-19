<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
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
            ->withCount(['children', 'products'])
            ->when($request->search, function ($query, $search) {
                // ilike (no like): Postgres es case-sensitive por defecto, a diferencia de MySQL.
                $query->where('name', 'ilike', "%{$search}%");
            })
            ->when($request->boolean('roots_only'), function ($query) {
                $query->whereNull('parent_id');
            })
            ->when($request->filled('parent_id'), function ($query) use ($request) {
                $query->where('parent_id', $request->parent_id);
            })
            ->when($request->filled('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return CategoryResource::collection($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('organization_id', Auth::user()->organization_id)],
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['name']);

        $category = Category::create($validated);

        return new CategoryResource($category);
    }

    public function show(Category $category)
    {
        $category->loadCount(['children', 'products'])->load('parent');

        return new CategoryResource($category);
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

        if (! empty($validated['slug']) && $validated['slug'] !== $category->slug) {
            $validated['slug'] = $this->uniqueSlug($validated['slug'], $category->id);
        } else {
            unset($validated['slug']);
        }

        $category->update($validated);

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: la categoria tiene subcategorias. Reasignalas o eliminalas primero.',
            ], 422);
        }

        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: hay productos asignados a esta categoria.',
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Categoria eliminada correctamente.']);
    }

    /**
     * Genera un slug unico dentro de la organizacion, agregando -2, -3... si hace falta.
     */
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

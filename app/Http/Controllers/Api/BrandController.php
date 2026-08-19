<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::query()
            ->withCount('products')
            ->when($request->search, function ($query, $search) {
                // ilike (no like): Postgres es case-sensitive por defecto, a diferencia de MySQL.
                $query->where('name', 'ilike', "%{$search}%");
            })
            ->when($request->filled('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return BrandResource::collection($brands);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('brands')->where('organization_id', Auth::user()->organization_id),
            ],
            'is_active' => 'boolean',
        ]);

        $brand = Brand::create($validated);

        return new BrandResource($brand);
    }

    public function show(Brand $brand)
    {
        $brand->loadCount('products');

        return new BrandResource($brand);
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('brands')->where('organization_id', Auth::user()->organization_id)->ignore($brand->id),
            ],
            'is_active' => 'boolean',
        ]);

        $brand->update($validated);

        return new BrandResource($brand);
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: hay productos asignados a esta marca.',
            ], 422);
        }

        $brand->delete();

        return response()->json(['message' => 'Marca eliminada correctamente.']);
    }
}

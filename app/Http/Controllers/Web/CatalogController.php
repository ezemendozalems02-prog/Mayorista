<?php

namespace App\Http\Controllers\Web;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Catálogo público (Fase 19): una vitrina de solo lectura, sin login, para
 * compartirle un link a un cliente mayorista. Muestra precio de lista
 * (retail_price) -- nunca wholesale_price ni costo, que son datos internos.
 * Solo tiene sentido en single_license (una sola organización instalada);
 * en modo SaaS habría que resolver la organización por dominio/slug, algo
 * que queda fuera de esta fase.
 */
class CatalogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(config('platform.mode') === 'single_license', 404);

        $organization = Organization::first();
        abort_unless($organization, 404);

        $products = Product::where('organization_id', $organization->id)
            ->where('status', ProductStatus::ACTIVE)
            ->with(['category', 'brand', 'stock'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                // ilike (no like): Postgres es case-sensitive por defecto, a diferencia de MySQL.
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('internal_code', 'ilike', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->category_id))
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $categories = Category::where('organization_id', $organization->id)
            ->orderBy('name')
            ->get();

        return view('catalog.index', compact('organization', 'products', 'categories'));
    }
}

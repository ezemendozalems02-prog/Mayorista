<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SparePart;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    public function index()
    {
        return response()->json(SparePart::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'sku' => 'nullable|string',
            'stock' => 'required|integer',
            'cost_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'currency' => 'required|string|size:3',
        ]);

        $part = SparePart::create($validated);
        return response()->json($part);
    }

    public function update(Request $request, SparePart $spare_part)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'stock' => 'required|integer',
            'cost_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
        ]);

        $spare_part->update($validated);

        if ($spare_part->stock <= 3) {
            $owners = \App\Models\User::where('organization_id', $spare_part->organization_id)
                ->where('role', \App\Enums\UserRole::OWNER)
                ->get();
            
            foreach ($owners as $owner) {
                $owner->notify(new \App\Notifications\LowStockNotification($spare_part));
            }
        }

        return response()->json($spare_part);
    }

    public function destroy(SparePart $spare_part)
    {
        $spare_part->delete();
        return response()->json(['message' => 'Repuesto eliminado.']);
    }
}

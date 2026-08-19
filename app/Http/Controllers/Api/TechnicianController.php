<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use Illuminate\Http\Request;

class TechnicianController extends Controller
{
    public function index()
    {
        return response()->json(Technician::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'specialties' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $technician = Technician::create($validated);
        return response()->json($technician);
    }

    public function update(Request $request, Technician $technician)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'specialties' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $technician->update($validated);
        return response()->json($technician);
    }

    public function destroy(Technician $technician)
    {
        $technician->delete();
        return response()->json(['message' => 'Técnico eliminado.']);
    }
}

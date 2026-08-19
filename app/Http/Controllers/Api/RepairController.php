<?php

namespace App\Http\Controllers\Api;

use App\Enums\RepairStatus;
use App\Http\Controllers\Controller;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $repairs = Repair::with(['client', 'technician'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, function ($q, $search) {
                $q->where('repair_number', 'like', "%{$search}%")
                    ->orWhere('device_model', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($repairs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'technician_id' => 'nullable|exists:technicians,id',
            'device_model' => 'required|string',
            'imei' => 'nullable|string',
            'reported_issue' => 'required|string',
            'estimated_cost' => 'nullable|numeric',
            'deposit_amount' => 'nullable|numeric',
            'priority' => 'required|string',
        ]);

        $validated['repair_number'] = 'REP-' . strtoupper(Str::random(6));
        $validated['status'] = RepairStatus::PENDING;

        $repair = Repair::create($validated);

        return response()->json($repair);
    }

    public function show(Repair $repair)
    {
        return response()->json($repair->load(['client', 'technician', 'repairParts.sparePart', 'payments']));
    }

    public function update(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'status' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'final_cost' => 'nullable|numeric',
            'technician_id' => 'nullable|exists:technicians,id',
            'internal_notes' => 'nullable|string',
            'delivered_at' => 'nullable|date',
        ]);

        $repair->update($validated);

        return response()->json($repair);
    }

    public function destroy(Repair $repair)
    {
        $repair->delete();
        return response()->json(['message' => 'Reparación eliminada.']);
    }
}

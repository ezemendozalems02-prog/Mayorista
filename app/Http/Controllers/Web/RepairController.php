<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $repairsQuery = Repair::where('organization_id', $orgId);

        $stats = [
            'pending' => (clone $repairsQuery)->where('status', \App\Enums\RepairStatus::PENDING)->count(),
            'in_progress' => (clone $repairsQuery)->where('status', \App\Enums\RepairStatus::IN_PROGRESS)->count(),
            'ready' => (clone $repairsQuery)->where('status', \App\Enums\RepairStatus::READY)->count(),
            'delivered_today' => (clone $repairsQuery)->where('status', \App\Enums\RepairStatus::DELIVERED)->whereDate('delivered_at', now())->count(),
        ];

        $repairs = Repair::where('organization_id', $orgId)
            ->with(['client', 'technician'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('repair_number', 'like', "%{$search}%")
                        ->orWhere('device_model', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($c) use ($search) {
                            $c->where('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('repairs.index', compact('repairs', 'stats'));
    }

    public function create()
    {
        $orgId = auth()->user()->organization_id;
        $clients = \App\Models\Client::where('organization_id', $orgId)->orderBy('full_name')->get();
        $technicians = \App\Models\Technician::where('organization_id', $orgId)->where('is_active', true)->orderBy('full_name')->get();

        return view('repairs.create', compact('clients', 'technicians'));
    }

    public function store(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where('organization_id', $orgId)],
            'technician_id' => ['nullable', Rule::exists('technicians', 'id')->where('organization_id', $orgId)],
            'device_brand' => 'required|string',
            'device_model' => 'required|string',
            'imei' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'priority' => 'required|string',
            'reported_issue' => 'required|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'internal_notes' => 'nullable|string',
        ]);

        $validated['organization_id'] = $orgId;
        $validated['repair_number'] = 'REP-' . strtoupper(\Illuminate\Support\Str::random(6));
        $validated['status'] = \App\Enums\RepairStatus::PENDING;
        $validated['received_at'] = now();

        \App\Models\Repair::create($validated);

        return redirect()->route('repair.index')->with('success', 'Orden de reparación creada.');
    }

    public function edit(\App\Models\Repair $repair)
    {
        $orgId = auth()->user()->organization_id;
        $technicians = \App\Models\Technician::where('organization_id', $orgId)->where('is_active', true)->orderBy('full_name')->get();
        return view('repairs.edit', compact('repair', 'technicians'));
    }

    public function update(Request $request, \App\Models\Repair $repair)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'diagnosis' => 'nullable|string',
            'final_cost' => 'nullable|numeric|min:0',
            'technician_id' => 'nullable|exists:technicians,id',
            'internal_notes' => 'nullable|string',
            'warranty_days' => 'nullable|integer|min:0',
        ]);

        if ($validated['status'] === \App\Enums\RepairStatus::DELIVERED->value && !$repair->delivered_at) {
            $validated['delivered_at'] = now();
        }

        $oldStatus = $repair->status;
        $repair->update($validated);

        if ($validated['status'] === \App\Enums\RepairStatus::READY->value && $oldStatus !== \App\Enums\RepairStatus::READY) {
            // Notify Owners
            $owners = \App\Models\User::where('organization_id', $repair->organization_id)
                ->where('role', \App\Enums\UserRole::OWNER)
                ->get();
            
            foreach ($owners as $owner) {
                $owner->notify(new \App\Notifications\RepairCompletedNotification($repair));
            }
        }

        return redirect()->route('repair.index')->with('success', 'Orden actualizada.');
    }

    public function show(Repair $repair)
    {
        $repair->load(['client', 'technician', 'payments']);
        return view('repairs.show', compact('repair'));
    }
}

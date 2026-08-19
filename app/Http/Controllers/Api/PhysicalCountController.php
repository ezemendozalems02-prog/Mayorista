<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhysicalCountResource;
use App\Models\Category;
use App\Models\PhysicalCount;
use App\Services\PhysicalCountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PhysicalCountController extends Controller
{
    public function __construct(private PhysicalCountService $service)
    {
    }

    public function index(Request $request)
    {
        $counts = PhysicalCount::query()
            ->with('category')
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return PhysicalCountResource::collection($counts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('organization_id', Auth::user()->organization_id)],
            'notes' => 'nullable|string|max:1000',
        ]);

        $category = isset($validated['category_id']) ? Category::find($validated['category_id']) : null;

        $count = $this->service->start($category, Auth::user(), $validated['notes'] ?? null);

        return new PhysicalCountResource($count->load(['category', 'items.product']));
    }

    public function show(PhysicalCount $physicalCount)
    {
        $physicalCount->load(['category', 'creator', 'items.product']);

        return new PhysicalCountResource($physicalCount);
    }

    public function saveCounts(Request $request, PhysicalCount $physicalCount)
    {
        $validated = $request->validate([
            'counts' => 'required|array',
            'counts.*' => 'nullable|integer|min:0',
        ]);

        try {
            $this->service->saveCounts($physicalCount, $validated['counts']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new PhysicalCountResource($physicalCount->load(['category', 'items.product']));
    }

    public function finalize(PhysicalCount $physicalCount)
    {
        try {
            $result = $this->service->finalize($physicalCount);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'count' => new PhysicalCountResource($result['count']->load(['category', 'items.product'])),
            'adjustments' => $result['adjustments'],
        ]);
    }

    public function cancel(PhysicalCount $physicalCount)
    {
        try {
            $this->service->cancel($physicalCount);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new PhysicalCountResource($physicalCount->fresh());
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
            ->paginate(15)
            ->withQueryString();

        return view('physical-counts.index', compact('counts'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('physical-counts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('organization_id', Auth::user()->organization_id)],
            'notes' => 'nullable|string|max:1000',
        ]);

        $category = isset($validated['category_id']) ? Category::find($validated['category_id']) : null;

        $count = $this->service->start($category, Auth::user(), $validated['notes'] ?? null);

        return redirect()->route('physical-count.show', $count)->with('success', "Conteo {$count->code} iniciado con {$count->items()->count()} productos.");
    }

    public function show(PhysicalCount $physicalCount)
    {
        $physicalCount->load(['category', 'creator', 'items.product']);

        return view('physical-counts.show', compact('physicalCount'));
    }

    public function save(Request $request, PhysicalCount $physicalCount)
    {
        $validated = $request->validate([
            'counts' => 'nullable|array',
            'counts.*' => 'nullable|integer|min:0',
        ]);

        try {
            $this->service->saveCounts($physicalCount, $validated['counts'] ?? []);
        } catch (\RuntimeException $e) {
            return redirect()->route('physical-count.show', $physicalCount)->with('error', $e->getMessage());
        }

        return redirect()->route('physical-count.show', $physicalCount)->with('success', 'Cantidades guardadas correctamente.');
    }

    public function finalize(PhysicalCount $physicalCount)
    {
        try {
            $result = $this->service->finalize($physicalCount);
        } catch (\RuntimeException $e) {
            return redirect()->route('physical-count.show', $physicalCount)->with('error', $e->getMessage());
        }

        $adjustedCount = count($result['adjustments']);
        $message = $adjustedCount > 0
            ? "Conteo finalizado: se generaron {$adjustedCount} ajustes de stock."
            : 'Conteo finalizado: no hubo diferencias.';

        return redirect()->route('physical-count.show', $physicalCount)->with('success', $message);
    }

    public function cancel(PhysicalCount $physicalCount)
    {
        try {
            $this->service->cancel($physicalCount);
        } catch (\RuntimeException $e) {
            return redirect()->route('physical-count.show', $physicalCount)->with('error', $e->getMessage());
        }

        return redirect()->route('physical-count.index')->with('success', "Conteo {$physicalCount->code} cancelado.");
    }
}

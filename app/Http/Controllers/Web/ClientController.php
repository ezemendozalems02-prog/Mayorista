<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\PriceList;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $clients = Client::where('organization_id', $orgId)
            ->withCount('sales')
            ->withSum('sales', 'total')
            ->when($request->search, function ($query, $search) {
                // ilike (no like): Postgres es case-sensitive por defecto, a diferencia de MySQL.
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'ilike', "%{$search}%")
                        ->orWhere('business_name', 'ilike', "%{$search}%")
                        ->orWhere('phone', 'ilike', "%{$search}%")
                        ->orWhere('document_number', 'ilike', "%{$search}%")
                        ->orWhere('cuit', 'ilike', "%{$search}%");
                });
            })
            ->when($request->filled('client_type'), fn ($q) => $q->where('client_type', $request->client_type))
            ->when($request->filter === 'best', function ($q) {
                $q->orderBy('sales_count', 'desc');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $priceLists = PriceList::where('is_active', true)->orderBy('name')->get();

        return view('clients.create', compact('priceLists'));
    }

    public function store(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'client_type' => ['required', new \Illuminate\Validation\Rules\Enum(\App\Enums\ClientType::class)],
            'business_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->where('organization_id', $orgId),
            ],
            'document_number' => 'nullable|string|max:20',
            'cuit' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'price_list_id' => ['nullable', Rule::exists('price_lists', 'id')->where('organization_id', $orgId)],
            'notes' => 'nullable|string',
        ]);

        Client::create($validated);

        return redirect()->route('client.index')->with('success', 'Cliente registrado correctamente.');
    }

    public function quickStore(Request $request): JsonResponse
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $existing = Client::where('organization_id', $orgId)
            ->where('email', $validated['email'])
            ->first();

        if ($existing) {
            return response()->json([
                'id' => $existing->id,
                'full_name' => $existing->full_name,
                'email' => $existing->email,
                'phone' => $existing->phone,
            ], 200);
        }

        $client = Client::create([
            'organization_id' => $orgId,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        return response()->json([
            'id' => $client->id,
            'full_name' => $client->full_name,
            'email' => $client->email,
            'phone' => $client->phone,
        ], 201);
    }

    public function show(Client $client)
    {
        $client->load(['sales.items.inventoryItem']);
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $priceLists = PriceList::where('is_active', true)->orderBy('name')->get();

        return view('clients.edit', compact('client', 'priceLists'));
    }

    public function update(Request $request, Client $client)
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'client_type' => ['required', new \Illuminate\Validation\Rules\Enum(\App\Enums\ClientType::class)],
            'business_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients', 'email')
                    ->where('organization_id', $orgId)
                    ->ignore($client->id),
            ],
            'document_number' => 'nullable|string|max:20',
            'cuit' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'price_list_id' => ['nullable', Rule::exists('price_lists', 'id')->where('organization_id', $orgId)],
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('client.index')->with('success', 'Datos de cliente actualizados.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('client.index')->with('success', 'Cliente eliminado.');
    }
}

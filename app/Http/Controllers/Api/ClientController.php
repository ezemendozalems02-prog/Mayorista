<?php

namespace App\Http\Controllers\Api;

use App\Enums\ClientType;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
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
            ->paginate($request->per_page ?? 15);

        return ClientResource::collection($clients);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'client_type' => ['required', new Enum(ClientType::class)],
            'business_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('clients')->where('organization_id', Auth::user()->organization_id),
            ],
            'document_number' => 'nullable|string|max:20',
            'cuit' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'price_list_id' => ['nullable', Rule::exists('price_lists', 'id')->where('organization_id', Auth::user()->organization_id)],
            'notes' => 'nullable|string',
        ]);

        $client = Client::create($validated);

        return new ClientResource($client);
    }

    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'client_type' => ['required', new Enum(ClientType::class)],
            'business_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('clients')->where('organization_id', Auth::user()->organization_id)->ignore($client->id),
            ],
            'document_number' => 'nullable|string|max:20',
            'cuit' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'price_list_id' => ['nullable', Rule::exists('price_lists', 'id')->where('organization_id', Auth::user()->organization_id)],
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return new ClientResource($client);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(['message' => 'Cliente eliminado correctamente.']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->when($request->search, function ($query, $search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%");
            })
            ->paginate($request->per_page ?? 15);

        return ClientResource::collection($clients);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'document_number' => 'nullable|string|max:20',
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
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'document_number' => 'nullable|string|max:20',
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

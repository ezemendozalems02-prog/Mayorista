<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountMovementResource;
use App\Models\Client;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class AccountController extends Controller
{
    public function __construct(private AccountService $accountService)
    {
    }

    /**
     * GET /clients/{client}/account-movements — historial paginado + saldo actual.
     */
    public function movements(Request $request, Client $client)
    {
        $movements = $client->accountMovements()
            ->with('user')
            ->latest('created_at')
            ->paginate($request->per_page ?? 20);

        return AccountMovementResource::collection($movements)->additional([
            'balance' => $client->current_balance,
        ]);
    }

    /**
     * POST /clients/{client}/account-payments — registra un pago que reduce la deuda.
     */
    public function storePayment(Request $request, Client $client)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => ['required', new Enum(PaymentMethod::class)],
            'notes' => 'nullable|string|max:1000',
        ]);

        $movement = $this->accountService->registerPayment(
            client: $client,
            amount: (float) $validated['amount'],
            method: PaymentMethod::from($validated['method']),
            notes: $validated['notes'] ?? null,
        );

        return new AccountMovementResource($movement->load('user'));
    }
}

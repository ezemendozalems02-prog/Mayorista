<?php

namespace App\Http\Controllers\Web;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class AccountController extends Controller
{
    public function __construct(private AccountService $accountService)
    {
    }

    public function show(Client $client)
    {
        $movements = $client->accountMovements()
            ->with('user')
            ->latest('created_at')
            ->paginate(20);

        return view('clients.account', compact('client', 'movements'));
    }

    public function storePayment(Request $request, Client $client)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => ['required', new Enum(PaymentMethod::class)],
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->accountService->registerPayment(
            client: $client,
            amount: (float) $validated['amount'],
            method: PaymentMethod::from($validated['method']),
            notes: $validated['notes'] ?? null,
        );

        return redirect()->route('client.account', $client)->with('success', 'Pago registrado correctamente.');
    }
}

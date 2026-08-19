<?php

namespace App\Services;

use App\Enums\AccountMovementType;
use App\Enums\PaymentMethod;
use App\Exceptions\CreditLimitExceededException;
use App\Models\AccountMovement;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Unico punto de entrada para tocar la cuenta corriente de un cliente
 * (Fase 13). Mismo principio que StockService (Fase 6): todo cambio se
 * registra como un AccountMovement (ledger append-only) y esta clase es la
 * unica que hay que usar para generarlos.
 */
class AccountService
{
    /**
     * Registra un movimiento con signo: positivo = aumenta la deuda,
     * negativo = la reduce. Si el resultado supera credit_limit (cuando el
     * cliente tiene uno configurado), rechaza sin registrar nada.
     */
    public function recordMovement(
        Client $client,
        float $amount,
        AccountMovementType $type,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $userId = null,
    ): AccountMovement {
        if ($amount == 0) {
            throw new \InvalidArgumentException('amount no puede ser 0.');
        }

        return DB::transaction(function () use ($client, $amount, $type, $notes, $referenceType, $referenceId, $userId) {
            if ($amount > 0 && $client->credit_limit !== null && (float) $client->credit_limit > 0) {
                $projected = $this->balanceFor($client) + $amount;
                if ($projected > (float) $client->credit_limit) {
                    throw new CreditLimitExceededException(
                        "El cliente {$client->display_name} superaría su límite de crédito ".
                        "(límite: \${$client->credit_limit}, saldo proyectado: \$" . number_format($projected, 2) . ')'
                    );
                }
            }

            return AccountMovement::create([
                'organization_id' => $client->organization_id,
                'client_id' => $client->id,
                'type' => $type,
                'amount' => number_format($amount, 2, '.', ''),
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'user_id' => $userId ?? Auth::id(),
            ]);
        });
    }

    /**
     * Saldo actual: positivo = el cliente debe, negativo = tiene a favor.
     */
    public function balanceFor(Client $client): float
    {
        return (float) AccountMovement::where('client_id', $client->id)->sum('amount');
    }

    /**
     * Registra que el cliente pago (reduce la deuda) y ademas deja
     * constancia en payments (dinero efectivamente recibido), igual que
     * cualquier otro cobro del sistema.
     */
    public function registerPayment(
        Client $client,
        float $amount,
        PaymentMethod $method,
        ?string $notes = null,
        ?int $userId = null,
    ): AccountMovement {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('El monto del pago debe ser mayor a 0.');
        }

        return DB::transaction(function () use ($client, $amount, $method, $notes, $userId) {
            $movement = $this->recordMovement(
                client: $client,
                amount: -$amount,
                type: AccountMovementType::PAYMENT,
                notes: $notes,
                userId: $userId,
            );

            Payment::create([
                'organization_id' => $client->organization_id,
                'client_id' => $client->id,
                'type' => 'income',
                'method' => $method,
                'currency' => 'ARS',
                'amount' => number_format($amount, 2, '.', ''),
                'notes' => $notes ?? 'Pago de cuenta corriente',
                'paid_at' => now(),
            ]);

            return $movement;
        });
    }
}

<?php

namespace App\Services;

use App\Enums\CashMovementType;
use App\Enums\CashSessionStatus;
use App\Exceptions\CashSessionException;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Unico punto de entrada para tocar la caja (Fase 14). Mismo principio que
 * StockService (Fase 6) y AccountService (Fase 13): todo movimiento se
 * registra como un CashMovement (ledger append-only) y esta clase es la
 * unica que hay que usar para generarlos.
 *
 * A diferencia de la cuenta corriente, la caja es completamente OPCIONAL:
 * una venta o un cobro nunca fallan por no tener una caja abierta. Ver
 * registerCashInflowIfOpen().
 */
class CashService
{
    /**
     * Abre un turno de caja con un fondo inicial. Solo puede haber una caja
     * abierta por organizacion a la vez.
     *
     * @throws CashSessionException
     */
    public function openSession(int $organizationId, User $user, float $openingAmount, ?string $notes = null): CashSession
    {
        if ($openingAmount < 0) {
            throw new \InvalidArgumentException('El fondo inicial no puede ser negativo.');
        }

        if ($this->currentSession($organizationId)) {
            throw new CashSessionException('Ya hay una caja abierta. Cerrala antes de abrir una nueva.');
        }

        return DB::transaction(function () use ($organizationId, $user, $openingAmount, $notes) {
            $session = CashSession::create([
                'organization_id' => $organizationId,
                'status' => CashSessionStatus::OPEN,
                'opening_amount' => number_format($openingAmount, 2, '.', ''),
                'opened_by' => $user->id,
                'notes' => $notes,
                'opened_at' => now(),
            ]);

            CashMovement::create([
                'organization_id' => $organizationId,
                'cash_session_id' => $session->id,
                'type' => CashMovementType::OPENING,
                'amount' => number_format($openingAmount, 2, '.', ''),
                'reference_type' => 'cash_session',
                'reference_id' => $session->id,
                'notes' => 'Apertura de caja',
                'user_id' => $user->id,
            ]);

            return $session;
        });
    }

    /**
     * La caja abierta de la organizacion, o null si no hay ninguna.
     */
    public function currentSession(int $organizationId): ?CashSession
    {
        return CashSession::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('status', CashSessionStatus::OPEN)
            ->latest('opened_at')
            ->first();
    }

    /**
     * Saldo esperado en el cajon: SUM de todos los movimientos de la caja
     * (incluye el fondo inicial, que tambien es un CashMovement de tipo
     * OPENING). No se cachea -- volumen bajo por turno, igual razonamiento
     * que AccountService::balanceFor.
     */
    public function balanceFor(CashSession $session): float
    {
        return (float) CashMovement::withoutGlobalScopes()
            ->where('cash_session_id', $session->id)
            ->sum('amount');
    }

    /**
     * Registra un movimiento con signo: positivo = entra plata al cajon,
     * negativo = sale. Rechaza si la caja ya esta cerrada.
     *
     * @throws CashSessionException
     */
    public function recordMovement(
        CashSession $session,
        float $amount,
        CashMovementType $type,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $userId = null,
    ): CashMovement {
        if ($amount == 0) {
            throw new \InvalidArgumentException('amount no puede ser 0.');
        }

        if (! $session->isOpen()) {
            throw new CashSessionException('La caja está cerrada, no se pueden registrar movimientos.');
        }

        return CashMovement::create([
            'organization_id' => $session->organization_id,
            'cash_session_id' => $session->id,
            'type' => $type,
            'amount' => number_format($amount, 2, '.', ''),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'user_id' => $userId ?? Auth::id(),
        ]);
    }

    public function registerIncome(CashSession $session, float $amount, ?string $notes = null, ?int $userId = null): CashMovement
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('El monto del ingreso debe ser mayor a 0.');
        }

        return $this->recordMovement($session, $amount, CashMovementType::INCOME, $notes, userId: $userId);
    }

    public function registerExpense(CashSession $session, float $amount, ?string $notes = null, ?int $userId = null): CashMovement
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('El monto del egreso debe ser mayor a 0.');
        }

        return $this->recordMovement($session, -$amount, CashMovementType::EXPENSE, $notes, userId: $userId);
    }

    /**
     * Best-effort: si la organizacion tiene una caja abierta, registra el
     * ingreso ahi. Si no hay ninguna abierta, no hace nada -- la caja es
     * opcional y nunca debe bloquear una venta o un cobro. Usado por
     * SaleService (venta en efectivo) y AccountService (cobro de cuenta
     * corriente en efectivo).
     */
    public function registerCashInflowIfOpen(
        int $organizationId,
        float $amount,
        CashMovementType $type,
        string $referenceType,
        int $referenceId,
        ?string $notes = null,
        ?int $userId = null,
    ): ?CashMovement {
        $session = $this->currentSession($organizationId);

        if (! $session) {
            return null;
        }

        return $this->recordMovement($session, $amount, $type, $notes, $referenceType, $referenceId, $userId);
    }

    /**
     * Cierra el turno: compara lo contado fisicamente (arqueo) contra lo
     * esperado segun el ledger y guarda la diferencia.
     *
     * @throws CashSessionException
     */
    public function closeSession(CashSession $session, float $countedAmount, ?string $notes, User $user): CashSession
    {
        if (! $session->isOpen()) {
            throw new CashSessionException('Esta caja ya está cerrada.');
        }

        return DB::transaction(function () use ($session, $countedAmount, $notes, $user) {
            $expected = $this->balanceFor($session);
            $difference = $countedAmount - $expected;

            $session->update([
                'status' => CashSessionStatus::CLOSED,
                'closing_amount' => number_format($countedAmount, 2, '.', ''),
                'expected_amount' => number_format($expected, 2, '.', ''),
                'difference' => number_format($difference, 2, '.', ''),
                'closed_by' => $user->id,
                'closed_at' => now(),
                'notes' => $notes ? trim(($session->notes ? $session->notes . ' | ' : '') . $notes) : $session->notes,
            ]);

            return $session->fresh();
        });
    }
}

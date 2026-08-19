<?php

namespace App\Http\Controllers\Api;

use App\Enums\CashMovementType;
use App\Exceptions\CashSessionException;
use App\Http\Controllers\Controller;
use App\Http\Resources\CashSessionResource;
use App\Models\CashSession;
use App\Services\CashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class CashSessionController extends Controller
{
    public function __construct(private CashService $cashService)
    {
    }

    public function index(Request $request)
    {
        $sessions = CashSession::with(['openedBy', 'closedBy'])
            ->latest('opened_at')
            ->paginate($request->per_page ?? 15);

        return CashSessionResource::collection($sessions);
    }

    /**
     * GET /cash-sessions/current — la caja abierta de la organizacion, o null.
     */
    public function current(Request $request)
    {
        $session = $this->cashService->currentSession(Auth::user()->organization_id);

        if (! $session) {
            return response()->json(['data' => null]);
        }

        return new CashSessionResource($session->load(['openedBy', 'movements.user']));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $session = $this->cashService->openSession(
                organizationId: Auth::user()->organization_id,
                user: $request->user(),
                openingAmount: (float) $validated['opening_amount'],
                notes: $validated['notes'] ?? null,
            );
        } catch (CashSessionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new CashSessionResource($session->load('openedBy'));
    }

    public function show(CashSession $cashSession)
    {
        return new CashSessionResource($cashSession->load(['openedBy', 'closedBy', 'movements.user']));
    }

    public function storeMovement(Request $request, CashSession $cashSession)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => ['required', new Enum(CashMovementType::class)],
            'notes' => 'nullable|string|max:1000',
        ]);

        $type = CashMovementType::from($validated['type']);
        if (! in_array($type, [CashMovementType::INCOME, CashMovementType::EXPENSE], true)) {
            return response()->json(['message' => 'Solo se pueden cargar movimientos manuales de tipo income o expense.'], 422);
        }

        try {
            $movement = $type === CashMovementType::INCOME
                ? $this->cashService->registerIncome($cashSession, (float) $validated['amount'], $validated['notes'] ?? null)
                : $this->cashService->registerExpense($cashSession, (float) $validated['amount'], $validated['notes'] ?? null);
        } catch (CashSessionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new \App\Http\Resources\CashMovementResource($movement->load('user'));
    }

    public function close(Request $request, CashSession $cashSession)
    {
        $validated = $request->validate([
            'counted_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $session = $this->cashService->closeSession(
                session: $cashSession,
                countedAmount: (float) $validated['counted_amount'],
                notes: $validated['notes'] ?? null,
                user: $request->user(),
            );
        } catch (CashSessionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new CashSessionResource($session->load(['openedBy', 'closedBy', 'movements.user']));
    }
}

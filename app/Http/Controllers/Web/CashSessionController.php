<?php

namespace App\Http\Controllers\Web;

use App\Enums\CashMovementType;
use App\Exceptions\CashSessionException;
use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Services\CashService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class CashSessionController extends Controller
{
    public function __construct(private CashService $cashService)
    {
    }

    /**
     * Pagina principal de caja: si hay un turno abierto lo muestra con sus
     * movimientos y el formulario de cierre; si no, muestra el formulario
     * para abrir uno nuevo.
     */
    public function index(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $session = $this->cashService->currentSession($orgId);

        if ($session) {
            $session->load(['openedBy', 'movements' => fn ($q) => $q->with('user')->latest('created_at')]);
            $balance = $this->cashService->balanceFor($session);

            return view('cash-sessions.index', compact('session', 'balance'));
        }

        return view('cash-sessions.index', ['session' => null, 'balance' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->cashService->openSession(
                organizationId: auth()->user()->organization_id,
                user: $request->user(),
                openingAmount: (float) $validated['opening_amount'],
                notes: $validated['notes'] ?? null,
            );
        } catch (CashSessionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('cash-session.index')->with('success', 'Caja abierta correctamente.');
    }

    public function storeMovement(Request $request)
    {
        $orgId = auth()->user()->organization_id;
        $session = $this->cashService->currentSession($orgId);

        if (! $session) {
            return redirect()->route('cash-session.index')->with('error', 'No hay ninguna caja abierta.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => ['required', new Enum(CashMovementType::class)],
            'notes' => 'nullable|string|max:1000',
        ]);

        $type = CashMovementType::from($validated['type']);
        if (! in_array($type, [CashMovementType::INCOME, CashMovementType::EXPENSE], true)) {
            return back()->withInput()->with('error', 'Tipo de movimiento inválido.');
        }

        try {
            if ($type === CashMovementType::INCOME) {
                $this->cashService->registerIncome($session, (float) $validated['amount'], $validated['notes'] ?? null);
            } else {
                $this->cashService->registerExpense($session, (float) $validated['amount'], $validated['notes'] ?? null);
            }
        } catch (CashSessionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('cash-session.index')->with('success', 'Movimiento registrado correctamente.');
    }

    public function close(Request $request)
    {
        $orgId = auth()->user()->organization_id;
        $session = $this->cashService->currentSession($orgId);

        if (! $session) {
            return redirect()->route('cash-session.index')->with('error', 'No hay ninguna caja abierta.');
        }

        $validated = $request->validate([
            'counted_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $session = $this->cashService->closeSession(
                session: $session,
                countedAmount: (float) $validated['counted_amount'],
                notes: $validated['notes'] ?? null,
                user: $request->user(),
            );
        } catch (CashSessionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('cash-session.show', $session)->with('success', 'Caja cerrada correctamente.');
    }

    public function history(Request $request)
    {
        $sessions = CashSession::with(['openedBy', 'closedBy'])
            ->latest('opened_at')
            ->paginate(15)
            ->withQueryString();

        return view('cash-sessions.history', compact('sessions'));
    }

    public function show(CashSession $cashSession)
    {
        $cashSession->load(['openedBy', 'closedBy', 'movements' => fn ($q) => $q->with('user')->latest('created_at')]);
        $balance = $this->cashService->balanceFor($cashSession);

        return view('cash-sessions.show', ['session' => $cashSession, 'balance' => $balance]);
    }
}

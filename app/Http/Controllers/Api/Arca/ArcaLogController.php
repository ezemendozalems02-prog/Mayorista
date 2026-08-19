<?php

namespace App\Http\Controllers\Api\Arca;

use App\Http\Controllers\Controller;
use App\Models\ArcaLog;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArcaLogController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/arca/logs
     *
     * Paginated ARCA interaction log for the authenticated organization.
     * Supports filters: endpoint, status, date_from, date_to, invoice_id.
     */
    public function index(Request $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;
        $perPage        = min((int) $request->query('per_page', 20), 100);

        $query = ArcaLog::where('organization_id', $organizationId)
            ->with('invoice:id,tipo_comprobante,punto_venta,numero_comprobante,estado')
            ->latest();

        if ($endpoint = $request->query('endpoint')) {
            $query->where('endpoint', 'like', "%{$endpoint}%");
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($invoiceId = $request->query('invoice_id')) {
            $query->where('invoice_id', (int) $invoiceId);
        }

        $logs = $query->paginate($perPage);

        return $this->success($logs);
    }

    /**
     * GET /api/arca/logs/{log}
     *
     * Full detail for a single ARCA log entry.
     * Returns request/response payloads for technical diagnostics.
     * Token and sign are masked at write time by ArcaWsfeService.
     */
    public function show(Request $request, int $log): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        $arcaLog = ArcaLog::where('organization_id', $organizationId)
            ->where('id', $log)
            ->with('invoice:id,tipo_comprobante,punto_venta,numero_comprobante,estado')
            ->first();

        if (! $arcaLog) {
            return $this->notFound('Log no encontrado.');
        }

        return $this->success($arcaLog);
    }
}

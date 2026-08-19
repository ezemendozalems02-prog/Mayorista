<?php

namespace App\Http\Controllers\Api\Arca;

use App\Http\Controllers\Controller;
use App\Http\Requests\Arca\StoreInvoiceRequest;
use App\Services\Arca\InvoiceService;
use App\Services\Invoices\InvoicePdfService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly InvoiceService    $service,
        private readonly \App\Services\Arca\AfipSdkInvoiceService $afipSdkService,
        private readonly InvoicePdfService $pdfService,
    ) {}

    /**
     * POST /api/arca/invoices
     *
     * Create a new draft invoice for the authenticated user's organization.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        try {
            $invoice = $this->service->createDraftInvoice($organizationId, $request->validated());
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), [], 422);
        }

        return $this->success($invoice, 'Factura creada en estado PENDIENTE.', 201);
    }

    /**
     * POST /api/arca/invoices/{invoice}/authorize
     *
     * Authorize a draft invoice against ARCA WSFEv1 and retrieve a CAE.
     */
    public function authorize(Request $request, int $invoice): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        // "usar_integracion" can come from request or default to manual
        $motor = $request->input('usar_integracion');
        
        if (!$motor) {
            $setting = \App\Models\FiscalSetting::where('organization_id', $organizationId)->first();
            $motor = $setting ? $setting->motor_integracion : 'manual';
        }

        if ($motor === 'afip_sdk') {
            return $this->emitirAfipSdk($request, $invoice);
        }

        try {
            $result = $this->service->authorizeInvoice($organizationId, $invoice);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), [], 422);
        }

        $httpStatus = $result['success'] ? 200 : 422;

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $result['data'],
        ], $httpStatus);
    }

    /**
     * POST /api/arca/invoices/{invoice}/emitir-afip-sdk
     */
    public function emitirAfipSdk(Request $request, int $invoice): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        try {
            $result = $this->afipSdkService->authorizeInvoice($organizationId, $invoice);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), [], 422);
        }

        $httpStatus = $result['success'] ? 200 : 422;

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $result['data'],
        ], $httpStatus);
    }

    /**
     * POST /api/arca/invoices/{invoice}/generate-pdf
     *
     * Generate (or regenerate) the PDF for an authorized invoice.
     * Returns a download URL — does not stream the file itself.
     */
    public function generatePdf(Request $request, int $invoice): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        try {
            $data = $this->pdfService->generate($organizationId, $invoice);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), [], 422);
        }

        return $this->success($data, 'PDF generado exitosamente.');
    }

    /**
     * GET /api/arca/invoices/{invoice}/download-pdf
     *
     * Stream the PDF file for an authorized invoice.
     * Auto-generates the PDF on first request if it doesn't exist yet.
     */
    public function downloadPdf(Request $request, int $invoice): StreamedResponse|JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        try {
            return $this->pdfService->download($organizationId, $invoice);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    /**
     * GET /api/arca/invoices
     *
     * List invoices for the authenticated user's organization (paginated).
     */
    /**
     * GET /api/arca/invoices/{invoice}
     *
     * Fetch a single invoice with its items.
     */
    public function show(Request $request, int $invoice): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        $inv = \App\Models\Invoice::where('organization_id', $organizationId)
            ->where('id', $invoice)
            ->with('items')
            ->first();

        if (! $inv) {
            return $this->error('Factura no encontrada o no pertenece a su organización.', [], 404);
        }

        return $this->success($inv);
    }

    /**
     * GET /api/arca/invoices
     *
     * List invoices for the authenticated user's organization (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        $perPage = min((int) $request->query('per_page', 20), 100);
        $filters = $request->only(['status', 'type', 'date_from', 'date_to', 'client', 'number']);

        $invoices = $this->service->getInvoicesByOrganization($organizationId, $perPage, $filters);

        return $this->success($invoices);
    }
}

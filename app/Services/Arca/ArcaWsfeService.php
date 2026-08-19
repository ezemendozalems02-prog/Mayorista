<?php

namespace App\Services\Arca;

use App\Models\ArcaLog;
use App\Models\FiscalSetting;
use App\Models\Invoice;
use App\Support\ArcaDocumentTypes;
use App\Support\ArcaVoucherTypes;
use Carbon\Carbon;
use RuntimeException;
use SoapClient;
use SoapFault;
use Throwable;

/**
 * ArcaWsfeService — WSFEv1 (Web Service de Facturación Electrónica v1) client.
 *
 * Flow per authorization:
 *   1. Obtain valid token/sign from ArcaWsaaService (cached or fresh).
 *   2. Query FECompUltimoAutorizado to get the last used invoice number.
 *   3. Increment by 1 to produce the next number.
 *   4. Build and send a FECAESolicitar request.
 *   5. Parse the response: save CAE + expiry on approval, error details on rejection.
 *   6. Log every SOAP interaction — token/sign are NEVER written to logs.
 */
class ArcaWsfeService
{
    public function __construct(private readonly ArcaWsaaService $wsaa) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Build a SoapClient connected to the WSFEv1 WSDL for the given environment.
     *
     * @throws RuntimeException if the WSDL is unreachable
     */
    public function getWsfeClient(string $environment): SoapClient
    {
        $wsdlUrl = $environment === 'PRODUCTION'
            ? config('arca.wsfe.production_url')
            : config('arca.wsfe.testing_url');

        $timeout = config('arca.wsaa.timeout', 30);

        try {
            return new SoapClient($wsdlUrl, [
                'soap_version'       => SOAP_1_2,
                'exceptions'         => true,
                'trace'              => true,
                'cache_wsdl'         => WSDL_CACHE_NONE,
                'connection_timeout' => $timeout,
                'stream_context'     => stream_context_create([
                    'ssl'  => ['verify_peer' => true, 'verify_peer_name' => true],
                    'http' => ['timeout' => $timeout],
                ]),
            ]);
        } catch (SoapFault $e) {
            throw new RuntimeException(
                "No se pudo conectar al WSDL de WSFEv1 ({$environment}): {$e->getMessage()}",
                0,
                $e,
            );
        }
    }

    /**
     * Build the Auth array required by every WSFEv1 SOAP method.
     *
     * INTERNAL USE ONLY — never forward this array to API consumers.
     *
     * @return array{Token: string, Sign: string, Cuit: string}
     * @throws RuntimeException
     */
    public function getAuthData(int $organizationId): array
    {
        $fiscalSetting = FiscalSetting::where('organization_id', $organizationId)->firstOrFail();
        $tokenData     = $this->wsaa->getValidToken($organizationId);

        return [
            'Token' => $tokenData['token'],
            'Sign'  => $tokenData['sign'],
            'Cuit'  => preg_replace('/\D/', '', $fiscalSetting->cuit),
        ];
    }

    /**
     * Call FECompUltimoAutorizado and return the last authorized invoice number.
     *
     * Returns 0 if no invoice has been authorized yet for this point-of-sale /
     * voucher-type combination (ARCA returns 0 in that case as well).
     *
     * @throws RuntimeException on SOAP errors
     */
    public function getLastAuthorizedNumber(
        int    $organizationId,
        int    $pointOfSale,
        int    $voucherType,
        ?int   $invoiceId = null,
    ): int {
        $fiscalSetting = FiscalSetting::where('organization_id', $organizationId)->firstOrFail();
        $environment   = $fiscalSetting->ambiente;
        $authData      = $this->getAuthData($organizationId);

        $params = [
            'Auth'     => $authData,
            'PtoVta'   => $pointOfSale,
            'CbteTipo' => $voucherType,
        ];

        try {
            $client   = $this->getWsfeClient($environment);
            $response = $client->FECompUltimoAutorizado($params);
            $result   = $response->FECompUltimoAutorizadoResult ?? null;
            $cbteNro  = (int) ($result->CbteNro ?? 0);

            $this->log(
                $organizationId,
                $invoiceId,
                'wsfe/FECompUltimoAutorizado',
                $environment,
                $this->maskAuthInRequest($params),
                ['CbteNro' => $cbteNro],
                'OK',
            );

            return $cbteNro;

        } catch (SoapFault $e) {
            $this->log(
                $organizationId,
                $invoiceId,
                'wsfe/FECompUltimoAutorizado',
                $environment,
                $this->maskAuthInRequest($params),
                null,
                'ERROR',
                $e->getMessage(),
            );

            throw new RuntimeException(
                "Error SOAP en FECompUltimoAutorizado ({$environment}): {$e->getMessage()}",
                0,
                $e,
            );
        }
    }

    /**
     * Authorize an invoice against ARCA WSFEv1.
     *
     * Orchestrates the full flow: validate state → get auth → get last number →
     * build request → call FECAESolicitar → persist result → return clean response.
     *
     * Returns a structured result array — success or failure — never throws.
     *
     * @return array{success: bool, message: string, data: array}
     */
    public function authorizeInvoice(Invoice $invoice): array
    {
        // Guard: already authorized
        if ($invoice->isAuthorized()) {
            return [
                'success' => false,
                'message' => 'La factura ya está autorizada.',
                'data'    => ['invoice_id' => $invoice->id, 'estado' => $invoice->estado],
            ];
        }

        // Guard: wrong state
        if (! in_array($invoice->estado, ['PENDIENTE', 'ERROR'], true)) {
            return [
                'success' => false,
                'message' => "La factura no puede autorizarse en estado '{$invoice->estado}'.",
                'data'    => ['invoice_id' => $invoice->id, 'estado' => $invoice->estado],
            ];
        }

        $organizationId = $invoice->organization_id;

        $fiscalSetting = FiscalSetting::where('organization_id', $organizationId)->first();
        if (! $fiscalSetting) {
            return [
                'success' => false,
                'message' => 'No hay configuración fiscal registrada para esta organización.',
                'data'    => ['invoice_id' => $invoice->id, 'estado' => $invoice->estado],
            ];
        }

        $environment = $fiscalSetting->ambiente;

        try {
            $authData    = $this->getAuthData($organizationId);
            $voucherType = ArcaVoucherTypes::fromTipoComprobante($invoice->tipo_comprobante);
            $lastNumber  = $this->getLastAuthorizedNumber($organizationId, $invoice->punto_venta, $voucherType, $invoice->id);
            $nextNumber  = $lastNumber + 1;
            $request     = $this->buildFecaeRequest($invoice, $authData, $voucherType, $nextNumber);

            $client   = $this->getWsfeClient($environment);
            $response = $client->FECAESolicitar($request);
            $result   = $response->FECAESolicitarResult ?? null;

            return $this->handleFecaeResponse($invoice, $result, $nextNumber, $environment, $request);

        } catch (Throwable $e) {
            // Technical failure (SOAP, network, config, etc.) — update invoice to ERROR
            $invoice->update([
                'estado'        => 'ERROR',
                'error_message' => $this->truncate($e->getMessage()),
            ]);

            $this->log(
                $organizationId,
                $invoice->id,
                'wsfe/FECAESolicitar',
                $environment,
                null,
                null,
                'ERROR',
                $e->getMessage(),
            );

            return [
                'success' => false,
                'message' => 'Error técnico al comunicar con ARCA WSFEv1.',
                'data'    => [
                    'invoice_id'    => $invoice->id,
                    'estado'        => 'ERROR',
                    'error_message' => $this->truncate($e->getMessage()),
                ],
            ];
        }
    }

    /**
     * Build the FECAESolicitar request array for a single invoice.
     *
     * IVA rules:
     *   - Tipo C: ImpNeto = total, ImpIVA = 0, no Iva array.
     *   - Tipo A / B: ImpNeto = subtotal, ImpIVA = iva, include Iva array (Id=5 → 21%).
     */
    public function buildFecaeRequest(Invoice $invoice, array $authData, int $voucherType, int $nextNumber): array
    {
        $doc            = ArcaDocumentTypes::resolve($invoice->cliente_documento);
        $isTypeC        = $invoice->tipo_comprobante === 'C';

        $impNeto  = $isTypeC ? (float) $invoice->total    : (float) $invoice->subtotal;
        $impIva   = $isTypeC ? 0.0                        : (float) $invoice->iva;
        $impTotal = (float) $invoice->total;

        $detail = [
            'Concepto'   => 1,              // 1=Productos, 2=Servicios, 3=Productos y Servicios
            'DocTipo'    => $doc['tipo'],
            'DocNro'     => $doc['numero'],
            'CbteDesde'  => $nextNumber,
            'CbteHasta'  => $nextNumber,
            'CbteFch'    => now()->format('Ymd'),
            'ImpTotal'   => $impTotal,
            'ImpTotConc' => 0,
            'ImpNeto'    => $impNeto,
            'ImpOpEx'    => 0,
            'ImpIVA'     => $impIva,
            'ImpTrib'    => 0,
            'MonId'      => 'PES',
            'MonCotiz'   => 1,
        ];

        // ARCA requires the Iva breakdown only for A and B invoices with IVA > 0
        if (! $isTypeC && $impIva > 0) {
            $detail['Iva'] = [
                'AlicIva' => [
                    [
                        'Id'      => 5,         // 5 = alícuota 21%
                        'BaseImp' => $impNeto,
                        'Importe' => $impIva,
                    ],
                ],
            ];
        }

        return [
            'Auth'     => $authData,
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg'  => 1,
                    'PtoVta'   => $invoice->punto_venta,
                    'CbteTipo' => $voucherType,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => $detail,
                ],
            ],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Inspect the FECAESolicitar result and persist the outcome on the invoice.
     *
     * ARCA returns Resultado='A' for approved ('A'probado) and 'R' for rejected.
     */
    private function handleFecaeResponse(
        Invoice $invoice,
        mixed   $result,
        int     $nextNumber,
        string  $environment,
        array   $request,
    ): array {
        $organizationId = $invoice->organization_id;

        $detResp = $result->FeDetResp->FECAEDetResponse ?? null;
        $outcome = strtoupper(trim((string) ($detResp->Resultado ?? '')));
        $cae     = trim((string) ($detResp->CAE ?? ''));
        $caeVto  = trim((string) ($detResp->CAEFchVto ?? ''));

        $arcaErrors = $this->extractArcaErrors($result);

        $responsePayload = [
            'resultado' => $outcome,
            'cae'       => $cae ?: null,
            'cae_vto'   => $caeVto ?: null,
            'errors'    => $arcaErrors,
        ];

        if ($outcome === 'A') {
            $caeFecha = Carbon::createFromFormat('Ymd', $caeVto);

            $invoice->update([
                'estado'             => 'AUTORIZADO',
                'numero_comprobante' => $nextNumber,
                'cae'                => $cae,
                'cae_vencimiento'    => $caeFecha,
                'error_message'      => null,
                'arca_response'      => $responsePayload,
            ]);

            $this->log(
                $organizationId,
                $invoice->id,
                'wsfe/FECAESolicitar',
                $environment,
                $this->maskAuthInRequest($request),
                $responsePayload,
                'OK',
            );

            return [
                'success' => true,
                'message' => 'Factura autorizada exitosamente.',
                'data'    => [
                    'invoice_id'         => $invoice->id,
                    'estado'             => 'AUTORIZADO',
                    'tipo_comprobante'   => $invoice->tipo_comprobante,
                    'punto_venta'        => $invoice->punto_venta,
                    'numero_comprobante' => $nextNumber,
                    'cae'                => $cae,
                    'cae_vencimiento'    => $caeFecha->toDateString(),
                    'total'              => $invoice->total,
                ],
            ];
        }

        // Rejected by ARCA
        $errorMessage = implode(' | ', $arcaErrors)
            ?: 'ARCA rechazó la solicitud sin detalles adicionales.';

        $invoice->update([
            'estado'        => 'RECHAZADO',
            'error_message' => $this->truncate($errorMessage),
            'arca_response' => $responsePayload,
        ]);

        $this->log(
            $organizationId,
            $invoice->id,
            'wsfe/FECAESolicitar',
            $environment,
            $this->maskAuthInRequest($request),
            $responsePayload,
            'RECHAZADO',
            $errorMessage,
        );

        return [
            'success' => false,
            'message' => 'Factura rechazada por ARCA.',
            'data'    => [
                'invoice_id'    => $invoice->id,
                'estado'        => 'RECHAZADO',
                'error_message' => $this->truncate($errorMessage),
            ],
        ];
    }

    /**
     * Extract human-readable error strings from an ARCA FECAESolicitar result.
     *
     * ARCA can return errors at two levels:
     *   • FeDetResp.FECAEDetResponse.Observaciones.Obs — per-voucher observations
     *   • Errors.Err — header-level errors (usually auth / request format problems)
     *
     * PHP's SOAP client maps single-item arrays as objects, so we cast to (array)
     * before iterating to handle both cases transparently.
     */
    private function extractArcaErrors(mixed $result): array
    {
        $messages = [];

        $obs = $result->FeDetResp->FECAEDetResponse->Observaciones->Obs ?? null;
        if ($obs) {
            foreach ((array) $obs as $ob) {
                $messages[] = "Obs {$ob->Code}: {$ob->Msg}";
            }
        }

        $errs = $result->Errors->Err ?? null;
        if ($errs) {
            foreach ((array) $errs as $err) {
                $messages[] = "Err {$err->Code}: {$err->Msg}";
            }
        }

        return $messages;
    }

    /**
     * Replace token/sign values in a request array with '[REDACTED]' before logging.
     */
    private function maskAuthInRequest(array $request): array
    {
        if (isset($request['Auth'])) {
            $request['Auth'] = [
                'Token' => '[REDACTED]',
                'Sign'  => '[REDACTED]',
                'Cuit'  => $request['Auth']['Cuit'] ?? null,
            ];
        }

        return $request;
    }

    private function log(
        int     $organizationId,
        ?int    $invoiceId,
        string  $endpoint,
        string  $environment,
        ?array  $requestPayload,
        ?array  $responsePayload,
        string  $status,
        ?string $errorMessage = null,
    ): void {
        ArcaLog::create([
            'organization_id'  => $organizationId,
            'invoice_id'       => $invoiceId,
            'endpoint'         => $endpoint,
            'request_payload'  => array_merge($requestPayload ?? [], ['environment' => $environment]),
            'response_payload' => $responsePayload,
            'status'           => $status,
            'error_message'    => $errorMessage ? $this->truncate($errorMessage) : null,
        ]);
    }

    private function truncate(string $message, int $limit = 500): string
    {
        return strlen($message) > $limit
            ? substr($message, 0, $limit) . '…'
            : $message;
    }
}

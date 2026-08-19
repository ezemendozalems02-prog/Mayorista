<?php

namespace App\Services\Arca;

use Afip;
use App\Models\ArcaCertificate;
use App\Models\ArcaLog;
use App\Models\FiscalSetting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AfipSdkInvoiceService
{
    public function __construct() {}

    /**
     * Create a draft invoice.
     * This shares logic with the manual service, we could refactor later.
     */
    public function createDraftInvoice(int $organizationId, array $data): Invoice
    {
        $fiscalSetting = FiscalSetting::where('organization_id', $organizationId)->first();

        if (! $fiscalSetting) {
            throw new RuntimeException('La organización no tiene configuración fiscal. Configure los datos fiscales antes de emitir facturas.');
        }

        if (! $fiscalSetting->activo) {
            throw new RuntimeException('La configuración fiscal está inactiva. Actívela antes de emitir facturas.');
        }

        $totals = $this->calculateTotals($data['items'], $fiscalSetting->condicion_iva, $data['tipo_comprobante']);

        return DB::transaction(function () use ($organizationId, $data, $totals, $fiscalSetting) {
            $invoice = Invoice::create([
                'organization_id'       => $organizationId,
                'cliente_nombre'        => $data['cliente_nombre'],
                'cliente_documento'     => $data['cliente_documento'] ?? null,
                'cliente_condicion_iva' => $data['cliente_condicion_iva'] ?? null,
                'tipo_comprobante'      => $data['tipo_comprobante'],
                'punto_venta'           => $fiscalSetting->punto_venta,
                'subtotal'              => $totals['subtotal'],
                'iva'                   => $totals['iva'],
                'total'                 => $totals['total'],
                'estado'                => 'PENDIENTE',
            ]);

            foreach ($data['items'] as $item) {
                $itemTotal = round((float) $item['cantidad'] * (float) $item['precio_unitario'], 2);

                InvoiceItem::create([
                    'invoice_id'      => $invoice->id,
                    'descripcion'     => $item['descripcion'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'total'           => $itemTotal,
                ]);
            }

            return $invoice->load('items');
        });
    }

    public function authorizeInvoice(int $organizationId, int $invoiceId): array
    {
        $invoice = Invoice::where('organization_id', $organizationId)
            ->where('id', $invoiceId)
            ->first();

        if (! $invoice) {
            throw new RuntimeException('Factura no encontrada o no pertenece a su organización.');
        }

        if ($invoice->estado === 'AUTORIZADO') {
            throw new RuntimeException('La factura ya se encuentra autorizada.');
        }

        $fiscalSetting = FiscalSetting::where('organization_id', $organizationId)->first();
        if (! $fiscalSetting) {
            throw new RuntimeException('Configuración fiscal no encontrada.');
        }

        $certificate = ArcaCertificate::where('organization_id', $organizationId)->first();
        if (! $certificate || ! $certificate->certificate || ! $certificate->private_key) {
            throw new RuntimeException('Certificado o clave privada no configurados para esta organización.');
        }

        // Preparar temporales para Afip SDK
        $certPath = storage_path("app/private/afip_certs/cert_{$organizationId}.crt");
        $keyPath = storage_path("app/private/afip_certs/key_{$organizationId}.key");

        if (! file_exists(dirname($certPath))) {
            mkdir(dirname($certPath), 0755, true);
        }

        file_put_contents($certPath, $certificate->certificate);
        file_put_contents($keyPath, $certificate->private_key);

        $production = $fiscalSetting->ambiente === 'PRODUCTION';

        try {
            $afip = new Afip([
                'CUIT' => $fiscalSetting->cuit,
                'production' => $production,
                'cert' => $certPath,
                'key' => $keyPath,
                'res_folder' => storage_path("app/private/afip_certs/") // For tokens
            ]);

            $cbteTipo = $this->getAfipCbteTipo($invoice->tipo_comprobante);
            $puntoVenta = $invoice->punto_venta;

            $lastVoucher = $afip->ElectronicBilling->GetLastVoucher($puntoVenta, $cbteTipo);
            $nextVoucher = $lastVoucher + 1;

            $docTipo = $invoice->cliente_documento ? 80 : 99; // 80 = CUIT, 99 = Consumidor Final
            if (strlen((string) $invoice->cliente_documento) === 8) {
                $docTipo = 96; // DNI
            }

            $date = date('Ymd');
            
            $data = [
                'CantReg' 	=> 1, // Cantidad de comprobantes a registrar
                'PtoVta' 	=> $puntoVenta, // Punto de venta
                'CbteTipo' 	=> $cbteTipo, // Tipo de comprobante
                'Concepto' 	=> 1, // Concepto: 1 = Productos, 2 = Servicios, 3 = Productos y Servicios
                'DocTipo' 	=> $docTipo, // Tipo de documento del comprador
                'DocNro' 	=> $invoice->cliente_documento ?: 0, // Nro de documento del comprador
                'CbteDesde' => $nextVoucher, // Nro de comprobante desde
                'CbteHasta' => $nextVoucher, // Nro de comprobante hasta
                'CbteFch' 	=> intval($date), // Fecha del comprobante (YYYYMMDD)
                'ImpTotal' 	=> $invoice->total, // Importe total
                'ImpTotConc'=> 0, // Importe neto no gravado
                'ImpNeto' 	=> $invoice->subtotal, // Importe neto gravado
                'ImpOpEx' 	=> 0, // Importe exento
                'ImpIVA' 	=> $invoice->iva, // Importe IVA
                'ImpTrib' 	=> 0, // Importe total de tributos
                'MonId' 	=> 'PES', // Tipo de moneda (PES = Pesos)
                'MonCotiz' 	=> 1, // Cotización de la moneda (1 para PES)
            ];

            // If we have IVA, we need to pass the Iva array. If invoice is C or EXENTO we shouldn't pass IVA usually.
            if ($invoice->iva > 0) {
                $data['Iva'] = [
                    [
                        'Id' 		=> 5, // 5 = 21%
                        'BaseImp' 	=> $invoice->subtotal,
                        'Importe' 	=> $invoice->iva
                    ]
                ];
            }

            $requestPayload = $data;
            
            $res = $afip->ElectronicBilling->CreateNextVoucher($data);

            $invoice->update([
                'cae' => $res['CAE'],
                'cae_vencimiento' => $this->parseAfipDate($res['CAEFchVto']),
                'numero_comprobante' => $nextVoucher,
                'estado' => 'AUTORIZADO',
                'arca_response' => $res,
            ]);

            $this->logArca($organizationId, $invoice->id, 'CreateNextVoucher (Afip SDK)', $requestPayload, $res, 'SUCCESS', null);

            return [
                'success' => true,
                'message' => 'Conexión exitosa con ARCA mediante Afip SDK. Comprobante autorizado.',
                'data' => [
                    'cae' => $res['CAE'],
                    'vencimiento' => $this->parseAfipDate($res['CAEFchVto']),
                    'numero' => $nextVoucher
                ]
            ];
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Customize some common errors as requested
            if (strpos($errorMessage, 'cert') !== false || strpos($errorMessage, 'key') !== false) {
                $errorMessage = "El certificado no coincide con la clave privada o es inválido.";
            } elseif (strpos($errorMessage, 'autorizado') !== false) {
                $errorMessage = "El servicio Facturación Electrónica no está autorizado para este certificado.";
            } else {
                $errorMessage = "No se pudo autenticar con ARCA: " . $errorMessage;
            }

            $invoice->update([
                'estado' => 'ERROR',
                'error_message' => $errorMessage
            ]);

            $this->logArca($organizationId, $invoice->id, 'CreateNextVoucher (Afip SDK)', $data ?? [], [], 'ERROR', $errorMessage);

            throw new RuntimeException($errorMessage);
        } finally {
            // Clean up files securely
            if (file_exists($certPath)) unlink($certPath);
            if (file_exists($keyPath)) unlink($keyPath);
        }
    }

    private function getAfipCbteTipo(string $tipo): int
    {
        return match ($tipo) {
            'A' => 1,
            'B' => 6,
            'C' => 11,
            default => throw new RuntimeException("Tipo de comprobante desconocido: {$tipo}"),
        };
    }

    private function parseAfipDate(string $date): string
    {
        // 20260505 -> 2026-05-05
        return substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
    }

    private function logArca(int $organizationId, int $invoiceId, string $endpoint, array $req, array $res, string $status, ?string $error)
    {
        ArcaLog::create([
            'organization_id' => $organizationId,
            'invoice_id' => $invoiceId,
            'endpoint' => $endpoint,
            'request_payload' => $req,
            'response_payload' => $res,
            'status' => $status,
            'error_message' => $error,
        ]);
    }

    public function calculateTotals(array $items, string $condicionIva, string $tipoComprobante): array
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += (float) $item['cantidad'] * (float) $item['precio_unitario'];
        }

        $subtotal = round($subtotal, 2);

        $applyIva = $condicionIva === 'RESPONSABLE_INSCRIPTO' && $tipoComprobante === 'A';
        $iva = $applyIva ? round($subtotal * 0.21, 2) : 0.0;
        $total = round($subtotal + $iva, 2);

        return compact('subtotal', 'iva', 'total');
    }
}

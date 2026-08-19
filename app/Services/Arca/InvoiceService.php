<?php

namespace App\Services\Arca;

use App\Models\FiscalSetting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvoiceService
{
    public function __construct(private readonly ArcaWsfeService $wsfe) {}

    /**
     * Create a draft invoice with its items inside a transaction.
     * Totals are calculated from the items; the subtotal/iva breakdown
     * depends on the fiscal setting's condicion_iva.
     *
     * @throws RuntimeException when fiscal settings are missing
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

    /**
     * Authorize a pending invoice via ARCA WSFEv1.
     *
     * Verifies the invoice belongs to the given organization before delegating
     * to ArcaWsfeService — this is the multi-tenant ownership check.
     *
     * @return array{success: bool, message: string, data: array}
     * @throws RuntimeException if the invoice is not found or belongs to another org
     */
    public function authorizeInvoice(int $organizationId, int $invoiceId): array
    {
        $invoice = Invoice::where('organization_id', $organizationId)
            ->where('id', $invoiceId)
            ->first();

        if (! $invoice) {
            throw new RuntimeException('Factura no encontrada o no pertenece a su organización.');
        }

        return $this->wsfe->authorizeInvoice($invoice);
    }

    /**
     * Return paginated invoices for the given organization.
     *
     * Accepted filters: status, type, date_from, date_to, client, number.
     */
    public function getInvoicesByOrganization(int $organizationId, int $perPage = 20, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Invoice::where('organization_id', $organizationId)
            ->with('items')
            ->latest();

        if (! empty($filters['status'])) {
            $query->where('estado', strtoupper($filters['status']));
        }

        if (! empty($filters['type'])) {
            $query->where('tipo_comprobante', strtoupper($filters['type']));
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['client'])) {
            $query->where('cliente_nombre', 'like', '%' . $filters['client'] . '%');
        }

        if (isset($filters['number']) && $filters['number'] !== '') {
            $query->where('numero_comprobante', (int) $filters['number']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Calculate subtotal, IVA and total from a list of items.
     *
     * IVA (21%) is only applied on Tipo A comprobantes for Responsable Inscripto.
     * Monotributo and Exento always emit without discriminated IVA.
     */
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

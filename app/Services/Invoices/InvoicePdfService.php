<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * InvoicePdfService — generates and serves ARCA-compliant electronic invoice PDFs.
 *
 * Security model:
 *   - All public methods require an organizationId that must match the invoice's
 *     organization_id. Cross-tenant access silently returns "not found."
 *   - PDFs are stored in private storage (not web-accessible).
 *   - Downloads go through an authenticated controller route.
 *
 * Storage path: storage/app/private/invoices/{organizationId}/{invoiceId}.pdf
 */
class InvoicePdfService
{
    /**
     * Generate (or regenerate) the PDF for an authorized invoice.
     *
     * @return array{invoice_id: int, pdf_generated_at: string, download_url: string}
     * @throws RuntimeException on ownership or estado violations
     */
    public function generate(int $organizationId, int $invoiceId): array
    {
        $invoice = $this->findAndAuthorize($organizationId, $invoiceId);

        // Eagerly load all data the template needs in a single query
        $invoice->load(['items', 'organization.fiscalSetting']);
        $fiscalSetting = $invoice->organization?->fiscalSetting;

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice'       => $invoice,
            'fiscalSetting' => $fiscalSetting,
            'qrData'        => $this->generateArcaQrData($invoice),
        ])->setPaper('a4', 'portrait');

        $storagePath = $this->storagePath($organizationId, $invoiceId);

        // Ensure the directory exists (Storage::put creates it automatically)
        Storage::disk('local')->put($storagePath, $pdf->output());

        $invoice->update([
            'pdf_path'         => $storagePath,
            'pdf_generated_at' => now(),
        ]);

        return [
            'invoice_id'       => $invoice->id,
            'pdf_generated_at' => now()->toDateTimeString(),
            'download_url'     => "/api/arca/invoices/{$invoice->id}/download-pdf",
        ];
    }

    /**
     * Return a file download response for an authorized invoice's PDF.
     *
     * Auto-generates the PDF on first download if it doesn't exist yet.
     *
     * @throws RuntimeException on ownership or estado violations
     */
    public function download(int $organizationId, int $invoiceId): StreamedResponse
    {
        $invoice = $this->findAndAuthorize($organizationId, $invoiceId);

        $storagePath = $this->storagePath($organizationId, $invoiceId);

        // Auto-generate if missing (idempotent — safe to call multiple times)
        if (empty($invoice->pdf_path) || ! Storage::disk('local')->exists($storagePath)) {
            $this->generate($organizationId, $invoiceId);
        }

        $filename = sprintf(
            'factura-%s-%s-%s.pdf',
            $invoice->tipo_comprobante,
            str_pad($invoice->punto_venta, 4, '0', STR_PAD_LEFT),
            str_pad($invoice->numero_comprobante, 8, '0', STR_PAD_LEFT),
        );

        return Storage::disk('local')->download($storagePath, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Placeholder for ARCA QR data generation.
     *
     * ARCA requires a URL-encoded JSON QR with fiscal data. Full implementation
     * is Phase 5. Returns null so the template shows the placeholder block.
     *
     * @see https://www.afip.gob.ar/fe/documentos/ARCA-QR-formato.pdf
     */
    public function generateArcaQrData(Invoice $invoice): ?array
    {
        // TODO Phase 5: return structured QR data per ARCA specification
        return null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Find an invoice, verify it belongs to the given organization, and verify
     * it is in AUTORIZADO estado. Throws on any violation.
     */
    private function findAndAuthorize(int $organizationId, int $invoiceId): Invoice
    {
        $invoice = Invoice::where('organization_id', $organizationId)
            ->where('id', $invoiceId)
            ->first();

        if (! $invoice) {
            throw new RuntimeException('Factura no encontrada o no pertenece a su organización.');
        }

        if (! $invoice->isAuthorized()) {
            throw new RuntimeException('Solo se pueden generar PDFs para facturas con estado AUTORIZADO.');
        }

        return $invoice;
    }

    /** Canonical private storage path for an invoice PDF. */
    private function storagePath(int $organizationId, int $invoiceId): string
    {
        return "private/invoices/{$organizationId}/{$invoiceId}.pdf";
    }
}

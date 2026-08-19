<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ArcaCertificate;
use App\Models\ArcaLog;
use App\Models\FiscalSetting;
use App\Models\Invoice;
use App\Services\Arca\ArcaConnectionService;
use App\Services\Arca\FiscalSettingService;
use App\Services\Arca\InvoiceService;
use App\Services\Invoices\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ArcaController extends Controller
{
    public function __construct(
        private readonly FiscalSettingService  $fiscalService,
        private readonly InvoiceService        $invoiceService,
        private readonly InvoicePdfService     $pdfService,
        private readonly ArcaConnectionService $connectionService,
    ) {}

    // ── Configuración ─────────────────────────────────────────────────────────

    public function configuracion()
    {
        $orgId   = Auth::user()->organization_id;
        $setting = FiscalSetting::where('organization_id', $orgId)->first();
        $cert    = ArcaCertificate::where('organization_id', $orgId)->first();

        return view('arca.configuracion', compact('setting', 'cert'));
    }

    public function guardarConfiguracion(Request $request)
    {
        $validated = $request->validate([
            'cuit'                     => ['required', 'string', 'max:20'],
            'razon_social'             => ['required', 'string', 'max:255'],
            'condicion_iva'            => ['required', 'in:RESPONSABLE_INSCRIPTO,MONOTRIBUTO,EXENTO'],
            'punto_venta'              => ['required', 'integer', 'min:1', 'max:9999'],
            'tipo_comprobante_default' => ['required', 'in:A,B,C'],
            'ambiente'                 => ['required', 'in:TESTING,PRODUCTION'],
            'motor_integracion'        => ['required', 'in:manual,afip_sdk'],
            'activo'                   => ['nullable', 'boolean'],
        ]);

        $validated['activo'] = $request->boolean('activo');

        try {
            $this->fiscalService->createOrUpdate(Auth::user()->organization_id, $validated);
        } catch (Throwable $e) {
            Log::error('FiscalSetting save failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'No se pudo guardar la configuración fiscal. Revisá los datos ingresados.');
        }

        return back()->with('success', 'Configuración fiscal guardada correctamente.');
    }

    public function guardarCertificado(Request $request)
    {
        $request->validate([
            'certificate'       => ['required', 'string'],
            'private_key'       => ['required', 'string'],
            'certificate_alias' => ['nullable', 'string', 'max:255'],
            'expires_at'        => ['nullable', 'date'],
        ]);

        $orgId = Auth::user()->organization_id;

        $certContent = str_replace("\r\n", "\n", $request->input('certificate'));
        $keyContent = str_replace("\r\n", "\n", $request->input('private_key'));

        ArcaCertificate::updateOrCreate(
            ['organization_id' => $orgId],
            [
                'organization_id'   => $orgId,
                'certificate'       => $certContent,
                'private_key'       => $keyContent,
                'certificate_alias' => $request->input('certificate_alias'),
                'expires_at'        => $request->input('expires_at'),
            ]
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Certificado guardado correctamente.']);
        }

        return back()->with('success', 'Certificado guardado correctamente. Los datos sensibles están cifrados.');
    }

    public function validarCertificado(Request $request)
    {
        $orgId = Auth::user()->organization_id;
        $cert = ArcaCertificate::where('organization_id', $orgId)->first();

        if (!$cert || !$cert->certificate || !$cert->private_key) {
            return response()->json(['success' => false, 'message' => 'No hay certificado o clave privada guardados.']);
        }

        $certRes = openssl_x509_read($cert->certificate);
        if ($certRes === false) {
            return response()->json(['success' => false, 'message' => 'El certificado cargado no tiene formato válido. Revisá que incluya BEGIN CERTIFICATE y END CERTIFICATE.']);
        }

        $keyRes = openssl_pkey_get_private($cert->private_key);
        if ($keyRes === false) {
            openssl_x509_free($certRes);
            return response()->json(['success' => false, 'message' => 'La clave privada cargada no tiene formato válido o no corresponde.']);
        }

        // Validate Modulus match
        $certData = openssl_x509_parse($cert->certificate);
        $certDetails = openssl_pkey_get_details(openssl_pkey_get_public($cert->certificate));
        $keyDetails = openssl_pkey_get_details($keyRes);

        $match = ($certDetails['rsa']['n'] ?? '1') === ($keyDetails['rsa']['n'] ?? '2');

        openssl_x509_free($certRes);
        openssl_free_key($keyRes);

        if (!$match) {
            return response()->json(['success' => false, 'message' => 'El certificado y la clave privada no coinciden.']);
        }

        return response()->json(['success' => true, 'message' => 'Certificado y clave privada leídos correctamente y coinciden entre sí.']);
    }

    public function testConnection(Request $request)
    {
        $result = $this->connectionService->testConnection(Auth::user()->organization_id);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function generateCsr(Request $request)
    {
        $validated = $request->validate([
            'alias'        => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'cuit'         => ['required', 'string', 'max:20'],
            'razon_social' => ['required', 'string', 'max:255'],
        ]);

        if (! extension_loaded('openssl')) {
            return response()->json(['message' => 'La extensión OpenSSL no está disponible en este servidor.'], 500);
        }

        $cuit = preg_replace('/[^0-9]/', '', $validated['cuit']);

        $privKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($privKey === false) {
            return response()->json(['message' => 'No se pudo generar la clave RSA. Verificá la configuración de OpenSSL en el servidor.'], 500);
        }

        openssl_pkey_export($privKey, $privateKeyPem);

        $csr = openssl_csr_new([
            'countryName'      => 'AR',
            'organizationName' => mb_substr($validated['razon_social'], 0, 64),
            'commonName'       => $validated['alias'],
            'serialNumber'     => 'CUIT ' . $cuit,
        ], $privKey, ['digest_alg' => 'sha256']);

        if ($csr === false) {
            return response()->json(['message' => 'No se pudo generar el CSR.'], 500);
        }

        openssl_csr_export($csr, $csrPem);

        return response()->json([
            'private_key'  => $privateKeyPem,
            'csr'          => $csrPem,
            'filename_key' => $validated['alias'] . '.key',
            'filename_csr' => $validated['alias'] . '.csr',
        ]);
    }

    // ── Nueva Factura ─────────────────────────────────────────────────────────

    public function nueva()
    {
        $orgId   = Auth::user()->organization_id;
        $setting = FiscalSetting::where('organization_id', $orgId)->first();

        return view('arca.nueva', compact('setting'));
    }

    public function storeFactura(Request $request)
    {
        $validated = $request->validate([
            'cliente_nombre'          => ['required', 'string', 'max:255'],
            'cliente_documento'       => ['nullable', 'string', 'max:20'],
            'cliente_condicion_iva'   => ['nullable', 'in:MONOTRIBUTO,RESPONSABLE_INSCRIPTO,EXENTO,CONSUMIDOR_FINAL'],
            'tipo_comprobante'        => ['required', 'in:A,B,C'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.descripcion'     => ['required', 'string', 'max:255'],
            'items.*.cantidad'        => ['required', 'numeric', 'min:0.01'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'autorizar'               => ['nullable', 'boolean'],
        ]);

        $orgId = Auth::user()->organization_id;

        try {
            $invoice = $this->invoiceService->createDraftInvoice($orgId, $validated);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->boolean('autorizar')) {
            try {
                $result = $this->invoiceService->authorizeInvoice($orgId, $invoice->id);
                if ($result['success']) {
                    return redirect()->route('arca.show-factura', $invoice->id)
                        ->with('success', 'Factura autorizada por ARCA correctamente. CAE: ' . ($result['data']['cae'] ?? ''));
                }
                return redirect()->route('arca.show-factura', $invoice->id)
                    ->with('error', $result['message']);
            } catch (RuntimeException $e) {
                return redirect()->route('arca.show-factura', $invoice->id)
                    ->with('error', 'Factura creada pero no autorizada: ' . $e->getMessage());
            }
        }

        return redirect()->route('arca.show-factura', $invoice->id)
            ->with('success', 'Borrador creado. Recordá autorizarlo con ARCA.');
    }

    // ── Comprobantes ──────────────────────────────────────────────────────────

    public function comprobantes(Request $request)
    {
        $orgId   = Auth::user()->organization_id;
        $perPage = 20;
        $filters = $request->only(['status', 'type', 'date_from', 'date_to', 'client', 'number']);

        $invoices = $this->invoiceService->getInvoicesByOrganization($orgId, $perPage, $filters);

        return view('arca.comprobantes.index', compact('invoices', 'filters'));
    }

    public function showFactura(int $invoice)
    {
        $orgId = Auth::user()->organization_id;

        $inv = Invoice::where('organization_id', $orgId)
            ->where('id', $invoice)
            ->with('items')
            ->firstOrFail();

        return view('arca.comprobantes.show', ['invoice' => $inv]);
    }

    public function autorizarFactura(int $invoice)
    {
        $orgId = Auth::user()->organization_id;

        try {
            $result = $this->invoiceService->authorizeInvoice($orgId, $invoice);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($result['success']) {
            return back()->with('success', 'Factura autorizada. CAE: ' . ($result['data']['cae'] ?? ''));
        }

        return back()->with('error', $result['message']);
    }

    public function generarPdf(int $invoice)
    {
        $orgId = Auth::user()->organization_id;

        try {
            $this->pdfService->generate($orgId, $invoice);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'PDF generado correctamente.');
    }

    public function descargarPdf(int $invoice)
    {
        $orgId = Auth::user()->organization_id;

        try {
            return $this->pdfService->download($orgId, $invoice);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Logs ──────────────────────────────────────────────────────────────────

    public function logs(Request $request)
    {
        $orgId = Auth::user()->organization_id;

        $query = ArcaLog::where('organization_id', $orgId)
            ->with('invoice:id,tipo_comprobante,punto_venta,numero_comprobante,estado')
            ->latest();

        if ($request->filled('endpoint')) {
            $query->where('endpoint', 'like', '%' . $request->input('endpoint') . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('arca.logs', compact('logs'));
    }

    public function showLog(int $log)
    {
        $orgId = Auth::user()->organization_id;

        $arcaLog = ArcaLog::where('organization_id', $orgId)
            ->where('id', $log)
            ->with('invoice:id,tipo_comprobante,punto_venta,numero_comprobante')
            ->firstOrFail();

        return response()->json($arcaLog);
    }
}

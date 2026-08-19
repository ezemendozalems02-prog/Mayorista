<?php

namespace App\Http\Controllers\Api\Arca;

use App\Http\Controllers\Controller;
use App\Http\Requests\Arca\StoreCertificateRequest;
use App\Models\ArcaCertificate;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/arca/certificate
     *
     * Returns certificate status for the authenticated organization.
     * Never exposes PEM content, token, or private key.
     */
    public function show(Request $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        $cert = ArcaCertificate::where('organization_id', $organizationId)->first();

        if (! $cert) {
            return $this->success([
                'has_certificate'   => false,
                'has_private_key'   => false,
                'certificate_alias' => null,
                'expires_at'        => null,
                'is_expired'        => false,
            ]);
        }

        return $this->success([
            'has_certificate'   => ! empty($cert->certificate),
            'has_private_key'   => ! empty($cert->private_key),
            'certificate_alias' => $cert->certificate_alias,
            'expires_at'        => $cert->expires_at?->toDateString(),
            'is_expired'        => $cert->isExpired(),
        ]);
    }

    /**
     * POST /api/arca/certificate
     *
     * Create or replace the ARCA certificate and private key.
     * Sensitive fields are encrypted at rest via Eloquent cast.
     */
    public function store(StoreCertificateRequest $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;

        $validated = $request->validated();
        if (isset($validated['certificate'])) {
            $validated['certificate'] = str_replace("\r\n", "\n", $validated['certificate']);
        }
        if (isset($validated['private_key'])) {
            $validated['private_key'] = str_replace("\r\n", "\n", $validated['private_key']);
        }

        $cert = ArcaCertificate::updateOrCreate(
            ['organization_id' => $organizationId],
            array_merge($validated, ['organization_id' => $organizationId]),
        );

        $wasCreated = $cert->wasRecentlyCreated;

        return $this->success(
            [
                'has_certificate'   => ! empty($cert->certificate),
                'has_private_key'   => ! empty($cert->private_key),
                'certificate_alias' => $cert->certificate_alias,
                'expires_at'        => $cert->expires_at?->toDateString(),
                'is_expired'        => $cert->isExpired(),
            ],
            $wasCreated ? 'Certificado guardado correctamente.' : 'Certificado actualizado correctamente.',
            $wasCreated ? 201 : 200,
        );
    }

    /**
     * POST /api/arca/certificate/validate
     */
    public function validateCertificate(Request $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;
        $cert = ArcaCertificate::where('organization_id', $organizationId)->first();

        if (!$cert || !$cert->certificate || !$cert->private_key) {
            return $this->error('No hay certificado o clave privada guardados.', [], 422);
        }

        $certRes = openssl_x509_read($cert->certificate);
        if ($certRes === false) {
            return $this->error('El certificado cargado no tiene formato válido. Revisá que incluya BEGIN CERTIFICATE y END CERTIFICATE.', [], 422);
        }

        $keyRes = openssl_pkey_get_private($cert->private_key);
        if ($keyRes === false) {
            openssl_x509_free($certRes);
            return $this->error('La clave privada cargada no tiene formato válido o no corresponde.', [], 422);
        }

        $certDetails = openssl_pkey_get_details(openssl_pkey_get_public($cert->certificate));
        $keyDetails = openssl_pkey_get_details($keyRes);

        $match = ($certDetails['rsa']['n'] ?? '1') === ($keyDetails['rsa']['n'] ?? '2');

        openssl_x509_free($certRes);
        openssl_free_key($keyRes);

        if (!$match) {
            return $this->error('El certificado y la clave privada no coinciden.', [], 422);
        }

        return $this->success([], 'Certificado y clave privada leídos correctamente y coinciden entre sí.');
    }
}

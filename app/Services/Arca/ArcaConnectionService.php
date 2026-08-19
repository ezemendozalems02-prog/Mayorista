<?php

namespace App\Services\Arca;

use App\Models\ArcaCertificate;
use App\Models\ArcaLog;
use App\Models\FiscalSetting;
use App\Models\Organization;
use Throwable;

/**
 * ArcaConnectionService — orchestrates the WSAA authentication test.
 *
 * Validates all prerequisites and then delegates to ArcaWsaaService to
 * obtain a real token from ARCA. Exposes only non-sensitive data to callers
 * (environment + expiration — never token/sign).
 */
class ArcaConnectionService
{
    public function __construct(private readonly ArcaWsaaService $wsaa) {}

    /**
     * Run a full WSAA authentication test for the given organization.
     *
     * Steps:
     *   1. Verify the organization exists.
     *   2. Verify fiscal settings are configured and active.
     *   3. Verify a certificate and private key have been uploaded.
     *   4. Delegate to ArcaWsaaService::getValidToken (uses cached token or
     *      requests a fresh one from ARCA WSAA).
     *   5. Return a structured result — token/sign are never forwarded to the caller.
     */
    public function testConnection(int $organizationId): array
    {
        // ── Step 1: organization ──────────────────────────────────────────────
        $organization = Organization::find($organizationId);
        if (! $organization) {
            return $this->error('Organización no encontrada.');
        }

        // ── Step 2: fiscal settings ───────────────────────────────────────────
        $fiscalSetting = FiscalSetting::where('organization_id', $organizationId)->first();
        if (! $fiscalSetting) {
            return $this->error('No hay configuración fiscal registrada para esta organización.');
        }
        if (! $fiscalSetting->activo) {
            return $this->error('La configuración fiscal está inactiva.');
        }

        // ── Step 3: certificate and private key ───────────────────────────────
        $certificate = ArcaCertificate::where('organization_id', $organizationId)->first();
        if (! $certificate) {
            return $this->error('No se encontró un certificado ARCA para esta organización.');
        }
        if (empty($certificate->certificate) || empty($certificate->private_key)) {
            return $this->error('El certificado o la clave privada no han sido cargados.');
        }
        if ($certificate->isExpired()) {
            return $this->error(
                "El certificado ARCA está vencido desde {$certificate->expires_at->toDateString()}. Renuévelo en ARCA.",
            );
        }

        // ── Step 4: authenticate with ARCA WSAA ───────────────────────────────
        try {
            $result = $this->wsaa->getValidToken($organizationId);

            return [
                'success' => true,
                'message' => $result['cached']
                    ? 'Token WSAA vigente reutilizado correctamente.'
                    : 'Autenticación WSAA con ARCA exitosa. Token obtenido.',
                'data' => [
                    'environment' => $result['environment'],
                    'expiration'  => $result['expiration'],
                    'cached'      => $result['cached'],
                ],
            ];

        } catch (Throwable $e) {
            return $this->error($this->sanitizeMessage($e->getMessage()));
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function error(string $message): array
    {
        return ['success' => false, 'message' => $message, 'data' => []];
    }

    /**
     * Remove anything that looks like a certificate/key/token from an exception
     * message before it reaches the response payload.
     */
    private function sanitizeMessage(string $message): string
    {
        // Strip PEM blocks that might appear in chained exception messages
        $sanitized = preg_replace('/-----BEGIN[^-]+-----[\s\S]*?-----END[^-]+-----/', '[REDACTED]', $message);
        // Truncate very long strings (e.g. raw base64 blobs)
        if (strlen($sanitized) > 300) {
            $sanitized = substr($sanitized, 0, 300) . '…';
        }

        return $sanitized;
    }
}

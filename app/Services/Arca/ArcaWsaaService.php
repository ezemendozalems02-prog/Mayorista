<?php

namespace App\Services\Arca;

use App\Models\ArcaCertificate;
use App\Models\ArcaLog;
use App\Models\ArcaToken;
use App\Models\FiscalSetting;
use Carbon\Carbon;
use RuntimeException;
use SoapClient;
use SoapFault;
use Throwable;

/**
 * ArcaWsaaService — WSAA (Web Service de Autenticación y Autorización) client.
 *
 * Flow summary:
 *   1. Build a TRA (Ticket de Requerimiento de Acceso) XML.
 *   2. Sign it with the tenant's X.509 certificate via PKCS#7/CMS.
 *   3. Send the CMS to ARCA's WSAA SOAP endpoint (loginCms).
 *   4. Parse the Ticket de Acceso (TA) response and extract token + sign.
 *   5. Persist token/sign encrypted; reuse them until they expire.
 *
 * This service handles WSAA only. WSFEv1 invoice authorisation is Phase 3.
 */
class ArcaWsaaService
{
    // ──────────────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Return a valid token/sign pair for the given organization.
     *
     * Reuses the stored token when it still has at least `expiry_buffer_minutes`
     * left; otherwise requests a fresh one from ARCA WSAA.
     *
     * The returned array is for INTERNAL service-to-service use only.
     * Never forward token/sign to API consumers.
     *
     * @return array{token: string, sign: string, expiration: string, environment: string, cached: bool}
     * @throws RuntimeException
     */
    public function getValidToken(int $organizationId): array
    {
        $fiscalSetting = FiscalSetting::where('organization_id', $organizationId)->firstOrFail();
        $environment   = $fiscalSetting->ambiente;
        $bufferMinutes = config('arca.wsaa.expiry_buffer_minutes', 5);

        $existing = ArcaToken::where('organization_id', $organizationId)
            ->where('environment', $environment)
            ->first();

        // Token is still valid if it won't expire within the buffer window
        if (
            $existing
            && $existing->expiration
            && $existing->expiration->gt(now()->addMinutes($bufferMinutes))
            && $existing->token !== null
            && $existing->sign !== null
        ) {
            return [
                'token'       => $existing->token,   // decrypted by Eloquent cast
                'sign'        => $existing->sign,     // decrypted by Eloquent cast
                'expiration'  => $existing->expiration->toIso8601String(),
                'environment' => $environment,
                'cached'      => true,
            ];
        }

        return $this->requestNewToken($organizationId);
    }

    /**
     * Authenticate against ARCA WSAA and persist the resulting token/sign.
     *
     * @return array{token: string, sign: string, expiration: string, environment: string, cached: bool}
     * @throws RuntimeException
     */
    public function requestNewToken(int $organizationId): array
    {
        $fiscalSetting = FiscalSetting::where('organization_id', $organizationId)->firstOrFail();
        $certificate   = ArcaCertificate::where('organization_id', $organizationId)->firstOrFail();
        $environment   = $fiscalSetting->ambiente;
        $service       = config('arca.wsaa.service', 'wsfe');

        // Decrypt certificate/key — Eloquent handles decryption automatically via cast
        $certContent = $certificate->certificate;
        $keyContent  = $certificate->private_key;

        if (empty($certContent) || empty($keyContent)) {
            throw new RuntimeException('El certificado o la clave privada no están cargados. Suba ambos antes de autenticar.');
        }

        $traXml = $this->generateTraXml($service);

        try {
            $cms         = $this->signTra($traXml, $certContent, $keyContent, $organizationId);
            $responseXml = $this->callWsaa($cms, $environment);
            $parsed      = $this->parseLoginCmsResponse($responseXml);
            $expiration  = Carbon::parse($parsed['expiration']);

            // One active token per organization + environment (enforced by DB unique constraint)
            ArcaToken::updateOrCreate(
                ['organization_id' => $organizationId, 'environment' => $environment],
                [
                    'token'      => $parsed['token'],
                    'sign'       => $parsed['sign'],
                    'expiration' => $expiration,
                ],
            );

            // Log success — never store token/sign or private key in logs
            $this->log($organizationId, $service, $environment, 'OK', [
                'environment' => $environment,
                'expiration'  => $expiration->toIso8601String(),
            ]);

            return [
                'token'       => $parsed['token'],
                'sign'        => $parsed['sign'],
                'expiration'  => $expiration->toIso8601String(),
                'environment' => $environment,
                'cached'      => false,
            ];

        } catch (Throwable $e) {
            $this->log($organizationId, $service, $environment, 'ERROR', null, $e->getMessage());

            // Re-throw without sensitive context so the caller can surface a clean message
            throw new RuntimeException(
                "WSAA authentication failed for organization {$organizationId}: " . $e->getMessage(),
                0,
                $e,
            );
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TRA generation
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Build the TRA (Ticket de Requerimiento de Acceso) XML.
     *
     * The TRA is valid from 10 minutes ago (clock-skew tolerance) to
     * 12 hours from now, which is the maximum WSAA allows per session.
     */
    public function generateTraXml(string $service): string
    {
        $uniqueId       = time();
        $generationTime = now()->subMinutes(10)->format('Y-m-d\TH:i:s');
        $expirationTime = now()->addHours(12)->format('Y-m-d\TH:i:s');
        $service        = htmlspecialchars($service, ENT_XML1);

        return <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <loginTicketRequest version="1.0">
              <header>
                <uniqueId>{$uniqueId}</uniqueId>
                <generationTime>{$generationTime}</generationTime>
                <expirationTime>{$expirationTime}</expirationTime>
              </header>
              <service>{$service}</service>
            </loginTicketRequest>
            XML;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Signing
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Valida que el certificado y la clave privada coincidan y tengan formato válido.
     */
    private function validateCertificateAndPrivateKeyMatch(string $certificate, string &$privateKey): void
    {
        if (!str_contains($certificate, '-----BEGIN CERTIFICATE-----')) {
            throw new RuntimeException('El certificado cargado no tiene formato válido. Revisá que incluya BEGIN CERTIFICATE y END CERTIFICATE.');
        }

        if (!str_contains($privateKey, '-----BEGIN RSA PRIVATE KEY-----') && !str_contains($privateKey, '-----BEGIN PRIVATE KEY-----')) {
            throw new RuntimeException('La clave privada cargada no tiene formato válido o no corresponde.');
        }

        $certRes = openssl_x509_read($certificate);
        if ($certRes === false) {
            throw new RuntimeException('El certificado cargado no tiene formato válido. Revisá que incluya BEGIN CERTIFICATE y END CERTIFICATE.');
        }

        $keyRes = openssl_pkey_get_private($privateKey);
        if ($keyRes === false) {
            throw new RuntimeException('La clave privada cargada no tiene formato válido o no corresponde.');
        }

        // Convertir PKCS8 a RSA PKCS1 si es necesario
        if (str_contains($privateKey, '-----BEGIN PRIVATE KEY-----')) {
            openssl_pkey_export($keyRes, $exportedKey, null, ["private_key_type" => OPENSSL_KEYTYPE_RSA]);
            if ($exportedKey) {
                $privateKey = $exportedKey;
            }
        }

        $certData = openssl_pkey_get_details(openssl_pkey_get_public($certRes));
        $keyData = openssl_pkey_get_details($keyRes);

        if (!isset($certData['rsa']['n']) || !isset($keyData['rsa']['n']) || $certData['rsa']['n'] !== $keyData['rsa']['n']) {
            throw new RuntimeException('No se pudo firmar la solicitud WSAA. Verificá que el certificado y la clave privada coincidan.');
        }
        
        openssl_x509_free($certRes);
        openssl_free_key($keyRes);
    }

    /**
     * Sign the TRA XML using the tenant's certificate and private key.
     *
     * Produces a PKCS#7/CMS signed message in the format ARCA WSAA expects.
     * Temporary files are always deleted in the finally block.
     *
     * @throws RuntimeException on OpenSSL errors or file I/O failures
     */
    private function signTra(string $traXml, string $certificate, string $privateKey, int $organizationId = 0): string
    {
        if (! function_exists('openssl_pkcs7_sign')) {
            throw new RuntimeException('La extensión OpenSSL de PHP no está habilitada.');
        }

        $this->validateCertificateAndPrivateKeyMatch($certificate, $privateKey);

        $tmpDir = config('arca.tmp_path', storage_path('app/private/arca_tmp'));

        if (! is_dir($tmpDir) && ! mkdir($tmpDir, 0700, true) && ! is_dir($tmpDir)) {
            throw new RuntimeException("No se pudo crear el directorio temporal: {$tmpDir}");
        }

        $uid        = uniqid('wsaa_', true);
        $traFile    = "{$tmpDir}/{$uid}_tra.xml";
        $certFile   = "{$tmpDir}/{$uid}_cert.crt";
        $keyFile    = "{$tmpDir}/{$uid}_key.key";
        $signedFile = "{$tmpDir}/{$uid}_signed.tmp";

        try {
            // Escribir archivos reales para openssl_pkcs7_sign
            if (file_put_contents($traFile, $traXml) === false) {
                throw new RuntimeException('No se pudo escribir el archivo TRA temporal.');
            }
            if (file_put_contents($certFile, $certificate) === false) {
                throw new RuntimeException('No se pudo escribir el archivo de certificado temporal.');
            }
            if (file_put_contents($keyFile, $privateKey) === false) {
                throw new RuntimeException('No se pudo escribir el archivo de clave temporal.');
            }
            
            chmod($traFile, 0600);
            chmod($certFile, 0600);
            chmod($keyFile, 0600);

            $this->logInternal($organizationId, "file://{$certFile}", true, true, null);

            $signed = openssl_pkcs7_sign(
                $traFile,
                $signedFile,
                "file://{$certFile}",
                ["file://{$keyFile}", ''],
                [],
                PKCS7_BINARY | PKCS7_DETACHED
            );

            if ($signed === false) {
                $errors = [];
                while ($msg = openssl_error_string()) {
                    $errors[] = $msg;
                }
                $openSslError = implode(', ', $errors) ?: 'unknown OpenSSL error';
                throw new RuntimeException("No se pudo firmar la solicitud WSAA. Verificá que el certificado y la clave privada coincidan. Detalle técnico: {$openSslError}");
            }

            $smime = file_get_contents($signedFile);

            if ($smime === false || $smime === '') {
                throw new RuntimeException('El archivo firmado está vacío o no pudo leerse.');
            }

            return $this->extractCmsFromSmime($smime);

        } finally {
            // Limpiar archivos temporales siempre, incluso si falla
            foreach ([$traFile, $certFile, $keyFile, $signedFile] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    private function logInternal(int $organizationId, string $certPath, bool $certReadable, bool $keyReadable, ?string $error)
    {
        \Illuminate\Support\Facades\Log::info("WSAA Cert Auth", [
            'organization_id' => $organizationId,
            'cert_path'       => $certPath,
            'key_path'        => str_replace('_cert.crt', '_key.key', $certPath),
            'cert_readable'   => $certReadable,
            'key_readable'    => $keyReadable,
            'error'           => $error
        ]);
    }

    /**
     * Extract the base64-encoded CMS body from the S/MIME envelope produced
     * by openssl_pkcs7_sign.
     *
     * S/MIME format:
     *   <MIME headers>
     *   <blank line>
     *   <base64-encoded CMS data>
     *
     * ARCA WSAA expects only the base64 body (without MIME headers or newlines).
     */
    private function extractCmsFromSmime(string $smime): string
    {
        // Split on the first blank line that separates headers from body
        $pos = strpos($smime, "\n\n");
        if ($pos === false) {
            $pos = strpos($smime, "\r\n\r\n");
            if ($pos === false) {
                throw new RuntimeException('Formato S/MIME inesperado: no se encontró la separación de cabeceras.');
            }
            $body = substr($smime, $pos + 4);
        } else {
            $body = substr($smime, $pos + 2);
        }

        // Strip all line breaks — WSAA expects a single base64 string
        return str_replace(["\r", "\n"], '', trim($body));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SOAP call
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Send the signed CMS to ARCA WSAA via SOAP and return the raw XML response.
     *
     * ARCA uses an Apache Axis SOAP service where the single parameter is
     * named `in0` (Axis convention) and the return value is `loginCmsReturn`.
     *
     * @throws RuntimeException wrapping any SoapFault
     */
    private function callWsaa(string $cms, string $environment): string
    {
        $baseUrl = $environment === 'PRODUCTION'
            ? config('arca.wsaa.production_url')
            : config('arca.wsaa.testing_url');

        $wsdlUrl = $baseUrl . '?wsdl';
        $timeout = config('arca.wsaa.timeout', 30);

        try {
            $client = new SoapClient($wsdlUrl, [
                'soap_version'       => SOAP_1_2,
                'exceptions'         => true,
                'trace'              => true,
                'connection_timeout' => $timeout,
                'stream_context'     => stream_context_create([
                    'ssl'  => ['verify_peer' => true, 'verify_peer_name' => true],
                    'http' => ['timeout' => $timeout],
                ]),
            ]);

            $response = $client->loginCms(['in0' => $cms]);

            return $response->loginCmsReturn;

        } catch (SoapFault $e) {
            throw new RuntimeException(
                "Error SOAP al conectar con WSAA ({$environment}): {$e->getMessage()}",
                0,
                $e,
            );
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Response parsing
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Parse the loginTicketResponse XML returned by WSAA.
     *
     * Expected structure:
     *   <loginTicketResponse>
     *     <header>
     *       <expirationTime>2026-04-27T22:00:00.000-03:00</expirationTime>
     *     </header>
     *     <credentials>
     *       <token>...</token>
     *       <sign>...</sign>
     *     </credentials>
     *   </loginTicketResponse>
     *
     * @return array{token: string, sign: string, expiration: string}
     * @throws RuntimeException on malformed XML or missing fields
     */
    public function parseLoginCmsResponse(string $xml): array
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        libxml_clear_errors();

        if ($doc === false) {
            throw new RuntimeException('La respuesta de WSAA no es XML válido.');
        }

        $token      = trim((string) $doc->credentials->token);
        $sign       = trim((string) $doc->credentials->sign);
        $expiration = trim((string) $doc->header->expirationTime);

        if ($token === '' || $sign === '' || $expiration === '') {
            throw new RuntimeException('La respuesta de WSAA no contiene token, sign o expirationTime.');
        }

        return compact('token', 'sign', 'expiration');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Logging helper
    // ──────────────────────────────────────────────────────────────────────────

    private function log(
        int     $organizationId,
        string  $service,
        string  $environment,
        string  $status,
        ?array  $responsePayload,
        ?string $errorMessage = null,
    ): void {
        // Request payload deliberately omits certificate/key/CMS content
        ArcaLog::create([
            'organization_id'  => $organizationId,
            'invoice_id'       => null,
            'endpoint'         => "wsaa/loginCms/{$service}",
            'request_payload'  => ['service' => $service, 'environment' => $environment],
            'response_payload' => $responsePayload,
            'status'           => $status,
            'error_message'    => $errorMessage,
        ]);
    }
}

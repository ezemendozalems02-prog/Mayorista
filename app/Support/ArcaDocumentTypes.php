<?php

namespace App\Support;

/**
 * Resolves ARCA WSFEv1 document type codes from raw document strings.
 *
 * Reference: ARCA "Descripción de Tablas y Parámetros" — Tabla de tipos de documento.
 *
 * Rules for this phase:
 *   - 11 digits  → CUIT (80)
 *   - 7–8 digits → DNI (96)
 *   - empty/other → Consumidor Final (99), document number 0
 */
class ArcaDocumentTypes
{
    public const CUIT             = 80;
    public const DNI              = 96;
    public const CONSUMIDOR_FINAL = 99;

    /**
     * Resolve document type code and cleaned number from a raw document string.
     *
     * @return array{tipo: int, numero: int}
     */
    public static function resolve(?string $documento): array
    {
        if (empty($documento)) {
            return ['tipo' => self::CONSUMIDOR_FINAL, 'numero' => 0];
        }

        $digits = preg_replace('/\D/', '', $documento);

        if (strlen($digits) === 11) {
            return ['tipo' => self::CUIT, 'numero' => (int) $digits];
        }

        if (strlen($digits) >= 7 && strlen($digits) <= 8) {
            return ['tipo' => self::DNI, 'numero' => (int) $digits];
        }

        return ['tipo' => self::CONSUMIDOR_FINAL, 'numero' => 0];
    }
}

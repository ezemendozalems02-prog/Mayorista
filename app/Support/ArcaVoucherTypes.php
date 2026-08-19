<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Maps internal tipo_comprobante strings to ARCA WSFEv1 numeric codes.
 *
 * Reference: ARCA "Descripción de Tablas y Parámetros" — Tabla de tipos de comprobante.
 * Notes de crédito/débito are listed as constants for future phases but
 * are not wired into fromTipoComprobante() yet.
 */
class ArcaVoucherTypes
{
    // ── Facturas ──────────────────────────────────────────────────────────────
    public const FACTURA_A = 1;
    public const FACTURA_B = 6;
    public const FACTURA_C = 11;

    // ── Notas de crédito (Phase 4+) ───────────────────────────────────────────
    public const NOTA_CREDITO_A = 3;
    public const NOTA_CREDITO_B = 8;
    public const NOTA_CREDITO_C = 13;

    private static array $supported = [
        'A' => self::FACTURA_A,
        'B' => self::FACTURA_B,
        'C' => self::FACTURA_C,
    ];

    /**
     * Convert a tipo_comprobante letter ('A', 'B', 'C') to its ARCA numeric code.
     *
     * @throws InvalidArgumentException for unsupported types
     */
    public static function fromTipoComprobante(string $tipo): int
    {
        if (! array_key_exists($tipo, self::$supported)) {
            throw new InvalidArgumentException(
                "Tipo de comprobante '{$tipo}' no soportado en esta fase. Tipos válidos: " . implode(', ', array_keys(self::$supported)),
            );
        }

        return self::$supported[$tipo];
    }

    public static function isSupported(string $tipo): bool
    {
        return array_key_exists($tipo, self::$supported);
    }
}

<?php

namespace App\Enums;

/**
 * Determina, entre otras cosas, que lista de precios/tipo de precio ve el
 * cliente por defecto en Ventas (Fase 11) -- minorista ve retail_price,
 * mayorista ve wholesale_price -- y si tiene sentido habilitarle cuenta
 * corriente (Fase 13).
 */
enum ClientType: string
{
    case RETAIL = 'retail';       // Consumidor final / compra al menudeo
    case WHOLESALE = 'wholesale'; // Revendedor / compra al por mayor
}

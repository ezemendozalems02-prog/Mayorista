<?php

namespace App\Enums;

enum OrderStatus: string
{
    case DRAFT = 'draft';         // Cargado, esperando confirmacion del cliente. No afecta stock.
    case CONFIRMED = 'confirmed'; // Cliente confirmo, listo para preparar. Sigue sin afectar stock.
    case FULFILLED = 'fulfilled'; // Convertido en Sale (Fase 11): ahi recien se descuenta stock y se cobra.
    case CANCELLED = 'cancelled'; // Descartado sin haberse facturado.
}

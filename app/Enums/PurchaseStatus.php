<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case PENDING = 'pending';     // Cargada, todavia no llego la mercaderia -> no afecta stock
    case RECEIVED = 'received';   // Mercaderia recibida -> genero movimientos de stock y actualizo costo
    case CANCELLED = 'cancelled'; // Descartada sin recibir, no afecta stock
}

<?php

namespace App\Enums;

enum PhysicalCountStatus: string
{
    case OPEN = 'open';           // Conteo en curso, se pueden seguir cargando cantidades
    case COMPLETED = 'completed'; // Finalizado: ya generó los ajustes de stock correspondientes
    case CANCELLED = 'cancelled'; // Descartado, no genera ningun ajuste
}

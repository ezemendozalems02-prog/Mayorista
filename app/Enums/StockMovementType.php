<?php

namespace App\Enums;

/**
 * Motivo de un movimiento de stock. La direccion (entrada/salida) NO depende
 * del tipo sino del signo de quantity_delta en StockMovement -- un mismo tipo
 * (ej. ADJUSTMENT) puede sumar o restar segun el caso de uso.
 */
enum StockMovementType: string
{
    case INITIAL = 'initial';               // Carga inicial de stock al dar de alta un producto
    case PURCHASE = 'purchase';              // Ingreso por compra a proveedor (Fase 12)
    case SALE = 'sale';                      // Egreso por venta (Fase 11)
    case ADJUSTMENT = 'adjustment';          // Ajuste manual (rotura, perdida, correccion)
    case RETURN = 'return';                  // Devolucion de cliente (entrada) o a proveedor (salida)
    case TRANSFER = 'transfer';              // Transferencia entre sucursales (Fase futura)
    case PHYSICAL_COUNT = 'physical_count';  // Ajuste por conteo fisico de inventario
}

<?php

namespace App\Enums;

/**
 * Motivo de un movimiento de caja. Igual principio que AccountMovementType
 * (Fase 13): la direccion (entra/sale plata) NO depende del tipo sino del
 * signo de amount en CashMovement -- positivo suma al cajon, negativo resta.
 */
enum CashMovementType: string
{
    case OPENING = 'opening';                 // Fondo inicial al abrir la caja
    case INCOME = 'income';                   // Ingreso manual (no ligado a una venta)
    case EXPENSE = 'expense';                 // Egreso manual (compra chica, retiro, etc.)
    case SALE = 'sale';                       // Venta cobrada en efectivo (automatico)
    case ACCOUNT_PAYMENT = 'account_payment'; // Cobro de cuenta corriente en efectivo (automatico)
}

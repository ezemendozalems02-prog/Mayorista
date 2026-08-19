<?php

namespace App\Enums;

/**
 * Motivo de un movimiento de cuenta corriente. La direccion (deuda/pago) NO
 * depende del tipo sino del signo de amount en AccountMovement -- positivo
 * aumenta lo que el cliente debe, negativo lo reduce. Mismo principio que
 * StockMovementType (Fase 6).
 */
enum AccountMovementType: string
{
    case SALE = 'sale';               // Venta a cuenta corriente (aumenta la deuda)
    case PAYMENT = 'payment';         // El cliente paga (reduce la deuda)
    case ADJUSTMENT = 'adjustment';   // Correccion manual (positiva o negativa)
    case CREDIT_NOTE = 'credit_note'; // Nota de credito (devolucion, reduce la deuda)
}

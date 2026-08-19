<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Se lanza cuando una venta pediria mas cantidad de la que hay en stock.
 * getMessage() ya viene lista para mostrar al usuario (lista los productos
 * afectados).
 */
class InsufficientStockException extends RuntimeException
{
}

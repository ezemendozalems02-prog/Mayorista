<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Estado invalido de caja: intentar abrir una cuando ya hay una abierta,
 * registrar un movimiento o cerrar una que ya esta cerrada.
 */
class CashSessionException extends RuntimeException
{
}

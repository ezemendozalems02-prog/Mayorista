<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Se lanza cuando un cargo a cuenta corriente dejaria al cliente por encima
 * de su credit_limit. getMessage() ya viene lista para mostrar al usuario.
 */
class CreditLimitExceededException extends RuntimeException
{
}

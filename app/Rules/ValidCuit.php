<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCuit implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cuit = preg_replace('/[\s\-]/', '', (string) $value);

        if (! ctype_digit($cuit)) {
            $fail('El :attribute debe contener solo dígitos.');
            return;
        }

        if (strlen($cuit) !== 11) {
            $fail('El :attribute debe tener 11 dígitos.');
            return;
        }

        if (! $this->checkDigitIsValid($cuit)) {
            $fail('El :attribute no es válido (dígito verificador incorrecto).');
        }
    }

    private function checkDigitIsValid(string $cuit): bool
    {
        $multipliers = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cuit[$i] * $multipliers[$i];
        }

        $remainder = $sum % 11;

        if ($remainder === 0) {
            $expectedDigit = 0;
        } elseif ($remainder === 1) {
            // Remainder 1 produces an invalid check digit (10), so the CUIT is invalid
            return false;
        } else {
            $expectedDigit = 11 - $remainder;
        }

        return (int) $cuit[10] === $expectedDigit;
    }
}

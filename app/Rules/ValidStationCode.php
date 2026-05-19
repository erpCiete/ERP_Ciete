<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ValidStationCode implements ValidationRule
{
    public static function normalize(mixed $value): ?string
    {
        $normalized = Str::upper((string) preg_replace('/\s+/', '', trim((string) ($value ?? ''))));

        return $normalized === '' ? null : $normalized;
    }

    public static function passes(mixed $value): bool
    {
        $normalized = self::normalize($value);

        if ($normalized === null) {
            return true;
        }

        // Formatos aceptados: MOEVE-EST-001, REPSOL-0001, EST-1234, etc.
        return preg_match('/^[A-Z0-9]{2,20}(?:-[A-Z0-9]{1,20})*$/', $normalized) === 1;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::passes($value)) {
            $fail(
                app()->isLocale('en')
                    ? 'Enter a valid station code. Example: MOEVE-EST-001.'
                    : 'Introduce un código de estación válido. Ejemplo: MOEVE-EST-001.'
            );
        }
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSpanishPostalCode implements ValidationRule
{
    public static function normalize(mixed $value): ?string
    {
        $normalized = (string) preg_replace('/\D+/', '', trim((string) ($value ?? '')));

        return $normalized === '' ? null : $normalized;
    }

    public static function passes(mixed $value): bool
    {
        $normalized = self::normalize($value);

        if ($normalized === null) {
            return true;
        }

        return preg_match('/^(0[1-9]|[1-4]\d|5[0-2])\d{3}$/', $normalized) === 1;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::passes($value)) {
            $fail(app()->isLocale('en')
                ? 'Enter a valid Spanish postal code.'
                : 'Introduce un código postal español válido.');
        }
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ValidStationCode implements ValidationRule
{
    public function __construct(private readonly string $format = 'internal') {}

    public static function normalize(mixed $value): ?string
    {
        $normalized = Str::upper((string) preg_replace('/\s+/', '', trim((string) ($value ?? ''))));

        return $normalized === '' ? null : $normalized;
    }

    public static function passes(mixed $value, string $format = 'internal'): bool
    {
        $normalized = self::normalize($value);

        if ($normalized === null) {
            return true;
        }

        $pattern = match ($format) {
            'repsol' => '/^REPSOL-\d{4,6}$/',
            'cepsa' => '/^CEPSA-\d{4,6}$/',
            default => '/^[A-Z0-9]{2,10}(?:-[A-Z0-9]{2,10})*-\d{2,6}$/',
        };

        return preg_match($pattern, $normalized) === 1;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::passes($value, $this->format)) {
            $fail($this->message());
        }
    }

    private function message(): string
    {
        return match ($this->format) {
            'repsol' => app()->isLocale('en')
                ? 'Enter a valid Repsol station code. Example: REPSOL-0001.'
                : 'Introduce un codigo Repsol valido. Ejemplo: REPSOL-0001.',
            'cepsa' => app()->isLocale('en')
                ? 'Enter a valid Cepsa station code. Example: CEPSA-0001.'
                : 'Introduce un codigo Cepsa valido. Ejemplo: CEPSA-0001.',
            default => app()->isLocale('en')
                ? 'Enter a valid internal station code. Example: CEPSA-EST-001.'
                : 'Introduce un codigo interno de estacion valido. Ejemplo: CEPSA-EST-001.',
        };
    }
}

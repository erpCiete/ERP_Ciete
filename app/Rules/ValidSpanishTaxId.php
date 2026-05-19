<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ValidSpanishTaxId implements ValidationRule
{
    private const DNI_CONTROL_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    private const CIF_CONTROL_LETTERS = 'JABCDEFGHI';

    private const CIF_DIGIT_ONLY_TYPES = ['A', 'B', 'E', 'H'];

    private const CIF_LETTER_ONLY_TYPES = ['N', 'P', 'Q', 'R', 'S', 'W'];

    public static function normalize(mixed $value): ?string
    {
        $normalized = Str::upper((string) preg_replace('/[\s-]+/', '', trim((string) ($value ?? ''))));

        return $normalized === '' ? null : $normalized;
    }

    public static function passes(mixed $value): bool
    {
        $normalized = self::normalize($value);

        if ($normalized === null) {
            return true;
        }

        if (preg_match('/^\d{8}[A-Z]$/', $normalized) === 1) {
            return self::matchesDniControl(substr($normalized, 0, 8), substr($normalized, -1));
        }

        if (preg_match('/^[XYZ]\d{7}[A-Z]$/', $normalized) === 1) {
            $number = str_replace(['X', 'Y', 'Z'], ['0', '1', '2'], substr($normalized, 0, 8));

            return self::matchesDniControl($number, substr($normalized, -1));
        }

        if (preg_match('/^[ABCDEFGHJNPQRSUVW]\d{7}[0-9A-J]$/', $normalized) === 1) {
            return self::matchesCifControl($normalized);
        }

        return false;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::passes($value)) {
            $fail(self::message());
        }
    }

    private static function matchesDniControl(string $number, string $control): bool
    {
        $expected = self::DNI_CONTROL_LETTERS[((int) $number) % 23] ?? null;

        return $expected === $control;
    }

    private static function matchesCifControl(string $value): bool
    {
        $type = $value[0];
        $digits = substr($value, 1, 7);
        $control = substr($value, -1);

        $oddSum = 0;
        $evenSum = 0;

        for ($index = 0; $index < strlen($digits); $index++) {
            $digit = (int) $digits[$index];

            if ($index % 2 === 0) {
                $doubled = $digit * 2;
                $oddSum += intdiv($doubled, 10) + ($doubled % 10);
                continue;
            }

            $evenSum += $digit;
        }

        $controlDigit = (10 - (($oddSum + $evenSum) % 10)) % 10;
        $controlLetter = self::CIF_CONTROL_LETTERS[$controlDigit] ?? null;

        if (in_array($type, self::CIF_DIGIT_ONLY_TYPES, true)) {
            return $control === (string) $controlDigit;
        }

        if (in_array($type, self::CIF_LETTER_ONLY_TYPES, true)) {
            return $control === $controlLetter;
        }

        return $control === (string) $controlDigit || $control === $controlLetter;
    }

    private static function message(): string
    {
        return app()->isLocale('en')
            ? 'Enter a valid Spanish tax ID (NIF, NIE, or CIF).'
            : 'Introduce un identificador fiscal español válido (NIF, NIE o CIF).';
    }
}

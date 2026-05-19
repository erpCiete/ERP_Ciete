<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

abstract class BaseApiRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->errorResponse(
                $this->authorizationFailureMessage(),
                'AUTHORIZATION_ERROR',
                [],
                403,
            )
        );
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->errorResponse(
                $this->validationFailureMessage(),
                'VALIDATION_ERROR',
                $validator->errors()->toArray(),
                422,
            )
        );
    }

    protected function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }

    protected function normalizeUppercaseCode(mixed $value): ?string
    {
        $normalized = $this->normalizeNullableString($value);

        if ($normalized === null) {
            return null;
        }

        return Str::upper((string) preg_replace('/\s+/', '', $normalized));
    }

    protected function currentUser(): ?User
    {
        $user = $this->user();

        return $user instanceof User ? $user : null;
    }

    private function validationFailureMessage(): string
    {
        return app()->isLocale('en')
            ? 'The provided data is invalid.'
            : 'Los datos proporcionados no son validos.';
    }

    protected function authorizationFailureMessage(): string
    {
        return app()->isLocale('en')
            ? 'You are not authorized to perform this action.'
            : 'No autorizado.';
    }
}

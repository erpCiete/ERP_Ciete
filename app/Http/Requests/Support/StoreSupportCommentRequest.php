<?php

namespace App\Http\Requests\Support;

use App\Models\SolicitudSoporte;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupportCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000'],
            'status' => ['nullable', 'in:' . implode(',', SolicitudSoporte::ESTADOS)],
        ];
    }
}

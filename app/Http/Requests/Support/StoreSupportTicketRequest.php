<?php

namespace App\Http\Requests\Support;

use App\Models\SolicitudSoporte;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'topic' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'priority' => ['sometimes', 'in:' . implode(',', SolicitudSoporte::PRIORIDADES)],
        ];
    }
}

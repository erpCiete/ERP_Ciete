<?php

namespace App\Http\Requests\Support;

use App\Models\SolicitudSoporte;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSupportTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageSupport() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:' . implode(',', SolicitudSoporte::ESTADOS)],
        ];
    }
}

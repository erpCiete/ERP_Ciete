<?php

namespace App\Http\Requests\Messages;

use Illuminate\Foundation\Http\FormRequest;

class BroadcastMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'asunto' => ['required', 'string', 'max:255'],
            'cuerpo' => ['required', 'string', 'max:5000'],
            'prioridad' => ['sometimes', 'in:normal,alta,urgente'],
        ];
    }
}

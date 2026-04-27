<?php

namespace App\Http\Requests\Messages;

use App\Models\MensajeInterno;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInternalMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'id_destinatario' => ['required', 'integer', 'exists:usuarios,id_usuario'],
            'asunto' => ['required', 'string', 'max:255'],
            'cuerpo' => ['required', 'string', 'max:5000'],
            'prioridad' => ['sometimes', 'in:normal,alta,urgente'],
            'id_mensaje_padre' => ['nullable', 'integer', 'exists:mensajes_internos,id_mensaje'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            $recipient = User::query()
                ->where('id_usuario', $this->integer('id_destinatario'))
                ->where('activo', true)
                ->first();

            if (! $recipient) {
                $validator->errors()->add('id_destinatario', 'El destinatario seleccionado no está disponible.');
                return;
            }

            if ($recipient->id_usuario === $user?->id_usuario) {
                $validator->errors()->add('id_destinatario', 'No puedes enviarte mensajes a ti mismo.');
            }

            if (! $this->filled('id_mensaje_padre')) {
                return;
            }

            $parent = MensajeInterno::query()->find($this->integer('id_mensaje_padre'));

            if (! $parent) {
                return;
            }

            $participants = collect([$parent->id_remitente, $parent->id_destinatario])->filter();

            if (! $participants->contains($user?->id_usuario) || ! $participants->contains($recipient->id_usuario)) {
                $validator->errors()->add('id_destinatario', 'La respuesta debe dirigirse al otro participante del mensaje original.');
            }

            if ($parent->id_solicitud_soporte) {
                $validator->errors()->add('cuerpo', 'Las conversaciones de soporte deben continuar desde el módulo de soporte.');
            }
        });
    }
}

<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class UpdateTrabajoRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'required', 'string', 'max:150'],
            'descripcion' => ['sometimes', 'nullable', 'string'],
            'id_estacion_servicio' => ['sometimes', 'required', 'integer'],
            'fecha_programada' => ['sometimes', 'nullable', 'date'],
            'prioridad' => ['sometimes', 'nullable', Rule::in(['baja', 'media', 'alta'])],
            'estado' => ['sometimes', 'nullable', Rule::in(['pendiente', 'en_proceso', 'cerrado'])],

            'cod_repsol' => [
                'sometimes',
                'nullable',
                'string',
                'max:80',
                Rule::prohibitedIf(fn () => $this->isCepsaContext()),
            ],
            'cod_cepsa' => [
                'sometimes',
                'nullable',
                'string',
                'max:80',
                Rule::prohibitedIf(fn () => $this->isRepsolContext()),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->has('cod_repsol') && ! $this->has('cod_cepsa')) {
                return;
            }

            if ($this->isRepsolContext() && blank($this->input('cod_repsol'))) {
                $validator->errors()->add('cod_repsol', 'The cod repsol field is required.');
            }

            if ($this->isCepsaContext() && blank($this->input('cod_cepsa'))) {
                $validator->errors()->add('cod_cepsa', 'The cod cepsa field is required.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('titulo') && is_string($this->titulo)) {
            $data['titulo'] = trim($this->titulo);
        }

        if ($this->has('descripcion') && is_string($this->descripcion)) {
            $data['descripcion'] = trim($this->descripcion);
        }

        if ($this->has('cod_repsol') && is_string($this->cod_repsol)) {
            $data['cod_repsol'] = trim($this->cod_repsol);
        }

        if ($this->has('cod_cepsa') && is_string($this->cod_cepsa)) {
            $data['cod_cepsa'] = trim($this->cod_cepsa);
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

    private function isRepsolContext(): bool
    {
        $codigo = strtoupper((string) optional($this->user()?->contexto)->codigo);

        return $codigo === 'REPSOL';
    }

    private function isCepsaContext(): bool
    {
        $codigo = strtoupper((string) optional($this->user()?->contexto)->codigo);

        return $codigo === 'CEPSA';
    }
}


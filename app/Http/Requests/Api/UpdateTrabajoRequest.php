<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class UpdateTrabajoRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'numero_trabajo' => ['sometimes', 'required', 'string', 'max:100'],
            'descripcion_trabajo' => ['sometimes', 'required', 'string', 'max:255'],
            'id_estacion_servicio' => ['sometimes', 'required', 'integer', 'exists:estacion_servicios,id'],
            'fecha_encargo' => ['sometimes', 'required', 'date'],
            'observaciones' => ['sometimes', 'nullable', 'string'],
            'activo' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('numero_trabajo') && is_string($this->numero_trabajo)) {
            $data['numero_trabajo'] = trim($this->numero_trabajo);
        }

        if ($this->has('descripcion_trabajo') && is_string($this->descripcion_trabajo)) {
            $data['descripcion_trabajo'] = trim($this->descripcion_trabajo);
        }

        if ($this->has('observaciones') && is_string($this->observaciones)) {
            $data['observaciones'] = trim($this->observaciones);
        }

        if ($this->has('activo')) {
            $data['activo'] = (bool) $this->activo;
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}

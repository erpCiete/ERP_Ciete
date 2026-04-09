<?php

namespace App\Http\Requests\Api;

use App\Models\EstacionServicio;
use App\Rules\ValidSpanishPostalCode;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class EstacionUpdateRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $contextId = Auth::user()?->id_contexto;
        $estacion = $this->route('estacion');
        $estacionId = $estacion instanceof EstacionServicio ? $estacion->id_estacion_servicio : $estacion;

        return [
            'id_empresa_cliente' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('empresas', 'id_empresa')->where(
                    fn($query) => $query->where('id_contexto', $contextId)
                ),
            ],
            'nombre' => ['sometimes', 'required', 'string', 'max:180'],
            'codigo_estacion_interno' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('estaciones_servicio', 'codigo_estacion_interno')
                    ->ignore($estacionId, 'id_estacion_servicio')
                    ->where(fn($query) => $query->where('id_contexto', $contextId)),
            ],
            'cod_repsol' => [
                'nullable',
                'string',
                'max:13',
                Rule::unique('estaciones_servicio', 'cod_repsol')
                    ->ignore($estacionId, 'id_estacion_servicio')
                    ->where(fn($query) => $query->where('id_contexto', $contextId)),
            ],
            'cod_cepsa' => [
                'nullable',
                'string',
                'max:12',
                Rule::unique('estaciones_servicio', 'cod_cepsa')
                    ->ignore($estacionId, 'id_estacion_servicio')
                    ->where(fn($query) => $query->where('id_contexto', $contextId)),
            ],
            'tipo' => ['nullable', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'size:5', new ValidSpanishPostalCode()],
            'poblacion' => ['nullable', 'string', 'max:120'],
            'provincia' => ['nullable', 'string', 'max:120'],
            'pais' => ['nullable', 'string', 'max:120'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('id_empresa_cliente')) {
            $normalized['id_empresa_cliente'] = $this->input('id_empresa_cliente');
        }

        if ($this->has('nombre')) {
            $normalized['nombre'] = $this->normalizeNullableString($this->input('nombre'));
        }

        if ($this->has('codigo_estacion_interno')) {
            $normalized['codigo_estacion_interno'] = $this->normalizeNullableString($this->input('codigo_estacion_interno'));
        }

        if ($this->has('cod_repsol')) {
            $normalized['cod_repsol'] = $this->normalizeNullableString($this->input('cod_repsol'));
        }

        if ($this->has('cod_cepsa')) {
            $normalized['cod_cepsa'] = $this->normalizeNullableString($this->input('cod_cepsa'));
        }

        if ($this->has('tipo')) {
            $normalized['tipo'] = $this->normalizeNullableString($this->input('tipo'));
        }

        if ($this->has('direccion')) {
            $normalized['direccion'] = $this->normalizeNullableString($this->input('direccion'));
        }

        if ($this->has('codigo_postal')) {
            $normalized['codigo_postal'] = ValidSpanishPostalCode::normalize($this->input('codigo_postal'));
        }

        if ($this->has('poblacion')) {
            $normalized['poblacion'] = $this->normalizeNullableString($this->input('poblacion'));
        }

        if ($this->has('provincia')) {
            $normalized['provincia'] = $this->normalizeNullableString($this->input('provincia'));
        }

        if ($this->has('pais')) {
            $normalized['pais'] = $this->normalizeNullableString($this->input('pais'));
        }

        if ($this->has('observaciones')) {
            $normalized['observaciones'] = $this->normalizeNullableString($this->input('observaciones'));
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }

        if ($this->has('activo')) {
            $this->merge([
                'activo' => $this->boolean('activo'),
            ]);
        }
    }
}

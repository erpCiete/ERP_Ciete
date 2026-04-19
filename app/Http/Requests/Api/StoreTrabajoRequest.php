<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest; // IMPORTANTE: Cambiar a FormRequest
use Illuminate\Validation\Rule;
use App\Models\EstacionServicio;

class StoreTrabajoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'numero_trabajo'       => ['required', 'string', 'max:50'],
            'descripcion_trabajo'  => ['required', 'string', 'max:150'], // Sincronizado con React
            'id_estacion_servicio' => ['required', 'exists:estaciones_servicio,id_estacion_servicio'],
            'fecha_encargo'        => ['required', 'date'],
            'fecha_terminado'      => ['nullable', 'date'],
            'estado'               => ['required', Rule::in(['borrador', 'en_curso', 'terminado', 'cerrado', 'cancelado'])],
            'observaciones'        => ['nullable', 'string'],

            // VALIDACIÓN INTELIGENTE: Solo pide estos campos si la estación es del cliente correcto
            'id_contrato' => [
                Rule::requiredIf(fn () => $this->esCliente(1)), // 1 = MOEVE
                'nullable', 'integer'
            ],
            'id_tipo_documento' => [
                Rule::requiredIf(fn () => $this->esCliente(2)), // 2 = REPSOL
                'nullable', 'integer'
            ],
            'id_tipo_trabajo' => [
                Rule::requiredIf(fn () => $this->esCliente(2)),
                'nullable', 'integer'
            ],
        ];
    }

    private function esCliente($idContexto): bool
    {
        if (!$this->id_estacion_servicio) return false;
        $estacion = EstacionServicio::find($this->id_estacion_servicio);
        return $estacion && $estacion->id_contexto == $idContexto;
    }
}
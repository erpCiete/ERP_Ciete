<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot entre contratos y empresas facturadoras autorizadas.
 * Permite declarar qué sociedades/CIF están permitidas para facturar bajo un contrato.
 *
 * @property int         $id
 * @property int         $id_contrato
 * @property int         $id_empresa
 * @property int         $id_contexto
 * @property bool        $activo
 * @property string|null $observaciones
 */
class ContratoEmpresaFacturadora extends Model
{
    use HasContext;

    protected $table = 'contrato_empresas_facturadoras';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_contrato',
        'id_empresa',
        'id_contexto',
        'activo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'id_contrato', 'id_contrato');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function contexto(): BelongsTo
    {
        return $this->belongsTo(ContextoCliente::class, 'id_contexto', 'id_contexto');
    }
}

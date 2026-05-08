<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trabajo extends Model
{
    use HasFactory;
    use HasContext;

    public const ESTADOS_FUNCIONALES = [
        'en_curso',
        'terminado',
        'pendiente_facturar',
        'facturado',
        'finalizado',
        'cancelado',
    ];

    protected $table = 'trabajos';

    protected $primaryKey = 'id_trabajo';

    protected $fillable = [
        'id_contexto',
        'id_empresa_cliente',
        'id_estacion_servicio',
        'id_tipo_documento',
        'id_tipo_trabajo',
        'id_contrato',
        'id_tarifario',
        'id_responsable_ciete',
        'numero_trabajo',
        'numero_trabajo_operativo',
        'numero_estacion',
        'zona',
        'descripcion_trabajo',
        'fecha_encargo',
        'fecha_terminacion',
        'observaciones',
        'numero_aviso',
        'orden_mantenimiento',
        'categoria',
        'responsable_cliente',
        'estado',
        'bloqueado_cierre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'numero_trabajo' => 'integer',
            'fecha_encargo' => 'date',
            'fecha_terminacion' => 'date',
            'bloqueado_cierre' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa_cliente', 'id_empresa');
    }

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(EstacionServicio::class, 'id_estacion_servicio', 'id_estacion_servicio');
    }

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class, 'id_tipo_documento', 'id_tipo_documento');
    }

    public function tipoTrabajo(): BelongsTo
    {
        return $this->belongsTo(TipoTrabajo::class, 'id_tipo_trabajo', 'id_tipo_trabajo');
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'id_contrato', 'id_contrato');
    }

    public function tarifario(): BelongsTo
    {
        return $this->belongsTo(Tarifario::class, 'id_tarifario', 'id_tarifario');
    }

    public function responsableCiete(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_responsable_ciete', 'id_usuario');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_trabajo', 'id_trabajo');
    }

    public function primerPedido(): HasOne
    {
        return $this->hasOne(Pedido::class, 'id_trabajo', 'id_trabajo')
            ->oldestOfMany('fecha_solicitud');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'id_trabajo', 'id_trabajo');
    }

    public function numeroTrabajoVisible(): string
    {
        return (string) ($this->numero_trabajo_operativo ?: $this->numero_trabajo);
    }

    public function presupuestos(): HasMany
    {
        return $this->hasMany(Presupuesto::class, 'id_trabajo', 'id_trabajo');
    }

    public function legalizaciones(): HasMany
    {
        return $this->hasMany(Legalizacion::class, 'id_trabajo', 'id_trabajo');
    }

    public function isFunctionallyFinalized(): bool
    {
        return $this->estado === 'finalizado';
    }

    public function isProtectedFinalizedState(): bool
    {
        return $this->isFunctionallyFinalized();
    }
}

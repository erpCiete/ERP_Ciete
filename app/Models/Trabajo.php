<?php

namespace App\Models;

use App\Traits\HasContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trabajo extends Model
{
    use HasFactory;
    use HasContext;

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
        'id_usuario_cierre',
        'numero_trabajo',
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
        'cerrado',
        'bloqueado_cierre',
        'fecha_cierre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_encargo' => 'date',
            'fecha_terminacion' => 'date',
            'fecha_cierre' => 'datetime',
            'cerrado' => 'boolean',
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

    public function usuarioCierre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_cierre', 'id_usuario');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_trabajo', 'id_trabajo');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'id_trabajo', 'id_trabajo');
    }

    public function presupuestos(): HasMany
    {
        return $this->hasMany(Presupuesto::class, 'id_trabajo', 'id_trabajo');
    }

    public function legalizaciones(): HasMany
    {
        return $this->hasMany(Legalizacion::class, 'id_trabajo', 'id_trabajo');
    }
}

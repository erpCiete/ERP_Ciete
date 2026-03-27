<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->bigIncrements('id_proyecto');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->unsignedBigInteger('id_contacto_empresa_cliente')->nullable();
            $table->unsignedBigInteger('id_contacto_empresa_ciete')->nullable();
            $table->unsignedBigInteger('id_estacion_servicio')->nullable();
            $table->unsignedBigInteger('id_tarifario')->nullable();
            $table->unsignedBigInteger('id_usuario_responsable')->nullable();
            $table->unsignedBigInteger('id_usuario_cierre')->nullable();
            $table->string('codigo_proyecto', 100);
            $table->string('nombre_proyecto', 180);
            $table->string('descripcion_seleccionable', 255)->nullable();
            $table->text('descripcion_libre')->nullable();
            $table->text('workplan_resumen')->nullable();
            $table->string('numero_aviso', 100)->nullable();
            $table->date('fecha_encargo')->nullable();
            $table->date('fecha_inicio_prevista')->nullable();
            $table->date('fecha_inicio_real')->nullable();
            $table->date('fecha_fin_prevista')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->enum('estado_general', ['borrador', 'en_curso', 'pausado', 'terminado', 'cerrado', 'cancelado'])
                ->default('borrador');
            $table->enum(
                'estado_workplan',
                [
                    'pendiente',
                    'encargo_recibido',
                    'solicitado_pedido',
                    'pedido_recibido',
                    'inicio_sin_pedido',
                    'inicio_con_pedido',
                    'en_ejecucion',
                    'pendiente_factura',
                    'facturado',
                    'cobrado',
                    'cerrado',
                ]
            )->default('pendiente');
            $table->boolean('trabajo_terminado')->default(false);
            $table->boolean('cerrado')->default(false);
            $table->boolean('bloqueado_cierre')->default(false);
            $table->dateTime('fecha_cierre')->nullable();
            $table->text('observaciones_operativas')->nullable();
            $table->text('observaciones_facturacion')->nullable();
            $table->timestamps();

            $table->unique(['id_contexto', 'codigo_proyecto'], 'uq_proyectos_contexto_codigo');
            $table->index('id_contexto', 'idx_proyectos_contexto');
            $table->index(['id_proyecto', 'id_contexto'], 'idx_proyectos_id_contexto');
            $table->index(['id_empresa_cliente', 'id_contexto'], 'idx_proyectos_empresa_contexto');
            $table->index(
                ['id_contacto_empresa_cliente', 'id_contexto'],
                'idx_proyectos_contacto_cliente_contexto'
            );
            $table->index(
                ['id_contacto_empresa_ciete', 'id_contexto'],
                'idx_proyectos_contacto_ciete_contexto'
            );
            $table->index(['id_estacion_servicio', 'id_contexto'], 'idx_proyectos_estacion_contexto');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_proyectos_tarifario_contexto');
            $table->index(['id_usuario_responsable', 'id_contexto'], 'idx_proyectos_usuario_responsable_contexto');
            $table->index(['id_usuario_cierre', 'id_contexto'], 'idx_proyectos_usuario_cierre_contexto');

            $table->foreign('id_contexto', 'fk_proyectos_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa_cliente', 'id_contexto'], 'fk_proyectos_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(
                ['id_contacto_empresa_cliente', 'id_contexto'],
                'fk_proyectos_contacto_cliente_contexto'
            )
                ->references(['id_contacto_empresa', 'id_contexto'])
                ->on('contactos_empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(
                ['id_contacto_empresa_ciete', 'id_contexto'],
                'fk_proyectos_contacto_ciete_contexto'
            )
                ->references(['id_contacto_empresa', 'id_contexto'])
                ->on('contactos_empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_estacion_servicio', 'id_contexto'], 'fk_proyectos_estacion_contexto')
                ->references(['id_estacion_servicio', 'id_contexto'])
                ->on('estaciones_servicio')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_tarifario', 'id_contexto'], 'fk_proyectos_tarifario_contexto')
                ->references(['id_tarifario', 'id_contexto'])
                ->on('tarifarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_usuario_responsable', 'id_contexto'], 'fk_proyectos_usuario_responsable_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_usuario_cierre', 'id_contexto'], 'fk_proyectos_usuario_cierre_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('proyectos_workplan', function (Blueprint $table) {
            $table->bigIncrements('id_proyecto_workplan');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_proyecto');
            $table->unsignedSmallInteger('orden')->default(1);
            $table->enum('fase', ['encargo', 'pedido', 'ejecucion', 'facturacion', 'cobro', 'cierre', 'otro'])
                ->default('otro');
            $table->enum('estado', ['pendiente', 'en_curso', 'completado', 'bloqueado', 'cancelado'])
                ->default('pendiente');
            $table->string('descripcion_seleccionable', 255)->nullable();
            $table->text('descripcion_libre')->nullable();
            $table->date('fecha_prevista')->nullable();
            $table->date('fecha_real')->nullable();
            $table->unsignedBigInteger('id_usuario_responsable')->nullable();
            $table->boolean('bloqueado')->default(false);
            $table->timestamps();

            $table->unique(
                ['id_contexto', 'id_proyecto', 'orden'],
                'uq_proyectos_workplan_contexto_proyecto_orden'
            );
            $table->index('id_contexto', 'idx_proyectos_workplan_contexto');
            $table->index(['id_proyecto', 'id_contexto'], 'idx_proyectos_workplan_proyecto_contexto');
            $table->index(['id_usuario_responsable', 'id_contexto'], 'idx_proyectos_workplan_usuario_contexto');

            $table->foreign('id_contexto', 'fk_proyectos_workplan_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_proyecto', 'id_contexto'], 'fk_proyectos_workplan_proyecto_contexto')
                ->references(['id_proyecto', 'id_contexto'])
                ->on('proyectos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                ['id_usuario_responsable', 'id_contexto'],
                'fk_proyectos_workplan_usuario_contexto'
            )
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('proyectos_comentarios', function (Blueprint $table) {
            $table->bigIncrements('id_comentario_proyecto');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_proyecto');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->dateTime('fecha_comentario')->useCurrent();
            $table->text('comentario');
            $table->timestamps();

            $table->index('id_contexto', 'idx_proyectos_comentarios_contexto');
            $table->index(['id_proyecto', 'id_contexto'], 'idx_proyectos_comentarios_proyecto_contexto');
            $table->index(['id_usuario', 'id_contexto'], 'idx_proyectos_comentarios_usuario_contexto');

            $table->foreign('id_contexto', 'fk_proyectos_comentarios_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_proyecto', 'id_contexto'], 'fk_proyectos_comentarios_proyecto_contexto')
                ->references(['id_proyecto', 'id_contexto'])
                ->on('proyectos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_usuario', 'id_contexto'], 'fk_proyectos_comentarios_usuario_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('presupuestos', function (Blueprint $table) {
            $table->bigIncrements('id_presupuesto');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_proyecto')->nullable();
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->unsignedBigInteger('id_contacto_empresa_cliente')->nullable();
            $table->unsignedBigInteger('id_estacion_servicio')->nullable();
            $table->unsignedBigInteger('id_tarifario')->nullable();
            $table->unsignedBigInteger('id_usuario_creador')->nullable();
            $table->string('numero_presupuesto', 100);
            $table->date('fecha_emision');
            $table->date('fecha_validez_hasta')->nullable();
            $table->enum('estado', ['borrador', 'enviado', 'aceptado', 'rechazado', 'caducado', 'convertido', 'anulado'])
                ->default('borrador');
            $table->string('descripcion_seleccionable', 255)->nullable();
            $table->text('descripcion_libre')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento_total', 14, 2)->default(0);
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['id_contexto', 'numero_presupuesto'], 'uq_presupuestos_contexto_numero');
            $table->index('id_contexto', 'idx_presupuestos_contexto');
            $table->index(['id_presupuesto', 'id_contexto'], 'idx_presupuestos_id_contexto');
            $table->index(['id_proyecto', 'id_contexto'], 'idx_presupuestos_proyecto_contexto');
            $table->index(['id_empresa_cliente', 'id_contexto'], 'idx_presupuestos_empresa_contexto');
            $table->index(['id_contacto_empresa_cliente', 'id_contexto'], 'idx_presupuestos_contacto_contexto');
            $table->index(['id_estacion_servicio', 'id_contexto'], 'idx_presupuestos_estacion_contexto');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_presupuestos_tarifario_contexto');
            $table->index(['id_usuario_creador', 'id_contexto'], 'idx_presupuestos_usuario_contexto');

            $table->foreign('id_contexto', 'fk_presupuestos_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_proyecto', 'id_contexto'], 'fk_presupuestos_proyecto_contexto')
                ->references(['id_proyecto', 'id_contexto'])
                ->on('proyectos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa_cliente', 'id_contexto'], 'fk_presupuestos_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_contacto_empresa_cliente', 'id_contexto'], 'fk_presupuestos_contacto_contexto')
                ->references(['id_contacto_empresa', 'id_contexto'])
                ->on('contactos_empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_estacion_servicio', 'id_contexto'], 'fk_presupuestos_estacion_contexto')
                ->references(['id_estacion_servicio', 'id_contexto'])
                ->on('estaciones_servicio')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_tarifario', 'id_contexto'], 'fk_presupuestos_tarifario_contexto')
                ->references(['id_tarifario', 'id_contexto'])
                ->on('tarifarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_usuario_creador', 'id_contexto'], 'fk_presupuestos_usuario_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('presupuestos_lineas', function (Blueprint $table) {
            $table->bigIncrements('id_linea_presupuesto');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_presupuesto');
            $table->unsignedBigInteger('id_tarifario_servicio')->nullable();
            $table->unsignedBigInteger('id_servicio')->nullable();
            $table->unsignedInteger('orden')->default(1);
            $table->string('concepto_seleccionable', 255)->nullable();
            $table->text('concepto_libre')->nullable();
            $table->decimal('cantidad', 14, 3)->default(1);
            $table->decimal('precio_unitario', 14, 2)->default(0);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('iva_porcentaje', 5, 2)->default(21);
            $table->decimal('total_linea', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['id_contexto', 'id_presupuesto', 'orden'],
                'uq_presupuestos_lineas_contexto_presupuesto_orden'
            );
            $table->index('id_contexto', 'idx_presupuestos_lineas_contexto');
            $table->index(['id_presupuesto', 'id_contexto'], 'idx_presupuestos_lineas_presupuesto_contexto');
            $table->index(
                ['id_tarifario_servicio', 'id_contexto'],
                'idx_presupuestos_lineas_tarifario_servicio_contexto'
            );
            $table->index('id_servicio', 'idx_presupuestos_lineas_servicio');

            $table->foreign('id_contexto', 'fk_presupuestos_lineas_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_presupuesto', 'id_contexto'], 'fk_presupuestos_lineas_presupuesto_contexto')
                ->references(['id_presupuesto', 'id_contexto'])
                ->on('presupuestos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                ['id_tarifario_servicio', 'id_contexto'],
                'fk_presupuestos_lineas_tarifario_servicio_contexto'
            )
                ->references(['id_tarifario_servicio', 'id_contexto'])
                ->on('tarifario_servicios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('id_servicio', 'fk_presupuestos_lineas_servicio')
                ->references('id_servicio')
                ->on('servicios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->bigIncrements('id_pedido');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_presupuesto')->nullable();
            $table->unsignedBigInteger('id_proyecto');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->unsignedBigInteger('id_contacto_empresa_cliente')->nullable();
            $table->unsignedBigInteger('id_estacion_servicio')->nullable();
            $table->unsignedBigInteger('id_tarifario')->nullable();
            $table->unsignedBigInteger('id_usuario_responsable')->nullable();
            $table->string('numero_pedido', 100);
            $table->string('numero_aviso', 100)->nullable();
            $table->date('fecha_solicitud_pedido')->nullable();
            $table->date('fecha_recepcion_pedido')->nullable();
            $table->date('fecha_solicitud_factura')->nullable();
            $table->enum('estado', ['pendiente', 'solicitado', 'recibido', 'en_ejecucion', 'cerrado', 'anulado'])
                ->default('pendiente');
            $table->string('descripcion_seleccionable', 255)->nullable();
            $table->text('descripcion_libre')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['id_contexto', 'numero_pedido'], 'uq_pedidos_contexto_numero');
            $table->index('id_contexto', 'idx_pedidos_contexto');
            $table->index(['id_pedido', 'id_contexto'], 'idx_pedidos_id_contexto');
            $table->index(['id_presupuesto', 'id_contexto'], 'idx_pedidos_presupuesto_contexto');
            $table->index(['id_proyecto', 'id_contexto'], 'idx_pedidos_proyecto_contexto');
            $table->index(['id_empresa_cliente', 'id_contexto'], 'idx_pedidos_empresa_contexto');
            $table->index(['id_contacto_empresa_cliente', 'id_contexto'], 'idx_pedidos_contacto_contexto');
            $table->index(['id_estacion_servicio', 'id_contexto'], 'idx_pedidos_estacion_contexto');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_pedidos_tarifario_contexto');
            $table->index(['id_usuario_responsable', 'id_contexto'], 'idx_pedidos_usuario_contexto');

            $table->foreign('id_contexto', 'fk_pedidos_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_presupuesto', 'id_contexto'], 'fk_pedidos_presupuesto_contexto')
                ->references(['id_presupuesto', 'id_contexto'])
                ->on('presupuestos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_proyecto', 'id_contexto'], 'fk_pedidos_proyecto_contexto')
                ->references(['id_proyecto', 'id_contexto'])
                ->on('proyectos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa_cliente', 'id_contexto'], 'fk_pedidos_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_contacto_empresa_cliente', 'id_contexto'], 'fk_pedidos_contacto_contexto')
                ->references(['id_contacto_empresa', 'id_contexto'])
                ->on('contactos_empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_estacion_servicio', 'id_contexto'], 'fk_pedidos_estacion_contexto')
                ->references(['id_estacion_servicio', 'id_contexto'])
                ->on('estaciones_servicio')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_tarifario', 'id_contexto'], 'fk_pedidos_tarifario_contexto')
                ->references(['id_tarifario', 'id_contexto'])
                ->on('tarifarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_usuario_responsable', 'id_contexto'], 'fk_pedidos_usuario_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('pedidos_lineas', function (Blueprint $table) {
            $table->bigIncrements('id_linea_pedido');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_tarifario_servicio')->nullable();
            $table->unsignedBigInteger('id_servicio')->nullable();
            $table->unsignedInteger('orden')->default(1);
            $table->string('concepto_seleccionable', 255)->nullable();
            $table->text('concepto_libre')->nullable();
            $table->decimal('cantidad', 14, 3)->default(1);
            $table->decimal('precio_unitario', 14, 2)->default(0);
            $table->decimal('iva_porcentaje', 5, 2)->default(21);
            $table->decimal('total_linea', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['id_contexto', 'id_pedido', 'orden'], 'uq_pedidos_lineas_contexto_pedido_orden');
            $table->index('id_contexto', 'idx_pedidos_lineas_contexto');
            $table->index(['id_pedido', 'id_contexto'], 'idx_pedidos_lineas_pedido_contexto');
            $table->index(
                ['id_tarifario_servicio', 'id_contexto'],
                'idx_pedidos_lineas_tarifario_servicio_contexto'
            );
            $table->index('id_servicio', 'idx_pedidos_lineas_servicio');

            $table->foreign('id_contexto', 'fk_pedidos_lineas_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_pedido', 'id_contexto'], 'fk_pedidos_lineas_pedido_contexto')
                ->references(['id_pedido', 'id_contexto'])
                ->on('pedidos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                ['id_tarifario_servicio', 'id_contexto'],
                'fk_pedidos_lineas_tarifario_servicio_contexto'
            )
                ->references(['id_tarifario_servicio', 'id_contexto'])
                ->on('tarifario_servicios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('id_servicio', 'fk_pedidos_lineas_servicio')
                ->references('id_servicio')
                ->on('servicios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('facturas', function (Blueprint $table) {
            $table->bigIncrements('id_factura');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_pedido')->nullable();
            $table->unsignedBigInteger('id_presupuesto')->nullable();
            $table->unsignedBigInteger('id_proyecto');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->unsignedBigInteger('id_contacto_empresa_cliente')->nullable();
            $table->unsignedBigInteger('id_estacion_servicio')->nullable();
            $table->unsignedBigInteger('id_tarifario')->nullable();
            $table->unsignedBigInteger('id_usuario_emisor')->nullable();
            $table->string('numero_factura', 100);
            $table->string('serie', 20)->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('estado', ['emitida', 'enviada', 'cobrada_parcial', 'cobrada', 'vencida', 'anulada'])
                ->default('emitida');
            $table->boolean('autofactura')->default(false);
            $table->string('descripcion_seleccionable', 255)->nullable();
            $table->text('descripcion_libre')->nullable();
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('retencion', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['id_contexto', 'numero_factura'], 'uq_facturas_contexto_numero');
            $table->index('id_contexto', 'idx_facturas_contexto');
            $table->index(['id_factura', 'id_contexto'], 'idx_facturas_id_contexto');
            $table->index(['id_pedido', 'id_contexto'], 'idx_facturas_pedido_contexto');
            $table->index(['id_presupuesto', 'id_contexto'], 'idx_facturas_presupuesto_contexto');
            $table->index(['id_proyecto', 'id_contexto'], 'idx_facturas_proyecto_contexto');
            $table->index(['id_empresa_cliente', 'id_contexto'], 'idx_facturas_empresa_contexto');
            $table->index(['id_contacto_empresa_cliente', 'id_contexto'], 'idx_facturas_contacto_contexto');
            $table->index(['id_estacion_servicio', 'id_contexto'], 'idx_facturas_estacion_contexto');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_facturas_tarifario_contexto');
            $table->index(['id_usuario_emisor', 'id_contexto'], 'idx_facturas_usuario_contexto');

            $table->foreign('id_contexto', 'fk_facturas_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_pedido', 'id_contexto'], 'fk_facturas_pedido_contexto')
                ->references(['id_pedido', 'id_contexto'])
                ->on('pedidos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_presupuesto', 'id_contexto'], 'fk_facturas_presupuesto_contexto')
                ->references(['id_presupuesto', 'id_contexto'])
                ->on('presupuestos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_proyecto', 'id_contexto'], 'fk_facturas_proyecto_contexto')
                ->references(['id_proyecto', 'id_contexto'])
                ->on('proyectos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa_cliente', 'id_contexto'], 'fk_facturas_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_contacto_empresa_cliente', 'id_contexto'], 'fk_facturas_contacto_contexto')
                ->references(['id_contacto_empresa', 'id_contexto'])
                ->on('contactos_empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_estacion_servicio', 'id_contexto'], 'fk_facturas_estacion_contexto')
                ->references(['id_estacion_servicio', 'id_contexto'])
                ->on('estaciones_servicio')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_tarifario', 'id_contexto'], 'fk_facturas_tarifario_contexto')
                ->references(['id_tarifario', 'id_contexto'])
                ->on('tarifarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_usuario_emisor', 'id_contexto'], 'fk_facturas_usuario_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('facturas_lineas', function (Blueprint $table) {
            $table->bigIncrements('id_linea_factura');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_factura');
            $table->unsignedBigInteger('id_tarifario_servicio')->nullable();
            $table->unsignedBigInteger('id_servicio')->nullable();
            $table->unsignedInteger('orden')->default(1);
            $table->string('concepto_seleccionable', 255)->nullable();
            $table->text('concepto_libre')->nullable();
            $table->decimal('cantidad', 14, 3)->default(1);
            $table->decimal('precio_unitario', 14, 2)->default(0);
            $table->decimal('iva_porcentaje', 5, 2)->default(21);
            $table->decimal('total_linea', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['id_contexto', 'id_factura', 'orden'], 'uq_facturas_lineas_contexto_factura_orden');
            $table->index('id_contexto', 'idx_facturas_lineas_contexto');
            $table->index(['id_factura', 'id_contexto'], 'idx_facturas_lineas_factura_contexto');
            $table->index(
                ['id_tarifario_servicio', 'id_contexto'],
                'idx_facturas_lineas_tarifario_servicio_contexto'
            );
            $table->index('id_servicio', 'idx_facturas_lineas_servicio');

            $table->foreign('id_contexto', 'fk_facturas_lineas_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_factura', 'id_contexto'], 'fk_facturas_lineas_factura_contexto')
                ->references(['id_factura', 'id_contexto'])
                ->on('facturas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                ['id_tarifario_servicio', 'id_contexto'],
                'fk_facturas_lineas_tarifario_servicio_contexto'
            )
                ->references(['id_tarifario_servicio', 'id_contexto'])
                ->on('tarifario_servicios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('id_servicio', 'fk_facturas_lineas_servicio')
                ->references('id_servicio')
                ->on('servicios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('cobros', function (Blueprint $table) {
            $table->bigIncrements('id_cobro');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_factura');
            $table->unsignedBigInteger('id_usuario_registro')->nullable();
            $table->date('fecha_cobro');
            $table->decimal('importe', 14, 2);
            $table->enum('metodo_cobro', ['transferencia', 'giro', 'efectivo', 'confirming', 'otro'])
                ->default('transferencia');
            $table->string('referencia', 120)->nullable();
            $table->enum('estado', ['pendiente', 'recibido', 'conciliado', 'devuelto'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('id_contexto', 'idx_cobros_contexto');
            $table->index(['id_factura', 'id_contexto'], 'idx_cobros_factura_contexto');
            $table->index(['id_usuario_registro', 'id_contexto'], 'idx_cobros_usuario_contexto');

            $table->foreign('id_contexto', 'fk_cobros_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_factura', 'id_contexto'], 'fk_cobros_factura_contexto')
                ->references(['id_factura', 'id_contexto'])
                ->on('facturas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_usuario_registro', 'id_contexto'], 'fk_cobros_usuario_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros');
        Schema::dropIfExists('facturas_lineas');
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('pedidos_lineas');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('presupuestos_lineas');
        Schema::dropIfExists('presupuestos');
        Schema::dropIfExists('proyectos_comentarios');
        Schema::dropIfExists('proyectos_workplan');
        Schema::dropIfExists('proyectos');
    }
};

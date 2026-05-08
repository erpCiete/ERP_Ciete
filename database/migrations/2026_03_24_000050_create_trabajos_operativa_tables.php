<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flujo operativo principal: trabajos, pedidos, pedido_items, facturas.
     * Cobros queda disponible como modulo auxiliar/legacy, fuera del flujo vivo CIETE.
     */
    public function up(): void
    {
        Schema::create('trabajos', function (Blueprint $table) {
            $table->bigIncrements('id_trabajo');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->unsignedBigInteger('id_estacion_servicio')->nullable();
            $table->unsignedBigInteger('id_tipo_documento')->nullable();
            $table->unsignedBigInteger('id_tipo_trabajo')->nullable();
            $table->unsignedBigInteger('id_contrato')->nullable();
            $table->unsignedBigInteger('id_tarifario')->nullable();
            $table->unsignedBigInteger('id_responsable_ciete')->nullable();

            // Identificadores
            $table->unsignedInteger('numero_trabajo');
            $table->string('numero_trabajo_operativo', 100)->nullable();
            $table->string('numero_estacion', 30)->nullable();
            $table->string('zona', 10)->nullable();

            // Datos del trabajo
            $table->text('descripcion_trabajo')->nullable();
            $table->date('fecha_encargo')->nullable();
            $table->date('fecha_terminacion')->nullable();
            $table->text('observaciones')->nullable();

            // Campos específicos Repsol
            $table->string('numero_aviso', 100)->nullable();
            $table->string('orden_mantenimiento', 100)->nullable();

            // Campos específicos Moeve
            $table->string('categoria', 100)->nullable();

            // Responsable del cliente
            $table->string('responsable_cliente', 150)->nullable();

            // Estado y workflow
            $table->enum('estado', ['en_curso', 'terminado', 'pendiente_facturar', 'facturado', 'finalizado', 'cancelado'])
                ->default('en_curso');
            $table->boolean('bloqueado_cierre')->default(false);

            $table->timestamps();

            // Indexes
            $table->index('id_contexto', 'idx_trabajos_contexto');
            $table->index(['id_trabajo', 'id_contexto'], 'idx_trabajos_id_contexto');
            $table->index(['id_empresa_cliente', 'id_contexto'], 'idx_trabajos_empresa_contexto');
            $table->index(['id_estacion_servicio', 'id_contexto'], 'idx_trabajos_estacion_contexto');
            $table->index(['id_tipo_documento', 'id_contexto'], 'idx_trabajos_tipo_doc_contexto');
            $table->index(['id_tipo_trabajo', 'id_contexto'], 'idx_trabajos_tipo_trab_contexto');
            $table->index(['id_contrato', 'id_contexto'], 'idx_trabajos_contrato_contexto');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_trabajos_tarifario_contexto');
            $table->index(['id_contexto', 'numero_trabajo_operativo'], 'idx_trabajos_contexto_numero_operativo');
            $table->index(['estado', 'id_contexto'], 'idx_trabajos_estado_contexto');
            $table->index('id_responsable_ciete', 'idx_trabajos_responsable');
            $table->index(['fecha_encargo', 'id_contexto'], 'idx_trabajos_fecha_encargo_contexto');
            $table->unique(['id_contexto', 'id_tipo_documento', 'numero_trabajo'], 'uq_trabajos_ctx_tipodoc_numero');

            // Foreign keys
            $table->foreign('id_contexto', 'fk_trabajos_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa_cliente', 'id_contexto'], 'fk_trabajos_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_estacion_servicio', 'id_contexto'], 'fk_trabajos_estacion_contexto')
                ->references(['id_estacion_servicio', 'id_contexto'])
                ->on('estaciones_servicio')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_tipo_documento', 'id_contexto'], 'fk_trabajos_tipo_doc_contexto')
                ->references(['id_tipo_documento', 'id_contexto'])
                ->on('tipos_documento')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_tipo_trabajo', 'id_contexto'], 'fk_trabajos_tipo_trab_contexto')
                ->references(['id_tipo_trabajo', 'id_contexto'])
                ->on('tipos_trabajo')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_contrato', 'id_contexto'], 'fk_trabajos_contrato_contexto')
                ->references(['id_contrato', 'id_contexto'])
                ->on('contratos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_tarifario', 'id_contexto'], 'fk_trabajos_tarifario_contexto')
                ->references(['id_tarifario', 'id_contexto'])
                ->on('tarifarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Simple FKs for user references (users may belong to different context)
            $table->foreign('id_responsable_ciete', 'fk_trabajos_responsable_ciete')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->bigIncrements('id_pedido');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_tarifario')->nullable();

            $table->string('numero_pedido', 100);
            $table->date('fecha_solicitud')->nullable();
            $table->date('fecha_recepcion')->nullable();

            // Importes
            $table->decimal('importe_pedido', 14, 2)->default(0.00);
            $table->decimal('importe_solicitado', 14, 2)->nullable();
            $table->decimal('importe_facturado', 14, 2)->nullable();

            // Unidades
            $table->decimal('unidades_pedido', 14, 3)->default(1.000);
            $table->decimal('unidades_solicitadas', 14, 3)->nullable();

            // Estado
            $table->enum('estado', [
                'pendiente',
                'solicitado',
                'recibido',
                'en_ejecucion',
                'facturado_parcial',
                'facturado',
                'cancelado',
                'anulado',
            ])->default('pendiente');

            // Campos de control (Repsol)
            $table->boolean('pedido_completo')->nullable();
            $table->boolean('tiene_mas_de_1_item')->nullable();
            $table->boolean('facturado_completo')->nullable();

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('id_contexto', 'idx_pedidos_contexto');
            $table->index(['id_pedido', 'id_contexto'], 'idx_pedidos_id_contexto');
            $table->index(['id_trabajo', 'id_contexto'], 'idx_pedidos_trabajo_contexto');
            $table->index(['numero_pedido', 'id_contexto'], 'idx_pedidos_numero_contexto');
            $table->index(['estado', 'id_contexto'], 'idx_pedidos_estado_contexto');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_pedidos_tarifario_contexto');

            $table->foreign('id_contexto', 'fk_pedidos_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_trabajo', 'id_contexto'], 'fk_pedidos_trabajo_contexto')
                ->references(['id_trabajo', 'id_contexto'])
                ->on('trabajos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_tarifario', 'id_contexto'], 'fk_pedidos_tarifario_contexto')
                ->references(['id_tarifario', 'id_contexto'])
                ->on('tarifarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('pedido_items', function (Blueprint $table) {
            $table->bigIncrements('id_pedido_item');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_tarifario_linea')->nullable();

            $table->string('codigo_servicio', 30)->nullable();
            $table->string('numero_tarifa', 30)->nullable();
            $table->string('descripcion_servicio', 255)->nullable();
            $table->decimal('precio_unitario', 14, 2)->default(0.00);
            $table->decimal('cantidad', 14, 3)->default(1.000);
            $table->decimal('total_linea', 14, 2)->default(0.00);

            $table->timestamps();

            $table->index('id_contexto', 'idx_pedido_items_contexto');
            $table->index(['id_pedido', 'id_contexto'], 'idx_pedido_items_pedido_contexto');
            $table->index(['id_tarifario_linea', 'id_contexto'], 'idx_pedido_items_tarifa_contexto');

            $table->foreign('id_contexto', 'fk_pedido_items_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_pedido', 'id_contexto'], 'fk_pedido_items_pedido_contexto')
                ->references(['id_pedido', 'id_contexto'])
                ->on('pedidos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_tarifario_linea', 'id_contexto'], 'fk_pedido_items_tarifa_contexto')
                ->references(['id_tarifario_linea', 'id_contexto'])
                ->on('tarifario_lineas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('facturas', function (Blueprint $table) {
            $table->bigIncrements('id_factura');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_trabajo')->nullable();
            $table->unsignedBigInteger('id_contrato')->nullable();
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->unsignedBigInteger('id_empresa_facturadora')->nullable();

            // Identificación
            $table->string('numero_factura', 100)->nullable();
            $table->string('numero_factura_ccp', 100)->nullable();
            $table->string('serie', 20)->nullable();
            $table->tinyInteger('orden_factura')->unsigned()->default(1);

            // Fechas
            $table->date('fecha_solicitud')->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();

            // Importes
            $table->decimal('importe', 14, 2)->default(0.00);
            $table->decimal('base_imponible', 14, 2)->nullable();
            $table->decimal('iva', 14, 2)->nullable();
            $table->decimal('retencion', 14, 2)->nullable();
            $table->decimal('total', 14, 2)->nullable();

            // Estado
            $table->enum('estado', [
                'pendiente',
                'solicitada',
                'emitida',
                'enviada',
                'anulada',
            ])->default('pendiente');
            $table->boolean('autofactura')->default(false);

            // Sociedad (Moeve)
            $table->string('sociedad', 180)->nullable();

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('id_contexto', 'idx_facturas_contexto');
            $table->index(['id_factura', 'id_contexto'], 'idx_facturas_id_contexto');
            $table->index(['id_trabajo', 'id_contexto'], 'idx_facturas_trabajo_contexto');
            $table->index(['id_contrato', 'id_contexto'], 'idx_facturas_contrato_contexto');
            $table->index(['id_empresa_cliente', 'id_contexto'], 'idx_facturas_empresa_contexto');
            $table->index(['id_empresa_facturadora', 'id_contexto'], 'idx_facturas_empresa_facturadora_contexto');
            $table->index(['numero_factura', 'id_contexto'], 'idx_facturas_numero_contexto');
            $table->index(['estado', 'id_contexto'], 'idx_facturas_estado_contexto');
            $table->index(['fecha_emision', 'id_contexto'], 'idx_facturas_fecha_contexto');
            $table->unique(['id_contexto', 'id_empresa_facturadora', 'numero_factura'], 'uq_facturas_ctx_facturadora_numero');

            $table->foreign('id_contexto', 'fk_facturas_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_trabajo', 'id_contexto'], 'fk_facturas_trabajo_contexto')
                ->references(['id_trabajo', 'id_contexto'])
                ->on('trabajos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_contrato', 'id_contexto'], 'fk_facturas_contrato_contexto')
                ->references(['id_contrato', 'id_contexto'])
                ->on('contratos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa_cliente', 'id_contexto'], 'fk_facturas_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa_facturadora', 'id_contexto'], 'fk_facturas_empresa_facturadora_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
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

            $table->foreign('id_usuario_registro', 'fk_cobros_usuario_registro')
                ->references('id_usuario')
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
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('pedido_items');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('trabajos');
    }
};

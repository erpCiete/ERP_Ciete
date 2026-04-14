<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Presupuestos y presupuesto_lineas (referencia a trabajos en vez de proyectos).
     */
    public function up(): void
    {
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->bigIncrements('id_presupuesto');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->unsignedBigInteger('id_contacto_empresa_cliente')->nullable();
            $table->unsignedBigInteger('id_estacion_servicio')->nullable();
            $table->unsignedBigInteger('id_tarifario')->nullable();
            $table->unsignedBigInteger('id_usuario_responsable')->nullable();
            $table->unsignedBigInteger('id_usuario_cierre')->nullable();
            $table->string('codigo_presupuesto', 60);
            $table->string('nombre_presupuesto', 200)->nullable();
            $table->enum('estado', ['borrador', 'enviado', 'aprobado', 'rechazado', 'anulado'])
                ->default('borrador');
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_validez')->nullable();
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('retencion', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['id_contexto', 'codigo_presupuesto'], 'uq_presupuestos_contexto_codigo');
            $table->index('id_contexto', 'idx_presupuestos_contexto');
            $table->index(['id_presupuesto', 'id_contexto'], 'idx_presupuestos_id_contexto');
            $table->index(['id_trabajo', 'id_contexto'], 'idx_presupuestos_trabajo_contexto');
            $table->index(['id_empresa_cliente', 'id_contexto'], 'idx_presupuestos_empresa_contexto');
            $table->index(['id_estacion_servicio', 'id_contexto'], 'idx_presupuestos_estacion_contexto');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_presupuestos_tarifario_contexto');

            $table->foreign('id_contexto', 'fk_presupuestos_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_trabajo', 'id_contexto'], 'fk_presupuestos_trabajo_contexto')
                ->references(['id_trabajo', 'id_contexto'])
                ->on('trabajos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

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

            $table->foreign('id_usuario_responsable', 'fk_presupuestos_usuario_responsable')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('id_usuario_cierre', 'fk_presupuestos_usuario_cierre')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('presupuesto_lineas', function (Blueprint $table) {
            $table->bigIncrements('id_linea_presupuesto');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_presupuesto');
            $table->unsignedBigInteger('id_tarifario_linea')->nullable();
            $table->unsignedInteger('orden')->default(1);
            $table->string('concepto_seleccionable', 255)->nullable();
            $table->text('concepto_libre')->nullable();
            $table->decimal('cantidad', 14, 3)->default(1);
            $table->decimal('precio_unitario', 14, 2)->default(0);
            $table->decimal('iva_porcentaje', 5, 2)->default(21);
            $table->decimal('total_linea', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['id_contexto', 'id_presupuesto', 'orden'],
                'uq_presupuesto_lineas_contexto_presup_orden'
            );
            $table->index('id_contexto', 'idx_presupuesto_lineas_contexto');
            $table->index(['id_presupuesto', 'id_contexto'], 'idx_presupuesto_lineas_presup_contexto');
            $table->index(
                ['id_tarifario_linea', 'id_contexto'],
                'idx_presupuesto_lineas_tarifa_contexto'
            );

            $table->foreign('id_contexto', 'fk_presupuesto_lineas_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(
                ['id_presupuesto', 'id_contexto'],
                'fk_presupuesto_lineas_presup_contexto'
            )
                ->references(['id_presupuesto', 'id_contexto'])
                ->on('presupuestos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                ['id_tarifario_linea', 'id_contexto'],
                'fk_presupuesto_lineas_tarifa_contexto'
            )
                ->references(['id_tarifario_linea', 'id_contexto'])
                ->on('tarifario_lineas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_lineas');
        Schema::dropIfExists('presupuestos');
    }
};

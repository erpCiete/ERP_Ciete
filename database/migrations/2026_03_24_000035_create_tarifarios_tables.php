<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contratos, tarifarios y tarifario_lineas.
     * Contratos necesita FK a empresas (000020), por eso va aquí y no en 000015.
     */
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->bigIncrements('id_contrato');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->string('codigo_contrato', 100);
            $table->string('nombre', 180)->nullable();
            $table->enum('tipo', ['marco', 'directo', 'otro'])->default('marco');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['vigente', 'expirado', 'cancelado'])->default('vigente');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_contexto', 'codigo_contrato'], 'uq_contratos_ctx_codigo');
            $table->index('id_contexto', 'idx_contratos_contexto');
            $table->index(['id_contrato', 'id_contexto'], 'idx_contratos_id_contexto');
            $table->index(['id_empresa_cliente', 'id_contexto'], 'idx_contratos_empresa_contexto');

            $table->foreign('id_contexto', 'fk_contratos_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa_cliente', 'id_contexto'], 'fk_contratos_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('tarifarios', function (Blueprint $table) {
            $table->bigIncrements('id_tarifario');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_contrato')->nullable();
            $table->string('nombre', 160);
            $table->string('version', 40)->nullable();
            $table->date('fecha_inicio_vigencia')->nullable();
            $table->date('fecha_fin_vigencia')->nullable();
            $table->decimal('factor_multiplicador', 6, 4)->default(1.0000);
            $table->char('moneda', 3)->default('EUR');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_contexto', 'nombre', 'version'], 'uq_tarifarios_ctx_nombre_version');
            $table->index('id_contexto', 'idx_tarifarios_contexto');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_tarifarios_id_contexto');
            $table->index(['id_contrato', 'id_contexto'], 'idx_tarifarios_contrato_contexto');

            $table->foreign('id_contexto', 'fk_tarifarios_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_contrato', 'id_contexto'], 'fk_tarifarios_contrato_contexto')
                ->references(['id_contrato', 'id_contexto'])
                ->on('contratos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('tarifario_lineas', function (Blueprint $table) {
            $table->bigIncrements('id_tarifario_linea');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_tarifario');
            $table->string('codigo_tarifa', 30);
            $table->string('grupo', 120)->nullable();
            $table->string('actuacion', 255);
            $table->text('descripcion')->nullable();
            $table->decimal('tarifa_anterior', 14, 2)->nullable();
            $table->decimal('tarifa_base', 14, 2);
            $table->decimal('tarifa_aplicada', 14, 2);
            $table->unsignedBigInteger('id_unidad')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_contexto', 'id_tarifario', 'codigo_tarifa'], 'uq_tarifa_linea_ctx_tarif_codigo');
            $table->index('id_contexto', 'idx_tarifario_lineas_contexto');
            $table->index(['id_tarifario_linea', 'id_contexto'], 'idx_tarifario_lineas_id_contexto');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_tarifario_lineas_tarifario_contexto');

            $table->foreign('id_contexto', 'fk_tarifario_lineas_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_tarifario', 'id_contexto'], 'fk_tarifario_lineas_tarifario_contexto')
                ->references(['id_tarifario', 'id_contexto'])
                ->on('tarifarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_unidad', 'fk_tarifario_lineas_unidad')
                ->references('id_unidad')
                ->on('unidades')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifario_lineas');
        Schema::dropIfExists('tarifarios');
        Schema::dropIfExists('contratos');
    }
};

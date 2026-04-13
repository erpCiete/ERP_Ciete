<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas maestras: unidades, tipos_documento, tipos_trabajo.
     * Contratos se crean en 000035 (necesita FK a empresas de 000020).
     */
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->bigIncrements('id_unidad');
            $table->string('nombre', 60);
            $table->string('abreviatura', 10);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('abreviatura', 'uq_unidades_abreviatura');
        });

        Schema::create('tipos_documento', function (Blueprint $table) {
            $table->bigIncrements('id_tipo_documento');
            $table->unsignedBigInteger('id_contexto');
            $table->string('codigo', 30);
            $table->string('nombre', 120);
            $table->boolean('tiene_doble_factura')->default(false);
            $table->boolean('tiene_orden_mto')->default(false);
            $table->boolean('tiene_num_tarifa')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_contexto', 'codigo'], 'uq_tipo_doc_contexto_codigo');
            $table->index('id_contexto', 'idx_tipos_documento_contexto');
            $table->index(['id_tipo_documento', 'id_contexto'], 'idx_tipos_documento_id_contexto');

            $table->foreign('id_contexto', 'fk_tipos_documento_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('tipos_trabajo', function (Blueprint $table) {
            $table->bigIncrements('id_tipo_trabajo');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_tipo_documento');
            $table->string('codigo', 80);
            $table->string('nombre', 180);
            $table->string('responsable_ciete_defecto', 150)->nullable();
            $table->string('responsable_cliente_defecto', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_contexto', 'id_tipo_documento', 'codigo'], 'uq_tipo_trabajo_ctx_doc_codigo');
            $table->index('id_contexto', 'idx_tipos_trabajo_contexto');
            $table->index(['id_tipo_documento', 'id_contexto'], 'idx_tipos_trabajo_tipo_doc_contexto');
            $table->index(['id_tipo_trabajo', 'id_contexto'], 'idx_tipos_trabajo_id_contexto');

            $table->foreign('id_contexto', 'fk_tipos_trabajo_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_tipo_documento', 'id_contexto'], 'fk_tipos_trabajo_tipo_doc_contexto')
                ->references(['id_tipo_documento', 'id_contexto'])
                ->on('tipos_documento')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_trabajo');
        Schema::dropIfExists('tipos_documento');
        Schema::dropIfExists('unidades');
    }
};

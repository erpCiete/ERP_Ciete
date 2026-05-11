<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla pivot aditiva: sociedades/CIF autorizadas para facturar por contrato.
 *
 * Permite declarar explícitamente qué empresas (entidades fiscales/CIF) están
 * autorizadas para emitir facturas bajo un contrato concreto.
 *
 * Diseño P0-02. Migración aditiva y reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contrato_empresas_facturadoras')) {
            return;
        }

        Schema::create('contrato_empresas_facturadoras', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_contrato');
            $table->unsignedBigInteger('id_empresa');
            $table->unsignedBigInteger('id_contexto');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Unique: un contrato no puede tener la misma empresa dos veces en el mismo contexto
            $table->unique(
                ['id_contrato', 'id_empresa', 'id_contexto'],
                'uq_cef_contrato_empresa_contexto'
            );

            $table->index('id_contrato', 'idx_cef_contrato');
            $table->index(['id_empresa', 'id_contexto'], 'idx_cef_empresa_contexto');
            $table->index('id_contexto', 'idx_cef_contexto');

            // FK a contratos
            $table->foreign('id_contrato', 'fk_cef_contrato')
                ->references('id_contrato')
                ->on('contratos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // FK compuesta a empresas (id_empresa + id_contexto)
            $table->foreign(['id_empresa', 'id_contexto'], 'fk_cef_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // FK a contextos_cliente
            $table->foreign('id_contexto', 'fk_cef_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_empresas_facturadoras');
    }
};

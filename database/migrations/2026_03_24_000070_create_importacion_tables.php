<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sistema de importación: registro de cargas + staging de filas.
     */
    public function up(): void
    {
        Schema::create('importaciones', function (Blueprint $table) {
            $table->bigIncrements('id_importacion');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_usuario');
            $table->enum('tipo', ['estaciones', 'trabajos', 'tarifario', 'facturas']);
            $table->string('archivo_original', 255);
            $table->unsignedInteger('total_filas')->default(0);
            $table->unsignedInteger('filas_importadas')->default(0);
            $table->unsignedInteger('filas_con_error')->default(0);
            $table->unsignedInteger('filas_duplicadas')->default(0);
            $table->enum('estado', ['subido', 'validando', 'validado', 'importando', 'completado', 'fallido'])
                ->default('subido');
            $table->unsignedInteger('version_importacion')->default(1);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->timestamps();

            $table->index('id_contexto', 'idx_importaciones_contexto');
            $table->index(['id_usuario'], 'idx_importaciones_usuario');

            $table->foreign('id_contexto', 'fk_importaciones_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('id_usuario', 'fk_importaciones_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('importacion_filas', function (Blueprint $table) {
            $table->bigIncrements('id_importacion_fila');
            $table->unsignedBigInteger('id_importacion');
            $table->unsignedInteger('numero_fila');
            $table->json('datos_json');
            $table->enum('estado', ['pendiente', 'valido', 'error', 'duplicado', 'importado'])
                ->default('pendiente');
            $table->text('mensaje_error')->nullable();
            $table->unsignedBigInteger('id_registro_destino')->nullable();
            $table->string('tipo_registro_destino', 50)->nullable();
            $table->timestamps();

            $table->index(['id_importacion', 'estado'], 'idx_import_filas_importacion_estado');

            $table->foreign('id_importacion', 'fk_import_filas_importacion')
                ->references('id_importacion')
                ->on('importaciones')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importacion_filas');
        Schema::dropIfExists('importaciones');
    }
};

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
        Schema::create('legalizaciones', function (Blueprint $table) {
            $table->bigIncrements('id_legalizacion');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_proyecto');
            $table->unsignedBigInteger('id_usuario_responsable')->nullable();
            $table->string('tipo_legalizacion', 150);
            $table->string('numero_expediente', 120)->nullable();
            $table->string('organismo', 180)->nullable();
            $table->enum('estado', ['pendiente', 'en_tramite', 'resuelta', 'cancelada'])->default('pendiente');
            $table->string('descripcion_seleccionable', 255)->nullable();
            $table->text('descripcion_libre')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_limite')->nullable();
            $table->date('fecha_resolucion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['id_contexto', 'numero_expediente'], 'uq_legalizaciones_contexto_num_expediente');
            $table->index('id_contexto', 'idx_legalizaciones_contexto');
            $table->index(['id_legalizacion', 'id_contexto'], 'idx_legalizaciones_id_contexto');
            $table->index(['id_proyecto', 'id_contexto'], 'idx_legalizaciones_proyecto_contexto');
            $table->index(['id_usuario_responsable', 'id_contexto'], 'idx_legalizaciones_usuario_contexto');

            $table->foreign('id_contexto', 'fk_legalizaciones_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_proyecto', 'id_contexto'], 'fk_legalizaciones_proyecto_contexto')
                ->references(['id_proyecto', 'id_contexto'])
                ->on('proyectos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_usuario_responsable', 'id_contexto'], 'fk_legalizaciones_usuario_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('legalizaciones_contactos', function (Blueprint $table) {
            $table->bigIncrements('id_legalizacion_contacto');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_legalizacion');
            $table->unsignedBigInteger('id_contacto_empresa');
            $table->string('rol_en_legalizacion', 150)->nullable();
            $table->boolean('principal')->default(false);
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->unique(
                ['id_contexto', 'id_legalizacion', 'id_contacto_empresa'],
                'uq_legalizaciones_contactos'
            );
            $table->index('id_contexto', 'idx_legalizaciones_contactos_contexto');
            $table->index(
                ['id_legalizacion', 'id_contexto'],
                'idx_legalizaciones_contactos_legalizacion_contexto'
            );
            $table->index(
                ['id_contacto_empresa', 'id_contexto'],
                'idx_legalizaciones_contactos_contacto_contexto'
            );

            $table->foreign('id_contexto', 'fk_legalizaciones_contactos_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(
                ['id_legalizacion', 'id_contexto'],
                'fk_legalizaciones_contactos_legalizacion_contexto'
            )
                ->references(['id_legalizacion', 'id_contexto'])
                ->on('legalizaciones')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                ['id_contacto_empresa', 'id_contexto'],
                'fk_legalizaciones_contactos_contacto_contexto'
            )
                ->references(['id_contacto_empresa', 'id_contexto'])
                ->on('contactos_empresas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('comentarios_legalizaciones', function (Blueprint $table) {
            $table->bigIncrements('id_comentario_legalizacion');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_legalizacion');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->dateTime('fecha_comentario')->useCurrent();
            $table->text('comentario');
            $table->timestamps();

            $table->index('id_contexto', 'idx_comentarios_legalizaciones_contexto');
            $table->index(
                ['id_legalizacion', 'id_contexto'],
                'idx_comentarios_legalizaciones_legalizacion_contexto'
            );
            $table->index(['id_usuario', 'id_contexto'], 'idx_comentarios_legalizaciones_usuario_contexto');

            $table->foreign('id_contexto', 'fk_comentarios_legalizaciones_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(
                ['id_legalizacion', 'id_contexto'],
                'fk_comentarios_legalizaciones_legalizacion_contexto'
            )
                ->references(['id_legalizacion', 'id_contexto'])
                ->on('legalizaciones')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(
                ['id_usuario', 'id_contexto'],
                'fk_comentarios_legalizaciones_usuario_contexto'
            )
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
        Schema::dropIfExists('comentarios_legalizaciones');
        Schema::dropIfExists('legalizaciones_contactos');
        Schema::dropIfExists('legalizaciones');
    }
};

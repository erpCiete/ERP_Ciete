<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_soporte', function (Blueprint $table) {
            $table->bigIncrements('id_solicitud_soporte');
            $table->unsignedBigInteger('id_usuario_solicitante');
            $table->unsignedBigInteger('id_usuario_asignado')->nullable();
            $table->string('tema', 100);
            $table->string('asunto', 255);
            $table->string('estado', 20)->default('pending');
            $table->string('prioridad', 20)->default('normal');
            $table->dateTime('ultimo_mensaje_at')->nullable();
            $table->dateTime('resuelta_at')->nullable();
            $table->dateTime('archivada_at')->nullable();
            $table->timestamps();

            $table->index('id_usuario_solicitante', 'idx_soporte_solicitante');
            $table->index('id_usuario_asignado', 'idx_soporte_asignado');
            $table->index('estado', 'idx_soporte_estado');
            $table->index('prioridad', 'idx_soporte_prioridad');
            $table->index('ultimo_mensaje_at', 'idx_soporte_ultimo_mensaje');

            $table->foreign('id_usuario_solicitante', 'fk_soporte_solicitante')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_usuario_asignado', 'fk_soporte_asignado')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::create('comentarios_soporte', function (Blueprint $table) {
            $table->bigIncrements('id_comentario_soporte');
            $table->unsignedBigInteger('id_solicitud_soporte');
            $table->unsignedBigInteger('id_usuario');
            $table->string('tipo_autor', 20)->default('user');
            $table->text('mensaje');
            $table->timestamps();

            $table->index('id_solicitud_soporte', 'idx_comentarios_soporte_ticket');
            $table->index('id_usuario', 'idx_comentarios_soporte_usuario');
            $table->index('created_at', 'idx_comentarios_soporte_fecha');

            $table->foreign('id_solicitud_soporte', 'fk_comentarios_soporte_ticket')
                ->references('id_solicitud_soporte')
                ->on('solicitudes_soporte')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_usuario', 'fk_comentarios_soporte_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('mensajes_internos', function (Blueprint $table) {
            $table->string('tipo_remitente', 20)->default('user')->after('id_destinatario');
            $table->unsignedBigInteger('id_mensaje_padre')->nullable()->after('id_destinatario');
            $table->unsignedBigInteger('id_solicitud_soporte')->nullable()->after('id_mensaje_padre');

            $table->index('tipo_remitente', 'idx_mensajes_tipo_remitente');
            $table->index('id_mensaje_padre', 'idx_mensajes_padre');
            $table->index('id_solicitud_soporte', 'idx_mensajes_soporte');

            $table->foreign('id_mensaje_padre', 'fk_mensajes_padre')
                ->references('id_mensaje')
                ->on('mensajes_internos')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('id_solicitud_soporte', 'fk_mensajes_solicitud_soporte')
                ->references('id_solicitud_soporte')
                ->on('solicitudes_soporte')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mensajes_internos', function (Blueprint $table) {
            $table->dropForeign('fk_mensajes_padre');
            $table->dropForeign('fk_mensajes_solicitud_soporte');
            $table->dropIndex('idx_mensajes_tipo_remitente');
            $table->dropIndex('idx_mensajes_padre');
            $table->dropIndex('idx_mensajes_soporte');
            $table->dropColumn(['tipo_remitente', 'id_mensaje_padre', 'id_solicitud_soporte']);
        });

        Schema::dropIfExists('comentarios_soporte');
        Schema::dropIfExists('solicitudes_soporte');
    }
};

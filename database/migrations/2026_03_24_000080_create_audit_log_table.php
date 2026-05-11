<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de auditoría para trazabilidad de cambios.
     */
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->bigIncrements('id_audit');
            $table->unsignedBigInteger('id_contexto')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->enum('accion', [
                'crear',
                'editar',
                'actualizar',
                'eliminar',
                'cerrar',
                'reabrir',
                'activar',
                'desactivar',
                'cambiar_estado',
                'importar',
                'exportar',
                'limpiar_logs',
                'cambiar_contexto',
            ]);
            $table->string('tabla', 80);
            $table->string('modulo', 80)->nullable();
            $table->string('entity_type', 120)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->string('campo', 120)->nullable();
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tabla', 'registro_id'], 'idx_audit_tabla_registro');
            $table->index('id_usuario', 'idx_audit_usuario');
            $table->index('id_contexto', 'idx_audit_contexto');
            $table->index('accion', 'idx_audit_accion');
            $table->index('created_at', 'idx_audit_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};

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
            $table->enum('accion', ['crear', 'editar', 'eliminar', 'cerrar', 'reabrir', 'importar']);
            $table->string('tabla', 80);
            $table->unsignedBigInteger('registro_id');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tabla', 'registro_id'], 'idx_audit_tabla_registro');
            $table->index('id_usuario', 'idx_audit_usuario');
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('email_recuperacion', 180)->nullable()->after('email');
        });

        Schema::create('mensajes_internos', function (Blueprint $table) {
            $table->bigIncrements('id_mensaje');
            $table->unsignedBigInteger('id_remitente');
            $table->unsignedBigInteger('id_destinatario')->nullable();
            $table->string('asunto', 255);
            $table->text('cuerpo');
            $table->enum('prioridad', ['normal', 'alta', 'urgente'])->default('normal');
            $table->boolean('es_aviso_sistema')->default(false);
            $table->dateTime('leido_at')->nullable();
            $table->boolean('archivado')->default(false);
            $table->timestamps();

            $table->index('id_remitente', 'idx_mensajes_remitente');
            $table->index('id_destinatario', 'idx_mensajes_destinatario');
            $table->index('leido_at', 'idx_mensajes_leido');
            $table->index('created_at', 'idx_mensajes_fecha');

            $table->foreign('id_remitente', 'fk_mensajes_remitente')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_destinatario', 'fk_mensajes_destinatario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_internos');

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('email_recuperacion');
        });
    }
};

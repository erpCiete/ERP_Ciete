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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->bigIncrements('id_usuario');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_contacto_empresa')->nullable();
            $table->string('nombre', 100);
            $table->string('apellidos', 150)->nullable();
            $table->string('nombre_usuario', 100);
            $table->string('email', 180);
            $table->dateTime('email_verificado_at')->nullable();
            $table->string('password', 255);
            $table->string('telefono', 30)->nullable();
            $table->string('avatar_key', 32)->default('avatar-ciete-logo');
            $table->rememberToken();
            $table->dateTime('ultimo_login_at')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_contexto', 'nombre_usuario'], 'uq_usuarios_contexto_nombre_usuario');
            $table->unique('email', 'uq_usuarios_email');
            $table->index('id_contexto', 'idx_usuarios_contexto');
            $table->index(['id_usuario', 'id_contexto'], 'idx_usuarios_id_contexto');
            $table->index(['id_contacto_empresa', 'id_contexto'], 'idx_usuarios_contacto_empresa_contexto');

            $table->foreign('id_contexto', 'fk_usuarios_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_contacto_empresa', 'id_contexto'], 'fk_usuarios_contacto_empresa_contexto')
                ->references(['id_contacto_empresa', 'id_contexto'])
                ->on('contactos_empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('usuario_roles', function (Blueprint $table) {
            $table->bigIncrements('id_usuario_rol');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['id_usuario', 'id_rol'], 'uq_usuario_roles_usuario_rol');
            $table->index('id_rol', 'idx_usuario_roles_rol');

            $table->foreign('id_usuario', 'fk_usuario_roles_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_rol', 'fk_usuario_roles_rol')
                ->references('id_rol')
                ->on('roles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('sesiones_login', function (Blueprint $table) {
            $table->bigIncrements('id_sesion');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_contexto')->nullable();
            $table->dateTime('fecha_hora_login')->useCurrent();
            $table->dateTime('fecha_hora_logout')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('id_usuario', 'idx_sesiones_login_usuario');
            $table->index('id_contexto', 'idx_sesiones_login_contexto');

            $table->foreign('id_usuario', 'fk_sesiones_login_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_contexto', 'fk_sesiones_login_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('usuario_contextos', function (Blueprint $table) {
            $table->bigIncrements('id_usuario_contexto');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_contexto');
            $table->boolean('es_contexto_principal')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['id_usuario', 'id_contexto'], 'uq_usuario_contexto');
            $table->index('id_contexto', 'idx_usuario_contextos_contexto');

            $table->foreign('id_usuario', 'fk_usuario_contextos_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_contexto', 'fk_usuario_contextos_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_contextos');
        Schema::dropIfExists('sesiones_login');
        Schema::dropIfExists('usuario_roles');
        Schema::dropIfExists('usuarios');
    }
};

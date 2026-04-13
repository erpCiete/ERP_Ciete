<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas de comunicación: direcciones, telefonos, emails.
     * (estaciones, unidades, servicios, tarifarios movidos a migraciones propias)
     */
    public function up(): void
    {
        Schema::create('direcciones', function (Blueprint $table) {
            $table->bigIncrements('id_direccion');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_empresa')->nullable();
            $table->unsignedBigInteger('id_contacto')->nullable();
            $table->unsignedBigInteger('id_contacto_empresa')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->enum('tipo', ['fiscal', 'social', 'principal', 'obra', 'facturacion', 'delegacion', 'otra'])
                ->default('principal');
            $table->string('linea1', 255);
            $table->string('linea2', 255)->nullable();
            $table->string('codigo_postal', 20)->nullable();
            $table->string('localidad', 120)->nullable();
            $table->string('provincia', 120)->nullable();
            $table->string('pais', 120)->default('Espana');
            $table->boolean('es_principal')->default(false);
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();

            $table->index('id_contexto', 'idx_direcciones_contexto');
            $table->index(['id_empresa', 'id_contexto'], 'idx_direcciones_empresa_contexto');
            $table->index('id_contacto', 'idx_direcciones_contacto');
            $table->index(['id_contacto_empresa', 'id_contexto'], 'idx_direcciones_contacto_empresa_contexto');
            $table->index(['id_usuario', 'id_contexto'], 'idx_direcciones_usuario_contexto');

            $table->foreign('id_contexto', 'fk_direcciones_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa', 'id_contexto'], 'fk_direcciones_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_contacto', 'fk_direcciones_contacto')
                ->references('id_contacto')
                ->on('contactos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_contacto_empresa', 'id_contexto'], 'fk_direcciones_contacto_empresa_contexto')
                ->references(['id_contacto_empresa', 'id_contexto'])
                ->on('contactos_empresas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_usuario', 'id_contexto'], 'fk_direcciones_usuario_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('telefonos', function (Blueprint $table) {
            $table->bigIncrements('id_telefono');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_empresa')->nullable();
            $table->unsignedBigInteger('id_contacto')->nullable();
            $table->unsignedBigInteger('id_contacto_empresa')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('prefijo', 10)->nullable();
            $table->string('numero', 30);
            $table->enum('tipo', ['fijo', 'movil', 'oficina', 'personal', 'urgencias', 'otro'])->default('movil');
            $table->boolean('es_principal')->default(false);
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();

            $table->index('id_contexto', 'idx_telefonos_contexto');
            $table->index(['id_empresa', 'id_contexto'], 'idx_telefonos_empresa_contexto');
            $table->index('id_contacto', 'idx_telefonos_contacto');
            $table->index(['id_contacto_empresa', 'id_contexto'], 'idx_telefonos_contacto_empresa_contexto');
            $table->index(['id_usuario', 'id_contexto'], 'idx_telefonos_usuario_contexto');

            $table->foreign('id_contexto', 'fk_telefonos_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa', 'id_contexto'], 'fk_telefonos_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_contacto', 'fk_telefonos_contacto')
                ->references('id_contacto')
                ->on('contactos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_contacto_empresa', 'id_contexto'], 'fk_telefonos_contacto_empresa_contexto')
                ->references(['id_contacto_empresa', 'id_contexto'])
                ->on('contactos_empresas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_usuario', 'id_contexto'], 'fk_telefonos_usuario_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('emails', function (Blueprint $table) {
            $table->bigIncrements('id_email');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_empresa')->nullable();
            $table->unsignedBigInteger('id_contacto')->nullable();
            $table->unsignedBigInteger('id_contacto_empresa')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('email', 180);
            $table->enum('tipo', ['personal', 'profesional', 'facturacion', 'avisos', 'tecnico', 'otro'])
                ->default('profesional');
            $table->boolean('es_principal')->default(false);
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();

            $table->index('id_contexto', 'idx_emails_contexto');
            $table->index(['id_empresa', 'id_contexto'], 'idx_emails_empresa_contexto');
            $table->index('id_contacto', 'idx_emails_contacto');
            $table->index(['id_contacto_empresa', 'id_contexto'], 'idx_emails_contacto_empresa_contexto');
            $table->index(['id_usuario', 'id_contexto'], 'idx_emails_usuario_contexto');

            $table->foreign('id_contexto', 'fk_emails_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa', 'id_contexto'], 'fk_emails_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_contacto', 'fk_emails_contacto')
                ->references('id_contacto')
                ->on('contactos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_contacto_empresa', 'id_contexto'], 'fk_emails_contacto_empresa_contexto')
                ->references(['id_contacto_empresa', 'id_contexto'])
                ->on('contactos_empresas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['id_usuario', 'id_contexto'], 'fk_emails_usuario_contexto')
                ->references(['id_usuario', 'id_contexto'])
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
        Schema::dropIfExists('telefonos');
        Schema::dropIfExists('direcciones');
    }
};

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
        Schema::create('empresas', function (Blueprint $table) {
            $table->bigIncrements('id_empresa');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('empresa_padre_id')->nullable();
            $table->string('nombre', 180);
            $table->string('nombre_comercial', 180)->nullable();
            $table->string('razon_social', 220)->nullable();
            $table->string('cif', 20)->nullable();
            $table->enum('tipo_empresa', ['cliente', 'proveedor', 'cliente_proveedor', 'interna', 'otra'])
                ->default('cliente');
            $table->string('web', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_contexto', 'nombre'], 'uq_empresas_contexto_nombre');
            $table->unique(['id_contexto', 'cif'], 'uq_empresas_contexto_cif');
            $table->index('id_contexto', 'idx_empresas_contexto');
            $table->index(['id_empresa', 'id_contexto'], 'idx_empresas_id_contexto');
            $table->index(['empresa_padre_id', 'id_contexto'], 'idx_empresas_padre_contexto');

            $table->foreign('id_contexto', 'fk_empresas_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['empresa_padre_id', 'id_contexto'], 'fk_empresas_padre_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('contactos', function (Blueprint $table) {
            $table->bigIncrements('id_contacto');
            $table->string('nombre', 100);
            $table->string('apellidos', 150)->nullable();
            $table->string('dni', 20)->nullable();
            $table->string('cargo_general', 150)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('dni', 'uq_contactos_dni');
        });

        Schema::create('contactos_empresas', function (Blueprint $table) {
            $table->bigIncrements('id_contacto_empresa');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_empresa');
            $table->unsignedBigInteger('id_contacto');
            $table->string('puesto', 150)->nullable();
            $table->string('categoria', 100)->nullable();
            $table->boolean('es_responsable_principal')->default(false);
            $table->boolean('recibe_avisos')->default(false);
            $table->boolean('recibe_presupuestos')->default(false);
            $table->boolean('recibe_facturas')->default(false);
            $table->boolean('es_usuario')->default(false);
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(
                ['id_contexto', 'id_empresa', 'id_contacto'],
                'uq_contactos_empresas_contexto_empresa_contacto'
            );
            $table->index('id_contexto', 'idx_contactos_empresas_contexto');
            $table->index('id_contacto', 'idx_contactos_empresas_contacto');
            $table->index(['id_contacto_empresa', 'id_contexto'], 'idx_contactos_empresas_id_contexto');
            $table->index(['id_empresa', 'id_contexto'], 'idx_contactos_empresas_empresa_contexto');

            $table->foreign('id_contexto', 'fk_contactos_empresas_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa', 'id_contexto'], 'fk_contactos_empresas_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_contacto', 'fk_contactos_empresas_contacto')
                ->references('id_contacto')
                ->on('contactos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactos_empresas');
        Schema::dropIfExists('contactos');
        Schema::dropIfExists('empresas');
    }
};

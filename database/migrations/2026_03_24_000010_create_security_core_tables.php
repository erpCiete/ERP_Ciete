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
        Schema::create('contextos_cliente', function (Blueprint $table) {
            $table->bigIncrements('id_contexto');
            $table->string('nombre', 50);
            $table->string('codigo', 30);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('nombre', 'uq_contextos_cliente_nombre');
            $table->unique('codigo', 'uq_contextos_cliente_codigo');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id_rol');
            $table->string('nombre', 80);
            $table->string('slug', 80);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('nombre', 'uq_roles_nombre');
            $table->unique('slug', 'uq_roles_slug');
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->bigIncrements('id_permiso');
            $table->string('nombre', 120);
            $table->string('slug', 120);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('slug', 'uq_permisos_slug');
        });

        Schema::create('rol_permisos', function (Blueprint $table) {
            $table->bigIncrements('id_rol_permiso');
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_permiso');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['id_rol', 'id_permiso'], 'uq_rol_permisos');
            $table->index('id_permiso', 'idx_rol_permisos_permiso');

            $table->foreign('id_rol', 'fk_rol_permisos_rol')
                ->references('id_rol')
                ->on('roles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_permiso', 'fk_rol_permisos_permiso')
                ->references('id_permiso')
                ->on('permisos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rol_permisos');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('contextos_cliente');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estaciones de servicio: base común + extensiones por cliente (1:1).
     */
    public function up(): void
    {
        Schema::create('estaciones_servicio', function (Blueprint $table) {
            $table->bigIncrements('id_estacion_servicio');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_empresa_cliente');

            // Campos comunes
            $table->string('codigo_estacion', 80);
            $table->string('nombre', 180);
            $table->string('direccion', 255)->nullable();
            $table->string('codigo_postal', 20)->nullable();
            $table->string('poblacion', 120)->nullable();
            $table->string('provincia', 120)->nullable();
            $table->string('pais', 120)->default('Espana');
            $table->decimal('latitud_wgs84', 11, 8)->nullable();
            $table->decimal('longitud_wgs84', 11, 8)->nullable();
            $table->string('estado', 50)->nullable();
            $table->date('f_baja')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_contexto', 'codigo_estacion'], 'uq_estaciones_ctx_codigo');
            $table->index('id_contexto', 'idx_estaciones_contexto');
            $table->index(['id_estacion_servicio', 'id_contexto'], 'idx_estaciones_id_contexto');
            $table->index(['id_empresa_cliente', 'id_contexto'], 'idx_estaciones_empresa_contexto');

            $table->foreign('id_contexto', 'fk_estaciones_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_empresa_cliente', 'id_contexto'], 'fk_estaciones_empresa_contexto')
                ->references(['id_empresa', 'id_contexto'])
                ->on('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('estaciones_moeve_ext', function (Blueprint $table) {
            $table->unsignedBigInteger('id_estacion_servicio')->primary();
            $table->string('n_margenes', 20)->nullable();
            $table->string('tecnico_gestion', 150)->nullable();
            $table->string('telefono_tecnico', 30)->nullable();
            $table->string('email_tecnico', 180)->nullable();
            $table->string('responsable_gestor', 150)->nullable();
            $table->string('telefono_gestor', 30)->nullable();
            $table->string('telefono_oficina', 30)->nullable();
            $table->string('sede_email', 180)->nullable();
            $table->string('vinculo_1', 255)->nullable();
            $table->string('vinculo_2', 255)->nullable();
            $table->date('f_alta_modificacion')->nullable();
            $table->string('cod_retailgas', 80)->nullable();
            $table->string('cod_sociedad', 80)->nullable();
            $table->string('sociedad', 180)->nullable();
            $table->timestamps();

            $table->foreign('id_estacion_servicio', 'fk_estaciones_moeve_ext_estacion')
                ->references('id_estacion_servicio')
                ->on('estaciones_servicio')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('estaciones_repsol_ext', function (Blueprint $table) {
            $table->unsignedBigInteger('id_estacion_servicio')->primary();
            $table->string('codigo_solred', 80)->nullable();
            $table->decimal('litros_21', 14, 0)->nullable();
            $table->decimal('gnas_95_21', 14, 0)->nullable();
            $table->decimal('gnas_98_21', 14, 0)->nullable();
            $table->decimal('gasoleo_a_21', 14, 0)->nullable();
            $table->decimal('eplus10_21', 14, 0)->nullable();
            $table->decimal('glp_21', 14, 0)->nullable();
            $table->decimal('adblue_21', 14, 0)->nullable();
            $table->string('cliente_nombre', 180)->nullable();
            $table->string('nom_encargado', 150)->nullable();
            $table->string('nom_gerente', 150)->nullable();
            $table->string('tfno_instalacion', 30)->nullable();
            $table->string('fax_instalacion', 30)->nullable();
            $table->string('tfno_movil_gerente', 30)->nullable();
            $table->string('tfno_movil_encargado', 30)->nullable();
            $table->char('margen', 1)->nullable();
            $table->string('provincial', 150)->nullable();
            $table->timestamps();

            $table->foreign('id_estacion_servicio', 'fk_estaciones_repsol_ext_estacion')
                ->references('id_estacion_servicio')
                ->on('estaciones_servicio')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estaciones_repsol_ext');
        Schema::dropIfExists('estaciones_moeve_ext');
        Schema::dropIfExists('estaciones_servicio');
    }
};

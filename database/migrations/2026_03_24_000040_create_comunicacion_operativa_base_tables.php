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

        Schema::create('estaciones_servicio', function (Blueprint $table) {
            $table->bigIncrements('id_estacion_servicio');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->string('nombre', 180);
            $table->string('codigo_estacion_interno', 80)->nullable();
            $table->string('cod_repsol', 80)->nullable();
            $table->string('cod_cepsa', 80)->nullable();
            $table->string('concesion', 120)->nullable();
            $table->string('tipo', 120)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('codigo_postal', 20)->nullable();
            $table->string('poblacion', 120)->nullable();
            $table->string('provincia', 120)->nullable();
            $table->string('pais', 120)->default('Espana');
            $table->decimal('latitud_wgs84', 11, 8)->nullable();
            $table->decimal('longitud_wgs84', 11, 8)->nullable();
            $table->string('delegacion', 150)->nullable();
            $table->string('delegado', 150)->nullable();
            $table->string('tecnico_gestion', 150)->nullable();
            $table->string('telefono_tecnico_gestion', 30)->nullable();
            $table->string('email_tecnico_gestion', 180)->nullable();
            $table->string('responsable_es_gestor', 150)->nullable();
            $table->string('telefono_movil', 30)->nullable();
            $table->string('telefono_oficina', 30)->nullable();
            $table->string('sede', 150)->nullable();
            $table->string('tipo_mantenimiento', 150)->nullable();
            $table->date('f_baja')->nullable();
            $table->string('razon_modificacion', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_contexto', 'cod_repsol'], 'uq_estaciones_contexto_cod_repsol');
            $table->unique(['id_contexto', 'cod_cepsa'], 'uq_estaciones_contexto_cod_cepsa');
            $table->unique(['id_contexto', 'codigo_estacion_interno'], 'uq_estaciones_contexto_codigo_interno');
            $table->index(['id_empresa_cliente', 'id_contexto'], 'idx_estaciones_empresa_contexto');
            $table->index(['id_estacion_servicio', 'id_contexto'], 'idx_estaciones_id_contexto');

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

        Schema::create('unidades', function (Blueprint $table) {
            $table->bigIncrements('id_unidad');
            $table->string('nombre', 100);
            $table->string('abreviatura', 20);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('nombre', 'uq_unidades_nombre');
            $table->unique('abreviatura', 'uq_unidades_abreviatura');
        });

        Schema::create('servicios', function (Blueprint $table) {
            $table->bigIncrements('id_servicio');
            $table->string('codigo', 80);
            $table->string('nombre', 160);
            $table->string('descripcion_seleccionable', 255)->nullable();
            $table->text('descripcion_libre')->nullable();
            $table->unsignedBigInteger('id_unidad');
            $table->enum('tipo_servicio', ['mantenimiento', 'obra', 'legalizacion', 'inspeccion', 'otro'])
                ->default('otro');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('codigo', 'uq_servicios_codigo');
            $table->index('id_unidad', 'idx_servicios_unidad');

            $table->foreign('id_unidad', 'fk_servicios_unidad')
                ->references('id_unidad')
                ->on('unidades')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('tarifarios', function (Blueprint $table) {
            $table->bigIncrements('id_tarifario');
            $table->unsignedBigInteger('id_contexto');
            $table->string('nombre', 160);
            $table->string('version', 40)->nullable();
            $table->date('fecha_inicio_vigencia')->nullable();
            $table->date('fecha_fin_vigencia')->nullable();
            $table->char('moneda', 3)->default('EUR');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['id_contexto', 'nombre', 'version'], 'uq_tarifarios_contexto_nombre_version');
            $table->index('id_contexto', 'idx_tarifarios_contexto');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_tarifarios_id_contexto');

            $table->foreign('id_contexto', 'fk_tarifarios_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('tarifario_servicios', function (Blueprint $table) {
            $table->bigIncrements('id_tarifario_servicio');
            $table->unsignedBigInteger('id_contexto');
            $table->unsignedBigInteger('id_tarifario');
            $table->unsignedBigInteger('id_servicio');
            $table->decimal('precio_unitario', 14, 2)->default(0);
            $table->decimal('iva_porcentaje', 5, 2)->default(21);
            $table->boolean('activo')->default(true);
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->unique(
                ['id_contexto', 'id_tarifario', 'id_servicio'],
                'uq_tarifario_servicios_contexto_tarifario_servicio'
            );
            $table->index('id_contexto', 'idx_tarifario_servicios_contexto');
            $table->index('id_servicio', 'idx_tarifario_servicios_servicio');
            $table->index(['id_tarifario', 'id_contexto'], 'idx_tarifario_servicios_tarifario_contexto');
            $table->index(['id_tarifario_servicio', 'id_contexto'], 'idx_tarifario_servicios_id_contexto');

            $table->foreign('id_contexto', 'fk_tarifario_servicios_contexto')
                ->references('id_contexto')
                ->on('contextos_cliente')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign(['id_tarifario', 'id_contexto'], 'fk_tarifario_servicios_tarifario_contexto')
                ->references(['id_tarifario', 'id_contexto'])
                ->on('tarifarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_servicio', 'fk_tarifario_servicios_servicio')
                ->references('id_servicio')
                ->on('servicios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifario_servicios');
        Schema::dropIfExists('tarifarios');
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('unidades');
        Schema::dropIfExists('estaciones_servicio');
        Schema::dropIfExists('emails');
        Schema::dropIfExists('telefonos');
        Schema::dropIfExists('direcciones');
    }
};

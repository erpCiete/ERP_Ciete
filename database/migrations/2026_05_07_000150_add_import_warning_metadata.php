<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('importaciones', function (Blueprint $table): void {
            $table->unsignedInteger('filas_ignoradas')->default(0)->after('filas_importadas');
            $table->unsignedInteger('filas_con_aviso')->default(0)->after('filas_con_error');
            $table->json('resumen_json')->nullable()->after('finished_at');
        });

        Schema::table('importacion_filas', function (Blueprint $table): void {
            $table->string('archivo_origen')->nullable()->after('id_importacion');
            $table->string('hoja_origen')->nullable()->after('archivo_origen');
            $table->string('resultado', 20)->nullable()->after('estado');
            $table->string('tipo_fila', 50)->nullable()->after('resultado');
            $table->string('severidad', 20)->nullable()->after('tipo_fila');
            $table->string('codigo', 100)->nullable()->after('severidad');
            $table->string('clasificacion', 40)->nullable()->after('codigo');
            $table->text('decision_sugerida')->nullable()->after('mensaje_error');

            $table->index(['id_importacion', 'severidad'], 'idx_import_filas_importacion_severidad');
            $table->index(['id_importacion', 'clasificacion'], 'idx_import_filas_importacion_clasificacion');
            $table->index(['archivo_origen', 'hoja_origen'], 'idx_import_filas_archivo_hoja');
        });
    }

    public function down(): void
    {
        Schema::table('importacion_filas', function (Blueprint $table): void {
            $table->dropIndex('idx_import_filas_importacion_severidad');
            $table->dropIndex('idx_import_filas_importacion_clasificacion');
            $table->dropIndex('idx_import_filas_archivo_hoja');
            $table->dropColumn([
                'archivo_origen',
                'hoja_origen',
                'resultado',
                'tipo_fila',
                'severidad',
                'codigo',
                'clasificacion',
                'decision_sugerida',
            ]);
        });

        Schema::table('importaciones', function (Blueprint $table): void {
            $table->dropColumn([
                'filas_ignoradas',
                'filas_con_aviso',
                'resumen_json',
            ]);
        });
    }
};

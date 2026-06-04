<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarifarios', function (Blueprint $table) {
            $table->boolean('es_predeterminado')
                ->default(false)
                ->after('observaciones');

            $table->index(['id_contrato', 'es_predeterminado'], 'idx_tarifarios_contrato_predeterminado');
        });
    }

    public function down(): void
    {
        Schema::table('tarifarios', function (Blueprint $table) {
            $table->dropIndex('idx_tarifarios_contrato_predeterminado');
            $table->dropColumn('es_predeterminado');
        });
    }
};

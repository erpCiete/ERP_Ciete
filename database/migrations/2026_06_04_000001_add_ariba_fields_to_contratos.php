<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table): void {
            $table->string('ariba_cta_mayor', 120)->nullable()->after('observaciones');
            $table->string('ariba_propuesta_opex', 120)->nullable()->after('ariba_cta_mayor');
            $table->string('ariba_accion_gasto', 120)->nullable()->after('ariba_propuesta_opex');
            $table->string('ariba_nombre_proveedor', 200)->nullable()->after('ariba_accion_gasto');
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table): void {
            $table->dropColumn([
                'ariba_cta_mayor',
                'ariba_propuesta_opex',
                'ariba_accion_gasto',
                'ariba_nombre_proveedor',
            ]);
        });
    }
};

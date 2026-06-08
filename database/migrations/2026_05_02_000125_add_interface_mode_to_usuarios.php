<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->enum('interface_mode', ['ciete_excel', 'ciete_moderno'])
                ->default('ciete_excel')
                ->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'interface_mode')) {
                $table->dropColumn('interface_mode');
            }
        });
    }
};

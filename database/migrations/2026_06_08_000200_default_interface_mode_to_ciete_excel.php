<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuarios') || ! Schema::hasColumn('usuarios', 'interface_mode')) {
            return;
        }

        DB::table('usuarios')
            ->whereNull('interface_mode')
            ->orWhere('interface_mode', 'ciete_moderno')
            ->update(['interface_mode' => 'ciete_excel']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE usuarios MODIFY interface_mode ENUM('ciete_excel','ciete_moderno') NOT NULL DEFAULT 'ciete_excel'"
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('usuarios') || ! Schema::hasColumn('usuarios', 'interface_mode')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE usuarios MODIFY interface_mode ENUM('ciete_excel','ciete_moderno') NOT NULL DEFAULT 'ciete_moderno'"
            );
        }
    }
};

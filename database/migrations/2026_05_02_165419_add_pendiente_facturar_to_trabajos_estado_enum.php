<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Extend the estado ENUM on trabajos to include CIETE workflow states.
     * Estados vivos CIETE: sin borrador ni cerrado legacy.
     */
    public function up(): void
    {
        DB::table('trabajos')->where('estado', 'borrador')->update(['estado' => 'en_curso']);
        DB::table('trabajos')->where('estado', 'cerrado')->update(['estado' => 'finalizado']);

        DB::statement("ALTER TABLE trabajos MODIFY COLUMN estado ENUM('en_curso','terminado','pendiente_facturar','facturado','finalizado','cancelado') NOT NULL DEFAULT 'en_curso'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE trabajos MODIFY COLUMN estado ENUM('en_curso','terminado','finalizado','cancelado') NOT NULL DEFAULT 'en_curso'");
    }
};

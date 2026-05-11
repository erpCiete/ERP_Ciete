<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pedidos')->where('estado', 'borrador')->update(['estado' => 'pendiente']);
        DB::table('pedidos')->where('estado', 'cerrado')->update(['estado' => 'facturado']);

        DB::statement(
            "ALTER TABLE pedidos MODIFY COLUMN estado ENUM(
                'pendiente',
                'solicitado',
                'recibido',
                'en_ejecucion',
                'facturado_parcial',
                'facturado',
                'cancelado',
                'anulado'
            ) DEFAULT 'pendiente'"
        );
    }

    public function down(): void
    {
        DB::table('pedidos')
            ->whereIn('estado', ['cancelado'])
            ->update(['estado' => 'pendiente']);

        DB::statement(
            "ALTER TABLE pedidos MODIFY COLUMN estado ENUM(
                'pendiente',
                'solicitado',
                'recibido',
                'en_ejecucion',
                'facturado_parcial',
                'facturado',
                'anulado'
            ) DEFAULT 'pendiente'"
        );
    }
};

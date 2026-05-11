<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('trabajos')
            ->where('estado', 'borrador')
            ->update(['estado' => 'en_curso']);

        DB::table('trabajos')
            ->where('estado', 'cerrado')
            ->update(['estado' => 'finalizado']);

        DB::statement("ALTER TABLE trabajos MODIFY COLUMN estado ENUM('en_curso','terminado','pendiente_facturar','facturado','finalizado','cancelado') NOT NULL DEFAULT 'en_curso'");

        if (Schema::hasTable('factura_pedidos')) {
            Schema::drop('factura_pedidos');
        }

        if (Schema::hasColumn('trabajos', 'id_usuario_cierre') && $this->hasForeignKey('trabajos', 'fk_trabajos_usuario_cierre')) {
            Schema::table('trabajos', function (Blueprint $table): void {
                $table->dropForeign('fk_trabajos_usuario_cierre');
            });
        }

        Schema::table('trabajos', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('trabajos', 'cerrado') ? 'cerrado' : null,
                Schema::hasColumn('trabajos', 'fecha_cierre') ? 'fecha_cierre' : null,
                Schema::hasColumn('trabajos', 'id_usuario_cierre') ? 'id_usuario_cierre' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        DB::table('pedidos')
            ->where('estado', 'borrador')
            ->update(['estado' => 'pendiente']);

        DB::table('pedidos')
            ->where('estado', 'cerrado')
            ->update(['estado' => 'facturado']);

        DB::statement("ALTER TABLE pedidos MODIFY COLUMN estado ENUM('pendiente','solicitado','recibido','en_ejecucion','facturado_parcial','facturado','cancelado','anulado') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        Schema::table('trabajos', function (Blueprint $table): void {
            if (! Schema::hasColumn('trabajos', 'id_usuario_cierre')) {
                $table->unsignedBigInteger('id_usuario_cierre')->nullable()->after('id_responsable_ciete');
            }

            if (! Schema::hasColumn('trabajos', 'cerrado')) {
                $table->boolean('cerrado')->default(false)->after('estado');
            }

            if (! Schema::hasColumn('trabajos', 'fecha_cierre')) {
                $table->dateTime('fecha_cierre')->nullable()->after('bloqueado_cierre');
            }
        });

        DB::statement("ALTER TABLE trabajos MODIFY COLUMN estado ENUM('borrador','en_curso','terminado','pendiente_facturar','facturado','finalizado','cerrado','cancelado') NOT NULL DEFAULT 'borrador'");

        DB::statement("ALTER TABLE pedidos MODIFY COLUMN estado ENUM('borrador','pendiente','solicitado','recibido','en_ejecucion','facturado_parcial','facturado','cerrado','cancelado','anulado') DEFAULT 'pendiente'");
    }

    private function hasForeignKey(string $table, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->whereRaw('CONSTRAINT_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};

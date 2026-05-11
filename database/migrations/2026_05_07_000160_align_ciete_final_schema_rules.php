<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('trabajos')) {
            Schema::table('trabajos', function (Blueprint $table): void {
                if (! Schema::hasColumn('trabajos', 'numero_trabajo_operativo')) {
                    $table->string('numero_trabajo_operativo', 100)->nullable()->after('numero_trabajo');
                }
            });

            Schema::table('trabajos', function (Blueprint $table): void {
                if (! $this->hasIndex('trabajos', 'idx_trabajos_contexto_numero_operativo')) {
                    $table->index(['id_contexto', 'numero_trabajo_operativo'], 'idx_trabajos_contexto_numero_operativo');
                }
            });
        }

        if (! Schema::hasTable('facturas')) {
            return;
        }

        DB::statement(
            "UPDATE facturas
             SET estado = CASE
                 WHEN numero_factura IS NULL OR TRIM(numero_factura) = '' THEN 'solicitada'
                 ELSE 'enviada'
             END
             WHERE estado IN ('cobrada_parcial', 'cobrada', 'vencida')"
        );

        DB::statement("ALTER TABLE facturas MODIFY COLUMN estado ENUM('pendiente','solicitada','emitida','enviada','anulada') NOT NULL DEFAULT 'pendiente'");

        foreach ([
            'uq_facturas_contexto_numero',
            'facturas_id_contexto_numero_factura_unique',
        ] as $legacyIndex) {
            if ($this->hasIndex('facturas', $legacyIndex)) {
                Schema::table('facturas', function (Blueprint $table) use ($legacyIndex): void {
                    $table->dropIndex($legacyIndex);
                });
            }
        }

        if (! $this->hasIndex('facturas', 'uq_facturas_ctx_facturadora_numero')) {
            $this->assertNoDuplicateInvoiceNumbersForTargetUnique();

            Schema::table('facturas', function (Blueprint $table): void {
                $table->unique(
                    ['id_contexto', 'id_empresa_facturadora', 'numero_factura'],
                    'uq_facturas_ctx_facturadora_numero'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('facturas')) {
            if ($this->hasIndex('facturas', 'uq_facturas_ctx_facturadora_numero')) {
                Schema::table('facturas', function (Blueprint $table): void {
                    $table->dropIndex('uq_facturas_ctx_facturadora_numero');
                });
            }

            DB::statement("ALTER TABLE facturas MODIFY COLUMN estado ENUM('pendiente','solicitada','emitida','enviada','cobrada_parcial','cobrada','vencida','anulada') NOT NULL DEFAULT 'pendiente'");
        }

        if (! Schema::hasTable('trabajos') || ! Schema::hasColumn('trabajos', 'numero_trabajo_operativo')) {
            return;
        }

        Schema::table('trabajos', function (Blueprint $table): void {
            if ($this->hasIndex('trabajos', 'idx_trabajos_contexto_numero_operativo')) {
                $table->dropIndex('idx_trabajos_contexto_numero_operativo');
            }
        });

        Schema::table('trabajos', function (Blueprint $table): void {
            $table->dropColumn('numero_trabajo_operativo');
        });
    }

    private function assertNoDuplicateInvoiceNumbersForTargetUnique(): void
    {
        $duplicate = DB::table('facturas')
            ->select('id_contexto', 'id_empresa_facturadora', 'numero_factura')
            ->whereNotNull('id_empresa_facturadora')
            ->whereNotNull('numero_factura')
            ->whereRaw("TRIM(numero_factura) <> ''")
            ->groupBy('id_contexto', 'id_empresa_facturadora', 'numero_factura')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate !== null) {
            throw new RuntimeException(sprintf(
                'No se puede crear uq_facturas_ctx_facturadora_numero: factura duplicada contexto=%s empresa_facturadora=%s numero=%s.',
                $duplicate->id_contexto,
                $duplicate->id_empresa_facturadora,
                $duplicate->numero_factura
            ));
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }
};

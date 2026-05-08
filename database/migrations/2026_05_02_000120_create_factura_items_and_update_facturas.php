<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add invoice item support and make invoices compatible with multi-source billing.
     */
    public function up(): void
    {
        if ($this->hasForeignKey('facturas', 'fk_facturas_trabajo_contexto')) {
            Schema::table('facturas', function (Blueprint $table) {
                $table->dropForeign('fk_facturas_trabajo_contexto');
            });
        }

        DB::statement('ALTER TABLE facturas MODIFY COLUMN id_trabajo BIGINT UNSIGNED NULL');

        Schema::table('facturas', function (Blueprint $table) {
            if (! Schema::hasColumn('facturas', 'id_contrato')) {
                $table->unsignedBigInteger('id_contrato')->nullable()->after('id_trabajo');
            }

            if (! Schema::hasColumn('facturas', 'id_empresa_facturadora')) {
                $table->unsignedBigInteger('id_empresa_facturadora')->nullable()->after('id_empresa_cliente');
            }
        });

        Schema::table('facturas', function (Blueprint $table) {
            if (! $this->hasIndex('facturas', 'idx_facturas_trabajo_contexto')) {
                $table->index(['id_trabajo', 'id_contexto'], 'idx_facturas_trabajo_contexto');
            }

            if (! $this->hasForeignKey('facturas', 'fk_facturas_trabajo_contexto')) {
                $table->foreign(['id_trabajo', 'id_contexto'], 'fk_facturas_trabajo_contexto')
                    ->references(['id_trabajo', 'id_contexto'])
                    ->on('trabajos')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }

            if (! $this->hasIndex('facturas', 'idx_facturas_contrato_contexto')) {
                $table->index(['id_contrato', 'id_contexto'], 'idx_facturas_contrato_contexto');
            }

            if (! $this->hasForeignKey('facturas', 'fk_facturas_contrato_contexto')) {
                $table->foreign(['id_contrato', 'id_contexto'], 'fk_facturas_contrato_contexto')
                    ->references(['id_contrato', 'id_contexto'])
                    ->on('contratos')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }

            if (! $this->hasIndex('facturas', 'idx_facturas_empresa_facturadora_contexto')) {
                $table->index(['id_empresa_facturadora', 'id_contexto'], 'idx_facturas_empresa_facturadora_contexto');
            }

            if (! $this->hasForeignKey('facturas', 'fk_facturas_empresa_facturadora_contexto')) {
                $table->foreign(['id_empresa_facturadora', 'id_contexto'], 'fk_facturas_empresa_facturadora_contexto')
                    ->references(['id_empresa', 'id_contexto'])
                    ->on('empresas')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        });

        if (! Schema::hasTable('factura_items')) {
            Schema::create('factura_items', function (Blueprint $table) {
                $table->bigIncrements('id_factura_item');
                $table->unsignedBigInteger('id_factura');
                $table->unsignedBigInteger('id_pedido_item');
                $table->decimal('unidades_facturadas', 10, 3)->nullable();
                $table->decimal('importe_facturado', 10, 2)->default(0.00);
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index('id_factura', 'idx_factura_items_factura');
                $table->index('id_pedido_item', 'idx_factura_items_pedido_item');

                $table->foreign('id_factura', 'fk_factura_items_factura')
                    ->references('id_factura')
                    ->on('facturas')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign('id_pedido_item', 'fk_factura_items_pedido_item')
                    ->references('id_pedido_item')
                    ->on('pedido_items')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_items');

        Schema::table('facturas', function (Blueprint $table) {
            if ($this->hasForeignKey('facturas', 'fk_facturas_empresa_facturadora_contexto')) {
                $table->dropForeign('fk_facturas_empresa_facturadora_contexto');
            }

            if ($this->hasIndex('facturas', 'idx_facturas_empresa_facturadora_contexto')) {
                $table->dropIndex('idx_facturas_empresa_facturadora_contexto');
            }

            if ($this->hasForeignKey('facturas', 'fk_facturas_contrato_contexto')) {
                $table->dropForeign('fk_facturas_contrato_contexto');
            }

            if ($this->hasIndex('facturas', 'idx_facturas_contrato_contexto')) {
                $table->dropIndex('idx_facturas_contrato_contexto');
            }

            if ($this->hasForeignKey('facturas', 'fk_facturas_trabajo_contexto')) {
                $table->dropForeign('fk_facturas_trabajo_contexto');
            }
        });

        Schema::table('facturas', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('facturas', 'id_contrato') ? 'id_contrato' : null,
                Schema::hasColumn('facturas', 'id_empresa_facturadora') ? 'id_empresa_facturadora' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        DB::statement('ALTER TABLE facturas MODIFY COLUMN id_trabajo BIGINT UNSIGNED NOT NULL');

        Schema::table('facturas', function (Blueprint $table) {
            if (! $this->hasIndex('facturas', 'idx_facturas_trabajo_contexto')) {
                $table->index(['id_trabajo', 'id_contexto'], 'idx_facturas_trabajo_contexto');
            }

            if (! $this->hasForeignKey('facturas', 'fk_facturas_trabajo_contexto')) {
                $table->foreign(['id_trabajo', 'id_contexto'], 'fk_facturas_trabajo_contexto')
                    ->references(['id_trabajo', 'id_contexto'])
                    ->on('trabajos')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        });
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

    private function hasIndex(string $table, string $index): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }
};

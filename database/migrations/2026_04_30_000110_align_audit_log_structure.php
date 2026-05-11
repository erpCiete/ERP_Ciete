<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_log')) {
            return;
        }

        // Incluye acciones históricas y nuevas para evitar errores con datos existentes.
        DB::statement("ALTER TABLE audit_log MODIFY accion ENUM('crear','editar','actualizar','eliminar','cerrar','reabrir','activar','desactivar','cambiar_estado','importar','exportar','limpiar_logs','cambiar_contexto') NOT NULL");
        DB::statement('ALTER TABLE audit_log MODIFY registro_id BIGINT UNSIGNED NULL');

        Schema::table('audit_log', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_log', 'modulo')) {
                $table->string('modulo', 80)->nullable()->after('tabla');
            }

            if (! Schema::hasColumn('audit_log', 'entity_type')) {
                $table->string('entity_type', 120)->nullable()->after('modulo');
            }

            if (! Schema::hasColumn('audit_log', 'entity_id')) {
                $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
            }

            if (! Schema::hasColumn('audit_log', 'campo')) {
                $table->string('campo', 120)->nullable()->after('registro_id');
            }

            if (! Schema::hasColumn('audit_log', 'valor_anterior')) {
                $table->text('valor_anterior')->nullable()->after('campo');
            }

            if (! Schema::hasColumn('audit_log', 'valor_nuevo')) {
                $table->text('valor_nuevo')->nullable()->after('valor_anterior');
            }

            if (! Schema::hasColumn('audit_log', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('datos_nuevos');
            }

            if (! Schema::hasColumn('audit_log', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('ip');
            }

            if (! Schema::hasColumn('audit_log', 'user_agent')) {
                $table->string('user_agent', 500)->nullable()->after('ip_address');
            }
        });

        DB::statement('UPDATE audit_log SET modulo = tabla WHERE modulo IS NULL AND tabla IS NOT NULL');
        DB::statement('UPDATE audit_log SET entity_id = registro_id WHERE entity_id IS NULL AND registro_id IS NOT NULL');
        DB::statement("UPDATE audit_log SET entity_type = tabla WHERE entity_type IS NULL AND tabla IS NOT NULL AND tabla <> ''");
        DB::statement('UPDATE audit_log SET ip_address = ip WHERE ip_address IS NULL AND ip IS NOT NULL');

        $this->ensureIndex('audit_log', 'idx_audit_contexto', 'CREATE INDEX idx_audit_contexto ON audit_log (id_contexto)');
        $this->ensureIndex('audit_log', 'idx_audit_accion', 'CREATE INDEX idx_audit_accion ON audit_log (accion)');
        $this->ensureIndex('audit_log', 'idx_audit_modulo', 'CREATE INDEX idx_audit_modulo ON audit_log (modulo)');
        $this->ensureIndex('audit_log', 'idx_audit_entity', 'CREATE INDEX idx_audit_entity ON audit_log (entity_type, entity_id)');
    }

    public function down(): void
    {
        if (! Schema::hasTable('audit_log')) {
            return;
        }

        $this->dropIndexIfExists('audit_log', 'idx_audit_entity');
        $this->dropIndexIfExists('audit_log', 'idx_audit_modulo');
        $this->dropIndexIfExists('audit_log', 'idx_audit_accion');
        $this->dropIndexIfExists('audit_log', 'idx_audit_contexto');

        Schema::table('audit_log', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['modulo', 'entity_type', 'entity_id', 'campo', 'valor_anterior', 'valor_nuevo', 'descripcion', 'ip_address', 'user_agent'] as $column) {
                if (Schema::hasColumn('audit_log', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });

        DB::statement("UPDATE audit_log SET accion = 'editar' WHERE accion IN ('actualizar','activar','desactivar','cambiar_estado','exportar','limpiar_logs','cambiar_contexto')");
        DB::statement("ALTER TABLE audit_log MODIFY accion ENUM('crear','editar','eliminar','cerrar','reabrir','importar') NOT NULL");
        DB::statement('UPDATE audit_log SET registro_id = 0 WHERE registro_id IS NULL');
        DB::statement('ALTER TABLE audit_log MODIFY registro_id BIGINT UNSIGNED NOT NULL');
    }

    private function ensureIndex(string $table, string $indexName, string $createStatement): void
    {
        try {
            DB::statement($createStatement);
        } catch (QueryException $exception) {
            if (! str_contains(strtolower($exception->getMessage()), 'duplicate')) {
                throw $exception;
            }
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            DB::statement("DROP INDEX {$indexName} ON {$table}");
        } catch (QueryException $exception) {
            $message = strtolower($exception->getMessage());
            if (! str_contains($message, 'check that column/key exists') && ! str_contains($message, "doesn't exist")) {
                throw $exception;
            }
        }
    }
};

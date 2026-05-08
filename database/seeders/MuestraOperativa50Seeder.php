<?php

namespace Database\Seeders;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MuestraOperativa50Seeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = database_path('manual/2026_05_07_insert_muestra_operativa_50_casos.sql');

        if (! is_file($sqlPath)) {
            throw new RuntimeException("No existe el script SQL de muestra operativa: {$sqlPath}");
        }

        $sql = file_get_contents($sqlPath);

        if ($sql === false) {
            throw new RuntimeException("No se pudo leer el script SQL de muestra operativa: {$sqlPath}");
        }

        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException("MuestraOperativa50Seeder solo soporta MySQL/MariaDB. Driver actual: {$driver}");
        }

        $statements = $this->splitStatements($sql);

        foreach ($statements as $statement) {
            $this->executeStatement($connection, $statement);
        }

        if ($this->command !== null) {
            $this->command->info('Muestra operativa de 50 casos cargada desde SQL manual.');
        }
    }

    /**
     * @return list<string>
     */
    private function splitStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $length = strlen($sql);
        $inSingleQuote = false;
        $inLineComment = false;
        $inBlockComment = false;

        for ($index = 0; $index < $length; $index++) {
            $char = $sql[$index];
            $next = $index + 1 < $length ? $sql[$index + 1] : null;
            $third = $index + 2 < $length ? $sql[$index + 2] : null;

            if ($inLineComment) {
                if ($char === "\n") {
                    $inLineComment = false;
                }

                continue;
            }

            if ($inBlockComment) {
                if ($char === '*' && $next === '/') {
                    $inBlockComment = false;
                    $index++;
                }

                continue;
            }

            if ($inSingleQuote) {
                $buffer .= $char;

                if ($char === '\\' && $next !== null) {
                    $buffer .= $next;
                    $index++;
                    continue;
                }

                if ($char === "'" && $next === "'") {
                    $buffer .= $next;
                    $index++;
                    continue;
                }

                if ($char === "'") {
                    $inSingleQuote = false;
                }

                continue;
            }

            if ($char === '-' && $next === '-' && $third !== null && ctype_space($third)) {
                $inLineComment = true;
                $index++;
                continue;
            }

            if ($char === '/' && $next === '*') {
                $inBlockComment = true;
                $index++;
                continue;
            }

            if ($char === "'") {
                $inSingleQuote = true;
                $buffer .= $char;
                continue;
            }

            if ($char === ';') {
                $statement = trim($buffer);

                if ($statement !== '') {
                    $statements[] = $statement;
                }

                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $statement = trim($buffer);

        if ($statement !== '') {
            $statements[] = $statement;
        }

        return $statements;
    }

    private function executeStatement(ConnectionInterface $connection, string $statement): void
    {
        if (preg_match('/^SELECT\b/i', ltrim($statement)) === 1) {
            $connection->select($statement);

            return;
        }

        $connection->unprepared($statement);
    }
}
# Seeders privados CIETE

Esta carpeta existe para generar **seeders locales con datos reales** a partir de los Excel actualizados de CIETE.

Reglas:

- Los archivos `CieteReal*.php` generados aquí **no se versionan**.
- Solo se conservan en local/demo para reconstruir una base de pruebas con datos reales de MOEVE/REPSOL.
- No guardar aquí dumps SQL, CSV intermedios ni logs completos con datos sensibles.

Flujo recomendado:

1. `php artisan ciete:generate-private-seeders-from-excel --path="docs/Abaco/excelsactualizados" --dry-run`
2. `php artisan ciete:generate-private-seeders-from-excel --path="docs/Abaco/excelsactualizados" --write-seeders`
3. `composer dump-autoload`
4. `php artisan migrate:fresh --seed`
5. `php artisan db:seed --class="Database\\Seeders\\Private\\CieteRealDataSeeder"`

Los seeders privados generados deben mantenerse solo en la máquina local autorizada.

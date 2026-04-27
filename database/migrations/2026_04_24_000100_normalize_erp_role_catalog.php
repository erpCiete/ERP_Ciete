<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $roles = [
            [
                'id_rol' => 1,
                'nombre' => 'Admin',
                'slug' => 'admin',
                'descripcion' => 'Acceso total al ERP',
                'activo' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ],
            [
                'id_rol' => 2,
                'nombre' => 'Ejecución',
                'slug' => 'ejecucion',
                'descripcion' => 'Perfil operativo general con acceso a todos los clientes asignados',
                'activo' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ],
            [
                'id_rol' => 3,
                'nombre' => 'Dirección',
                'slug' => 'director',
                'descripcion' => 'Supervisión global, revisión y control de cierre operativo',
                'activo' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ],
            [
                'id_rol' => 4,
                'nombre' => 'Ejecución Moeve',
                'slug' => 'ejecucion_moeve',
                'descripcion' => 'Perfil operativo restringido al contexto Moeve/Cepsa',
                'activo' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ],
            [
                'id_rol' => 5,
                'nombre' => 'Ejecución Repsol',
                'slug' => 'ejecucion_repsol',
                'descripcion' => 'Perfil operativo restringido al contexto Repsol',
                'activo' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ],
            [
                'id_rol' => 6,
                'nombre' => 'Contabilidad',
                'slug' => 'contable',
                'descripcion' => 'Perfil económico para pedidos, facturas, cobros e informes contables',
                'activo' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id_rol' => $role['id_rol']],
                $role
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('id_rol', 2)->update([
            'nombre' => 'usuario',
            'slug' => 'usuario',
            'descripcion' => 'Gestion operativa ambos clientes',
            'updated_at' => now(),
        ]);

        DB::table('roles')->where('id_rol', 3)->update([
            'nombre' => 'cierre',
            'slug' => 'cierre',
            'descripcion' => 'Control de cierre de trabajos',
            'updated_at' => now(),
        ]);

        DB::table('roles')->where('id_rol', 4)->update([
            'nombre' => 'gestor_moeve',
            'slug' => 'gestor_moeve',
            'descripcion' => 'Gestion operativa exclusiva MOEVE',
            'updated_at' => now(),
        ]);

        DB::table('roles')->where('id_rol', 5)->update([
            'nombre' => 'gestor_repsol',
            'slug' => 'gestor_repsol',
            'descripcion' => 'Gestion operativa exclusiva REPSOL',
            'updated_at' => now(),
        ]);

        DB::table('roles')->where('id_rol', 6)->delete();
    }
};

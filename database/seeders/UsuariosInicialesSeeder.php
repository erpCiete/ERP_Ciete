<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosInicialesSeeder extends Seeder
{
    /**
     * Seed de usuarios de arranque con roles.
     */
    public function run(): void
    {
        // Credenciales de arranque:
        // admin@ciete.es / Admin1234!
        // cesar@ciete.es / Cesar1234!
        // usuario@ciete.es / Usuario1234!
        $usuarios = [
            [
                'nombre' => 'Administrador',
                'apellidos' => 'ERP Ciete',
                'nombre_usuario' => 'admin',
                'email' => 'admin@ciete.es',
                'password' => 'Admin1234!',
                'id_contexto' => 3,
                'id_contacto_empresa' => 1,
                'rol' => 'admin',
            ],
            [
                'nombre' => 'Cesar',
                'apellidos' => 'Ciete',
                'nombre_usuario' => 'cesar',
                'email' => 'cesar@ciete.es',
                'password' => 'Cesar1234!',
                'id_contexto' => 3,
                'id_contacto_empresa' => null,
                'rol' => 'control_cierre',
            ],
            [
                'nombre' => 'Usuario',
                'apellidos' => 'Operativo',
                'nombre_usuario' => 'usuario',
                'email' => 'usuario@ciete.es',
                'password' => 'Usuario1234!',
                'id_contexto' => 3,
                'id_contacto_empresa' => null,
                'rol' => 'gestor',
            ],
        ];

        $roles = Role::query()->pluck('id_rol', 'slug');

        foreach ($usuarios as $item) {
            $usuario = User::query()->updateOrCreate(
                ['nombre_usuario' => $item['nombre_usuario']],
                [
                    'id_contexto' => $item['id_contexto'],
                    'id_contacto_empresa' => $item['id_contacto_empresa'],
                    'nombre' => $item['nombre'],
                    'apellidos' => $item['apellidos'],
                    'email' => $item['email'],
                    'email_verificado_at' => now(),
                    'password' => Hash::make($item['password']),
                    'activo' => true,
                ]
            );

            $roleId = $roles->get($item['rol']);
            if (! $roleId) {
                continue;
            }

            DB::table('usuario_roles')->where('id_usuario', $usuario->id_usuario)->delete();
            DB::table('usuario_roles')->insert([
                'id_usuario' => $usuario->id_usuario,
                'id_rol' => $roleId,
                'created_at' => now(),
            ]);
        }
    }
}

<?php
namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosInicialesSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'nombre' => 'Administrador',
                'apellidos' => 'ERP CIETE',
                'nombre_usuario' => 'admin',
                'email' => 'admin@ciete.es',
                'password' => 'Admin1234!',
                'id_contexto' => 3,
                'id_contacto_empresa' => null,
                'rol' => 'admin',
                'contextos' => [1, 2, 3],
                'interface_mode' => 'ciete_moderno',
            ],
            [
                'nombre' => 'Cesar',
                'apellidos' => 'CIETE',
                'nombre_usuario' => 'cesar',
                'email' => 'cesar@ciete.es',
                'password' => 'Cesar1234!',
                'id_contexto' => 3,
                'id_contacto_empresa' => null,
                'rol' => 'director',
                'contextos' => [1, 2, 3],
                'interface_mode' => 'ciete_excel',
            ],
            [
                'nombre' => 'Usuario',
                'apellidos' => 'Ejecucion',
                'nombre_usuario' => 'usuario',
                'email' => 'usuario@ciete.es',
                'password' => 'Usuario1234!',
                'id_contexto' => 3,
                'id_contacto_empresa' => null,
                'rol' => 'ejecucion',
                'contextos' => [1, 2, 3],
                'interface_mode' => 'ciete_excel',
            ],
            [
                'nombre' => 'Ejecucion',
                'apellidos' => 'Moeve',
                'nombre_usuario' => 'moeve',
                'email' => 'moeve@ciete.es',
                'password' => 'Moeve1234!',
                'id_contexto' => 1,
                'id_contacto_empresa' => null,
                'rol' => 'ejecucion_moeve',
                'contextos' => [1],
                'interface_mode' => 'ciete_excel',
            ],
            [
                'nombre' => 'Ejecucion',
                'apellidos' => 'Repsol',
                'nombre_usuario' => 'repsol',
                'email' => 'repsol@ciete.es',
                'password' => 'Repsol1234!',
                'id_contexto' => 2,
                'id_contacto_empresa' => null,
                'rol' => 'ejecucion_repsol',
                'contextos' => [2],
                'interface_mode' => 'ciete_excel',
            ],
            [
                'nombre' => 'Usuario',
                'apellidos' => 'Contable',
                'nombre_usuario' => 'contable',
                'email' => 'contable@ciete.es',
                'password' => 'Contable1234!',
                'id_contexto' => 3,
                'id_contacto_empresa' => null,
                'rol' => 'contable',
                'contextos' => [1, 2, 3],
                'interface_mode' => 'ciete_moderno',
            ],
        ];

        $roles = Role::query()->pluck('id_rol', 'slug');

        foreach ($usuarios as $item) {
            $usuario = User::query()->updateOrCreate(
                ['email' => $item['email']],
                [
                    'id_contexto' => $item['id_contexto'],
                    'id_contacto_empresa' => $item['id_contacto_empresa'],
                    'nombre' => $item['nombre'],
                    'apellidos' => $item['apellidos'],
                    'nombre_usuario' => $item['nombre_usuario'],
                    'email' => $item['email'],
                    'email_verificado_at' => now(),
                    'password' => Hash::make($item['password']),
                    'activo' => true,
                    'interface_mode' => $item['interface_mode'],
                ]
            );

            $roleId = $roles->get($item['rol']);

            if ($roleId) {
                $usuario->roles()->sync([$roleId]);
            }

            $pivotContextos = collect($item['contextos'])
                ->mapWithKeys(fn (int $contextoId) => [
                    $contextoId => [
                        'es_contexto_principal' => $contextoId === $item['id_contexto'],
                        'activo' => true,
                    ],
                ])
                ->all();

            $usuario->contextos()->sync($pivotContextos);
        }
    }
}
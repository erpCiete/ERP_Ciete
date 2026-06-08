<?php

namespace Tests\Feature\Seeders;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UsuariosInicialesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UsuariosInicialesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuarios_iniciales_quedan_creados_con_los_roles_finales_documentados(): void
    {
        $this->seed(DatabaseSeeder::class);

        $esperados = [
            'admin@ciete.es' => 'admin',
            'cesar@ciete.es' => 'director',
            'usuario@ciete.es' => 'ejecucion',
            'moeve@ciete.es' => 'ejecucion_moeve',
            'repsol@ciete.es' => 'ejecucion_repsol',
            'contable@ciete.es' => 'contable',
        ];

        $this->assertEqualsCanonicalizing(array_keys($esperados), User::query()->pluck('email')->all());

        foreach ($esperados as $email => $rol) {
            $this->assertContains($rol, User::query()->where('email', $email)->firstOrFail()->role_slugs);
        }
    }

    public function test_contextos_documentados_se_asignan_sin_mezclar_moeve_y_repsol(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();
        $director = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $ejecucion = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();
        $moeve = User::query()->where('email', 'moeve@ciete.es')->firstOrFail();
        $repsol = User::query()->where('email', 'repsol@ciete.es')->firstOrFail();
        $contable = User::query()->where('email', 'contable@ciete.es')->firstOrFail();

        $this->assertSame([1, 2, 3], $admin->contextos()->orderBy('contextos_cliente.id_contexto')->pluck('contextos_cliente.id_contexto')->all());
        $this->assertSame([1, 2, 3], $director->contextos()->orderBy('contextos_cliente.id_contexto')->pluck('contextos_cliente.id_contexto')->all());
        $this->assertSame([1, 2, 3], $ejecucion->contextos()->orderBy('contextos_cliente.id_contexto')->pluck('contextos_cliente.id_contexto')->all());
        $this->assertSame([1], $moeve->contextos()->pluck('contextos_cliente.id_contexto')->all());
        $this->assertSame([2], $repsol->contextos()->pluck('contextos_cliente.id_contexto')->all());
        $this->assertSame([1, 2, 3], $contable->contextos()->orderBy('contextos_cliente.id_contexto')->pluck('contextos_cliente.id_contexto')->all());
    }

    public function test_usuarios_iniciales_usan_modo_excel_por_defecto(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(
            ['ciete_excel'],
            User::query()
                ->whereIn('email', [
                    'admin@ciete.es',
                    'cesar@ciete.es',
                    'usuario@ciete.es',
                    'moeve@ciete.es',
                    'repsol@ciete.es',
                    'contable@ciete.es',
                ])
                ->pluck('interface_mode')
                ->unique()
                ->values()
                ->all()
        );
    }

    public function test_el_seeder_respeta_el_modo_elegido_manualmente_en_profile(): void
    {
        $this->seed(DatabaseSeeder::class);

        User::query()
            ->where('email', 'cesar@ciete.es')
            ->firstOrFail()
            ->update(['interface_mode' => 'ciete_moderno']);

        $this->seed(UsuariosInicialesSeeder::class);

        $this->assertSame(
            'ciete_moderno',
            User::query()->where('email', 'cesar@ciete.es')->firstOrFail()->interface_mode
        );
    }

    public function test_el_seeder_es_idempotente_y_no_duplica_usuarios_roles_ni_contextos(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(UsuariosInicialesSeeder::class);
        $this->seed(UsuariosInicialesSeeder::class);

        $emails = [
            'admin@ciete.es',
            'cesar@ciete.es',
            'usuario@ciete.es',
            'moeve@ciete.es',
            'repsol@ciete.es',
            'contable@ciete.es',
        ];

        $usuarios = User::query()->whereIn('email', $emails)->get();

        $this->assertCount(6, $usuarios);
        $this->assertSame(6, User::query()->whereIn('email', $emails)->count());

        foreach ($usuarios as $usuario) {
            $this->assertSame(1, DB::table('usuario_roles')->where('id_usuario', $usuario->id_usuario)->count());
            $this->assertSame(
                $usuario->contextos()->count(),
                DB::table('usuario_contextos')->where('id_usuario', $usuario->id_usuario)->count()
            );
        }
    }
}

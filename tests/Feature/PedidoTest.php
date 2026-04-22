<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Trabajo;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PedidoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $gestorMoeve;
    protected User $gestorRepsol;
    protected User $usuarioLector;

    protected function setUp(): void
    {
        parent::setUp();

        // 0. Cargar los contextos base en la BD de testing
        $this->seed(\Database\Seeders\ContextosClienteSeeder::class);

        // 1. Configuración de Permisos (Adaptado al esquema nativo de Ciete)
        $permisoVer = Permission::firstOrCreate(
            ['slug' => 'pedidos.ver'], 
            ['nombre' => 'Ver Pedidos', 'activo' => true]
        );
        $permisoGestionar = Permission::firstOrCreate(
            ['slug' => 'pedidos.gestionar'], 
            ['nombre' => 'Gestionar Pedidos', 'activo' => true]
        );

        // 2. Configuración de Roles
        $rolGestor = Role::firstOrCreate(
            ['slug' => 'gestor'], 
            ['nombre' => 'Gestor Operativo', 'activo' => true]
        );
        $rolGestor->permissions()->sync([
            $permisoVer->id_permiso, 
            $permisoGestionar->id_permiso
        ]);

        $rolLector = Role::firstOrCreate(
            ['slug' => 'usuario'], 
            ['nombre' => 'Usuario Base', 'activo' => true]
        );
        $rolLector->permissions()->sync([
            $permisoVer->id_permiso
        ]);

        // 3. Creación de Usuarios con Contextos (1 = MOEVE, 2 = REPSOL)
        $this->gestorMoeve = User::factory()->create(['id_contexto' => 1]);
        $this->gestorMoeve->roles()->sync([$rolGestor->id_rol]);

        $this->gestorRepsol = User::factory()->create(['id_contexto' => 2]);
        $this->gestorRepsol->roles()->sync([$rolGestor->id_rol]);

        $this->usuarioLector = User::factory()->create(['id_contexto' => 1]);
        $this->usuarioLector->roles()->sync([$rolLector->id_rol]);
    }

    public function test_visitante_no_puede_acceder_a_pedidos(): void
    {
        $response = $this->getJson('/api/v1/pedidos');
        $response->assertStatus(401);
    }

    public function test_usuario_lector_no_puede_crear_pedido(): void
    {
        Sanctum::actingAs($this->usuarioLector);
        $response = $this->postJson('/api/v1/pedidos', []);
        $response->assertStatus(403);
    }

    public function test_gestor_solo_ve_pedidos_de_su_contexto(): void
    {
        // Crear jerarquía completa y coherente para MOEVE (Contexto 1)
        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);
        Pedido::factory()->count(2)->create(['id_contexto' => 1, 'id_trabajo' => $trabajoMoeve->id_trabajo]);

        // Crear jerarquía completa y coherente para REPSOL (Contexto 2)
        $empresaRepsol = Empresa::factory()->create(['id_contexto' => 2]);
        $trabajoRepsol = Trabajo::factory()->create(['id_contexto' => 2, 'id_empresa_cliente' => $empresaRepsol->id_empresa]);
        Pedido::factory()->count(3)->create(['id_contexto' => 2, 'id_trabajo' => $trabajoRepsol->id_trabajo]);

        Sanctum::actingAs($this->gestorMoeve);

        $response = $this->getJson('/api/v1/pedidos');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data') // Solo debe devolver los 2 de MOEVE
                 ->assertJsonPath('data.0.id_contexto', 1);
    }

    public function test_gestor_moeve_no_puede_crear_pedido_para_trabajo_repsol(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        // Creamos un Trabajo que pertenece a REPSOL (contexto 2)
        $empresaRepsol = Empresa::factory()->create(['id_contexto' => 2]);
        $trabajoRepsol = Trabajo::factory()->create(['id_contexto' => 2, 'id_empresa_cliente' => $empresaRepsol->id_empresa]);

        $payload = [
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'numero_pedido' => 'PED-HACK-001',
            'importe_pedido' => 100,
            'importe_solicitado' => 100,
            'importe_facturado' => 0,
            'unidades_pedido' => 1,
            'unidades_solicitadas' => 1,
            'estado' => 'pendiente',
            'pedido_completo' => false,
            'tiene_mas_de_1_item' => false,
            'facturado_completo' => false,
        ];

        $response = $this->postJson('/api/v1/pedidos', $payload);

        // La validación debe bloquear el intento (422) porque el Trabajo no pertenece a MOEVE
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['id_trabajo']);
    }

    public function test_gestor_puede_crear_pedido_completo_con_items(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        // Preparar entorno coherente
        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);

        $payload = [
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'numero_pedido' => 'PED-TEST-OK-01',
            'fecha_solicitud' => '2026-04-20',
            'importe_pedido' => 1500.50,
            'importe_solicitado' => 1500.50,
            'importe_facturado' => 0,
            'unidades_pedido' => 2,
            'unidades_solicitadas' => 2,
            'estado' => 'pendiente',
            'pedido_completo' => false,
            'tiene_mas_de_1_item' => true,
            'facturado_completo' => false,
            'observaciones' => 'Test de integración',
            'items' => [
                [
                    'codigo_servicio' => 'SRV-01',
                    'descripcion_servicio' => 'Desplazamiento técnico',
                    'cantidad' => 2,
                    'precio_unitario' => 750.25,
                    'total_linea' => 1500.50
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/pedidos', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('data.numero_pedido', 'PED-TEST-OK-01')
                 ->assertJsonPath('data.importe_pedido', 1500.5);

        // Verificar BBDD principal
        $this->assertDatabaseHas('pedidos', [
            'numero_pedido' => 'PED-TEST-OK-01',
            'id_contexto' => 1,
            'importe_pedido' => 1500.50,
            'estado' => 'pendiente'
        ]);

        // Verificar líneas anidadas
        $this->assertDatabaseHas('pedido_items', [
            'codigo_servicio' => 'SRV-01',
            'total_linea' => 1500.50
        ]);
    }
}
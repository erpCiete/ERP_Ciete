<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_seeded_roles_always_redirect_to_common_home_after_login_even_with_intended_route(): void
    {
        $this->seed(DatabaseSeeder::class);

        $credentials = [
            ['email' => 'admin@ciete.es', 'password' => 'Admin1234!'],
            ['email' => 'cesar@ciete.es', 'password' => 'Cesar1234!'],
            ['email' => 'usuario@ciete.es', 'password' => 'Usuario1234!'],
            ['email' => 'moeve@ciete.es', 'password' => 'Moeve1234!'],
            ['email' => 'repsol@ciete.es', 'password' => 'Repsol1234!'],
            ['email' => 'contable@ciete.es', 'password' => 'Contable1234!'],
        ];

        foreach ($credentials as $credential) {
            $this->get('/trabajos')->assertRedirect('/login');

            $response = $this->post('/login', $credential);

            $this->assertAuthenticated();
            $response->assertRedirect(route('index', absolute: false));

            $this->post('/logout')->assertRedirect(route('index', absolute: false));
            $this->assertGuest();
        }
    }
}

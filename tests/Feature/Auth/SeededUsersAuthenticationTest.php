<?php

namespace Tests\Feature\Auth;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeededUsersAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_users_can_authenticate_using_documented_credentials(): void
    {
        $this->seed(DatabaseSeeder::class);

        $credentials = [
            ['email' => 'admin@ciete.es', 'password' => 'Admin1234!'],
            ['email' => 'cesar@ciete.es', 'password' => 'Cesar1234!'],
            ['email' => 'usuario@ciete.es', 'password' => 'Usuario1234!'],
            ['email' => 'moeve@ciete.es', 'password' => 'Moeve1234!'],
            ['email' => 'repsol@ciete.es', 'password' => 'Repsol1234!'],
        ];

        foreach ($credentials as $credential) {
            $response = $this->post('/login', $credential);

            $this->assertAuthenticated();
            $response->assertRedirect(route('index', absolute: false));

            $this->post('/logout');
            $this->assertGuest();
        }
    }
}

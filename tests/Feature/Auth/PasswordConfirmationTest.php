<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_routes_are_not_available(): void
    {
        $this->assertFalse(Route::has('password.confirm'));

        $this->get('/confirm-password')->assertRedirect('/login');
        $this->post('/confirm-password', ['password' => 'password'])->assertStatus(405);
    }
}

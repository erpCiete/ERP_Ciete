<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_routes_are_not_available(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('register'));

        $this->get('/register')->assertRedirect('/login');
    }
}

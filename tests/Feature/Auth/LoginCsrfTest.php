<?php

namespace Tests\Feature\Auth;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LoginCsrfTest extends TestCase
{
    public function test_expired_login_post_redirects_back_with_a_status_message(): void
    {
        Route::middleware('web')->post('/_test/token-mismatch', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        });

        $response = $this
            ->from('/login')
            ->post('/_test/token-mismatch');

        $response
            ->assertRedirect('/login')
            ->assertSessionHas('status', trans('auth.page_expired'));
    }
}

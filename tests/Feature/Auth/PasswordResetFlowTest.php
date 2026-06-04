<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(Vite::class, new class extends Vite {
            public function __invoke($entrypoints, $buildDirectory = null): HtmlString
            {
                return new HtmlString('');
            }
        });
    }

    public function test_forgot_password_page_loads(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/ForgotPassword'));
    }

    public function test_reset_link_can_be_requested_for_existing_user_without_sending_real_mail(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_reset_link_request_does_not_reveal_if_user_does_not_exist(): void
    {
        Notification::fake();

        $this->post(route('password.email'), [
            'email' => 'desconocido@ciete.es',
        ])->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Notification::assertNothingSent();
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'desconocido@ciete.es',
        ]);
    }

    public function test_email_is_required_to_request_a_reset_link(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => '',
            ])->assertSessionHasErrors('email')
            ->assertRedirect(route('password.request'));
    }

    public function test_reset_password_page_loads_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/ResetPassword')
                ->where('token', $token)
                ->where('email', $user->email));
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nuevo-password-seguro',
            'password_confirmation' => 'nuevo-password-seguro',
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('nuevo-password-seguro', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->from(route('password.request'))
            ->post(route('password.store'), [
                'token' => 'token-invalido',
                'email' => $user->email,
                'password' => 'nuevo-password-seguro',
                'password_confirmation' => 'nuevo-password-seguro',
            ])->assertSessionHasErrors('email');
    }

    public function test_used_token_cannot_be_reused(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $payload = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nuevo-password-seguro',
            'password_confirmation' => 'nuevo-password-seguro',
        ];

        $this->post(route('password.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->post(route('password.store'), $payload)
            ->assertSessionHasErrors('email');
    }

    public function test_password_confirmation_is_required(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nuevo-password-seguro',
            'password_confirmation' => 'distinta',
        ])->assertSessionHasErrors('password');
    }
}

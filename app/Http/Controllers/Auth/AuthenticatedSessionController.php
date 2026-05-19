<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $user?->forceFill([
            'ultimo_login_at' => now(),
        ])->save();

        if ($user) {
            $sesionLoginId = DB::table('sesiones_login')->insertGetId([
                'id_usuario' => $user->id_usuario,
                'id_contexto' => $user->id_contexto,
                'fecha_hora_login' => now(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $request->session()->put('sesion_login_id', $sesionLoginId);
        }

        return redirect()->route('index');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sesionLoginId = $request->session()->get('sesion_login_id');

        if ($user && $sesionLoginId) {
            DB::table('sesiones_login')
                ->where('id_sesion', $sesionLoginId)
                ->where('id_usuario', $user->id_usuario)
                ->whereNull('fecha_hora_logout')
                ->update([
                    'fecha_hora_logout' => now(),
                    'updated_at' => now(),
                ]);
        } elseif ($user) {
            $sesionAbierta = DB::table('sesiones_login')
                ->where('id_usuario', $user->id_usuario)
                ->whereNull('fecha_hora_logout')
                ->orderByDesc('id_sesion')
                ->first();

            if ($sesionAbierta) {
                DB::table('sesiones_login')
                    ->where('id_sesion', $sesionAbierta->id_sesion)
                    ->update([
                        'fecha_hora_logout' => now(),
                        'updated_at' => now(),
                    ]);
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('index');
    }
}

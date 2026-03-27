<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
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

        return response()->json([
            'success' => true,
            'message' => 'Login correcto',
            'data' => [
                'user' => $user,
                'context' => [
                    'id_contexto' => $user?->id_contexto,
                ],
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
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
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logout correcto',
            'data' => null,
            'meta' => [
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Usuario autenticado',
            'data' => [
                'user' => $user,
                'context' => [
                    'id_contexto' => $user?->id_contexto,
                ],
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }
}

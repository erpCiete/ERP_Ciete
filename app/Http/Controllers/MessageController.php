<?php

namespace App\Http\Controllers;

use App\Models\MensajeInterno;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tab = $request->input('tab', 'inbox');

        $query = match ($tab) {
            'sent' => MensajeInterno::where('id_remitente', $user->id_usuario),
            'archived' => MensajeInterno::where('id_destinatario', $user->id_usuario)->where('archivado', true),
            default => MensajeInterno::where('id_destinatario', $user->id_usuario)->where('archivado', false),
        };

        $messages = $query
            ->with(['remitente:id_usuario,nombre,apellidos,avatar_key', 'destinatario:id_usuario,nombre,apellidos'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $unreadCount = MensajeInterno::where('id_destinatario', $user->id_usuario)
            ->whereNull('leido_at')
            ->where('archivado', false)
            ->count();

        $users = User::where('id_usuario', '!=', $user->id_usuario)
            ->where('activo', true)
            ->select('id_usuario', 'nombre', 'apellidos', 'email')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Messages/Index', [
            'messages' => $messages,
            'tab' => $tab,
            'unreadCount' => $unreadCount,
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_destinatario' => ['required', 'exists:usuarios,id_usuario'],
            'asunto' => ['required', 'string', 'max:255'],
            'cuerpo' => ['required', 'string', 'max:5000'],
            'prioridad' => ['sometimes', 'in:normal,alta,urgente'],
        ]);

        MensajeInterno::create([
            'id_remitente' => $request->user()->id_usuario,
            'id_destinatario' => $validated['id_destinatario'],
            'asunto' => $validated['asunto'],
            'cuerpo' => $validated['cuerpo'],
            'prioridad' => $validated['prioridad'] ?? 'normal',
        ]);

        return back();
    }

    public function show(Request $request, MensajeInterno $mensaje): Response
    {
        $user = $request->user();

        if ($mensaje->id_destinatario === $user->id_usuario && ! $mensaje->leido_at) {
            $mensaje->update(['leido_at' => now()]);
        }

        $mensaje->load([
            'remitente:id_usuario,nombre,apellidos,email',
            'destinatario:id_usuario,nombre,apellidos,email',
        ]);

        return Inertia::render('Messages/Show', [
            'message' => $mensaje,
        ]);
    }

    public function markRead(Request $request, MensajeInterno $mensaje): RedirectResponse
    {
        if ($mensaje->id_destinatario === $request->user()->id_usuario) {
            $mensaje->update(['leido_at' => now()]);
        }

        return back();
    }

    public function archive(Request $request, MensajeInterno $mensaje): RedirectResponse
    {
        if ($mensaje->id_destinatario === $request->user()->id_usuario) {
            $mensaje->update(['archivado' => true]);
        }

        return back();
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $request->validate([
            'asunto' => ['required', 'string', 'max:255'],
            'cuerpo' => ['required', 'string', 'max:5000'],
            'prioridad' => ['sometimes', 'in:normal,alta,urgente'],
        ]);

        $sender = $request->user();

        $recipients = User::where('id_usuario', '!=', $sender->id_usuario)
            ->where('activo', true)
            ->pluck('id_usuario');

        $now = now();
        $rows = $recipients->map(fn($id) => [
            'id_remitente' => $sender->id_usuario,
            'id_destinatario' => $id,
            'asunto' => $request->input('asunto'),
            'cuerpo' => $request->input('cuerpo'),
            'prioridad' => $request->input('prioridad', 'normal'),
            'es_aviso_sistema' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        MensajeInterno::insert($rows);

        return back();
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Support');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'priority' => ['sometimes', 'in:normal,alta,urgente'],
        ]);

        $user = $request->user();
        $supportEmail = config('mail.from.address', 'soporte@ciete.es');

        Mail::raw(
            implode("\n\n", [
                "Tema: {$validated['topic']}",
                "Asunto: {$validated['subject']}",
                "Prioridad: " . ($validated['priority'] ?? 'normal'),
                "---",
                $validated['message'],
                "---",
                "Remitente: {$user->nombre} {$user->apellidos}",
                "Email: {$user->email}",
                "Usuario: {$user->nombre_usuario}",
                "Contexto: " . ($user->contexto?->nombre ?? 'N/A'),
            ]),
            function ($mail) use ($validated, $user, $supportEmail) {
                $mail->to($supportEmail)
                    ->replyTo($user->email, "{$user->nombre} {$user->apellidos}")
                    ->subject("[ERP Ciete Soporte] {$validated['topic']} — {$validated['subject']}");
            }
        );

        return back()->with('status', 'support-sent');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $avatarCatalog = collect(config('profile.avatar_catalog', []))
            ->map(function (array $avatar, string $key): array {
                return [
                    'key' => $key,
                    'label' => $avatar['label'] ?? $key,
                    'src' => asset($avatar['file']),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => false,
            'status' => session('status'),
            'avatarCatalog' => $avatarCatalog,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = ['avatar_key' => $request->validated('avatar_key')];

        if ($request->has('email_recuperacion')) {
            $data['email_recuperacion'] = $request->validated('email_recuperacion');
        }

        $request->user()->update($data);

        return Redirect::route('profile.edit');
    }

    /**
     * Update the user's interface preference.
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'interface_mode' => ['required', 'string', 'in:ciete_excel,ciete_moderno'],
        ]);

        $request->user()->update(['interface_mode' => $validated['interface_mode']]);

        return Redirect::back();
    }
}

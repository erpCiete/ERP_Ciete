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
        $request->user()->update([
            'avatar_key' => $request->validated('avatar_key'),
        ]);

        return Redirect::route('profile.edit');
    }
}

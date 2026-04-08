<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $defaultLocale = strtolower((string) config('app.locale', 'es'));
        $supportedLocales = collect(config('app.supported_locales', []))
            ->map(fn ($locale) => strtolower((string) $locale))
            ->filter()
            ->unique()
            ->values();

        if (! $supportedLocales->contains($defaultLocale)) {
            $supportedLocales->prepend($defaultLocale);
        }

        $validated = $request->validate([
            'locale' => ['required', 'string'],
        ]);

        $requestedLocale = strtolower((string) $validated['locale']);
        $resolvedLocale = $supportedLocales->contains($requestedLocale)
            ? $requestedLocale
            : $defaultLocale;

        $request->session()->put('locale', $resolvedLocale);
        app()->setLocale($resolvedLocale);

        return back(303);
    }
}

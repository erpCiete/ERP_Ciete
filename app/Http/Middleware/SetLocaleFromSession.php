<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromSession
{
    public function handle(Request $request, Closure $next): Response
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

        $sessionLocale = strtolower((string) $request->session()->get('locale', $defaultLocale));
        $resolvedLocale = $supportedLocales->contains($sessionLocale)
            ? $sessionLocale
            : $defaultLocale;

        app()->setLocale($resolvedLocale);
        $request->session()->put('locale', $resolvedLocale);

        return $next($request);
    }
}

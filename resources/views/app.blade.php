<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>ERP Ciete</title>
        <link rel="icon" type="image/svg+xml" href="/images/avatars/avatar-ciete-logo.svg?v=2">
        <link rel="shortcut icon" href="/images/avatars/avatar-ciete-logo.svg?v=2">
        <script>
            (function () {
                const DEFAULT_THEME = 'light';
                const STORAGE_KEY = 'ciete.theme';

                try {
                    const storedTheme = window.localStorage.getItem(STORAGE_KEY);
                    const isValidTheme = storedTheme === 'light' || storedTheme === 'dark';
                    const resolvedTheme = isValidTheme
                        ? storedTheme
                        : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : DEFAULT_THEME);

                    document.documentElement.setAttribute('data-theme', resolvedTheme);
                    document.documentElement.style.colorScheme = resolvedTheme;
                } catch (error) {
                    document.documentElement.setAttribute('data-theme', DEFAULT_THEME);
                    document.documentElement.style.colorScheme = DEFAULT_THEME;
                }
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"DM Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Marca
                primary: {
                    DEFAULT: 'var(--color-primary)',
                    strong: 'var(--color-primary-strong)',
                    hover: 'var(--color-primary-hover)',
                    light: 'var(--color-primary-light)',
                },
                secondary: 'var(--color-secondary)',
                accent: {
                    DEFAULT: 'var(--color-accent)',
                    light: 'var(--color-accent-light)',
                },
                brand: {
                    black: 'var(--color-brand-black)',
                },
                // Neutros
                page: 'var(--color-bg-page)',
                surface: {
                    DEFAULT: 'var(--color-surface)',
                    2: 'var(--color-surface-2)',
                },
                border: {
                    DEFAULT: 'var(--color-border)',
                    heavy: 'var(--color-border-heavy)',
                },
                text: {
                    main: 'var(--color-text-main)',
                    muted: 'var(--color-text-muted)',
                    hint: 'var(--color-text-hint)',
                },
                // Estados semánticos
                state: {
                    pending: {
                        bg: 'var(--color-state-pending-bg)',
                        text: 'var(--color-state-pending-text)',
                        dot: 'var(--color-state-pending-dot)',
                    },
                    progress: {
                        bg: 'var(--color-state-progress-bg)',
                        text: 'var(--color-state-progress-text)',
                        dot: 'var(--color-state-progress-dot)',
                    },
                    done: {
                        bg: 'var(--color-state-done-bg)',
                        text: 'var(--color-state-done-text)',
                        dot: 'var(--color-state-done-dot)',
                    },
                    billed: {
                        bg: 'var(--color-state-billed-bg)',
                        text: 'var(--color-state-billed-text)',
                        dot: 'var(--color-state-billed-dot)',
                    },
                    closed: {
                        bg: 'var(--color-state-closed-bg)',
                        text: 'var(--color-state-closed-text)',
                        dot: 'var(--color-state-closed-dot)',
                    },
                    blocked: {
                        bg: 'var(--color-state-blocked-bg)',
                        text: 'var(--color-state-blocked-text)',
                        dot: 'var(--color-state-blocked-dot)',
                    },
                },
                // Clientes
                client: {
                    repsol: 'var(--color-client-repsol)',
                    cepsa: 'var(--color-client-cepsa)',
                    bp: 'var(--color-client-bp)',
                    galp: 'var(--color-client-galp)',
                }
            },
            boxShadow: {
                'sm': '0 1px 3px rgba(0,0,0,.08)',
                'focus': '0 0 0 3px rgba(232,0,13,.12)',
            }
        },
    },

    plugins: [forms],
};

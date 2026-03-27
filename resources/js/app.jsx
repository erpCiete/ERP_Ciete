import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { I18nProvider } from '@/i18n';
import { ThemeProvider } from '@/theme';

const configuredAppName = import.meta.env.VITE_APP_NAME;
const appName =
    !configuredAppName || configuredAppName.toLowerCase() === 'laravel'
        ? 'ERP Ciete'
        : configuredAppName;

createInertiaApp({
    title: (title) => {
        const normalized = (title || '').trim();
        if (!normalized) return appName;

        return normalized.toLowerCase().endsWith(appName.toLowerCase())
            ? normalized
            : `${normalized} - ${appName}`;
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);
        const initialLocale = props?.initialPage?.props?.locale?.current;
        const supportedLocales = props?.initialPage?.props?.locale?.supported;

        root.render(
            <ThemeProvider>
                <I18nProvider initialLocale={initialLocale} supportedLocales={supportedLocales}>
                    <App {...props} />
                </I18nProvider>
            </ThemeProvider>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

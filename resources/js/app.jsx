import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { I18nProvider, preloadLocaleDictionaries } from '@/i18n';
import { ThemeProvider } from '@/theme';

const configuredAppName = import.meta.env.VITE_APP_NAME;
const appName =
    !configuredAppName || configuredAppName.toLowerCase() === 'laravel'
        ? 'ERP Ciete'
        : configuredAppName;
const pages = import.meta.glob([
    './Pages/*.jsx',
    './Pages/**/*.jsx',
]);

function readBootstrapLocaleConfig() {
    if (typeof document === 'undefined') {
        return { initialLocale: undefined, supportedLocales: undefined };
    }

    const appElement = document.getElementById('app');
    const serializedPage = appElement?.dataset?.page;

    if (!serializedPage) {
        return { initialLocale: undefined, supportedLocales: undefined };
    }

    try {
        const page = JSON.parse(serializedPage);

        return {
            initialLocale: page?.props?.locale?.current,
            supportedLocales: page?.props?.locale?.supported,
        };
    } catch {
        return { initialLocale: undefined, supportedLocales: undefined };
    }
}

async function bootstrapApp() {
    const { initialLocale: bootstrapLocale, supportedLocales: bootstrapLocales } =
        readBootstrapLocaleConfig();

    try {
        await preloadLocaleDictionaries([bootstrapLocale]);
    } catch (error) {
        console.error('Failed to preload locale dictionaries', error);
    }

    return createInertiaApp({
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
                pages,
            ),
        setup({ el, App, props }) {
            const root = createRoot(el);
            const initialLocale = props?.initialPage?.props?.locale?.current ?? bootstrapLocale;
            const supportedLocales =
                props?.initialPage?.props?.locale?.supported ?? bootstrapLocales;

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
}

bootstrapApp().catch((error) => {
    console.error('Failed to bootstrap the application', error);
});

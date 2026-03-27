import {
    createContext,
    createElement,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
} from 'react';
import { router } from '@inertiajs/react';
import en from '@/i18n/locales/en';
import es from '@/i18n/locales/es';

const dictionaries = { es, en };
const DEFAULT_LOCALE = 'es';

export const SUPPORTED_LOCALES = Object.keys(dictionaries);

const I18nContext = createContext(undefined);

const getNestedValue = (obj, path) => {
    return path.split('.').reduce((acc, key) => (acc && acc[key] !== undefined ? acc[key] : undefined), obj);
};

const interpolate = (template, params = {}) => {
    return template.replace(/\{(\w+)\}/g, (_, token) => {
        const value = params[token];
        return value === undefined || value === null ? `{${token}}` : String(value);
    });
};

const normalizeLocale = (value) => String(value || '').toLowerCase().split('-')[0];

const resolveSupportedLocales = (value) => {
    const locales = Array.isArray(value) ? value : SUPPORTED_LOCALES;
    const normalizedLocales = locales
        .map((locale) => normalizeLocale(locale))
        .filter((locale) => SUPPORTED_LOCALES.includes(locale));

    const uniqueLocales = Array.from(new Set(normalizedLocales));

    return uniqueLocales.length > 0 ? uniqueLocales : [DEFAULT_LOCALE];
};

const resolveLocale = (value, supportedLocales) => {
    const locale = normalizeLocale(value);

    if (supportedLocales.includes(locale)) {
        return locale;
    }

    return supportedLocales[0] ?? DEFAULT_LOCALE;
};

export function I18nProvider({ children, initialLocale, supportedLocales }) {
    const availableLocales = useMemo(() => resolveSupportedLocales(supportedLocales), [supportedLocales]);
    const [locale, setLocale] = useState(() => resolveLocale(initialLocale, availableLocales));

    useEffect(() => {
        setLocale((currentLocale) => {
            const nextLocale = resolveLocale(initialLocale, availableLocales);
            return currentLocale === nextLocale ? currentLocale : nextLocale;
        });
    }, [initialLocale, availableLocales]);

    useEffect(() => {
        const removeListener = router.on('success', (event) => {
            const pageProps = event?.detail?.page?.props;
            const pageLocales = resolveSupportedLocales(pageProps?.locale?.supported ?? availableLocales);
            const nextLocale = resolveLocale(pageProps?.locale?.current, pageLocales);

            setLocale((currentLocale) => (currentLocale === nextLocale ? currentLocale : nextLocale));
        });

        return () => {
            if (typeof removeListener === 'function') {
                removeListener();
            }
        };
    }, [availableLocales]);

    useEffect(() => {
        if (typeof document !== 'undefined') {
            document.documentElement.setAttribute('lang', locale);
        }
    }, [locale]);

    const changeLocale = useCallback(
        (nextLocale) => {
            const resolvedLocale = resolveLocale(nextLocale, availableLocales);

            if (resolvedLocale === locale) {
                return;
            }

            const updateRoute = typeof route === 'function' ? route('locale.update') : '/locale';

            router.post(
                updateRoute,
                { locale: resolvedLocale },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        },
        [availableLocales, locale],
    );

    const t = useCallback(
        (key, params = {}) => {
            const activeDictionary = dictionaries[locale] || dictionaries[DEFAULT_LOCALE];
            const template =
                getNestedValue(activeDictionary, key) ||
                getNestedValue(dictionaries[DEFAULT_LOCALE], key);

            if (typeof template !== 'string') {
                return key;
            }

            return interpolate(template, params);
        },
        [locale],
    );

    const value = useMemo(
        () => ({ locale, setLocale: changeLocale, t, supportedLocales: availableLocales }),
        [locale, changeLocale, t, availableLocales],
    );

    return createElement(I18nContext.Provider, { value }, children);
}

export function useI18n() {
    const context = useContext(I18nContext);

    if (!context) {
        throw new Error('useI18n must be used within an I18nProvider');
    }

    return context;
}

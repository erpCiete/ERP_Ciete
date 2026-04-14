import {
    createContext,
    createElement,
    startTransition,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
} from 'react';
import { router } from '@inertiajs/react';

const DEFAULT_LOCALE = 'es';
const localeLoaders = {
    es: () => import('./locales/es').then((module) => module.default),
    en: () => import('./locales/en').then((module) => module.default),
};
const loadedDictionaries = new Map();

export const SUPPORTED_LOCALES = Object.keys(localeLoaders);

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

async function loadDictionary(locale) {
    const normalizedLocale = SUPPORTED_LOCALES.includes(locale) ? locale : DEFAULT_LOCALE;

    if (loadedDictionaries.has(normalizedLocale)) {
        return loadedDictionaries.get(normalizedLocale);
    }

    const dictionary = await localeLoaders[normalizedLocale]();
    loadedDictionaries.set(normalizedLocale, dictionary);

    return dictionary;
}

function readLoadedDictionaries(locales) {
    return locales.reduce((catalog, locale) => {
        const dictionary = loadedDictionaries.get(locale);

        if (dictionary) {
            catalog[locale] = dictionary;
        }

        return catalog;
    }, {});
}

export async function preloadLocaleDictionaries(locales) {
    const normalizedLocales = resolveSupportedLocales(locales);
    const requiredLocales = Array.from(new Set([DEFAULT_LOCALE, ...normalizedLocales]));

    await Promise.all(requiredLocales.map((locale) => loadDictionary(locale)));
}

export function I18nProvider({ children, initialLocale, supportedLocales }) {
    const availableLocales = useMemo(() => resolveSupportedLocales(supportedLocales), [supportedLocales]);
    const resolvedInitialLocale = useMemo(
        () => resolveLocale(initialLocale, availableLocales),
        [initialLocale, availableLocales],
    );
    const [locale, setLocale] = useState(resolvedInitialLocale);
    const [dictionaries, setDictionaries] = useState(() =>
        readLoadedDictionaries([DEFAULT_LOCALE, resolvedInitialLocale]),
    );

    const syncLoadedDictionaries = useCallback((locales) => {
        setDictionaries((currentDictionaries) => {
            const nextDictionaries = readLoadedDictionaries(locales);
            let changed = false;
            const mergedDictionaries = { ...currentDictionaries };

            for (const [localeKey, dictionary] of Object.entries(nextDictionaries)) {
                if (mergedDictionaries[localeKey] === dictionary) {
                    continue;
                }

                mergedDictionaries[localeKey] = dictionary;
                changed = true;
            }

            return changed ? mergedDictionaries : currentDictionaries;
        });
    }, []);

    useEffect(() => {
        let cancelled = false;

        preloadLocaleDictionaries([resolvedInitialLocale]).then(() => {
            if (cancelled) {
                return;
            }

            startTransition(() => {
                syncLoadedDictionaries([DEFAULT_LOCALE, resolvedInitialLocale]);
                setLocale((currentLocale) =>
                    currentLocale === resolvedInitialLocale ? currentLocale : resolvedInitialLocale,
                );
            });
        });

        return () => {
            cancelled = true;
        };
    }, [resolvedInitialLocale, syncLoadedDictionaries]);

    useEffect(() => {
        let cancelled = false;

        const removeListener = router.on('success', (event) => {
            const pageProps = event?.detail?.page?.props;
            const pageLocales = resolveSupportedLocales(pageProps?.locale?.supported ?? availableLocales);
            const nextLocale = resolveLocale(pageProps?.locale?.current, pageLocales);

            preloadLocaleDictionaries([nextLocale]).then(() => {
                if (cancelled) {
                    return;
                }

                startTransition(() => {
                    syncLoadedDictionaries([DEFAULT_LOCALE, nextLocale]);
                    setLocale((currentLocale) =>
                        currentLocale === nextLocale ? currentLocale : nextLocale,
                    );
                });
            });
        });

        return () => {
            cancelled = true;
            if (typeof removeListener === 'function') {
                removeListener();
            }
        };
    }, [availableLocales, syncLoadedDictionaries]);

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
        [dictionaries, locale],
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

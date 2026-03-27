import {
    createContext,
    createElement,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
} from 'react';
import en from '@/i18n/locales/en';
import es from '@/i18n/locales/es';

const dictionaries = { es, en };
const DEFAULT_LOCALE = 'es';
const STORAGE_KEY = 'ciete.locale';

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

const resolveLocale = (value) => {
    const shortLocale = String(value || '').toLowerCase().split('-')[0];
    return SUPPORTED_LOCALES.includes(shortLocale) ? shortLocale : DEFAULT_LOCALE;
};

const getInitialLocale = () => {
    if (typeof window === 'undefined') {
        return DEFAULT_LOCALE;
    }

    const storedLocale = window.localStorage.getItem(STORAGE_KEY);
    if (storedLocale) {
        return resolveLocale(storedLocale);
    }

    return resolveLocale(window.navigator.language);
};

export function I18nProvider({ children }) {
    const [locale, setLocale] = useState(getInitialLocale);

    useEffect(() => {
        if (typeof window !== 'undefined') {
            window.localStorage.setItem(STORAGE_KEY, locale);
        }

        if (typeof document !== 'undefined') {
            document.documentElement.setAttribute('lang', locale);
        }
    }, [locale]);

    const changeLocale = useCallback((nextLocale) => {
        setLocale(resolveLocale(nextLocale));
    }, []);

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
        () => ({ locale, setLocale: changeLocale, t, supportedLocales: SUPPORTED_LOCALES }),
        [locale, changeLocale, t],
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

import {
    createContext,
    createElement,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
} from 'react';

const DEFAULT_THEME = 'light';
const STORAGE_KEY = 'ciete.theme';
export const SUPPORTED_THEMES = ['light', 'dark'];

const ThemeContext = createContext(undefined);

const normalizeTheme = (value) => {
    const normalized = String(value || '').toLowerCase();
    return SUPPORTED_THEMES.includes(normalized) ? normalized : null;
};

const getSystemTheme = () => {
    if (
        typeof window !== 'undefined' &&
        typeof window.matchMedia === 'function' &&
        window.matchMedia('(prefers-color-scheme: dark)').matches
    ) {
        return 'dark';
    }

    return DEFAULT_THEME;
};

const getStoredThemePreference = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        return normalizeTheme(window.localStorage.getItem(STORAGE_KEY));
    } catch {
        return null;
    }
};

const resolveTheme = (themePreference, systemTheme) => themePreference ?? systemTheme;

const applyTheme = (theme) => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.style.colorScheme = theme;
};

export function ThemeProvider({ children }) {
    const [themePreference, setThemePreference] = useState(getStoredThemePreference);
    const [systemTheme, setSystemTheme] = useState(getSystemTheme);
    const theme = useMemo(
        () => resolveTheme(themePreference, systemTheme),
        [themePreference, systemTheme],
    );

    useEffect(() => {
        if (typeof window === 'undefined' || typeof window.matchMedia !== 'function') {
            return undefined;
        }

        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const handleChange = (event) => {
            setSystemTheme(event.matches ? 'dark' : DEFAULT_THEME);
        };

        if (typeof mediaQuery.addEventListener === 'function') {
            mediaQuery.addEventListener('change', handleChange);

            return () => {
                mediaQuery.removeEventListener('change', handleChange);
            };
        }

        mediaQuery.addListener(handleChange);

        return () => {
            mediaQuery.removeListener(handleChange);
        };
    }, []);

    useEffect(() => {
        if (typeof window !== 'undefined') {
            try {
                if (themePreference) {
                    window.localStorage.setItem(STORAGE_KEY, themePreference);
                } else {
                    window.localStorage.removeItem(STORAGE_KEY);
                }
            } catch {
                // Ignore storage failures and keep the in-memory theme working.
            }
        }

        applyTheme(theme);
    }, [theme, themePreference]);

    const changeTheme = useCallback((nextTheme) => {
        const normalizedTheme = normalizeTheme(nextTheme);

        if (!normalizedTheme) {
            return;
        }

        setThemePreference(normalizedTheme);
    }, []);

    const toggleTheme = useCallback(() => {
        setThemePreference((currentThemePreference) => {
            const activeTheme = resolveTheme(currentThemePreference, systemTheme);
            return activeTheme === 'dark' ? 'light' : 'dark';
        });
    }, [systemTheme]);

    const value = useMemo(
        () => ({
            theme,
            setTheme: changeTheme,
            toggleTheme,
            supportedThemes: SUPPORTED_THEMES,
        }),
        [theme, changeTheme, toggleTheme],
    );

    return createElement(ThemeContext.Provider, { value }, children);
}

export function useTheme() {
    const context = useContext(ThemeContext);

    if (!context) {
        throw new Error('useTheme must be used within a ThemeProvider');
    }

    return context;
}

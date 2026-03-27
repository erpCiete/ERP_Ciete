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

const resolveTheme = (value) => {
    const normalized = String(value || '').toLowerCase();
    return SUPPORTED_THEMES.includes(normalized) ? normalized : DEFAULT_THEME;
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

const getInitialTheme = () => {
    if (typeof window === 'undefined') {
        return DEFAULT_THEME;
    }

    const storedTheme = window.localStorage.getItem(STORAGE_KEY);
    if (storedTheme) {
        return resolveTheme(storedTheme);
    }

    return getSystemTheme();
};

const applyTheme = (theme) => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.style.colorScheme = theme;
};

export function ThemeProvider({ children }) {
    const [theme, setTheme] = useState(getInitialTheme);

    useEffect(() => {
        if (typeof window !== 'undefined') {
            window.localStorage.setItem(STORAGE_KEY, theme);
        }

        applyTheme(theme);
    }, [theme]);

    const changeTheme = useCallback((nextTheme) => {
        setTheme(resolveTheme(nextTheme));
    }, []);

    const toggleTheme = useCallback(() => {
        setTheme((currentTheme) => (currentTheme === 'dark' ? 'light' : 'dark'));
    }, []);

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

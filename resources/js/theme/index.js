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
const DEFAULT_VISUAL_STYLE = 'ciete_moderno';
const VISUAL_STYLE_STORAGE_KEY = 'ciete.visualStyle';
const DEFAULT_WORKSPACE_CONTEXT = 'moeve';
const WORKSPACE_CONTEXT_STORAGE_KEY = 'ciete.workspaceContext';
export const SUPPORTED_THEMES = ['light', 'dark'];
export const SUPPORTED_VISUAL_STYLES = ['ciete_excel', 'ciete_moderno'];
export const SUPPORTED_WORKSPACE_CONTEXTS = ['moeve', 'repsol', 'otros', 'todos'];

const ThemeContext = createContext(undefined);

const normalizeTheme = (value) => {
    const normalized = String(value || '').toLowerCase();
    return SUPPORTED_THEMES.includes(normalized) ? normalized : null;
};

const normalizeVisualStyle = (value) => {
    const normalized = String(value || '').toLowerCase();
    return SUPPORTED_VISUAL_STYLES.includes(normalized) ? normalized : null;
};

const normalizeWorkspaceContext = (value) => {
    const normalized = String(value || '').toLowerCase();
    return SUPPORTED_WORKSPACE_CONTEXTS.includes(normalized) ? normalized : null;
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

const getStoredVisualStylePreference = () => {
    if (typeof window === 'undefined') {
        return DEFAULT_VISUAL_STYLE;
    }

    try {
        return normalizeVisualStyle(window.localStorage.getItem(VISUAL_STYLE_STORAGE_KEY)) ?? DEFAULT_VISUAL_STYLE;
    } catch {
        return DEFAULT_VISUAL_STYLE;
    }
};

const getStoredWorkspaceContextPreference = () => {
    if (typeof window === 'undefined') {
        return DEFAULT_WORKSPACE_CONTEXT;
    }

    try {
        return normalizeWorkspaceContext(window.localStorage.getItem(WORKSPACE_CONTEXT_STORAGE_KEY)) ?? DEFAULT_WORKSPACE_CONTEXT;
    } catch {
        return DEFAULT_WORKSPACE_CONTEXT;
    }
};

const resolveTheme = (themePreference, systemTheme) => themePreference ?? systemTheme;
const THEME_SWITCHING_CLASS = 'theme-switching';

const applyTheme = (theme) => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.style.colorScheme = theme;
};

const applyVisualStyle = (visualStyle) => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.setAttribute('data-visual-style', visualStyle);
};

const applyWorkspaceContext = (workspaceContext) => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.setAttribute('data-workspace-context', workspaceContext);
};

const animateThemeSwitch = () => {
    if (typeof document === 'undefined' || typeof window === 'undefined') {
        return;
    }

    const root = document.documentElement;
    root.classList.add(THEME_SWITCHING_CLASS);
    window.clearTimeout(window.__cieteThemeSwitchTimer);
    window.__cieteThemeSwitchTimer = window.setTimeout(() => {
        root.classList.remove(THEME_SWITCHING_CLASS);
    }, 220);
};

export function ThemeProvider({ children, initialVisualStyle = null, initialWorkspaceContext = null }) {
    const [themePreference, setThemePreference] = useState(getStoredThemePreference);
    const [visualStyle, setVisualStylePreference] = useState(
        () => normalizeVisualStyle(initialVisualStyle) ?? getStoredVisualStylePreference()
    );
    const [workspaceContext, setWorkspaceContextPreference] = useState(
        () => normalizeWorkspaceContext(initialWorkspaceContext) ?? getStoredWorkspaceContextPreference()
    );
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

    useEffect(() => {
        if (typeof window !== 'undefined') {
            try {
                window.localStorage.setItem(VISUAL_STYLE_STORAGE_KEY, visualStyle);
            } catch {
                // Ignore storage failures and keep the in-memory style working.
            }
        }

        applyVisualStyle(visualStyle);
    }, [visualStyle]);

    useEffect(() => {
        const normalizedInitialVisualStyle = normalizeVisualStyle(initialVisualStyle);
        if (normalizedInitialVisualStyle) {
            setVisualStylePreference(normalizedInitialVisualStyle);
        }
    }, [initialVisualStyle]);

    useEffect(() => {
        const normalizedInitialContext = normalizeWorkspaceContext(initialWorkspaceContext);
        if (normalizedInitialContext) {
            setWorkspaceContextPreference(normalizedInitialContext);
        }
    }, [initialWorkspaceContext]);

    useEffect(() => {
        if (typeof window !== 'undefined') {
            try {
                window.localStorage.setItem(WORKSPACE_CONTEXT_STORAGE_KEY, workspaceContext);
            } catch {
                // Ignore storage failures and keep the in-memory context working.
            }
        }

        applyWorkspaceContext(workspaceContext);
    }, [workspaceContext]);

    const changeTheme = useCallback((nextTheme) => {
        const normalizedTheme = normalizeTheme(nextTheme);

        if (!normalizedTheme) {
            return;
        }

        animateThemeSwitch();
        setThemePreference(normalizedTheme);
    }, []);

    const toggleTheme = useCallback(() => {
        animateThemeSwitch();
        setThemePreference((currentThemePreference) => {
            const activeTheme = resolveTheme(currentThemePreference, systemTheme);
            return activeTheme === 'dark' ? 'light' : 'dark';
        });
    }, [systemTheme]);

    const changeVisualStyle = useCallback((nextVisualStyle) => {
        const normalizedVisualStyle = normalizeVisualStyle(nextVisualStyle);

        if (!normalizedVisualStyle) {
            return;
        }

        animateThemeSwitch();
        setVisualStylePreference(normalizedVisualStyle);
    }, []);

    const changeWorkspaceContext = useCallback((nextWorkspaceContext) => {
        const normalizedWorkspaceContext = normalizeWorkspaceContext(nextWorkspaceContext);

        if (!normalizedWorkspaceContext) {
            return;
        }

        setWorkspaceContextPreference(normalizedWorkspaceContext);
    }, []);

    const value = useMemo(
        () => ({
            theme,
            setTheme: changeTheme,
            toggleTheme,
            supportedThemes: SUPPORTED_THEMES,
            visualStyle,
            setVisualStyle: changeVisualStyle,
            supportedVisualStyles: SUPPORTED_VISUAL_STYLES,
            workspaceContext,
            setWorkspaceContext: changeWorkspaceContext,
            supportedWorkspaceContexts: SUPPORTED_WORKSPACE_CONTEXTS,
        }),
        [theme, changeTheme, toggleTheme, visualStyle, changeVisualStyle, workspaceContext, changeWorkspaceContext],
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

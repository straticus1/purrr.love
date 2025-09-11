import React, { createContext, useContext, useEffect, useState } from 'react';
import { theme, Colors, type Theme } from '@/styles/design-tokens';

type ThemeMode = 'light' | 'dark' | 'system';

interface ThemeContextType {
  mode: ThemeMode;
  effectiveTheme: 'light' | 'dark';
  colors: Colors;
  toggleTheme: () => void;
  setTheme: (mode: ThemeMode) => void;
}

const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

interface ThemeProviderProps {
  children: React.ReactNode;
  defaultTheme?: ThemeMode;
}

const THEME_STORAGE_KEY = 'purrr-love-theme';

export const ThemeProvider: React.FC<ThemeProviderProps> = ({
  children,
  defaultTheme = 'system',
}) => {
  const [mode, setMode] = useState<ThemeMode>(() => {
    if (typeof window !== 'undefined') {
      const stored = localStorage.getItem(THEME_STORAGE_KEY) as ThemeMode;
      return stored || defaultTheme;
    }
    return defaultTheme;
  });

  const [systemTheme, setSystemTheme] = useState<'light' | 'dark'>(() => {
    if (typeof window !== 'undefined') {
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    return 'light';
  });

  // Listen for system theme changes
  useEffect(() => {
    if (typeof window === 'undefined') return;

    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    const handleChange = (e: MediaQueryListEvent) => {
      setSystemTheme(e.matches ? 'dark' : 'light');
    };

    mediaQuery.addEventListener('change', handleChange);
    return () => mediaQuery.removeEventListener('change', handleChange);
  }, []);

  // Determine the effective theme
  const effectiveTheme = mode === 'system' ? systemTheme : mode;

  // Update document class and localStorage when theme changes
  useEffect(() => {
    if (typeof window === 'undefined') return;

    const root = window.document.documentElement;
    const isDark = effectiveTheme === 'dark';

    // Update document class
    root.classList.toggle('dark', isDark);
    root.classList.toggle('light', !isDark);

    // Update CSS custom properties
    const themeColors = isDark ? theme.dark.colors : theme.light.colors;
    
    // Set CSS variables for the current theme
    root.style.setProperty('--color-background-primary', themeColors.background.primary);
    root.style.setProperty('--color-background-secondary', themeColors.background.secondary);
    root.style.setProperty('--color-background-tertiary', themeColors.background.tertiary);
    root.style.setProperty('--color-text-primary', themeColors.text.primary);
    root.style.setProperty('--color-text-secondary', themeColors.text.secondary);
    root.style.setProperty('--color-text-muted', themeColors.text.muted);
    root.style.setProperty('--color-border', themeColors.border);

    // Store preference
    localStorage.setItem(THEME_STORAGE_KEY, mode);
  }, [mode, effectiveTheme]);

  const toggleTheme = () => {
    setMode(prev => {
      if (prev === 'light') return 'dark';
      if (prev === 'dark') return 'system';
      return 'light';
    });
  };

  const setTheme = (newMode: ThemeMode) => {
    setMode(newMode);
  };

  const value: ThemeContextType = {
    mode,
    effectiveTheme,
    colors: effectiveTheme === 'dark' ? theme.dark.colors : theme.light.colors,
    toggleTheme,
    setTheme,
  };

  return (
    <ThemeContext.Provider value={value}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = (): ThemeContextType => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};

// Theme-aware styled component helper
export const useThemedStyles = () => {
  const { colors, effectiveTheme } = useTheme();
  
  return {
    colors,
    isDark: effectiveTheme === 'dark',
    isLight: effectiveTheme === 'light',
    
    // Common style combinations
    card: {
      backgroundColor: colors.background.secondary,
      borderColor: colors.border,
      color: colors.text.primary,
    },
    
    input: {
      backgroundColor: colors.background.primary,
      borderColor: colors.border,
      color: colors.text.primary,
      '&::placeholder': {
        color: colors.text.muted,
      },
    },
    
    button: {
      primary: {
        backgroundColor: colors.primary[500],
        color: colors.light.bg.primary,
        '&:hover': {
          backgroundColor: colors.primary[600],
        },
      },
      secondary: {
        backgroundColor: colors.background.tertiary,
        color: colors.text.primary,
        borderColor: colors.border,
        '&:hover': {
          backgroundColor: colors.background.secondary,
        },
      },
    },
    
    text: {
      primary: { color: colors.text.primary },
      secondary: { color: colors.text.secondary },
      muted: { color: colors.text.muted },
    },
  };
};
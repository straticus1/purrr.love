/**
 * 🎨 Purrr.love Design Tokens
 * Comprehensive design system with colors, typography, spacing, and animations
 */

export const colors = {
  // Brand Colors
  primary: {
    50: '#fef7ee',
    100: '#fdedd3',
    200: '#fbd8a5',
    300: '#f8bc6d',
    400: '#f49533',
    500: '#f1770b',
    600: '#e25d06',
    700: '#bb4509',
    800: '#95380e',
    900: '#7a2f0f',
    950: '#421505',
  },
  secondary: {
    50: '#f5f3ff',
    100: '#ede9fe',
    200: '#ddd6fe',
    300: '#c4b5fd',
    400: '#a78bfa',
    500: '#8b5cf6',
    600: '#7c3aed',
    700: '#6d28d9',
    800: '#5b21b6',
    900: '#4c1d95',
    950: '#2e1065',
  },
  
  // Semantic Colors
  success: {
    50: '#f0fdf4',
    100: '#dcfce7',
    200: '#bbf7d0',
    300: '#86efac',
    400: '#4ade80',
    500: '#22c55e',
    600: '#16a34a',
    700: '#15803d',
    800: '#166534',
    900: '#14532d',
    950: '#052e16',
  },
  warning: {
    50: '#fffbeb',
    100: '#fef3c7',
    200: '#fde68a',
    300: '#fcd34d',
    400: '#fbbf24',
    500: '#f59e0b',
    600: '#d97706',
    700: '#b45309',
    800: '#92400e',
    900: '#78350f',
    950: '#451a03',
  },
  error: {
    50: '#fef2f2',
    100: '#fee2e2',
    200: '#fecaca',
    300: '#fca5a5',
    400: '#f87171',
    500: '#ef4444',
    600: '#dc2626',
    700: '#b91c1c',
    800: '#991b1b',
    900: '#7f1d1d',
    950: '#450a0a',
  },
  info: {
    50: '#eff6ff',
    100: '#dbeafe',
    200: '#bfdbfe',
    300: '#93c5fd',
    400: '#60a5fa',
    500: '#3b82f6',
    600: '#2563eb',
    700: '#1d4ed8',
    800: '#1e40af',
    900: '#1e3a8a',
    950: '#172554',
  },

  // Cat-themed Colors
  cat: {
    orange: '#f97316',
    cream: '#fef3c7',
    brown: '#92400e',
    black: '#1f2937',
    white: '#f9fafb',
    gray: '#6b7280',
    tabby: '#d97706',
    calico: '#f59e0b',
  },

  // Neutral Grays
  gray: {
    50: '#f9fafb',
    100: '#f3f4f6',
    200: '#e5e7eb',
    300: '#d1d5db',
    400: '#9ca3af',
    500: '#6b7280',
    600: '#4b5563',
    700: '#374151',
    800: '#1f2937',
    900: '#111827',
    950: '#030712',
  },

  // Dark Mode Colors
  dark: {
    bg: {
      primary: '#0f172a',
      secondary: '#1e293b',
      tertiary: '#334155',
    },
    text: {
      primary: '#f8fafc',
      secondary: '#cbd5e1',
      muted: '#94a3b8',
    },
    border: '#475569',
  },

  // Light Mode Colors
  light: {
    bg: {
      primary: '#ffffff',
      secondary: '#f8fafc',
      tertiary: '#f1f5f9',
    },
    text: {
      primary: '#0f172a',
      secondary: '#475569',
      muted: '#64748b',
    },
    border: '#e2e8f0',
  },
} as const;

export const typography = {
  fontFamily: {
    sans: [
      'Inter',
      'system-ui',
      '-apple-system',
      'BlinkMacSystemFont',
      '"Segoe UI"',
      'Roboto',
      '"Helvetica Neue"',
      'Arial',
      '"Noto Sans"',
      'sans-serif',
    ],
    mono: [
      '"JetBrains Mono"',
      'Consolas',
      '"Liberation Mono"',
      'Menlo',
      'Courier',
      'monospace',
    ],
    display: [
      '"Cal Sans"',
      'Inter',
      'system-ui',
      'sans-serif',
    ],
  },
  
  fontSize: {
    xs: ['0.75rem', { lineHeight: '1rem' }],
    sm: ['0.875rem', { lineHeight: '1.25rem' }],
    base: ['1rem', { lineHeight: '1.5rem' }],
    lg: ['1.125rem', { lineHeight: '1.75rem' }],
    xl: ['1.25rem', { lineHeight: '1.75rem' }],
    '2xl': ['1.5rem', { lineHeight: '2rem' }],
    '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
    '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
    '5xl': ['3rem', { lineHeight: '1' }],
    '6xl': ['3.75rem', { lineHeight: '1' }],
    '7xl': ['4.5rem', { lineHeight: '1' }],
    '8xl': ['6rem', { lineHeight: '1' }],
    '9xl': ['8rem', { lineHeight: '1' }],
  },

  fontWeight: {
    thin: '100',
    extralight: '200',
    light: '300',
    normal: '400',
    medium: '500',
    semibold: '600',
    bold: '700',
    extrabold: '800',
    black: '900',
  },
} as const;

export const spacing = {
  px: '1px',
  0: '0px',
  0.5: '0.125rem',
  1: '0.25rem',
  1.5: '0.375rem',
  2: '0.5rem',
  2.5: '0.625rem',
  3: '0.75rem',
  3.5: '0.875rem',
  4: '1rem',
  5: '1.25rem',
  6: '1.5rem',
  7: '1.75rem',
  8: '2rem',
  9: '2.25rem',
  10: '2.5rem',
  11: '2.75rem',
  12: '3rem',
  14: '3.5rem',
  16: '4rem',
  20: '5rem',
  24: '6rem',
  28: '7rem',
  32: '8rem',
  36: '9rem',
  40: '10rem',
  44: '11rem',
  48: '12rem',
  52: '13rem',
  56: '14rem',
  60: '15rem',
  64: '16rem',
  72: '18rem',
  80: '20rem',
  96: '24rem',
} as const;

export const borderRadius = {
  none: '0px',
  sm: '0.125rem',
  DEFAULT: '0.25rem',
  md: '0.375rem',
  lg: '0.5rem',
  xl: '0.75rem',
  '2xl': '1rem',
  '3xl': '1.5rem',
  full: '9999px',
} as const;

export const shadows = {
  sm: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
  DEFAULT: '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
  md: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
  lg: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
  xl: '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
  '2xl': '0 25px 50px -12px rgb(0 0 0 / 0.25)',
  inner: 'inset 0 2px 4px 0 rgb(0 0 0 / 0.05)',
  
  // Custom cat-themed shadows
  glow: '0 0 20px rgb(139 92 246 / 0.3)',
  purr: '0 0 30px rgb(249 115 22 / 0.2)',
  whisker: '0 2px 10px rgb(0 0 0 / 0.1)',
} as const;

export const animations = {
  // Duration
  duration: {
    instant: '0ms',
    fast: '150ms',
    normal: '300ms',
    slow: '500ms',
    slower: '750ms',
    slowest: '1000ms',
  },

  // Easing
  easing: {
    linear: 'linear',
    in: 'cubic-bezier(0.4, 0, 1, 1)',
    out: 'cubic-bezier(0, 0, 0.2, 1)',
    inOut: 'cubic-bezier(0.4, 0, 0.2, 1)',
    
    // Custom cat-like easing curves
    purr: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
    pounce: 'cubic-bezier(0.175, 0.885, 0.32, 1.275)',
    stretch: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
  },

  // Keyframes
  keyframes: {
    fadeIn: {
      '0%': { opacity: '0' },
      '100%': { opacity: '1' },
    },
    fadeOut: {
      '0%': { opacity: '1' },
      '100%': { opacity: '0' },
    },
    slideInUp: {
      '0%': { transform: 'translateY(100%)', opacity: '0' },
      '100%': { transform: 'translateY(0)', opacity: '1' },
    },
    slideInDown: {
      '0%': { transform: 'translateY(-100%)', opacity: '0' },
      '100%': { transform: 'translateY(0)', opacity: '1' },
    },
    scaleIn: {
      '0%': { transform: 'scale(0.8)', opacity: '0' },
      '100%': { transform: 'scale(1)', opacity: '1' },
    },
    bounce: {
      '0%, 20%, 53%, 80%, 100%': { transform: 'translate3d(0,0,0)' },
      '40%, 43%': { transform: 'translate3d(0,-30px,0)' },
      '70%': { transform: 'translate3d(0,-15px,0)' },
      '90%': { transform: 'translate3d(0,-4px,0)' },
    },
    
    // Cat-themed animations
    purr: {
      '0%, 100%': { transform: 'translateY(0px)' },
      '50%': { transform: 'translateY(-2px)' },
    },
    wiggle: {
      '0%, 100%': { transform: 'rotate(-3deg)' },
      '50%': { transform: 'rotate(3deg)' },
    },
    pawPrint: {
      '0%': { transform: 'scale(0) rotate(45deg)', opacity: '0' },
      '50%': { transform: 'scale(1.2) rotate(45deg)', opacity: '0.8' },
      '100%': { transform: 'scale(1) rotate(45deg)', opacity: '1' },
    },
  },
} as const;

export const breakpoints = {
  sm: '640px',
  md: '768px',
  lg: '1024px',
  xl: '1280px',
  '2xl': '1536px',
} as const;

export const zIndex = {
  hide: -1,
  auto: 'auto',
  base: 0,
  docked: 10,
  dropdown: 1000,
  sticky: 1100,
  banner: 1200,
  overlay: 1300,
  modal: 1400,
  popover: 1500,
  skipLink: 1600,
  toast: 1700,
  tooltip: 1800,
} as const;

// Theme configuration
export const theme = {
  light: {
    colors: {
      ...colors,
      background: colors.light.bg,
      text: colors.light.text,
      border: colors.light.border,
    },
  },
  dark: {
    colors: {
      ...colors,
      background: colors.dark.bg,
      text: colors.dark.text,
      border: colors.dark.border,
    },
  },
} as const;

// Component variants
export const variants = {
  button: {
    primary: {
      backgroundColor: colors.primary[500],
      color: colors.light.bg.primary,
      '&:hover': {
        backgroundColor: colors.primary[600],
      },
    },
    secondary: {
      backgroundColor: colors.secondary[500],
      color: colors.light.bg.primary,
      '&:hover': {
        backgroundColor: colors.secondary[600],
      },
    },
    success: {
      backgroundColor: colors.success[500],
      color: colors.light.bg.primary,
      '&:hover': {
        backgroundColor: colors.success[600],
      },
    },
    warning: {
      backgroundColor: colors.warning[500],
      color: colors.light.bg.primary,
      '&:hover': {
        backgroundColor: colors.warning[600],
      },
    },
    error: {
      backgroundColor: colors.error[500],
      color: colors.light.bg.primary,
      '&:hover': {
        backgroundColor: colors.error[600],
      },
    },
  },
} as const;

export type Colors = typeof colors;
export type Typography = typeof typography;
export type Spacing = typeof spacing;
export type Animations = typeof animations;
export type Theme = typeof theme;
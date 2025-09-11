import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import { devtools } from 'zustand/middleware';

export interface User {
  id: string;
  email: string;
  username: string;
  name: string;
  avatar?: string;
  role: 'user' | 'admin' | 'moderator';
  level: number;
  experience: number;
  coins: number;
  premiumCoins?: number;
  subscriptionTier: 'free' | 'premium' | 'pro';
  subscriptionExpires?: string;
  preferences: {
    theme: 'light' | 'dark' | 'system';
    notifications: boolean;
    language: string;
    timezone: string;
  };
  stats: {
    totalCats: number;
    totalGamesPlayed: number;
    totalTimeSpent: number;
    achievementsUnlocked: number;
  };
  createdAt: string;
  lastLoginAt: string;
}

interface AuthState {
  // State
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
  loginAttempts: number;
  lastLoginAttempt: number | null;
  
  // Actions
  login: (credentials: LoginCredentials) => Promise<void>;
  logout: () => void;
  register: (userData: RegisterData) => Promise<void>;
  updateProfile: (updates: Partial<User>) => Promise<void>;
  refreshToken: () => Promise<void>;
  updateSubscription: (tier: User['subscriptionTier'], expires?: string) => void;
  addExperience: (points: number) => void;
  addCoins: (amount: number) => void;
  spendCoins: (amount: number) => boolean;
  addPremiumCoins: (amount: number) => void;
  spendPremiumCoins: (amount: number) => boolean;
  clearError: () => void;
  reset: () => void;
}

interface LoginCredentials {
  email: string;
  password: string;
  rememberMe?: boolean;
}

interface RegisterData {
  email: string;
  username: string;
  name: string;
  password: string;
  acceptTerms: boolean;
}

const API_BASE_URL = import.meta.env.VITE_API_URL || '/api';

// Rate limiting for login attempts
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_DURATION = 15 * 60 * 1000; // 15 minutes

export const useAuthStore = create<AuthState>()(
  devtools(
    persist(
      (set, get) => ({
        // Initial state
        user: null,
        isAuthenticated: false,
        isLoading: false,
        error: null,
        loginAttempts: 0,
        lastLoginAttempt: null,

        // Actions
        login: async (credentials: LoginCredentials) => {
          const state = get();
          
          // Check rate limiting
          if (state.loginAttempts >= MAX_LOGIN_ATTEMPTS) {
            const timeSinceLastAttempt = Date.now() - (state.lastLoginAttempt || 0);
            if (timeSinceLastAttempt < LOCKOUT_DURATION) {
              const remainingTime = Math.ceil((LOCKOUT_DURATION - timeSinceLastAttempt) / (60 * 1000));
              set({ 
                error: `Too many login attempts. Please try again in ${remainingTime} minutes.`,
                isLoading: false 
              });
              return;
            } else {
              // Reset attempts after lockout period
              set({ loginAttempts: 0, lastLoginAttempt: null });
            }
          }

          set({ isLoading: true, error: null });

          try {
            const response = await fetch(`${API_BASE_URL}/auth/login`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify(credentials),
              credentials: 'include',
            });

            if (!response.ok) {
              const errorData = await response.json().catch(() => ({}));
              throw new Error(errorData.message || 'Login failed');
            }

            const { user, token } = await response.json();

            // Store token in localStorage if remember me is checked
            if (credentials.rememberMe && token) {
              localStorage.setItem('auth_token', token);
            }

            set({
              user: {
                ...user,
                lastLoginAt: new Date().toISOString(),
              },
              isAuthenticated: true,
              isLoading: false,
              error: null,
              loginAttempts: 0,
              lastLoginAttempt: null,
            });

          } catch (error) {
            const errorMessage = error instanceof Error ? error.message : 'Login failed';
            
            set({
              error: errorMessage,
              isLoading: false,
              loginAttempts: state.loginAttempts + 1,
              lastLoginAttempt: Date.now(),
            });
            
            throw error;
          }
        },

        logout: () => {
          // Clear auth token
          localStorage.removeItem('auth_token');
          sessionStorage.removeItem('auth_token');

          // Call logout endpoint
          fetch(`${API_BASE_URL}/auth/logout`, {
            method: 'POST',
            credentials: 'include',
          }).catch(() => {
            // Ignore logout errors - token is already cleared locally
          });

          set({
            user: null,
            isAuthenticated: false,
            error: null,
            loginAttempts: 0,
            lastLoginAttempt: null,
          });
        },

        register: async (userData: RegisterData) => {
          set({ isLoading: true, error: null });

          try {
            const response = await fetch(`${API_BASE_URL}/auth/register`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify(userData),
              credentials: 'include',
            });

            if (!response.ok) {
              const errorData = await response.json().catch(() => ({}));
              throw new Error(errorData.message || 'Registration failed');
            }

            const { user, token } = await response.json();

            // Store token
            if (token) {
              localStorage.setItem('auth_token', token);
            }

            set({
              user: {
                ...user,
                createdAt: new Date().toISOString(),
                lastLoginAt: new Date().toISOString(),
              },
              isAuthenticated: true,
              isLoading: false,
              error: null,
            });

          } catch (error) {
            const errorMessage = error instanceof Error ? error.message : 'Registration failed';
            set({
              error: errorMessage,
              isLoading: false,
            });
            throw error;
          }
        },

        updateProfile: async (updates: Partial<User>) => {
          const { user } = get();
          if (!user) return;

          set({ isLoading: true, error: null });

          try {
            const response = await fetch(`${API_BASE_URL}/auth/profile`, {
              method: 'PUT',
              headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
              },
              body: JSON.stringify(updates),
              credentials: 'include',
            });

            if (!response.ok) {
              const errorData = await response.json().catch(() => ({}));
              throw new Error(errorData.message || 'Profile update failed');
            }

            const updatedUser = await response.json();

            set({
              user: { ...user, ...updatedUser },
              isLoading: false,
              error: null,
            });

          } catch (error) {
            const errorMessage = error instanceof Error ? error.message : 'Profile update failed';
            set({
              error: errorMessage,
              isLoading: false,
            });
            throw error;
          }
        },

        refreshToken: async () => {
          try {
            const token = localStorage.getItem('auth_token');
            if (!token) return;

            const response = await fetch(`${API_BASE_URL}/auth/refresh`, {
              method: 'POST',
              headers: {
                'Authorization': `Bearer ${token}`,
              },
              credentials: 'include',
            });

            if (!response.ok) {
              throw new Error('Token refresh failed');
            }

            const { user: updatedUser, token: newToken } = await response.json();

            if (newToken) {
              localStorage.setItem('auth_token', newToken);
            }

            if (updatedUser) {
              set({ user: updatedUser });
            }

          } catch (error) {
            // If refresh fails, logout user
            get().logout();
            throw error;
          }
        },

        updateSubscription: (tier: User['subscriptionTier'], expires?: string) => {
          const { user } = get();
          if (!user) return;

          set({
            user: {
              ...user,
              subscriptionTier: tier,
              subscriptionExpires: expires,
            }
          });
        },

        addExperience: (points: number) => {
          const { user } = get();
          if (!user) return;

          const newExperience = user.experience + points;
          const newLevel = Math.floor(newExperience / 1000) + 1; // 1000 XP per level

          set({
            user: {
              ...user,
              experience: newExperience,
              level: Math.max(user.level, newLevel),
            }
          });

          // If user leveled up, you could trigger a celebration here
          if (newLevel > user.level) {
            // Dispatch level up event
            window.dispatchEvent(new CustomEvent('levelUp', { 
              detail: { oldLevel: user.level, newLevel, experience: newExperience }
            }));
          }
        },

        addCoins: (amount: number) => {
          const { user } = get();
          if (!user) return;

          set({
            user: {
              ...user,
              coins: user.coins + amount,
            }
          });
        },

        spendCoins: (amount: number) => {
          const { user } = get();
          if (!user || user.coins < amount) return false;

          set({
            user: {
              ...user,
              coins: user.coins - amount,
            }
          });

          return true;
        },

        addPremiumCoins: (amount: number) => {
          const { user } = get();
          if (!user) return;

          set({
            user: {
              ...user,
              premiumCoins: (user.premiumCoins || 0) + amount,
            }
          });
        },

        spendPremiumCoins: (amount: number) => {
          const { user } = get();
          if (!user || (user.premiumCoins || 0) < amount) return false;

          set({
            user: {
              ...user,
              premiumCoins: (user.premiumCoins || 0) - amount,
            }
          });

          return true;
        },

        clearError: () => {
          set({ error: null });
        },

        reset: () => {
          set({
            user: null,
            isAuthenticated: false,
            isLoading: false,
            error: null,
            loginAttempts: 0,
            lastLoginAttempt: null,
          });
        },
      }),
      {
        name: 'auth-storage',
        storage: createJSONStorage(() => localStorage),
        partialize: (state) => ({
          user: state.user,
          isAuthenticated: state.isAuthenticated,
          loginAttempts: state.loginAttempts,
          lastLoginAttempt: state.lastLoginAttempt,
        }),
      }
    ),
    {
      name: 'auth-store',
    }
  )
);

// Selectors for easier access to specific parts of the state
export const useUser = () => useAuthStore((state) => state.user);
export const useIsAuthenticated = () => useAuthStore((state) => state.isAuthenticated);
export const useAuthLoading = () => useAuthStore((state) => state.isLoading);
export const useAuthError = () => useAuthStore((state) => state.error);

// Initialize auth state from stored token
export const initializeAuth = async () => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    try {
      await useAuthStore.getState().refreshToken();
    } catch (error) {
      console.error('Failed to initialize auth:', error);
      useAuthStore.getState().logout();
    }
  }
};
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

export interface LoginCredentials {
  email: string;
  password: string;
  rememberMe?: boolean;
}

export interface RegisterData {
  email: string;
  username: string;
  name: string;
  password: string;
  acceptTerms: boolean;
}

export interface AuthResponse {
  user: User;
  token: string;
}
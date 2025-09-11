import {createSlice, createAsyncThunk, PayloadAction} from '@reduxjs/toolkit';
import {ApiService} from '@/services/ApiService';
import {User, LoginCredentials, RegisterData} from '@/types/auth';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}

const initialState: AuthState = {
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: false,
  error: null,
};

// Async thunks
export const login = createAsyncThunk(
  'auth/login',
  async (credentials: LoginCredentials, {rejectWithValue}) => {
    try {
      const response = await ApiService.login(credentials);
      return response;
    } catch (error: any) {
      return rejectWithValue(error.message || 'Login failed');
    }
  },
);

export const register = createAsyncThunk(
  'auth/register',
  async (userData: RegisterData, {rejectWithValue}) => {
    try {
      const response = await ApiService.register(userData);
      return response;
    } catch (error: any) {
      return rejectWithValue(error.message || 'Registration failed');
    }
  },
);

export const refreshToken = createAsyncThunk(
  'auth/refreshToken',
  async (_, {getState, rejectWithValue}) => {
    try {
      const state = getState() as {auth: AuthState};
      const currentToken = state.auth.token;
      
      if (!currentToken) {
        throw new Error('No token available');
      }
      
      const response = await ApiService.refreshToken(currentToken);
      return response;
    } catch (error: any) {
      return rejectWithValue(error.message || 'Token refresh failed');
    }
  },
);

export const updateProfile = createAsyncThunk(
  'auth/updateProfile',
  async (updates: Partial<User>, {rejectWithValue}) => {
    try {
      const response = await ApiService.updateProfile(updates);
      return response;
    } catch (error: any) {
      return rejectWithValue(error.message || 'Profile update failed');
    }
  },
);

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    logout: (state) => {
      state.user = null;
      state.token = null;
      state.isAuthenticated = false;
      state.error = null;
    },
    clearError: (state) => {
      state.error = null;
    },
    addCoins: (state, action: PayloadAction<number>) => {
      if (state.user) {
        state.user.coins += action.payload;
      }
    },
    spendCoins: (state, action: PayloadAction<number>) => {
      if (state.user && state.user.coins >= action.payload) {
        state.user.coins -= action.payload;
      }
    },
    addPremiumCoins: (state, action: PayloadAction<number>) => {
      if (state.user) {
        state.user.premiumCoins = (state.user.premiumCoins || 0) + action.payload;
      }
    },
    spendPremiumCoins: (state, action: PayloadAction<number>) => {
      if (state.user && (state.user.premiumCoins || 0) >= action.payload) {
        state.user.premiumCoins = (state.user.premiumCoins || 0) - action.payload;
      }
    },
    addExperience: (state, action: PayloadAction<number>) => {
      if (state.user) {
        state.user.experience += action.payload;
        // Calculate new level (1000 XP per level)
        const newLevel = Math.floor(state.user.experience / 1000) + 1;
        if (newLevel > state.user.level) {
          state.user.level = newLevel;
        }
      }
    },
  },
  extraReducers: (builder) => {
    // Login
    builder
      .addCase(login.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(login.fulfilled, (state, action) => {
        state.isLoading = false;
        state.user = action.payload.user;
        state.token = action.payload.token;
        state.isAuthenticated = true;
        state.error = null;
      })
      .addCase(login.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
        state.isAuthenticated = false;
      });

    // Register
    builder
      .addCase(register.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(register.fulfilled, (state, action) => {
        state.isLoading = false;
        state.user = action.payload.user;
        state.token = action.payload.token;
        state.isAuthenticated = true;
        state.error = null;
      })
      .addCase(register.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
        state.isAuthenticated = false;
      });

    // Refresh token
    builder
      .addCase(refreshToken.pending, (state) => {
        state.isLoading = true;
      })
      .addCase(refreshToken.fulfilled, (state, action) => {
        state.isLoading = false;
        state.token = action.payload.token;
        if (action.payload.user) {
          state.user = action.payload.user;
        }
      })
      .addCase(refreshToken.rejected, (state) => {
        state.isLoading = false;
        // If refresh fails, logout user
        state.user = null;
        state.token = null;
        state.isAuthenticated = false;
      });

    // Update profile
    builder
      .addCase(updateProfile.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(updateProfile.fulfilled, (state, action) => {
        state.isLoading = false;
        state.user = {...state.user, ...action.payload};
      })
      .addCase(updateProfile.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });
  },
});

export const {
  logout,
  clearError,
  addCoins,
  spendCoins,
  addPremiumCoins,
  spendPremiumCoins,
  addExperience,
} = authSlice.actions;

export default authSlice.reducer;
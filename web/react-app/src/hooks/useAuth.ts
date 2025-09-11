import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useAuthStore } from '@/store/authStore';
import { apiClient, handleApiError } from '@/lib/api';

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

export const useAuth = () => {
  const queryClient = useQueryClient();
  const { user, isAuthenticated, login, register, logout, updateProfile } = useAuthStore();

  // Login mutation
  const loginMutation = useMutation({
    mutationFn: async (credentials: LoginCredentials) => {
      await login(credentials);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user'] });
    },
  });

  // Register mutation
  const registerMutation = useMutation({
    mutationFn: async (userData: RegisterData) => {
      await register(userData);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user'] });
    },
  });

  // Update profile mutation
  const updateProfileMutation = useMutation({
    mutationFn: async (updates: any) => {
      await updateProfile(updates);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user'] });
    },
  });

  // Logout mutation
  const logoutMutation = useMutation({
    mutationFn: async () => {
      logout();
    },
    onSuccess: () => {
      queryClient.clear();
    },
  });

  // User profile query
  const userQuery = useQuery({
    queryKey: ['user'],
    queryFn: () => apiClient.get('/auth/profile'),
    enabled: isAuthenticated,
    staleTime: 1000 * 60 * 5, // 5 minutes
  });

  return {
    // State
    user,
    isAuthenticated,
    isLoading: loginMutation.isPending || registerMutation.isPending || userQuery.isLoading,
    error: loginMutation.error || registerMutation.error || userQuery.error,

    // Actions
    login: loginMutation.mutate,
    register: registerMutation.mutate,
    updateProfile: updateProfileMutation.mutate,
    logout: logoutMutation.mutate,

    // Status
    isLoginLoading: loginMutation.isPending,
    isRegisterLoading: registerMutation.isPending,
    isProfileLoading: updateProfileMutation.isPending,
  };
};
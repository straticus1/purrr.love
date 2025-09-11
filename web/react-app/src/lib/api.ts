import axios, { AxiosResponse, AxiosError } from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || '/api';

// Create axios instance with default config
export const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor for error handling
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError) => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      localStorage.removeItem('auth_token');
      sessionStorage.removeItem('auth_token');
      // Redirect to login page
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// API helper functions
export const apiClient = {
  get: <T>(url: string) => api.get<T>(url).then(res => res.data),
  post: <T>(url: string, data?: any) => api.post<T>(url, data).then(res => res.data),
  put: <T>(url: string, data?: any) => api.put<T>(url, data).then(res => res.data),
  patch: <T>(url: string, data?: any) => api.patch<T>(url, data).then(res => res.data),
  delete: <T>(url: string) => api.delete<T>(url).then(res => res.data),
};

// Error handling utility
export const handleApiError = (error: unknown): string => {
  if (axios.isAxiosError(error)) {
    if (error.response?.data?.message) {
      return error.response.data.message;
    }
    if (error.response?.status === 404) {
      return 'Resource not found';
    }
    if (error.response?.status >= 500) {
      return 'Server error occurred. Please try again later.';
    }
    if (error.message) {
      return error.message;
    }
  }
  return 'An unexpected error occurred';
};
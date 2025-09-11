import {User, LoginCredentials, RegisterData, AuthResponse} from '@/types/auth';

const API_BASE_URL = __DEV__ 
  ? 'http://localhost:3000/api'  // Development
  : 'https://api.purrr.love/api'; // Production

class ApiServiceClass {
  private baseURL: string;
  private token: string | null = null;

  constructor(baseURL: string) {
    this.baseURL = baseURL;
  }

  setToken(token: string | null) {
    this.token = token;
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {},
  ): Promise<T> {
    const url = `${this.baseURL}${endpoint}`;
    
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      ...options.headers,
    };

    if (this.token) {
      headers.Authorization = `Bearer ${this.token}`;
    }

    const config: RequestInit = {
      ...options,
      headers,
    };

    try {
      const response = await fetch(url, config);
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();
      return data;
    } catch (error) {
      if (error instanceof TypeError) {
        // Network error
        throw new Error('Network error. Please check your connection.');
      }
      throw error;
    }
  }

  // Auth endpoints
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response = await this.request<AuthResponse>('/auth/login', {
      method: 'POST',
      body: JSON.stringify(credentials),
    });
    
    this.setToken(response.token);
    return response;
  }

  async register(userData: RegisterData): Promise<AuthResponse> {
    const response = await this.request<AuthResponse>('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData),
    });
    
    this.setToken(response.token);
    return response;
  }

  async refreshToken(currentToken: string): Promise<AuthResponse> {
    const response = await this.request<AuthResponse>('/auth/refresh', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${currentToken}`,
      },
    });
    
    this.setToken(response.token);
    return response;
  }

  async logout(): Promise<void> {
    await this.request<void>('/auth/logout', {
      method: 'POST',
    });
    
    this.setToken(null);
  }

  async updateProfile(updates: Partial<User>): Promise<User> {
    return this.request<User>('/auth/profile', {
      method: 'PUT',
      body: JSON.stringify(updates),
    });
  }

  // Cat endpoints
  async getCats(): Promise<any[]> {
    return this.request<any[]>('/cats');
  }

  async getCat(catId: string): Promise<any> {
    return this.request<any>(`/cats/${catId}`);
  }

  async createCat(catData: any): Promise<any> {
    return this.request<any>('/cats', {
      method: 'POST',
      body: JSON.stringify(catData),
    });
  }

  async updateCat(catId: string, updates: any): Promise<any> {
    return this.request<any>(`/cats/${catId}`, {
      method: 'PUT',
      body: JSON.stringify(updates),
    });
  }

  async deleteCat(catId: string): Promise<void> {
    return this.request<void>(`/cats/${catId}`, {
      method: 'DELETE',
    });
  }

  // Store endpoints
  async getStoreItems(): Promise<any[]> {
    return this.request<any[]>('/store/items');
  }

  async purchaseItem(itemId: string, quantity: number): Promise<any> {
    return this.request<any>('/store/purchase', {
      method: 'POST',
      body: JSON.stringify({ itemId, quantity }),
    });
  }

  async getInventory(): Promise<any> {
    return this.request<any>('/store/inventory');
  }

  // Game endpoints
  async submitGameScore(gameType: string, score: number): Promise<any> {
    return this.request<any>('/games/score', {
      method: 'POST',
      body: JSON.stringify({ gameType, score }),
    });
  }

  async getLeaderboard(gameType: string): Promise<any[]> {
    return this.request<any[]>(`/games/leaderboard/${gameType}`);
  }

  // Upload endpoints
  async uploadImage(imageUri: string, type: 'avatar' | 'cat' | 'general'): Promise<{url: string}> {
    const formData = new FormData();
    formData.append('image', {
      uri: imageUri,
      type: 'image/jpeg',
      name: 'image.jpg',
    } as any);
    formData.append('type', type);

    const response = await fetch(`${this.baseURL}/upload/image`, {
      method: 'POST',
      headers: {
        'Content-Type': 'multipart/form-data',
        ...(this.token && { Authorization: `Bearer ${this.token}` }),
      },
      body: formData,
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || 'Upload failed');
    }

    return response.json();
  }
}

export const ApiService = new ApiServiceClass(API_BASE_URL);
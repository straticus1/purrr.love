/**
 * 🐱 Purrr.love Node.js SDK - Main Client
 * Main client class for interacting with the Purrr.love API
 */

import axios, { AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';
import { 
  Cat, 
  User, 
  ApiKey, 
  TradingOffer, 
  CatShow,
  VRInteraction,
  HealthDevice,
  MultiplayerSession 
} from '../models';
import { 
  PurrrLoveError, 
  AuthenticationError, 
  RateLimitError,
  NotFoundError,
  ValidationError,
  ServerError,
  NetworkError,
  TimeoutError
} from '../exceptions';
import { 
  ClientConfig, 
  RequestOptions, 
  ResponseData,
  CatData,
  UserData,
  ApiKeyData,
  TradingOfferData,
  CatShowData,
  VRInteractionData,
  HealthDeviceData,
  MultiplayerSessionData
} from '../types';
import { API_ENDPOINTS, DEFAULT_TIMEOUT, MAX_RETRIES } from '../constants';

export class PurrrLoveClient {
  private readonly baseUrl: string;
  private readonly apiKey?: string;
  private readonly axiosInstance: AxiosInstance;
  private readonly config: ClientConfig;

  constructor(config: ClientConfig) {
    this.config = {
      timeout: DEFAULT_TIMEOUT,
      maxRetries: MAX_RETRIES,
      retryDelay: 1000,
      ...config
    };

    this.baseUrl = this.config.baseUrl.replace(/\/$/, '');
    this.apiKey = this.config.apiKey;

    this.axiosInstance = axios.create({
      baseURL: this.baseUrl,
      timeout: this.config.timeout,
      headers: {
        'Content-Type': 'application/json',
        'User-Agent': `PurrrLove-NodeJS-SDK/${this.config.version || '1.0.0'}`
      }
    });

    this.setupInterceptors();
  }

  /**
   * Set up axios interceptors for authentication and error handling
   */
  private setupInterceptors(): void {
    // Request interceptor for authentication
    this.axiosInstance.interceptors.request.use(
      (config) => {
        if (this.apiKey) {
          config.headers['X-API-Key'] = this.apiKey;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Response interceptor for error handling
    this.axiosInstance.interceptors.response.use(
      (response: AxiosResponse) => response,
      async (error) => {
        if (error.response) {
          const { status, data } = error.response;
          
          switch (status) {
            case 401:
              throw new AuthenticationError('Invalid API key or authentication failed');
            case 403:
              throw new ValidationError('Permission denied');
            case 404:
              throw new NotFoundError('Resource not found');
            case 429:
              const retryAfter = error.response.headers['retry-after'] || 60;
              throw new RateLimitError(`Rate limit exceeded. Retry after ${retryAfter} seconds`);
            case 500:
              throw new ServerError('Server error occurred');
            default:
              if (status >= 400) {
                const message = data?.error?.message || `HTTP ${status} error`;
                throw new PurrrLoveError(message, status);
              }
          }
        } else if (error.code === 'ECONNABORTED') {
          throw new TimeoutError(`Request timed out after ${this.config.timeout}ms`);
        } else if (error.code === 'ENOTFOUND' || error.code === 'ECONNREFUSED') {
          throw new NetworkError('Network error occurred');
        }
        
        throw new NetworkError(`Request failed: ${error.message}`);
      }
    );
  }

  /**
   * Make an HTTP request with retry logic
   */
  private async makeRequest<T>(
    method: string,
    endpoint: string,
    data?: any,
    options?: RequestOptions
  ): Promise<T> {
    const config: AxiosRequestConfig = {
      method,
      url: endpoint,
      data: method !== 'GET' ? data : undefined,
      params: method === 'GET' ? data : undefined,
      ...options
    };

    let lastError: Error;
    
    for (let attempt = 0; attempt <= this.config.maxRetries; attempt++) {
      try {
        const response = await this.axiosInstance.request(config);
        return response.data;
      } catch (error) {
        lastError = error as Error;
        
        // Don't retry on certain errors
        if (error instanceof AuthenticationError || 
            error instanceof ValidationError || 
            error instanceof NotFoundError ||
            error instanceof PermissionError) {
          throw error;
        }
        
        // Wait before retrying
        if (attempt < this.config.maxRetries) {
          await this.delay(this.config.retryDelay * Math.pow(2, attempt));
        }
      }
    }
    
    throw lastError!;
  }

  /**
   * Delay function for retry logic
   */
  private delay(ms: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  // ===== Cat Management =====

  /**
   * Get user's cats
   */
  async getCats(limit = 50, offset = 0): Promise<Cat[]> {
    const response = await this.makeRequest<ResponseData<CatData[]>>(
      'GET',
      API_ENDPOINTS.CATS,
      { limit, offset }
    );
    
    return response.data.map(catData => new Cat(catData));
  }

  /**
   * Get a specific cat by ID
   */
  async getCat(catId: number): Promise<Cat> {
    const response = await this.makeRequest<ResponseData<CatData>>(
      'GET',
      `${API_ENDPOINTS.CATS}/${catId}`
    );
    
    return new Cat(response.data);
  }

  /**
   * Create a new cat
   */
  async createCat(catData: Partial<CatData>): Promise<Cat> {
    const response = await this.makeRequest<ResponseData<CatData>>(
      'POST',
      API_ENDPOINTS.CATS,
      catData
    );
    
    return new Cat(response.data);
  }

  /**
   * Update a cat's information
   */
  async updateCat(catId: number, updates: Partial<CatData>): Promise<Cat> {
    const response = await this.makeRequest<ResponseData<CatData>>(
      'PUT',
      `${API_ENDPOINTS.CATS}/${catId}`,
      updates
    );
    
    return new Cat(response.data);
  }

  /**
   * Delete a cat
   */
  async deleteCat(catId: number): Promise<boolean> {
    await this.makeRequest('DELETE', `${API_ENDPOINTS.CATS}/${catId}`);
    return true;
  }

  // ===== Cat Activities =====

  /**
   * Play with a cat
   */
  async playWithCat(catId: number, gameType: string, duration = 10): Promise<any> {
    const response = await this.makeRequest<ResponseData<any>>(
      'POST',
      `${API_ENDPOINTS.CATS}/${catId}/play`,
      { game_type: gameType, duration }
    );
    
    return response.data;
  }

  /**
   * Train a cat
   */
  async trainCat(catId: number, command: string, difficulty = 'normal'): Promise<any> {
    const response = await this.makeRequest<ResponseData<any>>(
      'POST',
      `${API_ENDPOINTS.CATS}/${catId}/train`,
      { command, difficulty }
    );
    
    return response.data;
  }

  /**
   * Care for a cat
   */
  async careForCat(catId: number, careType: string, options?: any): Promise<any> {
    const data = { care_type: careType, ...options };
    const response = await this.makeRequest<ResponseData<any>>(
      'POST',
      `${API_ENDPOINTS.CATS}/${catId}/care`,
      data
    );
    
    return response.data;
  }

  // ===== VR Interactions =====

  /**
   * Start a VR interaction session
   */
  async startVRSession(catId: number, vrDevice = 'webvr'): Promise<any> {
    const response = await this.makeRequest<ResponseData<any>>(
      'POST',
      `${API_ENDPOINTS.CATS}/${catId}/vr_interaction`,
      { vr_device: vrDevice }
    );
    
    return response.data;
  }

  /**
   * Perform VR interaction
   */
  async vrInteract(sessionId: string, interactionType: string, options?: any): Promise<any> {
    const data = { interaction_type: interactionType, ...options };
    const response = await this.makeRequest<ResponseData<any>>(
      'POST',
      `${API_ENDPOINTS.VR}/${sessionId}/interact`,
      data
    );
    
    return response.data;
  }

  // ===== AI Learning =====

  /**
   * Get AI learning insights for a cat
   */
  async getAIInsights(catId: number): Promise<any> {
    const response = await this.makeRequest<ResponseData<any>>(
      'GET',
      `${API_ENDPOINTS.CATS}/${catId}/ai_learning`
    );
    
    return response.data;
  }

  // ===== Trading =====

  /**
   * Get available trading offers
   */
  async getTradingOffers(filters?: any): Promise<TradingOffer[]> {
    const response = await this.makeRequest<ResponseData<TradingOfferData[]>>(
      'GET',
      API_ENDPOINTS.TRADING_OFFERS,
      filters
    );
    
    return response.data.map(offerData => new TradingOffer(offerData));
  }

  /**
   * Create a trading offer
   */
  async createTradingOffer(offerData: Partial<TradingOfferData>): Promise<TradingOffer> {
    const response = await this.makeRequest<ResponseData<TradingOfferData>>(
      'POST',
      API_ENDPOINTS.TRADING_OFFERS,
      offerData
    );
    
    return new TradingOffer(response.data);
  }

  /**
   * Accept a trading offer
   */
  async acceptTradingOffer(offerId: number): Promise<any> {
    const response = await this.makeRequest<ResponseData<any>>(
      'POST',
      `${API_ENDPOINTS.TRADING_OFFERS}/${offerId}/accept`
    );
    
    return response.data;
  }

  // ===== Cat Shows =====

  /**
   * Get available cat shows
   */
  async getCatShows(filters?: any): Promise<CatShow[]> {
    const response = await this.makeRequest<ResponseData<CatShowData[]>>(
      'GET',
      API_ENDPOINTS.SHOWS,
      filters
    );
    
    return response.data.map(showData => new CatShow(showData));
  }

  /**
   * Register a cat for a show
   */
  async registerCatForShow(catId: number, showId: number, categories: string[]): Promise<any> {
    const response = await this.makeRequest<ResponseData<any>>(
      'POST',
      `${API_ENDPOINTS.SHOWS}/${showId}/register`,
      { cat_id: catId, categories }
    );
    
    return response.data;
  }

  // ===== Multiplayer =====

  /**
   * Join a multiplayer room
   */
  async joinMultiplayerRoom(catId: number, roomType = 'playground'): Promise<any> {
    const response = await this.makeRequest<ResponseData<any>>(
      'POST',
      API_ENDPOINTS.MULTIPLAYER_JOIN,
      { cat_id: catId, room_type: roomType }
    );
    
    return response.data;
  }

  /**
   * Perform multiplayer action
   */
  async multiplayerAction(sessionId: string, actionType: string, options?: any): Promise<any> {
    const data = { action_type: actionType, ...options };
    const response = await this.makeRequest<ResponseData<any>>(
      'POST',
      `${API_ENDPOINTS.MULTIPLAYER}/${sessionId}/action`,
      data
    );
    
    return response.data;
  }

  // ===== Health Monitoring =====

  /**
   * Register a health monitoring device
   */
  async registerHealthDevice(catId: number, deviceData: any): Promise<any> {
    const response = await this.makeRequest<ResponseData<any>>(
      'POST',
      `${API_ENDPOINTS.CATS}/${catId}/health_monitoring`,
      deviceData
    );
    
    return response.data;
  }

  /**
   * Get cat health summary
   */
  async getHealthSummary(catId: number, timeframe = '7d'): Promise<any> {
    const response = await this.makeRequest<ResponseData<any>>(
      'GET',
      `${API_ENDPOINTS.CATS}/${catId}/health`,
      { timeframe }
    );
    
    return response.data;
  }

  // ===== API Key Management =====

  /**
   * Get user's API keys
   */
  async getApiKeys(): Promise<ApiKey[]> {
    const response = await this.makeRequest<ResponseData<ApiKeyData[]>>(
      'GET',
      API_ENDPOINTS.API_KEYS
    );
    
    return response.data.map(keyData => new ApiKey(keyData));
  }

  /**
   * Create a new API key
   */
  async createApiKey(keyData: Partial<ApiKeyData>): Promise<ApiKey> {
    const response = await this.makeRequest<ResponseData<ApiKeyData>>(
      'POST',
      API_ENDPOINTS.API_KEYS,
      keyData
    );
    
    return new ApiKey(response.data);
  }

  /**
   * Revoke an API key
   */
  async revokeApiKey(keyId: number): Promise<boolean> {
    await this.makeRequest('DELETE', `${API_ENDPOINTS.API_KEYS}/${keyId}`);
    return true;
  }

  // ===== Analytics =====

  /**
   * Get cat analytics
   */
  async getCatAnalytics(catId: number, timeframe = '30d', metrics?: string[]): Promise<any> {
    const params: any = { timeframe };
    if (metrics) params.metrics = metrics;
    
    const response = await this.makeRequest<ResponseData<any>>(
      'GET',
      `${API_ENDPOINTS.CATS}/${catId}/analytics`,
      params
    );
    
    return response.data;
  }

  /**
   * Get user statistics
   */
  async getUserStats(): Promise<any> {
    const response = await this.makeRequest<ResponseData<any>>(
      'GET',
      API_ENDPOINTS.USER_STATS
    );
    
    return response.data;
  }

  // ============================================================================
  // LOST PET FINDER SYSTEM
  // ============================================================================

  /**
   * Report a lost pet
   */
  async reportLostPet(petData: {
    name: string;
    breed: string;
    color: string;
    lastSeenLocation: string;
    lastSeenDate: string;
    description?: string;
    photos?: string[];
    facebookShareEnabled?: boolean;
  }): Promise<any> {
    const response = await this.makeRequest('POST', '/api/v2/lost_pet_finder/report', petData);
    return response.data;
  }

  /**
   * Search for lost pets
   */
  async searchLostPets(searchCriteria: {
    breed?: string;
    color?: string;
    ageRange?: { min: number; max: number };
    radiusKm?: number;
    latitude?: number;
    longitude?: number;
  }): Promise<any> {
    const response = await this.makeRequest('GET', '/api/v2/lost_pet_finder/search', undefined, searchCriteria);
    return response.data;
  }

  /**
   * Report a pet sighting
   */
  async reportPetSighting(sightingData: {
    lostPetReportId: number;
    location: string;
    sightingDate: string;
    description?: string;
    confidenceLevel?: 'low' | 'medium' | 'high';
  }): Promise<any> {
    const response = await this.makeRequest('POST', '/api/v2/lost_pet_finder/sighting', sightingData);
    return response.data;
  }

  /**
   * Mark pet as found
   */
  async markPetFound(reportId: number, foundData: {
    foundLocation?: string;
    foundDetails?: any;
  }): Promise<any> {
    const response = await this.makeRequest('PUT', '/api/v2/lost_pet_finder/found', foundData);
    return response.data;
  }

  /**
   * Get lost pet statistics
   */
  async getLostPetStatistics(): Promise<any> {
    const response = await this.makeRequest('GET', '/api/v2/lost_pet_finder/statistics');
    return response.data;
  }

  // ============================================================================
  // BLOCKCHAIN & NFT MANAGEMENT
  // ============================================================================

  /**
   * Mint an NFT for a cat
   */
  async mintCatNFT(catId: number, network: string = 'ethereum', metadata?: any): Promise<any> {
    const data = {
      cat_id: catId,
      network,
      metadata: metadata || {}
    };
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/blockchain?action=mint-nft', data);
    return response.data;
  }

  /**
   * Transfer NFT ownership
   */
  async transferNFT(nftId: number, toUserId: number, network: string = 'ethereum'): Promise<any> {
    const data = {
      nft_id: nftId,
      to_user_id: toUserId,
      network
    };
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/blockchain?action=transfer-nft', data);
    return response.data;
  }

  /**
   * Verify NFT ownership
   */
  async verifyNFTOwnership(nftId: number): Promise<any> {
    const response = await this.makeRequest('GET', `/api/v2/advanced_features/blockchain?action=verify-nft&nft_id=${nftId}`);
    return response.data;
  }

  /**
   * Get NFT collection
   */
  async getNFTCollection(network?: string): Promise<any> {
    const params: any = { action: 'collection' };
    if (network) params.network = network;
    const response = await this.makeRequest('GET', '/api/v2/advanced_features/blockchain', undefined, params);
    return response.data;
  }

  /**
   * Get blockchain statistics
   */
  async getBlockchainStatistics(): Promise<any> {
    const response = await this.makeRequest('GET', '/api/v2/advanced_features/blockchain?action=stats');
    return response.data;
  }

  // ============================================================================
  // MACHINE LEARNING PERSONALITY PREDICTION
  // ============================================================================

  /**
   * Predict cat personality using ML
   */
  async predictCatPersonality(catId: number, includeConfidence: boolean = true): Promise<any> {
    const params = { action: 'predict', cat_id: catId, confidence: includeConfidence };
    const response = await this.makeRequest('GET', '/api/v2/advanced_features/ml-personality', undefined, params);
    return response.data;
  }

  /**
   * Get personality insights
   */
  async getPersonalityInsights(catId: number): Promise<any> {
    const params = { action: 'insights', cat_id: catId };
    const response = await this.makeRequest('GET', '/api/v2/advanced_features/ml-personality', undefined, params);
    return response.data;
  }

  /**
   * Record behavior observation
   */
  async recordBehaviorObservation(catId: number, behaviorData: {
    type: string;
    intensity: number;
    duration: number;
    context: string;
  }): Promise<any> {
    const data = {
      action: 'observe',
      cat_id: catId,
      ...behaviorData
    };
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/ml-personality', data);
    return response.data;
  }

  /**
   * Update genetic data
   */
  async updateGeneticData(catId: number, geneticData: {
    heritageScore: number;
    coatPattern?: string;
    markers?: string;
  }): Promise<any> {
    const data = {
      action: 'genetic',
      cat_id: catId,
      heritage_score: geneticData.heritageScore,
      coat_pattern: geneticData.coatPattern,
      markers: geneticData.markers
    };
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/ml-personality', data);
    return response.data;
  }

  /**
   * Get ML training status
   */
  async getMLTrainingStatus(): Promise<any> {
    const response = await this.makeRequest('GET', '/api/v2/advanced_features/ml-personality?action=training');
    return response.data;
  }

  // ============================================================================
  // METAVERSE & VR WORLDS
  // ============================================================================

  /**
   * Create metaverse world
   */
  async createMetaverseWorld(worldData: {
    name: string;
    type: string;
    maxPlayers: number;
    accessLevel: string;
  }): Promise<any> {
    const data = {
      action: 'create-world',
      ...worldData
    };
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/metaverse', data);
    return response.data;
  }

  /**
   * Join metaverse world
   */
  async joinMetaverseWorld(worldId: number, catId?: number): Promise<any> {
    const data: any = {
      action: 'join-world',
      world_id: worldId
    };
    if (catId) data.cat_id = catId;
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/metaverse', data);
    return response.data;
  }

  /**
   * Leave metaverse world
   */
  async leaveMetaverseWorld(worldId: number): Promise<any> {
    const data = { action: 'leave-world', world_id: worldId };
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/metaverse', data);
    return response.data;
  }

  /**
   * List metaverse worlds
   */
  async listMetaverseWorlds(filters?: any): Promise<any> {
    const params: any = { action: 'worlds' };
    if (filters) Object.assign(params, filters);
    const response = await this.makeRequest('GET', '/api/v2/advanced_features/metaverse', undefined, params);
    return response.data;
  }

  /**
   * Perform VR interaction
   */
  async performVRInteraction(worldId: number, interactionData: {
    type: string;
    targetData?: any;
  }): Promise<any> {
    const data = {
      action: 'interact',
      world_id: worldId,
      ...interactionData
    };
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/metaverse', data);
    return response.data;
  }

  /**
   * Get metaverse statistics
   */
  async getMetaverseStatistics(): Promise<any> {
    const response = await this.makeRequest('GET', '/api/v2/advanced_features/metaverse?action=stats');
    return response.data;
  }

  // ============================================================================
  // WEBHOOK SYSTEM
  // ============================================================================

  /**
   * Create webhook
   */
  async createWebhook(webhookData: {
    url: string;
    events: string[];
    secret?: string;
    headers?: any;
  }): Promise<any> {
    const data = {
      action: 'create',
      ...webhookData
    };
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/webhooks', data);
    return response.data;
  }

  /**
   * List webhooks
   */
  async listWebhooks(): Promise<any> {
    const response = await this.makeRequest('GET', '/api/v2/advanced_features/webhooks?action=list');
    return response.data;
  }

  /**
   * Update webhook
   */
  async updateWebhook(webhookId: number, updates: any): Promise<any> {
    const data = {
      action: 'update',
      webhook_id: webhookId,
      ...updates
    };
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/webhooks', data);
    return response.data;
  }

  /**
   * Delete webhook
   */
  async deleteWebhook(webhookId: number): Promise<boolean> {
    const data = { action: 'delete', webhook_id: webhookId };
    await this.makeRequest('POST', '/api/v2/advanced_features/webhooks', data);
    return true;
  }

  /**
   * Test webhook
   */
  async testWebhook(webhookId: number): Promise<any> {
    const data = { action: 'test', webhook_id: webhookId };
    const response = await this.makeRequest('POST', '/api/v2/advanced_features/webhooks', data);
    return response.data;
  }

  /**
   * Get webhook logs
   */
  async getWebhookLogs(webhookId: number, limit: number = 100): Promise<any> {
    const params = {
      action: 'logs',
      webhook_id: webhookId,
      limit
    };
    const response = await this.makeRequest('GET', '/api/v2/advanced_features/webhooks', undefined, params);
    return response.data;
  }

  // ============================================================================
  // ANALYTICS & HEALTH
  // ============================================================================

  /**
   * Get analytics data
   */
  async getAnalyticsData(analyticsType: string = 'overview', filters?: any): Promise<any> {
    const params: any = { type: analyticsType };
    if (filters) Object.assign(params, filters);
    const response = await this.makeRequest('GET', '/web/analytics_dashboard.php', undefined, params);
    return response.data;
  }

  /**
   * Health check
   */
  async healthCheck(): Promise<any> {
    const response = await this.makeRequest('GET', '/api/health.php');
    return response;
  }

  /**
   * Get API info
   */
  async getApiInfo(): Promise<any> {
    const response = await this.makeRequest('GET', '/api/');
    return response;
  }

  /**
   * Get rate limit info
   */
  async getRateLimitInfo(): Promise<any> {
    const response = await this.makeRequest('GET', '/api/health.php');
    return {
      remaining: response.rate_limit_remaining || 'unknown',
      resetTime: response.rate_limit_reset || 'unknown'
    };
  }

  /**
   * Close the client and clean up resources
   */
  close(): void {
    // Clean up any ongoing requests or connections
    this.axiosInstance.defaults.signal?.abort();
  }
}

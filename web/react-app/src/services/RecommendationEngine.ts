import { apiClient } from '@/lib/api';
import { User } from '@/store/authStore';

export interface Recommendation {
  id: string;
  type: 'product' | 'content' | 'feature' | 'action';
  category: 'store' | 'health' | 'games' | 'social' | 'general';
  title: string;
  description: string;
  confidence: number; // 0-1
  reasoning: string;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  
  // Action details
  cta_text: string;
  cta_action: string;
  cta_params?: Record<string, any>;
  
  // Targeting
  user_segments: string[];
  personalization_factors: string[];
  
  // Business metrics
  expected_value?: number;
  expected_engagement?: number;
  conversion_probability?: number;
  
  // Metadata
  created_at: Date;
  expires_at?: Date;
  source: 'ml_model' | 'rule_based' | 'hybrid';
  model_version?: string;
  
  // A/B testing
  experiment_id?: string;
  variant?: string;
  
  // Feedback
  feedback?: {
    shown: boolean;
    clicked: boolean;
    converted: boolean;
    dismissed: boolean;
    rating?: number;
  };
}

export interface UserProfile {
  user_id: string;
  demographics: {
    age_group?: string;
    location?: string;
    signup_date: Date;
    platform: string;
  };
  behavioral_patterns: {
    activity_level: 'low' | 'medium' | 'high';
    preferred_times: string[];
    session_frequency: number;
    avg_session_duration: number;
    feature_usage: Record<string, number>;
  };
  preferences: {
    cat_breeds: string[];
    game_types: string[];
    content_categories: string[];
    notification_preferences: Record<string, boolean>;
  };
  purchase_behavior: {
    total_spent: number;
    avg_order_value: number;
    purchase_frequency: number;
    preferred_payment_methods: string[];
    price_sensitivity: 'low' | 'medium' | 'high';
  };
  engagement_metrics: {
    ltv: number;
    churn_risk: number;
    satisfaction_score: number;
    nps_score?: number;
  };
}

export interface RecommendationContext {
  page?: string;
  previous_actions?: string[];
  current_session_duration?: number;
  device_type?: string;
  time_of_day?: string;
  weather?: string;
  is_weekend?: boolean;
  recent_purchases?: string[];
  cart_contents?: string[];
  browsing_history?: string[];
}

export interface RecommendationRequest {
  user_id: string;
  context: RecommendationContext;
  categories?: string[];
  max_results?: number;
  exclude_seen?: boolean;
  include_experimental?: boolean;
  diversification_factor?: number; // 0-1, higher = more diverse
}

export interface ContentRecommendation extends Recommendation {
  content_type: 'article' | 'video' | 'tip' | 'tutorial' | 'news';
  content_url: string;
  thumbnail_url?: string;
  estimated_read_time?: number;
  tags: string[];
}

export interface ProductRecommendation extends Recommendation {
  product_id: string;
  product_name: string;
  price: number;
  discount?: number;
  rating: number;
  review_count: number;
  image_url: string;
  in_stock: boolean;
  similar_products: string[];
}

export interface FeatureRecommendation extends Recommendation {
  feature_name: string;
  feature_description: string;
  benefits: string[];
  tutorial_url?: string;
  prerequisites?: string[];
  estimated_setup_time?: number;
}

class RecommendationEngineClass {
  private userProfiles: Map<string, UserProfile> = new Map();
  private recommendationCache: Map<string, { recommendations: Recommendation[]; expiry: number }> = new Map();
  private abTestExperiments: Map<string, any> = new Map();

  // Initialize recommendation engine
  async initialize(): Promise<void> {
    try {
      // Load A/B test configurations
      const experiments = await apiClient.get('/recommendations/experiments');
      experiments.forEach((exp: any) => {
        this.abTestExperiments.set(exp.id, exp);
      });
      
      console.log('Recommendation engine initialized');
    } catch (error) {
      console.error('Failed to initialize recommendation engine:', error);
    }
  }

  // Get personalized recommendations
  async getRecommendations(request: RecommendationRequest): Promise<Recommendation[]> {
    const cacheKey = this.generateCacheKey(request);
    
    // Check cache
    const cached = this.recommendationCache.get(cacheKey);
    if (cached && Date.now() < cached.expiry) {
      return this.filterAndRankRecommendations(cached.recommendations, request);
    }

    try {
      // Get user profile
      const userProfile = await this.getUserProfile(request.user_id);
      
      // Generate recommendations from multiple sources
      const [
        mlRecommendations,
        ruleBasedRecommendations,
        collaborativeRecommendations,
      ] = await Promise.all([
        this.getMLRecommendations(request, userProfile),
        this.getRuleBasedRecommendations(request, userProfile),
        this.getCollaborativeRecommendations(request, userProfile),
      ]);

      // Combine and rank recommendations
      const combinedRecommendations = [
        ...mlRecommendations,
        ...ruleBasedRecommendations,
        ...collaborativeRecommendations,
      ];

      const rankedRecommendations = this.rankRecommendations(
        combinedRecommendations,
        userProfile,
        request
      );

      // Apply diversification
      const diversifiedRecommendations = this.diversifyRecommendations(
        rankedRecommendations,
        request.diversification_factor || 0.3
      );

      // Cache results
      this.recommendationCache.set(cacheKey, {
        recommendations: diversifiedRecommendations,
        expiry: Date.now() + 15 * 60 * 1000, // 15 minutes
      });

      return this.filterAndRankRecommendations(diversifiedRecommendations, request);
    } catch (error) {
      console.error('Failed to generate recommendations:', error);
      return this.getFallbackRecommendations(request);
    }
  }

  // Get ML-based recommendations
  private async getMLRecommendations(
    request: RecommendationRequest,
    userProfile: UserProfile
  ): Promise<Recommendation[]> {
    try {
      const response = await apiClient.post('/recommendations/ml', {
        user_profile: userProfile,
        context: request.context,
        max_results: Math.ceil((request.max_results || 10) * 0.6), // 60% from ML
      });

      return response.map((rec: any) => this.transformMLRecommendation(rec));
    } catch (error) {
      console.error('ML recommendations failed:', error);
      return [];
    }
  }

  // Get rule-based recommendations
  private async getRuleBasedRecommendations(
    request: RecommendationRequest,
    userProfile: UserProfile
  ): Promise<Recommendation[]> {
    const recommendations: Recommendation[] = [];

    // Rule 1: New user onboarding
    if (this.isNewUser(userProfile)) {
      recommendations.push(...this.getOnboardingRecommendations(userProfile));
    }

    // Rule 2: Abandoned cart recovery
    if (request.context.cart_contents && request.context.cart_contents.length > 0) {
      recommendations.push(...this.getCartRecoveryRecommendations(request.context.cart_contents, userProfile));
    }

    // Rule 3: Seasonal/timely recommendations
    recommendations.push(...this.getSeasonalRecommendations(userProfile));

    // Rule 4: Feature discovery
    if (userProfile.behavioral_patterns.feature_usage) {
      recommendations.push(...this.getFeatureDiscoveryRecommendations(userProfile));
    }

    // Rule 5: Health reminders
    recommendations.push(...this.getHealthReminders(userProfile));

    // Rule 6: Social recommendations
    if (userProfile.behavioral_patterns.activity_level === 'high') {
      recommendations.push(...this.getSocialRecommendations(userProfile));
    }

    return recommendations;
  }

  // Get collaborative filtering recommendations
  private async getCollaborativeRecommendations(
    request: RecommendationRequest,
    userProfile: UserProfile
  ): Promise<Recommendation[]> {
    try {
      const response = await apiClient.post('/recommendations/collaborative', {
        user_id: request.user_id,
        max_results: Math.ceil((request.max_results || 10) * 0.3), // 30% from collaborative
      });

      return response.map((rec: any) => this.transformCollaborativeRecommendation(rec));
    } catch (error) {
      console.error('Collaborative recommendations failed:', error);
      return [];
    }
  }

  // Get user profile (with caching)
  private async getUserProfile(userId: string): Promise<UserProfile> {
    if (this.userProfiles.has(userId)) {
      return this.userProfiles.get(userId)!;
    }

    try {
      const profile = await apiClient.get<UserProfile>(`/recommendations/profile/${userId}`);
      this.userProfiles.set(userId, profile);
      
      // Cache for 1 hour
      setTimeout(() => {
        this.userProfiles.delete(userId);
      }, 60 * 60 * 1000);

      return profile;
    } catch (error) {
      console.error('Failed to get user profile:', error);
      return this.getDefaultUserProfile(userId);
    }
  }

  // Rank recommendations based on multiple factors
  private rankRecommendations(
    recommendations: Recommendation[],
    userProfile: UserProfile,
    request: RecommendationRequest
  ): Recommendation[] {
    return recommendations
      .map(rec => ({
        ...rec,
        final_score: this.calculateRecommendationScore(rec, userProfile, request),
      }))
      .sort((a, b) => (b as any).final_score - (a as any).final_score);
  }

  // Calculate recommendation score
  private calculateRecommendationScore(
    rec: Recommendation,
    userProfile: UserProfile,
    request: RecommendationRequest
  ): number {
    let score = rec.confidence;

    // Priority boost
    const priorityMultiplier = {
      urgent: 1.5,
      high: 1.3,
      medium: 1.0,
      low: 0.8,
    };
    score *= priorityMultiplier[rec.priority];

    // Personalization boost
    if (rec.personalization_factors.length > 0) {
      score *= 1.2;
    }

    // Expected value boost
    if (rec.expected_value && rec.expected_value > 0) {
      score *= 1 + (rec.expected_value / 100); // Normalize by typical order value
    }

    // Context relevance
    if (this.isContextuallyRelevant(rec, request.context)) {
      score *= 1.15;
    }

    // Freshness factor (newer recommendations get slight boost)
    const hoursSinceCreated = (Date.now() - rec.created_at.getTime()) / (1000 * 60 * 60);
    if (hoursSinceCreated < 24) {
      score *= 1 + (24 - hoursSinceCreated) / 240; // Max 10% boost for newest
    }

    return score;
  }

  // Diversify recommendations to avoid over-focusing on one category
  private diversifyRecommendations(
    recommendations: Recommendation[],
    diversificationFactor: number
  ): Recommendation[] {
    if (diversificationFactor === 0) return recommendations;

    const categoryCount: Record<string, number> = {};
    const diversified: Recommendation[] = [];

    for (const rec of recommendations) {
      const categoryPenalty = (categoryCount[rec.category] || 0) * diversificationFactor;
      const adjustedScore = (rec as any).final_score * (1 - categoryPenalty);

      if (adjustedScore > 0.3) { // Minimum threshold
        diversified.push(rec);
        categoryCount[rec.category] = (categoryCount[rec.category] || 0) + 1;
      }

      if (diversified.length >= recommendations.length) break;
    }

    return diversified;
  }

  // Filter and apply final ranking
  private filterAndRankRecommendations(
    recommendations: Recommendation[],
    request: RecommendationRequest
  ): Recommendation[] {
    let filtered = recommendations;

    // Filter by categories if specified
    if (request.categories && request.categories.length > 0) {
      filtered = filtered.filter(rec => request.categories!.includes(rec.category));
    }

    // Exclude already seen recommendations if requested
    if (request.exclude_seen) {
      filtered = filtered.filter(rec => !rec.feedback?.shown);
    }

    // Exclude experimental if not requested
    if (!request.include_experimental) {
      filtered = filtered.filter(rec => !rec.experiment_id);
    }

    // Apply final limit
    return filtered.slice(0, request.max_results || 10);
  }

  // Track recommendation feedback
  async trackRecommendationFeedback(
    recommendationId: string,
    feedback: {
      action: 'shown' | 'clicked' | 'converted' | 'dismissed';
      rating?: number;
      metadata?: Record<string, any>;
    }
  ): Promise<void> {
    try {
      await apiClient.post('/recommendations/feedback', {
        recommendation_id: recommendationId,
        feedback,
        timestamp: new Date().toISOString(),
      });
    } catch (error) {
      console.error('Failed to track recommendation feedback:', error);
    }
  }

  // Get recommendation explanations
  async getRecommendationExplanation(recommendationId: string): Promise<{
    factors: Array<{ factor: string; weight: number; explanation: string }>;
    similar_users: Array<{ similarity: number; behavior: string }>;
    data_sources: string[];
    confidence_breakdown: Record<string, number>;
  }> {
    try {
      return await apiClient.get(`/recommendations/${recommendationId}/explanation`);
    } catch (error) {
      console.error('Failed to get recommendation explanation:', error);
      return {
        factors: [],
        similar_users: [],
        data_sources: [],
        confidence_breakdown: {},
      };
    }
  }

  // Helper methods for rule-based recommendations
  private isNewUser(profile: UserProfile): boolean {
    const daysSinceSignup = (Date.now() - profile.demographics.signup_date.getTime()) / (1000 * 60 * 60 * 24);
    return daysSinceSignup < 7;
  }

  private getOnboardingRecommendations(profile: UserProfile): FeatureRecommendation[] {
    return [
      {
        id: `onboarding_${Date.now()}`,
        type: 'feature',
        category: 'general',
        title: 'Add Your First Cat',
        description: 'Start your journey by adding your cat\'s profile to track their health and happiness.',
        confidence: 0.95,
        reasoning: 'New users benefit from completing their profile setup',
        priority: 'high',
        cta_text: 'Add Cat Profile',
        cta_action: 'navigate_to_add_cat',
        user_segments: ['new_users'],
        personalization_factors: ['signup_recent'],
        created_at: new Date(),
        source: 'rule_based',
        feature_name: 'cat_profile_creation',
        feature_description: 'Create detailed profiles for your cats',
        benefits: ['Health tracking', 'Personalized recommendations', 'Care reminders'],
        estimated_setup_time: 5,
      },
    ];
  }

  private getCartRecoveryRecommendations(cartItems: string[], profile: UserProfile): ProductRecommendation[] {
    return cartItems.slice(0, 2).map((itemId, index) => ({
      id: `cart_recovery_${itemId}_${Date.now()}`,
      type: 'product',
      category: 'store',
      title: 'Complete Your Purchase',
      description: 'Don\'t forget about the items in your cart! Complete your purchase and save 10%.',
      confidence: 0.8,
      reasoning: 'User has items in cart but hasn\'t completed purchase',
      priority: 'medium',
      cta_text: 'Complete Purchase',
      cta_action: 'navigate_to_cart',
      cta_params: { discount: 'CART10' },
      user_segments: ['cart_abandoners'],
      personalization_factors: ['cart_contents'],
      expected_value: 25,
      conversion_probability: 0.3,
      created_at: new Date(),
      expires_at: new Date(Date.now() + 24 * 60 * 60 * 1000), // 24 hours
      source: 'rule_based',
      product_id: itemId,
      product_name: `Product ${itemId}`,
      price: 19.99,
      discount: 0.1,
      rating: 4.5,
      review_count: 123,
      image_url: `/images/products/${itemId}.jpg`,
      in_stock: true,
      similar_products: [],
    }));
  }

  private getSeasonalRecommendations(profile: UserProfile): Recommendation[] {
    const now = new Date();
    const month = now.getMonth();
    const recommendations: Recommendation[] = [];

    // Winter health tips (Dec, Jan, Feb)
    if ([11, 0, 1].includes(month)) {
      recommendations.push({
        id: `seasonal_winter_${now.getTime()}`,
        type: 'content',
        category: 'health',
        title: 'Winter Cat Care Tips',
        description: 'Keep your cats healthy and happy during the cold winter months.',
        confidence: 0.7,
        reasoning: 'Seasonal health tips are relevant during winter',
        priority: 'low',
        cta_text: 'Read Tips',
        cta_action: 'navigate_to_content',
        cta_params: { content_id: 'winter_care_tips' },
        user_segments: ['all'],
        personalization_factors: ['seasonal'],
        created_at: new Date(),
        source: 'rule_based',
      });
    }

    return recommendations;
  }

  private getFeatureDiscoveryRecommendations(profile: UserProfile): FeatureRecommendation[] {
    const unusedFeatures = this.identifyUnusedFeatures(profile);
    
    return unusedFeatures.slice(0, 2).map(feature => ({
      id: `feature_discovery_${feature}_${Date.now()}`,
      type: 'feature',
      category: 'general',
      title: `Try ${feature.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}`,
      description: `Discover the benefits of our ${feature} feature.`,
      confidence: 0.6,
      reasoning: 'User hasn\'t explored this feature yet',
      priority: 'low',
      cta_text: 'Learn More',
      cta_action: 'show_feature_tutorial',
      cta_params: { feature },
      user_segments: ['feature_explorers'],
      personalization_factors: ['unused_features'],
      created_at: new Date(),
      source: 'rule_based',
      feature_name: feature,
      feature_description: `The ${feature} feature helps you manage your cats better`,
      benefits: ['Better organization', 'Improved tracking', 'Enhanced experience'],
    }));
  }

  private getHealthReminders(profile: UserProfile): Recommendation[] {
    // This would typically check cat health data and generate appropriate reminders
    return [];
  }

  private getSocialRecommendations(profile: UserProfile): Recommendation[] {
    return [
      {
        id: `social_${Date.now()}`,
        type: 'feature',
        category: 'social',
        title: 'Connect with Other Cat Lovers',
        description: 'Join our community to share photos and tips with fellow cat enthusiasts.',
        confidence: 0.5,
        reasoning: 'Active users often enjoy social features',
        priority: 'low',
        cta_text: 'Join Community',
        cta_action: 'navigate_to_social',
        user_segments: ['active_users'],
        personalization_factors: ['high_activity'],
        created_at: new Date(),
        source: 'rule_based',
      },
    ];
  }

  // Utility methods
  private generateCacheKey(request: RecommendationRequest): string {
    return `rec_${request.user_id}_${JSON.stringify(request.context)}_${request.categories?.join(',') || 'all'}`;
  }

  private transformMLRecommendation(mlRec: any): Recommendation {
    return {
      id: mlRec.id,
      type: mlRec.type,
      category: mlRec.category,
      title: mlRec.title,
      description: mlRec.description,
      confidence: mlRec.confidence,
      reasoning: mlRec.reasoning || 'ML model prediction',
      priority: mlRec.priority || 'medium',
      cta_text: mlRec.cta_text,
      cta_action: mlRec.cta_action,
      cta_params: mlRec.cta_params,
      user_segments: mlRec.user_segments || [],
      personalization_factors: mlRec.personalization_factors || [],
      expected_value: mlRec.expected_value,
      expected_engagement: mlRec.expected_engagement,
      conversion_probability: mlRec.conversion_probability,
      created_at: new Date(mlRec.created_at),
      expires_at: mlRec.expires_at ? new Date(mlRec.expires_at) : undefined,
      source: 'ml_model',
      model_version: mlRec.model_version,
    };
  }

  private transformCollaborativeRecommendation(colRec: any): Recommendation {
    return {
      id: colRec.id,
      type: colRec.type,
      category: colRec.category,
      title: colRec.title,
      description: colRec.description,
      confidence: colRec.similarity_score,
      reasoning: `Users similar to you also liked this`,
      priority: 'medium',
      cta_text: colRec.cta_text,
      cta_action: colRec.cta_action,
      user_segments: ['collaborative_filtered'],
      personalization_factors: ['user_similarity'],
      created_at: new Date(),
      source: 'ml_model',
    };
  }

  private isContextuallyRelevant(rec: Recommendation, context: RecommendationContext): boolean {
    // Check if recommendation is relevant to current context
    if (context.page && rec.category === 'store' && context.page !== 'store') return false;
    if (context.page === 'health' && rec.category === 'health') return true;
    return true; // Default to relevant
  }

  private identifyUnusedFeatures(profile: UserProfile): string[] {
    const allFeatures = ['health_tracking', 'social_sharing', 'game_achievements', 'premium_analytics'];
    const usedFeatures = Object.keys(profile.behavioral_patterns.feature_usage || {});
    return allFeatures.filter(feature => !usedFeatures.includes(feature));
  }

  private getDefaultUserProfile(userId: string): UserProfile {
    return {
      user_id: userId,
      demographics: {
        signup_date: new Date(),
        platform: 'web',
      },
      behavioral_patterns: {
        activity_level: 'medium',
        preferred_times: ['evening'],
        session_frequency: 3,
        avg_session_duration: 300,
        feature_usage: {},
      },
      preferences: {
        cat_breeds: [],
        game_types: [],
        content_categories: [],
        notification_preferences: {},
      },
      purchase_behavior: {
        total_spent: 0,
        avg_order_value: 0,
        purchase_frequency: 0,
        preferred_payment_methods: [],
        price_sensitivity: 'medium',
      },
      engagement_metrics: {
        ltv: 0,
        churn_risk: 0.5,
        satisfaction_score: 0.5,
      },
    };
  }

  private getFallbackRecommendations(request: RecommendationRequest): Recommendation[] {
    return [
      {
        id: `fallback_${Date.now()}`,
        type: 'feature',
        category: 'general',
        title: 'Explore Purrr.love',
        description: 'Discover all the amazing features we have for cat lovers!',
        confidence: 0.5,
        reasoning: 'Fallback recommendation',
        priority: 'low',
        cta_text: 'Learn More',
        cta_action: 'navigate_to_features',
        user_segments: ['all'],
        personalization_factors: [],
        created_at: new Date(),
        source: 'rule_based',
      },
    ];
  }
}

export const RecommendationEngine = new RecommendationEngineClass();
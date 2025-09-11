import { apiClient } from '@/lib/api';

export interface AIInsight {
  id: string;
  type: 'trend' | 'anomaly' | 'prediction' | 'recommendation' | 'alert';
  title: string;
  description: string;
  confidence: number; // 0-1
  importance: 'low' | 'medium' | 'high' | 'critical';
  category: 'user_behavior' | 'revenue' | 'engagement' | 'health' | 'performance';
  data: any;
  timestamp: Date;
  actionable: boolean;
  suggestedActions?: string[];
  relatedMetrics?: string[];
}

export interface PredictiveModel {
  id: string;
  name: string;
  type: 'regression' | 'classification' | 'clustering' | 'time_series';
  accuracy: number;
  lastTrained: Date;
  features: string[];
  targetVariable: string;
  predictions: any[];
}

export interface UserBehaviorPattern {
  pattern_id: string;
  pattern_type: 'engagement' | 'churn_risk' | 'spending' | 'activity';
  user_segment: string;
  confidence: number;
  description: string;
  affected_users: number;
  trend_direction: 'increasing' | 'decreasing' | 'stable';
  timeframe: string;
}

export interface BusinessInsight {
  insight_id: string;
  title: string;
  impact: 'high' | 'medium' | 'low';
  category: 'revenue_optimization' | 'user_retention' | 'feature_usage' | 'market_trends';
  description: string;
  supporting_data: any;
  recommended_actions: string[];
  potential_impact: {
    metric: string;
    estimated_change: number;
    timeframe: string;
  }[];
}

class AIInsightsEngineClass {
  private cache: Map<string, any> = new Map();
  private cacheExpiry: Map<string, number> = new Map();

  // Generate AI insights based on analytics data
  async generateInsights(dateRange: { startDate: Date; endDate: Date }): Promise<AIInsight[]> {
    const cacheKey = `insights_${dateRange.startDate.getTime()}_${dateRange.endDate.getTime()}`;
    
    if (this.isValidCache(cacheKey)) {
      return this.cache.get(cacheKey);
    }

    try {
      const insights = await apiClient.post<AIInsight[]>('/ai/insights', {
        startDate: dateRange.startDate.toISOString(),
        endDate: dateRange.endDate.toISOString(),
      });

      this.setCache(cacheKey, insights, 5 * 60 * 1000); // 5 minutes cache
      return insights;
    } catch (error) {
      console.error('Failed to generate AI insights:', error);
      return this.getMockInsights(); // Fallback to mock data
    }
  }

  // Analyze user behavior patterns
  async analyzeUserBehavior(userId?: string): Promise<UserBehaviorPattern[]> {
    const cacheKey = `behavior_${userId || 'all'}`;
    
    if (this.isValidCache(cacheKey)) {
      return this.cache.get(cacheKey);
    }

    try {
      const patterns = await apiClient.post<UserBehaviorPattern[]>('/ai/behavior-analysis', {
        userId,
        includeSegmentation: true,
        includePredictions: true,
      });

      this.setCache(cacheKey, patterns, 10 * 60 * 1000); // 10 minutes cache
      return patterns;
    } catch (error) {
      console.error('Failed to analyze user behavior:', error);
      return this.getMockBehaviorPatterns();
    }
  }

  // Generate business insights
  async generateBusinessInsights(): Promise<BusinessInsight[]> {
    const cacheKey = 'business_insights';
    
    if (this.isValidCache(cacheKey)) {
      return this.cache.get(cacheKey);
    }

    try {
      const insights = await apiClient.get<BusinessInsight[]>('/ai/business-insights');
      
      this.setCache(cacheKey, insights, 15 * 60 * 1000); // 15 minutes cache
      return insights;
    } catch (error) {
      console.error('Failed to generate business insights:', error);
      return this.getMockBusinessInsights();
    }
  }

  // Predict user churn risk
  async predictChurnRisk(userId: string): Promise<{
    risk_score: number;
    risk_level: 'low' | 'medium' | 'high';
    factors: Array<{ factor: string; impact: number; description: string }>;
    suggested_interventions: string[];
  }> {
    try {
      return await apiClient.post('/ai/churn-prediction', { userId });
    } catch (error) {
      console.error('Failed to predict churn risk:', error);
      return {
        risk_score: 0.3,
        risk_level: 'low',
        factors: [
          { factor: 'login_frequency', impact: 0.4, description: 'User login frequency has decreased' },
          { factor: 'session_duration', impact: 0.2, description: 'Average session time is below normal' },
        ],
        suggested_interventions: ['Send engagement email', 'Offer special promotion'],
      };
    }
  }

  // Predict revenue for next period
  async predictRevenue(period: 'week' | 'month' | 'quarter'): Promise<{
    predicted_revenue: number;
    confidence_interval: [number, number];
    key_drivers: Array<{ factor: string; contribution: number }>;
    scenario_analysis: Array<{ scenario: string; revenue: number; probability: number }>;
  }> {
    try {
      return await apiClient.post('/ai/revenue-prediction', { period });
    } catch (error) {
      console.error('Failed to predict revenue:', error);
      return {
        predicted_revenue: 15000,
        confidence_interval: [12000, 18000],
        key_drivers: [
          { factor: 'subscription_growth', contribution: 0.4 },
          { factor: 'virtual_goods_sales', contribution: 0.3 },
          { factor: 'user_acquisition', contribution: 0.3 },
        ],
        scenario_analysis: [
          { scenario: 'optimistic', revenue: 18500, probability: 0.3 },
          { scenario: 'baseline', revenue: 15000, probability: 0.4 },
          { scenario: 'pessimistic', revenue: 11500, probability: 0.3 },
        ],
      };
    }
  }

  // Analyze cat health trends
  async analyzeCatHealthTrends(): Promise<{
    overall_health_score: number;
    trending_conditions: Array<{ condition: string; trend: 'increasing' | 'decreasing'; severity: string }>;
    breed_insights: Array<{ breed: string; health_score: number; common_issues: string[] }>;
    preventive_recommendations: string[];
  }> {
    try {
      return await apiClient.get('/ai/cat-health-analysis');
    } catch (error) {
      console.error('Failed to analyze cat health trends:', error);
      return {
        overall_health_score: 8.2,
        trending_conditions: [
          { condition: 'obesity', trend: 'increasing', severity: 'moderate' },
          { condition: 'dental_issues', trend: 'decreasing', severity: 'low' },
        ],
        breed_insights: [
          { breed: 'Persian', health_score: 7.8, common_issues: ['respiratory', 'eye_problems'] },
          { breed: 'Maine Coon', health_score: 8.5, common_issues: ['hip_dysplasia', 'heart_conditions'] },
        ],
        preventive_recommendations: [
          'Increase exercise activities for overweight cats',
          'Schedule regular dental checkups',
          'Monitor respiratory health in flat-faced breeds',
        ],
      };
    }
  }

  // Generate personalized recommendations
  async generateRecommendations(userId: string, context: 'store' | 'games' | 'health' | 'general'): Promise<Array<{
    id: string;
    type: 'product' | 'activity' | 'feature' | 'content';
    title: string;
    description: string;
    confidence: number;
    reasoning: string;
    cta_text: string;
    cta_action: string;
    metadata?: any;
  }>> {
    const cacheKey = `recommendations_${userId}_${context}`;
    
    if (this.isValidCache(cacheKey)) {
      return this.cache.get(cacheKey);
    }

    try {
      const recommendations = await apiClient.post('/ai/recommendations', {
        userId,
        context,
        maxResults: 10,
      });

      this.setCache(cacheKey, recommendations, 30 * 60 * 1000); // 30 minutes cache
      return recommendations;
    } catch (error) {
      console.error('Failed to generate recommendations:', error);
      return this.getMockRecommendations(context);
    }
  }

  // Analyze feature usage patterns
  async analyzeFeatureUsage(): Promise<{
    most_used_features: Array<{ feature: string; usage_rate: number; user_satisfaction: number }>;
    underutilized_features: Array<{ feature: string; potential: string; barriers: string[] }>;
    feature_correlation: Array<{ feature_a: string; feature_b: string; correlation: number }>;
    recommendations: string[];
  }> {
    try {
      return await apiClient.get('/ai/feature-usage-analysis');
    } catch (error) {
      console.error('Failed to analyze feature usage:', error);
      return {
        most_used_features: [
          { feature: 'cat_feeding', usage_rate: 0.85, user_satisfaction: 4.2 },
          { feature: 'play_games', usage_rate: 0.72, user_satisfaction: 4.1 },
          { feature: 'store_browsing', usage_rate: 0.68, user_satisfaction: 3.8 },
        ],
        underutilized_features: [
          { feature: 'health_tracking', potential: 'high', barriers: ['complexity', 'unclear_value'] },
          { feature: 'social_features', potential: 'medium', barriers: ['privacy_concerns', 'discoverability'] },
        ],
        feature_correlation: [
          { feature_a: 'cat_feeding', feature_b: 'health_tracking', correlation: 0.67 },
          { feature_a: 'games', feature_b: 'store_purchases', correlation: 0.43 },
        ],
        recommendations: [
          'Simplify health tracking onboarding',
          'Add social sharing prompts after achievements',
          'Cross-promote health tracking in feeding flows',
        ],
      };
    }
  }

  // Detect anomalies in metrics
  async detectAnomalies(metrics: string[], timeframe: 'hour' | 'day' | 'week'): Promise<Array<{
    metric: string;
    anomaly_type: 'spike' | 'drop' | 'trend_break' | 'seasonal_deviation';
    severity: 'low' | 'medium' | 'high';
    detected_at: Date;
    current_value: number;
    expected_value: number;
    deviation: number;
    possible_causes: string[];
    suggested_actions: string[];
  }>> {
    try {
      return await apiClient.post('/ai/anomaly-detection', {
        metrics,
        timeframe,
        sensitivity: 'medium',
      });
    } catch (error) {
      console.error('Failed to detect anomalies:', error);
      return [];
    }
  }

  // Cache management
  private isValidCache(key: string): boolean {
    const expiry = this.cacheExpiry.get(key);
    if (!expiry || Date.now() > expiry) {
      this.cache.delete(key);
      this.cacheExpiry.delete(key);
      return false;
    }
    return this.cache.has(key);
  }

  private setCache(key: string, value: any, ttl: number): void {
    this.cache.set(key, value);
    this.cacheExpiry.set(key, Date.now() + ttl);
  }

  // Mock data for development/fallback
  private getMockInsights(): AIInsight[] {
    return [
      {
        id: 'insight_1',
        type: 'trend',
        title: 'User Engagement Increasing',
        description: 'Daily active users have increased by 15% over the past week',
        confidence: 0.89,
        importance: 'high',
        category: 'engagement',
        data: { growth_rate: 0.15, period: '7d' },
        timestamp: new Date(),
        actionable: true,
        suggestedActions: ['Maintain current content strategy', 'Consider expanding successful features'],
        relatedMetrics: ['dau', 'session_duration', 'retention_rate'],
      },
      {
        id: 'insight_2',
        type: 'anomaly',
        title: 'Revenue Drop Detected',
        description: 'Virtual goods revenue decreased by 8% yesterday, unusual for weekdays',
        confidence: 0.76,
        importance: 'medium',
        category: 'revenue',
        data: { drop_percentage: -0.08, timeframe: '1d' },
        timestamp: new Date(),
        actionable: true,
        suggestedActions: ['Check payment system status', 'Review recent price changes'],
        relatedMetrics: ['revenue', 'conversion_rate', 'cart_abandonment'],
      },
      {
        id: 'insight_3',
        type: 'prediction',
        title: 'Churn Risk Alert',
        description: '12% of premium users show early churn indicators',
        confidence: 0.82,
        importance: 'critical',
        category: 'user_behavior',
        data: { at_risk_users: 1200, total_premium: 10000 },
        timestamp: new Date(),
        actionable: true,
        suggestedActions: ['Launch retention campaign', 'Improve onboarding experience'],
        relatedMetrics: ['churn_rate', 'user_satisfaction', 'feature_adoption'],
      },
    ];
  }

  private getMockBehaviorPatterns(): UserBehaviorPattern[] {
    return [
      {
        pattern_id: 'pattern_1',
        pattern_type: 'engagement',
        user_segment: 'heavy_users',
        confidence: 0.87,
        description: 'Heavy users show 40% higher session duration on weekends',
        affected_users: 2500,
        trend_direction: 'increasing',
        timeframe: '30d',
      },
      {
        pattern_id: 'pattern_2',
        pattern_type: 'spending',
        user_segment: 'premium_subscribers',
        confidence: 0.92,
        description: 'Premium users spend 3x more on virtual goods after level 10',
        affected_users: 800,
        trend_direction: 'stable',
        timeframe: '60d',
      },
    ];
  }

  private getMockBusinessInsights(): BusinessInsight[] {
    return [
      {
        insight_id: 'biz_1',
        title: 'Premium Tier Pricing Optimization',
        impact: 'high',
        category: 'revenue_optimization',
        description: 'Analysis suggests 15% price increase would optimize revenue with minimal churn',
        supporting_data: { current_price: 9.99, suggested_price: 11.49, churn_impact: 0.02 },
        recommended_actions: ['A/B test new pricing', 'Grandfather existing users', 'Add value to justify increase'],
        potential_impact: [
          { metric: 'revenue', estimated_change: 0.13, timeframe: '6_months' },
          { metric: 'churn_rate', estimated_change: 0.02, timeframe: '3_months' },
        ],
      },
    ];
  }

  private getMockRecommendations(context: string) {
    const baseRecommendations = {
      store: [
        {
          id: 'rec_1',
          type: 'product' as const,
          title: 'Premium Cat Food Bundle',
          description: 'Based on your cats\' dietary preferences and health data',
          confidence: 0.85,
          reasoning: 'Your cats prefer salmon flavor and need higher protein',
          cta_text: 'Shop Now',
          cta_action: 'navigate_to_product',
          metadata: { product_id: 'food_bundle_1', discount: 0.1 },
        },
      ],
      games: [
        {
          id: 'rec_2',
          type: 'activity' as const,
          title: 'Laser Chase Challenge',
          description: 'Perfect for your cats\' energy level and play preferences',
          confidence: 0.78,
          reasoning: 'Your cats are most active in the evening and love chase games',
          cta_text: 'Play Now',
          cta_action: 'start_game',
          metadata: { game_id: 'laser_chase', difficulty: 'medium' },
        },
      ],
    };

    return baseRecommendations[context as keyof typeof baseRecommendations] || [];
  }
}

export const AIInsightsEngine = new AIInsightsEngineClass();
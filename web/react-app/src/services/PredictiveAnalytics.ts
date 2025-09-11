import { apiClient } from '@/lib/api';

export interface PredictionModel {
  id: string;
  name: string;
  type: 'regression' | 'classification' | 'time_series' | 'clustering';
  version: string;
  accuracy: number;
  lastTrained: Date;
  features: ModelFeature[];
  hyperparameters: Record<string, any>;
  performance_metrics: {
    mse?: number;
    rmse?: number;
    mae?: number;
    r2_score?: number;
    precision?: number;
    recall?: number;
    f1_score?: number;
    auc_score?: number;
  };
}

export interface ModelFeature {
  name: string;
  importance: number;
  type: 'numerical' | 'categorical' | 'boolean' | 'datetime';
  description: string;
}

export interface PredictionRequest {
  model_id: string;
  input_data: Record<string, any>;
  prediction_horizon?: string; // '1d', '7d', '30d', etc.
  confidence_level?: number; // 0.8, 0.9, 0.95
  include_explanation?: boolean;
}

export interface PredictionResult {
  prediction: any;
  confidence: number;
  confidence_interval?: [number, number];
  explanation?: {
    feature_importance: Array<{ feature: string; impact: number; value: any }>;
    similar_cases: Array<{ case_id: string; similarity: number; outcome: any }>;
    decision_path?: string[];
  };
  metadata: {
    model_version: string;
    prediction_time: Date;
    input_hash: string;
  };
}

export interface TimeSeriesData {
  timestamp: Date;
  value: number;
  metadata?: Record<string, any>;
}

export interface ForecastResult {
  forecast: TimeSeriesData[];
  confidence_bands: {
    lower: number[];
    upper: number[];
  };
  seasonality_components?: {
    trend: number[];
    seasonal: number[];
    residual: number[];
  };
  changepoints?: Array<{
    timestamp: Date;
    significance: number;
    direction: 'increase' | 'decrease';
  }>;
}

class PredictiveAnalyticsClass {
  private models: Map<string, PredictionModel> = new Map();
  private predictionCache: Map<string, { result: PredictionResult; expiry: number }> = new Map();

  // Initialize available models
  async initializeModels(): Promise<PredictionModel[]> {
    try {
      const models = await apiClient.get<PredictionModel[]>('/ml/models');
      models.forEach(model => this.models.set(model.id, model));
      return models;
    } catch (error) {
      console.error('Failed to initialize prediction models:', error);
      return this.getMockModels();
    }
  }

  // User churn prediction
  async predictUserChurn(userId: string, timeHorizon: '7d' | '30d' | '90d' = '30d'): Promise<{
    churn_probability: number;
    risk_level: 'low' | 'medium' | 'high';
    key_factors: Array<{ factor: string; impact: number; current_value: any; threshold: any }>;
    intervention_suggestions: Array<{ action: string; expected_impact: number; urgency: 'low' | 'medium' | 'high' }>;
    similar_users: Array<{ user_id: string; similarity: number; churned: boolean; intervention_used?: string }>;
  }> {
    const request: PredictionRequest = {
      model_id: 'churn_prediction_v3',
      input_data: { user_id: userId, prediction_horizon: timeHorizon },
      include_explanation: true,
    };

    try {
      const result = await this.makePrediction(request);
      
      return {
        churn_probability: result.prediction.probability,
        risk_level: result.prediction.probability > 0.7 ? 'high' : 
                   result.prediction.probability > 0.4 ? 'medium' : 'low',
        key_factors: result.explanation?.feature_importance.map(f => ({
          factor: f.feature,
          impact: f.impact,
          current_value: f.value,
          threshold: this.getThresholdForFeature(f.feature),
        })) || [],
        intervention_suggestions: this.generateChurnInterventions(result.prediction.probability),
        similar_users: result.explanation?.similar_cases.map(c => ({
          user_id: c.case_id,
          similarity: c.similarity,
          churned: c.outcome.churned,
          intervention_used: c.outcome.intervention,
        })) || [],
      };
    } catch (error) {
      console.error('Churn prediction failed:', error);
      return this.getMockChurnPrediction(userId);
    }
  }

  // Revenue forecasting
  async forecastRevenue(
    timeHorizon: '7d' | '30d' | '90d' | '365d',
    segment?: 'all' | 'subscriptions' | 'virtual_goods' | 'premium'
  ): Promise<{
    forecast: Array<{ date: string; predicted_revenue: number; confidence_lower: number; confidence_upper: number }>;
    total_predicted: number;
    growth_rate: number;
    key_drivers: Array<{ factor: string; contribution: number; explanation: string }>;
    scenarios: Array<{ name: string; probability: number; revenue: number; description: string }>;
    recommendations: string[];
  }> {
    try {
      const historicalData = await this.getHistoricalRevenue(segment);
      const forecastResult = await this.generateTimeSeries Forecast('revenue', historicalData, timeHorizon);
      
      return {
        forecast: forecastResult.forecast.map((point, index) => ({
          date: point.timestamp.toISOString(),
          predicted_revenue: point.value,
          confidence_lower: forecastResult.confidence_bands.lower[index],
          confidence_upper: forecastResult.confidence_bands.upper[index],
        })),
        total_predicted: forecastResult.forecast.reduce((sum, point) => sum + point.value, 0),
        growth_rate: this.calculateGrowthRate(forecastResult.forecast),
        key_drivers: this.identifyRevenueDrivers(segment),
        scenarios: this.generateRevenueScenarios(forecastResult),
        recommendations: this.generateRevenueRecommendations(forecastResult),
      };
    } catch (error) {
      console.error('Revenue forecasting failed:', error);
      return this.getMockRevenueForecast(timeHorizon);
    }
  }

  // User lifetime value prediction
  async predictLifetimeValue(userId: string): Promise<{
    predicted_ltv: number;
    confidence: number;
    ltv_segments: Array<{ segment: string; probability: number; ltv_range: [number, number] }>;
    value_drivers: Array<{ driver: string; impact: number; optimization_opportunity: string }>;
    milestones: Array<{ milestone: string; probability: number; estimated_date: Date; ltv_impact: number }>;
  }> {
    const request: PredictionRequest = {
      model_id: 'ltv_prediction_v2',
      input_data: { user_id: userId },
      include_explanation: true,
    };

    try {
      const result = await this.makePrediction(request);
      
      return {
        predicted_ltv: result.prediction.ltv,
        confidence: result.confidence,
        ltv_segments: result.prediction.segments,
        value_drivers: result.explanation?.feature_importance.map(f => ({
          driver: f.feature,
          impact: f.impact,
          optimization_opportunity: this.getOptimizationTip(f.feature, f.value),
        })) || [],
        milestones: result.prediction.milestones || [],
      };
    } catch (error) {
      console.error('LTV prediction failed:', error);
      return this.getMockLTVPrediction(userId);
    }
  }

  // Cat health prediction
  async predictCatHealth(catId: string): Promise<{
    overall_health_score: number;
    health_trend: 'improving' | 'stable' | 'declining';
    risk_factors: Array<{ condition: string; risk_level: number; prevention_tips: string[] }>;
    recommended_actions: Array<{ action: string; priority: 'low' | 'medium' | 'high'; timeline: string }>;
    next_checkup_date: Date;
    similar_cats: Array<{ cat_id: string; breed: string; age: number; health_outcome: string }>;
  }> {
    const request: PredictionRequest = {
      model_id: 'cat_health_prediction_v1',
      input_data: { cat_id: catId },
      include_explanation: true,
    };

    try {
      const result = await this.makePrediction(request);
      
      return {
        overall_health_score: result.prediction.health_score,
        health_trend: result.prediction.trend,
        risk_factors: result.prediction.risk_factors,
        recommended_actions: result.prediction.actions,
        next_checkup_date: new Date(result.prediction.next_checkup),
        similar_cats: result.explanation?.similar_cases.map(c => ({
          cat_id: c.case_id,
          breed: c.outcome.breed,
          age: c.outcome.age,
          health_outcome: c.outcome.health_status,
        })) || [],
      };
    } catch (error) {
      console.error('Cat health prediction failed:', error);
      return this.getMockHealthPrediction(catId);
    }
  }

  // Feature adoption prediction
  async predictFeatureAdoption(featureName: string, userSegment?: string): Promise<{
    adoption_rate: number;
    adoption_timeline: Array<{ week: number; cumulative_adoption: number; new_adopters: number }>;
    key_factors: Array<{ factor: string; influence: number; description: string }>;
    barriers: Array<{ barrier: string; impact: number; mitigation_strategy: string }>;
    optimal_rollout_strategy: {
      target_segments: string[];
      rollout_phases: Array<{ phase: string; duration: string; expected_adoption: number }>;
      success_metrics: string[];
    };
  }> {
    try {
      const result = await apiClient.post('/ml/feature-adoption-prediction', {
        feature_name: featureName,
        user_segment: userSegment,
      });
      
      return result;
    } catch (error) {
      console.error('Feature adoption prediction failed:', error);
      return this.getMockFeatureAdoptionPrediction(featureName);
    }
  }

  // Generic prediction method
  async makePrediction(request: PredictionRequest): Promise<PredictionResult> {
    const cacheKey = `${request.model_id}_${JSON.stringify(request.input_data)}`;
    
    // Check cache
    const cached = this.predictionCache.get(cacheKey);
    if (cached && Date.now() < cached.expiry) {
      return cached.result;
    }

    try {
      const result = await apiClient.post<PredictionResult>('/ml/predict', request);
      
      // Cache result for 1 hour
      this.predictionCache.set(cacheKey, {
        result,
        expiry: Date.now() + 60 * 60 * 1000,
      });
      
      return result;
    } catch (error) {
      console.error('Prediction request failed:', error);
      throw error;
    }
  }

  // Time series forecasting
  async generateTimeSeriesForecast(
    metric: string,
    historicalData: TimeSeriesData[],
    forecastHorizon: string
  ): Promise<ForecastResult> {
    try {
      return await apiClient.post<ForecastResult>('/ml/forecast', {
        metric,
        historical_data: historicalData,
        forecast_horizon: forecastHorizon,
        include_seasonality: true,
        include_changepoints: true,
      });
    } catch (error) {
      console.error('Time series forecasting failed:', error);
      throw error;
    }
  }

  // Model performance monitoring
  async getModelPerformance(modelId: string): Promise<{
    accuracy_trend: Array<{ date: string; accuracy: number; sample_size: number }>;
    drift_detection: { is_drifting: boolean; confidence: number; affected_features: string[] };
    prediction_distribution: Array<{ range: string; count: number; percentage: number }>;
    error_analysis: Array<{ error_type: string; frequency: number; impact: string }>;
  }> {
    try {
      return await apiClient.get(`/ml/models/${modelId}/performance`);
    } catch (error) {
      console.error('Failed to get model performance:', error);
      throw error;
    }
  }

  // A/B test result prediction
  async predictABTestResult(testConfig: {
    feature: string;
    segments: string[];
    success_metric: string;
    expected_duration: string;
  }): Promise<{
    predicted_winner: 'A' | 'B' | 'inconclusive';
    confidence: number;
    expected_lift: number;
    sample_size_needed: number;
    estimated_duration: string;
    statistical_power: number;
    risk_factors: string[];
  }> {
    try {
      return await apiClient.post('/ml/ab-test-prediction', testConfig);
    } catch (error) {
      console.error('A/B test prediction failed:', error);
      return this.getMockABTestPrediction();
    }
  }

  // Helper methods
  private async getHistoricalRevenue(segment?: string): Promise<TimeSeriesData[]> {
    // This would fetch actual historical data
    return [];
  }

  private calculateGrowthRate(forecast: TimeSeriesData[]): number {
    if (forecast.length < 2) return 0;
    const first = forecast[0].value;
    const last = forecast[forecast.length - 1].value;
    return (last - first) / first;
  }

  private identifyRevenueDrivers(segment?: string) {
    return [
      { factor: 'user_acquisition', contribution: 0.35, explanation: 'New user signups drive revenue growth' },
      { factor: 'subscription_retention', contribution: 0.28, explanation: 'Retained subscribers provide stable revenue' },
      { factor: 'virtual_goods_adoption', contribution: 0.22, explanation: 'Virtual goods purchases increase over time' },
      { factor: 'premium_conversion', contribution: 0.15, explanation: 'Free to premium conversions boost revenue' },
    ];
  }

  private generateRevenueScenarios(forecastResult: ForecastResult) {
    return [
      { name: 'optimistic', probability: 0.25, revenue: 50000, description: 'High user growth and engagement' },
      { name: 'baseline', probability: 0.50, revenue: 42000, description: 'Expected performance based on trends' },
      { name: 'conservative', probability: 0.25, revenue: 35000, description: 'Lower growth due to market conditions' },
    ];
  }

  private generateRevenueRecommendations(forecastResult: ForecastResult): string[] {
    return [
      'Focus on user acquisition in Q2 for maximum impact',
      'Improve subscription retention with better onboarding',
      'Launch virtual goods promotion during peak engagement periods',
      'A/B test premium pricing to optimize conversion rates',
    ];
  }

  private generateChurnInterventions(churnProbability: number) {
    if (churnProbability > 0.7) {
      return [
        { action: 'Personal outreach call', expected_impact: 0.3, urgency: 'high' as const },
        { action: 'Exclusive premium trial', expected_impact: 0.25, urgency: 'high' as const },
        { action: 'Customized feature tutorial', expected_impact: 0.2, urgency: 'medium' as const },
      ];
    } else if (churnProbability > 0.4) {
      return [
        { action: 'Targeted email campaign', expected_impact: 0.15, urgency: 'medium' as const },
        { action: 'In-app engagement prompt', expected_impact: 0.12, urgency: 'medium' as const },
        { action: 'Feature usage analytics', expected_impact: 0.1, urgency: 'low' as const },
      ];
    }
    
    return [
      { action: 'Continue monitoring', expected_impact: 0.05, urgency: 'low' as const },
      { action: 'General engagement content', expected_impact: 0.03, urgency: 'low' as const },
    ];
  }

  private getThresholdForFeature(feature: string): any {
    const thresholds: Record<string, any> = {
      'days_since_last_login': 7,
      'session_duration_avg': 300, // 5 minutes
      'feature_usage_count': 10,
      'support_tickets': 3,
    };
    return thresholds[feature] || 'N/A';
  }

  private getOptimizationTip(feature: string, value: any): string {
    const tips: Record<string, string> = {
      'session_duration': 'Increase engagement with personalized content recommendations',
      'purchase_frequency': 'Optimize pricing and product placement',
      'feature_usage': 'Improve feature discoverability and user education',
      'social_activity': 'Enhance social features and community building',
    };
    return tips[feature] || 'Monitor and optimize based on user feedback';
  }

  // Mock data generators for fallback
  private getMockModels(): PredictionModel[] {
    return [
      {
        id: 'churn_prediction_v3',
        name: 'User Churn Prediction',
        type: 'classification',
        version: '3.1.2',
        accuracy: 0.87,
        lastTrained: new Date('2024-01-15'),
        features: [
          { name: 'days_since_last_login', importance: 0.23, type: 'numerical', description: 'Days since user last logged in' },
          { name: 'session_duration_avg', importance: 0.19, type: 'numerical', description: 'Average session duration' },
          { name: 'feature_usage_count', importance: 0.15, type: 'numerical', description: 'Number of features used' },
        ],
        hyperparameters: { max_depth: 10, learning_rate: 0.01 },
        performance_metrics: { precision: 0.84, recall: 0.89, f1_score: 0.86, auc_score: 0.92 },
      },
    ];
  }

  private getMockChurnPrediction(userId: string) {
    return {
      churn_probability: 0.35,
      risk_level: 'medium' as const,
      key_factors: [
        { factor: 'login_frequency', impact: 0.4, current_value: 2, threshold: 5 },
        { factor: 'feature_usage', impact: 0.3, current_value: 3, threshold: 7 },
      ],
      intervention_suggestions: [
        { action: 'Send engagement email', expected_impact: 0.15, urgency: 'medium' as const },
        { action: 'Offer feature tutorial', expected_impact: 0.12, urgency: 'low' as const },
      ],
      similar_users: [
        { user_id: 'user_123', similarity: 0.89, churned: false, intervention_used: 'email_campaign' },
      ],
    };
  }

  private getMockRevenueForecast(timeHorizon: string) {
    const days = timeHorizon === '7d' ? 7 : timeHorizon === '30d' ? 30 : timeHorizon === '90d' ? 90 : 365;
    const forecast = Array.from({ length: days }, (_, i) => {
      const date = new Date();
      date.setDate(date.getDate() + i + 1);
      return {
        date: date.toISOString(),
        predicted_revenue: 1000 + Math.random() * 500 + i * 10,
        confidence_lower: 800 + Math.random() * 300 + i * 8,
        confidence_upper: 1200 + Math.random() * 700 + i * 12,
      };
    });

    return {
      forecast,
      total_predicted: forecast.reduce((sum, point) => sum + point.predicted_revenue, 0),
      growth_rate: 0.15,
      key_drivers: this.identifyRevenueDrivers(),
      scenarios: this.generateRevenueScenarios({} as ForecastResult),
      recommendations: [
        'Focus on user acquisition for maximum impact',
        'Improve subscription retention rates',
        'Optimize virtual goods pricing strategy',
      ],
    };
  }

  private getMockLTVPrediction(userId: string) {
    return {
      predicted_ltv: 150,
      confidence: 0.78,
      ltv_segments: [
        { segment: 'high_value', probability: 0.3, ltv_range: [200, 500] as [number, number] },
        { segment: 'medium_value', probability: 0.5, ltv_range: [100, 200] as [number, number] },
        { segment: 'low_value', probability: 0.2, ltv_range: [50, 100] as [number, number] },
      ],
      value_drivers: [
        { driver: 'subscription_length', impact: 0.4, optimization_opportunity: 'Improve retention programs' },
        { driver: 'virtual_goods_spending', impact: 0.35, optimization_opportunity: 'Personalize product recommendations' },
      ],
      milestones: [
        { milestone: 'first_purchase', probability: 0.7, estimated_date: new Date(), ltv_impact: 25 },
        { milestone: 'premium_upgrade', probability: 0.4, estimated_date: new Date(), ltv_impact: 75 },
      ],
    };
  }

  private getMockHealthPrediction(catId: string) {
    return {
      overall_health_score: 8.2,
      health_trend: 'stable' as const,
      risk_factors: [
        { condition: 'obesity', risk_level: 0.3, prevention_tips: ['Increase exercise', 'Monitor diet'] },
        { condition: 'dental_issues', risk_level: 0.2, prevention_tips: ['Regular dental checkups', 'Dental treats'] },
      ],
      recommended_actions: [
        { action: 'Schedule dental cleaning', priority: 'medium' as const, timeline: '3 months' },
        { action: 'Weight monitoring', priority: 'low' as const, timeline: 'ongoing' },
      ],
      next_checkup_date: new Date(Date.now() + 90 * 24 * 60 * 60 * 1000),
      similar_cats: [
        { cat_id: 'cat_456', breed: 'Persian', age: 5, health_outcome: 'healthy' },
      ],
    };
  }

  private getMockFeatureAdoptionPrediction(featureName: string) {
    return {
      adoption_rate: 0.65,
      adoption_timeline: Array.from({ length: 12 }, (_, i) => ({
        week: i + 1,
        cumulative_adoption: Math.min(0.65, (i + 1) * 0.08),
        new_adopters: i === 0 ? 0.08 : Math.max(0, 0.08 - i * 0.005),
      })),
      key_factors: [
        { factor: 'ease_of_use', influence: 0.4, description: 'Feature complexity affects adoption' },
        { factor: 'perceived_value', influence: 0.35, description: 'Users need to see clear benefits' },
      ],
      barriers: [
        { barrier: 'discoverability', impact: 0.3, mitigation_strategy: 'Add onboarding flow' },
        { barrier: 'learning_curve', impact: 0.2, mitigation_strategy: 'Create tutorial videos' },
      ],
      optimal_rollout_strategy: {
        target_segments: ['power_users', 'early_adopters'],
        rollout_phases: [
          { phase: 'beta', duration: '2 weeks', expected_adoption: 0.1 },
          { phase: 'gradual', duration: '4 weeks', expected_adoption: 0.4 },
          { phase: 'full', duration: '6 weeks', expected_adoption: 0.65 },
        ],
        success_metrics: ['adoption_rate', 'user_satisfaction', 'feature_retention'],
      },
    };
  }

  private getMockABTestPrediction() {
    return {
      predicted_winner: 'B' as const,
      confidence: 0.78,
      expected_lift: 0.12,
      sample_size_needed: 2500,
      estimated_duration: '3 weeks',
      statistical_power: 0.8,
      risk_factors: ['seasonal_effects', 'external_campaigns'],
    };
  }
}

export const PredictiveAnalytics = new PredictiveAnalyticsClass();
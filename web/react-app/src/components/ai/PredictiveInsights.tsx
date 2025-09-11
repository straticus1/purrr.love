import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  TrendingUp, 
  TrendingDown, 
  AlertTriangle, 
  Target, 
  Users, 
  DollarSign,
  Heart,
  Calendar,
  ChevronRight,
  RefreshCw,
  Info
} from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { PredictiveAnalytics } from '@/services/PredictiveAnalytics';

interface PredictiveInsightsProps {
  userId?: string;
  className?: string;
}

interface PredictionCard {
  id: string;
  title: string;
  type: 'churn' | 'ltv' | 'health' | 'revenue' | 'feature_adoption';
  icon: React.ComponentType<any>;
  value: string | number;
  confidence: number;
  trend: 'up' | 'down' | 'stable';
  timeframe: string;
  description: string;
  actionable: boolean;
  details?: any;
}

export const PredictiveInsights: React.FC<PredictiveInsightsProps> = ({
  userId,
  className = '',
}) => {
  const [predictions, setPredictions] = useState<PredictionCard[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedPrediction, setSelectedPrediction] = useState<string | null>(null);
  const [expandedDetails, setExpandedDetails] = useState<Record<string, any>>({});

  useEffect(() => {
    loadPredictions();
  }, [userId]);

  const loadPredictions = async () => {
    setIsLoading(true);
    try {
      const predictionCards: PredictionCard[] = [];

      // Load different types of predictions
      if (userId) {
        // User-specific predictions
        const churnPrediction = await PredictiveAnalytics.predictUserChurn(userId);
        predictionCards.push({
          id: 'churn',
          title: 'Churn Risk',
          type: 'churn',
          icon: AlertTriangle,
          value: `${(churnPrediction.churn_probability * 100).toFixed(1)}%`,
          confidence: 0.85,
          trend: churnPrediction.churn_probability > 0.5 ? 'up' : 'down',
          timeframe: '30 days',
          description: `${churnPrediction.risk_level} risk of churning in the next 30 days`,
          actionable: true,
          details: churnPrediction,
        });

        const ltvPrediction = await PredictiveAnalytics.predictLifetimeValue(userId);
        predictionCards.push({
          id: 'ltv',
          title: 'Lifetime Value',
          type: 'ltv',
          icon: DollarSign,
          value: `$${ltvPrediction.predicted_ltv}`,
          confidence: ltvPrediction.confidence,
          trend: 'up',
          timeframe: 'Total',
          description: 'Predicted total value this user will generate',
          actionable: true,
          details: ltvPrediction,
        });
      }

      // General business predictions
      const revenueForecast = await PredictiveAnalytics.forecastRevenue('30d');
      predictionCards.push({
        id: 'revenue',
        title: 'Revenue Forecast',
        type: 'revenue',
        icon: TrendingUp,
        value: `$${Math.round(revenueForecast.total_predicted)}`,
        confidence: 0.78,
        trend: revenueForecast.growth_rate > 0 ? 'up' : 'down',
        timeframe: '30 days',
        description: `Expected revenue growth of ${(revenueForecast.growth_rate * 100).toFixed(1)}%`,
        actionable: true,
        details: revenueForecast,
      });

      const featureAdoption = await PredictiveAnalytics.predictFeatureAdoption('social_sharing');
      predictionCards.push({
        id: 'feature_adoption',
        title: 'Feature Adoption',
        type: 'feature_adoption',
        icon: Target,
        value: `${(featureAdoption.adoption_rate * 100).toFixed(1)}%`,
        confidence: 0.72,
        trend: 'up',
        timeframe: '90 days',
        description: 'Predicted adoption rate for new social features',
        actionable: true,
        details: featureAdoption,
      });

      setPredictions(predictionCards);
    } catch (error) {
      console.error('Failed to load predictions:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handlePredictionClick = async (predictionId: string) => {
    if (selectedPrediction === predictionId) {
      setSelectedPrediction(null);
      return;
    }

    setSelectedPrediction(predictionId);
    
    // Load additional details if not already loaded
    if (!expandedDetails[predictionId]) {
      const prediction = predictions.find(p => p.id === predictionId);
      if (prediction) {
        setExpandedDetails(prev => ({
          ...prev,
          [predictionId]: prediction.details,
        }));
      }
    }
  };

  const getTrendColor = (trend: 'up' | 'down' | 'stable', type: string) => {
    if (type === 'churn') {
      return trend === 'up' ? 'text-red-600' : 'text-green-600';
    }
    return trend === 'up' ? 'text-green-600' : trend === 'down' ? 'text-red-600' : 'text-gray-600';
  };

  const getTrendIcon = (trend: 'up' | 'down' | 'stable') => {
    switch (trend) {
      case 'up':
        return TrendingUp;
      case 'down':
        return TrendingDown;
      default:
        return () => <div className="w-4 h-4 border border-gray-400 rounded"></div>;
    }
  };

  const PredictionDetails: React.FC<{ prediction: PredictionCard; details: any }> = ({ 
    prediction, 
    details 
  }) => {
    switch (prediction.type) {
      case 'churn':
        return (
          <div className="space-y-4">
            <div>
              <h4 className="font-medium text-gray-900 dark:text-white mb-2">Risk Factors</h4>
              <div className="space-y-2">
                {details.key_factors?.map((factor: any, index: number) => (
                  <div key={index} className="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">
                    <span className="text-sm capitalize">{factor.factor.replace('_', ' ')}</span>
                    <span className="text-sm font-medium">{(factor.impact * 100).toFixed(0)}% impact</span>
                  </div>
                ))}
              </div>
            </div>
            <div>
              <h4 className="font-medium text-gray-900 dark:text-white mb-2">Suggested Interventions</h4>
              <div className="space-y-2">
                {details.intervention_suggestions?.map((intervention: any, index: number) => (
                  <div key={index} className="flex items-start gap-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded">
                    <div className={`w-2 h-2 rounded-full mt-1.5 ${
                      intervention.urgency === 'high' ? 'bg-red-500' :
                      intervention.urgency === 'medium' ? 'bg-orange-500' : 'bg-green-500'
                    }`}></div>
                    <div>
                      <div className="text-sm font-medium">{intervention.action}</div>
                      <div className="text-xs text-gray-600 dark:text-gray-400">
                        Expected {(intervention.expected_impact * 100).toFixed(0)}% improvement
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        );

      case 'ltv':
        return (
          <div className="space-y-4">
            <div>
              <h4 className="font-medium text-gray-900 dark:text-white mb-2">Value Segments</h4>
              <div className="space-y-2">
                {details.ltv_segments?.map((segment: any, index: number) => (
                  <div key={index} className="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">
                    <span className="text-sm capitalize">{segment.segment.replace('_', ' ')}</span>
                    <span className="text-sm">
                      {(segment.probability * 100).toFixed(0)}% - ${segment.ltv_range[0]}-${segment.ltv_range[1]}
                    </span>
                  </div>
                ))}
              </div>
            </div>
            <div>
              <h4 className="font-medium text-gray-900 dark:text-white mb-2">Value Drivers</h4>
              <div className="space-y-2">
                {details.value_drivers?.map((driver: any, index: number) => (
                  <div key={index} className="p-2 bg-green-50 dark:bg-green-900/20 rounded">
                    <div className="text-sm font-medium capitalize">{driver.driver.replace('_', ' ')}</div>
                    <div className="text-xs text-gray-600 dark:text-gray-400">{driver.optimization_opportunity}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        );

      case 'revenue':
        return (
          <div className="space-y-4">
            <div>
              <h4 className="font-medium text-gray-900 dark:text-white mb-2">Key Drivers</h4>
              <div className="space-y-2">
                {details.key_drivers?.map((driver: any, index: number) => (
                  <div key={index} className="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">
                    <div>
                      <div className="text-sm font-medium capitalize">{driver.factor.replace('_', ' ')}</div>
                      <div className="text-xs text-gray-600 dark:text-gray-400">{driver.explanation}</div>
                    </div>
                    <span className="text-sm font-medium">{(driver.contribution * 100).toFixed(0)}%</span>
                  </div>
                ))}
              </div>
            </div>
            <div>
              <h4 className="font-medium text-gray-900 dark:text-white mb-2">Scenarios</h4>
              <div className="space-y-2">
                {details.scenarios?.map((scenario: any, index: number) => (
                  <div key={index} className="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">
                    <div>
                      <div className="text-sm font-medium capitalize">{scenario.name}</div>
                      <div className="text-xs text-gray-600 dark:text-gray-400">{scenario.description}</div>
                    </div>
                    <div className="text-right">
                      <div className="text-sm font-medium">${scenario.revenue.toLocaleString()}</div>
                      <div className="text-xs text-gray-600 dark:text-gray-400">{(scenario.probability * 100).toFixed(0)}%</div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        );

      case 'feature_adoption':
        return (
          <div className="space-y-4">
            <div>
              <h4 className="font-medium text-gray-900 dark:text-white mb-2">Adoption Timeline</h4>
              <div className="space-y-1">
                {details.adoption_timeline?.slice(0, 4).map((week: any, index: number) => (
                  <div key={index} className="flex items-center justify-between p-1 text-sm">
                    <span>Week {week.week}</span>
                    <span>{(week.cumulative_adoption * 100).toFixed(1)}% cumulative</span>
                  </div>
                ))}
              </div>
            </div>
            <div>
              <h4 className="font-medium text-gray-900 dark:text-white mb-2">Barriers</h4>
              <div className="space-y-2">
                {details.barriers?.map((barrier: any, index: number) => (
                  <div key={index} className="p-2 bg-red-50 dark:bg-red-900/20 rounded">
                    <div className="text-sm font-medium capitalize">{barrier.barrier.replace('_', ' ')}</div>
                    <div className="text-xs text-gray-600 dark:text-gray-400">{barrier.mitigation_strategy}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        );

      default:
        return <div className="text-sm text-gray-600 dark:text-gray-400">Details not available</div>;
    }
  };

  if (isLoading) {
    return (
      <div className={`space-y-4 ${className}`}>
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
            Predictive Insights
          </h2>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {[...Array(4)].map((_, i) => (
            <div key={i} className="animate-pulse">
              <Card>
                <CardContent className="p-4">
                  <div className="flex items-center gap-3 mb-3">
                    <div className="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div className="flex-1">
                      <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                      <div className="h-3 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
                    </div>
                  </div>
                  <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                  <div className="h-3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </CardContent>
              </Card>
            </div>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className={`space-y-4 ${className}`}>
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <Target className="w-5 h-5 text-blue-600" />
          <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
            Predictive Insights
          </h2>
        </div>
        
        <Button
          onClick={loadPredictions}
          variant="outline"
          size="sm"
          icon={<RefreshCw className="w-4 h-4" />}
        >
          Refresh
        </Button>
      </div>

      {/* Predictions Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {predictions.map((prediction) => {
          const Icon = prediction.icon;
          const TrendIcon = getTrendIcon(prediction.trend);
          const isExpanded = selectedPrediction === prediction.id;
          
          return (
            <motion.div
              key={prediction.id}
              layout
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              className={isExpanded ? 'md:col-span-2' : ''}
            >
              <Card className={`cursor-pointer transition-all duration-200 hover:shadow-lg ${
                isExpanded ? 'ring-2 ring-blue-500' : ''
              }`}>
                <CardContent className="p-0">
                  <div 
                    className="p-4"
                    onClick={() => handlePredictionClick(prediction.id)}
                  >
                    <div className="flex items-center justify-between mb-3">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                          <Icon className="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                          <h3 className="font-medium text-gray-900 dark:text-white">
                            {prediction.title}
                          </h3>
                          <p className="text-sm text-gray-600 dark:text-gray-400">
                            {prediction.timeframe}
                          </p>
                        </div>
                      </div>
                      
                      <div className="flex items-center gap-2">
                        <div className={`flex items-center gap-1 ${getTrendColor(prediction.trend, prediction.type)}`}>
                          <TrendIcon className="w-4 h-4" />
                        </div>
                        <ChevronRight className={`w-4 h-4 text-gray-400 transition-transform ${
                          isExpanded ? 'rotate-90' : ''
                        }`} />
                      </div>
                    </div>
                    
                    <div className="mb-3">
                      <div className="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                        {prediction.value}
                      </div>
                      <p className="text-sm text-gray-600 dark:text-gray-400">
                        {prediction.description}
                      </p>
                    </div>
                    
                    <div className="flex items-center justify-between text-sm">
                      <div className="flex items-center gap-1 text-gray-500">
                        <Info className="w-3 h-3" />
                        <span>{(prediction.confidence * 100).toFixed(0)}% confidence</span>
                      </div>
                      {prediction.actionable && (
                        <span className="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-full text-xs">
                          Actionable
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Expanded Details */}
                  <AnimatePresence>
                    {isExpanded && expandedDetails[prediction.id] && (
                      <motion.div
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: 'auto', opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.3, ease: 'easeInOut' }}
                        className="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800/50"
                      >
                        <PredictionDetails 
                          prediction={prediction} 
                          details={expandedDetails[prediction.id]} 
                        />
                      </motion.div>
                    )}
                  </AnimatePresence>
                </CardContent>
              </Card>
            </motion.div>
          );
        })}
      </div>

      {predictions.length === 0 && (
        <Card>
          <CardContent className="p-8 text-center">
            <Target className="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
              No Predictions Available
            </h3>
            <p className="text-gray-600 dark:text-gray-400">
              Predictive models are analyzing your data. Check back soon for insights!
            </p>
          </CardContent>
        </Card>
      )}
    </div>
  );
};
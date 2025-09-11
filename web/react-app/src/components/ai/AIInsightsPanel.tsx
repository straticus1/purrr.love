import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Brain, 
  TrendingUp, 
  AlertTriangle, 
  Lightbulb, 
  Target,
  ChevronRight,
  Sparkles,
  Zap,
  Eye,
  RefreshCw
} from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { AIInsightsEngine, AIInsight } from '@/services/AIInsightsEngine';

interface AIInsightsPanelProps {
  dateRange: { startDate: Date; endDate: Date };
  className?: string;
}

const insightTypeIcons = {
  trend: TrendingUp,
  anomaly: AlertTriangle,
  prediction: Brain,
  recommendation: Lightbulb,
  alert: Zap,
};

const importanceColors = {
  low: 'text-gray-600 bg-gray-100 dark:bg-gray-800 dark:text-gray-400',
  medium: 'text-blue-600 bg-blue-100 dark:bg-blue-900 dark:text-blue-400',
  high: 'text-orange-600 bg-orange-100 dark:bg-orange-900 dark:text-orange-400',
  critical: 'text-red-600 bg-red-100 dark:bg-red-900 dark:text-red-400',
};

const categoryColors = {
  user_behavior: 'border-l-blue-500',
  revenue: 'border-l-green-500',
  engagement: 'border-l-purple-500',
  health: 'border-l-pink-500',
  performance: 'border-l-orange-500',
};

export const AIInsightsPanel: React.FC<AIInsightsPanelProps> = ({
  dateRange,
  className = '',
}) => {
  const [insights, setInsights] = useState<AIInsight[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [expandedInsights, setExpandedInsights] = useState<Set<string>>(new Set());

  useEffect(() => {
    loadInsights();
  }, [dateRange]);

  const loadInsights = async () => {
    setIsLoading(true);
    try {
      const aiInsights = await AIInsightsEngine.generateInsights(dateRange);
      setInsights(aiInsights.sort((a, b) => {
        // Sort by importance, then by confidence
        const importanceOrder = { critical: 4, high: 3, medium: 2, low: 1 };
        return (importanceOrder[b.importance] - importanceOrder[a.importance]) || 
               (b.confidence - a.confidence);
      }));
    } catch (error) {
      console.error('Failed to load AI insights:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const filteredInsights = insights.filter(insight =>
    selectedCategory === 'all' || insight.category === selectedCategory
  );

  const categories = ['all', ...new Set(insights.map(i => i.category))];

  const toggleExpanded = (insightId: string) => {
    setExpandedInsights(prev => {
      const newSet = new Set(prev);
      if (newSet.has(insightId)) {
        newSet.delete(insightId);
      } else {
        newSet.add(insightId);
      }
      return newSet;
    });
  };

  const InsightCard: React.FC<{ insight: AIInsight }> = ({ insight }) => {
    const Icon = insightTypeIcons[insight.type];
    const isExpanded = expandedInsights.has(insight.id);

    return (
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className={`border-l-4 ${categoryColors[insight.category]} overflow-hidden`}
      >
        <Card className="hover:shadow-md transition-all duration-200">
          <CardContent className="p-0">
            <div className="p-4">
              {/* Header */}
              <div className="flex items-start gap-3 mb-3">
                <div className={`w-10 h-10 rounded-lg ${importanceColors[insight.importance]} flex items-center justify-center flex-shrink-0`}>
                  <Icon className="w-5 h-5" />
                </div>
                
                <div className="flex-1 min-w-0">
                  <div className="flex items-start justify-between gap-2 mb-1">
                    <h3 className="font-semibold text-gray-900 dark:text-white line-clamp-2">
                      {insight.title}
                    </h3>
                    <div className="flex items-center gap-2 flex-shrink-0">
                      <div className="flex items-center gap-1 text-xs text-gray-500">
                        <Sparkles className="w-3 h-3" />
                        <span>{(insight.confidence * 100).toFixed(0)}%</span>
                      </div>
                      <button
                        onClick={() => toggleExpanded(insight.id)}
                        className="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                      >
                        <motion.div
                          animate={{ rotate: isExpanded ? 90 : 0 }}
                          transition={{ duration: 0.2 }}
                        >
                          <ChevronRight className="w-4 h-4 text-gray-400" />
                        </motion.div>
                      </button>
                    </div>
                  </div>
                  
                  <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    {insight.description}
                  </p>
                  
                  <div className="flex items-center gap-3 text-xs">
                    <span className={`px-2 py-1 rounded-full ${importanceColors[insight.importance]}`}>
                      {insight.importance}
                    </span>
                    <span className="text-gray-500 capitalize">
                      {insight.category.replace('_', ' ')}
                    </span>
                    <span className="text-gray-500">
                      {insight.timestamp.toLocaleDateString()}
                    </span>
                  </div>
                </div>
              </div>

              {/* Expanded Content */}
              <AnimatePresence>
                {isExpanded && (
                  <motion.div
                    initial={{ height: 0, opacity: 0 }}
                    animate={{ height: 'auto', opacity: 1 }}
                    exit={{ height: 0, opacity: 0 }}
                    transition={{ duration: 0.3, ease: 'easeInOut' }}
                    className="overflow-hidden"
                  >
                    <div className="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                      {/* Insight Data */}
                      {insight.data && Object.keys(insight.data).length > 0 && (
                        <div>
                          <h4 className="font-medium text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <Eye className="w-4 h-4" />
                            Key Data Points
                          </h4>
                          <div className="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                            <div className="grid grid-cols-2 gap-2 text-sm">
                              {Object.entries(insight.data).map(([key, value]) => (
                                <div key={key} className="flex justify-between">
                                  <span className="text-gray-600 dark:text-gray-400 capitalize">
                                    {key.replace('_', ' ')}:
                                  </span>
                                  <span className="font-medium text-gray-900 dark:text-white">
                                    {typeof value === 'number' && value < 1 && value > -1 
                                      ? `${(value * 100).toFixed(1)}%`
                                      : value?.toString()
                                    }
                                  </span>
                                </div>
                              ))}
                            </div>
                          </div>
                        </div>
                      )}

                      {/* Related Metrics */}
                      {insight.relatedMetrics && insight.relatedMetrics.length > 0 && (
                        <div>
                          <h4 className="font-medium text-gray-900 dark:text-white mb-2">
                            Related Metrics
                          </h4>
                          <div className="flex flex-wrap gap-2">
                            {insight.relatedMetrics.map((metric) => (
                              <span
                                key={metric}
                                className="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full text-xs"
                              >
                                {metric.replace('_', ' ')}
                              </span>
                            ))}
                          </div>
                        </div>
                      )}

                      {/* Suggested Actions */}
                      {insight.actionable && insight.suggestedActions && insight.suggestedActions.length > 0 && (
                        <div>
                          <h4 className="font-medium text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <Target className="w-4 h-4" />
                            Suggested Actions
                          </h4>
                          <div className="space-y-2">
                            {insight.suggestedActions.map((action, index) => (
                              <div
                                key={index}
                                className="flex items-start gap-2 p-2 bg-green-50 dark:bg-green-900/20 rounded-lg"
                              >
                                <div className="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                                <span className="text-sm text-green-800 dark:text-green-200">
                                  {action}
                                </span>
                              </div>
                            ))}
                          </div>
                        </div>
                      )}

                      {/* Action Buttons */}
                      <div className="flex items-center gap-2 pt-2">
                        <Button
                          size="sm"
                          variant="primary"
                          onClick={() => console.log('Taking action on insight:', insight.id)}
                        >
                          Take Action
                        </Button>
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => console.log('Viewing details for insight:', insight.id)}
                        >
                          View Details
                        </Button>
                      </div>
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
          </CardContent>
        </Card>
      </motion.div>
    );
  };

  if (isLoading) {
    return (
      <div className={`space-y-4 ${className}`}>
        <div className="flex items-center gap-2 mb-4">
          <Brain className="w-5 h-5 text-purple-600" />
          <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
            AI Insights
          </h2>
        </div>
        {[...Array(3)].map((_, i) => (
          <div key={i} className="animate-pulse">
            <Card>
              <CardContent className="p-4">
                <div className="flex items-start gap-3">
                  <div className="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                  <div className="flex-1 space-y-2">
                    <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                    <div className="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                    <div className="flex gap-2">
                      <div className="h-5 bg-gray-200 dark:bg-gray-700 rounded w-16"></div>
                      <div className="h-5 bg-gray-200 dark:bg-gray-700 rounded w-20"></div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        ))}
      </div>
    );
  }

  return (
    <div className={`space-y-4 ${className}`}>
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <Brain className="w-5 h-5 text-purple-600" />
          <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
            AI Insights
          </h2>
          <span className="px-2 py-1 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 rounded-full text-xs font-medium">
            {filteredInsights.length} insights
          </span>
        </div>
        
        <Button
          onClick={loadInsights}
          variant="outline"
          size="sm"
          icon={<RefreshCw className="w-4 h-4" />}
        >
          Refresh
        </Button>
      </div>

      {/* Category Filter */}
      <div className="flex items-center gap-2 overflow-x-auto pb-2">
        {categories.map((category) => (
          <button
            key={category}
            onClick={() => setSelectedCategory(category)}
            className={`px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap transition-colors ${
              selectedCategory === category
                ? 'bg-purple-600 text-white'
                : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
            }`}
          >
            {category === 'all' ? 'All Categories' : category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
          </button>
        ))}
      </div>

      {/* Insights List */}
      {filteredInsights.length > 0 ? (
        <div className="space-y-3">
          {filteredInsights.map((insight) => (
            <InsightCard key={insight.id} insight={insight} />
          ))}
        </div>
      ) : (
        <Card>
          <CardContent className="p-8 text-center">
            <Brain className="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
              No Insights Available
            </h3>
            <p className="text-gray-600 dark:text-gray-400">
              {selectedCategory === 'all' 
                ? 'AI is analyzing your data to generate insights. Check back soon!'
                : `No insights found for ${selectedCategory.replace('_', ' ')} category.`
              }
            </p>
          </CardContent>
        </Card>
      )}
    </div>
  );
};
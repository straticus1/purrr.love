import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { 
  ShoppingBag, 
  BookOpen, 
  Settings, 
  Play,
  Star,
  ThumbsUp,
  ThumbsDown,
  X,
  ExternalLink,
  Clock,
  Users,
  Zap
} from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { Recommendation } from '@/services/RecommendationEngine';

interface RecommendationCardProps {
  recommendation: Recommendation;
  onAction?: (action: string, params?: any) => void;
  onFeedback?: (feedback: 'positive' | 'negative' | 'dismiss') => void;
  showFeedback?: boolean;
  compact?: boolean;
  className?: string;
}

const typeIcons = {
  product: ShoppingBag,
  content: BookOpen,
  feature: Settings,
  action: Play,
};

const priorityColors = {
  low: 'border-gray-200 dark:border-gray-700',
  medium: 'border-blue-200 dark:border-blue-800',
  high: 'border-orange-200 dark:border-orange-800',
  urgent: 'border-red-200 dark:border-red-800',
};

const priorityBadgeColors = {
  low: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
  medium: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
  high: 'bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-400',
  urgent: 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400',
};

export const RecommendationCard: React.FC<RecommendationCardProps> = ({
  recommendation,
  onAction,
  onFeedback,
  showFeedback = true,
  compact = false,
  className = '',
}) => {
  const [showDetails, setShowDetails] = useState(false);
  const [feedbackGiven, setFeedbackGiven] = useState(false);

  const Icon = typeIcons[recommendation.type];

  const handleAction = () => {
    onAction?.(recommendation.cta_action, recommendation.cta_params);
  };

  const handleFeedback = (feedback: 'positive' | 'negative' | 'dismiss') => {
    setFeedbackGiven(true);
    onFeedback?.(feedback);
  };

  const formatConfidence = (confidence: number): string => {
    return `${(confidence * 100).toFixed(0)}%`;
  };

  const getTimeToRead = (description: string): number => {
    return Math.ceil(description.split(' ').length / 200); // Assume 200 words per minute
  };

  if (compact) {
    return (
      <motion.div
        initial={{ opacity: 0, x: -20 }}
        animate={{ opacity: 1, x: 0 }}
        className={className}
      >
        <Card className={`border-l-4 ${priorityColors[recommendation.priority]} hover:shadow-md transition-all duration-200`}>
          <CardContent className="p-4">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center flex-shrink-0">
                <Icon className="w-5 h-5 text-purple-600 dark:text-purple-400" />
              </div>
              
              <div className="flex-1 min-w-0">
                <h3 className="font-medium text-gray-900 dark:text-white truncate">
                  {recommendation.title}
                </h3>
                <p className="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                  {recommendation.description}
                </p>
              </div>
              
              <Button
                onClick={handleAction}
                variant="primary"
                size="sm"
              >
                {recommendation.cta_text}
              </Button>
            </div>
          </CardContent>
        </Card>
      </motion.div>
    );
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      className={className}
    >
      <Card className={`border-2 ${priorityColors[recommendation.priority]} overflow-hidden hover:shadow-lg transition-all duration-300`}>
        <CardContent className="p-0">
          {/* Header */}
          <div className="p-6 pb-4">
            <div className="flex items-start justify-between gap-4 mb-4">
              <div className="flex items-start gap-4">
                <div className="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center flex-shrink-0">
                  <Icon className="w-6 h-6 text-white" />
                </div>
                
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-2">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                      {recommendation.title}
                    </h3>
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${priorityBadgeColors[recommendation.priority]}`}>
                      {recommendation.priority}
                    </span>
                  </div>
                  
                  <p className="text-gray-600 dark:text-gray-400 mb-3">
                    {recommendation.description}
                  </p>
                  
                  {/* Meta Information */}
                  <div className="flex items-center gap-4 text-sm text-gray-500">
                    <div className="flex items-center gap-1">
                      <Star className="w-3 h-3" />
                      <span>{formatConfidence(recommendation.confidence)} confident</span>
                    </div>
                    
                    {recommendation.expected_value && (
                      <div className="flex items-center gap-1">
                        <Zap className="w-3 h-3" />
                        <span>+${recommendation.expected_value} value</span>
                      </div>
                    )}
                    
                    {recommendation.user_segments.length > 0 && (
                      <div className="flex items-center gap-1">
                        <Users className="w-3 h-3" />
                        <span>{recommendation.user_segments[0].replace('_', ' ')}</span>
                      </div>
                    )}
                    
                    <div className="flex items-center gap-1">
                      <Clock className="w-3 h-3" />
                      <span>{getTimeToRead(recommendation.description)} min read</span>
                    </div>
                  </div>
                </div>
              </div>
              
              {showFeedback && !feedbackGiven && (
                <div className="flex items-center gap-1">
                  <button
                    onClick={() => handleFeedback('positive')}
                    className="p-2 rounded-lg hover:bg-green-100 dark:hover:bg-green-900 transition-colors"
                    title="This is helpful"
                  >
                    <ThumbsUp className="w-4 h-4 text-green-600" />
                  </button>
                  <button
                    onClick={() => handleFeedback('negative')}
                    className="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900 transition-colors"
                    title="This is not helpful"
                  >
                    <ThumbsDown className="w-4 h-4 text-red-600" />
                  </button>
                  <button
                    onClick={() => handleFeedback('dismiss')}
                    className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    title="Dismiss"
                  >
                    <X className="w-4 h-4 text-gray-500" />
                  </button>
                </div>
              )}
            </div>

            {/* Reasoning */}
            <div className="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 mb-4">
              <p className="text-sm text-gray-700 dark:text-gray-300 italic">
                💡 {recommendation.reasoning}
              </p>
            </div>

            {/* Personalization Factors */}
            {recommendation.personalization_factors.length > 0 && (
              <div className="mb-4">
                <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Personalized for you based on:
                </h4>
                <div className="flex flex-wrap gap-2">
                  {recommendation.personalization_factors.map((factor) => (
                    <span
                      key={factor}
                      className="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full text-xs"
                    >
                      {factor.replace('_', ' ')}
                    </span>
                  ))}
                </div>
              </div>
            )}

            {/* Product Details for Product Recommendations */}
            {recommendation.type === 'product' && 'product_name' in recommendation && (
              <div className="mb-4">
                <div className="flex items-center gap-4">
                  {'image_url' in recommendation && (
                    <img
                      src={(recommendation as any).image_url}
                      alt={(recommendation as any).product_name}
                      className="w-16 h-16 object-cover rounded-lg"
                    />
                  )}
                  <div>
                    <h4 className="font-medium text-gray-900 dark:text-white">
                      {(recommendation as any).product_name}
                    </h4>
                    <div className="flex items-center gap-2 text-sm">
                      <span className="font-semibold text-green-600">
                        ${(recommendation as any).price}
                        {(recommendation as any).discount && (
                          <span className="ml-1 text-gray-500 line-through">
                            ${((recommendation as any).price / (1 - (recommendation as any).discount)).toFixed(2)}
                          </span>
                        )}
                      </span>
                      {'rating' in recommendation && (
                        <div className="flex items-center gap-1">
                          <Star className="w-3 h-3 fill-yellow-400 text-yellow-400" />
                          <span>{(recommendation as any).rating}</span>
                          <span className="text-gray-500">({(recommendation as any).review_count})</span>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Content Details for Content Recommendations */}
            {recommendation.type === 'content' && 'content_type' in recommendation && (
              <div className="mb-4">
                <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                  <span className="capitalize">{(recommendation as any).content_type}</span>
                  {(recommendation as any).estimated_read_time && (
                    <>
                      <span>•</span>
                      <span>{(recommendation as any).estimated_read_time} min read</span>
                    </>
                  )}
                </div>
                {(recommendation as any).tags && (recommendation as any).tags.length > 0 && (
                  <div className="flex flex-wrap gap-1 mt-2">
                    {(recommendation as any).tags.map((tag: string) => (
                      <span
                        key={tag}
                        className="px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-xs"
                      >
                        #{tag}
                      </span>
                    ))}
                  </div>
                )}
              </div>
            )}

            {/* Feature Details for Feature Recommendations */}
            {recommendation.type === 'feature' && 'benefits' in recommendation && (
              <div className="mb-4">
                <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">Benefits:</h4>
                <ul className="space-y-1">
                  {(recommendation as any).benefits.map((benefit: string, index: number) => (
                    <li key={index} className="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                      <div className="w-1 h-1 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                      {benefit}
                    </li>
                  ))}
                </ul>
              </div>
            )}

            {/* Actions */}
            <div className="flex items-center gap-3">
              <Button
                onClick={handleAction}
                variant="primary"
                icon={<ExternalLink className="w-4 h-4" />}
              >
                {recommendation.cta_text}
              </Button>
              
              <Button
                onClick={() => setShowDetails(!showDetails)}
                variant="outline"
              >
                {showDetails ? 'Hide Details' : 'More Details'}
              </Button>
            </div>

            {/* Feedback Message */}
            {feedbackGiven && (
              <motion.div
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                className="mt-3 p-2 bg-green-100 dark:bg-green-900 rounded-lg"
              >
                <p className="text-sm text-green-800 dark:text-green-200">
                  Thanks for your feedback! We'll use this to improve our recommendations.
                </p>
              </motion.div>
            )}
          </div>

          {/* Expanded Details */}
          {showDetails && (
            <motion.div
              initial={{ height: 0, opacity: 0 }}
              animate={{ height: 'auto', opacity: 1 }}
              className="border-t border-gray-200 dark:border-gray-700 p-6 bg-gray-50 dark:bg-gray-800/50"
            >
              <div className="space-y-4 text-sm">
                <div>
                  <h4 className="font-medium text-gray-900 dark:text-white mb-1">Source</h4>
                  <p className="text-gray-600 dark:text-gray-400 capitalize">
                    {recommendation.source.replace('_', ' ')}
                    {recommendation.model_version && ` (v${recommendation.model_version})`}
                  </p>
                </div>
                
                <div>
                  <h4 className="font-medium text-gray-900 dark:text-white mb-1">Category</h4>
                  <p className="text-gray-600 dark:text-gray-400 capitalize">
                    {recommendation.category}
                  </p>
                </div>
                
                {recommendation.expires_at && (
                  <div>
                    <h4 className="font-medium text-gray-900 dark:text-white mb-1">Expires</h4>
                    <p className="text-gray-600 dark:text-gray-400">
                      {recommendation.expires_at.toLocaleDateString()}
                    </p>
                  </div>
                )}
                
                {recommendation.conversion_probability && (
                  <div>
                    <h4 className="font-medium text-gray-900 dark:text-white mb-1">Conversion Probability</h4>
                    <p className="text-gray-600 dark:text-gray-400">
                      {(recommendation.conversion_probability * 100).toFixed(1)}%
                    </p>
                  </div>
                )}
              </div>
            </motion.div>
          )}
        </CardContent>
      </Card>
    </motion.div>
  );
};
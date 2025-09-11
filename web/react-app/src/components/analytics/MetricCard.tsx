import React from 'react';
import { motion } from 'framer-motion';
import { LucideIcon, TrendingUp, TrendingDown } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';

interface MetricCardProps {
  id: string;
  title: string;
  value: number;
  change: number;
  icon: LucideIcon;
  color: 'blue' | 'green' | 'purple' | 'orange' | 'pink' | 'indigo' | 'red' | 'yellow';
  trend: 'up' | 'down' | 'neutral';
  prefix?: string;
  suffix?: string;
  isLoading?: boolean;
  onClick?: () => void;
}

const colorVariants = {
  blue: {
    bg: 'bg-blue-100 dark:bg-blue-900',
    icon: 'text-blue-600 dark:text-blue-400',
    trend: 'text-blue-600 dark:text-blue-400',
  },
  green: {
    bg: 'bg-green-100 dark:bg-green-900',
    icon: 'text-green-600 dark:text-green-400',
    trend: 'text-green-600 dark:text-green-400',
  },
  purple: {
    bg: 'bg-purple-100 dark:bg-purple-900',
    icon: 'text-purple-600 dark:text-purple-400',
    trend: 'text-purple-600 dark:text-purple-400',
  },
  orange: {
    bg: 'bg-orange-100 dark:bg-orange-900',
    icon: 'text-orange-600 dark:text-orange-400',
    trend: 'text-orange-600 dark:text-orange-400',
  },
  pink: {
    bg: 'bg-pink-100 dark:bg-pink-900',
    icon: 'text-pink-600 dark:text-pink-400',
    trend: 'text-pink-600 dark:text-pink-400',
  },
  indigo: {
    bg: 'bg-indigo-100 dark:bg-indigo-900',
    icon: 'text-indigo-600 dark:text-indigo-400',
    trend: 'text-indigo-600 dark:text-indigo-400',
  },
  red: {
    bg: 'bg-red-100 dark:bg-red-900',
    icon: 'text-red-600 dark:text-red-400',
    trend: 'text-red-600 dark:text-red-400',
  },
  yellow: {
    bg: 'bg-yellow-100 dark:bg-yellow-900',
    icon: 'text-yellow-600 dark:text-yellow-400',
    trend: 'text-yellow-600 dark:text-yellow-400',
  },
};

export const MetricCard: React.FC<MetricCardProps> = ({
  id,
  title,
  value,
  change,
  icon: Icon,
  color,
  trend,
  prefix = '',
  suffix = '',
  isLoading = false,
  onClick,
}) => {
  const colorClasses = colorVariants[color];

  const formatValue = (val: number): string => {
    if (val >= 1000000) {
      return `${(val / 1000000).toFixed(1)}M`;
    } else if (val >= 1000) {
      return `${(val / 1000).toFixed(1)}K`;
    }
    return val.toLocaleString();
  };

  const formatChange = (change: number): string => {
    const absChange = Math.abs(change);
    if (absChange >= 1000000) {
      return `${(absChange / 1000000).toFixed(1)}M`;
    } else if (absChange >= 1000) {
      return `${(absChange / 1000).toFixed(1)}K`;
    }
    return absChange.toLocaleString();
  };

  if (isLoading) {
    return (
      <Card className="animate-pulse">
        <CardContent className="p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
            <div className="w-16 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
          </div>
          <div className="w-20 h-8 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
          <div className="w-24 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
        </CardContent>
      </Card>
    );
  }

  return (
    <motion.div
      whileHover={onClick ? { scale: 1.02, y: -2 } : {}}
      whileTap={onClick ? { scale: 0.98 } : {}}
      transition={{ type: 'spring', stiffness: 300, damping: 20 }}
    >
      <Card 
        className={`overflow-hidden transition-all duration-200 hover:shadow-lg ${
          onClick ? 'cursor-pointer' : ''
        }`}
        onClick={onClick}
      >
        <CardContent className="p-6">
          {/* Header */}
          <div className="flex items-center justify-between mb-4">
            <div className={`w-12 h-12 rounded-xl ${colorClasses.bg} flex items-center justify-center`}>
              <Icon className={`w-6 h-6 ${colorClasses.icon}`} />
            </div>
            
            {/* Trend Indicator */}
            <div className={`flex items-center gap-1 ${
              trend === 'up' ? 'text-green-600 dark:text-green-400' :
              trend === 'down' ? 'text-red-600 dark:text-red-400' :
              'text-gray-500'
            }`}>
              {trend === 'up' && <TrendingUp className="w-4 h-4" />}
              {trend === 'down' && <TrendingDown className="w-4 h-4" />}
              <span className="text-sm font-medium">
                {change >= 0 ? '+' : ''}{change.toFixed(1)}%
              </span>
            </div>
          </div>

          {/* Value */}
          <div className="mb-2">
            <div className="text-2xl font-bold text-gray-900 dark:text-white">
              {prefix}{formatValue(value)}{suffix}
            </div>
          </div>

          {/* Title and Change */}
          <div className="flex items-center justify-between">
            <span className="text-sm font-medium text-gray-600 dark:text-gray-400">
              {title}
            </span>
            {change !== 0 && (
              <span className="text-xs text-gray-500">
                {change > 0 ? '+' : ''}{formatChange(change)} from last period
              </span>
            )}
          </div>

          {/* Progress Bar (optional, based on trend) */}
          {Math.abs(change) > 0 && (
            <div className="mt-3 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
              <motion.div
                className={`h-1 rounded-full ${
                  trend === 'up' ? 'bg-green-500' :
                  trend === 'down' ? 'bg-red-500' :
                  'bg-gray-400'
                }`}
                initial={{ width: 0 }}
                animate={{ width: `${Math.min(Math.abs(change), 100)}%` }}
                transition={{ duration: 1, delay: 0.5 }}
              />
            </div>
          )}
        </CardContent>
      </Card>
    </motion.div>
  );
};
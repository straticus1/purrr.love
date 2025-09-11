import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Filter, Users, Activity, DollarSign, Target, Gamepad2, TrendingUp } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';

type MetricType = 'users' | 'engagement' | 'revenue' | 'cats' | 'games';
type ReportType = 'overview' | 'detailed' | 'export';

interface AnalyticsFiltersProps {
  selectedMetric: MetricType;
  onMetricChange: (metric: MetricType) => void;
  reportType: ReportType;
  onReportTypeChange: (type: ReportType) => void;
  onClose: () => void;
}

const metricOptions = [
  {
    type: 'users' as MetricType,
    label: 'User Analytics',
    description: 'User growth, demographics, and acquisition',
    icon: Users,
    color: 'blue',
    bgColor: 'bg-blue-100 dark:bg-blue-900',
    iconColor: 'text-blue-600 dark:text-blue-400',
  },
  {
    type: 'engagement' as MetricType,
    label: 'Engagement',
    description: 'Session duration, page views, and interactions',
    icon: Activity,
    color: 'green',
    bgColor: 'bg-green-100 dark:bg-green-900',
    iconColor: 'text-green-600 dark:text-green-400',
  },
  {
    type: 'revenue' as MetricType,
    label: 'Revenue',
    description: 'Sales, subscriptions, and monetization',
    icon: DollarSign,
    color: 'purple',
    bgColor: 'bg-purple-100 dark:bg-purple-900',
    iconColor: 'text-purple-600 dark:text-purple-400',
  },
  {
    type: 'cats' as MetricType,
    label: 'Cat Analytics',
    description: 'Cat breeds, health metrics, and adoptions',
    icon: Target,
    color: 'pink',
    bgColor: 'bg-pink-100 dark:bg-pink-900',
    iconColor: 'text-pink-600 dark:text-pink-400',
  },
  {
    type: 'games' as MetricType,
    label: 'Game Analytics',
    description: 'Game plays, scores, and achievements',
    icon: Gamepad2,
    color: 'orange',
    bgColor: 'bg-orange-100 dark:bg-orange-900',
    iconColor: 'text-orange-600 dark:text-orange-400',
  },
];

const reportTypeOptions = [
  {
    type: 'overview' as ReportType,
    label: 'Overview',
    description: 'High-level summary with key metrics',
    icon: TrendingUp,
  },
  {
    type: 'detailed' as ReportType,
    label: 'Detailed',
    description: 'In-depth analysis with comprehensive data',
    icon: Activity,
  },
  {
    type: 'export' as ReportType,
    label: 'Export Ready',
    description: 'Optimized for reports and presentations',
    icon: Filter,
  },
];

const additionalFilters = [
  {
    id: 'device-type',
    label: 'Device Type',
    options: ['All', 'Desktop', 'Mobile', 'Tablet'],
  },
  {
    id: 'user-segment',
    label: 'User Segment',
    options: ['All Users', 'New Users', 'Returning Users', 'Premium Users'],
  },
  {
    id: 'geographic',
    label: 'Geographic',
    options: ['All Regions', 'North America', 'Europe', 'Asia Pacific', 'Other'],
  },
  {
    id: 'subscription-tier',
    label: 'Subscription Tier',
    options: ['All Tiers', 'Free', 'Premium', 'Pro'],
  },
];

export const AnalyticsFilters: React.FC<AnalyticsFiltersProps> = ({
  selectedMetric,
  onMetricChange,
  reportType,
  onReportTypeChange,
  onClose,
}) => {
  const [selectedFilters, setSelectedFilters] = useState<Record<string, string>>({
    'device-type': 'All',
    'user-segment': 'All Users',
    'geographic': 'All Regions',
    'subscription-tier': 'All Tiers',
  });
  
  const [expandedSections, setExpandedSections] = useState<Record<string, boolean>>({
    metrics: true,
    reportType: true,
    advanced: false,
  });

  const handleFilterChange = (filterId: string, value: string) => {
    setSelectedFilters(prev => ({
      ...prev,
      [filterId]: value,
    }));
  };

  const toggleSection = (section: string) => {
    setExpandedSections(prev => ({
      ...prev,
      [section]: !prev[section],
    }));
  };

  const resetFilters = () => {
    setSelectedFilters({
      'device-type': 'All',
      'user-segment': 'All Users',
      'geographic': 'All Regions',
      'subscription-tier': 'All Tiers',
    });
  };

  const applyFilters = () => {
    // In a real implementation, you would apply these filters to your data
    console.log('Applying filters:', { selectedMetric, reportType, selectedFilters });
    onClose();
  };

  return (
    <Card className="overflow-hidden">
      <CardContent className="p-0">
        {/* Header */}
        <div className="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
          <div className="flex items-center gap-2">
            <Filter className="w-5 h-5 text-gray-600 dark:text-gray-400" />
            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
              Analytics Filters
            </h2>
          </div>
          
          <div className="flex items-center gap-2">
            <Button
              onClick={resetFilters}
              variant="outline"
              size="sm"
            >
              Reset
            </Button>
            <button
              onClick={onClose}
              className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
              <X className="w-4 h-4 text-gray-500" />
            </button>
          </div>
        </div>

        <div className="p-6 space-y-6">
          {/* Metric Selection */}
          <div>
            <button
              onClick={() => toggleSection('metrics')}
              className="flex items-center justify-between w-full mb-3 text-left"
            >
              <h3 className="font-medium text-gray-900 dark:text-white">
                Primary Metric
              </h3>
              <motion.div
                animate={{ rotate: expandedSections.metrics ? 180 : 0 }}
                transition={{ duration: 0.2 }}
                className="text-gray-500"
              >
                ▼
              </motion.div>
            </button>
            
            <AnimatePresence>
              {expandedSections.metrics && (
                <motion.div
                  initial={{ height: 0, opacity: 0 }}
                  animate={{ height: 'auto', opacity: 1 }}
                  exit={{ height: 0, opacity: 0 }}
                  transition={{ duration: 0.2 }}
                  className="overflow-hidden"
                >
                  <div className="grid grid-cols-1 gap-3">
                    {metricOptions.map((option) => {
                      const Icon = option.icon;
                      const isSelected = selectedMetric === option.type;
                      
                      return (
                        <button
                          key={option.type}
                          onClick={() => onMetricChange(option.type)}
                          className={`p-4 rounded-lg border-2 text-left transition-all ${
                            isSelected
                              ? 'border-purple-500 bg-purple-50 dark:bg-purple-900'
                              : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                          }`}
                        >
                          <div className="flex items-start gap-3">
                            <div className={`w-10 h-10 rounded-lg ${option.bgColor} flex items-center justify-center flex-shrink-0`}>
                              <Icon className={`w-5 h-5 ${option.iconColor}`} />
                            </div>
                            <div className="flex-1">
                              <div className="font-medium text-gray-900 dark:text-white">
                                {option.label}
                              </div>
                              <div className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {option.description}
                              </div>
                            </div>
                            {isSelected && (
                              <div className="w-5 h-5 bg-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <div className="w-2 h-2 bg-white rounded-full"></div>
                              </div>
                            )}
                          </div>
                        </button>
                      );
                    })}
                  </div>
                </motion.div>
              )}
            </AnimatePresence>
          </div>

          {/* Report Type */}
          <div>
            <button
              onClick={() => toggleSection('reportType')}
              className="flex items-center justify-between w-full mb-3 text-left"
            >
              <h3 className="font-medium text-gray-900 dark:text-white">
                Report Type
              </h3>
              <motion.div
                animate={{ rotate: expandedSections.reportType ? 180 : 0 }}
                transition={{ duration: 0.2 }}
                className="text-gray-500"
              >
                ▼
              </motion.div>
            </button>
            
            <AnimatePresence>
              {expandedSections.reportType && (
                <motion.div
                  initial={{ height: 0, opacity: 0 }}
                  animate={{ height: 'auto', opacity: 1 }}
                  exit={{ height: 0, opacity: 0 }}
                  transition={{ duration: 0.2 }}
                  className="overflow-hidden"
                >
                  <div className="grid grid-cols-3 gap-3">
                    {reportTypeOptions.map((option) => {
                      const Icon = option.icon;
                      const isSelected = reportType === option.type;
                      
                      return (
                        <button
                          key={option.type}
                          onClick={() => onReportTypeChange(option.type)}
                          className={`p-3 rounded-lg border-2 text-center transition-all ${
                            isSelected
                              ? 'border-purple-500 bg-purple-50 dark:bg-purple-900'
                              : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                          }`}
                        >
                          <Icon className={`w-6 h-6 mx-auto mb-2 ${
                            isSelected ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400'
                          }`} />
                          <div className="font-medium text-gray-900 dark:text-white text-sm">
                            {option.label}
                          </div>
                          <div className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            {option.description}
                          </div>
                        </button>
                      );
                    })}
                  </div>
                </motion.div>
              )}
            </AnimatePresence>
          </div>

          {/* Advanced Filters */}
          <div>
            <button
              onClick={() => toggleSection('advanced')}
              className="flex items-center justify-between w-full mb-3 text-left"
            >
              <h3 className="font-medium text-gray-900 dark:text-white">
                Advanced Filters
              </h3>
              <motion.div
                animate={{ rotate: expandedSections.advanced ? 180 : 0 }}
                transition={{ duration: 0.2 }}
                className="text-gray-500"
              >
                ▼
              </motion.div>
            </button>
            
            <AnimatePresence>
              {expandedSections.advanced && (
                <motion.div
                  initial={{ height: 0, opacity: 0 }}
                  animate={{ height: 'auto', opacity: 1 }}
                  exit={{ height: 0, opacity: 0 }}
                  transition={{ duration: 0.2 }}
                  className="overflow-hidden"
                >
                  <div className="space-y-4">
                    {additionalFilters.map((filter) => (
                      <div key={filter.id}>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                          {filter.label}
                        </label>
                        <select
                          value={selectedFilters[filter.id]}
                          onChange={(e) => handleFilterChange(filter.id, e.target.value)}
                          className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                          {filter.options.map((option) => (
                            <option key={option} value={option}>
                              {option}
                            </option>
                          ))}
                        </select>
                      </div>
                    ))}
                  </div>
                </motion.div>
              )}
            </AnimatePresence>
          </div>

          {/* Applied Filters Summary */}
          <div className="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">
              Current Selection
            </h4>
            <div className="space-y-1 text-sm text-gray-600 dark:text-gray-400">
              <div>Metric: <span className="font-medium">{metricOptions.find(m => m.type === selectedMetric)?.label}</span></div>
              <div>Report Type: <span className="font-medium">{reportTypeOptions.find(r => r.type === reportType)?.label}</span></div>
              {Object.entries(selectedFilters).map(([key, value]) => (
                value !== 'All' && value !== 'All Users' && value !== 'All Regions' && value !== 'All Tiers' && (
                  <div key={key}>
                    {additionalFilters.find(f => f.id === key)?.label}: <span className="font-medium">{value}</span>
                  </div>
                )
              ))}
            </div>
          </div>

          {/* Actions */}
          <div className="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <Button
              onClick={applyFilters}
              variant="primary"
              className="flex-1"
            >
              Apply Filters
            </Button>
            <Button
              onClick={onClose}
              variant="outline"
            >
              Cancel
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};
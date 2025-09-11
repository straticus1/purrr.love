import React, { useState, useMemo } from 'react';
import { motion } from 'framer-motion';
import { 
  BarChart3, 
  TrendingUp, 
  Users, 
  DollarSign,
  Calendar,
  Download,
  Filter,
  RefreshCw,
  Eye,
  MousePointer,
  Clock,
  Target
} from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { useAnalytics } from '@/hooks/useAnalytics';
import { MetricCard } from '@/components/analytics/MetricCard';
import { ChartContainer } from '@/components/analytics/ChartContainer';
import { ReportGenerator } from '@/components/analytics/ReportGenerator';
import { DateRangePicker } from '@/components/analytics/DateRangePicker';
import { AnalyticsFilters } from '@/components/analytics/AnalyticsFilters';

type DateRange = {
  startDate: Date;
  endDate: Date;
};

type MetricType = 'users' | 'engagement' | 'revenue' | 'cats' | 'games';

export const AnalyticsDashboard: React.FC = () => {
  const [dateRange, setDateRange] = useState<DateRange>({
    startDate: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000), // 30 days ago
    endDate: new Date(),
  });
  const [selectedMetric, setSelectedMetric] = useState<MetricType>('users');
  const [showFilters, setShowFilters] = useState(false);
  const [reportType, setReportType] = useState<'overview' | 'detailed' | 'export'>('overview');

  const {
    overview,
    userMetrics,
    engagementMetrics,
    revenueMetrics,
    catMetrics,
    gameMetrics,
    isLoading,
    error,
    refreshData,
  } = useAnalytics(dateRange);

  const keyMetrics = useMemo(() => [
    {
      id: 'total-users',
      title: 'Total Users',
      value: overview?.totalUsers || 0,
      change: overview?.userGrowth || 0,
      icon: Users,
      color: 'blue',
      trend: overview?.userGrowth > 0 ? 'up' : 'down',
    },
    {
      id: 'active-users',
      title: 'Active Users',
      value: overview?.activeUsers || 0,
      change: overview?.activeUserGrowth || 0,
      icon: Eye,
      color: 'green',
      trend: overview?.activeUserGrowth > 0 ? 'up' : 'down',
    },
    {
      id: 'revenue',
      title: 'Revenue',
      value: overview?.totalRevenue || 0,
      change: overview?.revenueGrowth || 0,
      icon: DollarSign,
      color: 'purple',
      trend: overview?.revenueGrowth > 0 ? 'up' : 'down',
      prefix: '$',
    },
    {
      id: 'engagement',
      title: 'Avg. Session Time',
      value: overview?.avgSessionTime || 0,
      change: overview?.sessionTimeGrowth || 0,
      icon: Clock,
      color: 'orange',
      trend: overview?.sessionTimeGrowth > 0 ? 'up' : 'down',
      suffix: 'min',
    },
    {
      id: 'total-cats',
      title: 'Total Cats',
      value: overview?.totalCats || 0,
      change: overview?.catsGrowth || 0,
      icon: Target,
      color: 'pink',
      trend: overview?.catsGrowth > 0 ? 'up' : 'down',
    },
    {
      id: 'conversion-rate',
      title: 'Conversion Rate',
      value: overview?.conversionRate || 0,
      change: overview?.conversionRateGrowth || 0,
      icon: TrendingUp,
      color: 'indigo',
      trend: overview?.conversionRateGrowth > 0 ? 'up' : 'down',
      suffix: '%',
    },
  ], [overview]);

  const chartConfigs = {
    users: {
      title: 'User Analytics',
      data: userMetrics?.dailySignups || [],
      color: '#3B82F6',
    },
    engagement: {
      title: 'Engagement Metrics',
      data: engagementMetrics?.sessionDuration || [],
      color: '#10B981',
    },
    revenue: {
      title: 'Revenue Analytics',
      data: revenueMetrics?.dailyRevenue || [],
      color: '#8B5CF6',
    },
    cats: {
      title: 'Cat Analytics',
      data: catMetrics?.newCats || [],
      color: '#EC4899',
    },
    games: {
      title: 'Game Analytics',
      data: gameMetrics?.gamePlays || [],
      color: '#F59E0B',
    },
  };

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center">
        <Card className="p-6 max-w-md">
          <CardContent>
            <div className="text-center">
              <BarChart3 className="w-16 h-16 text-red-500 mx-auto mb-4" />
              <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Analytics Error
              </h3>
              <p className="text-gray-600 dark:text-gray-400 mb-4">
                {error}
              </p>
              <Button onClick={refreshData} variant="primary">
                Try Again
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      <div className="max-w-7xl mx-auto p-6">
        {/* Header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="mb-8"
        >
          <div className="flex items-center justify-between mb-6">
            <div>
              <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Analytics Dashboard
              </h1>
              <p className="text-gray-600 dark:text-gray-400">
                Comprehensive insights into your cat ecosystem performance
              </p>
            </div>
            
            <div className="flex items-center gap-3">
              <Button
                onClick={() => setShowFilters(!showFilters)}
                variant="outline"
                icon={<Filter className="w-4 h-4" />}
              >
                Filters
              </Button>
              
              <Button
                onClick={refreshData}
                variant="outline"
                icon={<RefreshCw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />}
              >
                Refresh
              </Button>
              
              <ReportGenerator 
                dateRange={dateRange}
                selectedMetrics={[selectedMetric]}
              />
            </div>
          </div>

          {/* Date Range Picker */}
          <DateRangePicker
            dateRange={dateRange}
            onChange={setDateRange}
          />
        </motion.div>

        {/* Filters Panel */}
        {showFilters && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="mb-6"
          >
            <AnalyticsFilters
              selectedMetric={selectedMetric}
              onMetricChange={setSelectedMetric}
              reportType={reportType}
              onReportTypeChange={setReportType}
              onClose={() => setShowFilters(false)}
            />
          </motion.div>
        )}

        {/* Key Metrics */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8"
        >
          {keyMetrics.map((metric) => (
            <MetricCard
              key={metric.id}
              {...metric}
              isLoading={isLoading}
            />
          ))}
        </motion.div>

        {/* Main Chart */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
          className="mb-8"
        >
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-semibold text-gray-900 dark:text-white">
                  {chartConfigs[selectedMetric].title}
                </h2>
                
                <div className="flex items-center gap-2">
                  {Object.keys(chartConfigs).map((metric) => (
                    <Button
                      key={metric}
                      onClick={() => setSelectedMetric(metric as MetricType)}
                      variant={selectedMetric === metric ? 'primary' : 'outline'}
                      size="sm"
                    >
                      {metric.charAt(0).toUpperCase() + metric.slice(1)}
                    </Button>
                  ))}
                </div>
              </div>

              <ChartContainer
                data={chartConfigs[selectedMetric].data}
                color={chartConfigs[selectedMetric].color}
                type="line"
                height={300}
                isLoading={isLoading}
              />
            </CardContent>
          </Card>
        </motion.div>

        {/* Detailed Analytics Grid */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.3 }}
          className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8"
        >
          {/* User Demographics */}
          <Card>
            <CardContent className="p-6">
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                User Demographics
              </h3>
              <ChartContainer
                data={userMetrics?.demographics || []}
                type="pie"
                height={250}
                isLoading={isLoading}
              />
            </CardContent>
          </Card>

          {/* Top Performing Content */}
          <Card>
            <CardContent className="p-6">
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Popular Cat Breeds
              </h3>
              <div className="space-y-3">
                {catMetrics?.topBreeds?.map((breed, index) => (
                  <div key={breed.name} className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                        <span className="text-sm font-medium text-purple-600 dark:text-purple-400">
                          {index + 1}
                        </span>
                      </div>
                      <span className="font-medium text-gray-900 dark:text-white">
                        {breed.name}
                      </span>
                    </div>
                    <div className="text-right">
                      <span className="text-lg font-semibold text-gray-900 dark:text-white">
                        {breed.count}
                      </span>
                      <span className="block text-sm text-gray-500">cats</span>
                    </div>
                  </div>
                )) || (
                  <div className="text-center py-8">
                    <p className="text-gray-500">No data available</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Revenue Breakdown */}
          <Card>
            <CardContent className="p-6">
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Revenue Sources
              </h3>
              <ChartContainer
                data={revenueMetrics?.sourceBreakdown || []}
                type="doughnut"
                height={250}
                isLoading={isLoading}
              />
            </CardContent>
          </Card>

          {/* Engagement Heatmap */}
          <Card>
            <CardContent className="p-6">
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Activity Heatmap
              </h3>
              <div className="grid grid-cols-7 gap-1">
                {engagementMetrics?.activityHeatmap?.map((day, index) => (
                  <div key={index} className="space-y-1">
                    <div className="text-xs text-gray-500 text-center">
                      {['S', 'M', 'T', 'W', 'T', 'F', 'S'][index]}
                    </div>
                    {day.hours.map((hour, hourIndex) => (
                      <div
                        key={hourIndex}
                        className={`w-3 h-3 rounded-sm ${
                          hour.activity > 75 ? 'bg-green-500' :
                          hour.activity > 50 ? 'bg-green-400' :
                          hour.activity > 25 ? 'bg-green-300' :
                          hour.activity > 0 ? 'bg-green-200' :
                          'bg-gray-200 dark:bg-gray-700'
                        }`}
                        title={`${hour.activity}% active`}
                      />
                    ))}
                  </div>
                )) || (
                  <div className="col-span-7 text-center py-8">
                    <p className="text-gray-500">No activity data available</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </motion.div>

        {/* Real-time Events */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.4 }}
        >
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                  Recent Activity
                </h3>
                <div className="flex items-center gap-2 text-sm text-green-600">
                  <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                  Live
                </div>
              </div>
              
              <div className="space-y-3 max-h-64 overflow-y-auto">
                {overview?.recentEvents?.map((event, index) => (
                  <div key={index} className="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <div className="flex-1">
                      <span className="text-sm text-gray-900 dark:text-white">
                        {event.description}
                      </span>
                      <span className="block text-xs text-gray-500 mt-1">
                        {event.timestamp}
                      </span>
                    </div>
                    {event.value && (
                      <span className="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {event.value}
                      </span>
                    )}
                  </div>
                )) || (
                  <div className="text-center py-8">
                    <MousePointer className="w-12 h-12 text-gray-400 mx-auto mb-3" />
                    <p className="text-gray-500">No recent activity</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </motion.div>
      </div>
    </div>
  );
};
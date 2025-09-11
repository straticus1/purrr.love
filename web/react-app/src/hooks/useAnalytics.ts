import { useState, useEffect, useMemo } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient, handleApiError } from '@/lib/api';
import toast from 'react-hot-toast';

export interface DateRange {
  startDate: Date;
  endDate: Date;
}

export interface AnalyticsOverview {
  totalUsers: number;
  activeUsers: number;
  totalRevenue: number;
  totalCats: number;
  avgSessionTime: number;
  conversionRate: number;
  userGrowth: number;
  activeUserGrowth: number;
  revenueGrowth: number;
  catsGrowth: number;
  sessionTimeGrowth: number;
  conversionRateGrowth: number;
  recentEvents: Array<{
    id: string;
    description: string;
    timestamp: string;
    value?: string;
  }>;
}

export interface UserMetrics {
  dailySignups: Array<{
    date: string;
    value: number;
  }>;
  demographics: Array<{
    label: string;
    value: number;
    percentage: number;
  }>;
  retentionRates: {
    day1: number;
    day7: number;
    day30: number;
  };
  deviceBreakdown: Array<{
    device: string;
    users: number;
    percentage: number;
  }>;
}

export interface EngagementMetrics {
  sessionDuration: Array<{
    date: string;
    value: number;
  }>;
  pageViews: Array<{
    page: string;
    views: number;
    uniqueViews: number;
  }>;
  activityHeatmap: Array<{
    day: string;
    hours: Array<{
      hour: number;
      activity: number;
    }>;
  }>;
  featureUsage: Array<{
    feature: string;
    usage: number;
    growth: number;
  }>;
}

export interface RevenueMetrics {
  dailyRevenue: Array<{
    date: string;
    value: number;
  }>;
  sourceBreakdown: Array<{
    source: string;
    revenue: number;
    percentage: number;
  }>;
  averageOrderValue: number;
  lifetimeValue: number;
  subscriptionMetrics: {
    newSubscriptions: number;
    churnRate: number;
    monthlyRecurringRevenue: number;
  };
}

export interface CatMetrics {
  newCats: Array<{
    date: string;
    value: number;
  }>;
  topBreeds: Array<{
    name: string;
    count: number;
    percentage: number;
  }>;
  healthMetrics: {
    averageHealth: number;
    sickCats: number;
    vetVisits: number;
  };
  adoptionRates: Array<{
    date: string;
    adoptions: number;
    availableCats: number;
  }>;
}

export interface GameMetrics {
  gamePlays: Array<{
    date: string;
    value: number;
  }>;
  popularGames: Array<{
    game: string;
    plays: number;
    avgScore: number;
  }>;
  playerProgression: Array<{
    level: number;
    players: number;
  }>;
  achievements: Array<{
    name: string;
    unlocks: number;
    rarity: number;
  }>;
}

export interface AnalyticsEvent {
  eventType: string;
  eventData: any;
  userId?: string;
  sessionId?: string;
  timestamp?: Date;
}

export const useAnalytics = (dateRange: DateRange) => {
  const queryClient = useQueryClient();

  // Overview query
  const overviewQuery = useQuery({
    queryKey: ['analytics', 'overview', dateRange],
    queryFn: () => apiClient.post<AnalyticsOverview>('/analytics/overview', {
      startDate: dateRange.startDate.toISOString(),
      endDate: dateRange.endDate.toISOString(),
    }),
    staleTime: 1000 * 60 * 5, // 5 minutes
  });

  // User metrics query
  const userMetricsQuery = useQuery({
    queryKey: ['analytics', 'users', dateRange],
    queryFn: () => apiClient.post<UserMetrics>('/analytics/users', {
      startDate: dateRange.startDate.toISOString(),
      endDate: dateRange.endDate.toISOString(),
    }),
    staleTime: 1000 * 60 * 5,
  });

  // Engagement metrics query
  const engagementMetricsQuery = useQuery({
    queryKey: ['analytics', 'engagement', dateRange],
    queryFn: () => apiClient.post<EngagementMetrics>('/analytics/engagement', {
      startDate: dateRange.startDate.toISOString(),
      endDate: dateRange.endDate.toISOString(),
    }),
    staleTime: 1000 * 60 * 5,
  });

  // Revenue metrics query
  const revenueMetricsQuery = useQuery({
    queryKey: ['analytics', 'revenue', dateRange],
    queryFn: () => apiClient.post<RevenueMetrics>('/analytics/revenue', {
      startDate: dateRange.startDate.toISOString(),
      endDate: dateRange.endDate.toISOString(),
    }),
    staleTime: 1000 * 60 * 5,
  });

  // Cat metrics query
  const catMetricsQuery = useQuery({
    queryKey: ['analytics', 'cats', dateRange],
    queryFn: () => apiClient.post<CatMetrics>('/analytics/cats', {
      startDate: dateRange.startDate.toISOString(),
      endDate: dateRange.endDate.toISOString(),
    }),
    staleTime: 1000 * 60 * 5,
  });

  // Game metrics query
  const gameMetricsQuery = useQuery({
    queryKey: ['analytics', 'games', dateRange],
    queryFn: () => apiClient.post<GameMetrics>('/analytics/games', {
      startDate: dateRange.startDate.toISOString(),
      endDate: dateRange.endDate.toISOString(),
    }),
    staleTime: 1000 * 60 * 5,
  });

  // Track analytics event mutation
  const trackEventMutation = useMutation({
    mutationFn: (event: AnalyticsEvent) => {
      return apiClient.post('/analytics/track', {
        ...event,
        timestamp: event.timestamp || new Date(),
      });
    },
    onError: (error) => {
      console.error('Failed to track analytics event:', handleApiError(error));
      // Don't show toast for tracking failures to avoid user annoyance
    },
  });

  // Generate report mutation
  const generateReportMutation = useMutation({
    mutationFn: async (params: {
      type: 'pdf' | 'csv' | 'json';
      metrics: string[];
      dateRange: DateRange;
    }) => {
      const response = await apiClient.post('/analytics/report', params);
      return response;
    },
    onSuccess: (data) => {
      // Trigger download
      if (data.downloadUrl) {
        window.open(data.downloadUrl, '_blank');
      }
      toast.success('Report generated successfully');
    },
    onError: (error) => {
      toast.error(`Report generation failed: ${handleApiError(error)}`);
    },
  });

  // Real-time event tracking
  const trackEvent = (eventType: string, eventData: any, userId?: string) => {
    trackEventMutation.mutate({
      eventType,
      eventData,
      userId,
      sessionId: getSessionId(),
    });
  };

  // Page view tracking
  const trackPageView = (page: string, userId?: string) => {
    trackEvent('page_view', { page }, userId);
  };

  // User action tracking
  const trackUserAction = (action: string, details: any, userId?: string) => {
    trackEvent('user_action', { action, details }, userId);
  };

  // Cat interaction tracking
  const trackCatInteraction = (catId: string, interaction: string, userId?: string) => {
    trackEvent('cat_interaction', { catId, interaction }, userId);
  };

  // Game event tracking
  const trackGameEvent = (gameType: string, event: string, score?: number, userId?: string) => {
    trackEvent('game_event', { gameType, event, score }, userId);
  };

  // Purchase tracking
  const trackPurchase = (itemId: string, amount: number, currency: string, userId?: string) => {
    trackEvent('purchase', { itemId, amount, currency }, userId);
  };

  // Refresh all data
  const refreshData = () => {
    const queries = [
      'analytics', 'overview',
      'analytics', 'users',
      'analytics', 'engagement',
      'analytics', 'revenue',
      'analytics', 'cats',
      'analytics', 'games',
    ];
    
    queries.forEach((queryKey) => {
      queryClient.invalidateQueries({ queryKey: [queryKey] });
    });
  };

  // Get session ID (create if doesn't exist)
  const getSessionId = () => {
    let sessionId = sessionStorage.getItem('analytics_session_id');
    if (!sessionId) {
      sessionId = `session_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
      sessionStorage.setItem('analytics_session_id', sessionId);
    }
    return sessionId;
  };

  // Export data
  const exportData = (type: 'pdf' | 'csv' | 'json', metrics: string[]) => {
    generateReportMutation.mutate({
      type,
      metrics,
      dateRange,
    });
  };

  // Computed values
  const isLoading = useMemo(() => {
    return overviewQuery.isLoading || 
           userMetricsQuery.isLoading || 
           engagementMetricsQuery.isLoading || 
           revenueMetricsQuery.isLoading || 
           catMetricsQuery.isLoading || 
           gameMetricsQuery.isLoading;
  }, [
    overviewQuery.isLoading,
    userMetricsQuery.isLoading,
    engagementMetricsQuery.isLoading,
    revenueMetricsQuery.isLoading,
    catMetricsQuery.isLoading,
    gameMetricsQuery.isLoading,
  ]);

  const error = useMemo(() => {
    const errors = [
      overviewQuery.error,
      userMetricsQuery.error,
      engagementMetricsQuery.error,
      revenueMetricsQuery.error,
      catMetricsQuery.error,
      gameMetricsQuery.error,
    ].filter(Boolean);
    
    return errors.length > 0 ? handleApiError(errors[0]) : null;
  }, [
    overviewQuery.error,
    userMetricsQuery.error,
    engagementMetricsQuery.error,
    revenueMetricsQuery.error,
    catMetricsQuery.error,
    gameMetricsQuery.error,
  ]);

  return {
    // Data
    overview: overviewQuery.data,
    userMetrics: userMetricsQuery.data,
    engagementMetrics: engagementMetricsQuery.data,
    revenueMetrics: revenueMetricsQuery.data,
    catMetrics: catMetricsQuery.data,
    gameMetrics: gameMetricsQuery.data,
    
    // Loading states
    isLoading,
    isGeneratingReport: generateReportMutation.isPending,
    
    // Error states
    error,
    
    // Actions
    refreshData,
    exportData,
    
    // Event tracking
    trackEvent,
    trackPageView,
    trackUserAction,
    trackCatInteraction,
    trackGameEvent,
    trackPurchase,
  };
};

// Hook for real-time analytics (WebSocket)
export const useRealTimeAnalytics = () => {
  const [isConnected, setIsConnected] = useState(false);
  const [liveEvents, setLiveEvents] = useState<any[]>([]);
  const [liveMetrics, setLiveMetrics] = useState<any>({});

  useEffect(() => {
    // In a real implementation, this would connect to WebSocket
    console.log('Real-time analytics connection established');
    setIsConnected(true);

    // Simulate live data updates
    const interval = setInterval(() => {
      const mockEvent = {
        id: Date.now().toString(),
        type: 'user_action',
        description: 'User played with cat',
        timestamp: new Date().toISOString(),
      };
      
      setLiveEvents(prev => [mockEvent, ...prev.slice(0, 9)]); // Keep last 10 events
    }, 5000);

    return () => {
      clearInterval(interval);
      setIsConnected(false);
      console.log('Real-time analytics connection closed');
    };
  }, []);

  return {
    isConnected,
    liveEvents,
    liveMetrics,
  };
};
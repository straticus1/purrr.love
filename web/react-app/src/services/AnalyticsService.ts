import { apiClient } from '@/lib/api';

export interface AnalyticsEvent {
  eventType: string;
  eventData: Record<string, any>;
  userId?: string;
  sessionId?: string;
  timestamp?: Date;
  userAgent?: string;
  url?: string;
  referrer?: string;
  deviceInfo?: {
    platform: string;
    browser: string;
    screenResolution: string;
    language: string;
    timezone: string;
  };
}

export interface PageViewEvent {
  page: string;
  title: string;
  userId?: string;
  duration?: number;
  metadata?: Record<string, any>;
}

export interface UserActionEvent {
  action: string;
  category: string;
  label?: string;
  value?: number;
  userId?: string;
  metadata?: Record<string, any>;
}

export interface ConversionEvent {
  event: string;
  value?: number;
  currency?: string;
  userId?: string;
  metadata?: Record<string, any>;
}

class AnalyticsServiceClass {
  private isInitialized = false;
  private sessionId: string = '';
  private userId: string | null = null;
  private eventQueue: AnalyticsEvent[] = [];
  private flushInterval: NodeJS.Timeout | null = null;
  private pageStartTime: Date = new Date();

  // Initialize the analytics service
  initialize(userId?: string) {
    if (this.isInitialized) return;

    this.userId = userId || null;
    this.sessionId = this.generateSessionId();
    this.startFlushInterval();
    this.setupPageVisibilityTracking();
    this.setupErrorTracking();
    this.trackPageView();

    this.isInitialized = true;
    console.log('Analytics service initialized');
  }

  // Set user ID for tracking
  setUserId(userId: string) {
    this.userId = userId;
    this.trackEvent('user_identified', { userId });
  }

  // Generate unique session ID
  private generateSessionId(): string {
    const sessionId = `session_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    sessionStorage.setItem('analytics_session_id', sessionId);
    return sessionId;
  }

  // Get or create session ID
  private getSessionId(): string {
    if (this.sessionId) return this.sessionId;
    
    let sessionId = sessionStorage.getItem('analytics_session_id');
    if (!sessionId) {
      sessionId = this.generateSessionId();
    }
    
    this.sessionId = sessionId;
    return sessionId;
  }

  // Get device and browser information
  private getDeviceInfo() {
    const userAgent = navigator.userAgent;
    const platform = navigator.platform;
    const language = navigator.language;
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const screenResolution = `${screen.width}x${screen.height}`;
    
    // Simple browser detection
    let browser = 'Unknown';
    if (userAgent.includes('Chrome')) browser = 'Chrome';
    else if (userAgent.includes('Firefox')) browser = 'Firefox';
    else if (userAgent.includes('Safari')) browser = 'Safari';
    else if (userAgent.includes('Edge')) browser = 'Edge';

    return {
      platform,
      browser,
      screenResolution,
      language,
      timezone,
    };
  }

  // Track generic event
  trackEvent(eventType: string, eventData: Record<string, any> = {}) {
    const event: AnalyticsEvent = {
      eventType,
      eventData,
      userId: this.userId,
      sessionId: this.getSessionId(),
      timestamp: new Date(),
      userAgent: navigator.userAgent,
      url: window.location.href,
      referrer: document.referrer,
      deviceInfo: this.getDeviceInfo(),
    };

    this.eventQueue.push(event);
    
    // Flush immediately for critical events
    if (this.isCriticalEvent(eventType)) {
      this.flush();
    }
  }

  // Track page view
  trackPageView(page?: string, title?: string, metadata?: Record<string, any>) {
    const pageData: PageViewEvent = {
      page: page || window.location.pathname,
      title: title || document.title,
      userId: this.userId,
      duration: Date.now() - this.pageStartTime.getTime(),
      metadata,
    };

    this.trackEvent('page_view', pageData);
    this.pageStartTime = new Date();
  }

  // Track user action
  trackUserAction(action: string, category: string, label?: string, value?: number, metadata?: Record<string, any>) {
    const actionData: UserActionEvent = {
      action,
      category,
      label,
      value,
      userId: this.userId,
      metadata,
    };

    this.trackEvent('user_action', actionData);
  }

  // Track conversion event
  trackConversion(event: string, value?: number, currency?: string, metadata?: Record<string, any>) {
    const conversionData: ConversionEvent = {
      event,
      value,
      currency,
      userId: this.userId,
      metadata,
    };

    this.trackEvent('conversion', conversionData);
  }

  // Track cat-specific interactions
  trackCatInteraction(catId: string, interaction: string, details?: Record<string, any>) {
    this.trackUserAction('cat_interaction', 'cats', `${interaction}_${catId}`, undefined, {
      catId,
      interaction,
      ...details,
    });
  }

  // Track game events
  trackGameEvent(gameType: string, event: string, score?: number, level?: number, metadata?: Record<string, any>) {
    this.trackUserAction('game_event', 'games', `${gameType}_${event}`, score, {
      gameType,
      event,
      score,
      level,
      ...metadata,
    });
  }

  // Track purchase events
  trackPurchase(itemId: string, amount: number, currency: string, paymentMethod?: string, metadata?: Record<string, any>) {
    this.trackConversion('purchase', amount, currency, {
      itemId,
      paymentMethod,
      ...metadata,
    });
  }

  // Track subscription events
  trackSubscription(action: string, tier: string, amount?: number, metadata?: Record<string, any>) {
    this.trackConversion('subscription', amount, 'usd', {
      action, // 'subscribe', 'upgrade', 'downgrade', 'cancel'
      tier,
      ...metadata,
    });
  }

  // Track search events
  trackSearch(query: string, results?: number, category?: string, metadata?: Record<string, any>) {
    this.trackUserAction('search', 'navigation', query, results, {
      query,
      results,
      category,
      ...metadata,
    });
  }

  // Track form interactions
  trackFormEvent(formName: string, event: string, fieldName?: string, metadata?: Record<string, any>) {
    this.trackUserAction('form_event', 'forms', `${formName}_${event}`, undefined, {
      formName,
      event, // 'start', 'complete', 'abandon', 'error'
      fieldName,
      ...metadata,
    });
  }

  // Track errors
  trackError(error: Error, context?: string, metadata?: Record<string, any>) {
    this.trackEvent('error', {
      message: error.message,
      stack: error.stack,
      context,
      url: window.location.href,
      timestamp: new Date().toISOString(),
      ...metadata,
    });
  }

  // Track performance metrics
  trackPerformance(metric: string, value: number, unit: string = 'ms', metadata?: Record<string, any>) {
    this.trackEvent('performance', {
      metric,
      value,
      unit,
      ...metadata,
    });
  }

  // Setup automatic page visibility tracking
  private setupPageVisibilityTracking() {
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        this.trackEvent('page_blur', { timestamp: new Date() });
        this.flush(); // Ensure data is sent before page becomes inactive
      } else {
        this.trackEvent('page_focus', { timestamp: new Date() });
      }
    });

    // Track page unload
    window.addEventListener('beforeunload', () => {
      this.trackPageView(); // Final page view with duration
      this.flush();
    });
  }

  // Setup automatic error tracking
  private setupErrorTracking() {
    window.addEventListener('error', (event) => {
      this.trackError(new Error(event.message), 'global_error', {
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
      });
    });

    window.addEventListener('unhandledrejection', (event) => {
      this.trackError(new Error(event.reason), 'unhandled_promise_rejection');
    });
  }

  // Check if event is critical and should be sent immediately
  private isCriticalEvent(eventType: string): boolean {
    return [
      'error',
      'purchase',
      'subscription',
      'conversion',
      'user_identified',
    ].includes(eventType);
  }

  // Start automatic flush interval
  private startFlushInterval() {
    this.flushInterval = setInterval(() => {
      if (this.eventQueue.length > 0) {
        this.flush();
      }
    }, 10000); // Flush every 10 seconds
  }

  // Flush events to server
  async flush() {
    if (this.eventQueue.length === 0) return;

    const events = [...this.eventQueue];
    this.eventQueue = [];

    try {
      await apiClient.post('/analytics/events', { events });
      console.log(`Flushed ${events.length} analytics events`);
    } catch (error) {
      console.error('Failed to send analytics events:', error);
      // Re-queue events if they failed to send (with a limit to prevent infinite growth)
      if (this.eventQueue.length < 100) {
        this.eventQueue.unshift(...events.slice(0, 50)); // Only keep the most recent 50 failed events
      }
    }
  }

  // Get session stats
  getSessionStats() {
    return {
      sessionId: this.sessionId,
      userId: this.userId,
      eventsQueued: this.eventQueue.length,
      sessionDuration: Date.now() - new Date(parseInt(this.sessionId.split('_')[1])).getTime(),
    };
  }

  // Cleanup on app unmount
  cleanup() {
    if (this.flushInterval) {
      clearInterval(this.flushInterval);
    }
    this.flush(); // Final flush
    this.isInitialized = false;
  }
}

// Export singleton instance
export const AnalyticsService = new AnalyticsServiceClass();

// Convenience function for React components
export const useAnalyticsTracking = () => {
  return {
    trackEvent: AnalyticsService.trackEvent.bind(AnalyticsService),
    trackPageView: AnalyticsService.trackPageView.bind(AnalyticsService),
    trackUserAction: AnalyticsService.trackUserAction.bind(AnalyticsService),
    trackConversion: AnalyticsService.trackConversion.bind(AnalyticsService),
    trackCatInteraction: AnalyticsService.trackCatInteraction.bind(AnalyticsService),
    trackGameEvent: AnalyticsService.trackGameEvent.bind(AnalyticsService),
    trackPurchase: AnalyticsService.trackPurchase.bind(AnalyticsService),
    trackSubscription: AnalyticsService.trackSubscription.bind(AnalyticsService),
    trackSearch: AnalyticsService.trackSearch.bind(AnalyticsService),
    trackFormEvent: AnalyticsService.trackFormEvent.bind(AnalyticsService),
    trackError: AnalyticsService.trackError.bind(AnalyticsService),
    trackPerformance: AnalyticsService.trackPerformance.bind(AnalyticsService),
  };
};
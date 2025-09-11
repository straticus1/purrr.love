import React from 'react';
import { motion } from 'framer-motion';
import { Crown, Calendar, CreditCard, AlertTriangle, CheckCircle, XCircle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { useSubscription } from '@/hooks/useSubscription';
import { SUBSCRIPTION_PLANS } from '@/types/subscription';
import { format, formatDistanceToNow } from 'date-fns';

export const SubscriptionDashboard: React.FC = () => {
  const {
    subscription,
    isLoadingSubscription,
    cancelSubscription,
    resumeSubscription,
    isCancellingSubscription,
    isResumingSubscription,
    hasActiveSubscription,
    isTrialing,
    isPastDue,
    willCancelAtPeriodEnd,
  } = useSubscription();

  const currentPlan = subscription 
    ? SUBSCRIPTION_PLANS.find(plan => plan.tier === subscription.tier)
    : SUBSCRIPTION_PLANS.find(plan => plan.tier === 'free');

  const handleCancelSubscription = () => {
    const confirmed = confirm(
      'Are you sure you want to cancel your subscription? You\'ll continue to have access until the end of your current billing period.'
    );
    
    if (confirmed) {
      cancelSubscription({ cancelAtPeriodEnd: true });
    }
  };

  const handleResumeSubscription = () => {
    resumeSubscription();
  };

  const getStatusIcon = () => {
    if (isPastDue) return <AlertTriangle className="w-5 h-5 text-yellow-500" />;
    if (hasActiveSubscription) return <CheckCircle className="w-5 h-5 text-green-500" />;
    if (willCancelAtPeriodEnd) return <XCircle className="w-5 h-5 text-red-500" />;
    return <Crown className="w-5 h-5 text-gray-400" />;
  };

  const getStatusText = () => {
    if (isPastDue) return 'Payment Failed';
    if (isTrialing) return 'Trial Active';
    if (hasActiveSubscription && !willCancelAtPeriodEnd) return 'Active';
    if (willCancelAtPeriodEnd) return 'Cancelling';
    return 'Free Plan';
  };

  const getStatusColor = () => {
    if (isPastDue) return 'text-yellow-600 dark:text-yellow-400';
    if (hasActiveSubscription && !willCancelAtPeriodEnd) return 'text-green-600 dark:text-green-400';
    if (willCancelAtPeriodEnd) return 'text-red-600 dark:text-red-400';
    return 'text-gray-600 dark:text-gray-400';
  };

  if (isLoadingSubscription) {
    return (
      <Card>
        <CardContent className="p-8 text-center">
          <div className="animate-spin w-8 h-8 border-2 border-purple-500 border-t-transparent rounded-full mx-auto mb-4"></div>
          <p className="text-gray-600 dark:text-gray-400">Loading subscription details...</p>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="space-y-6">
      {/* Current Plan Overview */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Crown className="w-5 h-5" />
            Current Plan
          </CardTitle>
        </CardHeader>
        
        <CardContent>
          <div className="flex items-center justify-between mb-6">
            <div className="flex items-center gap-4">
              <div className={`w-16 h-16 rounded-xl flex items-center justify-center ${
                currentPlan?.tier === 'free' ? 'bg-gray-100 dark:bg-gray-800' :
                currentPlan?.tier === 'premium' ? 'bg-purple-100 dark:bg-purple-900' :
                'bg-gradient-to-br from-purple-500 to-pink-500'
              }`}>
                <span className="text-2xl">
                  {currentPlan?.tier === 'free' ? '🐱' :
                   currentPlan?.tier === 'premium' ? '⚡' : '👑'}
                </span>
              </div>
              
              <div>
                <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                  {currentPlan?.name}
                </h3>
                
                <div className="flex items-center gap-2">
                  {getStatusIcon()}
                  <span className={`font-medium ${getStatusColor()}`}>
                    {getStatusText()}
                  </span>
                </div>
                
                {subscription && (
                  <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {willCancelAtPeriodEnd 
                      ? `Access until ${format(new Date(subscription.currentPeriodEnd), 'MMM dd, yyyy')}`
                      : `Next billing: ${format(new Date(subscription.currentPeriodEnd), 'MMM dd, yyyy')}`
                    }
                  </p>
                )}
              </div>
            </div>

            <div className="text-right">
              {currentPlan && currentPlan.tier !== 'free' && (
                <div className="mb-4">
                  <div className="text-3xl font-bold text-gray-900 dark:text-white">
                    ${currentPlan.monthlyPrice}
                  </div>
                  <div className="text-sm text-gray-600 dark:text-gray-400">
                    per month
                  </div>
                </div>
              )}
              
              {!hasActiveSubscription ? (
                <Button variant="primary">
                  Upgrade Plan
                </Button>
              ) : willCancelAtPeriodEnd ? (
                <Button
                  onClick={handleResumeSubscription}
                  loading={isResumingSubscription}
                  variant="primary"
                >
                  Resume Subscription
                </Button>
              ) : (
                <Button
                  onClick={handleCancelSubscription}
                  loading={isCancellingSubscription}
                  variant="outline"
                  className="text-red-600 hover:text-red-700 border-red-200 hover:border-red-300"
                >
                  Cancel Subscription
                </Button>
              )}
            </div>
          </div>

          {/* Plan Features */}
          <div>
            <h4 className="font-semibold text-gray-900 dark:text-white mb-4">
              Your Plan Includes:
            </h4>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {currentPlan?.features
                .filter(feature => feature.included)
                .slice(0, 6)
                .map((feature, index) => (
                  <motion.div
                    key={index}
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ duration: 0.3, delay: index * 0.1 }}
                    className="flex items-center gap-3"
                  >
                    <CheckCircle className="w-4 h-4 text-green-500 flex-shrink-0" />
                    <span className="text-sm text-gray-700 dark:text-gray-300">
                      {feature.name}
                      {feature.limit && feature.limit !== 'unlimited' && (
                        <span className="text-gray-500 dark:text-gray-500 ml-1">
                          ({feature.limit})
                        </span>
                      )}
                    </span>
                  </motion.div>
                ))}
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Usage Stats */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                <CreditCard className="w-6 h-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">Cats Owned</p>
                <p className="text-2xl font-bold text-gray-900 dark:text-white">12</p>
                <p className="text-xs text-gray-500">
                  of {currentPlan?.features.find(f => f.name.includes('cats'))?.limit || 'unlimited'} limit
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                <Calendar className="w-6 h-6 text-green-600 dark:text-green-400" />
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">Member Since</p>
                <p className="text-2xl font-bold text-gray-900 dark:text-white">
                  {subscription 
                    ? formatDistanceToNow(new Date(subscription.createdAt), { addSuffix: false })
                    : '---'
                  }
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                <Crown className="w-6 h-6 text-purple-600 dark:text-purple-400" />
              </div>
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">Status</p>
                <p className="text-xl font-bold text-gray-900 dark:text-white">
                  {getStatusText()}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Alerts and Notifications */}
      {isPastDue && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl"
        >
          <div className="flex items-start gap-3">
            <AlertTriangle className="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
            <div>
              <h4 className="font-semibold text-yellow-800 dark:text-yellow-200 mb-1">
                Payment Failed
              </h4>
              <p className="text-sm text-yellow-700 dark:text-yellow-300 mb-3">
                Your last payment couldn't be processed. Please update your payment method to continue using premium features.
              </p>
              <Button variant="outline" size="sm">
                Update Payment Method
              </Button>
            </div>
          </div>
        </motion.div>
      )}

      {willCancelAtPeriodEnd && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl"
        >
          <div className="flex items-start gap-3">
            <XCircle className="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
            <div>
              <h4 className="font-semibold text-red-800 dark:text-red-200 mb-1">
                Subscription Ending
              </h4>
              <p className="text-sm text-red-700 dark:text-red-300 mb-3">
                Your subscription will end on {subscription && format(new Date(subscription.currentPeriodEnd), 'MMM dd, yyyy')}. 
                You'll still have access to premium features until then.
              </p>
              <Button
                onClick={handleResumeSubscription}
                loading={isResumingSubscription}
                variant="outline"
                size="sm"
              >
                Resume Subscription
              </Button>
            </div>
          </div>
        </motion.div>
      )}
    </div>
  );
};
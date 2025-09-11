import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Check, Star, Zap } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { useSubscription } from '@/hooks/useSubscription';
import { SUBSCRIPTION_PLANS, SubscriptionTier } from '@/types/subscription';
import { useThemedStyles } from '@/contexts/ThemeContext';

interface SubscriptionPlansProps {
  currentTier?: SubscriptionTier;
  onPlanSelect?: (tier: SubscriptionTier, billing: 'monthly' | 'yearly') => void;
}

export const SubscriptionPlans: React.FC<SubscriptionPlansProps> = ({
  currentTier = 'free',
  onPlanSelect,
}) => {
  const { colors } = useThemedStyles();
  const [billingCycle, setBillingCycle] = useState<'monthly' | 'yearly'>('monthly');
  const { createCheckoutSession, isCreatingCheckoutSession } = useSubscription();

  const handleUpgrade = (tier: SubscriptionTier) => {
    if (tier === 'free') return;
    
    const plan = SUBSCRIPTION_PLANS.find(p => p.tier === tier);
    if (!plan) return;

    const priceId = plan.stripePriceIds[billingCycle];
    
    if (onPlanSelect) {
      onPlanSelect(tier, billingCycle);
    } else {
      createCheckoutSession({
        priceId,
        tier,
        billing: billingCycle,
        successUrl: `${window.location.origin}/subscription/success`,
        cancelUrl: `${window.location.origin}/subscription`,
      });
    }
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(price);
  };

  const getYearlySavings = (monthlyPrice: number, yearlyPrice: number) => {
    const monthlyCost = monthlyPrice * 12;
    const savings = monthlyCost - yearlyPrice;
    const percentage = Math.round((savings / monthlyCost) * 100);
    return { savings, percentage };
  };

  return (
    <div className="space-y-8">
      {/* Billing Toggle */}
      <div className="flex justify-center">
        <div className="bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
          <div className="flex">
            {(['monthly', 'yearly'] as const).map((cycle) => (
              <button
                key={cycle}
                onClick={() => setBillingCycle(cycle)}
                className={`px-6 py-2 text-sm font-medium rounded-lg transition-all duration-200 ${
                  billingCycle === cycle
                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                }`}
              >
                {cycle === 'monthly' ? 'Monthly' : 'Yearly'}
                {cycle === 'yearly' && (
                  <span className="ml-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-0.5 rounded-full">
                    Save up to 20%
                  </span>
                )}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Plans Grid */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        {SUBSCRIPTION_PLANS.map((plan, index) => {
          const isCurrentPlan = plan.tier === currentTier;
          const price = billingCycle === 'monthly' ? plan.monthlyPrice : plan.yearlyPrice;
          const yearlySavings = billingCycle === 'yearly' && plan.yearlyPrice > 0 
            ? getYearlySavings(plan.monthlyPrice, plan.yearlyPrice)
            : null;

          return (
            <motion.div
              key={plan.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.3, delay: index * 0.1 }}
              className="relative"
            >
              {plan.popular && (
                <div className="absolute -top-4 left-1/2 transform -translate-x-1/2 z-10">
                  <div className="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-4 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                    <Star className="w-3 h-3" />
                    Most Popular
                  </div>
                </div>
              )}

              <Card
                className={`h-full relative overflow-hidden ${
                  plan.popular ? 'ring-2 ring-purple-500 ring-opacity-50' : ''
                } ${isCurrentPlan ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : ''}`}
                variant={plan.popular ? 'elevated' : 'default'}
                padding="none"
              >
                <CardContent className="p-8">
                  {/* Plan Header */}
                  <div className="text-center mb-8">
                    <div className="flex items-center justify-center mb-4">
                      <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${
                        plan.tier === 'free' ? 'bg-gray-100 dark:bg-gray-800' :
                        plan.tier === 'premium' ? 'bg-purple-100 dark:bg-purple-900' :
                        'bg-gradient-to-br from-purple-500 to-pink-500'
                      }`}>
                        {plan.tier === 'free' && <span className="text-2xl">🐱</span>}
                        {plan.tier === 'premium' && <Zap className="w-6 h-6 text-purple-600 dark:text-purple-400" />}
                        {plan.tier === 'pro' && <Star className="w-6 h-6 text-white" />}
                      </div>
                    </div>
                    
                    <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                      {plan.name}
                    </h3>
                    
                    <p className="text-gray-600 dark:text-gray-400 mb-6">
                      {plan.description}
                    </p>

                    {/* Pricing */}
                    <div className="mb-6">
                      <div className="flex items-baseline justify-center gap-2">
                        <span className="text-4xl font-bold text-gray-900 dark:text-white">
                          {formatPrice(price)}
                        </span>
                        {price > 0 && (
                          <span className="text-gray-600 dark:text-gray-400">
                            /{billingCycle === 'monthly' ? 'month' : 'year'}
                          </span>
                        )}
                      </div>
                      
                      {yearlySavings && (
                        <div className="text-sm text-green-600 dark:text-green-400 mt-2">
                          Save {formatPrice(yearlySavings.savings)} ({yearlySavings.percentage}%) annually
                        </div>
                      )}
                    </div>

                    {/* CTA Button */}
                    <Button
                      onClick={() => handleUpgrade(plan.tier)}
                      disabled={isCurrentPlan || isCreatingCheckoutSession}
                      variant={plan.popular ? 'primary' : 'outline'}
                      size="lg"
                      fullWidth
                      loading={isCreatingCheckoutSession}
                    >
                      {isCurrentPlan 
                        ? 'Current Plan' 
                        : plan.tier === 'free' 
                        ? 'Get Started' 
                        : `Upgrade to ${plan.name}`
                      }
                    </Button>

                    {isCurrentPlan && (
                      <div className="flex items-center justify-center mt-2 text-sm text-green-600 dark:text-green-400">
                        <Check className="w-4 h-4 mr-1" />
                        Active
                      </div>
                    )}
                  </div>

                  {/* Features */}
                  <div className="space-y-3">
                    {plan.features.map((feature, featureIndex) => (
                      <div
                        key={featureIndex}
                        className={`flex items-center gap-3 ${
                          feature.included ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600'
                        }`}
                      >
                        <div className={`w-5 h-5 rounded-full flex items-center justify-center ${
                          feature.included 
                            ? 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' 
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600'
                        }`}>
                          {feature.included ? (
                            <Check className="w-3 h-3" />
                          ) : (
                            <span className="text-xs">×</span>
                          )}
                        </div>
                        
                        <span className="text-sm">
                          {feature.name}
                          {feature.limit && feature.limit !== 'unlimited' && (
                            <span className="text-gray-500 dark:text-gray-500 ml-1">
                              ({feature.limit})
                            </span>
                          )}
                        </span>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </motion.div>
          );
        })}
      </div>
    </div>
  );
};
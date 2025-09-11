import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Settings, CreditCard, Receipt, Crown } from 'lucide-react';
import { SubscriptionDashboard } from '@/components/subscription/SubscriptionDashboard';
import { SubscriptionPlans } from '@/components/subscription/SubscriptionPlans';
import { PaymentMethods } from '@/components/subscription/PaymentMethods';
import { BillingHistory } from '@/components/subscription/BillingHistory';
import { StripeProvider } from '@/providers/StripeProvider';
import { useSubscription } from '@/hooks/useSubscription';

type TabType = 'overview' | 'plans' | 'payment' | 'billing';

const tabs: { id: TabType; label: string; icon: React.ReactNode }[] = [
  { id: 'overview', label: 'Overview', icon: <Crown className="w-4 h-4" /> },
  { id: 'plans', label: 'Plans & Pricing', icon: <Settings className="w-4 h-4" /> },
  { id: 'payment', label: 'Payment Methods', icon: <CreditCard className="w-4 h-4" /> },
  { id: 'billing', label: 'Billing History', icon: <Receipt className="w-4 h-4" /> },
];

export const Subscription: React.FC = () => {
  const [activeTab, setActiveTab] = useState<TabType>('overview');
  const { subscription } = useSubscription();

  const renderTabContent = () => {
    switch (activeTab) {
      case 'overview':
        return <SubscriptionDashboard />;
      case 'plans':
        return (
          <SubscriptionPlans 
            currentTier={subscription?.tier || 'free'}
          />
        );
      case 'payment':
        return <PaymentMethods />;
      case 'billing':
        return <BillingHistory />;
      default:
        return <SubscriptionDashboard />;
    }
  };

  return (
    <StripeProvider>
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
        <div className="max-w-6xl mx-auto">
          {/* Header */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="mb-8"
          >
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
              Subscription & Billing
            </h1>
            <p className="text-gray-600 dark:text-gray-400">
              Manage your subscription, payment methods, and billing history
            </p>
          </motion.div>

          {/* Navigation Tabs */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
            className="mb-8"
          >
            <nav className="flex space-x-1 bg-white dark:bg-gray-800 rounded-xl p-1 shadow-sm">
              {tabs.map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 ${
                    activeTab === tab.id
                      ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300'
                      : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700'
                  }`}
                >
                  {tab.icon}
                  {tab.label}
                </button>
              ))}
            </nav>
          </motion.div>

          {/* Tab Content */}
          <motion.div
            key={activeTab}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3 }}
          >
            {renderTabContent()}
          </motion.div>
        </div>
      </div>
    </StripeProvider>
  );
};
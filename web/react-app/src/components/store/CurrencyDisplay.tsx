import React from 'react';
import { motion } from 'framer-motion';
import { Coins, Zap, Plus, TrendingUp } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { User } from '@/store/authStore';

interface CurrencyDisplayProps {
  user: User;
  onBuyCoins?: () => void;
  onBuyPremiumCoins?: () => void;
  compact?: boolean;
}

export const CurrencyDisplay: React.FC<CurrencyDisplayProps> = ({
  user,
  onBuyCoins,
  onBuyPremiumCoins,
  compact = false,
}) => {
  const formatCurrency = (amount: number): string => {
    if (amount >= 1000000) {
      return `${(amount / 1000000).toFixed(1)}M`;
    } else if (amount >= 1000) {
      return `${(amount / 1000).toFixed(1)}K`;
    }
    return amount.toLocaleString();
  };

  if (compact) {
    return (
      <div className="flex items-center gap-4">
        {/* Regular Coins */}
        <div className="flex items-center gap-2 bg-yellow-100 dark:bg-yellow-900 px-3 py-2 rounded-lg">
          <Coins className="w-4 h-4 text-yellow-600 dark:text-yellow-400" />
          <span className="font-medium text-yellow-800 dark:text-yellow-200">
            {formatCurrency(user.coins)}
          </span>
          {onBuyCoins && (
            <button
              onClick={onBuyCoins}
              className="p-1 rounded-md hover:bg-yellow-200 dark:hover:bg-yellow-800 transition-colors"
            >
              <Plus className="w-3 h-3 text-yellow-600 dark:text-yellow-400" />
            </button>
          )}
        </div>

        {/* Premium Coins */}
        <div className="flex items-center gap-2 bg-purple-100 dark:bg-purple-900 px-3 py-2 rounded-lg">
          <Zap className="w-4 h-4 text-purple-600 dark:text-purple-400" />
          <span className="font-medium text-purple-800 dark:text-purple-200">
            {formatCurrency(user.premiumCoins || 0)}
          </span>
          {onBuyPremiumCoins && (
            <button
              onClick={onBuyPremiumCoins}
              className="p-1 rounded-md hover:bg-purple-200 dark:hover:bg-purple-800 transition-colors"
            >
              <Plus className="w-3 h-3 text-purple-600 dark:text-purple-400" />
            </button>
          )}
        </div>
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
      {/* Regular Coins Card */}
      <motion.div
        whileHover={{ scale: 1.02 }}
        whileTap={{ scale: 0.98 }}
      >
        <Card className="overflow-hidden bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900 dark:to-orange-900 border-yellow-200 dark:border-yellow-700">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-4">
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center">
                  <Coins className="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 className="font-semibold text-gray-900 dark:text-white">Regular Coins</h3>
                  <p className="text-xs text-gray-600 dark:text-gray-400">In-game currency</p>
                </div>
              </div>
              {onBuyCoins && (
                <Button
                  onClick={onBuyCoins}
                  variant="outline"
                  size="sm"
                  icon={<Plus className="w-4 h-4" />}
                >
                  Buy
                </Button>
              )}
            </div>

            <div className="space-y-2">
              <div className="text-2xl font-bold text-gray-900 dark:text-white">
                {user.coins.toLocaleString()}
              </div>
              <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <TrendingUp className="w-4 h-4 text-green-500" />
                <span>Earned from gameplay</span>
              </div>
            </div>

            {/* Recent earnings (placeholder) */}
            <div className="mt-4 pt-4 border-t border-yellow-200 dark:border-yellow-700">
              <div className="text-xs text-gray-600 dark:text-gray-400">
                Recent: +150 coins from cat care
              </div>
            </div>
          </CardContent>
        </Card>
      </motion.div>

      {/* Premium Coins Card */}
      <motion.div
        whileHover={{ scale: 1.02 }}
        whileTap={{ scale: 0.98 }}
      >
        <Card className="overflow-hidden bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900 dark:to-indigo-900 border-purple-200 dark:border-purple-700">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-4">
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                  <Zap className="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 className="font-semibold text-gray-900 dark:text-white">Premium Coins</h3>
                  <p className="text-xs text-gray-600 dark:text-gray-400">Premium currency</p>
                </div>
              </div>
              {onBuyPremiumCoins && (
                <Button
                  onClick={onBuyPremiumCoins}
                  variant="primary"
                  size="sm"
                  icon={<Plus className="w-4 h-4" />}
                >
                  Buy
                </Button>
              )}
            </div>

            <div className="space-y-2">
              <div className="text-2xl font-bold text-gray-900 dark:text-white">
                {(user.premiumCoins || 0).toLocaleString()}
              </div>
              <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <div className="w-2 h-2 bg-purple-500 rounded-full"></div>
                <span>Purchase with real money</span>
              </div>
            </div>

            {/* Exchange rate info */}
            <div className="mt-4 pt-4 border-t border-purple-200 dark:border-purple-700">
              <div className="text-xs text-gray-600 dark:text-gray-400">
                1 Premium Coin = 100 Regular Coins
              </div>
            </div>
          </CardContent>
        </Card>
      </motion.div>
    </div>
  );
};
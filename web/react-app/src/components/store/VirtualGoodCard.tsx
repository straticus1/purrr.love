import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { ShoppingCart, Heart, Gift, Clock, Zap, Star, Package } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { VirtualGood } from '@/types/virtualGoods';
import { useVirtualStore } from '@/hooks/useVirtualStore';
import { useAuthStore } from '@/store/authStore';

interface VirtualGoodCardProps {
  virtualGood: VirtualGood;
  viewMode?: 'grid' | 'list';
  showFeaturedBadge?: boolean;
  onSelect?: (item: VirtualGood) => void;
}

export const VirtualGoodCard: React.FC<VirtualGoodCardProps> = ({
  virtualGood,
  viewMode = 'grid',
  showFeaturedBadge = false,
  onSelect,
}) => {
  const [isLiked, setIsLiked] = useState(false);
  const [showDetails, setShowDetails] = useState(false);
  
  const { user } = useAuthStore();
  const {
    purchaseWithCoins,
    purchaseWithStripe,
    isPurchasing,
    isPurchasingWithStripe,
    canAfford,
    getEffectivePrice,
    getInventoryItem,
  } = useVirtualStore();

  const inventoryItem = getInventoryItem(virtualGood.id);
  const effectivePrice = getEffectivePrice(virtualGood);
  const hasDiscount = effectivePrice.coins < (virtualGood.pricing.coins || 0) ||
                     effectivePrice.premiumCoins < (virtualGood.pricing.premiumCoins || 0);

  const isLimitedTime = virtualGood.isLimitedTime && virtualGood.availableUntil &&
                       new Date(virtualGood.availableUntil) > new Date();
  
  const isLimitedQuantity = virtualGood.isLimitedQuantity && virtualGood.totalQuantity &&
                           virtualGood.soldQuantity < virtualGood.totalQuantity;
  
  const isOutOfStock = virtualGood.isLimitedQuantity && virtualGood.totalQuantity &&
                      virtualGood.soldQuantity >= virtualGood.totalQuantity;

  const handlePurchase = () => {
    if (virtualGood.pricing.coins && canAfford(virtualGood)) {
      purchaseWithCoins({
        virtualGoodId: virtualGood.id,
        quantity: 1,
        useCoins: 'regular',
      });
    } else if (virtualGood.pricing.realMoney) {
      purchaseWithStripe({
        virtualGoodId: virtualGood.id,
        quantity: 1,
        successUrl: `${window.location.origin}/store?success=true`,
        cancelUrl: `${window.location.origin}/store`,
      });
    }
  };

  const getRarityColor = (rarity: string) => {
    const colors = {
      common: 'text-gray-600 bg-gray-100 dark:bg-gray-800 dark:text-gray-400',
      uncommon: 'text-green-600 bg-green-100 dark:bg-green-900 dark:text-green-400',
      rare: 'text-blue-600 bg-blue-100 dark:bg-blue-900 dark:text-blue-400',
      epic: 'text-purple-600 bg-purple-100 dark:bg-purple-900 dark:text-purple-400',
      legendary: 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-400',
    };
    return colors[rarity as keyof typeof colors] || colors.common;
  };

  const formatPrice = (price: number, currency: string) => {
    if (currency === 'coins') return `${price.toLocaleString()} coins`;
    if (currency === 'premium_coins') return `${price.toLocaleString()} 💎`;
    if (currency === 'real_money') return `$${(price / 100).toFixed(2)}`;
    return price.toString();
  };

  const getPrimaryPrice = () => {
    if (effectivePrice.coins > 0) return { amount: effectivePrice.coins, currency: 'coins' };
    if (effectivePrice.premiumCoins > 0) return { amount: effectivePrice.premiumCoins, currency: 'premium_coins' };
    if (effectivePrice.realMoney > 0) return { amount: effectivePrice.realMoney, currency: 'real_money' };
    return { amount: 0, currency: 'coins' };
  };

  const primaryPrice = getPrimaryPrice();

  if (viewMode === 'list') {
    return (
      <motion.div
        whileHover={{ scale: 1.01 }}
        whileTap={{ scale: 0.99 }}
        className="cursor-pointer"
        onClick={() => onSelect?.(virtualGood)}
      >
        <Card className="hover:shadow-lg transition-shadow duration-200">
          <CardContent className="p-4">
            <div className="flex items-center gap-4">
              {/* Image */}
              <div className="relative w-20 h-20 flex-shrink-0">
                <img
                  src={virtualGood.image}
                  alt={virtualGood.name}
                  className="w-full h-full object-cover rounded-lg"
                />
                {inventoryItem && (
                  <div className="absolute -top-1 -right-1 w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center text-xs">
                    {inventoryItem.quantity}
                  </div>
                )}
              </div>

              {/* Info */}
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-1">
                  <h3 className="font-semibold text-gray-900 dark:text-white truncate">
                    {virtualGood.name}
                  </h3>
                  <div className={`px-2 py-1 rounded-full text-xs font-medium ${getRarityColor(virtualGood.rarity)}`}>
                    {virtualGood.rarity}
                  </div>
                  {showFeaturedBadge && (
                    <Star className="w-4 h-4 text-yellow-500 fill-current" />
                  )}
                </div>
                
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2 truncate">
                  {virtualGood.description}
                </p>

                <div className="flex items-center gap-4 text-sm">
                  <span className="font-medium text-gray-900 dark:text-white">
                    {formatPrice(primaryPrice.amount, primaryPrice.currency)}
                  </span>
                  
                  {hasDiscount && virtualGood.pricing.coins && (
                    <span className="text-gray-500 line-through">
                      {formatPrice(virtualGood.pricing.coins, 'coins')}
                    </span>
                  )}
                  
                  {isLimitedTime && (
                    <div className="flex items-center gap-1 text-orange-600">
                      <Clock className="w-3 h-3" />
                      <span className="text-xs">Limited</span>
                    </div>
                  )}
                </div>
              </div>

              {/* Actions */}
              <div className="flex items-center gap-2">
                {!inventoryItem && !isOutOfStock && (
                  <Button
                    onClick={(e) => {
                      e.stopPropagation();
                      handlePurchase();
                    }}
                    loading={isPurchasing || isPurchasingWithStripe}
                    variant="primary"
                    size="sm"
                    icon={<ShoppingCart className="w-4 h-4" />}
                  >
                    Buy
                  </Button>
                )}
                
                {inventoryItem && (
                  <div className="flex items-center gap-1 text-green-600 text-sm">
                    <Package className="w-4 h-4" />
                    <span>Owned</span>
                  </div>
                )}
              </div>
            </div>
          </CardContent>
        </Card>
      </motion.div>
    );
  }

  return (
    <motion.div
      whileHover={{ y: -5 }}
      whileTap={{ scale: 0.98 }}
      className="cursor-pointer"
      onClick={() => onSelect?.(virtualGood)}
    >
      <Card className="overflow-hidden hover:shadow-xl transition-all duration-300 group relative">
        {/* Top badges */}
        <div className="absolute top-3 left-3 right-3 flex justify-between items-start z-10">
          <div className="flex flex-col gap-1">
            <div className={`px-2 py-1 rounded-full text-xs font-medium ${getRarityColor(virtualGood.rarity)}`}>
              {virtualGood.rarity}
            </div>
            {showFeaturedBadge && (
              <div className="px-2 py-1 bg-yellow-500 text-white rounded-full text-xs font-medium flex items-center gap-1">
                <Star className="w-3 h-3" />
                Featured
              </div>
            )}
          </div>
          
          <div className="flex flex-col gap-1">
            {hasDiscount && (
              <div className="px-2 py-1 bg-red-500 text-white rounded-full text-xs font-medium">
                SALE
              </div>
            )}
            {isLimitedTime && (
              <div className="px-2 py-1 bg-orange-500 text-white rounded-full text-xs font-medium flex items-center gap-1">
                <Clock className="w-3 h-3" />
                Limited
              </div>
            )}
            {inventoryItem && (
              <div className="px-2 py-1 bg-green-500 text-white rounded-full text-xs font-medium">
                {inventoryItem.quantity}x
              </div>
            )}
          </div>
        </div>

        {/* Item Image */}
        <div className="aspect-square relative overflow-hidden">
          <img
            src={virtualGood.image}
            alt={virtualGood.name}
            className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
          />
          
          {isOutOfStock && (
            <div className="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
              <div className="text-white font-bold">OUT OF STOCK</div>
            </div>
          )}
        </div>

        <CardContent className="p-4">
          {/* Item info */}
          <div className="mb-3">
            <h3 className="font-semibold text-gray-900 dark:text-white truncate mb-1">
              {virtualGood.name}
            </h3>
            <p className="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
              {virtualGood.description}
            </p>
          </div>

          {/* Effects preview */}
          {virtualGood.effects && virtualGood.effects.length > 0 && (
            <div className="mb-3">
              <div className="flex flex-wrap gap-1">
                {virtualGood.effects.slice(0, 2).map((effect, index) => (
                  <div
                    key={index}
                    className="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full text-xs flex items-center gap-1"
                  >
                    <Zap className="w-3 h-3" />
                    +{effect.value}{effect.percentage ? '%' : ''} {effect.type.replace('_', ' ')}
                  </div>
                ))}
                {virtualGood.effects.length > 2 && (
                  <div className="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-full text-xs">
                    +{virtualGood.effects.length - 2}
                  </div>
                )}
              </div>
            </div>
          )}

          {/* Stock info */}
          {isLimitedQuantity && virtualGood.totalQuantity && (
            <div className="mb-3">
              <div className="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                <span>Stock</span>
                <span>{virtualGood.totalQuantity - virtualGood.soldQuantity} left</span>
              </div>
              <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div 
                  className="bg-blue-500 h-2 rounded-full transition-all duration-300"
                  style={{ width: `${((virtualGood.totalQuantity - virtualGood.soldQuantity) / virtualGood.totalQuantity) * 100}%` }}
                ></div>
              </div>
            </div>
          )}

          {/* Price and actions */}
          <div className="flex items-center justify-between">
            <div>
              <div className="font-bold text-gray-900 dark:text-white">
                {formatPrice(primaryPrice.amount, primaryPrice.currency)}
              </div>
              {hasDiscount && virtualGood.pricing.coins && (
                <div className="text-sm text-gray-500 line-through">
                  {formatPrice(virtualGood.pricing.coins, 'coins')}
                </div>
              )}
            </div>

            <div className="flex items-center gap-2">
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  setIsLiked(!isLiked);
                }}
                className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              >
                <Heart className={`w-4 h-4 ${isLiked ? 'fill-red-500 text-red-500' : 'text-gray-400'}`} />
              </button>

              {!inventoryItem && !isOutOfStock && (
                <Button
                  onClick={(e) => {
                    e.stopPropagation();
                    handlePurchase();
                  }}
                  loading={isPurchasing || isPurchasingWithStripe}
                  disabled={!canAfford(virtualGood)}
                  variant="primary"
                  size="sm"
                  icon={<ShoppingCart className="w-4 h-4" />}
                >
                  Buy
                </Button>
              )}
              
              {inventoryItem && (
                <div className="flex items-center gap-1 px-3 py-2 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm">
                  <Package className="w-4 h-4" />
                  Owned
                </div>
              )}
            </div>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
};
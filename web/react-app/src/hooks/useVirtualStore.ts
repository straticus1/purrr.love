import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiClient, handleApiError } from '@/lib/api';
import { 
  VirtualGood, 
  UserInventory, 
  Purchase, 
  CurrencyPackage, 
  StoreSection,
  StorePromotion,
  StoreFilters,
  StoreSortOptions 
} from '@/types/virtualGoods';
import { useAuthStore } from '@/store/authStore';
import toast from 'react-hot-toast';

export const useVirtualStore = () => {
  const queryClient = useQueryClient();
  const { user, spendCoins, addCoins, spendPremiumCoins, addPremiumCoins } = useAuthStore();

  // Fetch all virtual goods
  const virtualGoodsQuery = useQuery({
    queryKey: ['virtual-goods'],
    queryFn: () => apiClient.get<VirtualGood[]>('/store/items'),
    staleTime: 1000 * 60 * 5, // 5 minutes
  });

  // Fetch store sections
  const storeSectionsQuery = useQuery({
    queryKey: ['store-sections'],
    queryFn: () => apiClient.get<StoreSection[]>('/store/sections'),
    staleTime: 1000 * 60 * 10, // 10 minutes
  });

  // Fetch current promotions
  const promotionsQuery = useQuery({
    queryKey: ['store-promotions'],
    queryFn: () => apiClient.get<StorePromotion[]>('/store/promotions'),
    staleTime: 1000 * 60 * 2, // 2 minutes
  });

  // Fetch user inventory
  const inventoryQuery = useQuery({
    queryKey: ['inventory', user?.id],
    queryFn: () => apiClient.get<UserInventory>(`/store/inventory/${user?.id}`),
    enabled: !!user?.id,
    staleTime: 1000 * 30, // 30 seconds
  });

  // Fetch purchase history
  const purchaseHistoryQuery = useQuery({
    queryKey: ['purchase-history', user?.id],
    queryFn: () => apiClient.get<Purchase[]>(`/store/purchases/${user?.id}`),
    enabled: !!user?.id,
    staleTime: 1000 * 60 * 2, // 2 minutes
  });

  // Fetch premium currency packages
  const currencyPackagesQuery = useQuery({
    queryKey: ['currency-packages'],
    queryFn: () => apiClient.get<CurrencyPackage[]>('/store/currency-packages'),
    staleTime: 1000 * 60 * 10, // 10 minutes
  });

  // Purchase virtual good with coins
  const purchaseWithCoinsMutation = useMutation({
    mutationFn: async (data: {
      virtualGoodId: string;
      quantity: number;
      useCoins: 'regular' | 'premium';
    }) => {
      return apiClient.post('/store/purchase/coins', data);
    },
    onSuccess: (data, variables) => {
      const item = virtualGoodsQuery.data?.find(g => g.id === variables.virtualGoodId);
      const cost = variables.useCoins === 'regular' 
        ? (item?.pricing.coins || 0) * variables.quantity
        : (item?.pricing.premiumCoins || 0) * variables.quantity;
      
      // Update local coin balance
      if (variables.useCoins === 'regular') {
        spendCoins(cost);
      } else {
        spendPremiumCoins(cost);
      }
      
      // Invalidate relevant queries
      queryClient.invalidateQueries({ queryKey: ['inventory', user?.id] });
      queryClient.invalidateQueries({ queryKey: ['purchase-history', user?.id] });
      
      toast.success(`Purchased ${item?.name || 'item'} successfully!`);
    },
    onError: (error) => {
      toast.error(`Purchase failed: ${handleApiError(error)}`);
    },
  });

  // Purchase virtual good with real money (Stripe)
  const purchaseWithStripeMutation = useMutation({
    mutationFn: async (data: {
      virtualGoodId: string;
      quantity: number;
      successUrl?: string;
      cancelUrl?: string;
    }) => {
      return apiClient.post('/store/purchase/stripe', data);
    },
    onSuccess: (data) => {
      if (data.url) {
        // Redirect to Stripe checkout
        window.location.href = data.url;
      }
    },
    onError: (error) => {
      toast.error(`Payment setup failed: ${handleApiError(error)}`);
    },
  });

  // Purchase premium currency package
  const purchaseCurrencyMutation = useMutation({
    mutationFn: async (data: {
      packageId: string;
      successUrl?: string;
      cancelUrl?: string;
    }) => {
      return apiClient.post('/store/currency/purchase', data);
    },
    onSuccess: (data) => {
      if (data.url) {
        // Redirect to Stripe checkout
        window.location.href = data.url;
      }
    },
    onError: (error) => {
      toast.error(`Currency purchase failed: ${handleApiError(error)}`);
    },
  });

  // Use inventory item
  const useItemMutation = useMutation({
    mutationFn: async (data: {
      inventoryItemId: string;
      targetCatId?: string; // For items that target specific cats
      quantity?: number;
    }) => {
      return apiClient.post('/store/inventory/use', data);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['inventory', user?.id] });
      queryClient.invalidateQueries({ queryKey: ['cats'] }); // Items might affect cats
      toast.success('Item used successfully!');
    },
    onError: (error) => {
      toast.error(`Failed to use item: ${handleApiError(error)}`);
    },
  });

  // Gift item to another user
  const giftItemMutation = useMutation({
    mutationFn: async (data: {
      virtualGoodId: string;
      quantity: number;
      recipientId: string;
      message?: string;
    }) => {
      return apiClient.post('/store/gift', data);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['inventory', user?.id] });
      toast.success('Gift sent successfully!');
    },
    onError: (error) => {
      toast.error(`Failed to send gift: ${handleApiError(error)}`);
    },
  });

  // Filter and sort virtual goods
  const filterAndSortGoods = (
    goods: VirtualGood[], 
    filters: Partial<StoreFilters>, 
    sort: Partial<StoreSortOptions>
  ) => {
    let filtered = goods.filter(good => {
      // Category filter
      if (filters.category && good.category !== filters.category) return false;
      
      // Rarity filter
      if (filters.rarity && good.rarity !== filters.rarity) return false;
      
      // Type filter
      if (filters.type && good.type !== filters.type) return false;
      
      // Price range filter
      if (filters.priceRange) {
        const price = filters.currency === 'premium_coins' 
          ? good.pricing.premiumCoins || 0
          : filters.currency === 'real_money'
          ? good.pricing.realMoney || 0
          : good.pricing.coins || 0;
        
        if (price < filters.priceRange[0] || price > filters.priceRange[1]) return false;
      }
      
      // Availability filter
      if (filters.availability) {
        const now = new Date();
        switch (filters.availability) {
          case 'limited_time':
            if (!good.isLimitedTime) return false;
            break;
          case 'limited_quantity':
            if (!good.isLimitedQuantity) return false;
            break;
          case 'available':
            if (good.isLimitedTime && good.availableUntil && new Date(good.availableUntil) < now) return false;
            if (good.isLimitedQuantity && good.totalQuantity && good.soldQuantity >= good.totalQuantity) return false;
            break;
        }
      }
      
      // In stock filter
      if (filters.inStock && good.isLimitedQuantity && good.totalQuantity) {
        if (good.soldQuantity >= good.totalQuantity) return false;
      }
      
      return true;
    });

    // Sort
    if (sort.sortBy) {
      filtered.sort((a, b) => {
        let aValue: any, bValue: any;
        
        switch (sort.sortBy) {
          case 'name':
            aValue = a.name;
            bValue = b.name;
            break;
          case 'price_low':
          case 'price_high':
            aValue = a.pricing.coins || a.pricing.premiumCoins || a.pricing.realMoney || 0;
            bValue = b.pricing.coins || b.pricing.premiumCoins || b.pricing.realMoney || 0;
            break;
          case 'newest':
            aValue = new Date(a.createdAt);
            bValue = new Date(b.createdAt);
            break;
          case 'rarity':
            const rarityOrder = { common: 0, uncommon: 1, rare: 2, epic: 3, legendary: 4 };
            aValue = rarityOrder[a.rarity];
            bValue = rarityOrder[b.rarity];
            break;
          case 'popular':
            aValue = a.soldQuantity;
            bValue = b.soldQuantity;
            break;
        }
        
        if (aValue < bValue) return sort.sortDirection === 'desc' ? 1 : -1;
        if (aValue > bValue) return sort.sortDirection === 'desc' ? -1 : 1;
        return 0;
      });
    }

    return filtered;
  };

  // Get item from inventory
  const getInventoryItem = (virtualGoodId: string) => {
    return inventoryQuery.data?.items.find(item => item.virtualGoodId === virtualGoodId);
  };

  // Check if user can afford item
  const canAfford = (virtualGood: VirtualGood, quantity: number = 1) => {
    if (!user) return false;
    
    const coinsNeeded = (virtualGood.pricing.coins || 0) * quantity;
    const premiumCoinsNeeded = (virtualGood.pricing.premiumCoins || 0) * quantity;
    
    // Check if user can afford with regular coins
    if (coinsNeeded > 0 && user.coins >= coinsNeeded) return true;
    
    // Check if user can afford with premium coins
    if (premiumCoinsNeeded > 0 && (user.premiumCoins || 0) >= premiumCoinsNeeded) return true;
    
    // Check if user has real money pricing (always assume they can pay with real money)
    if (virtualGood.pricing.realMoney && virtualGood.pricing.realMoney > 0) return true;
    
    return false;
  };

  // Get effective price after promotions
  const getEffectivePrice = (virtualGood: VirtualGood, quantity: number = 1) => {
    let effectivePrice = {
      coins: (virtualGood.pricing.coins || 0) * quantity,
      premiumCoins: (virtualGood.pricing.premiumCoins || 0) * quantity,
      realMoney: (virtualGood.pricing.realMoney || 0) * quantity,
    };

    // Apply promotions
    const applicablePromotions = promotionsQuery.data?.filter(promo => 
      promo.isActive && 
      new Date() >= new Date(promo.startDate) && 
      new Date() <= new Date(promo.endDate) &&
      (promo.applicableItems.includes(virtualGood.id) || 
       promo.applicableCategories.includes(virtualGood.category))
    );

    applicablePromotions?.forEach(promo => {
      if (promo.discountType === 'percentage') {
        const discount = promo.discountValue / 100;
        effectivePrice.coins = Math.floor(effectivePrice.coins * (1 - discount));
        effectivePrice.premiumCoins = Math.floor(effectivePrice.premiumCoins * (1 - discount));
        effectivePrice.realMoney = Math.floor(effectivePrice.realMoney * (1 - discount));
      }
    });

    return effectivePrice;
  };

  return {
    // Data
    virtualGoods: virtualGoodsQuery.data || [],
    storeSections: storeSectionsQuery.data || [],
    promotions: promotionsQuery.data || [],
    inventory: inventoryQuery.data,
    purchaseHistory: purchaseHistoryQuery.data || [],
    currencyPackages: currencyPackagesQuery.data || [],
    
    // Loading states
    isLoadingGoods: virtualGoodsQuery.isLoading,
    isLoadingSections: storeSectionsQuery.isLoading,
    isLoadingPromotions: promotionsQuery.isLoading,
    isLoadingInventory: inventoryQuery.isLoading,
    isLoadingHistory: purchaseHistoryQuery.isLoading,
    isLoadingPackages: currencyPackagesQuery.isLoading,
    
    // Error states
    goodsError: virtualGoodsQuery.error,
    sectionsError: storeSectionsQuery.error,
    promotionsError: promotionsQuery.error,
    inventoryError: inventoryQuery.error,
    historyError: purchaseHistoryQuery.error,
    packagesError: currencyPackagesQuery.error,
    
    // Actions
    purchaseWithCoins: purchaseWithCoinsMutation.mutate,
    purchaseWithStripe: purchaseWithStripeMutation.mutate,
    purchaseCurrency: purchaseCurrencyMutation.mutate,
    useItem: useItemMutation.mutate,
    giftItem: giftItemMutation.mutate,
    
    // Action loading states
    isPurchasing: purchaseWithCoinsMutation.isPending,
    isPurchasingWithStripe: purchaseWithStripeMutation.isPending,
    isPurchasingCurrency: purchaseCurrencyMutation.isPending,
    isUsingItem: useItemMutation.isPending,
    isGifting: giftItemMutation.isPending,
    
    // Utilities
    filterAndSortGoods,
    getInventoryItem,
    canAfford,
    getEffectivePrice,
  };
};
export interface VirtualGood {
  id: string;
  name: string;
  description: string;
  category: VirtualGoodCategory;
  rarity: 'common' | 'uncommon' | 'rare' | 'epic' | 'legendary';
  type: 'consumable' | 'equipment' | 'cosmetic' | 'boost' | 'collectible';
  
  // Pricing
  pricing: {
    coins?: number;           // In-game currency
    premiumCoins?: number;    // Premium currency
    realMoney?: number;       // USD cents
    stripeProductId?: string; // Stripe product ID
  };
  
  // Media
  image: string;
  icon: string;
  animation?: string;
  
  // Effects and properties
  effects?: VirtualGoodEffect[];
  duration?: number; // Duration in minutes for consumables/boosts
  stackable: boolean;
  maxStack?: number;
  
  // Availability
  isLimitedTime: boolean;
  availableFrom?: string;
  availableUntil?: string;
  isLimitedQuantity: boolean;
  totalQuantity?: number;
  soldQuantity: number;
  
  // Requirements
  requirements?: {
    minLevel?: number;
    requiredItems?: string[];
    excludedItems?: string[];
  };
  
  // Metadata
  createdAt: string;
  updatedAt: string;
  isActive: boolean;
  tags: string[];
}

export type VirtualGoodCategory = 
  | 'food'
  | 'toys'
  | 'accessories'
  | 'environments'
  | 'boosters'
  | 'currencies'
  | 'cosmetics'
  | 'special';

export interface VirtualGoodEffect {
  type: 'stat_boost' | 'experience_boost' | 'coin_boost' | 'happiness_boost' | 'health_restore' | 'energy_restore';
  value: number;
  percentage: boolean; // true for percentage-based, false for flat value
  target: 'cat' | 'user' | 'all_cats';
}

export interface UserInventory {
  id: string;
  userId: string;
  items: InventoryItem[];
  capacity: number;
  updatedAt: string;
}

export interface InventoryItem {
  id: string;
  virtualGoodId: string;
  virtualGood: VirtualGood;
  quantity: number;
  acquiredAt: string;
  expiresAt?: string; // For time-limited items
  metadata?: Record<string, any>; // Custom data like enchantments
}

export interface Purchase {
  id: string;
  userId: string;
  virtualGoodId: string;
  virtualGood: VirtualGood;
  quantity: number;
  
  // Pricing details
  totalCost: {
    coins?: number;
    premiumCoins?: number;
    realMoney?: number; // USD cents
  };
  
  // Payment method
  paymentMethod: 'coins' | 'premium_coins' | 'stripe';
  stripePaymentIntentId?: string;
  
  // Status
  status: 'pending' | 'completed' | 'failed' | 'refunded';
  
  // Timestamps
  createdAt: string;
  completedAt?: string;
}

export interface PremiumCurrency {
  id: string;
  name: string;
  symbol: string;
  iconUrl: string;
  
  // Exchange rates
  usdRate: number; // How many cents for 1 unit
  coinExchangeRate: number; // How many regular coins for 1 premium coin
  
  // Purchase packages
  packages: CurrencyPackage[];
}

export interface CurrencyPackage {
  id: string;
  name: string;
  description: string;
  amount: number; // Amount of premium currency
  bonusAmount: number; // Bonus currency (for bulk purchases)
  usdPrice: number; // Price in USD cents
  stripeProductId: string;
  
  // Marketing
  isPopular: boolean;
  isBestValue: boolean;
  discountPercentage?: number;
  
  // Availability
  isActive: boolean;
  availableFrom?: string;
  availableUntil?: string;
}

export interface StoreSection {
  id: string;
  name: string;
  description: string;
  icon: string;
  category: VirtualGoodCategory;
  sortOrder: number;
  isActive: boolean;
  
  // Featured items
  featuredItems: string[]; // VirtualGood IDs
  
  // Filter and sort options
  allowedRarities: string[];
  allowedTypes: string[];
  defaultSort: 'newest' | 'price_low' | 'price_high' | 'popular' | 'rarity';
}

export interface StorePromotion {
  id: string;
  name: string;
  description: string;
  type: 'discount' | 'bundle' | 'bogo' | 'free_item';
  
  // Discount details
  discountType: 'percentage' | 'fixed_amount' | 'free';
  discountValue: number;
  
  // Applicable items
  applicableItems: string[]; // VirtualGood IDs
  applicableCategories: VirtualGoodCategory[];
  
  // Requirements
  minPurchaseAmount?: number;
  requiredItems?: string[]; // Must buy these items
  maxUsesPerUser?: number;
  maxTotalUses?: number;
  
  // Timing
  startDate: string;
  endDate: string;
  isActive: boolean;
  
  // Display
  bannerImage?: string;
  badgeText?: string;
  priority: number; // For ordering multiple promotions
}

// Store filter and sort interfaces
export interface StoreFilters {
  category?: VirtualGoodCategory;
  rarity?: string;
  type?: string;
  priceRange: [number, number];
  currency: 'coins' | 'premium_coins' | 'real_money' | 'all';
  availability: 'all' | 'available' | 'limited_time' | 'limited_quantity';
  inStock: boolean;
}

export interface StoreSortOptions {
  sortBy: 'newest' | 'price_low' | 'price_high' | 'popular' | 'rarity' | 'name';
  sortDirection: 'asc' | 'desc';
}

// Analytics interfaces
export interface StoreAnalytics {
  totalRevenue: {
    coins: number;
    premiumCoins: number;
    realMoney: number; // USD cents
  };
  topSellingItems: {
    virtualGoodId: string;
    virtualGood: VirtualGood;
    quantitySold: number;
    revenue: number;
  }[];
  categoryPerformance: {
    category: VirtualGoodCategory;
    itemsSold: number;
    revenue: number;
  }[];
  dailyStats: {
    date: string;
    purchases: number;
    revenue: number;
    uniqueBuyers: number;
  }[];
}
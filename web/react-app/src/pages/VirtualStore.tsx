import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { ShoppingBag, Coins, Zap, Filter, Grid, List, Star, Gift, Package } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { VirtualGoodCard } from '@/components/store/VirtualGoodCard';
import { StoreFilters } from '@/components/store/StoreFilters';
import { CurrencyDisplay } from '@/components/store/CurrencyDisplay';
import { InventoryPanel } from '@/components/store/InventoryPanel';
import { useVirtualStore } from '@/hooks/useVirtualStore';
import { useAuthStore } from '@/store/authStore';
import { StoreFilters as IStoreFilters, StoreSortOptions } from '@/types/virtualGoods';

type ViewMode = 'grid' | 'list';
type TabType = 'featured' | 'all' | 'inventory' | 'currency';

const tabs = [
  { id: 'featured', label: 'Featured', icon: Star, description: 'Curated items and deals' },
  { id: 'all', label: 'All Items', icon: ShoppingBag, description: 'Browse all virtual goods' },
  { id: 'inventory', label: 'Inventory', icon: Package, description: 'Your purchased items' },
  { id: 'currency', label: 'Currency', icon: Coins, description: 'Premium currency packages' },
];

export const VirtualStore: React.FC = () => {
  const [activeTab, setActiveTab] = useState<TabType>('featured');
  const [viewMode, setViewMode] = useState<ViewMode>('grid');
  const [searchQuery, setSearchQuery] = useState('');
  const [showFilters, setShowFilters] = useState(false);
  const [filters, setFilters] = useState<Partial<IStoreFilters>>({
    priceRange: [0, 1000],
    currency: 'all',
    availability: 'all',
    inStock: true,
  });
  const [sortOptions, setSortOptions] = useState<Partial<StoreSortOptions>>({
    sortBy: 'newest',
    sortDirection: 'desc',
  });

  const { user } = useAuthStore();
  const {
    virtualGoods,
    storeSections,
    promotions,
    inventory,
    currencyPackages,
    isLoadingGoods,
    isLoadingInventory,
    isLoadingPackages,
    filterAndSortGoods,
  } = useVirtualStore();

  // Filter goods based on search and filters
  const filteredGoods = filterAndSortGoods(
    virtualGoods.filter(good => 
      good.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      good.description.toLowerCase().includes(searchQuery.toLowerCase())
    ),
    filters,
    sortOptions
  );

  // Get featured items (items with promotions or marked as featured)
  const featuredGoods = virtualGoods.filter(good => 
    promotions.some(promo => promo.applicableItems.includes(good.id)) ||
    storeSections.some(section => section.featuredItems.includes(good.id))
  );

  const renderContent = () => {
    switch (activeTab) {
      case 'featured':
        return (
          <div className="space-y-6">
            {/* Promotions Banner */}
            {promotions.length > 0 && (
              <div className="space-y-4">
                {promotions.slice(0, 2).map((promotion) => (
                  <motion.div
                    key={promotion.id}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-600 to-pink-600 p-6 text-white"
                  >
                    <div className="relative z-10">
                      <h3 className="text-2xl font-bold mb-2">{promotion.name}</h3>
                      <p className="text-purple-100 mb-4">{promotion.description}</p>
                      <Button variant="secondary" size="lg">
                        Shop Now
                      </Button>
                    </div>
                    {promotion.bannerImage && (
                      <div className="absolute right-0 top-0 h-full w-1/3 opacity-20">
                        <img
                          src={promotion.bannerImage}
                          alt={promotion.name}
                          className="h-full w-full object-cover"
                        />
                      </div>
                    )}
                  </motion.div>
                ))}
              </div>
            )}

            {/* Featured Items */}
            <div>
              <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                Featured Items
              </h2>
              {featuredGoods.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                  {featuredGoods.slice(0, 8).map((good) => (
                    <VirtualGoodCard
                      key={good.id}
                      virtualGood={good}
                      viewMode={viewMode}
                      showFeaturedBadge
                    />
                  ))}
                </div>
              ) : (
                <div className="text-center py-12">
                  <Star className="w-16 h-16 text-gray-400 mx-auto mb-4" />
                  <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    No Featured Items
                  </h3>
                  <p className="text-gray-600 dark:text-gray-400">
                    Check back later for special promotions and featured items.
                  </p>
                </div>
              )}
            </div>
          </div>
        );

      case 'all':
        return (
          <div className="space-y-6">
            {/* Search and Controls */}
            <div className="flex items-center gap-4 mb-6">
              <div className="flex-1 relative">
                <Input
                  placeholder="Search virtual goods..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  leftIcon={<ShoppingBag className="w-4 h-4" />}
                />
              </div>
              
              <Button
                onClick={() => setShowFilters(!showFilters)}
                variant="outline"
                icon={<Filter className="w-4 h-4" />}
              >
                Filters
              </Button>
              
              <div className="flex items-center gap-1 bg-white dark:bg-gray-800 rounded-lg p-1">
                <Button
                  onClick={() => setViewMode('grid')}
                  variant={viewMode === 'grid' ? 'primary' : 'ghost'}
                  size="sm"
                  icon={<Grid className="w-4 h-4" />}
                />
                <Button
                  onClick={() => setViewMode('list')}
                  variant={viewMode === 'list' ? 'primary' : 'ghost'}
                  size="sm"
                  icon={<List className="w-4 h-4" />}
                />
              </div>
            </div>

            {/* Filters Panel */}
            {showFilters && (
              <StoreFilters
                filters={filters}
                sortOptions={sortOptions}
                onFiltersChange={setFilters}
                onSortChange={setSortOptions}
                onClose={() => setShowFilters(false)}
              />
            )}

            {/* Items Grid */}
            {isLoadingGoods ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {[...Array(12)].map((_, i) => (
                  <div key={i} className="animate-pulse">
                    <div className="bg-gray-200 dark:bg-gray-700 rounded-xl h-80"></div>
                  </div>
                ))}
              </div>
            ) : filteredGoods.length > 0 ? (
              <div className={
                viewMode === 'grid' 
                  ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6'
                  : 'space-y-4'
              }>
                {filteredGoods.map((good) => (
                  <VirtualGoodCard
                    key={good.id}
                    virtualGood={good}
                    viewMode={viewMode}
                  />
                ))}
              </div>
            ) : (
              <div className="text-center py-12">
                <div className="text-6xl mb-4">🛒</div>
                <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                  No Items Found
                </h3>
                <p className="text-gray-600 dark:text-gray-400">
                  Try adjusting your search or filter criteria.
                </p>
              </div>
            )}
          </div>
        );

      case 'inventory':
        return (
          <InventoryPanel
            inventory={inventory}
            isLoading={isLoadingInventory}
          />
        );

      case 'currency':
        return (
          <div className="space-y-6">
            <div>
              <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Premium Currency Packages
              </h2>
              <p className="text-gray-600 dark:text-gray-400 mb-6">
                Purchase premium coins to unlock exclusive items and features.
              </p>
            </div>

            {isLoadingPackages ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {[...Array(6)].map((_, i) => (
                  <div key={i} className="animate-pulse">
                    <div className="bg-gray-200 dark:bg-gray-700 rounded-xl h-64"></div>
                  </div>
                ))}
              </div>
            ) : currencyPackages.length > 0 ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {currencyPackages.map((pkg) => (
                  <Card
                    key={pkg.id}
                    className={`relative overflow-hidden ${
                      pkg.isBestValue ? 'ring-2 ring-purple-500 ring-opacity-50' : ''
                    }`}
                  >
                    {pkg.isPopular && (
                      <div className="absolute top-4 right-4 z-10">
                        <div className="bg-orange-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                          Popular
                        </div>
                      </div>
                    )}
                    
                    {pkg.isBestValue && (
                      <div className="absolute top-4 left-4 z-10">
                        <div className="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                          Best Value
                        </div>
                      </div>
                    )}

                    <CardContent className="p-6 text-center">
                      <div className="mb-4">
                        <div className="w-16 h-16 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-3">
                          <Zap className="w-8 h-8 text-white" />
                        </div>
                        <h3 className="text-xl font-bold text-gray-900 dark:text-white">
                          {pkg.name}
                        </h3>
                        <p className="text-gray-600 dark:text-gray-400 text-sm">
                          {pkg.description}
                        </p>
                      </div>

                      <div className="mb-6">
                        <div className="text-3xl font-bold text-gray-900 dark:text-white mb-1">
                          {pkg.amount.toLocaleString()}
                          {pkg.bonusAmount > 0 && (
                            <span className="text-lg text-green-600">
                              +{pkg.bonusAmount.toLocaleString()}
                            </span>
                          )}
                        </div>
                        <div className="text-sm text-gray-600 dark:text-gray-400">
                          Premium Coins
                        </div>
                      </div>

                      <div className="mb-6">
                        <div className="text-2xl font-bold text-purple-600 dark:text-purple-400">
                          ${(pkg.usdPrice / 100).toFixed(2)}
                        </div>
                        {pkg.discountPercentage && (
                          <div className="text-sm text-green-600">
                            {pkg.discountPercentage}% OFF
                          </div>
                        )}
                      </div>

                      <Button
                        variant="primary"
                        fullWidth
                        icon={<Zap className="w-4 h-4" />}
                      >
                        Purchase
                      </Button>
                    </CardContent>
                  </Card>
                ))}
              </div>
            ) : (
              <div className="text-center py-12">
                <Coins className="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                  No Currency Packages
                </h3>
                <p className="text-gray-600 dark:text-gray-400">
                  Currency packages are not available at the moment.
                </p>
              </div>
            )}
          </div>
        );

      default:
        return null;
    }
  };

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
                Virtual Store
              </h1>
              <p className="text-gray-600 dark:text-gray-400">
                Enhance your cat experience with virtual goods and premium items
              </p>
            </div>
            
            {user && <CurrencyDisplay user={user} />}
          </div>
        </motion.div>

        {/* Navigation Tabs */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="mb-6"
        >
          <nav className="flex space-x-1 bg-white dark:bg-gray-800 rounded-xl p-1 shadow-sm">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id as TabType)}
                className={`flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 ${
                  activeTab === tab.id
                    ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300'
                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700'
                }`}
              >
                <tab.icon className="w-4 h-4" />
                <div className="text-left">
                  <div>{tab.label}</div>
                  <div className="text-xs opacity-75">{tab.description}</div>
                </div>
              </button>
            ))}
          </nav>
        </motion.div>

        {/* Content */}
        <motion.div
          key={activeTab}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.3 }}
        >
          {renderContent()}
        </motion.div>
      </div>
    </div>
  );
};
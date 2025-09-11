import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Package, 
  Gift, 
  Trash2, 
  Play, 
  Clock, 
  Star,
  Filter,
  Grid,
  List,
  Search,
  Zap,
  Calendar
} from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { UserInventory, InventoryItem } from '@/types/virtualGoods';
import { useVirtualStore } from '@/hooks/useVirtualStore';

interface InventoryPanelProps {
  inventory?: UserInventory;
  isLoading: boolean;
}

type ViewMode = 'grid' | 'list';
type FilterMode = 'all' | 'consumable' | 'equipment' | 'cosmetic' | 'boost' | 'collectible';
type SortMode = 'newest' | 'oldest' | 'name' | 'quantity' | 'rarity';

export const InventoryPanel: React.FC<InventoryPanelProps> = ({
  inventory,
  isLoading,
}) => {
  const [viewMode, setViewMode] = useState<ViewMode>('grid');
  const [filterMode, setFilterMode] = useState<FilterMode>('all');
  const [sortMode, setSortMode] = useState<SortMode>('newest');
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedItems, setSelectedItems] = useState<string[]>([]);

  const { useItem, giftItem, isUsingItem, isGifting } = useVirtualStore();

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

  const isExpiringSoon = (item: InventoryItem) => {
    if (!item.expiresAt) return false;
    const expiryDate = new Date(item.expiresAt);
    const now = new Date();
    const hoursUntilExpiry = (expiryDate.getTime() - now.getTime()) / (1000 * 60 * 60);
    return hoursUntilExpiry <= 24 && hoursUntilExpiry > 0;
  };

  const isExpired = (item: InventoryItem) => {
    if (!item.expiresAt) return false;
    return new Date(item.expiresAt) < new Date();
  };

  const filteredAndSortedItems = React.useMemo(() => {
    if (!inventory?.items) return [];

    let filtered = inventory.items.filter(item => {
      // Filter by type
      if (filterMode !== 'all' && item.virtualGood.type !== filterMode) return false;
      
      // Filter by search query
      if (searchQuery && !item.virtualGood.name.toLowerCase().includes(searchQuery.toLowerCase()) &&
          !item.virtualGood.description.toLowerCase().includes(searchQuery.toLowerCase())) {
        return false;
      }
      
      return true;
    });

    // Sort items
    filtered.sort((a, b) => {
      switch (sortMode) {
        case 'newest':
          return new Date(b.acquiredAt).getTime() - new Date(a.acquiredAt).getTime();
        case 'oldest':
          return new Date(a.acquiredAt).getTime() - new Date(b.acquiredAt).getTime();
        case 'name':
          return a.virtualGood.name.localeCompare(b.virtualGood.name);
        case 'quantity':
          return b.quantity - a.quantity;
        case 'rarity':
          const rarityOrder = { common: 0, uncommon: 1, rare: 2, epic: 3, legendary: 4 };
          return rarityOrder[b.virtualGood.rarity] - rarityOrder[a.virtualGood.rarity];
        default:
          return 0;
      }
    });

    return filtered;
  }, [inventory?.items, filterMode, searchQuery, sortMode]);

  const handleUseItem = (item: InventoryItem) => {
    useItem({
      inventoryItemId: item.id,
      quantity: 1,
    });
  };

  const handleGiftItem = (item: InventoryItem) => {
    // In a real app, this would open a gift modal
    console.log('Gift item:', item);
  };

  const toggleItemSelection = (itemId: string) => {
    setSelectedItems(prev => 
      prev.includes(itemId) 
        ? prev.filter(id => id !== itemId)
        : [...prev, itemId]
    );
  };

  const InventoryItemCard: React.FC<{ item: InventoryItem; compact?: boolean }> = ({ 
    item, 
    compact = false 
  }) => {
    const isSelected = selectedItems.includes(item.id);
    const expired = isExpired(item);
    const expiringSoon = isExpiringSoon(item);

    if (compact) {
      return (
        <motion.div
          whileHover={{ scale: 1.02 }}
          whileTap={{ scale: 0.98 }}
          className={`cursor-pointer ${isSelected ? 'ring-2 ring-purple-500' : ''}`}
          onClick={() => toggleItemSelection(item.id)}
        >
          <Card className={`${expired ? 'opacity-50' : ''} ${expiringSoon ? 'ring-2 ring-orange-400' : ''}`}>
            <CardContent className="p-4">
              <div className="flex items-center gap-4">
                <div className="relative w-16 h-16 flex-shrink-0">
                  <img
                    src={item.virtualGood.image}
                    alt={item.virtualGood.name}
                    className="w-full h-full object-cover rounded-lg"
                  />
                  <div className="absolute -top-1 -right-1 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">
                    {item.quantity}
                  </div>
                </div>

                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-1">
                    <h3 className="font-medium text-gray-900 dark:text-white truncate">
                      {item.virtualGood.name}
                    </h3>
                    <div className={`px-2 py-1 rounded-full text-xs font-medium ${getRarityColor(item.virtualGood.rarity)}`}>
                      {item.virtualGood.rarity}
                    </div>
                  </div>
                  
                  <p className="text-sm text-gray-600 dark:text-gray-400 mb-2 truncate">
                    {item.virtualGood.description}
                  </p>

                  <div className="flex items-center gap-4 text-xs text-gray-500">
                    <span>Acquired: {new Date(item.acquiredAt).toLocaleDateString()}</span>
                    {item.expiresAt && (
                      <span className={expiringSoon ? 'text-orange-600' : ''}>
                        Expires: {new Date(item.expiresAt).toLocaleDateString()}
                      </span>
                    )}
                  </div>
                </div>

                <div className="flex items-center gap-2">
                  {!expired && item.virtualGood.type !== 'collectible' && (
                    <Button
                      onClick={(e) => {
                        e.stopPropagation();
                        handleUseItem(item);
                      }}
                      loading={isUsingItem}
                      variant="primary"
                      size="sm"
                      icon={<Play className="w-4 h-4" />}
                    >
                      Use
                    </Button>
                  )}
                  
                  <Button
                    onClick={(e) => {
                      e.stopPropagation();
                      handleGiftItem(item);
                    }}
                    loading={isGifting}
                    variant="outline"
                    size="sm"
                    icon={<Gift className="w-4 h-4" />}
                  >
                    Gift
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </motion.div>
      );
    }

    return (
      <motion.div
        whileHover={{ y: -2 }}
        whileTap={{ scale: 0.98 }}
        className={`cursor-pointer ${isSelected ? 'ring-2 ring-purple-500' : ''}`}
        onClick={() => toggleItemSelection(item.id)}
      >
        <Card className={`overflow-hidden ${expired ? 'opacity-50' : ''} ${expiringSoon ? 'ring-2 ring-orange-400' : ''}`}>
          <div className="aspect-square relative">
            <img
              src={item.virtualGood.image}
              alt={item.virtualGood.name}
              className="w-full h-full object-cover"
            />
            
            {/* Quantity badge */}
            <div className="absolute top-2 right-2 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
              {item.quantity}
            </div>

            {/* Status badges */}
            <div className="absolute top-2 left-2 flex flex-col gap-1">
              <div className={`px-2 py-1 rounded-full text-xs font-medium ${getRarityColor(item.virtualGood.rarity)}`}>
                {item.virtualGood.rarity}
              </div>
              {expiringSoon && (
                <div className="px-2 py-1 bg-orange-500 text-white rounded-full text-xs font-medium flex items-center gap-1">
                  <Clock className="w-3 h-3" />
                  Soon
                </div>
              )}
              {expired && (
                <div className="px-2 py-1 bg-red-500 text-white rounded-full text-xs font-medium">
                  Expired
                </div>
              )}
            </div>
          </div>

          <CardContent className="p-4">
            <div className="mb-3">
              <h3 className="font-medium text-gray-900 dark:text-white mb-1">
                {item.virtualGood.name}
              </h3>
              <p className="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                {item.virtualGood.description}
              </p>
            </div>

            {/* Effects */}
            {item.virtualGood.effects && item.virtualGood.effects.length > 0 && (
              <div className="mb-3">
                <div className="flex flex-wrap gap-1">
                  {item.virtualGood.effects.slice(0, 2).map((effect, index) => (
                    <div
                      key={index}
                      className="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full text-xs flex items-center gap-1"
                    >
                      <Zap className="w-3 h-3" />
                      +{effect.value}{effect.percentage ? '%' : ''} {effect.type.replace('_', ' ')}
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Timestamps */}
            <div className="text-xs text-gray-500 mb-3">
              <div className="flex items-center gap-1 mb-1">
                <Calendar className="w-3 h-3" />
                <span>Acquired: {new Date(item.acquiredAt).toLocaleDateString()}</span>
              </div>
              {item.expiresAt && (
                <div className={`flex items-center gap-1 ${expiringSoon ? 'text-orange-600' : ''}`}>
                  <Clock className="w-3 h-3" />
                  <span>Expires: {new Date(item.expiresAt).toLocaleDateString()}</span>
                </div>
              )}
            </div>

            {/* Actions */}
            <div className="flex items-center gap-2">
              {!expired && item.virtualGood.type !== 'collectible' && (
                <Button
                  onClick={(e) => {
                    e.stopPropagation();
                    handleUseItem(item);
                  }}
                  loading={isUsingItem}
                  variant="primary"
                  size="sm"
                  icon={<Play className="w-4 h-4" />}
                  fullWidth
                >
                  Use
                </Button>
              )}
              
              <Button
                onClick={(e) => {
                  e.stopPropagation();
                  handleGiftItem(item);
                }}
                loading={isGifting}
                variant="outline"
                size="sm"
                icon={<Gift className="w-4 h-4" />}
                fullWidth={expired || item.virtualGood.type === 'collectible'}
              >
                Gift
              </Button>
            </div>
          </CardContent>
        </Card>
      </motion.div>
    );
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-48 animate-pulse"></div>
          <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-32 animate-pulse"></div>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {[...Array(8)].map((_, i) => (
            <div key={i} className="animate-pulse">
              <div className="bg-gray-200 dark:bg-gray-700 rounded-xl h-80"></div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  if (!inventory?.items || inventory.items.length === 0) {
    return (
      <div className="text-center py-12">
        <Package className="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
          Empty Inventory
        </h3>
        <p className="text-gray-600 dark:text-gray-400">
          You haven't purchased any items yet. Visit the store to get started!
        </p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            Your Inventory
          </h2>
          <p className="text-gray-600 dark:text-gray-400">
            {inventory.items.length} / {inventory.capacity} items
          </p>
        </div>

        <div className="flex items-center gap-2">
          {selectedItems.length > 0 && (
            <Button
              variant="outline"
              size="sm"
              icon={<Trash2 className="w-4 h-4" />}
              onClick={() => setSelectedItems([])}
            >
              Clear ({selectedItems.length})
            </Button>
          )}
        </div>
      </div>

      {/* Controls */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div className="flex-1 relative">
          <Input
            placeholder="Search inventory..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            leftIcon={<Search className="w-4 h-4" />}
          />
        </div>

        <div className="flex items-center gap-2">
          <select
            value={filterMode}
            onChange={(e) => setFilterMode(e.target.value as FilterMode)}
            className="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
          >
            <option value="all">All Types</option>
            <option value="consumable">Consumables</option>
            <option value="equipment">Equipment</option>
            <option value="cosmetic">Cosmetics</option>
            <option value="boost">Boosts</option>
            <option value="collectible">Collectibles</option>
          </select>

          <select
            value={sortMode}
            onChange={(e) => setSortMode(e.target.value as SortMode)}
            className="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
          >
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="name">Name</option>
            <option value="quantity">Quantity</option>
            <option value="rarity">Rarity</option>
          </select>

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
      </div>

      {/* Items Grid/List */}
      <div className={
        viewMode === 'grid' 
          ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6'
          : 'space-y-4'
      }>
        <AnimatePresence>
          {filteredAndSortedItems.map((item) => (
            <InventoryItemCard
              key={item.id}
              item={item}
              compact={viewMode === 'list'}
            />
          ))}
        </AnimatePresence>
      </div>

      {filteredAndSortedItems.length === 0 && (
        <div className="text-center py-12">
          <Filter className="w-16 h-16 text-gray-400 mx-auto mb-4" />
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
};
import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Filter, ChevronDown, ChevronUp } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { StoreFilters as IStoreFilters, StoreSortOptions, VirtualGoodCategory } from '@/types/virtualGoods';

interface StoreFiltersProps {
  filters: Partial<IStoreFilters>;
  sortOptions: Partial<StoreSortOptions>;
  onFiltersChange: (filters: Partial<IStoreFilters>) => void;
  onSortChange: (sortOptions: Partial<StoreSortOptions>) => void;
  onClose: () => void;
}

const categories: { value: VirtualGoodCategory; label: string }[] = [
  { value: 'food', label: 'Food & Treats' },
  { value: 'toys', label: 'Toys' },
  { value: 'accessories', label: 'Accessories' },
  { value: 'environments', label: 'Environments' },
  { value: 'boosters', label: 'Boosters' },
  { value: 'currencies', label: 'Currency' },
  { value: 'cosmetics', label: 'Cosmetics' },
  { value: 'special', label: 'Special Items' },
];

const rarities = [
  { value: 'common', label: 'Common', color: 'text-gray-600' },
  { value: 'uncommon', label: 'Uncommon', color: 'text-green-600' },
  { value: 'rare', label: 'Rare', color: 'text-blue-600' },
  { value: 'epic', label: 'Epic', color: 'text-purple-600' },
  { value: 'legendary', label: 'Legendary', color: 'text-yellow-600' },
];

const itemTypes = [
  { value: 'consumable', label: 'Consumable' },
  { value: 'equipment', label: 'Equipment' },
  { value: 'cosmetic', label: 'Cosmetic' },
  { value: 'boost', label: 'Boost' },
  { value: 'collectible', label: 'Collectible' },
];

const currencies = [
  { value: 'all', label: 'All Currencies' },
  { value: 'coins', label: 'Coins' },
  { value: 'premium_coins', label: 'Premium Coins' },
  { value: 'real_money', label: 'Real Money' },
];

const availabilityOptions = [
  { value: 'all', label: 'All Items' },
  { value: 'available', label: 'Available' },
  { value: 'limited_time', label: 'Limited Time' },
  { value: 'limited_quantity', label: 'Limited Quantity' },
];

const sortOptions = [
  { value: 'newest', label: 'Newest First' },
  { value: 'price_low', label: 'Price: Low to High' },
  { value: 'price_high', label: 'Price: High to Low' },
  { value: 'popular', label: 'Most Popular' },
  { value: 'rarity', label: 'Rarity' },
  { value: 'name', label: 'Name' },
];

export const StoreFilters: React.FC<StoreFiltersProps> = ({
  filters,
  sortOptions: currentSort,
  onFiltersChange,
  onSortChange,
  onClose,
}) => {
  const [expandedSections, setExpandedSections] = useState({
    category: true,
    price: true,
    rarity: false,
    type: false,
    availability: false,
    sort: true,
  });

  const toggleSection = (section: keyof typeof expandedSections) => {
    setExpandedSections(prev => ({
      ...prev,
      [section]: !prev[section],
    }));
  };

  const handlePriceRangeChange = (index: 0 | 1, value: string) => {
    const numValue = parseInt(value) || 0;
    const newRange: [number, number] = [...(filters.priceRange || [0, 1000])];
    newRange[index] = numValue;
    
    onFiltersChange({
      ...filters,
      priceRange: newRange,
    });
  };

  const resetFilters = () => {
    onFiltersChange({
      priceRange: [0, 1000],
      currency: 'all',
      availability: 'all',
      inStock: true,
    });
    onSortChange({
      sortBy: 'newest',
      sortDirection: 'desc',
    });
  };

  const FilterSection = ({ 
    title, 
    sectionKey, 
    children 
  }: { 
    title: string; 
    sectionKey: keyof typeof expandedSections; 
    children: React.ReactNode; 
  }) => (
    <div className="border-b border-gray-200 dark:border-gray-700 pb-4">
      <button
        onClick={() => toggleSection(sectionKey)}
        className="flex items-center justify-between w-full text-left mb-3"
      >
        <h3 className="font-medium text-gray-900 dark:text-white">{title}</h3>
        {expandedSections[sectionKey] ? (
          <ChevronUp className="w-4 h-4 text-gray-500" />
        ) : (
          <ChevronDown className="w-4 h-4 text-gray-500" />
        )}
      </button>
      
      <AnimatePresence>
        {expandedSections[sectionKey] && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: 'auto', opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.2 }}
            className="overflow-hidden"
          >
            {children}
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );

  return (
    <motion.div
      initial={{ opacity: 0, y: -10 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -10 }}
      className="mb-6"
    >
      <Card>
        <CardContent className="p-6">
          {/* Header */}
          <div className="flex items-center justify-between mb-6">
            <div className="flex items-center gap-2">
              <Filter className="w-5 h-5 text-gray-600 dark:text-gray-400" />
              <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                Filters & Sort
              </h2>
            </div>
            
            <div className="flex items-center gap-2">
              <Button
                onClick={resetFilters}
                variant="outline"
                size="sm"
              >
                Reset
              </Button>
              <button
                onClick={onClose}
                className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              >
                <X className="w-4 h-4 text-gray-500" />
              </button>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Categories */}
            <FilterSection title="Category" sectionKey="category">
              <div className="space-y-2">
                <label className="flex items-center">
                  <input
                    type="radio"
                    name="category"
                    checked={!filters.category}
                    onChange={() => onFiltersChange({ ...filters, category: undefined })}
                    className="mr-2"
                  />
                  <span className="text-sm text-gray-700 dark:text-gray-300">All Categories</span>
                </label>
                {categories.map((category) => (
                  <label key={category.value} className="flex items-center">
                    <input
                      type="radio"
                      name="category"
                      checked={filters.category === category.value}
                      onChange={() => onFiltersChange({ ...filters, category: category.value })}
                      className="mr-2"
                    />
                    <span className="text-sm text-gray-700 dark:text-gray-300">{category.label}</span>
                  </label>
                ))}
              </div>
            </FilterSection>

            {/* Price Range */}
            <FilterSection title="Price Range" sectionKey="price">
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Currency Type
                  </label>
                  <select
                    value={filters.currency || 'all'}
                    onChange={(e) => onFiltersChange({ 
                      ...filters, 
                      currency: e.target.value as any 
                    })}
                    className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
                  >
                    {currencies.map((currency) => (
                      <option key={currency.value} value={currency.value}>
                        {currency.label}
                      </option>
                    ))}
                  </select>
                </div>
                
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Min
                    </label>
                    <Input
                      type="number"
                      value={filters.priceRange?.[0] || 0}
                      onChange={(e) => handlePriceRangeChange(0, e.target.value)}
                      placeholder="0"
                      size="sm"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Max
                    </label>
                    <Input
                      type="number"
                      value={filters.priceRange?.[1] || 1000}
                      onChange={(e) => handlePriceRangeChange(1, e.target.value)}
                      placeholder="1000"
                      size="sm"
                    />
                  </div>
                </div>
              </div>
            </FilterSection>

            {/* Sort Options */}
            <FilterSection title="Sort By" sectionKey="sort">
              <div className="space-y-3">
                <div>
                  <select
                    value={currentSort.sortBy || 'newest'}
                    onChange={(e) => onSortChange({ 
                      ...currentSort, 
                      sortBy: e.target.value as any 
                    })}
                    className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
                  >
                    {sortOptions.map((option) => (
                      <option key={option.value} value={option.value}>
                        {option.label}
                      </option>
                    ))}
                  </select>
                </div>
                
                <div className="flex items-center gap-4">
                  <label className="flex items-center">
                    <input
                      type="radio"
                      name="sortDirection"
                      checked={currentSort.sortDirection === 'desc'}
                      onChange={() => onSortChange({ ...currentSort, sortDirection: 'desc' })}
                      className="mr-2"
                    />
                    <span className="text-sm text-gray-700 dark:text-gray-300">Descending</span>
                  </label>
                  <label className="flex items-center">
                    <input
                      type="radio"
                      name="sortDirection"
                      checked={currentSort.sortDirection === 'asc'}
                      onChange={() => onSortChange({ ...currentSort, sortDirection: 'asc' })}
                      className="mr-2"
                    />
                    <span className="text-sm text-gray-700 dark:text-gray-300">Ascending</span>
                  </label>
                </div>
              </div>
            </FilterSection>
          </div>

          {/* Additional Filters Row */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            {/* Rarity */}
            <FilterSection title="Rarity" sectionKey="rarity">
              <div className="space-y-2">
                <label className="flex items-center">
                  <input
                    type="radio"
                    name="rarity"
                    checked={!filters.rarity}
                    onChange={() => onFiltersChange({ ...filters, rarity: undefined })}
                    className="mr-2"
                  />
                  <span className="text-sm text-gray-700 dark:text-gray-300">All Rarities</span>
                </label>
                {rarities.map((rarity) => (
                  <label key={rarity.value} className="flex items-center">
                    <input
                      type="radio"
                      name="rarity"
                      checked={filters.rarity === rarity.value}
                      onChange={() => onFiltersChange({ ...filters, rarity: rarity.value })}
                      className="mr-2"
                    />
                    <span className={`text-sm ${rarity.color}`}>{rarity.label}</span>
                  </label>
                ))}
              </div>
            </FilterSection>

            {/* Item Type */}
            <FilterSection title="Item Type" sectionKey="type">
              <div className="space-y-2">
                <label className="flex items-center">
                  <input
                    type="radio"
                    name="type"
                    checked={!filters.type}
                    onChange={() => onFiltersChange({ ...filters, type: undefined })}
                    className="mr-2"
                  />
                  <span className="text-sm text-gray-700 dark:text-gray-300">All Types</span>
                </label>
                {itemTypes.map((type) => (
                  <label key={type.value} className="flex items-center">
                    <input
                      type="radio"
                      name="type"
                      checked={filters.type === type.value}
                      onChange={() => onFiltersChange({ ...filters, type: type.value })}
                      className="mr-2"
                    />
                    <span className="text-sm text-gray-700 dark:text-gray-300">{type.label}</span>
                  </label>
                ))}
              </div>
            </FilterSection>

            {/* Availability */}
            <FilterSection title="Availability" sectionKey="availability">
              <div className="space-y-2">
                {availabilityOptions.map((option) => (
                  <label key={option.value} className="flex items-center">
                    <input
                      type="radio"
                      name="availability"
                      checked={filters.availability === option.value}
                      onChange={() => onFiltersChange({ ...filters, availability: option.value })}
                      className="mr-2"
                    />
                    <span className="text-sm text-gray-700 dark:text-gray-300">{option.label}</span>
                  </label>
                ))}
                
                <label className="flex items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                  <input
                    type="checkbox"
                    checked={filters.inStock || false}
                    onChange={(e) => onFiltersChange({ ...filters, inStock: e.target.checked })}
                    className="mr-2"
                  />
                  <span className="text-sm text-gray-700 dark:text-gray-300">In Stock Only</span>
                </label>
              </div>
            </FilterSection>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
};
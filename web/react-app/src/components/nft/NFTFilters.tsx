import React from 'react';
import { motion } from 'framer-motion';
import { X, RotateCcw } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';

interface NFTFiltersProps {
  filters: {
    breed: string;
    rarity: string;
    priceRange: [number, number];
    generation: string;
    sortBy: string;
  };
  onFiltersChange: (filters: NFTFiltersProps['filters']) => void;
  onClose: () => void;
}

const BREEDS = [
  'Persian', 'Siamese', 'Maine Coon', 'British Shorthair', 'Ragdoll', 
  'Abyssinian', 'Russian Blue', 'Scottish Fold', 'Sphynx', 'Bengal'
];

const RARITIES = [
  { value: 'common', label: 'Common', color: 'text-gray-600' },
  { value: 'uncommon', label: 'Uncommon', color: 'text-green-600' },
  { value: 'rare', label: 'Rare', color: 'text-blue-600' },
  { value: 'epic', label: 'Epic', color: 'text-purple-600' },
  { value: 'legendary', label: 'Legendary', color: 'text-yellow-600' },
];

const GENERATIONS = ['1', '2', '3', '4', '5+'];

const SORT_OPTIONS = [
  { value: 'newest', label: 'Newest First' },
  { value: 'oldest', label: 'Oldest First' },
  { value: 'price_low', label: 'Price: Low to High' },
  { value: 'price_high', label: 'Price: High to Low' },
  { value: 'rarity', label: 'Rarity' },
  { value: 'level', label: 'Level' },
];

export const NFTFilters: React.FC<NFTFiltersProps> = ({
  filters,
  onFiltersChange,
  onClose,
}) => {
  const updateFilter = (key: keyof typeof filters, value: any) => {
    onFiltersChange({ ...filters, [key]: value });
  };

  const resetFilters = () => {
    onFiltersChange({
      breed: '',
      rarity: '',
      priceRange: [0, 1000],
      generation: '',
      sortBy: 'newest',
    });
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: -20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -20 }}
      transition={{ duration: 0.2 }}
    >
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle>Filters</CardTitle>
            <div className="flex items-center gap-2">
              <Button
                onClick={resetFilters}
                variant="ghost"
                size="sm"
                icon={<RotateCcw className="w-4 h-4" />}
              >
                Reset
              </Button>
              <Button
                onClick={onClose}
                variant="ghost"
                size="sm"
                icon={<X className="w-4 h-4" />}
              />
            </div>
          </div>
        </CardHeader>

        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {/* Sort */}
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Sort By
              </label>
              <select
                value={filters.sortBy}
                onChange={(e) => updateFilter('sortBy', e.target.value)}
                className="w-full p-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              >
                {SORT_OPTIONS.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
            </div>

            {/* Breed */}
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Breed
              </label>
              <select
                value={filters.breed}
                onChange={(e) => updateFilter('breed', e.target.value)}
                className="w-full p-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
              >
                <option value="">All Breeds</option>
                {BREEDS.map((breed) => (
                  <option key={breed} value={breed}>
                    {breed}
                  </option>
                ))}
              </select>
            </div>

            {/* Rarity */}
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Rarity
              </label>
              <div className="space-y-2">
                {RARITIES.map((rarity) => (
                  <label key={rarity.value} className="flex items-center">
                    <input
                      type="radio"
                      name="rarity"
                      value={rarity.value}
                      checked={filters.rarity === rarity.value}
                      onChange={(e) => updateFilter('rarity', e.target.value)}
                      className="mr-2"
                    />
                    <span className={`text-sm ${rarity.color}`}>
                      {rarity.label}
                    </span>
                  </label>
                ))}
                <label className="flex items-center">
                  <input
                    type="radio"
                    name="rarity"
                    value=""
                    checked={filters.rarity === ''}
                    onChange={(e) => updateFilter('rarity', e.target.value)}
                    className="mr-2"
                  />
                  <span className="text-sm text-gray-600 dark:text-gray-400">
                    All Rarities
                  </span>
                </label>
              </div>
            </div>

            {/* Generation */}
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Generation
              </label>
              <div className="flex flex-wrap gap-2">
                <button
                  onClick={() => updateFilter('generation', '')}
                  className={`px-3 py-1 text-sm rounded-full transition-colors ${
                    filters.generation === ''
                      ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300'
                      : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
                  }`}
                >
                  All
                </button>
                {GENERATIONS.map((gen) => (
                  <button
                    key={gen}
                    onClick={() => updateFilter('generation', gen)}
                    className={`px-3 py-1 text-sm rounded-full transition-colors ${
                      filters.generation === gen
                        ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300'
                        : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
                    }`}
                  >
                    Gen {gen}
                  </button>
                ))}
              </div>
            </div>

            {/* Price Range */}
            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Price Range (ETH)
              </label>
              <div className="flex items-center gap-4">
                <input
                  type="number"
                  placeholder="Min"
                  value={filters.priceRange[0]}
                  onChange={(e) => updateFilter('priceRange', [Number(e.target.value), filters.priceRange[1]])}
                  className="flex-1 p-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                />
                <span className="text-gray-500">to</span>
                <input
                  type="number"
                  placeholder="Max"
                  value={filters.priceRange[1]}
                  onChange={(e) => updateFilter('priceRange', [filters.priceRange[0], Number(e.target.value)])}
                  className="flex-1 p-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                />
              </div>
              
              {/* Price range slider */}
              <div className="mt-3">
                <input
                  type="range"
                  min="0"
                  max="100"
                  value={filters.priceRange[1]}
                  onChange={(e) => updateFilter('priceRange', [filters.priceRange[0], Number(e.target.value)])}
                  className="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer slider"
                />
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
};
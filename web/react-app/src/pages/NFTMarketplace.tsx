import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Search, Filter, Grid, List, Wallet } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { WalletConnection } from '@/components/web3/WalletConnection';
import { NFTCard } from '@/components/nft/NFTCard';
import { NFTFilters } from '@/components/nft/NFTFilters';
import { useNFTMarketplace } from '@/hooks/useNFTMarketplace';
import { useWallet } from '@/hooks/useWallet';

type ViewMode = 'grid' | 'list';
type TabType = 'marketplace' | 'my-nfts' | 'activity';

const tabs = [
  { id: 'marketplace', label: 'Marketplace', description: 'Buy and sell cat NFTs' },
  { id: 'my-nfts', label: 'My Collection', description: 'View and manage your NFTs' },
  { id: 'activity', label: 'Activity', description: 'Recent transactions and events' },
];

export const NFTMarketplace: React.FC = () => {
  const [activeTab, setActiveTab] = useState<TabType>('marketplace');
  const [viewMode, setViewMode] = useState<ViewMode>('grid');
  const [searchQuery, setSearchQuery] = useState('');
  const [showFilters, setShowFilters] = useState(false);
  const [filters, setFilters] = useState({
    breed: '',
    rarity: '',
    priceRange: [0, 1000],
    generation: '',
    sortBy: 'newest',
  });

  const { isConnected, canInteract } = useWallet();
  const {
    marketplaceListings,
    userNFTs,
    isLoadingMarketplace,
    isLoadingUserNFTs,
    formatPrice,
    getRarityColor,
  } = useNFTMarketplace();

  const handleSearch = (query: string) => {
    setSearchQuery(query);
    // Implement search logic
  };

  const handleFilterChange = (newFilters: typeof filters) => {
    setFilters(newFilters);
    // Implement filter logic
  };

  const filteredListings = marketplaceListings.filter(listing => {
    if (searchQuery && !listing.nft.name.toLowerCase().includes(searchQuery.toLowerCase())) {
      return false;
    }
    if (filters.breed && listing.nft.breed !== filters.breed) {
      return false;
    }
    if (filters.rarity && listing.nft.rarity !== filters.rarity) {
      return false;
    }
    return true;
  });

  const filteredUserNFTs = userNFTs.filter(nft => {
    if (searchQuery && !nft.name.toLowerCase().includes(searchQuery.toLowerCase())) {
      return false;
    }
    return true;
  });

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
                NFT Marketplace
              </h1>
              <p className="text-gray-600 dark:text-gray-400">
                Discover, collect, and trade unique cat NFTs
              </p>
            </div>
            
            <WalletConnection compact />
          </div>

          {/* Wallet Connection Banner */}
          {!isConnected && (
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              className="mb-6"
            >
              <Card className="border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20">
                <CardContent className="p-6">
                  <div className="flex items-center gap-4">
                    <Wallet className="w-8 h-8 text-purple-600 dark:text-purple-400" />
                    <div className="flex-1">
                      <h3 className="font-semibold text-purple-900 dark:text-purple-100 mb-1">
                        Connect Your Wallet
                      </h3>
                      <p className="text-purple-700 dark:text-purple-300 text-sm">
                        Connect your Web3 wallet to start buying, selling, and trading cat NFTs on the blockchain.
                      </p>
                    </div>
                    <WalletConnection compact />
                  </div>
                </CardContent>
              </Card>
            </motion.div>
          )}
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
                className={`flex-1 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 ${
                  activeTab === tab.id
                    ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300'
                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700'
                }`}
              >
                <div className="text-center">
                  <div>{tab.label}</div>
                  <div className="text-xs opacity-75 mt-1">{tab.description}</div>
                </div>
              </button>
            ))}
          </nav>
        </motion.div>

        {/* Search and Filters */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
          className="mb-6"
        >
          <div className="flex items-center gap-4 mb-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <Input
                placeholder="Search NFTs by name, breed, or traits..."
                value={searchQuery}
                onChange={(e) => handleSearch(e.target.value)}
                className="pl-10"
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
            <motion.div
              initial={{ opacity: 0, height: 0 }}
              animate={{ opacity: 1, height: 'auto' }}
              exit={{ opacity: 0, height: 0 }}
              transition={{ duration: 0.3 }}
            >
              <NFTFilters
                filters={filters}
                onFiltersChange={handleFilterChange}
                onClose={() => setShowFilters(false)}
              />
            </motion.div>
          )}
        </motion.div>

        {/* Content */}
        <motion.div
          key={activeTab}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.3 }}
        >
          {activeTab === 'marketplace' && (
            <div>
              {/* Marketplace Stats */}
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <Card>
                  <CardContent className="p-4 text-center">
                    <div className="text-2xl font-bold text-gray-900 dark:text-white">
                      {marketplaceListings.length}
                    </div>
                    <div className="text-sm text-gray-600 dark:text-gray-400">Active Listings</div>
                  </CardContent>
                </Card>
                <Card>
                  <CardContent className="p-4 text-center">
                    <div className="text-2xl font-bold text-gray-900 dark:text-white">
                      2.5 ETH
                    </div>
                    <div className="text-sm text-gray-600 dark:text-gray-400">Floor Price</div>
                  </CardContent>
                </Card>
                <Card>
                  <CardContent className="p-4 text-center">
                    <div className="text-2xl font-bold text-gray-900 dark:text-white">
                      156.8 ETH
                    </div>
                    <div className="text-sm text-gray-600 dark:text-gray-400">Total Volume</div>
                  </CardContent>
                </Card>
                <Card>
                  <CardContent className="p-4 text-center">
                    <div className="text-2xl font-bold text-green-600">
                      +12.5%
                    </div>
                    <div className="text-sm text-gray-600 dark:text-gray-400">24h Change</div>
                  </CardContent>
                </Card>
              </div>

              {/* NFT Grid */}
              {isLoadingMarketplace ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                  {[...Array(8)].map((_, i) => (
                    <div key={i} className="animate-pulse">
                      <div className="bg-gray-200 dark:bg-gray-700 rounded-xl h-80"></div>
                    </div>
                  ))}
                </div>
              ) : filteredListings.length > 0 ? (
                <div className={
                  viewMode === 'grid' 
                    ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6'
                    : 'space-y-4'
                }>
                  {filteredListings.map((listing) => (
                    <NFTCard
                      key={`${listing.contractAddress}-${listing.tokenId}`}
                      nft={listing.nft}
                      listing={listing}
                      viewMode={viewMode}
                    />
                  ))}
                </div>
              ) : (
                <div className="text-center py-12">
                  <div className="text-6xl mb-4">🐱</div>
                  <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    No NFTs Found
                  </h3>
                  <p className="text-gray-600 dark:text-gray-400">
                    Try adjusting your search or filter criteria.
                  </p>
                </div>
              )}
            </div>
          )}

          {activeTab === 'my-nfts' && (
            <div>
              {!canInteract ? (
                <div className="text-center py-12">
                  <Wallet className="w-16 h-16 text-gray-400 mx-auto mb-4" />
                  <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    Connect Your Wallet
                  </h3>
                  <p className="text-gray-600 dark:text-gray-400 mb-6">
                    Connect your wallet and switch to a supported network to view your NFT collection.
                  </p>
                  <WalletConnection />
                </div>
              ) : isLoadingUserNFTs ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                  {[...Array(4)].map((_, i) => (
                    <div key={i} className="animate-pulse">
                      <div className="bg-gray-200 dark:bg-gray-700 rounded-xl h-80"></div>
                    </div>
                  ))}
                </div>
              ) : filteredUserNFTs.length > 0 ? (
                <div className={
                  viewMode === 'grid' 
                    ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6'
                    : 'space-y-4'
                }>
                  {filteredUserNFTs.map((nft) => (
                    <NFTCard
                      key={`${nft.contractAddress}-${nft.tokenId}`}
                      nft={nft}
                      viewMode={viewMode}
                      showOwnerActions
                    />
                  ))}
                </div>
              ) : (
                <div className="text-center py-12">
                  <div className="text-6xl mb-4">🐾</div>
                  <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    No NFTs in Your Collection
                  </h3>
                  <p className="text-gray-600 dark:text-gray-400 mb-6">
                    Start collecting unique cat NFTs from the marketplace.
                  </p>
                  <Button
                    onClick={() => setActiveTab('marketplace')}
                    variant="primary"
                  >
                    Browse Marketplace
                  </Button>
                </div>
              )}
            </div>
          )}

          {activeTab === 'activity' && (
            <div className="text-center py-12">
              <div className="text-6xl mb-4">📈</div>
              <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Activity Feed Coming Soon
              </h3>
              <p className="text-gray-600 dark:text-gray-400">
                Track recent transactions, sales, and marketplace activity.
              </p>
            </div>
          )}
        </motion.div>
      </div>
    </div>
  );
};
import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Heart, Share, MoreHorizontal, ExternalLink, ShoppingCart, Tag, Zap } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { CatNFT, NFTMarketplaceListing } from '@/types/nft';
import { useNFTMarketplace } from '@/hooks/useNFTMarketplace';
import { useWallet } from '@/hooks/useWallet';

interface NFTCardProps {
  nft: CatNFT;
  listing?: NFTMarketplaceListing;
  viewMode?: 'grid' | 'list';
  showOwnerActions?: boolean;
  onSelect?: (nft: CatNFT) => void;
}

export const NFTCard: React.FC<NFTCardProps> = ({
  nft,
  listing,
  viewMode = 'grid',
  showOwnerActions = false,
  onSelect,
}) => {
  const [isLiked, setIsLiked] = useState(false);
  const [showDetails, setShowDetails] = useState(false);
  
  const { address } = useWallet();
  const {
    purchaseNFT,
    isPurchasing,
    formatPrice,
    getRarityColor,
  } = useNFTMarketplace();

  const isOwner = address && nft.owner.toLowerCase() === address.toLowerCase();
  const isListed = listing && listing.status === 'active';

  const handlePurchase = () => {
    if (listing) {
      purchaseNFT(listing.id);
    }
  };

  const handleShare = () => {
    if (navigator.share) {
      navigator.share({
        title: nft.name,
        text: nft.description,
        url: window.location.href,
      });
    } else {
      navigator.clipboard.writeText(window.location.href);
    }
  };

  const handleLike = () => {
    setIsLiked(!isLiked);
    // TODO: Implement like functionality
  };

  if (viewMode === 'list') {
    return (
      <motion.div
        whileHover={{ scale: 1.02 }}
        whileTap={{ scale: 0.98 }}
        className="cursor-pointer"
        onClick={() => onSelect?.(nft)}
      >
        <Card className="hover:shadow-lg transition-shadow duration-200">
          <CardContent className="p-4">
            <div className="flex items-center gap-4">
              {/* Image */}
              <div className="relative w-20 h-20 flex-shrink-0">
                <img
                  src={nft.image}
                  alt={nft.name}
                  className="w-full h-full object-cover rounded-lg"
                />
                <div className={`absolute top-1 right-1 px-2 py-1 rounded-full text-xs font-medium ${getRarityColor(nft.rarity)}`}>
                  {nft.rarity}
                </div>
              </div>

              {/* Info */}
              <div className="flex-1 min-w-0">
                <div className="flex items-center justify-between">
                  <h3 className="font-semibold text-gray-900 dark:text-white truncate">
                    {nft.name}
                  </h3>
                  <div className="text-sm text-gray-500">
                    Gen {nft.generation}
                  </div>
                </div>
                
                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                  {nft.breed} • Level {nft.stats.level}
                </p>

                <div className="flex items-center gap-4">
                  {listing && (
                    <div className="text-lg font-bold text-gray-900 dark:text-white">
                      {formatPrice(listing.price, listing.currency)}
                    </div>
                  )}
                  
                  <div className="flex items-center gap-2">
                    <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span className="text-sm text-gray-600 dark:text-gray-400">
                      {nft.stats.health}% Health
                    </span>
                  </div>
                </div>
              </div>

              {/* Actions */}
              <div className="flex items-center gap-2">
                {listing && !isOwner && (
                  <Button
                    onClick={(e) => {
                      e.stopPropagation();
                      handlePurchase();
                    }}
                    loading={isPurchasing}
                    variant="primary"
                    size="sm"
                    icon={<ShoppingCart className="w-4 h-4" />}
                  >
                    Buy
                  </Button>
                )}
                
                <Button
                  onClick={(e) => {
                    e.stopPropagation();
                    handleLike();
                  }}
                  variant="ghost"
                  size="sm"
                  icon={<Heart className={`w-4 h-4 ${isLiked ? 'fill-red-500 text-red-500' : ''}`} />}
                />
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
      onClick={() => onSelect?.(nft)}
    >
      <Card className="overflow-hidden hover:shadow-xl transition-all duration-300 group">
        <div className="relative">
          {/* NFT Image */}
          <div className="aspect-square relative overflow-hidden">
            <img
              src={nft.image}
              alt={nft.name}
              className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
            />
            
            {/* Overlay on hover */}
            <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity duration-300 flex items-center justify-center">
              <motion.div
                initial={{ opacity: 0, scale: 0.8 }}
                whileHover={{ opacity: 1, scale: 1 }}
                className="opacity-0 group-hover:opacity-100 transition-opacity duration-300"
              >
                <Button
                  variant="primary"
                  icon={<ExternalLink className="w-4 h-4" />}
                >
                  View Details
                </Button>
              </motion.div>
            </div>

            {/* Top badges */}
            <div className="absolute top-3 left-3 right-3 flex justify-between items-start">
              <div className={`px-2 py-1 rounded-full text-xs font-medium ${getRarityColor(nft.rarity)}`}>
                {nft.rarity}
              </div>
              <div className="flex gap-1">
                {isListed && (
                  <div className="px-2 py-1 bg-green-500 text-white rounded-full text-xs font-medium">
                    For Sale
                  </div>
                )}
                {nft.breeding.isBreedingEnabled && (
                  <div className="px-2 py-1 bg-purple-500 text-white rounded-full text-xs font-medium">
                    <Zap className="w-3 h-3" />
                  </div>
                )}
              </div>
            </div>

            {/* Action buttons */}
            <div className="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <div className="flex gap-1">
                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    handleLike();
                  }}
                  className="p-2 bg-white dark:bg-gray-800 rounded-full shadow-lg hover:scale-110 transition-transform"
                >
                  <Heart className={`w-4 h-4 ${isLiked ? 'fill-red-500 text-red-500' : 'text-gray-600'}`} />
                </button>
                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    handleShare();
                  }}
                  className="p-2 bg-white dark:bg-gray-800 rounded-full shadow-lg hover:scale-110 transition-transform"
                >
                  <Share className="w-4 h-4 text-gray-600" />
                </button>
              </div>
            </div>
          </div>

          {/* Stats bar */}
          <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-3">
            <div className="flex justify-between text-white text-xs">
              <div className="flex items-center gap-1">
                <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                <span>{nft.stats.health}%</span>
              </div>
              <div className="flex items-center gap-1">
                <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                <span>Lvl {nft.stats.level}</span>
              </div>
              <div className="flex items-center gap-1">
                <div className="w-2 h-2 bg-yellow-500 rounded-full"></div>
                <span>{nft.stats.happiness}%</span>
              </div>
            </div>
          </div>
        </div>

        <CardContent className="p-4">
          {/* Basic info */}
          <div className="mb-3">
            <div className="flex items-center justify-between mb-1">
              <h3 className="font-semibold text-gray-900 dark:text-white truncate">
                {nft.name}
              </h3>
              <span className="text-sm text-gray-500">
                Gen {nft.generation}
              </span>
            </div>
            <p className="text-sm text-gray-600 dark:text-gray-400">
              {nft.breed}
            </p>
          </div>

          {/* Traits preview */}
          <div className="mb-4">
            <div className="flex flex-wrap gap-1">
              {nft.attributes.slice(0, 3).map((attr, index) => (
                <span
                  key={index}
                  className="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-400 rounded-full"
                >
                  {attr.value}
                </span>
              ))}
              {nft.attributes.length > 3 && (
                <span className="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-400 rounded-full">
                  +{nft.attributes.length - 3}
                </span>
              )}
            </div>
          </div>

          {/* Price and actions */}
          <div className="flex items-center justify-between">
            {listing ? (
              <div>
                <div className="text-xs text-gray-500 dark:text-gray-400">Price</div>
                <div className="font-bold text-gray-900 dark:text-white">
                  {formatPrice(listing.price, listing.currency)}
                </div>
              </div>
            ) : (
              <div>
                <div className="text-xs text-gray-500 dark:text-gray-400">
                  {isOwner ? 'Owned' : 'Not for sale'}
                </div>
              </div>
            )}

            <div className="flex gap-2">
              {listing && !isOwner && (
                <Button
                  onClick={(e) => {
                    e.stopPropagation();
                    handlePurchase();
                  }}
                  loading={isPurchasing}
                  variant="primary"
                  size="sm"
                  icon={<ShoppingCart className="w-4 h-4" />}
                >
                  Buy
                </Button>
              )}
              
              {showOwnerActions && isOwner && (
                <Button
                  onClick={(e) => {
                    e.stopPropagation();
                    // TODO: Show owner actions menu
                  }}
                  variant="outline"
                  size="sm"
                  icon={<Tag className="w-4 h-4" />}
                >
                  List
                </Button>
              )}
            </div>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
};
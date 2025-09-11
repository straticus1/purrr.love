import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Heart, Zap, Clock, Info, AlertTriangle, CheckCircle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import { CatNFT, BreedingPair } from '@/types/nft';
import { useNFTMarketplace } from '@/hooks/useNFTMarketplace';
import { useWallet } from '@/hooks/useWallet';

interface BreedingLabProps {
  userNFTs: CatNFT[];
}

export const BreedingLab: React.FC<BreedingLabProps> = ({ userNFTs }) => {
  const [selectedParent1, setSelectedParent1] = useState<CatNFT | null>(null);
  const [selectedParent2, setSelectedParent2] = useState<CatNFT | null>(null);
  const [showPrediction, setShowPrediction] = useState(false);

  const { isConnected } = useWallet();
  const {
    breedNFTs,
    isBreeding,
    breedingPairs,
    calculateBreedingCompatibility,
  } = useNFTMarketplace();

  // Filter breeding-eligible NFTs
  const breedingEligibleNFTs = userNFTs.filter(nft => 
    nft.breeding.isBreedingEnabled && 
    nft.breeding.offspringCount < nft.breeding.maxOffspring &&
    (!nft.breeding.lastBreedingTime || 
     Date.now() - new Date(nft.breeding.lastBreedingTime).getTime() > nft.breeding.breedingCooldown * 1000)
  );

  const compatibility = selectedParent1 && selectedParent2 
    ? calculateBreedingCompatibility(selectedParent1, selectedParent2)
    : 0;

  const breedingCost = compatibility > 80 ? '0.05 ETH' : 
                      compatibility > 60 ? '0.03 ETH' : '0.02 ETH';

  const estimatedTime = compatibility > 80 ? '2 hours' :
                       compatibility > 60 ? '3 hours' : '4 hours';

  const handleBreeding = async () => {
    if (selectedParent1 && selectedParent2) {
      breedNFTs({
        parent1TokenId: selectedParent1.tokenId,
        parent2TokenId: selectedParent2.tokenId,
        contractAddress: selectedParent1.contractAddress,
      });
      
      // Reset selection after breeding
      setSelectedParent1(null);
      setSelectedParent2(null);
      setShowPrediction(false);
    }
  };

  const predictOffspringTraits = () => {
    if (!selectedParent1 || !selectedParent2) return [];

    // Simple trait prediction algorithm
    const parent1Traits = selectedParent1.genetics.dominantTraits;
    const parent2Traits = selectedParent2.genetics.dominantTraits;
    
    const predictions = [];
    
    // Dominant traits have higher chance of inheritance
    parent1Traits.forEach(trait => {
      predictions.push({
        trait,
        probability: parent2Traits.includes(trait) ? 85 : 60,
        source: 'Parent 1',
      });
    });

    parent2Traits.forEach(trait => {
      if (!parent1Traits.includes(trait)) {
        predictions.push({
          trait,
          probability: 45,
          source: 'Parent 2',
        });
      }
    });

    // Possible mutations
    if (compatibility > 70) {
      predictions.push({
        trait: 'Unique Pattern',
        probability: 25,
        source: 'Mutation',
      });
    }

    return predictions.sort((a, b) => b.probability - a.probability);
  };

  const getCompatibilityColor = (comp: number) => {
    if (comp >= 80) return 'text-green-600 bg-green-100';
    if (comp >= 60) return 'text-yellow-600 bg-yellow-100';
    if (comp >= 40) return 'text-orange-600 bg-orange-100';
    return 'text-red-600 bg-red-100';
  };

  const getCompatibilityIcon = (comp: number) => {
    if (comp >= 80) return <CheckCircle className="w-4 h-4" />;
    if (comp >= 60) return <Info className="w-4 h-4" />;
    return <AlertTriangle className="w-4 h-4" />;
  };

  if (!isConnected) {
    return (
      <Card>
        <CardContent className="p-8 text-center">
          <Zap className="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            Breeding Lab
          </h3>
          <p className="text-gray-600 dark:text-gray-400 mb-4">
            Connect your wallet to access the breeding laboratory and create unique offspring.
          </p>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="space-y-6">
      {/* Active Breeding Pairs */}
      {breedingPairs.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Clock className="w-5 h-5" />
              Active Breeding
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {breedingPairs.map((pair) => (
                <div key={pair.id} className="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                      <div className="flex items-center gap-2">
                        <img
                          src={pair.parent1.image}
                          alt={pair.parent1.name}
                          className="w-12 h-12 rounded-lg object-cover"
                        />
                        <Heart className="w-4 h-4 text-pink-500" />
                        <img
                          src={pair.parent2.image}
                          alt={pair.parent2.name}
                          className="w-12 h-12 rounded-lg object-cover"
                        />
                      </div>
                      <div>
                        <div className="font-medium text-gray-900 dark:text-white">
                          {pair.parent1.name} × {pair.parent2.name}
                        </div>
                        <div className="text-sm text-gray-600 dark:text-gray-400">
                          Status: {pair.status}
                        </div>
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="text-sm text-gray-600 dark:text-gray-400">
                        Started {new Date(pair.startedAt).toLocaleDateString()}
                      </div>
                      {pair.status === 'breeding' && (
                        <div className="text-sm text-purple-600 dark:text-purple-400">
                          ~2 hours remaining
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Breeding Selection */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Zap className="w-5 h-5" />
            Breeding Laboratory
          </CardTitle>
        </CardHeader>
        <CardContent>
          {breedingEligibleNFTs.length === 0 ? (
            <div className="text-center py-8">
              <div className="text-6xl mb-4">🧬</div>
              <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                No Breeding-Eligible Cats
              </h3>
              <p className="text-gray-600 dark:text-gray-400">
                You need at least 2 cats that are eligible for breeding to use the lab.
              </p>
            </div>
          ) : (
            <div className="space-y-6">
              {/* Parent Selection */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Parent 1 Selection */}
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Select Parent 1
                  </h3>
                  <div className="grid grid-cols-2 gap-3 max-h-60 overflow-y-auto">
                    {breedingEligibleNFTs.map((nft) => (
                      <motion.div
                        key={nft.tokenId}
                        whileHover={{ scale: 1.05 }}
                        whileTap={{ scale: 0.95 }}
                        onClick={() => setSelectedParent1(nft)}
                        className={`p-3 border-2 rounded-xl cursor-pointer transition-colors ${
                          selectedParent1?.tokenId === nft.tokenId
                            ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                            : 'border-gray-200 dark:border-gray-700 hover:border-purple-300'
                        }`}
                      >
                        <img
                          src={nft.image}
                          alt={nft.name}
                          className="w-full aspect-square object-cover rounded-lg mb-2"
                        />
                        <div className="text-sm font-medium text-gray-900 dark:text-white truncate">
                          {nft.name}
                        </div>
                        <div className="text-xs text-gray-600 dark:text-gray-400">
                          {nft.breed} • Lvl {nft.stats.level}
                        </div>
                      </motion.div>
                    ))}
                  </div>
                </div>

                {/* Parent 2 Selection */}
                <div>
                  <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Select Parent 2
                  </h3>
                  <div className="grid grid-cols-2 gap-3 max-h-60 overflow-y-auto">
                    {breedingEligibleNFTs
                      .filter(nft => nft.tokenId !== selectedParent1?.tokenId)
                      .map((nft) => (
                      <motion.div
                        key={nft.tokenId}
                        whileHover={{ scale: 1.05 }}
                        whileTap={{ scale: 0.95 }}
                        onClick={() => setSelectedParent2(nft)}
                        className={`p-3 border-2 rounded-xl cursor-pointer transition-colors ${
                          selectedParent2?.tokenId === nft.tokenId
                            ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                            : 'border-gray-200 dark:border-gray-700 hover:border-purple-300'
                        }`}
                      >
                        <img
                          src={nft.image}
                          alt={nft.name}
                          className="w-full aspect-square object-cover rounded-lg mb-2"
                        />
                        <div className="text-sm font-medium text-gray-900 dark:text-white truncate">
                          {nft.name}
                        </div>
                        <div className="text-xs text-gray-600 dark:text-gray-400">
                          {nft.breed} • Lvl {nft.stats.level}
                        </div>
                      </motion.div>
                    ))}
                  </div>
                </div>
              </div>

              {/* Breeding Analysis */}
              <AnimatePresence>
                {selectedParent1 && selectedParent2 && (
                  <motion.div
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: 'auto' }}
                    exit={{ opacity: 0, height: 0 }}
                    className="space-y-4"
                  >
                    {/* Compatibility Score */}
                    <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                      <div className="flex items-center justify-between mb-3">
                        <h4 className="font-semibold text-gray-900 dark:text-white">
                          Breeding Compatibility
                        </h4>
                        <div className={`flex items-center gap-1 px-3 py-1 rounded-full ${getCompatibilityColor(compatibility)}`}>
                          {getCompatibilityIcon(compatibility)}
                          <span className="font-medium">{compatibility}%</span>
                        </div>
                      </div>
                      
                      <div className="space-y-2 text-sm">
                        <div className="flex justify-between">
                          <span className="text-gray-600 dark:text-gray-400">Breeding Cost:</span>
                          <span className="font-medium text-gray-900 dark:text-white">{breedingCost}</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-gray-600 dark:text-gray-400">Estimated Time:</span>
                          <span className="font-medium text-gray-900 dark:text-white">{estimatedTime}</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-gray-600 dark:text-gray-400">Success Rate:</span>
                          <span className="font-medium text-gray-900 dark:text-white">{Math.max(70, compatibility)}%</span>
                        </div>
                      </div>
                    </div>

                    {/* Trait Prediction */}
                    <div className="space-y-3">
                      <div className="flex items-center justify-between">
                        <h4 className="font-semibold text-gray-900 dark:text-white">
                          Predicted Offspring Traits
                        </h4>
                        <Button
                          onClick={() => setShowPrediction(!showPrediction)}
                          variant="ghost"
                          size="sm"
                        >
                          {showPrediction ? 'Hide' : 'Show'} Details
                        </Button>
                      </div>

                      <AnimatePresence>
                        {showPrediction && (
                          <motion.div
                            initial={{ opacity: 0, height: 0 }}
                            animate={{ opacity: 1, height: 'auto' }}
                            exit={{ opacity: 0, height: 0 }}
                            className="space-y-2"
                          >
                            {predictOffspringTraits().slice(0, 6).map((prediction, index) => (
                              <div
                                key={index}
                                className="flex items-center justify-between p-3 bg-white dark:bg-gray-700 rounded-lg"
                              >
                                <div className="flex items-center gap-3">
                                  <span className="text-sm font-medium text-gray-900 dark:text-white">
                                    {prediction.trait}
                                  </span>
                                  <span className="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded-full">
                                    {prediction.source}
                                  </span>
                                </div>
                                <div className="text-sm font-medium text-purple-600 dark:text-purple-400">
                                  {prediction.probability}%
                                </div>
                              </div>
                            ))}
                          </motion.div>
                        )}
                      </AnimatePresence>
                    </div>

                    {/* Breeding Button */}
                    <div className="pt-4 border-t border-gray-200 dark:border-gray-700">
                      <Button
                        onClick={handleBreeding}
                        loading={isBreeding}
                        disabled={compatibility < 20}
                        variant="primary"
                        fullWidth
                        icon={<Heart className="w-4 h-4" />}
                      >
                        {compatibility < 20 
                          ? 'Compatibility Too Low' 
                          : `Start Breeding (${breedingCost})`
                        }
                      </Button>
                      
                      {compatibility < 20 && (
                        <p className="text-xs text-center text-red-600 dark:text-red-400 mt-2">
                          Try different parent combinations for better compatibility.
                        </p>
                      )}
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};
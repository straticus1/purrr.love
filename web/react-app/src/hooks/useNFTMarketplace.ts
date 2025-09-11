import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useAccount, useContractRead, useContractWrite, usePrepareContractWrite } from 'wagmi';
import { apiClient, handleApiError } from '@/lib/api';
import { CatNFT, NFTMarketplaceListing, BreedingPair, NFTActivity } from '@/types/nft';
import { parseEther, formatEther } from 'ethers';
import toast from 'react-hot-toast';

export const useNFTMarketplace = () => {
  const queryClient = useQueryClient();
  const { address } = useAccount();

  // Fetch user's NFTs
  const userNFTsQuery = useQuery({
    queryKey: ['nfts', 'user', address],
    queryFn: () => apiClient.get<CatNFT[]>(`/nft/user/${address}`),
    enabled: !!address,
    staleTime: 1000 * 60 * 5, // 5 minutes
  });

  // Fetch marketplace listings
  const marketplaceQuery = useQuery({
    queryKey: ['nft', 'marketplace'],
    queryFn: () => apiClient.get<NFTMarketplaceListing[]>('/nft/marketplace'),
    staleTime: 1000 * 30, // 30 seconds
  });

  // Fetch NFT by token ID
  const useNFT = (tokenId: string, contractAddress: string) => {
    return useQuery({
      queryKey: ['nft', contractAddress, tokenId],
      queryFn: () => apiClient.get<CatNFT>(`/nft/${contractAddress}/${tokenId}`),
      enabled: !!tokenId && !!contractAddress,
      staleTime: 1000 * 60 * 2, // 2 minutes
    });
  };

  // Fetch NFT activity/history
  const useNFTActivity = (tokenId: string, contractAddress: string) => {
    return useQuery({
      queryKey: ['nft', 'activity', contractAddress, tokenId],
      queryFn: () => apiClient.get<NFTActivity[]>(`/nft/${contractAddress}/${tokenId}/activity`),
      enabled: !!tokenId && !!contractAddress,
      staleTime: 1000 * 60 * 1, // 1 minute
    });
  };

  // Create marketplace listing
  const createListingMutation = useMutation({
    mutationFn: async (data: {
      tokenId: string;
      contractAddress: string;
      price: string;
      currency: 'ETH' | 'MATIC' | 'PURR';
      duration: number; // in days
    }) => {
      // First, create the listing in our backend
      const listing = await apiClient.post('/nft/marketplace/create', data);
      
      // Then interact with the smart contract
      // This would normally be handled by the smart contract interaction
      return listing;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['nft', 'marketplace'] });
      queryClient.invalidateQueries({ queryKey: ['nfts', 'user', address] });
      toast.success('NFT listed for sale successfully!');
    },
    onError: (error) => {
      toast.error(`Failed to list NFT: ${handleApiError(error)}`);
    },
  });

  // Purchase NFT
  const purchaseNFTMutation = useMutation({
    mutationFn: async (listingId: string) => {
      return apiClient.post(`/nft/marketplace/purchase/${listingId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['nft', 'marketplace'] });
      queryClient.invalidateQueries({ queryKey: ['nfts', 'user', address] });
      toast.success('NFT purchased successfully!');
    },
    onError: (error) => {
      toast.error(`Failed to purchase NFT: ${handleApiError(error)}`);
    },
  });

  // Cancel listing
  const cancelListingMutation = useMutation({
    mutationFn: async (listingId: string) => {
      return apiClient.post(`/nft/marketplace/cancel/${listingId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['nft', 'marketplace'] });
      queryClient.invalidateQueries({ queryKey: ['nfts', 'user', address] });
      toast.success('Listing cancelled successfully!');
    },
    onError: (error) => {
      toast.error(`Failed to cancel listing: ${handleApiError(error)}`);
    },
  });

  // Mint new NFT
  const mintNFTMutation = useMutation({
    mutationFn: async (data: {
      name: string;
      description: string;
      image: string;
      attributes: any[];
      breed: string;
      rarity: string;
    }) => {
      return apiClient.post('/nft/mint', { ...data, owner: address });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['nfts', 'user', address] });
      toast.success('NFT minted successfully!');
    },
    onError: (error) => {
      toast.error(`Failed to mint NFT: ${handleApiError(error)}`);
    },
  });

  // Breed NFTs
  const breedNFTsMutation = useMutation({
    mutationFn: async (data: {
      parent1TokenId: string;
      parent2TokenId: string;
      contractAddress: string;
    }) => {
      return apiClient.post('/nft/breed', data);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['nfts', 'user', address] });
      toast.success('Breeding started successfully!');
    },
    onError: (error) => {
      toast.error(`Failed to start breeding: ${handleApiError(error)}`);
    },
  });

  // Get breeding pairs
  const breedingPairsQuery = useQuery({
    queryKey: ['nft', 'breeding', address],
    queryFn: () => apiClient.get<BreedingPair[]>(`/nft/breeding/user/${address}`),
    enabled: !!address,
    staleTime: 1000 * 60 * 2, // 2 minutes
  });

  // Transfer NFT
  const transferNFTMutation = useMutation({
    mutationFn: async (data: {
      tokenId: string;
      contractAddress: string;
      to: string;
    }) => {
      return apiClient.post('/nft/transfer', data);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['nfts', 'user', address] });
      toast.success('NFT transferred successfully!');
    },
    onError: (error) => {
      toast.error(`Failed to transfer NFT: ${handleApiError(error)}`);
    },
  });

  // Calculate breeding compatibility
  const calculateBreedingCompatibility = (parent1: CatNFT, parent2: CatNFT): number => {
    // Simple compatibility algorithm based on genetics
    let compatibility = 0;
    
    // Different breeds increase compatibility
    if (parent1.breed !== parent2.breed) compatibility += 20;
    
    // Different generations increase compatibility
    if (Math.abs(parent1.generation - parent2.generation) <= 2) compatibility += 15;
    
    // Genetic diversity check
    const parent1Traits = new Set(parent1.genetics.dominantTraits);
    const parent2Traits = new Set(parent2.genetics.dominantTraits);
    const commonTraits = [...parent1Traits].filter(trait => parent2Traits.has(trait));
    compatibility += Math.max(0, 30 - (commonTraits.length * 5));
    
    // Health and stats compatibility
    const statsDiff = Math.abs(
      (parent1.stats.health + parent1.stats.intelligence + parent1.stats.agility) -
      (parent2.stats.health + parent2.stats.intelligence + parent2.stats.agility)
    ) / 3;
    compatibility += Math.max(0, 20 - statsDiff);
    
    // Breeding history
    if (parent1.breeding.offspringCount < 3) compatibility += 10;
    if (parent2.breeding.offspringCount < 3) compatibility += 10;
    
    return Math.min(100, Math.max(0, compatibility));
  };

  // Format price for display
  const formatPrice = (price: string, currency: string): string => {
    try {
      if (currency === 'ETH' || currency === 'MATIC') {
        return `${formatEther(price)} ${currency}`;
      }
      return `${price} ${currency}`;
    } catch {
      return `${price} ${currency}`;
    }
  };

  // Get rarity color
  const getRarityColor = (rarity: string): string => {
    const colors = {
      common: 'text-gray-600 bg-gray-100',
      uncommon: 'text-green-600 bg-green-100',
      rare: 'text-blue-600 bg-blue-100',
      epic: 'text-purple-600 bg-purple-100',
      legendary: 'text-yellow-600 bg-yellow-100',
    };
    return colors[rarity as keyof typeof colors] || colors.common;
  };

  return {
    // Data
    userNFTs: userNFTsQuery.data || [],
    marketplaceListings: marketplaceQuery.data || [],
    breedingPairs: breedingPairsQuery.data || [],
    
    // Loading states
    isLoadingUserNFTs: userNFTsQuery.isLoading,
    isLoadingMarketplace: marketplaceQuery.isLoading,
    isLoadingBreeding: breedingPairsQuery.isLoading,
    
    // Error states
    userNFTsError: userNFTsQuery.error,
    marketplaceError: marketplaceQuery.error,
    breedingError: breedingPairsQuery.error,
    
    // Actions
    createListing: createListingMutation.mutate,
    purchaseNFT: purchaseNFTMutation.mutate,
    cancelListing: cancelListingMutation.mutate,
    mintNFT: mintNFTMutation.mutate,
    breedNFTs: breedNFTsMutation.mutate,
    transferNFT: transferNFTMutation.mutate,
    
    // Action loading states
    isCreatingListing: createListingMutation.isPending,
    isPurchasing: purchaseNFTMutation.isPending,
    isCancelling: cancelListingMutation.isPending,
    isMinting: mintNFTMutation.isPending,
    isBreeding: breedNFTsMutation.isPending,
    isTransferring: transferNFTMutation.isPending,
    
    // Utilities
    useNFT,
    useNFTActivity,
    calculateBreedingCompatibility,
    formatPrice,
    getRarityColor,
  };
};
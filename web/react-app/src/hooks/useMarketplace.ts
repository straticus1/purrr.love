import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiClient, handleApiError } from '@/lib/api';

interface MarketplaceListing {
  id: string;
  catId: string;
  sellerId: string;
  price: number;
  currency: 'coins' | 'premium' | 'crypto';
  status: 'active' | 'sold' | 'cancelled';
  createdAt: string;
  cat: any;
  seller: any;
}

interface CreateListingData {
  catId: string;
  price: number;
  currency: 'coins' | 'premium' | 'crypto';
}

export const useMarketplace = () => {
  const queryClient = useQueryClient();

  // Marketplace listings query
  const listingsQuery = useQuery({
    queryKey: ['marketplace', 'listings'],
    queryFn: () => apiClient.get<MarketplaceListing[]>('/marketplace/listings'),
    staleTime: 1000 * 60 * 1, // 1 minute
  });

  // User's listings query
  const myListingsQuery = useQuery({
    queryKey: ['marketplace', 'my-listings'],
    queryFn: () => apiClient.get<MarketplaceListing[]>('/marketplace/my-listings'),
    staleTime: 1000 * 60 * 2, // 2 minutes
  });

  // Create listing mutation
  const createListingMutation = useMutation({
    mutationFn: (listingData: CreateListingData) => 
      apiClient.post('/marketplace/listings', listingData),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['marketplace', 'listings'] });
      queryClient.invalidateQueries({ queryKey: ['marketplace', 'my-listings'] });
      queryClient.invalidateQueries({ queryKey: ['cats'] });
    },
  });

  // Purchase cat mutation
  const purchaseCatMutation = useMutation({
    mutationFn: (listingId: string) => 
      apiClient.post(`/marketplace/purchase/${listingId}`, {}),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['marketplace', 'listings'] });
      queryClient.invalidateQueries({ queryKey: ['cats'] });
      queryClient.invalidateQueries({ queryKey: ['user'] });
    },
  });

  // Cancel listing mutation
  const cancelListingMutation = useMutation({
    mutationFn: (listingId: string) => 
      apiClient.delete(`/marketplace/listings/${listingId}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['marketplace', 'listings'] });
      queryClient.invalidateQueries({ queryKey: ['marketplace', 'my-listings'] });
      queryClient.invalidateQueries({ queryKey: ['cats'] });
    },
  });

  // Market stats query
  const marketStatsQuery = useQuery({
    queryKey: ['marketplace', 'stats'],
    queryFn: () => apiClient.get('/marketplace/stats'),
    staleTime: 1000 * 60 * 5, // 5 minutes
  });

  return {
    // Data
    listings: listingsQuery.data || [],
    myListings: myListingsQuery.data || [],
    marketStats: marketStatsQuery.data,

    // Loading states
    isLoadingListings: listingsQuery.isLoading,
    isLoadingMyListings: myListingsQuery.isLoading,
    isLoadingStats: marketStatsQuery.isLoading,

    // Error states
    listingsError: listingsQuery.error,
    myListingsError: myListingsQuery.error,
    statsError: marketStatsQuery.error,

    // Actions
    createListing: createListingMutation.mutate,
    purchaseCat: purchaseCatMutation.mutate,
    cancelListing: cancelListingMutation.mutate,

    // Action loading states
    isCreatingListing: createListingMutation.isPending,
    isPurchasing: purchaseCatMutation.isPending,
    isCancelling: cancelListingMutation.isPending,

    // Action errors
    createListingError: createListingMutation.error,
    purchaseError: purchaseCatMutation.error,
    cancelError: cancelListingMutation.error,
  };
};
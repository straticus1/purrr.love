import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useCatsStore } from '@/store/catsStore';
import { apiClient, handleApiError } from '@/lib/api';

export const useCats = () => {
  const queryClient = useQueryClient();
  const catsStore = useCatsStore();

  // Cats query
  const catsQuery = useQuery({
    queryKey: ['cats'],
    queryFn: () => apiClient.get('/cats'),
    staleTime: 1000 * 60 * 2, // 2 minutes
  });

  // Create cat mutation
  const createCatMutation = useMutation({
    mutationFn: (catData: any) => apiClient.post('/cats', catData),
    onSuccess: (newCat) => {
      queryClient.invalidateQueries({ queryKey: ['cats'] });
      catsStore.addCat(newCat);
    },
  });

  // Update cat mutation
  const updateCatMutation = useMutation({
    mutationFn: ({ catId, updates }: { catId: string; updates: any }) => 
      apiClient.put(`/cats/${catId}`, updates),
    onSuccess: (updatedCat) => {
      queryClient.invalidateQueries({ queryKey: ['cats'] });
      queryClient.invalidateQueries({ queryKey: ['cat', updatedCat.id] });
      catsStore.updateCat(updatedCat.id, updatedCat);
    },
  });

  // Feed cat mutation
  const feedCatMutation = useMutation({
    mutationFn: ({ catId, foodType }: { catId: string; foodType: string }) => 
      apiClient.post(`/cats/${catId}/feed`, { foodType }),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['cats'] });
      queryClient.invalidateQueries({ queryKey: ['cat', data.catId] });
      catsStore.feedCat(data.catId, data.foodType);
    },
  });

  // Play with cat mutation
  const playCatMutation = useMutation({
    mutationFn: ({ catId, activityType }: { catId: string; activityType: string }) => 
      apiClient.post(`/cats/${catId}/play`, { activityType }),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['cats'] });
      queryClient.invalidateQueries({ queryKey: ['cat', data.catId] });
      catsStore.playCat(data.catId, data.activityType);
    },
  });

  // Breed cats mutation
  const breedCatsMutation = useMutation({
    mutationFn: ({ parentA, parentB }: { parentA: string; parentB: string }) => 
      apiClient.post('/cats/breed', { parentA, parentB }),
    onSuccess: (newCat) => {
      queryClient.invalidateQueries({ queryKey: ['cats'] });
      catsStore.addCat(newCat);
    },
  });

  // Train cat mutation
  const trainCatMutation = useMutation({
    mutationFn: ({ catId, skillType }: { catId: string; skillType: string }) => 
      apiClient.post(`/cats/${catId}/train`, { skillType }),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['cats'] });
      queryClient.invalidateQueries({ queryKey: ['cat', data.catId] });
      catsStore.trainCat(data.catId, data.skillType);
    },
  });

  return {
    // Data
    cats: catsQuery.data || [],
    isLoading: catsQuery.isLoading,
    error: catsQuery.error,

    // Actions
    createCat: createCatMutation.mutate,
    updateCat: updateCatMutation.mutate,
    feedCat: feedCatMutation.mutate,
    playCat: playCatMutation.mutate,
    breedCats: breedCatsMutation.mutate,
    trainCat: trainCatMutation.mutate,

    // Status
    isCreating: createCatMutation.isPending,
    isUpdating: updateCatMutation.isPending,
    isFeeding: feedCatMutation.isPending,
    isPlaying: playCatMutation.isPending,
    isBreeding: breedCatsMutation.isPending,
    isTraining: trainCatMutation.isPending,

    // Store actions
    ...catsStore,
  };
};
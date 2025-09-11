import { create } from 'zustand';
import { devtools, subscribeWithSelector } from 'zustand/middleware';

export interface Cat {
  id: string;
  name: string;
  breed: string;
  age: number;
  color: string;
  pattern?: string;
  gender: 'male' | 'female' | 'unknown';
  
  // Stats
  health: number; // 0-100
  happiness: number; // 0-100
  energy: number; // 0-100
  hunger: number; // 0-100 (lower is hungrier)
  
  // Personality traits (Big Five model)
  personality: {
    openness: number; // 0-100
    conscientiousness: number; // 0-100
    extraversion: number; // 0-100
    agreeableness: number; // 0-100
    neuroticism: number; // 0-100
  };
  
  // Genetics
  genetics: {
    heritageScore: number;
    rareTraits: string[];
    geneticMarkers: Record<string, any>;
  };
  
  // Game stats
  level: number;
  experience: number;
  totalPlayTime: number;
  gamesWon: number;
  favoriteActivities: string[];
  
  // Breeding
  isNeutered: boolean;
  breedingCooldown?: string;
  offspring: string[];
  parents?: {
    mother?: string;
    father?: string;
  };
  
  // Economy
  value: number; // estimated market value
  nftTokenId?: string;
  isListed: boolean;
  listingPrice?: number;
  
  // Status
  isActive: boolean;
  location: 'home' | 'vet' | 'boarding' | 'missing' | 'deceased';
  lastFed?: string;
  lastPlayed?: string;
  lastGroomed?: string;
  
  // Metadata
  imageUrl?: string;
  createdAt: string;
  updatedAt: string;
  ownerId: string;
}

interface CatsState {
  // State
  cats: Cat[];
  selectedCat: Cat | null;
  isLoading: boolean;
  error: string | null;
  filter: {
    breed?: string;
    minAge?: number;
    maxAge?: number;
    location?: Cat['location'];
    searchTerm?: string;
    sortBy: 'name' | 'age' | 'level' | 'health' | 'happiness' | 'value' | 'createdAt';
    sortOrder: 'asc' | 'desc';
  };
  
  // Actions
  fetchCats: () => Promise<void>;
  fetchCat: (catId: string) => Promise<Cat>;
  createCat: (catData: Partial<Cat>) => Promise<Cat>;
  updateCat: (catId: string, updates: Partial<Cat>) => Promise<void>;
  deleteCat: (catId: string) => Promise<void>;
  
  // Cat care actions
  feedCat: (catId: string, foodType?: string) => Promise<void>;
  playWithCat: (catId: string, gameType: string, duration?: number) => Promise<void>;
  groomCat: (catId: string) => Promise<void>;
  giveTreat: (catId: string, treatType: string) => Promise<void>;
  
  // Breeding
  breedCats: (motherId: string, fatherId: string) => Promise<Cat>;
  getBreedingCompatibility: (cat1Id: string, cat2Id: string) => Promise<number>;
  
  // Training & Activities
  trainCat: (catId: string, skill: string, duration: number) => Promise<void>;
  enterCompetition: (catId: string, competitionType: string) => Promise<any>;
  
  // Economy
  listCatForSale: (catId: string, price: number) => Promise<void>;
  unlistCat: (catId: string) => Promise<void>;
  purchaseCat: (catId: string) => Promise<void>;
  mintNFT: (catId: string) => Promise<string>;
  
  // Utilities
  selectCat: (cat: Cat | null) => void;
  updateFilter: (filter: Partial<CatsState['filter']>) => void;
  clearError: () => void;
  reset: () => void;
  
  // Computed getters
  getFilteredCats: () => Cat[];
  getCatsByBreed: (breed: string) => Cat[];
  getTopCats: (criteria: 'level' | 'value' | 'health', limit?: number) => Cat[];
  getUserStats: () => {
    totalCats: number;
    averageLevel: number;
    averageHealth: number;
    totalValue: number;
    rareTraits: number;
  };
}

const API_BASE_URL = import.meta.env.VITE_API_URL || '/api';

export const useCatsStore = create<CatsState>()(
  devtools(
    subscribeWithSelector((set, get) => ({
      // Initial state
      cats: [],
      selectedCat: null,
      isLoading: false,
      error: null,
      filter: {
        sortBy: 'name',
        sortOrder: 'asc',
      },

      // Actions
      fetchCats: async () => {
        set({ isLoading: true, error: null });

        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats`, {
            headers: {
              'Authorization': `Bearer ${token}`,
            },
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to fetch cats');
          }

          const cats = await response.json();

          set({
            cats,
            isLoading: false,
            error: null,
          });

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to fetch cats';
          set({
            error: errorMessage,
            isLoading: false,
          });
        }
      },

      fetchCat: async (catId: string) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats/${catId}`, {
            headers: {
              'Authorization': `Bearer ${token}`,
            },
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to fetch cat');
          }

          const cat = await response.json();
          
          // Update cat in store
          set(state => ({
            cats: state.cats.map(c => c.id === catId ? cat : c)
          }));

          return cat;

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to fetch cat';
          set({ error: errorMessage });
          throw error;
        }
      },

      createCat: async (catData: Partial<Cat>) => {
        set({ isLoading: true, error: null });

        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify(catData),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to create cat');
          }

          const newCat = await response.json();

          set(state => ({
            cats: [...state.cats, newCat],
            isLoading: false,
            error: null,
          }));

          return newCat;

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to create cat';
          set({
            error: errorMessage,
            isLoading: false,
          });
          throw error;
        }
      },

      updateCat: async (catId: string, updates: Partial<Cat>) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats/${catId}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify(updates),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to update cat');
          }

          const updatedCat = await response.json();

          set(state => ({
            cats: state.cats.map(cat => cat.id === catId ? updatedCat : cat),
            selectedCat: state.selectedCat?.id === catId ? updatedCat : state.selectedCat,
          }));

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to update cat';
          set({ error: errorMessage });
          throw error;
        }
      },

      deleteCat: async (catId: string) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats/${catId}`, {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${token}`,
            },
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to delete cat');
          }

          set(state => ({
            cats: state.cats.filter(cat => cat.id !== catId),
            selectedCat: state.selectedCat?.id === catId ? null : state.selectedCat,
          }));

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to delete cat';
          set({ error: errorMessage });
          throw error;
        }
      },

      // Cat care actions
      feedCat: async (catId: string, foodType = 'regular') => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats/${catId}/feed`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ foodType }),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to feed cat');
          }

          const result = await response.json();

          // Update cat stats
          set(state => ({
            cats: state.cats.map(cat =>
              cat.id === catId
                ? {
                    ...cat,
                    hunger: Math.min(100, cat.hunger + 20),
                    health: Math.min(100, cat.health + 2),
                    happiness: Math.min(100, cat.happiness + 5),
                    lastFed: new Date().toISOString(),
                  }
                : cat
            ),
          }));

          return result;

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to feed cat';
          set({ error: errorMessage });
          throw error;
        }
      },

      playWithCat: async (catId: string, gameType: string, duration = 10) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats/${catId}/play`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ gameType, duration }),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to play with cat');
          }

          const result = await response.json();

          // Update cat stats
          set(state => ({
            cats: state.cats.map(cat =>
              cat.id === catId
                ? {
                    ...cat,
                    happiness: Math.min(100, cat.happiness + 15),
                    energy: Math.max(0, cat.energy - 10),
                    experience: cat.experience + 50,
                    totalPlayTime: cat.totalPlayTime + duration,
                    lastPlayed: new Date().toISOString(),
                  }
                : cat
            ),
          }));

          return result;

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to play with cat';
          set({ error: errorMessage });
          throw error;
        }
      },

      groomCat: async (catId: string) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats/${catId}/groom`, {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`,
            },
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to groom cat');
          }

          const result = await response.json();

          // Update cat stats
          set(state => ({
            cats: state.cats.map(cat =>
              cat.id === catId
                ? {
                    ...cat,
                    health: Math.min(100, cat.health + 5),
                    happiness: Math.min(100, cat.happiness + 10),
                    lastGroomed: new Date().toISOString(),
                  }
                : cat
            ),
          }));

          return result;

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to groom cat';
          set({ error: errorMessage });
          throw error;
        }
      },

      giveTreat: async (catId: string, treatType: string) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats/${catId}/treat`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ treatType }),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to give treat');
          }

          const result = await response.json();

          // Update cat stats based on treat type
          const treatEffects = {
            'catnip': { happiness: 20, energy: 10 },
            'tuna': { happiness: 15, health: 5 },
            'salmon': { happiness: 15, health: 8 },
            'chicken': { happiness: 12, health: 6 },
          };

          const effects = treatEffects[treatType as keyof typeof treatEffects] || { happiness: 10 };

          set(state => ({
            cats: state.cats.map(cat =>
              cat.id === catId
                ? {
                    ...cat,
                    happiness: Math.min(100, cat.happiness + effects.happiness),
                    health: Math.min(100, cat.health + (effects.health || 0)),
                  }
                : cat
            ),
          }));

          return result;

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to give treat';
          set({ error: errorMessage });
          throw error;
        }
      },

      // Breeding
      breedCats: async (motherId: string, fatherId: string) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats/breed`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ motherId, fatherId }),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to breed cats');
          }

          const kitten = await response.json();

          set(state => ({
            cats: [...state.cats, kitten],
          }));

          return kitten;

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to breed cats';
          set({ error: errorMessage });
          throw error;
        }
      },

      getBreedingCompatibility: async (cat1Id: string, cat2Id: string) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats/breeding-compatibility?cat1=${cat1Id}&cat2=${cat2Id}`, {
            headers: {
              'Authorization': `Bearer ${token}`,
            },
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to get breeding compatibility');
          }

          const { compatibility } = await response.json();
          return compatibility;

        } catch (error) {
          console.error('Failed to get breeding compatibility:', error);
          return 0;
        }
      },

      // Training & Activities
      trainCat: async (catId: string, skill: string, duration: number) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/cats/${catId}/train`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ skill, duration }),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to train cat');
          }

          const result = await response.json();

          // Update cat experience and relevant personality traits
          set(state => ({
            cats: state.cats.map(cat =>
              cat.id === catId
                ? {
                    ...cat,
                    experience: cat.experience + duration * 10,
                    energy: Math.max(0, cat.energy - duration * 2),
                  }
                : cat
            ),
          }));

          return result;

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to train cat';
          set({ error: errorMessage });
          throw error;
        }
      },

      enterCompetition: async (catId: string, competitionType: string) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/competitions/enter`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ catId, competitionType }),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to enter competition');
          }

          const result = await response.json();
          return result;

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to enter competition';
          set({ error: errorMessage });
          throw error;
        }
      },

      // Economy
      listCatForSale: async (catId: string, price: number) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/marketplace/list`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ catId, price }),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to list cat for sale');
          }

          set(state => ({
            cats: state.cats.map(cat =>
              cat.id === catId
                ? {
                    ...cat,
                    isListed: true,
                    listingPrice: price,
                  }
                : cat
            ),
          }));

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to list cat for sale';
          set({ error: errorMessage });
          throw error;
        }
      },

      unlistCat: async (catId: string) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/marketplace/unlist`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ catId }),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to unlist cat');
          }

          set(state => ({
            cats: state.cats.map(cat =>
              cat.id === catId
                ? {
                    ...cat,
                    isListed: false,
                    listingPrice: undefined,
                  }
                : cat
            ),
          }));

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to unlist cat';
          set({ error: errorMessage });
          throw error;
        }
      },

      purchaseCat: async (catId: string) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/marketplace/purchase`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ catId }),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to purchase cat');
          }

          const purchasedCat = await response.json();

          set(state => ({
            cats: [...state.cats, purchasedCat],
          }));

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to purchase cat';
          set({ error: errorMessage });
          throw error;
        }
      },

      mintNFT: async (catId: string) => {
        try {
          const token = localStorage.getItem('auth_token');
          const response = await fetch(`${API_BASE_URL}/nft/mint`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ catId }),
            credentials: 'include',
          });

          if (!response.ok) {
            throw new Error('Failed to mint NFT');
          }

          const { tokenId } = await response.json();

          set(state => ({
            cats: state.cats.map(cat =>
              cat.id === catId
                ? {
                    ...cat,
                    nftTokenId: tokenId,
                  }
                : cat
            ),
          }));

          return tokenId;

        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Failed to mint NFT';
          set({ error: errorMessage });
          throw error;
        }
      },

      // Utilities
      selectCat: (cat: Cat | null) => {
        set({ selectedCat: cat });
      },

      updateFilter: (filter: Partial<CatsState['filter']>) => {
        set(state => ({
          filter: { ...state.filter, ...filter }
        }));
      },

      clearError: () => {
        set({ error: null });
      },

      reset: () => {
        set({
          cats: [],
          selectedCat: null,
          isLoading: false,
          error: null,
          filter: {
            sortBy: 'name',
            sortOrder: 'asc',
          },
        });
      },

      // Computed getters
      getFilteredCats: () => {
        const { cats, filter } = get();
        let filteredCats = [...cats];

        // Apply filters
        if (filter.breed) {
          filteredCats = filteredCats.filter(cat => 
            cat.breed.toLowerCase().includes(filter.breed!.toLowerCase())
          );
        }

        if (filter.minAge !== undefined) {
          filteredCats = filteredCats.filter(cat => cat.age >= filter.minAge!);
        }

        if (filter.maxAge !== undefined) {
          filteredCats = filteredCats.filter(cat => cat.age <= filter.maxAge!);
        }

        if (filter.location) {
          filteredCats = filteredCats.filter(cat => cat.location === filter.location);
        }

        if (filter.searchTerm) {
          const searchTerm = filter.searchTerm.toLowerCase();
          filteredCats = filteredCats.filter(cat =>
            cat.name.toLowerCase().includes(searchTerm) ||
            cat.breed.toLowerCase().includes(searchTerm) ||
            cat.color.toLowerCase().includes(searchTerm)
          );
        }

        // Apply sorting
        filteredCats.sort((a, b) => {
          let aValue: any = a[filter.sortBy];
          let bValue: any = b[filter.sortBy];

          if (typeof aValue === 'string') {
            aValue = aValue.toLowerCase();
            bValue = bValue.toLowerCase();
          }

          if (filter.sortOrder === 'asc') {
            return aValue < bValue ? -1 : aValue > bValue ? 1 : 0;
          } else {
            return aValue > bValue ? -1 : aValue < bValue ? 1 : 0;
          }
        });

        return filteredCats;
      },

      getCatsByBreed: (breed: string) => {
        const { cats } = get();
        return cats.filter(cat => cat.breed.toLowerCase() === breed.toLowerCase());
      },

      getTopCats: (criteria: 'level' | 'value' | 'health', limit = 10) => {
        const { cats } = get();
        return [...cats]
          .sort((a, b) => b[criteria] - a[criteria])
          .slice(0, limit);
      },

      getUserStats: () => {
        const { cats } = get();
        const activeCats = cats.filter(cat => cat.isActive);

        if (activeCats.length === 0) {
          return {
            totalCats: 0,
            averageLevel: 0,
            averageHealth: 0,
            totalValue: 0,
            rareTraits: 0,
          };
        }

        const totalLevel = activeCats.reduce((sum, cat) => sum + cat.level, 0);
        const totalHealth = activeCats.reduce((sum, cat) => sum + cat.health, 0);
        const totalValue = activeCats.reduce((sum, cat) => sum + cat.value, 0);
        const rareTraits = activeCats.reduce((sum, cat) => sum + cat.genetics.rareTraits.length, 0);

        return {
          totalCats: activeCats.length,
          averageLevel: Math.round(totalLevel / activeCats.length),
          averageHealth: Math.round(totalHealth / activeCats.length),
          totalValue,
          rareTraits,
        };
      },
    })),
    {
      name: 'cats-store',
    }
  )
);

// Selectors
export const useCats = () => useCatsStore((state) => state.getFilteredCats());
export const useSelectedCat = () => useCatsStore((state) => state.selectedCat);
export const useCatsLoading = () => useCatsStore((state) => state.isLoading);
export const useCatsError = () => useCatsStore((state) => state.error);
export const useCatsFilter = () => useCatsStore((state) => state.filter);
export const useUserCatStats = () => useCatsStore((state) => state.getUserStats());
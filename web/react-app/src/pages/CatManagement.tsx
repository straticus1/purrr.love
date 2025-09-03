import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Plus, 
  Search, 
  Filter, 
  SortAsc, 
  Grid, 
  List,
  Heart,
  Activity,
  Zap,
  Droplets,
  Star,
  Crown,
  Gem,
  TrendingUp,
  Users,
  Target,
  Award
} from 'lucide-react';
import CatCard from '@/components/CatCard';
import { Cat } from '@/types/cat';

// Mock data for demonstration
const mockCats: Cat[] = [
  {
    id: '1',
    name: 'Whiskers',
    breed: 'Persian',
    color: 'White',
    age: 3,
    health: 95,
    happiness: 88,
    energy: 75,
    hunger: 20,
    cleanliness: 90,
    ownerId: 'user1',
    createdAt: '2023-01-15T10:00:00Z',
    updatedAt: '2024-01-15T10:00:00Z',
    personality: {
      openness: 8,
      conscientiousness: 7,
      extraversion: 6,
      agreeableness: 9,
      neuroticism: 3,
      traits: ['Curious', 'Friendly', 'Playful'],
      preferences: {
        favoriteToys: ['Feather Wand', 'Laser Pointer'],
        favoriteFood: ['Salmon', 'Chicken'],
        favoriteActivities: ['Hunting', 'Climbing'],
        socialLevel: 'social',
        energyLevel: 'moderate',
        playStyle: 'playful'
      }
    },
    genetics: {
      breed: 'Persian',
      rarity: 'rare',
      mutations: [],
      inheritedTraits: ['Long Fur', 'Flat Face'],
      generation: 1,
      parents: []
    },
    achievements: [
      {
        id: '1',
        name: 'First Friend',
        description: 'Made first friend',
        icon: '👥',
        unlockedAt: '2023-02-01T10:00:00Z',
        rarity: 'common',
        points: 10
      }
    ],
    equipment: [],
    status: {
      isOnline: true,
      lastSeen: '2024-01-15T10:00:00Z',
      currentActivity: 'Playing with toys',
      location: 'Living Room',
      mood: 'happy',
      energy: 'moderate',
      hunger: 'satisfied',
      cleanliness: 'clean'
    }
  },
  {
    id: '2',
    name: 'Shadow',
    breed: 'Maine Coon',
    color: 'Black',
    age: 2,
    health: 100,
    happiness: 95,
    energy: 90,
    hunger: 15,
    cleanliness: 95,
    ownerId: 'user1',
    createdAt: '2023-06-20T14:00:00Z',
    updatedAt: '2024-01-15T10:00:00Z',
    personality: {
      openness: 9,
      conscientiousness: 8,
      extraversion: 7,
      agreeableness: 8,
      neuroticism: 2,
      traits: ['Adventurous', 'Intelligent', 'Loyal'],
      preferences: {
        favoriteToys: ['Interactive Puzzle', 'Cat Tree'],
        favoriteFood: ['Tuna', 'Beef'],
        favoriteActivities: ['Exploring', 'Training'],
        socialLevel: 'very_social',
        energyLevel: 'high',
        playStyle: 'curious'
      }
    },
    genetics: {
      breed: 'Maine Coon',
      rarity: 'epic',
      mutations: [
        {
          type: 'positive',
          name: 'Enhanced Intelligence',
          description: 'Above average problem-solving skills',
          effect: '+20% training effectiveness',
          rarity: 0.1
        }
      ],
      inheritedTraits: ['Large Size', 'Intelligence'],
      generation: 1,
      parents: []
    },
    achievements: [
      {
        id: '2',
        name: 'Puzzle Master',
        description: 'Solved 10 puzzles',
        icon: '🧩',
        unlockedAt: '2023-08-15T10:00:00Z',
        rarity: 'rare',
        points: 50
      }
    ],
    equipment: [],
    status: {
      isOnline: true,
      lastSeen: '2024-01-15T10:00:00Z',
      currentActivity: 'Solving puzzle',
      location: 'Study Room',
      mood: 'excited',
      energy: 'high',
      hunger: 'satisfied',
      cleanliness: 'clean'
    }
  },
  {
    id: '3',
    name: 'Luna',
    breed: 'Siamese',
    color: 'Cream',
    age: 1,
    health: 98,
    happiness: 92,
    energy: 85,
    hunger: 25,
    cleanliness: 88,
    ownerId: 'user1',
    createdAt: '2023-11-10T09:00:00Z',
    updatedAt: '2024-01-15T10:00:00Z',
    personality: {
      openness: 7,
      conscientiousness: 6,
      extraversion: 8,
      agreeableness: 7,
      neuroticism: 4,
      traits: ['Vocal', 'Active', 'Social'],
      preferences: {
        favoriteToys: ['Bell Ball', 'Rope Toy'],
        favoriteFood: ['Fish', 'Turkey'],
        favoriteActivities: ['Running', 'Chatting'],
        socialLevel: 'very_social',
        energyLevel: 'very_high',
        playStyle: 'aggressive'
      }
    },
    genetics: {
      breed: 'Siamese',
      rarity: 'uncommon',
      mutations: [],
      inheritedTraits: ['Pointed Ears', 'Blue Eyes'],
      generation: 1,
      parents: []
    },
    achievements: [],
    equipment: [],
    status: {
      isOnline: false,
      lastSeen: '2024-01-14T22:00:00Z',
      currentActivity: 'Sleeping',
      location: 'Bedroom',
      mood: 'content',
      energy: 'low',
      hunger: 'hungry',
      cleanliness: 'slightly_dirty'
    }
  }
];

const CatManagement: React.FC = () => {
  const [cats, setCats] = useState<Cat[]>(mockCats);
  const [filteredCats, setFilteredCats] = useState<Cat[]>(mockCats);
  const [searchTerm, setSearchTerm] = useState('');
  const [sortBy, setSortBy] = useState<'name' | 'health' | 'happiness' | 'energy' | 'age'>('name');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [selectedFilters, setSelectedFilters] = useState({
    breed: '',
    rarity: '',
    status: '',
    age: ''
  });

  // Stats calculation
  const stats = {
    totalCats: cats.length,
    healthyCats: cats.filter(cat => cat.health >= 80).length,
    happyCats: cats.filter(cat => cat.happiness >= 80).length,
    activeCats: cats.filter(cat => cat.energy >= 70).length,
    averageHealth: Math.round(cats.reduce((sum, cat) => sum + cat.health, 0) / cats.length),
    averageHappiness: Math.round(cats.reduce((sum, cat) => sum + cat.happiness, 0) / cats.length),
    totalExperience: cats.reduce((sum, cat) => sum + (cat.achievements?.length || 0), 0)
  };

  // Filter and sort cats
  useEffect(() => {
    let filtered = cats.filter(cat => {
      const matchesSearch = cat.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                           cat.breed?.toLowerCase().includes(searchTerm.toLowerCase());
      
      const matchesBreed = !selectedFilters.breed || cat.breed === selectedFilters.breed;
      const matchesRarity = !selectedFilters.rarity || cat.genetics?.rarity === selectedFilters.rarity;
      const matchesStatus = !selectedFilters.status || cat.status?.mood === selectedFilters.status;
      const matchesAge = !selectedFilters.age || 
        (selectedFilters.age === 'young' && cat.age && cat.age <= 2) ||
        (selectedFilters.age === 'adult' && cat.age && cat.age > 2 && cat.age <= 7) ||
        (selectedFilters.age === 'senior' && cat.age && cat.age > 7);

      return matchesSearch && matchesBreed && matchesRarity && matchesStatus && matchesAge;
    });

    // Sort cats
    filtered.sort((a, b) => {
      let aValue: any, bValue: any;
      
      switch (sortBy) {
        case 'name':
          aValue = a.name;
          bValue = b.name;
          break;
        case 'health':
          aValue = a.health;
          bValue = b.health;
          break;
        case 'happiness':
          aValue = a.happiness;
          bValue = b.happiness;
          break;
        case 'energy':
          aValue = a.energy;
          bValue = b.energy;
          break;
        case 'age':
          aValue = a.age || 0;
          bValue = b.age || 0;
          break;
        default:
          aValue = a.name;
          bValue = b.name;
      }

      if (sortOrder === 'asc') {
        return aValue > bValue ? 1 : -1;
      } else {
        return aValue < bValue ? 1 : -1;
      }
    });

    setFilteredCats(filtered);
  }, [cats, searchTerm, selectedFilters, sortBy, sortOrder]);

  // Handler functions
  const handleEdit = (cat: Cat) => {
    console.log('Edit cat:', cat);
    // TODO: Implement edit functionality
  };

  const handleDelete = (catId: string) => {
    setCats(cats.filter(cat => cat.id !== catId));
  };

  const handleCare = (catId: string) => {
    setCats(cats.map(cat => 
      cat.id === catId 
        ? { ...cat, health: Math.min(100, cat.health + 5), cleanliness: Math.min(100, cat.cleanliness + 10) }
        : cat
    ));
  };

  const handlePlay = (catId: string) => {
    setCats(cats.map(cat => 
      cat.id === catId 
        ? { ...cat, happiness: Math.min(100, cat.happiness + 8), energy: Math.max(0, cat.energy - 15) }
        : cat
    ));
  };

  const handleAddCat = () => {
    console.log('Add new cat');
    // TODO: Implement add cat functionality
  };

  const clearFilters = () => {
    setSelectedFilters({
      breed: '',
      rarity: '',
      status: '',
      age: ''
    });
    setSearchTerm('');
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
      {/* Animated Background */}
      <div className="fixed inset-0 overflow-hidden pointer-events-none">
        <div className="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div className="absolute top-40 left-40 w-80 h-80 bg-indigo-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
      </div>

      <div className="relative z-10 container mx-auto px-4 py-8">
        {/* Header */}
        <motion.div 
          className="text-center mb-12"
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
        >
          <h1 className="text-5xl font-bold bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 bg-clip-text text-transparent mb-4">
            🐱 Cat Management
          </h1>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            Manage your feline companions with love and care. Monitor their health, happiness, and growth in real-time.
          </p>
        </motion.div>

        {/* Stats Overview */}
        <motion.div 
          className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.1 }}
        >
          <div className="stats-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Total Cats</p>
                <p className="text-2xl font-bold text-gray-900">{stats.totalCats}</p>
              </div>
              <div className="p-3 bg-blue-100 rounded-full">
                <Users className="w-6 h-6 text-blue-600" />
              </div>
            </div>
          </div>

          <div className="stats-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Healthy Cats</p>
                <p className="text-2xl font-bold text-green-600">{stats.healthyCats}</p>
              </div>
              <div className="p-3 bg-green-100 rounded-full">
                <Heart className="w-6 h-6 text-green-600" />
              </div>
            </div>
          </div>

          <div className="stats-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Happy Cats</p>
                <p className="text-2xl font-bold text-yellow-600">{stats.happyCats}</p>
              </div>
              <div className="p-3 bg-yellow-100 rounded-full">
                <Star className="w-6 h-6 text-yellow-600" />
              </div>
            </div>
          </div>

          <div className="stats-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Active Cats</p>
                <p className="text-2xl font-bold text-purple-600">{stats.activeCats}</p>
              </div>
              <div className="p-3 bg-purple-100 rounded-full">
                <Activity className="w-6 h-6 text-purple-600" />
              </div>
            </div>
          </div>
        </motion.div>

        {/* Controls */}
        <motion.div 
          className="bg-white rounded-3xl p-6 shadow-xl border border-gray-100 mb-8"
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.2 }}
        >
          <div className="flex flex-col lg:flex-row gap-6 items-center justify-between">
            {/* Search and Filters */}
            <div className="flex-1 flex flex-col sm:flex-row gap-4">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                <input
                  type="text"
                  placeholder="Search cats by name or breed..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                />
              </div>

              <select
                value={selectedFilters.breed}
                onChange={(e) => setSelectedFilters({...selectedFilters, breed: e.target.value})}
                className="px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
              >
                <option value="">All Breeds</option>
                <option value="Persian">Persian</option>
                <option value="Maine Coon">Maine Coon</option>
                <option value="Siamese">Siamese</option>
                <option value="British Shorthair">British Shorthair</option>
                <option value="Ragdoll">Ragdoll</option>
              </select>

              <select
                value={selectedFilters.rarity}
                onChange={(e) => setSelectedFilters({...selectedFilters, rarity: e.target.value})}
                className="px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
              >
                <option value="">All Rarities</option>
                <option value="common">Common</option>
                <option value="uncommon">Uncommon</option>
                <option value="rare">Rare</option>
                <option value="epic">Epic</option>
                <option value="legendary">Legendary</option>
              </select>
            </div>

            {/* Sort and View Controls */}
            <div className="flex items-center gap-4">
              <div className="flex items-center gap-2">
                <SortAsc className="w-5 h-5 text-gray-500" />
                <select
                  value={sortBy}
                  onChange={(e) => setSortBy(e.target.value as any)}
                  className="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 text-sm"
                >
                  <option value="name">Name</option>
                  <option value="health">Health</option>
                  <option value="happiness">Happiness</option>
                  <option value="energy">Energy</option>
                  <option value="age">Age</option>
                </select>
                <button
                  onClick={() => setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc')}
                  className="p-2 hover:bg-gray-100 rounded-xl transition-colors duration-200"
                >
                  {sortOrder === 'asc' ? '↑' : '↓'}
                </button>
              </div>

              <div className="flex items-center gap-2 bg-gray-100 rounded-xl p-1">
                <button
                  onClick={() => setViewMode('grid')}
                  className={`p-2 rounded-lg transition-all duration-200 ${
                    viewMode === 'grid' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'
                  }`}
                >
                  <Grid className="w-5 h-5" />
                </button>
                <button
                  onClick={() => setViewMode('list')}
                  className={`p-2 rounded-lg transition-all duration-200 ${
                    viewMode === 'list' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'
                  }`}
                >
                  <List className="w-5 h-5" />
                </button>
              </div>

              <button
                onClick={clearFilters}
                className="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors duration-200"
              >
                Clear Filters
              </button>
            </div>
          </div>
        </motion.div>

        {/* Action Buttons */}
        <motion.div 
          className="flex flex-wrap gap-4 mb-8"
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.3 }}
        >
          <button
            onClick={handleAddCat}
            className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-2xl font-semibold hover:from-purple-700 hover:to-pink-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            <Plus className="w-5 h-5" />
            Add New Cat
          </button>

          <button className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl font-semibold hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
            <Target className="w-5 h-5" />
            Training Mode
          </button>

          <button className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-2xl font-semibold hover:from-green-700 hover:to-emerald-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
            <Award className="w-5 h-5" />
            View Achievements
          </button>
        </motion.div>

        {/* Cat Grid/List */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.4 }}
        >
          {filteredCats.length > 0 ? (
            <div className={viewMode === 'grid' 
              ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6' 
              : 'space-y-4'
            }>
              <AnimatePresence>
                {filteredCats.map((cat, index) => (
                  <motion.div
                    key={cat.id}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -20 }}
                    transition={{ duration: 0.4, delay: index * 0.1 }}
                  >
                    <CatCard
                      cat={cat}
                      onEdit={handleEdit}
                      onDelete={handleDelete}
                      onCare={handleCare}
                      onPlay={handlePlay}
                    />
                  </motion.div>
                ))}
              </AnimatePresence>
            </div>
          ) : (
            <div className="text-center py-16">
              <div className="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                <Search className="w-12 h-12 text-gray-400" />
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-2">No cats found</h3>
              <p className="text-gray-600 mb-6">
                {searchTerm || Object.values(selectedFilters).some(f => f) 
                  ? 'Try adjusting your search or filters'
                  : 'Get started by adding your first cat!'
                }
              </p>
              {!searchTerm && !Object.values(selectedFilters).some(f => f) && (
                <button
                  onClick={handleAddCat}
                  className="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-2xl font-semibold hover:from-purple-700 hover:to-pink-700 transform hover:scale-105 transition-all duration-200"
                >
                  Add Your First Cat
                </button>
              )}
            </div>
          )}
        </motion.div>

        {/* Cat Care Tips */}
        <motion.div 
          className="mt-16 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-3xl p-8 border border-blue-100"
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.5 }}
        >
          <div className="text-center mb-8">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">🐾 Cat Care Tips</h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              Keep your feline friends happy and healthy with these expert tips
            </p>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="text-center">
              <div className="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                <Heart className="w-8 h-8 text-blue-600" />
              </div>
              <h3 className="text-lg font-semibold text-gray-900 mb-2">Health Monitoring</h3>
              <p className="text-gray-600 text-sm">
                Regularly check your cat's health stats and schedule vet visits for optimal care.
              </p>
            </div>
            
            <div className="text-center">
              <div className="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                <Activity className="w-8 h-8 text-green-600" />
              </div>
              <h3 className="text-lg font-semibold text-gray-900 mb-2">Play & Exercise</h3>
              <p className="text-gray-600 text-sm">
                Engage in daily play sessions to keep your cat physically and mentally stimulated.
              </p>
            </div>
            
            <div className="text-center">
              <div className="w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center">
                <Star className="w-8 h-8 text-purple-600" />
              </div>
              <h3 className="text-lg font-semibold text-gray-900 mb-2">Social Bonding</h3>
              <p className="text-gray-600 text-sm">
                Spend quality time together to strengthen your bond and boost happiness levels.
              </p>
            </div>
          </div>
        </motion.div>
      </div>

      {/* Floating Action Button */}
      <motion.button
        onClick={handleAddCat}
        className="fixed bottom-8 right-8 w-16 h-16 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-200 z-50"
        whileHover={{ scale: 1.1 }}
        whileTap={{ scale: 0.9 }}
      >
        <Plus className="w-8 h-8 mx-auto" />
      </motion.button>
    </div>
  );
};

export default CatManagement;

export interface Cat {
  id: string;
  name: string;
  breed?: string;
  color?: string;
  age?: number;
  health: number;
  happiness: number;
  energy: number;
  hunger: number;
  cleanliness?: number;
  ownerId: string;
  createdAt: string;
  updatedAt: string;
  personality?: CatPersonality;
  genetics?: CatGenetics;
  achievements?: Achievement[];
  equipment?: Equipment[];
  status?: CatStatus;
}

export interface CatPersonality {
  openness: number;
  conscientiousness: number;
  extraversion: number;
  agreeableness: number;
  neuroticism: number;
  traits: string[];
  preferences: CatPreferences;
}

export interface CatPreferences {
  favoriteToys: string[];
  favoriteFood: string[];
  favoriteActivities: string[];
  socialLevel: 'very_social' | 'social' | 'neutral' | 'independent' | 'very_independent';
  energyLevel: 'very_high' | 'high' | 'moderate' | 'low' | 'very_low';
  playStyle: 'aggressive' | 'playful' | 'gentle' | 'curious' | 'cautious';
}

export interface CatGenetics {
  breed: string;
  rarity: 'common' | 'uncommon' | 'rare' | 'epic' | 'legendary';
  mutations: GeneticMutation[];
  inheritedTraits: string[];
  generation: number;
  parents?: string[];
}

export interface GeneticMutation {
  type: 'positive' | 'negative' | 'neutral';
  name: string;
  description: string;
  effect: string;
  rarity: number;
}

export interface Achievement {
  id: string;
  name: string;
  description: string;
  icon: string;
  unlockedAt: string;
  rarity: 'common' | 'uncommon' | 'rare' | 'epic' | 'legendary';
  points: number;
}

export interface Equipment {
  id: string;
  name: string;
  type: 'collar' | 'tag' | 'accessory' | 'toy' | 'furniture';
  rarity: 'common' | 'uncommon' | 'rare' | 'epic' | 'legendary';
  stats: EquipmentStats;
  equipped: boolean;
  durability: number;
  maxDurability: number;
}

export interface EquipmentStats {
  health?: number;
  happiness?: number;
  energy?: number;
  hunger?: number;
  cleanliness?: number;
  social?: number;
  intelligence?: number;
  agility?: number;
  strength?: number;
}

export interface CatStatus {
  isOnline: boolean;
  lastSeen: string;
  currentActivity?: string;
  location?: string;
  mood: 'happy' | 'content' | 'neutral' | 'sad' | 'angry' | 'excited' | 'tired';
  energy: 'full' | 'high' | 'moderate' | 'low' | 'exhausted';
  hunger: 'full' | 'satisfied' | 'hungry' | 'very_hungry' | 'starving';
  cleanliness: 'clean' | 'slightly_dirty' | 'dirty' | 'very_dirty' | 'filthy';
}

export interface CatBehavior {
  catId: string;
  timestamp: string;
  activity: string;
  duration: number;
  intensity: number;
  location: string;
  socialInteraction: boolean;
  mood: string;
  energyExpended: number;
}

export interface CatHealth {
  catId: string;
  timestamp: string;
  health: number;
  happiness: number;
  energy: number;
  hunger: number;
  cleanliness: number;
  weight: number;
  temperature: number;
  heartRate: number;
  notes?: string;
}

export interface CatTraining {
  catId: string;
  skillId: string;
  skillName: string;
  currentLevel: number;
  maxLevel: number;
  experience: number;
  experienceToNext: number;
  lastTrained: string;
  trainingHistory: TrainingSession[];
}

export interface TrainingSession {
  id: string;
  timestamp: string;
  skill: string;
  duration: number;
  success: boolean;
  experienceGained: number;
  notes?: string;
}

export interface CatSocial {
  catId: string;
  friends: CatFriend[];
  enemies: CatEnemy[];
  socialScore: number;
  reputation: number;
  lastInteraction: string;
  socialHistory: SocialInteraction[];
}

export interface CatFriend {
  catId: string;
  name: string;
  friendshipLevel: number;
  lastPlayed: string;
  compatibility: number;
}

export interface CatEnemy {
  catId: string;
  name: string;
  conflictLevel: number;
  lastConflict: string;
  reason: string;
}

export interface SocialInteraction {
  id: string;
  timestamp: string;
  otherCatId: string;
  otherCatName: string;
  type: 'play' | 'grooming' | 'sharing' | 'conflict' | 'bonding';
  duration: number;
  outcome: 'positive' | 'neutral' | 'negative';
  notes?: string;
}

export interface CatInventory {
  catId: string;
  items: InventoryItem[];
  currency: {
    coins: number;
    gems: number;
    tokens: number;
  };
  storage: {
    used: number;
    max: number;
  };
}

export interface InventoryItem {
  id: string;
  name: string;
  type: string;
  quantity: number;
  rarity: string;
  description: string;
  icon: string;
  usable: boolean;
  tradeable: boolean;
  stackable: boolean;
}

export interface CatQuest {
  id: string;
  catId: string;
  title: string;
  description: string;
  type: 'daily' | 'weekly' | 'achievement' | 'story' | 'event';
  status: 'active' | 'completed' | 'failed' | 'expired';
  progress: number;
  maxProgress: number;
  rewards: QuestReward[];
  expiresAt?: string;
  startedAt: string;
  completedAt?: string;
}

export interface QuestReward {
  type: 'coins' | 'gems' | 'experience' | 'item' | 'achievement';
  amount: number;
  itemId?: string;
  itemName?: string;
}

export interface CatStats {
  catId: string;
  level: number;
  experience: number;
  experienceToNext: number;
  totalExperience: number;
  gamesPlayed: number;
  gamesWon: number;
  winRate: number;
  totalPlayTime: number;
  achievementsUnlocked: number;
  friendsMade: number;
  itemsCollected: number;
  questsCompleted: number;
  lastUpdated: string;
}

export interface CatAnalytics {
  catId: string;
  period: 'daily' | 'weekly' | 'monthly' | 'yearly';
  startDate: string;
  endDate: string;
  metrics: {
    health: number[];
    happiness: number[];
    energy: number[];
    hunger: number[];
    cleanliness: number[];
    social: number[];
    activity: number[];
  };
  trends: {
    health: 'improving' | 'stable' | 'declining';
    happiness: 'improving' | 'stable' | 'declining';
    energy: 'improving' | 'stable' | 'declining';
    social: 'improving' | 'stable' | 'declining';
  };
  insights: string[];
  recommendations: string[];
}

export interface CatBreeding {
  catId: string;
  gender: 'male' | 'female';
  fertility: number;
  offspring: string[];
  parents: string[];
  generation: number;
  breedable: boolean;
  lastBred?: string;
  breedingCooldown: number;
  preferredPartners: string[];
  breedingHistory: BreedingRecord[];
}

export interface BreedingRecord {
  id: string;
  partnerId: string;
  partnerName: string;
  timestamp: string;
  success: boolean;
  offspringId?: string;
  offspringName?: string;
  notes?: string;
}

export interface CatNFT {
  catId: string;
  tokenId: string;
  contractAddress: string;
  blockchain: 'ethereum' | 'polygon' | 'binance' | 'solana';
  metadata: {
    name: string;
    description: string;
    image: string;
    attributes: NFTAttribute[];
  };
  ownership: {
    owner: string;
    previousOwners: string[];
    transferHistory: NFTTransfer[];
  };
  market: {
    listed: boolean;
    price?: number;
    currency?: string;
    listingDate?: string;
  };
}

export interface NFTAttribute {
  trait_type: string;
  value: string;
  rarity: number;
}

export interface NFTTransfer {
  from: string;
  to: string;
  timestamp: string;
  transactionHash: string;
  blockNumber: number;
}

export interface CatVR {
  catId: string;
  avatar: VRAvatar;
  worldAccess: string[];
  vrStats: {
    totalVrTime: number;
    worldsVisited: number;
    friendsMet: number;
    itemsCollected: number;
    achievements: string[];
  };
  vrPreferences: {
    favoriteWorlds: string[];
    preferredActivities: string[];
    socialSettings: {
      voiceChat: boolean;
      textChat: boolean;
      gestures: boolean;
      haptics: boolean;
    };
  };
}

export interface VRAvatar {
  model: string;
  customization: {
    furColor: string;
    eyeColor: string;
    accessories: string[];
    animations: string[];
  };
  capabilities: {
    flying: boolean;
    swimming: boolean;
    climbing: boolean;
    teleporting: boolean;
  };
}

export interface CatAI {
  catId: string;
  personalityModel: string;
  behaviorPatterns: string[];
  learningHistory: AILearningRecord[];
  predictions: AIPrediction[];
  recommendations: AIRecommendation[];
  lastAnalysis: string;
  confidence: number;
}

export interface AILearningRecord {
  id: string;
  timestamp: string;
  behavior: string;
  outcome: string;
  learned: boolean;
  confidence: number;
}

export interface AIPrediction {
  id: string;
  timestamp: string;
  type: 'health' | 'behavior' | 'social' | 'performance';
  prediction: string;
  confidence: number;
  timeframe: string;
  factors: string[];
}

export interface AIRecommendation {
  id: string;
  timestamp: string;
  category: 'health' | 'behavior' | 'social' | 'training' | 'care';
  title: string;
  description: string;
  priority: 'low' | 'medium' | 'high' | 'critical';
  actionable: boolean;
  implemented: boolean;
  implementedAt?: string;
  effectiveness?: number;
}

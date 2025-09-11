export interface CatNFT {
  id: string;
  tokenId: string;
  contractAddress: string;
  chainId: number;
  owner: string;
  name: string;
  description: string;
  image: string;
  animationUrl?: string;
  externalUrl?: string;
  
  // Cat-specific metadata
  attributes: CatAttribute[];
  breed: string;
  generation: number;
  rarity: 'common' | 'uncommon' | 'rare' | 'epic' | 'legendary';
  
  // Genetics
  genetics: {
    dna: string;
    dominantTraits: string[];
    recessiveTraits: string[];
    mutations: string[];
  };
  
  // Game stats
  stats: {
    level: number;
    experience: number;
    happiness: number;
    health: number;
    energy: number;
    intelligence: number;
    agility: number;
    charisma: number;
  };
  
  // Breeding
  breeding: {
    isBreedingEnabled: boolean;
    breedingCooldown: number;
    lastBreedingTime?: string;
    offspringCount: number;
    maxOffspring: number;
  };
  
  // Market data
  marketData?: {
    isListed: boolean;
    price?: string;
    currency: 'ETH' | 'MATIC' | 'PURR';
    listedAt?: string;
    lastSalePrice?: string;
    lastSaleDate?: string;
  };
  
  // Metadata
  createdAt: string;
  updatedAt: string;
  mintedAt: string;
  mintedBy: string;
}

export interface CatAttribute {
  traitType: string;
  value: string | number;
  displayType?: 'string' | 'number' | 'date' | 'boost_number' | 'boost_percentage';
  maxValue?: number;
  rarity?: number; // 0-100, lower is rarer
}

export interface NFTMarketplaceListing {
  id: string;
  tokenId: string;
  contractAddress: string;
  seller: string;
  price: string;
  currency: 'ETH' | 'MATIC' | 'PURR';
  status: 'active' | 'sold' | 'cancelled' | 'expired';
  listedAt: string;
  expiresAt?: string;
  soldAt?: string;
  buyer?: string;
  
  // Associated NFT data
  nft: CatNFT;
  
  // Transaction data
  transactionHash?: string;
  blockNumber?: number;
}

export interface BreedingPair {
  id: string;
  parent1: CatNFT;
  parent2: CatNFT;
  owner: string;
  status: 'pending' | 'breeding' | 'completed' | 'failed';
  startedAt: string;
  completedAt?: string;
  offspring?: CatNFT;
  
  // Breeding costs
  breedingFee: string;
  currency: 'ETH' | 'MATIC' | 'PURR';
  
  // Genetic prediction
  predictedTraits: {
    traitType: string;
    probability: number;
    possibleValues: string[];
  }[];
  
  // Transaction data
  transactionHash?: string;
  blockNumber?: number;
}

export interface NFTCollection {
  id: string;
  name: string;
  symbol: string;
  contractAddress: string;
  chainId: number;
  description: string;
  image: string;
  externalUrl?: string;
  
  // Collection stats
  totalSupply: number;
  ownersCount: number;
  floorPrice?: string;
  totalVolume: string;
  currency: 'ETH' | 'MATIC' | 'PURR';
  
  // Market data
  listings: number;
  sales24h: number;
  volume24h: string;
  priceChange24h: number;
  
  // Metadata
  createdAt: string;
  creator: string;
  royaltyFee: number; // Percentage (0-10)
  royaltyRecipient: string;
}

export interface NFTActivity {
  id: string;
  type: 'mint' | 'transfer' | 'sale' | 'listing' | 'breeding' | 'evolution';
  tokenId: string;
  contractAddress: string;
  from: string;
  to: string;
  price?: string;
  currency?: 'ETH' | 'MATIC' | 'PURR';
  transactionHash: string;
  blockNumber: number;
  timestamp: string;
  
  // Additional context based on activity type
  metadata?: Record<string, any>;
}

// Smart contract interfaces
export interface CatNFTContract {
  address: string;
  chainId: number;
  
  // Read functions
  balanceOf(owner: string): Promise<number>;
  ownerOf(tokenId: string): Promise<string>;
  tokenURI(tokenId: string): Promise<string>;
  totalSupply(): Promise<number>;
  
  // Write functions
  mint(to: string, metadata: string): Promise<string>;
  breed(parent1: string, parent2: string): Promise<string>;
  evolve(tokenId: string): Promise<string>;
}

export interface MarketplaceContract {
  address: string;
  chainId: number;
  
  // Read functions
  getlisting(tokenId: string): Promise<NFTMarketplaceListing | null>;
  getActiveListings(limit?: number, offset?: number): Promise<NFTMarketplaceListing[]>;
  
  // Write functions
  createListing(tokenId: string, price: string, currency: string, duration: number): Promise<string>;
  buyNFT(tokenId: string): Promise<string>;
  cancelListing(tokenId: string): Promise<string>;
}
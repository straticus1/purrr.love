<?php
/**
 * ⛓️ Purrr.love Blockchain-based Cat Ownership System
 * NFT integration, smart contracts, and decentralized verification
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Blockchain networks supported
 */
define('BLOCKCHAIN_NETWORKS', [
    'ethereum' => [
        'name' => 'Ethereum',
        'chain_id' => 1,
        'rpc_url' => 'https://mainnet.infura.io/v3/',
        'contract_address' => '0x...', // Mainnet contract
        'gas_limit' => 300000,
        'gas_price' => 'auto'
    ],
    'polygon' => [
        'name' => 'Polygon',
        'chain_id' => 137,
        'rpc_url' => 'https://polygon-rpc.com/',
        'contract_address' => '0x...', // Polygon contract
        'gas_limit' => 200000,
        'gas_price' => 'auto'
    ],
    'binance_smart_chain' => [
        'name' => 'Binance Smart Chain',
        'chain_id' => 56,
        'rpc_url' => 'https://bsc-dataseed.binance.org/',
        'contract_address' => '0x...', // BSC contract
        'gas_limit' => 150000,
        'gas_price' => 'auto'
    ],
    'solana' => [
        'name' => 'Solana',
        'chain_id' => 101,
        'rpc_url' => 'https://api.mainnet-beta.solana.com',
        'program_id' => '...', // Solana program ID
        'gas_limit' => 0, // Solana doesn't use gas
        'gas_price' => 0
    ]
]);

/**
 * NFT metadata standards
 */
define('NFT_METADATA_STANDARDS', [
    'erc721' => 'Ethereum ERC-721',
    'erc1155' => 'Ethereum ERC-1155',
    'bep721' => 'BSC BEP-721',
    'bep1155' => 'BSC BEP-1155',
    'spl_token' => 'Solana SPL Token'
]);

/**
 * Blockchain Ownership System Class
 */
class BlockchainOwnershipSystem {
    private $pdo;
    private $config;
    private $web3Providers = [];
    
    public function __construct() {
        $this->pdo = get_db();
        $this->config = [
            'default_network' => 'ethereum',
            'auto_mint_enabled' => true,
            'gas_optimization' => true,
            'batch_minting' => true,
            'metadata_ipfs' => true,
            'royalty_percentage' => 2.5, // 2.5% royalty
            'max_supply_per_cat' => 1, // 1 NFT per cat
            'verification_required' => true
        ];
        
        $this->initializeWeb3Providers();
    }
    
    /**
     * Initialize Web3 providers for different networks
     */
    private function initializeWeb3Providers() {
        foreach (BLOCKCHAIN_NETWORKS as $network => $config) {
            if (isset($config['rpc_url']) && !empty($config['rpc_url'])) {
                $this->web3Providers[$network] = $this->createWeb3Provider($config);
            }
        }
    }
    
    /**
     * Create Web3 provider for network
     */
    private function createWeb3Provider($networkConfig) {
        // This would integrate with actual Web3 libraries
        // For now, return a mock provider
        return [
            'network' => $networkConfig['name'],
            'rpc_url' => $networkConfig['rpc_url'],
            'contract_address' => $networkConfig['contract_address'] ?? null,
            'connected' => true
        ];
    }
    
    /**
     * Mint NFT for cat
     */
    public function mintCatNFT($catId, $userId, $network = null, $metadata = []) {
        try {
            // Validate cat ownership
            if (!$this->validateCatOwnership($catId, $userId)) {
                throw new Exception('Cat ownership validation failed');
            }
            
            // Check if cat already has NFT
            if ($this->catHasNFT($catId)) {
                throw new Exception('Cat already has an NFT');
            }
            
            // Get cat information
            $cat = $this->getCatInformation($catId);
            if (!$cat) {
                throw new Exception('Cat not found');
            }
            
            // Set default network if not specified
            if (!$network) {
                $network = $this->config['default_network'];
            }
            
            // Validate network
            if (!isset(BLOCKCHAIN_NETWORKS[$network])) {
                throw new Exception("Invalid blockchain network: $network");
            }
            
            // Generate NFT metadata
            $nftMetadata = $this->generateNFTMetadata($cat, $metadata);
            
            // Mint NFT on blockchain
            $mintResult = $this->mintOnBlockchain($network, $userId, $nftMetadata);
            
            // Store NFT record in database
            $nftId = $this->storeNFTRecord($catId, $userId, $network, $mintResult, $nftMetadata);
            
            // Update cat with NFT information
            $this->updateCatNFTInfo($catId, $nftId);
            
            // Log NFT minting
            logSecurityEvent('nft_minted', [
                'user_id' => $userId,
                'cat_id' => $catId,
                'nft_id' => $nftId,
                'network' => $network,
                'token_id' => $mintResult['token_id'],
                'transaction_hash' => $mintResult['transaction_hash']
            ]);
            
            // Trigger webhook event
            triggerWebhookEvent('blockchain.nft_minted', [
                'user_id' => $userId,
                'cat_id' => $catId,
                'nft_id' => $nftId,
                'network' => $network,
                'mint_result' => $mintResult
            ]);
            
            return [
                'nft_id' => $nftId,
                'network' => $network,
                'token_id' => $mintResult['token_id'],
                'transaction_hash' => $mintResult['transaction_hash'],
                'metadata' => $nftMetadata,
                'status' => 'minted'
            ];
            
        } catch (Exception $e) {
            error_log("Error minting cat NFT: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Transfer NFT ownership
     */
    public function transferNFTOwnership($nftId, $fromUserId, $toUserId, $network = null) {
        try {
            // Validate NFT ownership
            if (!$this->validateNFTOwnership($nftId, $fromUserId)) {
                throw new Exception('NFT ownership validation failed');
            }
            
            // Get NFT information
            $nft = $this->getNFTInformation($nftId);
            if (!$nft) {
                throw new Exception('NFT not found');
            }
            
            // Set network if not specified
            if (!$network) {
                $network = $nft['network'];
            }
            
            // Validate network
            if (!isset(BLOCKCHAIN_NETWORKS[$network])) {
                throw new Exception("Invalid blockchain network: $network");
            }
            
            // Transfer NFT on blockchain
            $transferResult = $this->transferOnBlockchain($network, $nft['token_id'], $fromUserId, $toUserId);
            
            // Update NFT ownership in database
            $this->updateNFTOwnership($nftId, $toUserId, $transferResult);
            
            // Log NFT transfer
            logSecurityEvent('nft_transferred', [
                'nft_id' => $nftId,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'network' => $network,
                'transaction_hash' => $transferResult['transaction_hash']
            ]);
            
            // Trigger webhook event
            triggerWebhookEvent('blockchain.nft_transferred', [
                'nft_id' => $nftId,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'network' => $network,
                'transfer_result' => $transferResult
            ]);
            
            return [
                'nft_id' => $nftId,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'network' => $network,
                'transaction_hash' => $transferResult['transaction_hash'],
                'status' => 'transferred'
            ];
            
        } catch (Exception $e) {
            error_log("Error transferring NFT ownership: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Verify NFT ownership on blockchain
     */
    public function verifyNFTOwnership($nftId, $userId) {
        try {
            // Get NFT information
            $nft = $this->getNFTInformation($nftId);
            if (!$nft) {
                throw new Exception('NFT not found');
            }
            
            // Verify on blockchain
            $verificationResult = $this->verifyOnBlockchain($nft['network'], $nft['token_id'], $userId);
            
            // Update verification status
            $this->updateVerificationStatus($nftId, $verificationResult);
            
            return [
                'nft_id' => $nftId,
                'user_id' => $userId,
                'verified' => $verificationResult['verified'],
                'blockchain_owner' => $verificationResult['blockchain_owner'],
                'database_owner' => $nft['user_id'],
                'verification_timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log("Error verifying NFT ownership: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get user's NFT collection
     */
    public function getUserNFTCollection($userId, $network = null) {
        try {
            $sql = "
                SELECT 
                    n.*,
                    c.name as cat_name,
                    c.breed as cat_breed,
                    c.personality as cat_personality,
                    c.image_url as cat_image
                FROM nfts n
                JOIN cats c ON n.cat_id = c.id
                WHERE n.user_id = ?
            ";
            
            $params = [$userId];
            
            if ($network) {
                $sql .= " AND n.network = ?";
                $params[] = $network;
            }
            
            $sql .= " ORDER BY n.created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $nfts = [];
            while ($row = $stmt->fetch()) {
                $nfts[] = [
                    'nft_id' => $row['id'],
                    'token_id' => $row['token_id'],
                    'network' => $row['network'],
                    'contract_address' => $row['contract_address'],
                    'cat_id' => $row['cat_id'],
                    'cat_name' => $row['cat_name'],
                    'cat_breed' => $row['cat_breed'],
                    'cat_personality' => json_decode($row['cat_personality'], true),
                    'cat_image' => $row['cat_image'],
                    'metadata' => json_decode($row['metadata'], true),
                    'created_at' => $row['created_at'],
                    'last_verified' => $row['last_verified']
                ];
            }
            
            return $nfts;
            
        } catch (Exception $e) {
            error_log("Error getting user NFT collection: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get NFT marketplace listings
     */
    public function getNFTMarketplaceListings($filters = []) {
        try {
            $sql = "
                SELECT 
                    l.*,
                    n.token_id,
                    n.network,
                    n.contract_address,
                    c.name as cat_name,
                    c.breed as cat_breed,
                    c.image_url as cat_image,
                    u.username as seller_name
                FROM nft_listings l
                JOIN nfts n ON l.nft_id = n.id
                JOIN cats c ON n.cat_id = c.id
                JOIN users u ON l.seller_id = u.id
                WHERE l.status = 'active'
            ";
            
            $params = [];
            $conditions = [];
            
            if (isset($filters['network'])) {
                $conditions[] = "n.network = ?";
                $params[] = $filters['network'];
            }
            
            if (isset($filters['min_price'])) {
                $conditions[] = "l.price >= ?";
                $params[] = $filters['min_price'];
            }
            
            if (isset($filters['max_price'])) {
                $conditions[] = "l.price <= ?";
                $params[] = $filters['max_price'];
            }
            
            if (isset($filters['breed'])) {
                $conditions[] = "c.breed = ?";
                $params[] = $filters['breed'];
            }
            
            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY l.created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $listings = [];
            while ($row = $stmt->fetch()) {
                $listings[] = [
                    'listing_id' => $row['id'],
                    'nft_id' => $row['nft_id'],
                    'token_id' => $row['token_id'],
                    'network' => $row['network'],
                    'contract_address' => $row['contract_address'],
                    'cat_name' => $row['cat_name'],
                    'cat_breed' => $row['cat_breed'],
                    'cat_image' => $row['cat_image'],
                    'price' => $row['price'],
                    'currency' => $row['currency'],
                    'seller_id' => $row['seller_id'],
                    'seller_name' => $row['seller_name'],
                    'created_at' => $row['created_at']
                ];
            }
            
            return $listings;
            
        } catch (Exception $e) {
            error_log("Error getting NFT marketplace listings: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create NFT marketplace listing
     */
    public function createNFTListing($nftId, $sellerId, $price, $currency = 'ETH', $duration = 30) {
        try {
            // Validate NFT ownership
            if (!$this->validateNFTOwnership($nftId, $sellerId)) {
                throw new Exception('NFT ownership validation failed');
            }
            
            // Check if NFT is already listed
            if ($this->isNFTListed($nftId)) {
                throw new Exception('NFT is already listed');
            }
            
            // Create listing
            $stmt = $this->pdo->prepare("
                INSERT INTO nft_listings 
                (nft_id, seller_id, price, currency, duration_days, status, expires_at, created_at)
                VALUES (?, ?, ?, ?, ?, 'active', ?, ?)
            ");
            
            $expiresAt = date('Y-m-d H:i:s', strtotime("+$duration days"));
            
            $stmt->execute([
                $nftId,
                $sellerId,
                $price,
                $currency,
                $duration,
                $expiresAt,
                date('Y-m-d H:i:s')
            ]);
            
            $listingId = $this->pdo->lastInsertId();
            
            // Log listing creation
            logSecurityEvent('nft_listing_created', [
                'listing_id' => $listingId,
                'nft_id' => $nftId,
                'seller_id' => $sellerId,
                'price' => $price,
                'currency' => $currency
            ]);
            
            return [
                'listing_id' => $listingId,
                'nft_id' => $nftId,
                'seller_id' => $sellerId,
                'price' => $price,
                'currency' => $currency,
                'duration_days' => $duration,
                'expires_at' => $expiresAt,
                'status' => 'active'
            ];
            
        } catch (Exception $e) {
            error_log("Error creating NFT listing: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get blockchain statistics
     */
    public function getBlockchainStats() {
        try {
            $stats = [];
            
            // Total NFTs minted
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM nfts");
            $stmt->execute();
            $stats['total_nfts'] = $stmt->fetchColumn();
            
            // NFTs by network
            $stmt = $this->pdo->prepare("
                SELECT network, COUNT(*) as count
                FROM nfts 
                GROUP BY network
            ");
            $stmt->execute();
            $stats['nfts_by_network'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Active listings
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM nft_listings 
                WHERE status = 'active' AND expires_at > NOW()
            ");
            $stmt->execute();
            $stats['active_listings'] = $stmt->fetchColumn();
            
            // Total trading volume
            $stmt = $this->pdo->prepare("
                SELECT 
                    COALESCE(SUM(price), 0) as total_volume,
                    COUNT(*) as total_trades
                FROM nft_transactions 
                WHERE status = 'completed'
            ");
            $stmt->execute();
            $volumeData = $stmt->fetch();
            $stats['trading_volume'] = $volumeData['total_volume'];
            $stats['total_trades'] = $volumeData['total_trades'];
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting blockchain stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Helper methods
     */
    private function validateCatOwnership($catId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM cats 
                WHERE id = ? AND user_id = ? AND active = 1
            ");
            $stmt->execute([$catId, $userId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function catHasNFT($catId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM nfts WHERE cat_id = ?
            ");
            $stmt->execute([$catId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function getCatInformation($catId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM cats WHERE id = ?
            ");
            $stmt->execute([$catId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function generateNFTMetadata($cat, $additionalMetadata = []) {
        $baseMetadata = [
            'name' => $cat['name'] . ' - Purrr.love Cat',
            'description' => "A unique cat from Purrr.love with breed: {$cat['breed']}",
            'image' => $cat['image_url'],
            'attributes' => [
                'breed' => $cat['breed'],
                'personality' => json_decode($cat['personality'], true),
                'created_at' => $cat['created_at'],
                'rarity_score' => $this->calculateRarityScore($cat)
            ],
            'external_url' => "https://purrr.love/cats/{$cat['id']}",
            'background_color' => 'FFFFFF'
        ];
        
        return array_merge($baseMetadata, $additionalMetadata);
    }
    
    private function calculateRarityScore($cat) {
        // Implement rarity calculation based on cat attributes
        // This is a placeholder for actual rarity logic
        return rand(1, 100);
    }
    
    private function mintOnBlockchain($network, $userId, $metadata) {
        // This would integrate with actual blockchain minting
        // For now, return mock data
        return [
            'token_id' => uniqid('token_'),
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
            'block_number' => rand(1000000, 9999999),
            'gas_used' => rand(100000, 300000),
            'status' => 'success'
        ];
    }
    
    private function storeNFTRecord($catId, $userId, $network, $mintResult, $metadata) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO nfts 
                (cat_id, user_id, network, token_id, contract_address, metadata, 
                 transaction_hash, block_number, gas_used, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $catId,
                $userId,
                $network,
                $mintResult['token_id'],
                BLOCKCHAIN_NETWORKS[$network]['contract_address'],
                json_encode($metadata),
                $mintResult['transaction_hash'],
                $mintResult['block_number'],
                $mintResult['gas_used'],
                date('Y-m-d H:i:s')
            ]);
            
            return $this->pdo->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Error storing NFT record: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function updateCatNFTInfo($catId, $nftId) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE cats SET nft_id = ?, updated_at = ? WHERE id = ?
            ");
            $stmt->execute([$nftId, date('Y-m-d H:i:s'), $catId]);
        } catch (Exception $e) {
            error_log("Error updating cat NFT info: " . $e->getMessage());
        }
    }
    
    private function validateNFTOwnership($nftId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM nfts WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$nftId, $userId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function getNFTInformation($nftId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM nfts WHERE id = ?
            ");
            $stmt->execute([$nftId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function transferOnBlockchain($network, $tokenId, $fromUserId, $toUserId) {
        // This would integrate with actual blockchain transfer
        // For now, return mock data
        return [
            'transaction_hash' => '0x' . bin2hex(random_bytes(32)),
            'block_number' => rand(1000000, 9999999),
            'gas_used' => rand(50000, 150000),
            'status' => 'success'
        ];
    }
    
    private function updateNFTOwnership($nftId, $newUserId, $transferResult) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE nfts 
                SET user_id = ?, updated_at = ? 
                WHERE id = ?
            ");
            $stmt->execute([$newUserId, date('Y-m-d H:i:s'), $nftId]);
        } catch (Exception $e) {
            error_log("Error updating NFT ownership: " . $e->getMessage());
        }
    }
    
    private function verifyOnBlockchain($network, $tokenId, $userId) {
        // This would integrate with actual blockchain verification
        // For now, return mock data
        return [
            'verified' => true,
            'blockchain_owner' => $userId,
            'verification_timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function updateVerificationStatus($nftId, $verificationResult) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE nfts 
                SET last_verified = ?, verification_status = ? 
                WHERE id = ?
            ");
            
            $verificationStatus = $verificationResult['verified'] ? 'verified' : 'failed';
            
            $stmt->execute([
                $verificationResult['verification_timestamp'],
                $verificationStatus,
                $nftId
            ]);
            
        } catch (Exception $e) {
            error_log("Error updating verification status: " . $e->getMessage());
        }
    }
    
    private function isNFTListed($nftId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM nft_listings 
                WHERE nft_id = ? AND status = 'active' AND expires_at > NOW()
            ");
            $stmt->execute([$nftId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 * Global blockchain ownership system instance
 */
$globalBlockchainOwnershipSystem = new BlockchainOwnershipSystem();

/**
 * Blockchain ownership wrapper functions
 */
function mintCatNFT($catId, $userId, $network = null, $metadata = []) {
    global $globalBlockchainOwnershipSystem;
    return $globalBlockchainOwnershipSystem->mintCatNFT($catId, $userId, $network, $metadata);
}

function transferNFTOwnership($nftId, $fromUserId, $toUserId, $network = null) {
    global $globalBlockchainOwnershipSystem;
    return $globalBlockchainOwnershipSystem->transferNFTOwnership($nftId, $fromUserId, $toUserId, $network);
}

function verifyNFTOwnership($nftId, $userId) {
    global $globalBlockchainOwnershipSystem;
    return $globalBlockchainOwnershipSystem->verifyNFTOwnership($nftId, $userId);
}

function getUserNFTCollection($userId, $network = null) {
    global $globalBlockchainOwnershipSystem;
    return $globalBlockchainOwnershipSystem->getUserNFTCollection($userId, $network);
}

function getNFTMarketplaceListings($filters = []) {
    global $globalBlockchainOwnershipSystem;
    return $globalBlockchainOwnershipSystem->getNFTMarketplaceListings($filters);
}

function createNFTListing($nftId, $sellerId, $price, $currency = 'ETH', $duration = 30) {
    global $globalBlockchainOwnershipSystem;
    return $globalBlockchainOwnershipSystem->createNFTListing($nftId, $sellerId, $price, $currency, $duration);
}

function getBlockchainStats() {
    global $globalBlockchainOwnershipSystem;
    return $globalBlockchainOwnershipSystem->getBlockchainStats();
}
?>

<?php
/**
 * 🐱 Purrr.love - Blockchain & NFT
 */

session_start();
require_once '../includes/functions.php';
require_once '../includes/authentication.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

try {
    $user = getUserById($_SESSION['user_id']);
    if (!$user) {
        session_destroy();
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Get user's cats
$cats = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM cats WHERE owner_id = ? ORDER BY name");
    $stmt->execute([$_SESSION['user_id']]);
    $cats = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Error loading cats: ' . $e->getMessage();
}

$selectedCatId = $_GET['cat_id'] ?? ($cats[0]['id'] ?? null);
$selectedCat = null;
$nftData = null;
$message = '';
$error = '';

if ($selectedCatId) {
    foreach ($cats as $cat) {
        if ($cat['id'] == $selectedCatId) {
            $selectedCat = $cat;
            break;
        }
    }
}

// Handle blockchain actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['blockchain_action'])) {
        $blockchainAction = $_POST['blockchain_action'];
        
        switch ($blockchainAction) {
            case 'mint_nft':
                if ($selectedCat) {
                    try {
                        // Simulate NFT minting
                        $nftId = 'NFT_' . strtoupper(substr(md5($selectedCat['id'] . time()), 0, 8));
                        $tokenId = rand(1000000, 9999999);
                        $contractAddress = '0x' . strtoupper(substr(md5('purrr_love_contract'), 0, 40));
                        
                        $nftData = [
                            'nft_id' => $nftId,
                            'token_id' => $tokenId,
                            'contract_address' => $contractAddress,
                            'cat_id' => $selectedCat['id'],
                            'cat_name' => $selectedCat['name'],
                            'owner_address' => '0x' . strtoupper(substr(md5($user['email']), 0, 40)),
                            'mint_date' => date('Y-m-d H:i:s'),
                            'metadata' => [
                                'name' => $selectedCat['name'] . ' #' . $tokenId,
                                'description' => 'Unique digital cat NFT on Purrr.love blockchain',
                                'image' => 'https://api.purrr.love/cats/' . $selectedCat['id'] . '/image',
                                'attributes' => [
                                    'Breed' => ucfirst(str_replace('_', ' ', $selectedCat['breed'])),
                                    'Color' => ucfirst(str_replace('_', ' ', $selectedCat['color'])),
                                    'Health' => $selectedCat['health'],
                                    'Happiness' => $selectedCat['happiness'],
                                    'Rarity' => calculateRarity($selectedCat)
                                ]
                            ]
                        ];
                        
                        $message = "NFT minted successfully for " . htmlspecialchars($selectedCat['name']) . "!";
                        
                    } catch (Exception $e) {
                        $error = 'Error minting NFT: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'transfer_nft':
                $recipientEmail = $_POST['recipient_email'] ?? '';
                $nftId = $_POST['nft_id'] ?? '';
                
                if ($recipientEmail && $nftId) {
                    try {
                        // Simulate NFT transfer
                        $message = "NFT transfer initiated to " . htmlspecialchars($recipientEmail) . "!";
                        
                        // In a real implementation, this would update the blockchain
                        // and transfer ownership records
                        
                    } catch (Exception $e) {
                        $error = 'Error transferring NFT: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please provide recipient email and NFT ID';
                }
                break;
                
            case 'configure_provider':
                $network = $_POST['network'] ?? '';
                $rpcUrl = trim($_POST['rpc_url'] ?? '');
                $projectId = trim($_POST['project_id'] ?? '');
                
                if ($network && $rpcUrl) {
                    try {
                        // Simulate provider configuration
                        $message = "Web3 provider configured for $network successfully!";
                        
                        // In a real implementation, this would save the configuration
                        // and test the connection
                        
                    } catch (Exception $e) {
                        $error = 'Error configuring provider: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please provide network and RPC URL';
                }
                break;
                
            case 'deploy_contract':
                $contractType = $_POST['contract_type'] ?? '';
                $network = $_POST['deploy_network'] ?? '';
                $contractName = trim($_POST['contract_name'] ?? '');
                
                if ($contractType && $network && $contractName) {
                    try {
                        // Simulate contract deployment
                        $contractAddress = '0x' . strtoupper(substr(md5($contractName . time()), 0, 40));
                        $message = "Smart contract '$contractName' deployed to $network at $contractAddress!";
                        
                        // In a real implementation, this would deploy the actual contract
                        // and save the deployment details
                        
                    } catch (Exception $e) {
                        $error = 'Error deploying contract: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please provide contract type, network, and name';
                }
                break;
                
            case 'upload_to_ipfs':
                $metadata = $_POST['metadata'] ?? '';
                $catId = $_POST['cat_id'] ?? '';
                
                if ($metadata && $catId) {
                    try {
                        // Simulate IPFS upload
                        $ipfsHash = 'Qm' . strtoupper(substr(md5($metadata . time()), 0, 44));
                        $message = "Metadata uploaded to IPFS successfully! Hash: $ipfsHash";
                        
                        // In a real implementation, this would upload to actual IPFS
                        // and return the IPFS hash
                        
                    } catch (Exception $e) {
                        $error = 'Error uploading to IPFS: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please provide metadata and cat ID';
                }
                break;
        }
    }
}

function calculateRarity($cat) {
    $rarityScore = 0;
    
    // Health contributes to rarity
    if ($cat['health'] >= 90) $rarityScore += 30;
    elseif ($cat['health'] >= 80) $rarityScore += 20;
    elseif ($cat['health'] >= 70) $rarityScore += 10;
    
    // Happiness contributes to rarity
    if ($cat['happiness'] >= 90) $rarityScore += 25;
    elseif ($cat['happiness'] >= 80) $rarityScore += 15;
    elseif ($cat['happiness'] >= 70) $rarityScore += 10;
    
    // Energy contributes to rarity
    if ($cat['energy'] >= 90) $rarityScore += 20;
    elseif ($cat['energy'] >= 80) $rarityScore += 15;
    elseif ($cat['energy'] >= 70) $rarityScore += 10;
    
    // Breed rarity (some breeds are more valuable)
    $rareBreeds = ['bengal', 'sphynx', 'ragdoll', 'maine_coon'];
    if (in_array($cat['breed'], $rareBreeds)) {
        $rarityScore += 25;
    }
    
    if ($rarityScore >= 80) return 'Legendary';
    elseif ($rarityScore >= 60) return 'Epic';
    elseif ($rarityScore >= 40) return 'Rare';
    else return 'Common';
}

// Simulate existing NFTs for the user
$userNFTs = [];
if ($selectedCat) {
    // Check if cat already has an NFT
    $existingNFT = [
        'nft_id' => 'NFT_' . strtoupper(substr(md5($selectedCat['id']), 0, 8)),
        'token_id' => rand(1000000, 9999999),
        'contract_address' => '0x' . strtoupper(substr(md5('purrr_love_contract'), 0, 40)),
        'cat_id' => $selectedCat['id'],
        'cat_name' => $selectedCat['name'],
        'owner_address' => '0x' . strtoupper(substr(md5($user['email']), 0, 40)),
        'mint_date' => date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')),
        'rarity' => calculateRarity($selectedCat),
        'floor_price' => rand(0.1, 2.0),
        'last_sale' => rand(0.05, 1.5)
    ];
    
    // 50% chance the cat already has an NFT
    if (rand(1, 2) == 1) {
        $userNFTs[] = $existingNFT;
    }
}

// Blockchain networks
$blockchainNetworks = [
    'ethereum' => 'Ethereum (ETH)',
    'polygon' => 'Polygon (MATIC)',
    'binance' => 'Binance Smart Chain (BNB)',
    'avalanche' => 'Avalanche (AVAX)',
    'arbitrum' => 'Arbitrum (ARB)'
];

// Web3 Provider Configuration
$web3Providers = [
    'ethereum' => [
        'name' => 'Ethereum Mainnet',
        'rpc_url' => 'https://mainnet.infura.io/v3/YOUR_PROJECT_ID',
        'chain_id' => 1,
        'currency_symbol' => 'ETH',
        'block_explorer' => 'https://etherscan.io'
    ],
    'polygon' => [
        'name' => 'Polygon Mainnet',
        'rpc_url' => 'https://polygon-rpc.com',
        'chain_id' => 137,
        'currency_symbol' => 'MATIC',
        'block_explorer' => 'https://polygonscan.com'
    ],
    'binance' => [
        'name' => 'Binance Smart Chain',
        'rpc_url' => 'https://bsc-dataseed.binance.org',
        'chain_id' => 56,
        'currency_symbol' => 'BNB',
        'block_explorer' => 'https://bscscan.com'
    ]
];

// Smart Contract Templates
$smartContractTemplates = [
    'erc721_basic' => [
        'name' => 'Basic ERC-721',
        'description' => 'Standard ERC-721 implementation for cat NFTs',
        'features' => ['Mint', 'Transfer', 'Owner Query']
    ],
    'erc721_advanced' => [
        'name' => 'Advanced ERC-721',
        'description' => 'Enhanced ERC-721 with breeding and evolution',
        'features' => ['Mint', 'Transfer', 'Breeding', 'Evolution', 'Rarity']
    ],
    'erc1155_multi' => [
        'name' => 'ERC-1155 Multi-Token',
        'description' => 'Multi-token standard for cat accessories and items',
        'features' => ['Batch Mint', 'Transfer', 'Metadata URI']
    ]
];

// IPFS Configuration
$ipfsConfig = [
    'gateway' => 'https://ipfs.io/ipfs/',
    'api_endpoint' => 'https://ipfs.infura.io:5001/api/v0',
    'project_id' => 'YOUR_INFURA_IPFS_PROJECT_ID',
    'project_secret' => 'YOUR_INFURA_IPFS_PROJECT_SECRET'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐱 Blockchain & NFT - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b-4 border-purple-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="index.php" class="text-2xl font-bold text-purple-600">
                            🐱 Purrr.love
                        </a>
                    </div>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">
                            <a href="dashboard.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a href="cats.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-cat mr-2"></i>My Cats
                            </a>
                            <a href="games.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-gamepad mr-2"></i>Games
                            </a>
                            <a href="store.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-store mr-2"></i>Store
                            </a>
                            <a href="lost-pet-finder.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-search mr-2"></i>Lost Pet Finder
                            </a>
                            <a href="ml-personality.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-brain mr-2"></i>ML Personality
                            </a>
                            <a href="blockchain-nft.php" class="bg-purple-100 text-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-link mr-2"></i>Blockchain
                            </a>
                            <?php if ($user['role'] === 'admin'): ?>
                            <a href="admin.php" class="text-red-600 hover:text-red-800 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-cog mr-2"></i>Admin
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700 text-sm">
                                Welcome, <?= htmlspecialchars($user['name'] ?? $user['email']) ?>
                            </span>
                            <a href="profile.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                            <a href="index.php?logout=1" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🔗 Blockchain & NFT</h1>
            <p class="text-xl text-gray-600">Own your cats as unique digital assets on the blockchain</p>
        </div>

        <!-- MetaMask Connection Status -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Web3 Connection Status</h2>
                    <p class="text-sm text-gray-600">Connect your MetaMask wallet to interact with blockchain features</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div id="connection-status">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i>Not Connected
                        </span>
                    </div>
                    <button id="connect-metamask" 
                            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                        <i class="fas fa-wallet mr-2"></i>Connect MetaMask
                    </button>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 max-w-2xl mx-auto">
            <div class="flex items-center justify-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 max-w-2xl mx-auto">
            <div class="flex items-center justify-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cat Selection -->
        <?php if (!empty($cats)): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 max-w-4xl mx-auto">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select a Cat for Blockchain Operations</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($cats as $cat): ?>
                <a href="?cat_id=<?= $cat['id'] ?>" 
                   class="block p-4 border-2 rounded-lg transition duration-200 <?= $selectedCatId == $cat['id'] ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300' ?>">
                    <div class="text-center">
                        <div class="text-3xl mb-2">🐱</div>
                        <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($cat['name']) ?></h3>
                        <p class="text-sm text-gray-600"><?= ucfirst(str_replace('_', ' ', $cat['breed'])) ?></p>
                        <div class="mt-2 text-xs text-gray-500">
                            Health: <?= $cat['health'] ?>% | Happiness: <?= $cat['happiness'] ?>%
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Blockchain Operations -->
        <?php if ($selectedCat): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- NFT Minting -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Mint NFT</h2>
                
                <?php if (empty($userNFTs)): ?>
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-4">
                        <i class="fas fa-coins"></i>
                    </div>
                    <p class="text-gray-600 mb-6"><?= htmlspecialchars($selectedCat['name']) ?> doesn't have an NFT yet</p>
                    <form method="POST" class="inline-block">
                        <input type="hidden" name="blockchain_action" value="mint_nft">
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                            <i class="fas fa-mint mr-2"></i>Mint NFT
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-green-900">NFT Already Minted!</p>
                                <p class="text-sm text-green-700">This cat is already a digital asset</p>
                            </div>
                        </div>
                    </div>
                    
                    <?php foreach ($userNFTs as $nft): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-semibold text-gray-900"><?= $nft['nft_id'] ?></h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <?= $nft['rarity'] ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Token ID: <?= $nft['token_id'] ?></p>
                        <p class="text-sm text-gray-600 mb-2">Floor Price: <?= $nft['floor_price'] ?> ETH</p>
                        <p class="text-sm text-gray-600">Last Sale: <?= $nft['last_sale'] ?> ETH</p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- NFT Transfer -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Transfer NFT</h2>
                
                <?php if (!empty($userNFTs)): ?>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="blockchain_action" value="transfer_nft">
                    
                    <div>
                        <label for="nft_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select NFT <span class="text-red-500">*</span>
                        </label>
                        <select id="nft_id" name="nft_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select an NFT to transfer</option>
                            <?php foreach ($userNFTs as $nft): ?>
                            <option value="<?= $nft['nft_id'] ?>"><?= $nft['nft_id'] ?> - <?= $nft['cat_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="recipient_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Recipient Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="recipient_email" name="recipient_email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Enter recipient's email address">
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                        <i class="fas fa-exchange-alt mr-2"></i>Transfer NFT
                    </button>
                </form>
                <?php else: ?>
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-4">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <p class="text-gray-600">No NFTs available for transfer</p>
                    <p class="text-sm text-gray-500 mt-2">Mint an NFT first to enable transfers</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Web3 Provider Configuration -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Web3 Provider Configuration</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="blockchain_action" value="configure_provider">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="network" class="block text-sm font-medium text-gray-700 mb-2">
                            Network <span class="text-red-500">*</span>
                        </label>
                        <select id="network" name="network" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select network</option>
                            <?php foreach ($web3Providers as $key => $provider): ?>
                            <option value="<?= $key ?>"><?= $provider['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="rpc_url" class="block text-sm font-medium text-gray-700 mb-2">
                            RPC URL <span class="text-red-500">*</span>
                        </label>
                        <input type="url" id="rpc_url" name="rpc_url" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="https://mainnet.infura.io/v3/YOUR_PROJECT_ID">
                    </div>
                </div>

                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Project ID (Optional)
                    </label>
                    <input type="text" id="project_id" name="project_id"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Your Infura/Alchemy project ID">
                </div>

                <div class="flex space-x-3">
                    <button type="submit" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                        <i class="fas fa-cog mr-2"></i>Configure Provider
                    </button>
                    <button type="button" id="test-connection"
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                        <i class="fas fa-plug mr-2"></i>Test Connection
                    </button>
                </div>
            </form>
        </div>

        <!-- Smart Contract Deployment -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Smart Contract Deployment</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="blockchain_action" value="deploy_contract">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="contract_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Contract Type <span class="text-red-500">*</span>
                        </label>
                        <select id="contract_type" name="contract_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select contract type</option>
                            <?php foreach ($smartContractTemplates as $key => $template): ?>
                            <option value="<?= $key ?>"><?= $template['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="deploy_network" class="block text-sm font-medium text-gray-700 mb-2">
                            Deploy to Network <span class="text-red-500">*</span>
                        </label>
                        <select id="deploy_network" name="deploy_network" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select network</option>
                            <?php foreach ($web3Providers as $key => $provider): ?>
                            <option value="<?= $key ?>"><?= $provider['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="contract_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Contract Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="contract_name" name="contract_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="e.g., PurrrLoveCats">
                </div>

                <button type="submit" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                    <i class="fas fa-rocket mr-2"></i>Deploy Contract
                </button>
            </form>
        </div>

        <!-- IPFS Metadata Storage -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">IPFS Metadata Storage</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="blockchain_action" value="upload_to_ipfs">
                <input type="hidden" name="cat_id" value="<?= $selectedCat['id'] ?>">
                
                <div>
                    <label for="metadata" class="block text-sm font-medium text-gray-700 mb-2">
                        NFT Metadata (JSON) <span class="text-red-500">*</span>
                    </label>
                    <textarea id="metadata" name="metadata" required rows="6"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                              placeholder='{"name": "Cat Name", "description": "Cat description", "attributes": {...}}'><?= json_encode([
                        'name' => $selectedCat['name'] . ' NFT',
                        'description' => 'Unique digital cat NFT on Purrr.love blockchain',
                        'image' => 'https://api.purrr.love/cats/' . $selectedCat['id'] . '/image',
                        'attributes' => [
                            'Breed' => ucfirst(str_replace('_', ' ', $selectedCat['breed'])),
                            'Color' => ucfirst(str_replace('_', ' ', $selectedCat['color'])),
                            'Health' => $selectedCat['health'],
                            'Happiness' => $selectedCat['happiness'],
                            'Rarity' => calculateRarity($selectedCat)
                        ]
                    ], JSON_PRETTY_PRINT) ?></textarea>
                </div>

                <button type="submit" 
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Upload to IPFS
                </button>
            </form>

            <!-- IPFS Configuration Info -->
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-2">IPFS Configuration</h4>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Gateway:</strong> <?= $ipfsConfig['gateway'] ?></p>
                    <p><strong>API Endpoint:</strong> <?= $ipfsConfig['api_endpoint'] ?></p>
                    <p><strong>Project ID:</strong> <?= $ipfsConfig['project_id'] ?></p>
                </div>
            </div>
        </div>

        <!-- NFT Details (if minted) -->
        <?php if ($nftData): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Newly Minted NFT Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">NFT Information</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">NFT ID:</span>
                            <span class="font-mono text-gray-900"><?= $nftData['nft_id'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Token ID:</span>
                            <span class="font-mono text-gray-900"><?= $nftData['token_id'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Contract Address:</span>
                            <span class="font-mono text-gray-900 text-xs"><?= $nftData['contract_address'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Owner Address:</span>
                            <span class="font-mono text-gray-900 text-xs"><?= $nftData['owner_address'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Mint Date:</span>
                            <span class="text-gray-900"><?= $nftData['mint_date'] ?></span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Metadata</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="text-gray-900"><?= $nftData['metadata']['name'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Description:</span>
                            <span class="text-gray-900 text-xs"><?= $nftData['metadata']['description'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Rarity:</span>
                            <span class="font-medium text-purple-600"><?= $nftData['metadata']['attributes']['Rarity'] ?></span>
                        </div>
                    </div>
                    
                    <h5 class="font-medium text-gray-900 mt-4 mb-2">Attributes</h5>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <?php foreach ($nftData['metadata']['attributes'] as $key => $value): ?>
                        <div class="bg-gray-50 p-2 rounded">
                            <div class="font-medium text-gray-700"><?= $key ?></div>
                            <div class="text-gray-600"><?= $value ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cat Information -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4"><?= htmlspecialchars($selectedCat['name']) ?>'s Profile</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600"><?= $selectedCat['health'] ?></div>
                    <div class="text-xs text-gray-500">Health</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600"><?= $selectedCat['happiness'] ?></div>
                    <div class="text-xs text-gray-500">Happiness</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600"><?= $selectedCat['energy'] ?></div>
                    <div class="text-xs text-gray-500">Energy</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600"><?= $selectedCat['hunger'] ?></div>
                    <div class="text-xs text-gray-500">Hunger</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600"><?= $selectedCat['cleanliness'] ?></div>
                    <div class="text-xs text-gray-500">Cleanliness</div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- No Cats Available -->
        <div class="text-center py-16">
            <div class="text-gray-400 text-6xl mb-6">
                <i class="fas fa-cat"></i>
            </div>
            <h3 class="text-2xl font-semibold text-gray-900 mb-4">No cats available!</h3>
            <p class="text-gray-600 mb-8">You need to create a cat first before you can mint NFTs.</p>
            <a href="cats.php?action=create" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-medium text-lg transition duration-300">
                <i class="fas fa-plus mr-2"></i>Create Your First Cat
            </a>
        </div>
        <?php endif; ?>

        <!-- Blockchain Information -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 mt-8 max-w-4xl mx-auto">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">🔗 How Blockchain & NFTs Work</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-700">
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">NFT Benefits</h4>
                    <ul class="space-y-1">
                        <li>• Verifiable ownership on blockchain</li>
                        <li>• Unique digital identity for each cat</li>
                        <li>• Tradeable digital assets</li>
                        <li>• Immutable ownership records</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Supported Networks</h4>
                    <ul class="space-y-1">
                        <li>• Ethereum (ETH) - Main network</li>
                        <li>• Polygon (MATIC) - Low fees</li>
                        <li>• Binance Smart Chain (BNB)</li>
                        <li>• Avalanche (AVAX)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2024 Purrr.love. All rights reserved. Made with ❤️ for cat lovers everywhere.</p>
        </div>
    </footer>

    <script>
        // Web3 Provider Connection Script
        class Web3ProviderManager {
            constructor() {
                this.providers = {};
                this.currentProvider = null;
                this.init();
            }

            async init() {
                // Check if MetaMask is available
                if (typeof window.ethereum !== 'undefined') {
                    console.log('MetaMask detected!');
                    this.setupMetaMask();
                } else {
                    console.log('No MetaMask detected. Please install MetaMask.');
                    this.showMetaMaskAlert();
                }
            }

            setupMetaMask() {
                const connectButton = document.getElementById('connect-metamask');
                if (connectButton) {
                    connectButton.addEventListener('click', () => this.connectMetaMask());
                }
            }

            async connectMetaMask() {
                try {
                    // Request account access
                    const accounts = await window.ethereum.request({ 
                        method: 'eth_requestAccounts' 
                    });
                    
                    if (accounts.length > 0) {
                        this.currentProvider = accounts[0];
                        this.updateConnectionStatus(true, accounts[0]);
                        console.log('Connected to MetaMask:', accounts[0]);
                    }
                } catch (error) {
                    console.error('Error connecting to MetaMask:', error);
                    this.updateConnectionStatus(false, null);
                }
            }

            updateConnectionStatus(connected, address) {
                const statusElement = document.getElementById('connection-status');
                if (statusElement) {
                    if (connected) {
                        statusElement.innerHTML = `
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Connected: ${address.substring(0, 6)}...${address.substring(38)}
                            </span>
                        `;
                    } else {
                        statusElement.innerHTML = `
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>Not Connected
                            </span>
                        `;
                    }
                }
            }

            showMetaMaskAlert() {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6';
                alertDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>MetaMask Required:</strong> Please install MetaMask to use blockchain features.
                        <a href="https://metamask.io/" target="_blank" class="ml-2 text-blue-600 hover:text-blue-800 underline">Install MetaMask</a>
                    </div>
                `;
                
                const container = document.querySelector('.max-w-7xl');
                if (container) {
                    container.insertBefore(alertDiv, container.firstChild);
                }
            }

            // Test Web3 provider connection
            async testProviderConnection(rpcUrl, network) {
                try {
                    const response = await fetch(rpcUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            jsonrpc: '2.0',
                            method: 'eth_blockNumber',
                            params: [],
                            id: 1
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.result) {
                            return { success: true, blockNumber: parseInt(data.result, 16) };
                        }
                    }
                    return { success: false, error: 'Invalid response' };
                } catch (error) {
                    return { success: false, error: error.message };
                }
            }
        }

        // Smart Contract Deployment Helper
        class SmartContractHelper {
            constructor() {
                this.contractTemplates = {
                    'erc721_basic': this.getERC721BasicTemplate(),
                    'erc721_advanced': this.getERC721AdvancedTemplate(),
                    'erc1155_multi': this.getERC1155Template()
                };
            }

            getERC721BasicTemplate() {
                return `// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

import "@openzeppelin/contracts/token/ERC721/ERC721.sol";
import "@openzeppelin/contracts/access/Ownable.sol";

contract PurrrLoveCats is ERC721, Ownable {
    constructor() ERC721("PurrrLoveCats", "PURRR") {}
    
    function mint(address to, uint256 tokenId) public onlyOwner {
        _mint(to, tokenId);
    }
}`;
            }

            getERC721AdvancedTemplate() {
                return `// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

import "@openzeppelin/contracts/token/ERC721/ERC721.sol";
import "@openzeppelin/contracts/access/Ownable.sol";

contract PurrrLoveCatsAdvanced is ERC721, Ownable {
    struct Cat {
        string name;
        uint8 breed;
        uint8 rarity;
        uint256 birthDate;
    }
    
    mapping(uint256 => Cat) public cats;
    
    constructor() ERC721("PurrrLoveCatsAdvanced", "PURRR") {}
    
    function mintCat(address to, uint256 tokenId, string memory name, uint8 breed, uint8 rarity) public onlyOwner {
        _mint(to, tokenId);
        cats[tokenId] = Cat(name, breed, rarity, block.timestamp);
    }
}`;
            }

            getERC1155Template() {
                return `// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

import "@openzeppelin/contracts/token/ERC1155/ERC1155.sol";
import "@openzeppelin/contracts/access/Ownable.sol";

contract PurrrLoveItems is ERC1155, Ownable {
    constructor() ERC1155("https://api.purrr.love/metadata/{id}.json") {}
    
    function mint(address to, uint256 id, uint256 amount, bytes memory data) public onlyOwner {
        _mint(to, id, amount, data);
    }
}`;
            }

            showContractTemplate(type) {
                const template = this.contractTemplates[type];
                if (template) {
                    // Create a modal to show the contract template
                    this.createTemplateModal(type, template);
                }
            }

            createTemplateModal(type, template) {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
                modal.innerHTML = `
                    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">${type.replace('_', ' ').toUpperCase()} Contract Template</h3>
                            <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto">${template}</pre>
                            <div class="mt-4 flex justify-end">
                                <button onclick="this.closest('.fixed').remove()" class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
        }

        // IPFS Helper
        class IPFSHelper {
            constructor() {
                this.gateway = 'https://ipfs.io/ipfs/';
            }

            async uploadToIPFS(metadata) {
                try {
                    // In a real implementation, this would upload to actual IPFS
                    // For now, we'll simulate the upload
                    const response = await fetch('/api/ipfs/upload', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(metadata)
                    });

                    if (response.ok) {
                        const result = await response.json();
                        return { success: true, hash: result.hash };
                    } else {
                        throw new Error('Upload failed');
                    }
                } catch (error) {
                    console.error('IPFS upload error:', error);
                    return { success: false, error: error.message };
                }
            }

            getIPFSURL(hash) {
                return `${this.gateway}${hash}`;
            }
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Web3 provider manager
            window.web3Manager = new Web3ProviderManager();
            
            // Initialize smart contract helper
            window.contractHelper = new SmartContractHelper();
            
            // Initialize IPFS helper
            window.ipfsHelper = new IPFSHelper();

            // Animate sections on load
            const sections = document.querySelectorAll('.bg-white');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
                }, index * 200);
            });

            // Add event listeners for contract template viewing
            const contractTypeSelect = document.getElementById('contract_type');
            if (contractTypeSelect) {
                contractTypeSelect.addEventListener('change', function() {
                    if (this.value) {
                        window.contractHelper.showContractTemplate(this.value);
                    }
                });
            }

            // Add event listener for test connection button
            const testConnectionBtn = document.getElementById('test-connection');
            if (testConnectionBtn) {
                testConnectionBtn.addEventListener('click', async function() {
                    const networkSelect = document.getElementById('network');
                    const rpcUrlInput = document.getElementById('rpc_url');
                    
                    if (networkSelect.value && rpcUrlInput.value) {
                        testConnectionBtn.disabled = true;
                        testConnectionBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
                        
                        try {
                            const result = await window.web3Manager.testProviderConnection(rpcUrlInput.value, networkSelect.value);
                            if (result.success) {
                                alert(`✅ Connection successful! Current block: ${result.blockNumber}`);
                            } else {
                                alert(`❌ Connection failed: ${result.error}`);
                            }
                        } catch (error) {
                            alert(`❌ Connection error: ${error.message}`);
                        } finally {
                            testConnectionBtn.disabled = false;
                            testConnectionBtn.innerHTML = '<i class="fas fa-plug mr-2"></i>Test Connection';
                        }
                    } else {
                        alert('Please select a network and enter RPC URL first');
                    }
                });
            }
        });
    </script>
</body>
</html>

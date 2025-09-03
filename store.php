<?php
/**
 * Purrr.love - Enhanced Cat Store
 * Developed and Designed by Ryan Coleman. <coleman.ryan@gmail.com>
 * Enhanced with feline-specific items, toys, and accessories
 */
require_once 'includes/functions.php';
require_once 'includes/crypto.php';
require_once 'includes/cat_care.php';
require_once 'includes/quests.php';

requireLogin();

// Track quest progress for visiting the store
if (isset($_SESSION['user_id'])) {
    update_quest_progress($_SESSION['user_id'], 'visit_store');
}

$currentUser = getUserById($_SESSION['user_id']);
$error = '';
$success = '';

// Get user crypto balances
$balances = [];
foreach (SUPPORTED_CRYPTOS as $crypto => $name) {
    $balances[$crypto] = getUserCryptoBalance($_SESSION['user_id'], $crypto);
}

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_item'])) {
    requireCSRFToken();
    $itemId = intval($_POST['item_id']);
    $quantity = max(1, intval($_POST['quantity']));
    $cryptoType = sanitizeInput($_POST['crypto_type']);
    
    if (!array_key_exists($cryptoType, SUPPORTED_CRYPTOS)) {
        $error = 'Invalid cryptocurrency selected.';
    } else {
        $result = purchaseStoreItem($_SESSION['user_id'], $itemId, $quantity, $cryptoType);
        
        if ($result['success']) {
            $success = "Successfully purchased {$quantity}x {$result['item']['name']} for {$result['crypto_amount']} {$cryptoType}!";
            
            // Track quest progress for purchases
            update_quest_progress($_SESSION['user_id'], 'purchase_items', $quantity);
        } else {
            $error = $result['message'];
        }
    }
}

$storeItems = getCatStoreItems();
$userInventory = getUserInventory($_SESSION['user_id']);

// Group items by type
$itemsByType = [];
foreach ($storeItems as $item) {
    $itemsByType[$item['item_type']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cat Store - Purrr.love</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cat-store.css">
</head>
<body>
    <header>
        <nav class="container">
            <a href="index.php" class="logo">🐱 Purrr.love</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="upload.php">Upload</a></li>
                <li><a href="ai-generator.php">AI Generator</a></li>
                <li><a href="game.php">Games</a></li>
                <li><a href="store.php">Store</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="hero hero-padding">
                <h1>🛒 Cat Store</h1>
                <p>Buy food, treats, toys, and accessories for your cats and others!</p>
            </div>

            <?php if (defined('DEVELOPER_MODE') && DEVELOPER_MODE): ?>
                <div class="alert alert-info">
                    🔧 <strong>Developer Mode Active</strong> - All purchases are FREE for testing
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="balance-display">
                <h3>Your Crypto Balances</h3>
                <div class="balance-grid">
                    <?php foreach (SUPPORTED_CRYPTOS as $crypto => $name): ?>
                        <div class="balance-item">
                            <h4><?php echo $crypto; ?></h4>
                            <p class="balance-amount">
                                <?php echo number_format($balances[$crypto], 8); ?>
                            </p>
                            <small class="text-muted"><?php echo $name; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="deposit.php" class="btn btn-primary">Add Funds</a>
                </div>
            </div>

            <!-- Cat Food Section -->
            <div class="store-section">
                <h2>🍽️ Cat Food & Nutrition</h2>
                <p>Premium cat food to keep your feline friends healthy and happy!</p>
                
                <div class="item-grid">
                    <?php if (isset($itemsByType['food'])): ?>
                        <?php foreach ($itemsByType['food'] as $item): ?>
                            <div class="store-item">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="item-stats">
                                        <span class="stat">❤️ +<?php echo $item['happiness_boost']; ?> Happiness</span>
                                        <span class="stat">🍽️ +<?php echo $item['hunger_restore']; ?> Hunger</span>
                                        <span class="stat">⚡ +<?php echo $item['energy_boost']; ?> Energy</span>
                                    </div>
                                    <div class="item-price">
                                        <span class="price-usd">$<?php echo number_format($item['price_usd'], 2); ?></span>
                                        <span class="price-crypto" id="price-<?php echo $item['id']; ?>">-</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <form method="POST" class="purchase-form">
                                        <?php echo getCSRFTokenField(); ?>
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="crypto_type" id="crypto-<?php echo $item['id']; ?>" value="">
                                        
                                        <div class="quantity-selector">
                                            <label for="quantity-<?php echo $item['id']; ?>">Quantity:</label>
                                            <select name="quantity" id="quantity-<?php echo $item['id']; ?>">
                                                <option value="1">1</option>
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                            </select>
                                        </div>
                                        
                                        <button type="submit" name="purchase_item" class="btn btn-primary" 
                                                data-item-id="<?php echo $item['id']; ?>">
                                            🛒 Purchase
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-items">No food items available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cat Treats Section -->
            <div class="store-section">
                <h2>🍬 Cat Treats & Snacks</h2>
                <p>Delicious treats to reward your cats and boost their mood!</p>
                
                <div class="item-grid">
                    <?php if (isset($itemsByType['treats'])): ?>
                        <?php foreach ($itemsByType['treats'] as $item): ?>
                            <div class="store-item">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="item-stats">
                                        <span class="stat">❤️ +<?php echo $item['happiness_boost']; ?> Happiness</span>
                                        <span class="stat">😺 +<?php echo $item['mood_boost']; ?> Mood</span>
                                        <span class="stat">🎯 +<?php echo $item['training_bonus']; ?> Training</span>
                                    </div>
                                    <div class="item-price">
                                        <span class="price-usd">$<?php echo number_format($item['price_usd'], 2); ?></span>
                                        <span class="price-crypto" id="price-<?php echo $item['id']; ?>">-</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <form method="POST" class="purchase-form">
                                        <?php echo getCSRFTokenField(); ?>
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="crypto_type" id="crypto-<?php echo $item['id']; ?>" value="">
                                        
                                        <div class="quantity-selector">
                                            <label for="quantity-<?php echo $item['id']; ?>">Quantity:</label>
                                            <select name="quantity" id="quantity-<?php echo $item['id']; ?>">
                                                <option value="1">1</option>
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                            </select>
                                        </div>
                                        
                                        <button type="submit" name="purchase_item" class="btn btn-primary" 
                                                data-item-id="<?php echo $item['id']; ?>">
                                            🛒 Purchase
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-items">No treat items available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Interactive Toys Section -->
            <div class="store-section">
                <h2>🎾 Interactive Toys & Games</h2>
                <p>Engaging toys to keep your cats entertained and active!</p>
                
                <div class="item-grid">
                    <?php if (isset($itemsByType['toys'])): ?>
                        <?php foreach ($itemsByType['toys'] as $item): ?>
                            <div class="store-item">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="item-stats">
                                        <span class="stat">⚡ +<?php echo $item['energy_boost']; ?> Energy</span>
                                        <span class="stat">🎮 +<?php echo $item['play_bonus']; ?> Play Bonus</span>
                                        <span class="stat">🏆 +<?php echo $item['training_bonus']; ?> Training</span>
                                    </div>
                                    <div class="item-price">
                                        <span class="price-usd">$<?php echo number_format($item['price_usd'], 2); ?></span>
                                        <span class="price-crypto" id="price-<?php echo $item['id']; ?>">-</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <form method="POST" class="purchase-form">
                                        <?php echo getCSRFTokenField(); ?>
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="crypto_type" id="crypto-<?php echo $item['id']; ?>">
                                        
                                        <div class="quantity-selector">
                                            <label for="quantity-<?php echo $item['id']; ?>">Quantity:</label>
                                            <select name="quantity" id="quantity-<?php echo $item['id']; ?>">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="5">5</option>
                                            </select>
                                        </div>
                                        
                                        <button type="submit" name="purchase_item" class="btn btn-primary" 
                                                data-item-id="<?php echo $item['id']; ?>">
                                            🛒 Purchase
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-items">No toy items available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cat Furniture Section -->
            <div class="store-section">
                <h2>🏠 Cat Furniture & Comfort</h2>
                <p>Cozy furniture and accessories for your cat's comfort and territory!</p>
                
                <div class="item-grid">
                    <?php if (isset($itemsByType['furniture'])): ?>
                        <?php foreach ($itemsByType['furniture'] as $item): ?>
                            <div class="store-item">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="item-stats">
                                        <span class="stat">🏠 +<?php echo $item['territory_bonus']; ?> Territory</span>
                                        <span class="stat">😴 +<?php echo $item['rest_bonus']; ?> Rest Bonus</span>
                                        <span class="stat">🎯 +<?php echo $item['climbing_bonus']; ?> Climbing</span>
                                    </div>
                                    <div class="item-price">
                                        <span class="price-usd">$<?php echo number_format($item['price_usd'], 2); ?></span>
                                        <span class="price-crypto" id="price-<?php echo $item['id']; ?>">-</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <form method="POST" class="purchase-form">
                                        <?php echo getCSRFTokenField(); ?>
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="crypto_type" id="crypto-<?php echo $item['id']; ?>" value="">
                                        
                                        <div class="quantity-selector">
                                            <label for="quantity-<?php echo $item['id']; ?>">Quantity:</label>
                                            <select name="quantity" id="quantity-<?php echo $item['id']; ?>">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                            </select>
                                        </div>
                                        
                                        <button type="submit" name="purchase_item" class="btn btn-primary" 
                                                data-item-id="<?php echo $item['id']; ?>">
                                            🛒 Purchase
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-items">No furniture items available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Grooming & Health Section -->
            <div class="store-section">
                <h2>🧴 Grooming & Health</h2>
                <p>Essential grooming tools and health items for your cat's well-being!</p>
                
                <div class="item-grid">
                    <?php if (isset($itemsByType['grooming'])): ?>
                        <?php foreach ($itemsByType['grooming'] as $item): ?>
                            <div class="store-item">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="item-stats">
                                        <span class="stat">✨ +<?php echo $item['grooming_bonus']; ?> Grooming</span>
                                        <span class="stat">❤️ +<?php echo $item['health_bonus']; ?> Health</span>
                                        <span class="stat">😺 +<?php echo $item['mood_bonus']; ?> Mood</span>
                                    </div>
                                    <div class="item-price">
                                        <span class="price-usd">$<?php echo number_format($item['price_usd'], 2); ?></span>
                                        <span class="price-crypto" id="price-<?php echo $item['id']; ?>">-</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <form method="POST" class="purchase-form">
                                        <?php echo getCSRFTokenField(); ?>
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="crypto_type" id="crypto-<?php echo $item['id']; ?>" value="">
                                        
                                        <div class="quantity-selector">
                                            <label for="quantity-<?php echo $item['id']; ?>">Quantity:</label>
                                            <select name="quantity" id="quantity-<?php echo $item['id']; ?>">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="5">5</option>
                                            </select>
                                        </div>
                                        
                                        <button type="submit" name="purchase_item" class="btn btn-primary" 
                                                data-item-id="<?php echo $item['id']; ?>">
                                            🛒 Purchase
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-items">No grooming items available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Luxury Items Section -->
            <div class="store-section">
                <h2>💎 Luxury & Premium Items</h2>
                <p>Exclusive luxury items for the most pampered cats!</p>
                
                <div class="item-grid">
                    <?php if (isset($itemsByType['luxury'])): ?>
                        <?php foreach ($itemsByType['luxury'] as $item): ?>
                            <div class="store-item luxury-item">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div class="luxury-badge">💎 Premium</div>
                                </div>
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="item-stats">
                                        <span class="stat">💎 +<?php echo $item['luxury_bonus']; ?> Luxury Bonus</span>
                                        <span class="stat">🏆 +<?php echo $item['prestige_bonus']; ?> Prestige</span>
                                        <span class="stat">✨ +<?php echo $item['special_effect']; ?> Special Effect</span>
                                    </div>
                                    <div class="item-price">
                                        <span class="price-usd">$<?php echo number_format($item['price_usd'], 2); ?></span>
                                        <span class="price-crypto" id="price-<?php echo $item['id']; ?>">-</span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <form method="POST" class="purchase-form">
                                        <?php echo getCSRFTokenField(); ?>
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="crypto_type" id="crypto-<?php echo $item['id']; ?>" value="">
                                        
                                        <div class="quantity-selector">
                                            <label for="quantity-<?php echo $item['id']; ?>">Quantity:</label>
                                            <select name="quantity" id="quantity-<?php echo $item['id']; ?>">
                                                <option value="1">1</option>
                                            </select>
                                        </div>
                                        
                                        <button type="submit" name="purchase_item" class="btn btn-luxury" 
                                                data-item-id="<?php echo $item['id']; ?>">
                                            💎 Purchase Luxury
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-items">No luxury items available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stimulants Section -->
            <div class="store-section">
                <h3>🌿 Stimulants & Special Items</h3>
                <div class="items-grid">
                    <div class="store-item">
                        <div class="item-image">🌿</div>
                        <div class="item-info">
                            <h4>Catnip</h4>
                            <p>Classic cat stimulant that most cats love</p>
                            <div class="item-effects">
                                <span class="effect">😊 +30 Happiness</span>
                                <span class="effect">⚡ +40 Energy</span>
                            </div>
                            <div class="item-price">💰 50 coins</div>
                            <button class="buy-btn" onclick="buyItem('catnip', 50)">Buy</button>
                        </div>
                    </div>
                    
                    <div class="store-item">
                        <div class="item-image">🌸</div>
                        <div class="item-info">
                            <h4>Honeysuckle</h4>
                            <p>Alternative stimulant for cats that don't respond to catnip</p>
                            <div class="item-effects">
                                <span class="effect">😊 +25 Happiness</span>
                                <span class="effect">⚡ +35 Energy</span>
                            </div>
                            <div class="item-price">💰 60 coins</div>
                            <button class="buy-btn" onclick="buyItem('honeysuckle', 60)">Buy</button>
                        </div>
                    </div>
                    
                    <div class="store-item">
                        <div class="item-image">🎭</div>
                        <div class="item-info">
                            <h4>Dual Stimulant Pack</h4>
                            <p>Both catnip and honeysuckle for all cat types</p>
                            <div class="item-effects">
                                <span class="effect">😊 +40 Happiness</span>
                                <span class="effect">⚡ +50 Energy</span>
                            </div>
                            <div class="item-price">💰 100 coins</div>
                            <button class="buy-btn" onclick="buyItem('dual_stimulant_pack', 100)">Buy</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Your Inventory -->
            <div class="store-section">
                <h2>📦 Your Inventory</h2>
                <p>Items you've purchased and can use with your cats.</p>
                
                <div class="inventory-grid">
                    <?php if (!empty($userInventory)): ?>
                        <?php foreach ($userInventory as $item): ?>
                            <div class="inventory-item">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-info">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p class="quantity">Quantity: <?php echo $item['quantity']; ?></p>
                                    <p class="acquired">Acquired: <?php echo date('M j, Y', strtotime($item['acquired_at'])); ?></p>
                                </div>
                                <div class="item-actions">
                                    <a href="use_item.php?item_id=<?php echo $item['id']; ?>" class="btn btn-secondary">
                                        🎯 Use Item
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-items">Your inventory is empty. Purchase some items to get started!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/cat-store.js"></script>
</body>
</html>

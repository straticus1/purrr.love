<?php
/**
 * Purrr.love - Enhanced Feline Gaming Platform
 * Developed and Designed by Ryan Coleman. <coleman.ryan@gmail.com>
 * Enhanced with feline-specific features and multiple mini-games
 */
require_once 'includes/functions.php';
require_once 'includes/crypto.php';
require_once 'includes/cat_behavior.php';

requireLogin();

$currentUser = getUserById($_SESSION['user_id']);
$error = '';
$success = '';

// Get user crypto balances
$balances = [];
foreach (SUPPORTED_CRYPTOS as $crypto => $name) {
    $balances[$crypto] = getUserCryptoBalance($_SESSION['user_id'], $crypto);
}

// Get user's cats for game selection
$userCats = getUserCats($_SESSION['user_id']);

// Handle game play
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['play_game'])) {
    requireCSRFToken();
    $gameType = sanitizeInput($_POST['game_type']);
    $catId = intval($_POST['cat_id']);
    $cryptoType = sanitizeInput($_POST['crypto_type']);
    
    if (!array_key_exists($cryptoType, SUPPORTED_CRYPTOS)) {
        $error = 'Invalid cryptocurrency selected.';
    } else {
        $entryFeeUSD = getGameEntryFee($gameType);
        $cryptoAmount = convertUSDToCrypto($entryFeeUSD, $cryptoType);
        
        // In developer mode, skip crypto conversion and balance checks
        if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
            $cryptoAmount = 0; // Free play
        } else {
            if ($cryptoAmount === null) {
                $error = 'Unable to get crypto price. Please try again.';
            } else {
                $userBalance = getUserCryptoBalance($_SESSION['user_id'], $cryptoType);
                
                if ($userBalance < $cryptoAmount) {
                    $error = 'Insufficient balance. Please add funds to your account.';
                }
            }
        }
        
        if (!$error) {
            // Deduct entry fee (skip in developer mode)
            if (!(defined('DEVELOPER_MODE') && DEVELOPER_MODE)) {
                updateUserBalance($_SESSION['user_id'], $cryptoType, $cryptoAmount, 'subtract');
            }
            
            // Play the selected game
            $gameResult = playCatGame($gameType, $catId, $cryptoAmount);
            
            if ($gameResult['success']) {
                $winAmount = $gameResult['win_amount'];
                
                // In developer mode, simulate win without actual crypto
                if (defined('DEVELOPER_MODE') && DEVELOPER_MODE) {
                    $winAmount = 1.0; // Show 1.0 crypto win for demo
                } else {
                    updateUserBalance($_SESSION['user_id'], $cryptoType, $winAmount, 'add');
                }
                
                // Record win (skip in developer mode to avoid database clutter)
                if (!(defined('DEVELOPER_MODE') && DEVELOPER_MODE)) {
                    $stmt = $pdo->prepare("INSERT INTO game_results (user_id, game_type, crypto_type, entry_fee, win_amount, result, cat_id) VALUES (?, ?, ?, ?, ?, 'win', ?)");
                    $stmt->execute([$_SESSION['user_id'], $gameType, $cryptoType, $cryptoAmount, $winAmount, $catId]);
                }
                
                $success = "🎉 Congratulations! Your cat won " . number_format($winAmount, 8) . " $cryptoType!" . 
                          (defined('DEVELOPER_MODE') && DEVELOPER_MODE ? " (Developer Mode - No real crypto)" : "");
                
                // Update cat stats based on game performance
                updateCatGameStats($catId, $gameType, $gameResult['performance']);
                
            } else {
                // Record loss (skip in developer mode)
                if (!(defined('DEVELOPER_MODE') && DEVELOPER_MODE)) {
                    $stmt = $pdo->prepare("INSERT INTO game_results (user_id, game_type, crypto_type, entry_fee, win_amount, result, cat_id) VALUES (?, ?, ?, ?, 0, 'loss', ?)");
                    $stmt->execute([$_SESSION['user_id'], $gameType, $cryptoType, $cryptoAmount, $catId]);
                }
                
                $lossMessage = defined('DEVELOPER_MODE') && DEVELOPER_MODE ? 
                    "😢 Better luck next time! (Developer Mode - No real crypto lost)" :
                    "😢 Better luck next time! You lost " . number_format($cryptoAmount, 8) . " $cryptoType.";
                $error = $lossMessage;
            }
            
            // Refresh balances
            foreach (SUPPORTED_CRYPTOS as $crypto => $name) {
                $balances[$crypto] = getUserCryptoBalance($_SESSION['user_id'], $crypto);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cat Games - Purrr.love</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cat-games.css">
</head>
<body>
    <header>
        <nav class="container">
            <a href="index.php" class="logo">🐱 Purrr.love</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="upload.php">Upload</a></li>
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
                <h1>🎮 Cat Games</h1>
                <p>Play feline-focused games and win crypto rewards with your cats!</p>
            </div>

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

            <!-- Cat Selection for Games -->
            <div class="cat-selection-card">
                <h3>🐱 Select Your Cat</h3>
                <p>Choose which cat will play the games. Each cat has unique abilities and personality traits that affect gameplay!</p>
                
                <div class="cat-grid">
                    <?php if (empty($userCats)): ?>
                        <div class="no-cats">
                            <p>You don't have any cats yet!</p>
                            <a href="upload.php" class="btn btn-primary">Upload Your First Cat</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($userCats as $cat): ?>
                            <div class="cat-option" data-cat-id="<?php echo $cat['id']; ?>">
                                <img src="<?php echo htmlspecialchars($cat['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($cat['name']); ?>" 
                                     class="cat-avatar">
                                <div class="cat-info">
                                    <h4><?php echo htmlspecialchars($cat['name']); ?></h4>
                                    <p class="cat-personality"><?php echo getCatPersonalityName($cat['personality_type']); ?></p>
                                    <div class="cat-stats">
                                        <span class="stat">❤️ <?php echo $cat['happiness']; ?></span>
                                        <span class="stat">⚡ <?php echo $cat['energy']; ?></span>
                                        <span class="stat">🏆 <?php echo $cat['level']; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Enhanced Paw Match Game -->
            <div class="game-card">
                <h2>🐾 Enhanced Paw Match</h2>
                <p>Classic matching game with cat-themed elements and personality bonuses!</p>
                <p><strong>Entry Fee:</strong> $<?php echo getGameEntryFee('paw_match'); ?> (in crypto)</p>
                <p><strong>Cat Bonus:</strong> Cats with "Playful" personality get +20% win rate!</p>
                
                <form method="POST" class="game-form" id="paw-match-form">
                    <?php echo getCSRFTokenField(); ?>
                    <input type="hidden" name="game_type" value="paw_match">
                    <input type="hidden" name="cat_id" id="paw-match-cat" value="">
                    
                    <div class="form-group">
                        <label>Select Cryptocurrency</label>
                        <div class="crypto-selector">
                            <?php foreach (SUPPORTED_CRYPTOS as $crypto => $name): ?>
                                <div class="crypto-option" data-crypto="<?php echo $crypto; ?>">
                                    <div class="crypto-name"><?php echo $crypto; ?></div>
                                    <div class="crypto-amount" id="paw-match-amount-<?php echo $crypto; ?>">
                                        <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? 'FREE' : '-'; ?>
                                    </div>
                                    <div class="balance">Balance: <?php echo number_format($balances[$crypto], 8); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="crypto_type" id="paw-match-crypto" value="">
                    </div>
                    
                    <button type="submit" name="play_game" class="btn btn-primary btn-large" disabled id="paw-match-btn">
                        🎮 Play Paw Match <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? '(FREE)' : ''; ?>
                    </button>
                </form>
            </div>

            <!-- Mouse Hunt Game -->
            <div class="game-card">
                <h2>🐭 Mouse Hunt</h2>
                <p>Hunt virtual mice in a cat-themed hunting game! Cats with "Curious" personality excel at this game.</p>
                <p><strong>Entry Fee:</strong> $<?php echo getGameEntryFee('mouse_hunt'); ?> (in crypto)</p>
                <p><strong>Cat Bonus:</strong> "Curious" cats get +25% hunting success rate!</p>
                
                <form method="POST" class="game-form" id="mouse-hunt-form">
                    <?php echo getCSRFTokenField(); ?>
                    <input type="hidden" name="game_type" value="mouse_hunt">
                    <input type="hidden" name="cat_id" id="mouse-hunt-cat" value="">
                    
                    <div class="form-group">
                        <label>Select Cryptocurrency</label>
                        <div class="crypto-selector">
                            <?php foreach (SUPPORTED_CRYPTOS as $crypto => $name): ?>
                                <div class="crypto-option" data-crypto="<?php echo $crypto; ?>">
                                    <div class="crypto-name"><?php echo $crypto; ?></div>
                                    <div class="crypto-amount" id="mouse-hunt-amount-<?php echo $crypto; ?>">
                                        <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? 'FREE' : '-'; ?>
                                    </div>
                                    <div class="balance">Balance: <?php echo number_format($balances[$crypto], 8); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="crypto_type" id="mouse-hunt-crypto" value="">
                    </div>
                    
                    <button type="submit" name="play_game" class="btn btn-primary btn-large" disabled id="mouse-hunt-btn">
                        🎮 Hunt Mice <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? '(FREE)' : ''; ?>
                    </button>
                </form>
            </div>

            <!-- Yarn Chase Game -->
            <div class="game-card">
                <h2>🧶 Yarn Chase</h2>
                <p>Chase colorful yarn balls in this fast-paced reflex game! Perfect for energetic cats.</p>
                <p><strong>Entry Fee:</strong> $<?php echo getGameEntryFee('yarn_chase'); ?> (in crypto)</p>
                <p><strong>Cat Bonus:</strong> "Energetic" cats get +30% speed bonus!</p>
                
                <form method="POST" class="game-form" id="yarn-chase-form">
                    <?php echo getCSRFTokenField(); ?>
                    <input type="hidden" name="game_type" value="yarn_chase">
                    <input type="hidden" name="cat_id" id="yarn-chase-cat" value="">
                    
                    <div class="form-group">
                        <label>Select Cryptocurrency</label>
                        <div class="crypto-selector">
                            <?php foreach (SUPPORTED_CRYPTOS as $crypto => $name): ?>
                                <div class="crypto-option" data-crypto="<?php echo $crypto; ?>">
                                    <div class="crypto-name"><?php echo $crypto; ?></div>
                                    <div class="crypto-amount" id="yarn-chase-amount-<?php echo $crypto; ?>">
                                        <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? 'FREE' : '-'; ?>
                                    </div>
                                    <div class="balance">Balance: <?php echo number_format($balances[$crypto], 8); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="crypto_type" id="yarn-chase-crypto" value="">
                    </div>
                    
                    <button type="submit" name="play_game" class="btn btn-primary btn-large" disabled id="yarn-chase-btn">
                        🎮 Chase Yarn <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? '(FREE)' : ''; ?>
                    </button>
                </form>
            </div>

            <!-- Cat Tower Climbing -->
            <div class="game-card">
                <h2>🏗️ Cat Tower Climbing</h2>
                <p>Climb to the top of the cat tower! Cats with "Territorial" personality get height bonuses.</p>
                <p><strong>Entry Fee:</strong> $<?php echo getGameEntryFee('tower_climb'); ?> (in crypto)</p>
                <p><strong>Cat Bonus:</strong> "Territorial" cats get +15% climbing height!</p>
                
                <form method="POST" class="game-form" id="tower-climb-form">
                    <?php echo getCSRFTokenField(); ?>
                    <input type="hidden" name="game_type" value="tower_climb">
                    <input type="hidden" name="cat_id" id="tower-climb-cat" value="">
                    
                    <div class="form-group">
                        <label>Select Cryptocurrency</label>
                        <div class="crypto-selector">
                            <?php foreach (SUPPORTED_CRYPTOS as $crypto => $name): ?>
                                <div class="crypto-option" data-crypto="<?php echo $crypto; ?>">
                                    <div class="crypto-name"><?php echo $crypto; ?></div>
                                    <div class="crypto-amount" id="tower-climb-amount-<?php echo $crypto; ?>">
                                        <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? 'FREE' : '-'; ?>
                                    </div>
                                    <div class="balance">Balance: <?php echo number_format($balances[$crypto], 8); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="crypto_type" id="tower-climb-crypto" value="">
                    </div>
                    
                    <button type="submit" name="play_game" class="btn btn-primary btn-large" disabled id="tower-climb-btn">
                        🎮 Climb Tower <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? '(FREE)' : ''; ?>
                    </button>
                </form>
            </div>

            <!-- Bird Watching Game -->
            <div class="game-card">
                <h2>🐦 Bird Watching</h2>
                <p>Relaxing bird watching game with rare item rewards! Perfect for "Lazy" cats who enjoy observation.</p>
                <p><strong>Entry Fee:</strong> $<?php echo getGameEntryFee('bird_watching'); ?> (in crypto)</p>
                <p><strong>Cat Bonus:</strong> "Lazy" cats get +40% rare bird sighting chance!</p>
                
                <form method="POST" class="game-form" id="bird-watching-form">
                    <?php echo getCSRFTokenField(); ?>
                    <input type="hidden" name="game_type" value="bird_watching">
                    <input type="hidden" name="cat_id" id="bird-watching-cat" value="">
                    
                    <div class="form-group">
                        <label>Select Cryptocurrency</label>
                        <div class="crypto-selector">
                            <?php foreach (SUPPORTED_CRYPTOS as $crypto => $name): ?>
                                <div class="crypto-option" data-crypto="<?php echo $crypto; ?>">
                                    <div class="crypto-name"><?php echo $crypto; ?></div>
                                    <div class="crypto-amount" id="bird-watching-amount-<?php echo $crypto; ?>">
                                        <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? 'FREE' : ''; ?>
                                    </div>
                                    <div class="balance">Balance: <?php echo number_format($balances[$crypto], 8); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="crypto_type" id="bird-watching-crypto" value="">
                    </div>
                    
                    <button type="submit" name="play_game" class="btn btn-primary btn-large" disabled id="bird-watching-btn">
                        🎮 Watch Birds <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? '(FREE)' : ''; ?>
                    </button>
                </form>
            </div>

            <!-- Laser Pointer Game -->
            <div class="game-card">
                <h2>🔴 Laser Pointer</h2>
                <p>Fast-paced laser pointer game! Cats with "Social Butterfly" personality get interaction bonuses.</p>
                <p><strong>Entry Fee:</strong> $<?php echo getGameEntryFee('laser_pointer'); ?> (in crypto)</p>
                <p><strong>Cat Bonus:</strong> "Social Butterfly" cats get +25% interaction bonus!</p>
                
                <form method="POST" class="game-form" id="laser-pointer-form">
                    <?php echo getCSRFTokenField(); ?>
                    <input type="hidden" name="game_type" value="laser_pointer">
                    <input type="hidden" name="cat_id" id="laser-pointer-cat" value="">
                    
                    <div class="form-group">
                        <label>Select Cryptocurrency</label>
                        <div class="crypto-selector">
                            <?php foreach (SUPPORTED_CRYPTOS as $crypto => $name): ?>
                                <div class="crypto-option" data-crypto="<?php echo $crypto; ?>">
                                    <div class="crypto-name"><?php echo $crypto; ?></div>
                                    <div class="crypto-amount" id="laser-pointer-amount-<?php echo $crypto; ?>">
                                        <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? 'FREE' : ''; ?>
                                    </div>
                                    <div class="balance">Balance: <?php echo number_format($balances[$crypto], 8); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="crypto_type" id="laser-pointer-crypto" value="">
                    </div>
                    
                    <button type="submit" name="play_game" class="btn btn-primary btn-large" disabled id="laser-pointer-btn">
                        🎮 Chase Laser <?php echo (defined('DEVELOPER_MODE') && DEVELOPER_MODE) ? '(FREE)' : ''; ?>
                    </button>
                </form>
            </div>

            <!-- Coming Soon Games -->
            <div class="game-card">
                <h2>🏆 Cat Olympics</h2>
                <p>Seasonal competitive events where cats compete in various challenges!</p>
                <p><strong>Entry Fee:</strong> $<?php echo getGameEntryFee('cat_olympics'); ?> (in crypto)</p>
                <p><strong>Status:</strong> <span class="status-coming-soon">Coming Soon!</span></p>
                <button class="btn btn-secondary" disabled>Coming Soon</button>
            </div>

            <div class="game-card">
                <h2>🌙 Night Hunt</h2>
                <p>Special night-time hunting game with enhanced rewards and rare encounters!</p>
                <p><strong>Entry Fee:</strong> $<?php echo getGameEntryFee('night_hunt'); ?> (in crypto)</p>
                <p><strong>Status:</strong> <span class="status-coming-soon">Coming Soon!</span></p>
                <button class="btn btn-secondary" disabled>Coming Soon</button>
            </div>

            <div class="card">
                <h2>🏆 Recent Winners</h2>
                <div id="recent-winners">
                    <!-- Recent winners will be loaded here -->
                    <div class="loader"></div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/cat-games.js"></script>
</body>
</html>

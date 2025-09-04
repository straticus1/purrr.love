<?php
/**
 * ❓ Purrr.love - Help Center
 * Comprehensive help and documentation
 */

session_start();
require_once 'includes/db_config.php';

$user_logged_in = isset($_SESSION['user_id']);
$search_query = $_GET['search'] ?? '';
$category = $_GET['category'] ?? 'all';

// Help articles data
$help_articles = [
    'getting-started' => [
        'title' => 'Getting Started',
        'articles' => [
            [
                'title' => 'How to Create an Account',
                'content' => 'Creating an account on Purrr.love is easy! Click the "Get Started" button on the homepage, fill in your details, and you\'ll be ready to start your cat gaming journey.',
                'tags' => ['account', 'registration', 'signup']
            ],
            [
                'title' => 'Your First Cat Adoption',
                'content' => 'Once you\'re logged in, visit the Cats section and click "Adopt Cat". Choose from various breeds and personalities to find your perfect feline companion.',
                'tags' => ['adoption', 'cats', 'first-steps']
            ],
            [
                'title' => 'Understanding the Dashboard',
                'content' => 'Your dashboard shows your cat collection, coins, level progress, and recent activities. It\'s your central hub for managing your Purrr.love experience.',
                'tags' => ['dashboard', 'interface', 'navigation']
            ]
        ]
    ],
    'cats' => [
        'title' => 'Cat Management',
        'articles' => [
            [
                'title' => 'Cat Breeds and Personalities',
                'content' => 'Each cat has unique traits, personalities, and abilities. Some are rare and valuable, while others are perfect for beginners. Explore different breeds to find your favorites.',
                'tags' => ['breeds', 'personalities', 'traits']
            ],
            [
                'title' => 'Caring for Your Cats',
                'content' => 'Keep your cats happy by feeding them, playing with them, and providing proper care. Happy cats perform better in games and activities.',
                'tags' => ['care', 'feeding', 'happiness']
            ],
            [
                'title' => 'Breeding and Genetics',
                'content' => 'Breed your cats to create unique offspring with combined traits. The genetics system allows for complex breeding strategies and rare combinations.',
                'tags' => ['breeding', 'genetics', 'offspring']
            ]
        ]
    ],
    'games' => [
        'title' => 'Games & Activities',
        'articles' => [
            [
                'title' => 'Available Games',
                'content' => 'Play various mini-games with your cats including Mouse Hunt, Paw Match, and Cat Olympics. Each game offers different rewards and challenges.',
                'tags' => ['games', 'mini-games', 'activities']
            ],
            [
                'title' => 'Earning Coins and Rewards',
                'content' => 'Earn Purrr Coins by playing games, completing quests, and participating in community events. Coins can be used to buy items and accessories.',
                'tags' => ['coins', 'rewards', 'currency']
            ],
            [
                'title' => 'Multiplayer Games',
                'content' => 'Join real-time multiplayer games with other players. Compete in tournaments, team up for challenges, and climb the leaderboards.',
                'tags' => ['multiplayer', 'tournaments', 'leaderboards']
            ]
        ]
    ],
    'blockchain' => [
        'title' => 'Blockchain & NFTs',
        'articles' => [
            [
                'title' => 'Understanding Cat NFTs',
                'content' => 'Your cats can be minted as NFTs on multiple blockchains. This gives you true ownership and the ability to trade them on external marketplaces.',
                'tags' => ['nft', 'blockchain', 'ownership']
            ],
            [
                'title' => 'Trading and Marketplace',
                'content' => 'Buy and sell cats on our integrated marketplace. Set your own prices and trade with other players from around the world.',
                'tags' => ['trading', 'marketplace', 'buying', 'selling']
            ],
            [
                'title' => 'Supported Blockchains',
                'content' => 'We support Ethereum, Polygon, and Solana networks. Each offers different benefits for gas fees and transaction speeds.',
                'tags' => ['ethereum', 'polygon', 'solana', 'networks']
            ]
        ]
    ],
    'technical' => [
        'title' => 'Technical Support',
        'articles' => [
            [
                'title' => 'System Requirements',
                'content' => 'Purrr.love works on modern web browsers. For VR features, you\'ll need a compatible VR headset and sufficient system specifications.',
                'tags' => ['requirements', 'browser', 'vr', 'compatibility']
            ],
            [
                'title' => 'Troubleshooting Common Issues',
                'content' => 'Having problems? Check your internet connection, clear your browser cache, and ensure JavaScript is enabled. Most issues can be resolved with these steps.',
                'tags' => ['troubleshooting', 'issues', 'problems']
            ],
            [
                'title' => 'Account Security',
                'content' => 'Keep your account secure by using a strong password, enabling two-factor authentication, and never sharing your login credentials.',
                'tags' => ['security', 'password', '2fa', 'protection']
            ]
        ]
    ]
];

// Filter articles based on search and category
$filtered_articles = [];
if ($category === 'all') {
    foreach ($help_articles as $cat => $data) {
        foreach ($data['articles'] as $article) {
            if (empty($search_query) || 
                stripos($article['title'], $search_query) !== false || 
                stripos($article['content'], $search_query) !== false ||
                in_array(strtolower($search_query), array_map('strtolower', $article['tags']))) {
                $filtered_articles[] = array_merge($article, ['category' => $cat]);
            }
        }
    }
} else {
    if (isset($help_articles[$category])) {
        foreach ($help_articles[$category]['articles'] as $article) {
            if (empty($search_query) || 
                stripos($article['title'], $search_query) !== false || 
                stripos($article['content'], $search_query) !== false ||
                in_array(strtolower($search_query), array_map('strtolower', $article['tags']))) {
                $filtered_articles[] = array_merge($article, ['category' => $category]);
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
    <title>❓ Help Center - Purrr.love</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .search-highlight {
            background-color: #fef3c7;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b-4 border-green-500">
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
                            <a href="index.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-home mr-2"></i>Home
                            </a>
                            <?php if ($user_logged_in): ?>
                                <a href="dashboard.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>
                            <?php else: ?>
                                <a href="register.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                    <i class="fas fa-user-plus mr-2"></i>Register
                                </a>
                            <?php endif; ?>
                            <a href="support.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-ticket-alt mr-2"></i>Support
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <?php if ($user_logged_in): ?>
                        <a href="profile.php" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                        <a href="index.php?logout=1" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="index.php#login" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">❓ Help Center</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Find answers to your questions and learn how to make the most of your Purrr.love experience.
            </p>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" 
                               placeholder="Search help articles..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div>
                    <select name="category" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>All Categories</option>
                        <?php foreach ($help_articles as $cat => $data): ?>
                            <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $data['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-medium transition duration-300">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
        </div>

        <!-- Results -->
        <?php if (!empty($search_query) || $category !== 'all'): ?>
        <div class="mb-6">
            <p class="text-gray-600">
                <?php if (!empty($search_query)): ?>
                    Found <?= count($filtered_articles) ?> article(s) for "<?= htmlspecialchars($search_query) ?>"
                <?php else: ?>
                    Showing articles in <?= $help_articles[$category]['title'] ?? 'All Categories' ?>
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Articles Grid -->
        <?php if (!empty($filtered_articles)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($filtered_articles as $article): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-file-alt text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($article['title']) ?></h3>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                            <?= $help_articles[$article['category']]['title'] ?>
                        </span>
                    </div>
                </div>
                
                <p class="text-gray-600 text-sm mb-4">
                    <?= htmlspecialchars(substr($article['content'], 0, 120)) ?>...
                </p>
                
                <div class="flex flex-wrap gap-1 mb-4">
                    <?php foreach (array_slice($article['tags'], 0, 3) as $tag): ?>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                            <?= htmlspecialchars($tag) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                
                <button onclick="showArticle('<?= htmlspecialchars($article['title']) ?>', '<?= htmlspecialchars($article['content']) ?>')" 
                        class="w-full bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white font-medium py-2 px-4 rounded-md transition duration-300">
                    <i class="fas fa-eye mr-2"></i>Read More
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No articles found</h3>
            <p class="text-gray-600">Try adjusting your search terms or browse all categories.</p>
        </div>
        <?php endif; ?>

        <!-- Category Overview -->
        <?php if (empty($search_query) && $category === 'all'): ?>
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Browse by Category</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($help_articles as $cat => $data): ?>
                <a href="?category=<?= $cat ?>" class="bg-white rounded-lg shadow-lg p-6 card-hover block">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-<?= $cat === 'getting-started' ? 'rocket' : ($cat === 'cats' ? 'cat' : ($cat === 'games' ? 'gamepad' : ($cat === 'blockchain' ? 'link' : 'cog'))) ?> text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?= $data['title'] ?></h3>
                        <p class="text-gray-600 text-sm"><?= count($data['articles']) ?> articles</p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Article Modal -->
    <div id="articleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-96 overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 id="modalTitle" class="text-2xl font-bold text-gray-900"></h2>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div id="modalContent" class="text-gray-600"></div>
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
        function showArticle(title, content) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalContent').textContent = content;
            document.getElementById('articleModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('articleModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('articleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>

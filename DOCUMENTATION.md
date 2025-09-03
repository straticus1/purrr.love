# 📚 Purrr.love Complete Documentation

Welcome to the comprehensive technical documentation for Purrr.love, the advanced feline gaming ecosystem with cryptocurrency integration, AI-powered interactions, and enterprise-grade security.

## 📋 Table of Contents

- [System Architecture](#system-architecture)
- [Game Mechanics](#game-mechanics)
- [API Documentation](#api-documentation)
- [Database Schema](#database-schema)
- [Security Model](#security-model)
- [Cat Behavior System](#cat-behavior-system)
- [Cryptocurrency Integration](#cryptocurrency-integration)
- [AI & Machine Learning](#ai--machine-learning)
- [Development Guide](#development-guide)
- [Advanced Features](#advanced-features)
- [Testing](#testing)
- [Performance Optimization](#performance-optimization)

## 🏗️ System Architecture

### Overview

Purrr.love is built as a modular, multi-tier web application with the following architectural components:

```
┌─────────────────────────────────────────────────────────┐
│                    Frontend Layer                        │
├─────────────────────────────────────────────────────────┤
│  Web App (PHP) │ Desktop App │ CLI Tool │ Mobile App    │
├─────────────────────────────────────────────────────────┤
│                     API Layer                           │
├─────────────────────────────────────────────────────────┤
│  REST API │ OAuth2 Server │ Rate Limiting │ WebSockets  │
├─────────────────────────────────────────────────────────┤
│                   Business Logic                        │
├─────────────────────────────────────────────────────────┤
│ Cat Behavior │ Genetics │ AI Learning │ Crypto Trading │
├─────────────────────────────────────────────────────────┤
│                   Data Access Layer                     │
├─────────────────────────────────────────────────────────┤
│  MySQL/PostgreSQL │ Redis Cache │ File Storage         │
├─────────────────────────────────────────────────────────┤
│                External Integrations                    │
├─────────────────────────────────────────────────────────┤
│ Coinbase │ OpenAI │ Stability AI │ OAuth Providers     │
└─────────────────────────────────────────────────────────┘
```

### Core Components

1. **Frontend Components**:
   - **Web Application**: PHP-based responsive web interface
   - **Desktop Application**: Electron-based cross-platform app
   - **CLI Tool**: Command-line interface for developers and power users
   - **Mobile App**: React Native app (planned)

2. **Backend Services**:
   - **Core API**: RESTful API with comprehensive endpoints
   - **OAuth2 Server**: Complete OAuth2 implementation with PKCE
   - **Rate Limiting**: Tier-based API rate limiting system
   - **WebSocket Server**: Real-time communication for multiplayer features

3. **Data Layer**:
   - **Primary Database**: MySQL/PostgreSQL for application data
   - **Cache Layer**: Redis for session storage and performance caching
   - **File Storage**: Local filesystem or S3 for cat images and assets

4. **External Services**:
   - **Cryptocurrency**: Coinbase Commerce for payments
   - **AI Services**: OpenAI and Stability AI for cat generation
   - **Authentication**: OAuth2 providers (Google, Facebook, Apple, Twitter)

## 🎮 Game Mechanics

### Cat Stats System

Each cat in Purrr.love has comprehensive statistics that affect gameplay:

#### Core Stats
```php
// Basic cat statistics
$catStats = [
    'happiness' => 0-100,     // Affects all activities and breeding success
    'energy' => 0-100,        // Required for games and activities
    'hunger' => 0-100,        // Decreases over time, affects mood
    'mood' => 0-100,          // Dynamic based on care and environment
    'health' => 0-100,        // Long-term wellbeing indicator
    'cleanliness' => 0-100,   // Affects happiness and social interactions
    'level' => 1-50,          // Experience-based progression
    'experience' => 0-∞       // Earned through activities and care
];
```

#### Advanced Stats
```php
// Advanced cat attributes
$advancedStats = [
    'intelligence' => 0-100,   // Training speed and puzzle solving
    'strength' => 0-100,       // Physical activities and hunting success
    'agility' => 0-100,        // Climbing and reaction-based games
    'charisma' => 0-100,       // Social interactions and breeding appeal
    'training' => 0-100,       // Command obedience and trick knowledge
    'social' => 0-100          // Interaction comfort with other cats
];
```

### Personality System

Each cat has a unique personality type that influences behavior:

#### Personality Types
1. **Playful** 🎾
   - Increased energy from play activities
   - Prefers interactive games and toys
   - Faster XP gain from gaming activities
   - Higher success rates in agility-based games

2. **Aloof** 😼
   - Independent behavior patterns
   - Prefers quiet activities and observation
   - Lower social interaction needs
   - Higher success in hunting and stealth games

3. **Curious** 🔍
   - Bonus XP from exploration and quests
   - Higher intelligence stat growth
   - Prefers puzzle games and new experiences
   - Better at finding rare items during adventures

4. **Lazy** 😴
   - Lower energy consumption during activities
   - Prefers passive games and relaxation
   - Slower XP gain but higher happiness baseline
   - Better at patience-based activities

5. **Territorial** 🏠
   - Enhanced territory bonuses
   - Prefers defending and marking territory
   - Higher strength stat growth
   - Better at competitive multiplayer games

6. **Social Butterfly** 🦋
   - Bonus XP from social interactions
   - Prefers multiplayer activities
   - Higher charisma stat growth
   - Better breeding compatibility with all personalities

### Cat Genetics System

The breeding system uses advanced genetics simulation:

#### Genetic Traits
```php
// Genetic trait inheritance system
$geneticTraits = [
    'coat_color' => ['dominant', 'recessive'],
    'coat_pattern' => ['dominant', 'recessive'],
    'eye_color' => ['dominant', 'recessive'],
    'breed_traits' => ['breed_specific_abilities'],
    'personality_base' => ['inherited_personality_tendencies'],
    'stat_modifiers' => ['strength', 'agility', 'intelligence', 'charisma']
];
```

#### Breeding Mechanics
- **Compatibility System**: Personality and breed compatibility affects success rates
- **Inheritance Rules**: Mendelian genetics with some randomization
- **Mutation Chances**: Rare mutations can create unique traits
- **Breeding Cooldown**: Prevents excessive breeding
- **Enhancement Options**: AI-assisted breeding and genetic enhancement available

### Quest System

Dynamic quest system with multiple categories:

#### Quest Types
1. **Daily Quests**: Reset every 24 hours
2. **Seasonal Quests**: Special activities for each season
3. **Personality Quests**: Tailored to each cat's personality
4. **Achievement Quests**: Long-term goals and milestones
5. **Community Quests**: Collaborative goals with other players

#### Quest Mechanics
```php
// Quest completion tracking
$questProgress = [
    'quest_id' => 'unique_quest_identifier',
    'requirements' => [
        'feed_cat' => ['target' => 10, 'current' => 5],
        'play_games' => ['target' => 3, 'current' => 1],
        'win_crypto' => ['target' => 0.001, 'current' => 0.0005]
    ],
    'rewards' => [
        'experience' => 100,
        'crypto' => ['BTC' => 0.0001],
        'items' => ['premium_cat_food' => 5]
    ]
];
```

## 🚀 API Documentation

### Authentication

The API supports two authentication methods:

#### 1. OAuth2 Authentication
```bash
# Get authorization code
GET /api/v1/oauth/authorize?client_id=CLIENT_ID&redirect_uri=REDIRECT_URI&response_type=code&scope=read%20write

# Exchange code for tokens
POST /api/v1/oauth/token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code&code=AUTH_CODE&client_id=CLIENT_ID&client_secret=CLIENT_SECRET&redirect_uri=REDIRECT_URI
```

#### 2. API Key Authentication
```bash
# Include API key in request headers
GET /api/v1/cats
X-API-Key: pk_your_api_key_here
```

### Core Endpoints

#### Cat Management Endpoints

**List User's Cats**
```bash
GET /api/v1/cats
Authorization: Bearer ACCESS_TOKEN

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Whiskers",
      "personality_type": "playful",
      "level": 5,
      "happiness": 85,
      "energy": 60,
      "image_url": "https://example.com/cats/whiskers.jpg"
    }
  ]
}
```

**Get Cat Details**
```bash
GET /api/v1/cats/{id}
Authorization: Bearer ACCESS_TOKEN

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Whiskers",
    "personality_type": "playful",
    "breed": "maine_coon",
    "level": 5,
    "experience": 1250,
    "stats": {
      "happiness": 85,
      "energy": 60,
      "hunger": 30,
      "mood": 75,
      "health": 95,
      "cleanliness": 80
    },
    "advanced_stats": {
      "intelligence": 70,
      "strength": 65,
      "agility": 80,
      "charisma": 75,
      "training": 60,
      "social": 85
    }
  }
}
```

**Feed Cat**
```bash
POST /api/v1/cats/{id}/feed
Authorization: Bearer ACCESS_TOKEN
Content-Type: application/json

{
  "food_item": "premium_cat_food",
  "quantity": 1
}

Response:
{
  "success": true,
  "data": {
    "hunger_restored": 25,
    "happiness_gained": 10,
    "energy_gained": 5,
    "new_stats": {
      "happiness": 95,
      "energy": 65,
      "hunger": 55
    }
  }
}
```

#### Game Endpoints

**List Available Games**
```bash
GET /api/v1/games
Authorization: Bearer ACCESS_TOKEN

Response:
{
  "success": true,
  "data": [
    {
      "type": "paw_match",
      "name": "Enhanced Paw Match",
      "description": "Cat-themed matching game",
      "entry_fee_usd": 1.0,
      "max_win_multiplier": 2.0
    }
  ]
}
```

**Play Game**
```bash
POST /api/v1/games/{type}/play
Authorization: Bearer ACCESS_TOKEN
Content-Type: application/json

{
  "cat_id": 1,
  "crypto_type": "BTC"
}

Response:
{
  "success": true,
  "data": {
    "result": "win",
    "win_amount": 0.0001,
    "crypto_type": "BTC",
    "game_data": {
      "score": 850,
      "time": 45.2,
      "difficulty": "medium"
    }
  }
}
```

#### Breeding Endpoints

**Get Breeding Pairs**
```bash
GET /api/v1/breeding/pairs
Authorization: Bearer ACCESS_TOKEN

Response:
{
  "success": true,
  "data": [
    {
      "mother_id": 1,
      "father_id": 2,
      "compatibility_score": 85,
      "success_rate": 75,
      "predicted_traits": {
        "personality": "playful",
        "coat_color": "orange_tabby"
      }
    }
  ]
}
```

**Start Breeding**
```bash
POST /api/v1/breeding/breed
Authorization: Bearer ACCESS_TOKEN
Content-Type: application/json

{
  "mother_id": 1,
  "father_id": 2,
  "breeding_method": "natural"
}

Response:
{
  "success": true,
  "data": {
    "breeding_id": 123,
    "estimated_completion": "2025-09-03T12:00:00Z",
    "success_probability": 75
  }
}
```

#### Economy Endpoints

**Get Crypto Balance**
```bash
GET /api/v1/economy/balance
Authorization: Bearer ACCESS_TOKEN

Response:
{
  "success": true,
  "data": {
    "BTC": 0.00125,
    "ETH": 0.05,
    "USDC": 25.50,
    "SOL": 1.25,
    "XRP": 100.0
  }
}
```

**Create Deposit**
```bash
POST /api/v1/economy/deposit
Authorization: Bearer ACCESS_TOKEN
Content-Type: application/json

{
  "crypto_type": "BTC",
  "amount_usd": 10.00
}

Response:
{
  "success": true,
  "data": {
    "payment_url": "https://commerce.coinbase.com/charges/CHARGE_ID",
    "charge_id": "CHARGE_ID",
    "expires_at": "2025-09-03T12:00:00Z"
  }
}
```

### Rate Limiting

API endpoints are rate-limited based on user tiers:

| Tier | Requests/Hour | Features |
|------|---------------|----------|
| Free | 100 | Basic API access |
| Premium | 1,000 | Enhanced features |
| Enterprise | 10,000 | Full access + priority support |

Rate limit headers are included in all responses:
- `X-RateLimit-Limit`: Total requests allowed
- `X-RateLimit-Remaining`: Remaining requests in current window
- `X-RateLimit-Reset`: Timestamp when rate limit resets

## 🗄️ Database Schema

### Core Tables

#### Users Table
```sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
    subscription_tier ENUM('free', 'premium', 'enterprise') DEFAULT 'free',
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(32),
    email_verified BOOLEAN DEFAULT FALSE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Cats Table
```sql
CREATE TABLE cats (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    species VARCHAR(50) DEFAULT 'cat',
    breed VARCHAR(100),
    personality_type ENUM('playful', 'aloof', 'curious', 'lazy', 'territorial', 'social_butterfly'),
    
    -- Core stats
    level INTEGER DEFAULT 1,
    experience INTEGER DEFAULT 0,
    happiness INTEGER DEFAULT 50,
    energy INTEGER DEFAULT 100,
    hunger INTEGER DEFAULT 0,
    mood INTEGER DEFAULT 50,
    health INTEGER DEFAULT 100,
    cleanliness INTEGER DEFAULT 100,
    
    -- Advanced stats
    intelligence INTEGER DEFAULT 50,
    strength INTEGER DEFAULT 50,
    agility INTEGER DEFAULT 50,
    charisma INTEGER DEFAULT 50,
    training INTEGER DEFAULT 0,
    social INTEGER DEFAULT 50,
    
    -- Breeding and genetics
    genetic_data JSON,
    breeding_cooldown_until TIMESTAMP NULL,
    
    -- Metadata
    image_url VARCHAR(500),
    description TEXT,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_care_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Crypto Balances Table
```sql
CREATE TABLE crypto_balances (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    crypto_type ENUM('BTC', 'ETH', 'USDC', 'SOL', 'XRP'),
    balance DECIMAL(20, 8) DEFAULT 0.0,
    locked_balance DECIMAL(20, 8) DEFAULT 0.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_crypto (user_id, crypto_type)
);
```

#### Game Results Table
```sql
CREATE TABLE game_results (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    game_type VARCHAR(50) NOT NULL,
    crypto_type ENUM('BTC', 'ETH', 'USDC', 'SOL', 'XRP'),
    entry_fee DECIMAL(20, 8),
    win_amount DECIMAL(20, 8) DEFAULT 0.0,
    result ENUM('win', 'loss') NOT NULL,
    game_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Security Tables

#### API Keys Table
```sql
CREATE TABLE api_keys (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    key_hash VARCHAR(255) UNIQUE NOT NULL,
    scopes JSON NOT NULL,
    expires_at TIMESTAMP NULL,
    ip_whitelist JSON NULL,
    last_used_at TIMESTAMP NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### OAuth2 Tokens Table
```sql
CREATE TABLE oauth2_access_tokens (
    id SERIAL PRIMARY KEY,
    token VARCHAR(255) UNIQUE NOT NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    client_id INTEGER REFERENCES oauth2_clients(id) ON DELETE CASCADE,
    scope VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    revoked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Advanced Feature Tables

#### Cat Genetics Table
```sql
CREATE TABLE cat_genetics (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    genetic_sequence JSON NOT NULL,
    dominant_traits JSON,
    recessive_traits JSON,
    mutation_data JSON,
    breeding_value_score INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### AI Learning Table
```sql
CREATE TABLE ai_cat_learning (
    id SERIAL PRIMARY KEY,
    cat_id INTEGER REFERENCES cats(id) ON DELETE CASCADE,
    interaction_type VARCHAR(100) NOT NULL,
    input_data JSON,
    behavioral_response JSON,
    learning_insights JSON,
    confidence_score DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🔐 Security Model

### Authentication Flow

1. **User Authentication**:
   ```php
   // Login process with security checks
   function authenticateUser($username, $password) {
       // Check rate limiting
       if (checkLoginAttempts($username)) {
           throw new Exception('Too many login attempts');
       }
       
       // Verify credentials
       $user = getUserByUsername($username);
       if (!$user || !password_verify($password, $user['password_hash'])) {
           recordFailedLogin($username);
           throw new Exception('Invalid credentials');
       }
       
       // Regenerate session ID
       session_regenerate_id(true);
       
       // Set session variables
       $_SESSION['user_id'] = $user['id'];
       $_SESSION['csrf_token'] = generateCSRFToken();
       
       return $user;
   }
   ```

2. **CSRF Protection**:
   ```php
   // CSRF token validation
   function requireCSRFToken() {
       if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
           throw new Exception('CSRF token missing');
       }
       
       if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
           throw new Exception('CSRF token validation failed');
       }
   }
   ```

3. **Input Sanitization**:
   ```php
   function sanitizeInput($input, $type = 'string') {
       switch ($type) {
           case 'int':
               return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
           case 'email':
               return filter_var($input, FILTER_SANITIZE_EMAIL);
           case 'url':
               return filter_var($input, FILTER_SANITIZE_URL);
           default:
               return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
       }
   }
   ```

### API Security

1. **Rate Limiting Implementation**:
   ```php
   function checkRateLimit($identifier, $endpoint) {
       $window = time() - (time() % 3600); // 1-hour windows
       $key = "rate_limit:{$identifier}:{$endpoint}:{$window}";
       
       $redis = getRedisConnection();
       $current = $redis->get($key) ?? 0;
       $limit = getEndpointRateLimit($endpoint);
       
       if ($current >= $limit) {
           throw new Exception('Rate limit exceeded', 429);
       }
       
       $redis->incr($key);
       $redis->expire($key, 3600);
       
       return [
           'limit' => $limit,
           'remaining' => $limit - $current - 1,
           'reset' => $window + 3600
       ];
   }
   ```

2. **SQL Injection Prevention**:
   ```php
   // All database queries use prepared statements
   function getCatById($catId, $userId) {
       $pdo = get_db();
       $stmt = $pdo->prepare("SELECT * FROM cats WHERE id = ? AND user_id = ?");
       $stmt->execute([$catId, $userId]);
       return $stmt->fetch(PDO::FETCH_ASSOC);
   }
   ```

## 🐱 Cat Behavior System

### Personality-Based Behavior

Each cat personality type has unique behaviors and preferences:

```php
class CatBehavior {
    private $personalityBehaviors = [
        'playful' => [
            'preferred_activities' => ['games', 'toys', 'interactive_play'],
            'energy_multiplier' => 1.2,
            'happiness_from_play' => 1.5,
            'social_preference' => 'high',
            'activity_schedule' => [
                'morning' => 'high_energy',
                'afternoon' => 'moderate_energy',
                'evening' => 'high_energy',
                'night' => 'low_energy'
            ]
        ],
        'aloof' => [
            'preferred_activities' => ['solo_activities', 'observation', 'hunting'],
            'energy_multiplier' => 0.9,
            'happiness_from_solitude' => 1.3,
            'social_preference' => 'low',
            'independence_level' => 'high'
        ]
        // ... other personality types
    ];
}
```

### AI Learning System

The AI learns from user interactions to create more realistic cat behaviors:

```php
function processInteractionForAILearning($catId, $interactionType, $input, $context, $outcome) {
    $learningData = [
        'cat_id' => $catId,
        'interaction_type' => $interactionType,
        'input_data' => json_encode($input),
        'context_data' => json_encode($context),
        'behavioral_response' => json_encode($outcome),
        'timestamp' => time()
    ];
    
    // Store interaction for learning
    storeAILearningData($learningData);
    
    // Analyze patterns
    $insights = analyzeInteractionPatterns($catId);
    
    // Update cat's AI behavior model
    updateCatAIModel($catId, $insights);
    
    return $insights;
}
```

### Territory System

Cats can claim and defend territories for bonuses:

```php
class TerritorySystem {
    public function claimTerritory($catId, $territoryType, $location) {
        // Check if cat can claim territory
        $cat = getCatById($catId);
        if ($cat['energy'] < 50 || $cat['level'] < 3) {
            return ['success' => false, 'message' => 'Cat not ready for territory'];
        }
        
        // Check territory availability
        $existingClaim = getTerritoryClaim($location);
        if ($existingClaim && $existingClaim['cat_id'] !== $catId) {
            // Territory conflict - initiate challenge
            return $this->initiateTerritoryChallenge($catId, $existingClaim['cat_id']);
        }
        
        // Claim territory
        $territory = [
            'cat_id' => $catId,
            'territory_type' => $territoryType,
            'location' => $location,
            'bonus_multiplier' => $this->getTerritoryBonus($territoryType),
            'claimed_at' => time()
        ];
        
        storeTerritoryCllaim($territory);
        
        return ['success' => true, 'territory' => $territory];
    }
}
```

## 💰 Cryptocurrency Integration

### Payment Processing

Cryptocurrency payments are handled through Coinbase Commerce:

```php
class CryptoPayments {
    public function createPayment($userId, $amountUSD, $cryptoType) {
        // Convert USD to crypto amount
        $cryptoAmount = $this->convertUSDToCrypto($amountUSD, $cryptoType);
        
        // Create Coinbase Commerce charge
        $charge = [
            'name' => 'Purrr.love Deposit',
            'description' => "Deposit to Purrr.love account",
            'local_price' => [
                'amount' => $cryptoAmount,
                'currency' => $cryptoType
            ],
            'metadata' => [
                'user_id' => $userId,
                'crypto_type' => $cryptoType
            ]
        ];
        
        $response = $this->coinbaseAPI->createCharge($charge);
        
        // Store pending payment
        $this->storePendingPayment($userId, $response['id'], $cryptoAmount, $cryptoType);
        
        return $response;
    }
}
```

### Withdrawal System

Secure withdrawal system with 2FA verification:

```php
function processWithdrawal($userId, $cryptoType, $amount, $address) {
    // Verify 2FA
    if (!verify2FA($userId, $_POST['2fa_code'])) {
        throw new Exception('2FA verification failed');
    }
    
    // Check balance
    $balance = getUserCryptoBalance($userId, $cryptoType);
    if ($balance < $amount) {
        throw new Exception('Insufficient balance');
    }
    
    // Create withdrawal request
    $withdrawal = [
        'user_id' => $userId,
        'crypto_type' => $cryptoType,
        'amount' => $amount,
        'address' => $address,
        'status' => 'pending',
        'created_at' => time()
    ];
    
    $withdrawalId = storeWithdrawalRequest($withdrawal);
    
    // Queue for processing
    queueWithdrawalProcessing($withdrawalId);
    
    return $withdrawal;
}
```

## 🤖 AI & Machine Learning

### AI Cat Generation

Integration with OpenAI and Stability AI for cat image generation:

```php
class AIImageGeneration {
    public function generateCatImage($prompt, $style = 'realistic') {
        $enhancedPrompt = $this->enhancePromptForCats($prompt, $style);
        
        // Try OpenAI first
        try {
            return $this->generateWithOpenAI($enhancedPrompt);
        } catch (Exception $e) {
            // Fallback to Stability AI
            return $this->generateWithStabilityAI($enhancedPrompt);
        }
    }
    
    private function enhancePromptForCats($prompt, $style) {
        $basePrompt = "High quality, detailed cat image";
        $styleModifiers = [
            'realistic' => 'photorealistic, professional photography',
            'artistic' => 'digital art, stylized illustration',
            'cartoon' => 'cute cartoon style, animated'
        ];
        
        return $basePrompt . ', ' . $prompt . ', ' . $styleModifiers[$style];
    }
}
```

### Behavior Learning System

AI system that learns from cat interactions:

```php
class CatBehaviorLearning {
    public function analyzeBehaviorPatterns($catId) {
        // Get interaction history
        $interactions = getCatInteractionHistory($catId, 30); // Last 30 days
        
        // Analyze patterns
        $patterns = [
            'activity_preferences' => $this->analyzeActivityPreferences($interactions),
            'social_behavior' => $this->analyzeSocialBehavior($interactions),
            'learning_speed' => $this->calculateLearningSpeed($interactions),
            'personality_consistency' => $this->checkPersonalityConsistency($interactions)
        ];
        
        // Generate behavior predictions
        $predictions = $this->generateBehaviorPredictions($patterns);
        
        // Store insights
        $this->storeAIInsights($catId, $patterns, $predictions);
        
        return $patterns;
    }
}
```

## 💻 Development Guide

### Setting Up Development Environment

1. **Clone and Setup**:
   ```bash
   git clone https://github.com/straticus1/purrr.love.git
   cd purrr.love
   composer install
   cp config/config.example.php config/config.php
   # Edit configuration for development
   ```

2. **Development Configuration**:
   ```php
   // config/config.php - Development settings
   define('ENVIRONMENT', 'development');
   define('DEBUG_MODE', true);
   define('DEVELOPER_MODE', true);
   define('ERROR_REPORTING', true);
   define('ENABLE_CRYPTO', false); // Disable real crypto in dev
   ```

3. **Database Setup**:
   ```bash
   # Create development database
   mysql -e "CREATE DATABASE purrr_love_dev;"
   mysql purrr_love_dev < database/schema.sql
   
   # Seed with test data
   mysql purrr_love_dev < database/test_data.sql
   ```

### Code Structure

```
purrr.love/
├── api/                    # API endpoints and handlers
│   ├── index.php          # Main API entry point
│   └── v2/                # API version 2 (enhanced features)
├── cli/                   # Command-line interface
│   ├── purrr              # Main CLI executable
│   └── package.json       # CLI dependencies
├── config/                # Configuration files
├── database/              # Database schema and migrations
├── deployment/            # Deployment configurations
│   ├── aws/               # AWS deployment files
│   └── rocky-linux/       # Rocky Linux deployment
├── includes/              # Core PHP includes
│   ├── functions.php      # Core functions
│   ├── cat_behavior.php   # Cat behavior system
│   ├── oauth2.php         # OAuth2 implementation
│   └── api_keys.php       # API key management
├── assets/                # Static assets (CSS, JS, images)
├── uploads/               # User uploaded files
├── cache/                 # Application cache
├── logs/                  # Application logs
└── public/                # Web root (optional for web server config)
```

### Coding Standards

1. **PHP Standards**:
   - Follow PSR-12 coding standards
   - Use strict types: `declare(strict_types=1);`
   - Comprehensive error handling with try-catch blocks
   - Proper input validation and sanitization

2. **Database Standards**:
   - All queries use prepared statements
   - Foreign key constraints for data integrity
   - Proper indexing for performance
   - Use transactions for related operations

3. **Security Standards**:
   - CSRF protection on all forms
   - Input validation and sanitization
   - Output escaping for XSS prevention
   - Secure password hashing with bcrypt

### Adding New Features

1. **API Endpoints**:
   ```php
   // Example: Adding a new cat training endpoint
   function handleCatTraining($catId, $userId, $params) {
       // Verify ownership
       if (!canAccessCat($catId, $userId)) {
           throw new Exception('Access denied', 403);
       }
       
       // Validate parameters
       $validCommands = ['sit', 'stay', 'come', 'fetch'];
       if (!in_array($params['command'], $validCommands)) {
           throw new Exception('Invalid command', 400);
       }
       
       // Process training
       $result = processCatTraining($catId, $params['command']);
       
       return $result;
   }
   ```

2. **Database Migrations**:
   ```sql
   -- Add new column to cats table
   ALTER TABLE cats ADD COLUMN training_level INTEGER DEFAULT 0;
   
   -- Create new table for training sessions
   CREATE TABLE training_sessions (
       id SERIAL PRIMARY KEY,
       cat_id INTEGER REFERENCES cats(id),
       command VARCHAR(50),
       success BOOLEAN,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

### Testing

1. **Unit Tests**:
   ```bash
   # Run all tests
   ./vendor/bin/phpunit
   
   # Run specific test suite
   ./vendor/bin/phpunit tests/Unit/CatBehaviorTest.php
   
   # Run with coverage
   ./vendor/bin/phpunit --coverage-html coverage/
   ```

2. **API Testing**:
   ```bash
   # Test API endpoints
   curl -H "X-API-Key: pk_test_key" http://localhost:8000/api/v1/cats
   
   # Test OAuth2 flow
   curl -X POST http://localhost:8000/api/v1/oauth/token \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "grant_type=client_credentials&client_id=test&client_secret=test"
   ```

## 🔬 Advanced Features

### VR Integration

WebVR support for immersive cat interactions:

```javascript
// VR interaction system
class CatVRInteraction {
    async initializeVR(catId) {
        // Check VR support
        if (!navigator.getVRDisplays) {
            throw new Error('VR not supported');
        }
        
        // Get VR displays
        const displays = await navigator.getVRDisplays();
        if (displays.length === 0) {
            throw new Error('No VR displays found');
        }
        
        this.vrDisplay = displays[0];
        this.catId = catId;
        
        // Initialize VR session
        await this.setupVRScene();
        
        return {
            success: true,
            displayName: this.vrDisplay.displayName,
            capabilities: this.vrDisplay.capabilities
        };
    }
    
    async petCat(intensity = 1.0) {
        // Send haptic feedback
        if (this.vrDisplay.capabilities.hasExternalDisplay) {
            this.sendHapticFeedback('gentle_pet', intensity);
        }
        
        // Update cat happiness
        const response = await fetch(`/api/v1/cats/${this.catId}/vr-interact`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.accessToken}`
            },
            body: JSON.stringify({
                interaction_type: 'petting',
                intensity: intensity,
                duration: 5.0
            })
        });
        
        return await response.json();
    }
}
```

### Multiplayer System

Real-time multiplayer interactions using WebSockets:

```php
class MultiplayerSystem {
    public function initializeMultiplayerSession($userId, $catId, $roomType) {
        // Create or join room
        $room = $this->findOrCreateRoom($roomType);
        
        // Add cat to room
        $session = [
            'user_id' => $userId,
            'cat_id' => $catId,
            'room_id' => $room['id'],
            'joined_at' => time(),
            'status' => 'active'
        ];
        
        $sessionId = $this->storeMultiplayerSession($session);
        
        // Notify other players
        $this->notifyRoomPlayers($room['id'], 'player_joined', [
            'cat_id' => $catId,
            'session_id' => $sessionId
        ]);
        
        return $session;
    }
}
```

### Health Monitoring Integration

Integration with real pet health tracking devices:

```php
class HealthMonitoring {
    public function registerHealthDevice($userId, $catId, $deviceData) {
        // Validate device type
        $supportedDevices = ['collar_sensor', 'weight_scale', 'activity_tracker'];
        if (!in_array($deviceData['device_type'], $supportedDevices)) {
            throw new Exception('Unsupported device type');
        }
        
        // Register device
        $device = [
            'user_id' => $userId,
            'cat_id' => $catId,
            'device_type' => $deviceData['device_type'],
            'device_id' => $deviceData['device_id'],
            'api_key' => $deviceData['api_key'],
            'registered_at' => time()
        ];
        
        $deviceId = $this->storeHealthDevice($device);
        
        // Set up data sync
        $this->setupDeviceDataSync($deviceId);
        
        return $device;
    }
}
```

## 🧪 Testing

### Test Categories

1. **Unit Tests**: Individual function and class testing
2. **Integration Tests**: API endpoint and database integration testing
3. **Security Tests**: Authentication, authorization, and input validation testing
4. **Performance Tests**: Load testing and optimization validation

### Running Tests

```bash
# Install test dependencies
composer install --dev

# Run all tests
./vendor/bin/phpunit

# Run specific test categories
./vendor/bin/phpunit --group=unit
./vendor/bin/phpunit --group=integration
./vendor/bin/phpunit --group=security
./vendor/bin/phpunit --group=api

# Generate coverage report
./vendor/bin/phpunit --coverage-html=coverage/
```

### Test Examples

```php
// Example unit test for cat feeding
class CatCareTest extends PHPUnit\Framework\TestCase {
    public function testFeedCat() {
        $catId = $this->createTestCat();
        $foodItem = 'premium_cat_food';
        
        // Get initial stats
        $initialStats = getCatStats($catId);
        
        // Feed the cat
        $result = feedCat($catId, $foodItem, 1);
        
        // Verify result
        $this->assertTrue($result['success']);
        $this->assertGreaterThan($initialStats['hunger'], $result['new_stats']['hunger']);
        
        // Clean up
        $this->deleteTestCat($catId);
    }
}

// Example API test
class APITest extends PHPUnit\Framework\TestCase {
    public function testGetCatsEndpoint() {
        // Create test user and cat
        $userId = $this->createTestUser();
        $catId = $this->createTestCat($userId);
        
        // Get API key
        $apiKey = $this->generateTestAPIKey($userId);
        
        // Make API request
        $response = $this->get('/api/v1/cats', [
            'X-API-Key' => $apiKey
        ]);
        
        // Verify response
        $this->assertEquals(200, $response->status());
        $this->assertTrue($response->json('success'));
        $this->assertCount(1, $response->json('data'));
        
        // Clean up
        $this->cleanup([$userId, $catId]);
    }
}
```

## ⚡ Performance Optimization

### Caching Strategy

1. **Database Query Caching**:
   ```php
   function getCachedCatStats($catId, $cacheTime = 300) {
       $cacheKey = "cat_stats:{$catId}";
       $redis = getRedisConnection();
       
       $cached = $redis->get($cacheKey);
       if ($cached) {
           return json_decode($cached, true);
       }
       
       $stats = calculateCatStats($catId);
       $redis->setex($cacheKey, $cacheTime, json_encode($stats));
       
       return $stats;
   }
   ```

2. **Static Asset Optimization**:
   - CSS/JS minification and compression
   - Image optimization with WebP format
   - CDN integration with CloudFront
   - Browser caching headers

3. **Database Optimization**:
   ```sql
   -- Key indexes for performance
   CREATE INDEX idx_cats_user_level ON cats(user_id, level DESC);
   CREATE INDEX idx_crypto_balances_user_type ON crypto_balances(user_id, crypto_type);
   CREATE INDEX idx_game_results_cat_created ON game_results(cat_id, created_at DESC);
   CREATE INDEX idx_api_requests_user_endpoint ON api_requests(user_id, endpoint);
   ```

### Performance Monitoring

```php
class PerformanceMonitor {
    public function trackAPIPerformance($endpoint, $method, $startTime, $statusCode) {
        $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        
        // Store performance data
        $this->storePerformanceData([
            'endpoint' => $endpoint,
            'method' => $method,
            'response_time' => $responseTime,
            'status_code' => $statusCode,
            'timestamp' => time()
        ]);
        
        // Alert if response time is too high
        if ($responseTime > 5000) { // 5 seconds
            $this->alertSlowResponse($endpoint, $responseTime);
        }
    }
}
```

## 🔧 Configuration Management

### Environment-Specific Configurations

```php
// config/environments/development.php
return [
    'database' => [
        'host' => 'localhost',
        'name' => 'purrr_love_dev',
        'user' => 'dev_user',
        'pass' => 'dev_password'
    ],
    'features' => [
        'crypto_payments' => false,
        'ai_generation' => true,
        'debug_mode' => true
    ],
    'external_apis' => [
        'openai_key' => 'test_key',
        'coinbase_key' => 'test_key'
    ]
];

// config/environments/production.php
return [
    'database' => [
        'host' => 'db.example.com',
        'name' => 'purrr_love',
        'user' => getenv('DB_USER'),
        'pass' => getenv('DB_PASS')
    ],
    'features' => [
        'crypto_payments' => true,
        'ai_generation' => true,
        'debug_mode' => false
    ],
    'external_apis' => [
        'openai_key' => getenv('OPENAI_KEY'),
        'coinbase_key' => getenv('COINBASE_KEY')
    ]
];
```

### CLI Configuration

The CLI tool stores configuration in user's home directory:

```bash
# CLI configuration location
~/.purrr/config.json

# Example CLI configuration
{
  "server_url": "https://api.purrr.love",
  "api_key": "pk_your_api_key",
  "default_cat_id": 1,
  "output_format": "table",
  "color_output": true
}
```

## 🚀 Deployment Strategies

### Continuous Deployment

GitHub Actions workflow for automated deployment:

```yaml
# .github/workflows/deploy.yml
name: Deploy Purrr.love

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
      - run: composer install
      - run: ./vendor/bin/phpunit

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - uses: actions/checkout@v2
      - name: Deploy to AWS
        env:
          AWS_ACCESS_KEY_ID: ${{ secrets.AWS_ACCESS_KEY_ID }}
          AWS_SECRET_ACCESS_KEY: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
        run: |
          ./deploy.sh --aws --environment production
```

### Blue-Green Deployment

For zero-downtime deployments:

```bash
# Deploy to staging environment
./deploy.sh --aws --environment staging

# Run smoke tests
./scripts/smoke-tests.sh staging

# Switch traffic to new version
aws elbv2 modify-target-group --target-group-arn $STAGING_TG_ARN --health-check-path /health
aws elbv2 modify-listener --listener-arn $LISTENER_ARN --default-actions Type=forward,TargetGroupArn=$STAGING_TG_ARN
```

## 📊 Monitoring & Analytics

### Application Monitoring

```php
class ApplicationMonitor {
    public function recordMetric($metric, $value, $tags = []) {
        $data = [
            'metric' => $metric,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => time()
        ];
        
        // Store metric
        $this->storeMetric($data);
        
        // Send to external monitoring service
        $this->sendToCloudWatch($data);
    }
    
    public function checkHealthStatus() {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'redis' => $this->checkRedisHealth(),
            'external_apis' => $this->checkExternalAPIHealth(),
            'disk_space' => $this->checkDiskSpace(),
            'memory_usage' => $this->getMemoryUsage()
        ];
        
        return [
            'status' => $this->calculateOverallHealth($health),
            'details' => $health,
            'timestamp' => date('c')
        ];
    }
}
```

### User Analytics

```php
class UserAnalytics {
    public function trackUserActivity($userId, $action, $metadata = []) {
        $event = [
            'user_id' => $userId,
            'action' => $action,
            'metadata' => json_encode($metadata),
            'ip_address' => getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'timestamp' => time()
        ];
        
        $this->storeUserEvent($event);
    }
    
    public function generateUserInsights($userId, $timeframe = '30d') {
        return [
            'activity_summary' => $this->getUserActivitySummary($userId, $timeframe),
            'engagement_score' => $this->calculateEngagementScore($userId, $timeframe),
            'favorite_features' => $this->identifyFavoriteFeatures($userId, $timeframe),
            'recommendations' => $this->generateRecommendations($userId)
        ];
    }
}
```

## 🛡️ Security Considerations

### Data Protection

1. **Personal Data Handling**:
   - GDPR compliance for EU users
   - Data encryption at rest and in transit
   - Right to be forgotten implementation
   - Data export capabilities

2. **Cryptocurrency Security**:
   - Cold storage for large amounts
   - Multi-signature wallets for withdrawals
   - Audit trails for all transactions
   - Automated fraud detection

3. **API Security**:
   - Request signing for sensitive operations
   - IP whitelisting for high-privilege keys
   - Automatic key rotation capabilities
   - Anomaly detection for unusual usage patterns

### Compliance

```php
// GDPR compliance functions
function exportUserData($userId) {
    $userData = [
        'profile' => getUserProfile($userId),
        'cats' => getUserCats($userId),
        'transactions' => getCryptoTransactions($userId),
        'api_usage' => getAPIUsageHistory($userId),
        'interactions' => getUserInteractions($userId)
    ];
    
    return json_encode($userData, JSON_PRETTY_PRINT);
}

function deleteUserData($userId, $reason) {
    // Log deletion request
    logDataDeletion($userId, $reason);
    
    // Anonymize or delete data
    anonymizeUserData($userId);
    
    // Remove API keys and tokens
    revokeAllUserTokens($userId);
    
    // Mark account as deleted
    markAccountDeleted($userId);
}
```

## 🔍 Debugging & Troubleshooting

### Debug Mode

Enable comprehensive debugging:

```php
// config/config.php
define('DEBUG_MODE', true);
define('ERROR_REPORTING', true);
define('LOG_LEVEL', 'DEBUG');

// Enhanced error reporting
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}
```

### Logging System

```php
class Logger {
    public function log($level, $message, $context = []) {
        $logEntry = [
            'timestamp' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'request_id' => getRequestId(),
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        
        // Write to file
        $this->writeToFile($logEntry);
        
        // Send to external service if configured
        if (defined('LOG_EXTERNAL_SERVICE')) {
            $this->sendToExternalService($logEntry);
        }
    }
}
```

### Health Check Endpoint

```php
// health-check.php
function performHealthCheck() {
    $checks = [
        'database' => checkDatabaseConnection(),
        'redis' => checkRedisConnection(),
        'file_permissions' => checkFilePermissions(),
        'external_apis' => checkExternalAPIs(),
        'disk_space' => checkDiskSpace(),
        'memory' => checkMemoryUsage()
    ];
    
    $overall = array_reduce($checks, function($carry, $check) {
        return $carry && $check['status'];
    }, true);
    
    http_response_code($overall ? 200 : 503);
    
    return [
        'status' => $overall ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => date('c'),
        'version' => API_VERSION
    ];
}
```

---

## 📞 Support & Community

- **Documentation**: This file and [API_ECOSYSTEM_SUMMARY.md](API_ECOSYSTEM_SUMMARY.md)
- **Installation Guide**: [INSTALL.md](INSTALL.md)
- **Changelog**: [CHANGELOG.md](CHANGELOG.md)
- **Issues**: [GitHub Issues](https://github.com/straticus1/purrr.love/issues)
- **Discussions**: [GitHub Discussions](https://github.com/straticus1/purrr.love/discussions)

## 🎯 Getting Help

1. **Check Documentation**: Start with this documentation and the installation guide
2. **Search Issues**: Look for existing solutions in GitHub issues
3. **Create New Issue**: If you can't find a solution, create a detailed issue
4. **Join Community**: Participate in discussions and help others

---

**🐱 Purrr.love** - The most comprehensive cat gaming platform ever built! ❤️

*This documentation is continuously updated. Last updated: September 2, 2025*

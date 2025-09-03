# 🐱 Purrr.love Node.js SDK

**Official Node.js client library for the Purrr.love cat gaming platform**

[![npm version](https://badge.fury.io/js/%40purrr-love%2Fsdk.svg)](https://badge.fury.io/js/%40purrr-love%2Fsdk)
[![Node.js version](https://img.shields.io/node/v/@purrr-love/sdk.svg)](https://nodejs.org/)
[![License](https://img.shields.io/npm/l/@purrr-love/sdk.svg)](https://opensource.org/licenses/MIT)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.0-blue.svg)](https://www.typescriptlang.org/)

## 🌟 Features

- **Complete API Coverage** - All Purrr.love API endpoints
- **TypeScript Support** - Full type safety and IntelliSense
- **Advanced Cat Features** - VR interactions, AI learning, trading, shows, multiplayer
- **Health Monitoring** - Integration with real pet health devices
- **OAuth2 Support** - Secure authentication
- **Rate Limiting** - Built-in rate limit handling
- **Error Handling** - Comprehensive exception classes
- **WebSocket Support** - Real-time multiplayer features
- **Retry Logic** - Automatic retry with exponential backoff
- **Modern JavaScript** - ES2020+ features and async/await

## 🚀 Quick Start

### Installation

```bash
npm install @purrr-love/sdk
# or
yarn add @purrr-love/sdk
```

### Basic Usage

```typescript
import { PurrrLoveClient } from '@purrr-love/sdk';

// Initialize client
const client = new PurrrLoveClient({
  baseUrl: 'https://api.purrr.love',
  apiKey: 'your_api_key_here'
});

// Get your cats
const cats = await client.getCats();
cats.forEach(cat => {
  console.log(`🐱 ${cat.name} - Level ${cat.level}`);
});

// Play with a cat
const result = await client.playWithCat(
  cats[0].id,
  'laser_pointer',
  15
);
console.log('Play session result:', result);
```

## 🐱 Advanced Features

### VR Cat Interaction

```typescript
// Start VR session
const session = await client.startVRSession(
  cat.id,
  'webvr'
);

// Interact in VR
const response = await client.vrInteract(
  session.session_id,
  'petting',
  {
    location: 'head',
    intensity: 'gentle'
  }
);
```

### AI Cat Behavior Learning

```typescript
// Get AI insights
const insights = await client.getAIInsights(cat.id);
console.log('Learning progress:', insights.learning_progress);
console.log('Behavior patterns:', insights.behavior_patterns);
```

### Cross-Platform Cat Trading

```typescript
// Create trading offer
const offer = await client.createTradingOffer({
  cat_id: cat.id,
  price: 100.0,
  description: 'Beautiful Persian cat, well-trained',
  currency: 'USD'
});

// Browse available offers
const offers = await client.getTradingOffers({ breed: 'persian' });
offers.forEach(offer => {
  console.log(`Cat for sale: $${offer.price} - ${offer.description}`);
});
```

### Cat Show Competitions

```typescript
// Find cat shows
const shows = await client.getCatShows({ status: 'upcoming' });
shows.forEach(show => {
  console.log(`Show: ${show.name} - ${show.startDate}`);
});

// Register your cat
const registration = await client.registerCatForShow(
  cat.id,
  shows[0].id,
  ['beauty', 'personality']
);
```

### Real-time Multiplayer

```typescript
// Join multiplayer room
const session = await client.joinMultiplayerRoom(
  cat.id,
  'playground'
);

// Perform actions
const action = await client.multiplayerAction(
  session.session_id,
  'move',
  {
    direction: 'north',
    speed: 'walk'
  }
);
```

### Health Monitoring

```typescript
// Register health device
const device = await client.registerHealthDevice(cat.id, {
  type: 'smart_collar',
  name: 'WhiskerTracker Pro',
  capabilities: ['activity', 'heart_rate', 'location']
});

// Get health summary
const health = await client.getHealthSummary(cat.id, '7d');
console.log('Health score:', health.overall_score);
```

## 🔐 Authentication

### API Key Authentication

```typescript
const client = new PurrrLoveClient({
  baseUrl: 'https://api.purrr.love',
  apiKey: 'your_api_key_here'
});
```

### OAuth2 Authentication

```typescript
// For OAuth2 flows, you'll need to implement the authorization flow
// The SDK provides methods to work with OAuth2 tokens once obtained
```

## 📊 Data Models

The SDK provides comprehensive data models for all entities:

```typescript
import { Cat, User, ApiKey, TradingOffer, CatShow } from '@purrr-love/sdk';

// All models support serialization/deserialization
const catData = cat.toJSON();
const catFromData = new Cat(catData);

// Models include validation and type safety
console.log(cat.personalityType); // Access enum values
console.log(cat.mood);
```

## 🚨 Error Handling

The SDK provides detailed exception classes:

```typescript
import {
  PurrrLoveError,
  AuthenticationError,
  RateLimitError,
  NotFoundError,
  ValidationError
} from '@purrr-love/sdk';

try {
  const cat = await client.getCat(999);
} catch (error) {
  if (error instanceof NotFoundError) {
    console.log('Cat not found:', error.message);
  } else if (error instanceof RateLimitError) {
    console.log('Rate limited:', error.retryAfter, 'seconds');
  } else if (error instanceof PurrrLoveError) {
    console.log('API error:', error.message);
  }
}
```

## 🔧 Configuration

### Client Configuration

```typescript
const client = new PurrrLoveClient({
  baseUrl: 'https://api.purrr.love',
  apiKey: 'your_api_key_here',
  timeout: 30000, // 30 seconds
  maxRetries: 3,
  retryDelay: 1000,
  version: '1.0.0'
});
```

### Environment Variables

```bash
export PURRR_LOVE_API_KEY="your_api_key"
export PURRR_LOVE_BASE_URL="https://api.purrr.love"
```

```typescript
import { PurrrLoveClient } from '@purrr-love/sdk';

const client = new PurrrLoveClient({
  baseUrl: process.env.PURRR_LOVE_BASE_URL!,
  apiKey: process.env.PURRR_LOVE_API_KEY!
});
```

## 📚 API Reference

### Core Methods

- `getCats(limit?, offset?)` - List user's cats
- `getCat(catId)` - Get specific cat
- `createCat(catData)` - Create new cat
- `updateCat(catId, updates)` - Update cat information
- `deleteCat(catId)` - Delete cat

### Activity Methods

- `playWithCat(catId, gameType, duration)` - Play games
- `trainCat(catId, command, difficulty)` - Train commands
- `careForCat(catId, careType, options)` - Provide care

### Advanced Features

- `startVRSession(catId, vrDevice)` - Start VR interaction
- `getAIInsights(catId)` - Get AI learning insights
- `getTradingOffers(filters)` - Browse trading offers
- `getCatShows(filters)` - Find cat shows
- `joinMultiplayerRoom(catId, roomType)` - Join multiplayer

## 🧪 Testing

```bash
# Install dependencies
npm install

# Run tests
npm test

# Run with coverage
npm run test:coverage

# Run in watch mode
npm run test:watch

# Lint code
npm run lint

# Format code
npm run format
```

## 🔨 Development

```bash
# Clone repository
git clone https://github.com/purrr-love/nodejs-sdk.git
cd nodejs-sdk

# Install dependencies
npm install

# Build TypeScript
npm run build

# Development mode with watch
npm run dev

# Generate documentation
npm run docs
```

## 📦 Building

```bash
# Build for production
npm run build

# Clean build directory
npm run clean

# Prepare for publish
npm run prepublishOnly
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Run the test suite
6. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: [https://docs.purrr.love/nodejs-sdk](https://docs.purrr.love/nodejs-sdk)
- **Issues**: [GitHub Issues](https://github.com/purrr-love/nodejs-sdk/issues)
- **Discord**: [Purrr.love Community](https://discord.gg/purrr-love)
- **Email**: [dev@purrr.love](mailto:dev@purrr.love)

## 🐾 What's Next?

- **WebSocket Client** - Real-time multiplayer
- **GraphQL Support** - Alternative to REST API
- **Mobile SDKs** - React Native support
- **Plugin System** - Extensible functionality
- **Offline Support** - Local caching and sync

---

**Made with ❤️ by the Purrr.love Team**

*Purrr.love - The Ultimate Cat Gaming Platform*

# 🐱 Purrr.love Node.js SDK

Official Node.js client library for the Purrr.love cat gaming platform. This SDK provides easy access to all platform features including cat management, lost pet finder, blockchain/NFT integration, machine learning personality prediction, metaverse/VR worlds, and webhook systems.

## 🚀 Features

### Core Features
- **Cat Management** - Create, read, update, and delete cats
- **Cat Activities** - Play, feed, and groom your cats
- **User Management** - Manage user accounts and API keys
- **TypeScript Support** - Full type definitions and IntelliSense

### Advanced Features
- **🐾 Lost Pet Finder System** - Report lost pets, search, report sightings, and Facebook integration
- **⛓️ Blockchain & NFT Management** - Mint, transfer, and verify cat NFTs across multiple networks
- **🧠 Machine Learning Personality Prediction** - AI-powered cat personality analysis and behavior insights
- **🌐 Metaverse & VR Worlds** - Create and join virtual cat worlds with VR interactions
- **🔗 Webhook System** - Real-time notifications and integrations
- **📊 Analytics Dashboard** - Comprehensive platform analytics and insights

## 📦 Installation

```bash
npm install @purrr-love/sdk
```

Or install with yarn:

```bash
yarn add @purrr-love/sdk
```

## 🔑 Quick Start

```typescript
import { PurrrLoveClient } from '@purrr-love/sdk';

// Initialize the client
const client = new PurrrLoveClient({
  baseUrl: 'https://api.purrr.love',
  apiKey: 'your_api_key_here',
  timeout: 30000,
  maxRetries: 3
});

// Get your cats
const cats = await client.getCats();
console.log(`You have ${cats.length} cats!`);

// Play with a cat
const result = await client.playWithCat(123, 'honeysuckle_dance', 15);
console.log('Play session completed:', result);
```

## 📚 API Reference

### Authentication

```typescript
// Initialize with API key
const client = new PurrrLoveClient({
  apiKey: 'your_api_key'
});

// Or authenticate later
client.authenticate('your_api_key');
```

### Cat Management

```typescript
// Get all cats
const cats = await client.getCats(50, 0);

// Get specific cat
const cat = await client.getCat(123);

// Create new cat
const newCat = await client.createCat({
  name: 'Whiskers',
  species: 'cat',
  personalityType: 'playful',
  breed: 'Persian'
});

// Update cat
const updatedCat = await client.updateCat(123, { name: 'Mr. Whiskers' });

// Delete cat
await client.deleteCat(123);
```

### Cat Activities

```typescript
// Play with cat
const result = await client.playWithCat(
  123,
  'honeysuckle_dance',
  15
);

// Feed cat
const result = await client.feedCat(
  123,
  'premium_cat_food',
  1.5
);

// Groom cat
const result = await client.groomCat(
  123,
  'brushing'
);
```

## 🐾 Lost Pet Finder System

The Lost Pet Finder system helps reunite lost pets with their owners through advanced search, community reporting, and Facebook integration.

### Report a Lost Pet

```typescript
const lostPetData = {
  name: 'Whiskers',
  breed: 'Persian',
  color: 'White',
  lastSeenLocation: 'Central Park, New York',
  lastSeenDate: '2024-12-01',
  description: 'Friendly white Persian cat with blue eyes',
  photos: ['https://example.com/whiskers1.jpg'],
  facebookShareEnabled: true
};

const result = await client.reportLostPet(lostPetData);
const reportId = result.report_id;
```

### Search for Lost Pets

```typescript
const searchCriteria = {
  breed: 'Persian',
  color: 'White',
  radiusKm: 10,
  latitude: 40.7829,
  longitude: -73.9654
};

const results = await client.searchLostPets(searchCriteria);
console.log(`Found ${results.total_count || 0} lost pets`);
```

### Report a Sighting

```typescript
const sightingData = {
  lostPetReportId: 123,
  location: 'Brooklyn Bridge Park',
  sightingDate: '2024-12-02',
  description: 'Saw a white Persian cat that matches the description',
  confidenceLevel: 'high' as const
};

const result = await client.reportPetSighting(sightingData);
```

### Mark Pet as Found

```typescript
const foundData = {
  foundLocation: 'Home',
  foundDetails: 'Pet returned home safely'
};

const result = await client.markPetFound(123, foundData);
```

### Get Statistics

```typescript
const stats = await client.getLostPetStatistics();
console.log(`Total reports: ${stats.overall?.total_reports || 0}`);
```

## ⛓️ Blockchain & NFT Management

Manage cat NFTs across multiple blockchain networks including Ethereum, Polygon, BSC, and Solana.

### Mint an NFT

```typescript
const nftMetadata = {
  rarity: 'epic',
  trait: 'mystic',
  generation: 1
};

const result = await client.mintCatNFT(
  456,
  'ethereum',
  nftMetadata
);

const nftId = result.nft_id;
```

### Transfer NFT Ownership

```typescript
const result = await client.transferNFT(
  789,
  999,
  'ethereum'
);

const transactionHash = result.transaction_hash;
```

### Verify NFT Ownership

```typescript
const result = await client.verifyNFTOwnership(789);
const ownerId = result.owner_id;
```

### Get NFT Collection

```typescript
// Get all NFTs
const collection = await client.getNFTCollection();

// Get NFTs from specific network
const ethereumNfts = await client.getNFTCollection('ethereum');
```

### Get Blockchain Statistics

```typescript
const stats = await client.getBlockchainStatistics();
console.log(`Total NFTs: ${stats.total_nfts || 0}`);
```

## 🧠 Machine Learning Personality Prediction

AI-powered cat personality analysis using behavioral data, genetic markers, and environmental factors.

### Predict Cat Personality

```typescript
const prediction = await client.predictCatPersonality(123, true);

const personalityType = prediction.personality_type;
const confidenceScore = prediction.confidence_score;
```

### Get Personality Insights

```typescript
const insights = await client.getPersonalityInsights(123);
const insightsList = insights.insights || [];
```

### Record Behavior Observation

```typescript
const behaviorData = {
  type: 'play',
  intensity: 8,
  duration: 300,
  context: 'indoor, sunny day, other cats present'
};

const result = await client.recordBehaviorObservation(123, behaviorData);
```

### Update Genetic Data

```typescript
const geneticData = {
  heritageScore: 85,
  coatPattern: 'tabby',
  markers: 'hunting_instinct:0.8, social_tendency:0.7'
};

const result = await client.updateGeneticData(123, geneticData);
```

### Get ML Training Status

```typescript
const trainingStatus = await client.getMLTrainingStatus();
const status = trainingStatus.status;
const accuracy = trainingStatus.accuracy;
```

## 🌐 Metaverse & VR Worlds

Create and explore virtual cat worlds with VR interactions and social experiences.

### Create a Metaverse World

```typescript
const worldData = {
  name: 'Cat Paradise',
  type: 'cat_park',
  maxPlayers: 100,
  accessLevel: 'public'
};

const result = await client.createMetaverseWorld(worldData);
const worldId = result.world_id;
```

### Join a World

```typescript
const result = await client.joinMetaverseWorld(
  456,
  123  // Optional cat ID
);

const sessionId = result.session_id;
```

### Leave a World

```typescript
const result = await client.leaveMetaverseWorld(456);
```

### List Available Worlds

```typescript
// Get all worlds
const worlds = await client.listMetaverseWorlds();

// Get worlds with filters
const filteredWorlds = await client.listMetaverseWorlds({
  type: 'cat_park',
  accessLevel: 'public'
});
```

### Perform VR Interaction

```typescript
const interactionData = {
  type: 'petting',
  targetData: { cat_id: 789 }
};

const result = await client.performVRInteraction(456, interactionData);
```

### Get Metaverse Statistics

```typescript
const stats = await client.getMetaverseStatistics();
console.log(`Total worlds: ${stats.total_worlds || 0}`);
```

## 🔗 Webhook System

Set up real-time notifications for platform events and integrate with external systems.

### Create a Webhook

```typescript
const webhookData = {
  url: 'https://myapp.com/webhook',
  events: ['cat.created', 'nft.minted', 'lost_pet.found'],
  secret: 'webhook_secret_123',
  headers: { Authorization: 'Bearer my_token' }
};

const result = await client.createWebhook(webhookData);
const webhookId = result.webhook_id;
```

### List Webhooks

```typescript
const webhooks = await client.listWebhooks();
const activeWebhooks = webhooks.webhooks || [];
```

### Update Webhook

```typescript
const updates = {
  events: ['cat.created', 'nft.minted'],
  secret: 'new_secret_456'
};

const result = await client.updateWebhook(123, updates);
```

### Delete Webhook

```typescript
await client.deleteWebhook(123);
```

### Test Webhook

```typescript
const result = await client.testWebhook(123);
const status = result.status;
```

### Get Webhook Logs

```typescript
const logs = await client.getWebhookLogs(123, 100);
const deliveryLogs = logs.logs || [];
```

## 📊 Analytics & Health

Monitor platform performance and get comprehensive analytics data.

### Get Analytics Data

```typescript
// Get overview analytics
const overview = await client.getAnalyticsData('overview');

// Get user behavior analytics
const userBehavior = await client.getAnalyticsData('user_behavior', {
  timeframe: '7d'
});

// Get cat interaction analytics
const catInteractions = await client.getAnalyticsData('cat_interactions', {
  timeframe: '30d'
});
```

### Health Check

```typescript
const health = await client.healthCheck();
const status = health.status;
```

### Get API Info

```typescript
const apiInfo = await client.getApiInfo();
const version = apiInfo.version;
```

### Get Rate Limit Info

```typescript
const rateLimit = await client.getRateLimitInfo();
const remaining = rateLimit.remaining;
const resetTime = rateLimit.resetTime;
```

## 🧪 Examples

See the `examples/` directory for comprehensive examples:

- `advanced-features-examples.ts` - Complete examples for all advanced features
- `basic-usage.ts` - Basic SDK usage examples
- `webhook-integration.ts` - Webhook setup and testing examples

Run examples:

```bash
# Install ts-node for running TypeScript examples
npm install -g ts-node

# Run examples
npx ts-node examples/advanced-features-examples.ts

# Make sure to set your API key first
export PURRR_LOVE_API_KEY="your_api_key_here"
```

## 🔧 Configuration

### Environment Variables

```bash
export PURRR_LOVE_API_KEY="your_api_key_here"
export PURRR_LOVE_BASE_URL="https://api.purrr.love"
```

### Client Configuration

```typescript
const client = new PurrrLoveClient({
  baseUrl: 'https://api.purrr.love',  // Default
  apiKey: 'your_api_key_here',
  timeout: 30000,                     // 30 seconds
  maxRetries: 3,                      // Retry failed requests
  retryDelay: 1000                    // 1 second between retries
});
```

## 🚨 Error Handling

The SDK provides comprehensive error handling with specific exception types:

```typescript
import {
  PurrrLoveError,
  AuthenticationError,
  RateLimitError,
  ValidationError,
  NotFoundError
} from '@purrr-love/sdk';

try {
  const result = await client.getCat(999);
} catch (error) {
  if (error instanceof AuthenticationError) {
    console.log('Invalid API key');
  } else if (error instanceof NotFoundError) {
    console.log('Cat not found');
  } else if (error instanceof RateLimitError) {
    console.log(`Rate limit exceeded: ${error.message}`);
  } else if (error instanceof PurrrLoveError) {
    console.log(`API error: ${error.message}`);
  }
}
```

## 📈 Rate Limiting

The SDK automatically handles rate limiting and provides information about current limits:

```typescript
// Get current rate limit status
const rateLimit = await client.getRateLimitInfo();
console.log(`Requests remaining: ${rateLimit.remaining}`);
console.log(`Reset time: ${rateLimit.resetTime}`);
```

## 🔒 Security

- All API requests use HTTPS
- API keys are securely transmitted in headers
- Input validation and sanitization
- Secure error handling (no sensitive data in error messages)

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
git clone https://github.com/purrr-love/nodejs-sdk.git
cd nodejs-sdk
npm install
npm run build
```

### Running Tests

```bash
npm test
npm run test:coverage
```

### Building

```bash
npm run build
npm run build:watch
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: [https://docs.purrr.love](https://docs.purrr.love)
- **Issues**: [GitHub Issues](https://github.com/purrr-love/nodejs-sdk/issues)
- **Discord**: [Purrr.love Community](https://discord.gg/purrr-love)
- **Email**: [dev@purrr.love](mailto:dev@purrr.love)

## 🔄 Changelog

### Version 2.0.0
- ✨ Added Lost Pet Finder System
- ✨ Added Blockchain & NFT Management
- ✨ Added Machine Learning Personality Prediction
- ✨ Added Metaverse & VR Worlds
- ✨ Added Webhook System
- ✨ Added Analytics Dashboard integration
- ✨ Enhanced error handling and rate limiting
- ✨ Comprehensive examples and documentation

### Version 1.0.0
- 🎉 Initial release
- ✨ Basic cat management features
- ✨ Core API functionality
- ✨ TypeScript support

---

*Built with ❤️ by the Purrr.love team*

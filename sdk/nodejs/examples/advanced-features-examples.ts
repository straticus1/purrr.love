#!/usr/bin/env ts-node
/**
 * 🐱 Purrr.love Node.js SDK - Advanced Features Examples
 * Comprehensive examples demonstrating all the new advanced features
 */

import { PurrrLoveClient } from '../src';

async function main() {
  // Initialize the client
  // Replace with your actual API key and base URL
  const client = new PurrrLoveClient({
    baseUrl: 'https://api.purrr.love',
    apiKey: 'your_api_key_here',
    timeout: 30000,
    maxRetries: 3
  });

  console.log('🐱 Purrr.love Advanced Features Examples');
  console.log('='.repeat(50));

  try {
    // Example 1: Lost Pet Finder System
    console.log('\n1. 🐾 Lost Pet Finder System');
    console.log('-'.repeat(30));

    // Report a lost pet
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

    try {
      const result = await client.reportLostPet(lostPetData);
      console.log(`✅ Lost pet report created: ${result.report_id}`);
      const reportId = result.report_id;
    } catch (e) {
      console.log(`❌ Failed to create lost pet report: ${e}`);
    }

    // Search for lost pets
    const searchCriteria = {
      breed: 'Persian',
      color: 'White',
      radiusKm: 10,
      latitude: 40.7829,
      longitude: -73.9654
    };

    try {
      const searchResults = await client.searchLostPets(searchCriteria);
      console.log(`🔍 Found ${searchResults.total_count || 0} lost pets`);
    } catch (e) {
      console.log(`❌ Search failed: ${e}`);
    }

    // Report a sighting
    const sightingData = {
      lostPetReportId: 123,
      location: 'Brooklyn Bridge Park',
      sightingDate: '2024-12-02',
      description: 'Saw a white Persian cat that matches the description',
      confidenceLevel: 'high' as const
    };

    try {
      const result = await client.reportPetSighting(sightingData);
      console.log(`✅ Sighting reported: ${result.sighting_id}`);
    } catch (e) {
      console.log(`❌ Failed to report sighting: ${e}`);
    }

    // Get lost pet statistics
    try {
      const stats = await client.getLostPetStatistics();
      console.log(`📊 Total reports: ${stats.overall?.total_reports || 0}`);
    } catch (e) {
      console.log(`❌ Failed to get statistics: ${e}`);
    }

    // Example 2: Blockchain & NFT Management
    console.log('\n2. ⛓️ Blockchain & NFT Management');
    console.log('-'.repeat(30));

    // Mint an NFT for a cat
    const nftMetadata = {
      rarity: 'epic',
      trait: 'mystic',
      generation: 1
    };

    try {
      const result = await client.mintCatNFT(456, 'ethereum', nftMetadata);
      console.log(`✅ NFT minted: ${result.nft_id}`);
      const nftId = result.nft_id;
    } catch (e) {
      console.log(`❌ Failed to mint NFT: ${e}`);
    }

    // Transfer NFT ownership
    try {
      const result = await client.transferNFT(789, 999, 'ethereum');
      console.log(`✅ NFT transferred: ${result.transaction_hash}`);
    } catch (e) {
      console.log(`❌ Failed to transfer NFT: ${e}`);
    }

    // Verify NFT ownership
    try {
      const result = await client.verifyNFTOwnership(789);
      console.log(`✅ NFT ownership verified: ${result.owner_id}`);
    } catch (e) {
      console.log(`❌ Failed to verify ownership: ${e}`);
    }

    // Get NFT collection
    try {
      const collection = await client.getNFTCollection('ethereum');
      console.log(`✅ Collection retrieved: ${collection.nfts?.length || 0} NFTs`);
    } catch (e) {
      console.log(`❌ Failed to get collection: ${e}`);
    }

    // Get blockchain statistics
    try {
      const stats = await client.getBlockchainStatistics();
      console.log(`📊 Blockchain stats: ${stats.total_nfts || 0} total NFTs`);
    } catch (e) {
      console.log(`❌ Failed to get blockchain stats: ${e}`);
    }

    // Example 3: Machine Learning Personality Prediction
    console.log('\n3. 🧠 Machine Learning Personality Prediction');
    console.log('-'.repeat(30));

    // Predict cat personality
    try {
      const prediction = await client.predictCatPersonality(123, true);
      console.log(`✅ Personality predicted: ${prediction.personality_type}`);
      console.log(`   Confidence: ${(prediction.confidence_score || 0).toFixed(2)}%`);
    } catch (e) {
      console.log(`❌ Failed to predict personality: ${e}`);
    }

    // Get personality insights
    try {
      const insights = await client.getPersonalityInsights(123);
      console.log(`✅ Insights retrieved: ${insights.insights?.length || 0} insights`);
    } catch (e) {
      console.log(`❌ Failed to get insights: ${e}`);
    }

    // Record behavior observation
    const behaviorData = {
      type: 'play',
      intensity: 8,
      duration: 300,
      context: 'indoor, sunny day, other cats present'
    };

    try {
      const result = await client.recordBehaviorObservation(123, behaviorData);
      console.log(`✅ Behavior recorded: ${result.observation_id}`);
    } catch (e) {
      console.log(`❌ Failed to record behavior: ${e}`);
    }

    // Update genetic data
    const geneticData = {
      heritageScore: 85,
      coatPattern: 'tabby',
      markers: 'hunting_instinct:0.8, social_tendency:0.7'
    };

    try {
      const result = await client.updateGeneticData(123, geneticData);
      console.log(`✅ Genetic data updated: ${result.status}`);
    } catch (e) {
      console.log(`❌ Failed to update genetic data: ${e}`);
    }

    // Get ML training status
    try {
      const trainingStatus = await client.getMLTrainingStatus();
      console.log(`✅ Training status: ${trainingStatus.status}`);
      console.log(`   Model accuracy: ${(trainingStatus.accuracy || 0).toFixed(2)}%`);
    } catch (e) {
      console.log(`❌ Failed to get training status: ${e}`);
    }

    // Example 4: Metaverse & VR Worlds
    console.log('\n4. 🌐 Metaverse & VR Worlds');
    console.log('-'.repeat(30));

    // Create a metaverse world
    const worldData = {
      name: 'Cat Paradise',
      type: 'cat_park',
      maxPlayers: 100,
      accessLevel: 'public'
    };

    try {
      const result = await client.createMetaverseWorld(worldData);
      console.log(`✅ World created: ${result.world_id}`);
      const worldId = result.world_id;
    } catch (e) {
      console.log(`❌ Failed to create world: ${e}`);
    }

    // Join the world
    try {
      const result = await client.joinMetaverseWorld(456, 123);
      console.log(`✅ Joined world: ${result.session_id}`);
    } catch (e) {
      console.log(`❌ Failed to join world: ${e}`);
    }

    // List available worlds
    try {
      const worlds = await client.listMetaverseWorlds();
      console.log(`✅ Worlds listed: ${worlds.worlds?.length || 0} available`);
    } catch (e) {
      console.log(`❌ Failed to list worlds: ${e}`);
    }

    // Perform VR interaction
    const interactionData = {
      type: 'petting',
      targetData: { cat_id: 789 }
    };

    try {
      const result = await client.performVRInteraction(456, interactionData);
      console.log(`✅ VR interaction performed: ${result.interaction_id}`);
    } catch (e) {
      console.log(`❌ Failed to perform VR interaction: ${e}`);
    }

    // Get metaverse statistics
    try {
      const stats = await client.getMetaverseStatistics();
      console.log(`📊 Metaverse stats: ${stats.total_worlds || 0} worlds`);
    } catch (e) {
      console.log(`❌ Failed to get metaverse stats: ${e}`);
    }

    // Example 5: Webhook System
    console.log('\n5. 🔗 Webhook System');
    console.log('-'.repeat(30));

    // Create a webhook
    const webhookData = {
      url: 'https://myapp.com/webhook',
      events: ['cat.created', 'nft.minted', 'lost_pet.found'],
      secret: 'webhook_secret_123',
      headers: { Authorization: 'Bearer my_token' }
    };

    try {
      const result = await client.createWebhook(webhookData);
      console.log(`✅ Webhook created: ${result.webhook_id}`);
      const webhookId = result.webhook_id;
    } catch (e) {
      console.log(`❌ Failed to create webhook: ${e}`);
    }

    // List webhooks
    try {
      const webhooks = await client.listWebhooks();
      console.log(`✅ Webhooks listed: ${webhooks.webhooks?.length || 0} active`);
    } catch (e) {
      console.log(`❌ Failed to list webhooks: ${e}`);
    }

    // Test webhook
    try {
      const result = await client.testWebhook(123);
      console.log(`✅ Webhook tested: ${result.status}`);
    } catch (e) {
      console.log(`❌ Failed to test webhook: ${e}`);
    }

    // Get webhook logs
    try {
      const logs = await client.getWebhookLogs(123, 50);
      console.log(`✅ Logs retrieved: ${logs.logs?.length || 0} delivery logs`);
    } catch (e) {
      console.log(`❌ Failed to get webhook logs: ${e}`);
    }

    // Example 6: Analytics & Health
    console.log('\n6. 📊 Analytics & Health');
    console.log('-'.repeat(30));

    // Get analytics data
    try {
      const analytics = await client.getAnalyticsData('user_behavior', { timeframe: '7d' });
      console.log(`✅ Analytics retrieved: ${analytics.total_users || 0} users`);
    } catch (e) {
      console.log(`❌ Failed to get analytics: ${e}`);
    }

    // Health check
    try {
      const health = await client.healthCheck();
      console.log(`✅ Health check: ${health.status}`);
    } catch (e) {
      console.log(`❌ Health check failed: ${e}`);
    }

    // Get API info
    try {
      const apiInfo = await client.getApiInfo();
      console.log(`✅ API info: ${apiInfo.version}`);
    } catch (e) {
      console.log(`❌ Failed to get API info: ${e}`);
    }

    // Get rate limit info
    try {
      const rateLimit = await client.getRateLimitInfo();
      console.log(`✅ Rate limit: ${rateLimit.remaining} requests remaining`);
    } catch (e) {
      console.log(`❌ Failed to get rate limit info: ${e}`);
    }

    console.log('\n' + '='.repeat(50));
    console.log('🎉 All advanced features examples completed!');
    console.log('💡 Remember to replace \'your_api_key_here\' with your actual API key');
    console.log('🔗 Check the documentation for more details: https://docs.purrr.love');

  } catch (error) {
    console.error('❌ An error occurred:', error);
  } finally {
    // Clean up
    client.close();
  }
}

// Run the examples
if (require.main === module) {
  main().catch(console.error);
}

export { main };

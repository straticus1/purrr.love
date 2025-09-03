#!/usr/bin/env python3
"""
🐱 Purrr.love Python SDK - Advanced Features Examples
Comprehensive examples demonstrating all the new advanced features
"""

import os
import sys
from datetime import datetime, date
from typing import Dict, Any

# Add the parent directory to the path to import the SDK
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from purrr_love import PurrrLoveClient

def main():
    """Main function demonstrating all advanced features"""
    
    # Initialize the client
    # Replace with your actual API key and base URL
    client = PurrrLoveClient(
        base_url="https://api.purrr.love",
        api_key="your_api_key_here"
    )
    
    print("🐱 Purrr.love Advanced Features Examples")
    print("=" * 50)
    
    # Example 1: Lost Pet Finder System
    print("\n1. 🐾 Lost Pet Finder System")
    print("-" * 30)
    
    # Report a lost pet
    lost_pet_data = {
        "name": "Whiskers",
        "breed": "Persian",
        "color": "White",
        "last_seen_location": "Central Park, New York",
        "last_seen_date": "2024-12-01",
        "description": "Friendly white Persian cat with blue eyes",
        "photos": ["https://example.com/whiskers1.jpg"],
        "facebook_share_enabled": True
    }
    
    try:
        result = client.report_lost_pet(lost_pet_data)
        print(f"✅ Lost pet report created: {result.get('report_id')}")
        report_id = result.get('report_id')
    except Exception as e:
        print(f"❌ Failed to create lost pet report: {e}")
        report_id = 123  # Use dummy ID for examples
    
    # Search for lost pets
    search_criteria = {
        "breed": "Persian",
        "color": "White",
        "radius_km": 10,
        "latitude": 40.7829,
        "longitude": -73.9654
    }
    
    try:
        search_results = client.search_lost_pets(search_criteria)
        print(f"🔍 Found {search_results.get('total_count', 0)} lost pets")
    except Exception as e:
        print(f"❌ Search failed: {e}")
    
    # Report a sighting
    sighting_data = {
        "lost_pet_report_id": report_id,
        "location": "Brooklyn Bridge Park",
        "sighting_date": "2024-12-02",
        "description": "Saw a white Persian cat that matches the description",
        "confidence_level": "high"
    }
    
    try:
        result = client.report_pet_sighting(sighting_data)
        print(f"✅ Sighting reported: {result.get('sighting_id')}")
    except Exception as e:
        print(f"❌ Failed to report sighting: {e}")
    
    # Get lost pet statistics
    try:
        stats = client.get_lost_pet_statistics()
        print(f"📊 Total reports: {stats.get('overall', {}).get('total_reports', 0)}")
    except Exception as e:
        print(f"❌ Failed to get statistics: {e}")
    
    # Example 2: Blockchain & NFT Management
    print("\n2. ⛓️ Blockchain & NFT Management")
    print("-" * 30)
    
    # Mint an NFT for a cat
    nft_metadata = {
        "rarity": "epic",
        "trait": "mystic",
        "generation": 1
    }
    
    try:
        result = client.mint_cat_nft(
            cat_id=456,
            network="ethereum",
            metadata=nft_metadata
        )
        print(f"✅ NFT minted: {result.get('nft_id')}")
        nft_id = result.get('nft_id')
    except Exception as e:
        print(f"❌ Failed to mint NFT: {e}")
        nft_id = 789  # Use dummy ID for examples
    
    # Transfer NFT ownership
    try:
        result = client.transfer_nft(
            nft_id=nft_id,
            to_user_id=999,
            network="ethereum"
        )
        print(f"✅ NFT transferred: {result.get('transaction_hash')}")
    except Exception as e:
        print(f"❌ Failed to transfer NFT: {e}")
    
    # Verify NFT ownership
    try:
        result = client.verify_nft_ownership(nft_id)
        print(f"✅ NFT ownership verified: {result.get('owner_id')}")
    except Exception as e:
        print(f"❌ Failed to verify ownership: {e}")
    
    # Get NFT collection
    try:
        collection = client.get_nft_collection(network="ethereum")
        print(f"✅ Collection retrieved: {len(collection.get('nfts', []))} NFTs")
    except Exception as e:
        print(f"❌ Failed to get collection: {e}")
    
    # Get blockchain statistics
    try:
        stats = client.get_blockchain_statistics()
        print(f"📊 Blockchain stats: {stats.get('total_nfts', 0)} total NFTs")
    except Exception as e:
        print(f"❌ Failed to get blockchain stats: {e}")
    
    # Example 3: Machine Learning Personality Prediction
    print("\n3. 🧠 Machine Learning Personality Prediction")
    print("-" * 30)
    
    # Predict cat personality
    try:
        prediction = client.predict_cat_personality(
            cat_id=123,
            include_confidence=True
        )
        print(f"✅ Personality predicted: {prediction.get('personality_type')}")
        print(f"   Confidence: {prediction.get('confidence_score', 0):.2f}%")
    except Exception as e:
        print(f"❌ Failed to predict personality: {e}")
    
    # Get personality insights
    try:
        insights = client.get_personality_insights(cat_id=123)
        print(f"✅ Insights retrieved: {len(insights.get('insights', []))} insights")
    except Exception as e:
        print(f"❌ Failed to get insights: {e}")
    
    # Record behavior observation
    behavior_data = {
        "type": "play",
        "intensity": 8,
        "duration": 300,
        "context": "indoor, sunny day, other cats present"
    }
    
    try:
        result = client.record_behavior_observation(123, behavior_data)
        print(f"✅ Behavior recorded: {result.get('observation_id')}")
    except Exception as e:
        print(f"❌ Failed to record behavior: {e}")
    
    # Update genetic data
    genetic_data = {
        "heritage_score": 85,
        "coat_pattern": "tabby",
        "markers": "hunting_instinct:0.8, social_tendency:0.7"
    }
    
    try:
        result = client.update_genetic_data(123, genetic_data)
        print(f"✅ Genetic data updated: {result.get('status')}")
    except Exception as e:
        print(f"❌ Failed to update genetic data: {e}")
    
    # Get ML training status
    try:
        training_status = client.get_ml_training_status()
        print(f"✅ Training status: {training_status.get('status')}")
        print(f"   Model accuracy: {training_status.get('accuracy', 0):.2f}%")
    except Exception as e:
        print(f"❌ Failed to get training status: {e}")
    
    # Example 4: Metaverse & VR Worlds
    print("\n4. 🌐 Metaverse & VR Worlds")
    print("-" * 30)
    
    # Create a metaverse world
    world_data = {
        "name": "Cat Paradise",
        "type": "cat_park",
        "max_players": 100,
        "access_level": "public"
    }
    
    try:
        result = client.create_metaverse_world(world_data)
        print(f"✅ World created: {result.get('world_id')}")
        world_id = result.get('world_id')
    except Exception as e:
        print(f"❌ Failed to create world: {e}")
        world_id = 456  # Use dummy ID for examples
    
    # Join the world
    try:
        result = client.join_metaverse_world(world_id, cat_id=123)
        print(f"✅ Joined world: {result.get('session_id')}")
    except Exception as e:
        print(f"❌ Failed to join world: {e}")
    
    # List available worlds
    try:
        worlds = client.list_metaverse_worlds()
        print(f"✅ Worlds listed: {len(worlds.get('worlds', []))} available")
    except Exception as e:
        print(f"❌ Failed to list worlds: {e}")
    
    # Perform VR interaction
    interaction_data = {
        "type": "petting",
        "target_data": {"cat_id": 789}
    }
    
    try:
        result = client.perform_vr_interaction(world_id, interaction_data)
        print(f"✅ VR interaction performed: {result.get('interaction_id')}")
    except Exception as e:
        print(f"❌ Failed to perform VR interaction: {e}")
    
    # Get metaverse statistics
    try:
        stats = client.get_metaverse_statistics()
        print(f"📊 Metaverse stats: {stats.get('total_worlds', 0)} worlds")
    except Exception as e:
        print(f"❌ Failed to get metaverse stats: {e}")
    
    # Example 5: Webhook System
    print("\n5. 🔗 Webhook System")
    print("-" * 30)
    
    # Create a webhook
    webhook_data = {
        "url": "https://myapp.com/webhook",
        "events": ["cat.created", "nft.minted", "lost_pet.found"],
        "secret": "webhook_secret_123",
        "headers": {"Authorization": "Bearer my_token"}
    }
    
    try:
        result = client.create_webhook(webhook_data)
        print(f"✅ Webhook created: {result.get('webhook_id')}")
        webhook_id = result.get('webhook_id')
    except Exception as e:
        print(f"❌ Failed to create webhook: {e}")
        webhook_id = 123  # Use dummy ID for examples
    
    # List webhooks
    try:
        webhooks = client.list_webhooks()
        print(f"✅ Webhooks listed: {len(webhooks.get('webhooks', []))} active")
    except Exception as e:
        print(f"❌ Failed to list webhooks: {e}")
    
    # Test webhook
    try:
        result = client.test_webhook(webhook_id)
        print(f"✅ Webhook tested: {result.get('status')}")
    except Exception as e:
        print(f"❌ Failed to test webhook: {e}")
    
    # Get webhook logs
    try:
        logs = client.get_webhook_logs(webhook_id, limit=50)
        print(f"✅ Logs retrieved: {len(logs.get('logs', []))} delivery logs")
    except Exception as e:
        print(f"❌ Failed to get webhook logs: {e}")
    
    # Example 6: Analytics & Health
    print("\n6. 📊 Analytics & Health")
    print("-" * 30)
    
    # Get analytics data
    try:
        analytics = client.get_analytics_data(
            analytics_type="user_behavior",
            filters={"timeframe": "7d"}
        )
        print(f"✅ Analytics retrieved: {analytics.get('total_users', 0)} users")
    except Exception as e:
        print(f"❌ Failed to get analytics: {e}")
    
    # Health check
    try:
        health = client.health_check()
        print(f"✅ Health check: {health.get('status')}")
    except Exception as e:
        print(f"❌ Health check failed: {e}")
    
    # Get API info
    try:
        api_info = client.get_api_info()
        print(f"✅ API info: {api_info.get('version')}")
    except Exception as e:
        print(f"❌ Failed to get API info: {e}")
    
    # Get rate limit info
    try:
        rate_limit = client.get_rate_limit_info()
        print(f"✅ Rate limit: {rate_limit.get('remaining')} requests remaining")
    except Exception as e:
        print(f"❌ Failed to get rate limit info: {e}")
    
    print("\n" + "=" * 50)
    print("🎉 All advanced features examples completed!")
    print("💡 Remember to replace 'your_api_key_here' with your actual API key")
    print("🔗 Check the documentation for more details: https://docs.purrr.love")

if __name__ == "__main__":
    main()

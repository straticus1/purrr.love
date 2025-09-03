# 🐱 Purrr.love Python SDK

Official Python client library for the Purrr.love cat gaming platform. This SDK provides easy access to all platform features including cat management, lost pet finder, blockchain/NFT integration, machine learning personality prediction, metaverse/VR worlds, and webhook systems.

## 🚀 Features

### Core Features
- **Cat Management** - Create, read, update, and delete cats
- **Cat Activities** - Play, feed, and groom your cats
- **User Management** - Manage user accounts and API keys

### Advanced Features
- **🐾 Lost Pet Finder System** - Report lost pets, search, report sightings, and Facebook integration
- **⛓️ Blockchain & NFT Management** - Mint, transfer, and verify cat NFTs across multiple networks
- **🧠 Machine Learning Personality Prediction** - AI-powered cat personality analysis and behavior insights
- **🌐 Metaverse & VR Worlds** - Create and join virtual cat worlds with VR interactions
- **🔗 Webhook System** - Real-time notifications and integrations
- **📊 Analytics Dashboard** - Comprehensive platform analytics and insights

## 📦 Installation

```bash
pip install purrr-love-sdk
```

Or install from source:

```bash
git clone https://github.com/purrr-love/python-sdk.git
cd python-sdk
pip install -e .
```

## 🔑 Quick Start

```python
from purrr_love import PurrrLoveClient

# Initialize the client
client = PurrrLoveClient(
    base_url="https://api.purrr.love",
    api_key="your_api_key_here"
)

# Get your cats
cats = client.get_cats()
print(f"You have {len(cats)} cats!")

# Play with a cat
result = client.play_with_cat(cat_id=123, game_type="honeysuckle_dance", duration=15)
print(f"Play session completed: {result}")
```

## 📚 API Reference

### Authentication

```python
# Initialize with API key
client = PurrrLoveClient(api_key="your_api_key")

# Or authenticate later
client.authenticate("your_api_key")
```

### Cat Management

```python
# Get all cats
cats = client.get_cats(limit=50, offset=0)

# Get specific cat
cat = client.get_cat(cat_id=123)

# Create new cat
new_cat = client.create_cat(
    name="Whiskers",
    species="cat",
    personality_type="playful",
    breed="Persian"
)

# Update cat
updated_cat = client.update_cat(cat_id=123, name="Mr. Whiskers")

# Delete cat
client.delete_cat(cat_id=123)
```

### Cat Activities

```python
# Play with cat
result = client.play_with_cat(
    cat_id=123,
    game_type="honeysuckle_dance",
    duration=15
)

# Feed cat
result = client.feed_cat(
    cat_id=123,
    food_type="premium_cat_food",
    amount=1.5
)

# Groom cat
result = client.groom_cat(
    cat_id=123,
    grooming_type="brushing"
)
```

## 🐾 Lost Pet Finder System

The Lost Pet Finder system helps reunite lost pets with their owners through advanced search, community reporting, and Facebook integration.

### Report a Lost Pet

```python
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

result = client.report_lost_pet(lost_pet_data)
report_id = result.get('report_id')
```

### Search for Lost Pets

```python
search_criteria = {
    "breed": "Persian",
    "color": "White",
    "radius_km": 10,
    "latitude": 40.7829,
    "longitude": -73.9654
}

results = client.search_lost_pets(search_criteria)
print(f"Found {results.get('total_count', 0)} lost pets")
```

### Report a Sighting

```python
sighting_data = {
    "lost_pet_report_id": 123,
    "location": "Brooklyn Bridge Park",
    "sighting_date": "2024-12-02",
    "description": "Saw a white Persian cat that matches the description",
    "confidence_level": "high"
}

result = client.report_pet_sighting(sighting_data)
```

### Mark Pet as Found

```python
found_data = {
    "found_location": "Home",
    "found_details": "Pet returned home safely"
}

result = client.mark_pet_found(report_id=123, found_data=found_data)
```

### Get Statistics

```python
stats = client.get_lost_pet_statistics()
print(f"Total reports: {stats.get('overall', {}).get('total_reports', 0)}")
```

## ⛓️ Blockchain & NFT Management

Manage cat NFTs across multiple blockchain networks including Ethereum, Polygon, BSC, and Solana.

### Mint an NFT

```python
nft_metadata = {
    "rarity": "epic",
    "trait": "mystic",
    "generation": 1
}

result = client.mint_cat_nft(
    cat_id=456,
    network="ethereum",
    metadata=nft_metadata
)

nft_id = result.get('nft_id')
```

### Transfer NFT Ownership

```python
result = client.transfer_nft(
    nft_id=789,
    to_user_id=999,
    network="ethereum"
)

transaction_hash = result.get('transaction_hash')
```

### Verify NFT Ownership

```python
result = client.verify_nft_ownership(nft_id=789)
owner_id = result.get('owner_id')
```

### Get NFT Collection

```python
# Get all NFTs
collection = client.get_nft_collection()

# Get NFTs from specific network
ethereum_nfts = client.get_nft_collection(network="ethereum")
```

### Get Blockchain Statistics

```python
stats = client.get_blockchain_statistics()
print(f"Total NFTs: {stats.get('total_nfts', 0)}")
```

## 🧠 Machine Learning Personality Prediction

AI-powered cat personality analysis using behavioral data, genetic markers, and environmental factors.

### Predict Cat Personality

```python
prediction = client.predict_cat_personality(
    cat_id=123,
    include_confidence=True
)

personality_type = prediction.get('personality_type')
confidence_score = prediction.get('confidence_score')
```

### Get Personality Insights

```python
insights = client.get_personality_insights(cat_id=123)
insights_list = insights.get('insights', [])
```

### Record Behavior Observation

```python
behavior_data = {
    "type": "play",
    "intensity": 8,
    "duration": 300,
    "context": "indoor, sunny day, other cats present"
}

result = client.record_behavior_observation(123, behavior_data)
```

### Update Genetic Data

```python
genetic_data = {
    "heritage_score": 85,
    "coat_pattern": "tabby",
    "markers": "hunting_instinct:0.8, social_tendency:0.7"
}

result = client.update_genetic_data(123, genetic_data)
```

### Get ML Training Status

```python
training_status = client.get_ml_training_status()
status = training_status.get('status')
accuracy = training_status.get('accuracy')
```

## 🌐 Metaverse & VR Worlds

Create and explore virtual cat worlds with VR interactions and social experiences.

### Create a Metaverse World

```python
world_data = {
    "name": "Cat Paradise",
    "type": "cat_park",
    "max_players": 100,
    "access_level": "public"
}

result = client.create_metaverse_world(world_data)
world_id = result.get('world_id')
```

### Join a World

```python
result = client.join_metaverse_world(
    world_id=456,
    cat_id=123  # Optional
)

session_id = result.get('session_id')
```

### Leave a World

```python
result = client.leave_metaverse_world(world_id=456)
```

### List Available Worlds

```python
# Get all worlds
worlds = client.list_metaverse_worlds()

# Get worlds with filters
filtered_worlds = client.list_metaverse_worlds({
    "type": "cat_park",
    "access_level": "public"
})
```

### Perform VR Interaction

```python
interaction_data = {
    "type": "petting",
    "target_data": {"cat_id": 789}
}

result = client.perform_vr_interaction(456, interaction_data)
```

### Get Metaverse Statistics

```python
stats = client.get_metaverse_statistics()
print(f"Total worlds: {stats.get('total_worlds', 0)}")
```

## 🔗 Webhook System

Set up real-time notifications for platform events and integrate with external systems.

### Create a Webhook

```python
webhook_data = {
    "url": "https://myapp.com/webhook",
    "events": ["cat.created", "nft.minted", "lost_pet.found"],
    "secret": "webhook_secret_123",
    "headers": {"Authorization": "Bearer my_token"}
}

result = client.create_webhook(webhook_data)
webhook_id = result.get('webhook_id')
```

### List Webhooks

```python
webhooks = client.list_webhooks()
active_webhooks = webhooks.get('webhooks', [])
```

### Update Webhook

```python
updates = {
    "events": ["cat.created", "nft.minted"],
    "secret": "new_secret_456"
}

result = client.update_webhook(webhook_id=123, updates=updates)
```

### Delete Webhook

```python
client.delete_webhook(webhook_id=123)
```

### Test Webhook

```python
result = client.test_webhook(webhook_id=123)
status = result.get('status')
```

### Get Webhook Logs

```python
logs = client.get_webhook_logs(webhook_id=123, limit=100)
delivery_logs = logs.get('logs', [])
```

## 📊 Analytics & Health

Monitor platform performance and get comprehensive analytics data.

### Get Analytics Data

```python
# Get overview analytics
overview = client.get_analytics_data("overview")

# Get user behavior analytics
user_behavior = client.get_analytics_data(
    "user_behavior",
    filters={"timeframe": "7d"}
)

# Get cat interaction analytics
cat_interactions = client.get_analytics_data(
    "cat_interactions",
    filters={"timeframe": "30d"}
)
```

### Health Check

```python
health = client.health_check()
status = health.get('status')
```

### Get API Info

```python
api_info = client.get_api_info()
version = api_info.get('version')
```

### Get Rate Limit Info

```python
rate_limit = client.get_rate_limit_info()
remaining = rate_limit.get('remaining')
reset_time = rate_limit.get('reset_time')
```

## 🧪 Examples

See the `examples/` directory for comprehensive examples:

- `advanced_features_examples.py` - Complete examples for all advanced features
- `basic_usage.py` - Basic SDK usage examples
- `webhook_integration.py` - Webhook setup and testing examples

Run examples:

```bash
# Python examples
python examples/advanced_features_examples.py

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

```python
client = PurrrLoveClient(
    base_url="https://api.purrr.love",  # Default
    api_key="your_api_key_here"
)
```

## 🚨 Error Handling

The SDK provides comprehensive error handling with specific exception types:

```python
from purrr_love.exceptions import (
    PurrrLoveError,
    AuthenticationError,
    RateLimitError,
    ValidationError,
    NotFoundError
)

try:
    result = client.get_cat(cat_id=999)
except AuthenticationError:
    print("Invalid API key")
except NotFoundError:
    print("Cat not found")
except RateLimitError as e:
    print(f"Rate limit exceeded: {e}")
except PurrrLoveError as e:
    print(f"API error: {e}")
```

## 📈 Rate Limiting

The SDK automatically handles rate limiting and provides information about current limits:

```python
# Get current rate limit status
rate_limit = client.get_rate_limit_info()
print(f"Requests remaining: {rate_limit.get('remaining')}")
print(f"Reset time: {rate_limit.get('reset_time')}")
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
git clone https://github.com/purrr-love/python-sdk.git
cd python-sdk
pip install -e ".[dev]"
pre-commit install
```

### Running Tests

```bash
pytest tests/
pytest tests/ --cov=purrr_love --cov-report=html
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: [https://docs.purrr.love](https://docs.purrr.love)
- **Issues**: [GitHub Issues](https://github.com/purrr-love/python-sdk/issues)
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

---

*Built with ❤️ by the Purrr.love team*

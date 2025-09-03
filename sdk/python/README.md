# 🐱 Purrr.love Python SDK

**Official Python client library for the Purrr.love cat gaming platform**

[![PyPI version](https://badge.fury.io/py/purrr-love-sdk.svg)](https://badge.fury.io/py/purrr-love-sdk)
[![Python versions](https://img.shields.io/pypi/pyversions/purrr-love-sdk.svg)](https://pypi.org/project/purrr-love-sdk/)
[![License](https://img.shields.io/pypi/l/purrr-love-sdk.svg)](https://opensource.org/licenses/MIT)
[![Code style: black](https://img.shields.io/badge/code%20style-black-000000.svg)](https://github.com/psf/black)

## 🌟 Features

- **Complete API Coverage** - All Purrr.love API endpoints
- **Type Safety** - Full type hints and data validation
- **Advanced Cat Features** - VR interactions, AI learning, trading, shows, multiplayer
- **Health Monitoring** - Integration with real pet health devices
- **OAuth2 Support** - Secure authentication
- **Rate Limiting** - Built-in rate limit handling
- **Error Handling** - Comprehensive exception classes
- **Async Support** - Optional async/await support
- **WebSocket Support** - Real-time multiplayer features

## 🚀 Quick Start

### Installation

```bash
pip install purrr-love-sdk
```

### Basic Usage

```python
from purrr_love_sdk import PurrrLoveClient

# Initialize client
client = PurrrLoveClient(
    base_url="https://api.purrr.love",
    api_key="your_api_key_here"
)

# Get your cats
cats = client.get_cats()
for cat in cats:
    print(f"🐱 {cat.name} - Level {cat.level}")

# Play with a cat
result = client.play_with_cat(
    cat_id=cats[0].id,
    game_type="laser_pointer",
    duration=15
)
print(f"Play session result: {result}")
```

## 🐱 Advanced Features

### VR Cat Interaction

```python
# Start VR session
session = client.start_vr_session(
    cat_id=cat.id,
    vr_device="webvr"
)

# Interact in VR
response = client.vr_interact(
    session_id=session['session_id'],
    interaction_type="petting",
    location="head",
    intensity="gentle"
)
```

### AI Cat Behavior Learning

```python
# Get AI insights
insights = client.get_ai_insights(cat_id=cat.id)
print(f"Learning progress: {insights['learning_progress']}")
print(f"Behavior patterns: {insights['behavior_patterns']}")
```

### Cross-Platform Cat Trading

```python
# Create trading offer
offer = client.create_trading_offer(
    cat_id=cat.id,
    price=100.0,
    description="Beautiful Persian cat, well-trained",
    currency="USD"
)

# Browse available offers
offers = client.get_trading_offers(filters={'breed': 'persian'})
for offer in offers:
    print(f"Cat for sale: ${offer.price} - {offer.description}")
```

### Cat Show Competitions

```python
# Find cat shows
shows = client.get_cat_shows(filters={'status': 'upcoming'})
for show in shows:
    print(f"Show: {show.name} - {show.start_date}")

# Register your cat
registration = client.register_cat_for_show(
    cat_id=cat.id,
    show_id=shows[0].id,
    categories=["beauty", "personality"]
)
```

### Real-time Multiplayer

```python
# Join multiplayer room
session = client.join_multiplayer_room(
    cat_id=cat.id,
    room_type="playground"
)

# Perform actions
action = client.multiplayer_action(
    session_id=session['session_id'],
    action_type="move",
    direction="north",
    speed="walk"
)
```

### Health Monitoring

```python
# Register health device
device = client.register_health_device(
    cat_id=cat.id,
    device_data={
        "type": "smart_collar",
        "name": "WhiskerTracker Pro",
        "capabilities": ["activity", "heart_rate", "location"]
    }
)

# Get health summary
health = client.get_health_summary(
    cat_id=cat.id,
    timeframe="7d"
)
print(f"Health score: {health['overall_score']}")
```

## 🔐 Authentication

### API Key Authentication

```python
client = PurrrLoveClient(api_key="your_api_key_here")
```

### OAuth2 Authentication

```python
# For OAuth2 flows, you'll need to implement the authorization flow
# The SDK provides methods to work with OAuth2 tokens once obtained
```

## 📊 Data Models

The SDK provides comprehensive data models for all entities:

```python
from purrr_love_sdk import Cat, User, ApiKey, TradingOffer, CatShow

# All models support serialization/deserialization
cat_dict = cat.to_dict()
cat_from_dict = Cat.from_dict(cat_dict)

# Models include validation and type safety
print(cat.personality_type.value)  # Access enum values
print(cat.mood.value)
```

## 🚨 Error Handling

The SDK provides detailed exception classes:

```python
from purrr_love_sdk import (
    PurrrLoveError, AuthenticationError, RateLimitError,
    NotFoundError, ValidationError
)

try:
    cat = client.get_cat(cat_id=999)
except NotFoundError as e:
    print(f"Cat not found: {e}")
except RateLimitError as e:
    print(f"Rate limited: {e.retry_after} seconds")
except PurrrLoveError as e:
    print(f"API error: {e}")
```

## 🔧 Configuration

### Environment Variables

```bash
export PURRR_LOVE_API_KEY="your_api_key"
export PURRR_LOVE_BASE_URL="https://api.purrr.love"
```

### Configuration File

```python
import os
from dotenv import load_dotenv

load_dotenv()

client = PurrrLoveClient(
    base_url=os.getenv("PURRR_LOVE_BASE_URL"),
    api_key=os.getenv("PURRR_LOVE_API_KEY")
)
```

## 📚 API Reference

### Core Methods

- `get_cats()` - List user's cats
- `get_cat(cat_id)` - Get specific cat
- `create_cat(name, species, personality_type, breed)` - Create new cat
- `update_cat(cat_id, **kwargs)` - Update cat information
- `delete_cat(cat_id)` - Delete cat

### Activity Methods

- `play_with_cat(cat_id, game_type, duration)` - Play games
- `train_cat(cat_id, command, difficulty)` - Train commands
- `care_for_cat(cat_id, care_type, **kwargs)` - Provide care

### Advanced Features

- `start_vr_session(cat_id, vr_device)` - Start VR interaction
- `get_ai_insights(cat_id)` - Get AI learning insights
- `get_trading_offers(filters)` - Browse trading offers
- `get_cat_shows(filters)` - Find cat shows
- `join_multiplayer_room(cat_id, room_type)` - Join multiplayer

## 🧪 Testing

```bash
# Install development dependencies
pip install -e ".[dev]"

# Run tests
pytest

# Run with coverage
pytest --cov=purrr_love_sdk

# Code formatting
black purrr_love_sdk/

# Linting
flake8 purrr_love_sdk/
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

- **Documentation**: [https://docs.purrr.love/python-sdk](https://docs.purrr.love/python-sdk)
- **Issues**: [GitHub Issues](https://github.com/purrr-love/python-sdk/issues)
- **Discord**: [Purrr.love Community](https://discord.gg/purrr-love)
- **Email**: [dev@purrr.love](mailto:dev@purrr.love)

## 🐾 What's Next?

- **Async Client** - Full async/await support
- **WebSocket Client** - Real-time multiplayer
- **GraphQL Support** - Alternative to REST API
- **Mobile SDKs** - iOS and Android support
- **Plugin System** - Extensible functionality

---

**Made with ❤️ by the Purrr.love Team**

*Purrr.love - The Ultimate Cat Gaming Platform*

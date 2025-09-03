# 🚀 Purrr.love CLI Advanced Features Guide

## Overview

The Purrr.love CLI has been enhanced with comprehensive commands for managing advanced features including blockchain ownership, machine learning personality prediction, metaverse worlds, and webhook systems.

**CLI Version**: 2.1.0

## 🔐 Authentication

Before using advanced features, you must authenticate:

```bash
# Login with your credentials
purrr login

# Check authentication status
purrr config

# Logout when done
purrr logout
```

---

## ⛓️ Blockchain & NFT Management

### Command: `purrr blockchain` or `purrr nft`

Manage your cat NFTs on multiple blockchain networks.

#### Available Actions:

```bash
# Show help
purrr blockchain help

# Mint NFT for a cat
purrr blockchain mint <cat_id> [network] [metadata]

# Transfer NFT ownership
purrr blockchain transfer <nft_id> <to_user_id>

# Verify NFT ownership
purrr blockchain verify <nft_id>

# View your NFT collection
purrr blockchain collection [network]

# Browse NFT marketplace
purrr blockchain marketplace [filters]

# View blockchain statistics
purrr blockchain stats
```

#### Examples:

```bash
# Mint NFT for cat ID 123 on Ethereum
purrr blockchain mint 123 ethereum

# Mint with custom metadata
purrr blockchain mint 123 ethereum '{"rarity":"legendary","trait":"mystic"}'

# Transfer NFT 456 to user 789
purrr blockchain transfer 456 789

# Verify ownership of NFT 456
purrr blockchain verify 456

# View collection on Polygon
purrr blockchain collection polygon

# View marketplace listings
purrr blockchain marketplace

# View blockchain stats
purrr blockchain stats
```

#### Supported Networks:
- `ethereum` - Ethereum Mainnet
- `polygon` - Polygon Network
- `binance_smart_chain` - BSC
- `solana` - Solana Network

---

## 🧠 Machine Learning Personality

### Command: `purrr ml` or `purrr personality`

Analyze and predict cat personalities using advanced ML algorithms.

#### Available Actions:

```bash
# Show help
purrr ml help

# Predict cat personality
purrr ml predict <cat_id> [confidence]

# Get personality insights
purrr ml insights <cat_id>

# Record behavior observation
purrr ml observe <cat_id> <type> [intensity] [duration] [context]

# Update genetic data
purrr ml genetic <cat_id> <heritage_score> [coat_pattern] [markers]

# Check ML training status
purrr ml training
```

#### Examples:

```bash
# Predict personality for cat 123
purrr ml predict 123

# Predict with confidence scores
purrr ml predict 123 true

# Get detailed insights
purrr ml insights 123

# Record behavior observation
purrr ml observe 123 play 8 300 "indoor, sunny day"

# Update genetic data
purrr ml genetic 123 85 tabby "hunting_instinct:0.8"

# Check training status
purrr ml training
```

#### Behavior Types:
- `play` - Playful behavior
- `social` - Social interactions
- `explore` - Exploratory behavior
- `groom` - Grooming behavior
- `hunt` - Hunting instincts
- `sleep` - Sleep patterns

#### Personality Dimensions:
- `openness` - Openness to experience
- `conscientiousness` - Organization and discipline
- `extraversion` - Social and energetic traits
- `agreeableness` - Cooperation and trust
- `neuroticism` - Emotional stability

---

## 🌐 Metaverse & VR Worlds

### Command: `purrr metaverse` or `purrr vr`

Create and manage virtual 3D cat worlds and VR experiences.

#### Available Actions:

```bash
# Show help
purrr metaverse help

# Create new world
purrr metaverse create <name> <type> [max_players] [access_level]

# Join a world
purrr metaverse join <world_id> [cat_id]

# Leave a world
purrr metaverse leave <world_id>

# List active worlds
purrr metaverse worlds [filters]

# View world players
purrr metaverse players <world_id>

# Perform VR interaction
purrr metaverse interact <world_id> <type> [data]

# Create social VR space
purrr metaverse social <world_id> <name> <type> [capacity]

# View metaverse statistics
purrr metaverse stats
```

#### Examples:

```bash
# Create a cat paradise world
purrr metaverse create "Cat Paradise" cat_park 100 public

# Join world with specific cat
purrr metaverse join world_123 cat_456

# Leave current world
purrr metaverse leave world_123

# List all active worlds
purrr metaverse worlds

# View players in world
purrr metaverse players world_123

# Perform interaction
purrr metaverse interact world_123 petting "cat_789"

# Create social space
purrr metaverse social world_123 "Chat Lounge" chat_room 25

# View metaverse stats
purrr metaverse stats
```

#### World Types:
- `cat_park` - Outdoor cat playground
- `virtual_home` - Indoor cat environment
- `adventure_zone` - Exploration areas
- `social_hub` - Community spaces
- `custom` - User-defined worlds

#### Access Levels:
- `public` - Open to all users
- `friends` - Friends only
- `private` - Invitation only
- `invite_only` - Requires invitation

---

## 🔗 Webhook Management

### Command: `purrr webhooks`

Manage webhooks for real-time notifications and integrations.

#### Available Actions:

```bash
# Show help
purrr webhooks help

# Create new webhook
purrr webhooks create <url> <events> [secret] [headers]

# List your webhooks
purrr webhooks list

# Update webhook
purrr webhooks update <id> <updates>

# Delete webhook
purrr webhooks delete <id>

# Test webhook delivery
purrr webhooks test <id>

# View delivery logs
purrr webhooks logs <id> [limit]
```

#### Examples:

```bash
# Create webhook for cat events
purrr webhooks create "https://myapp.com/webhook" "cat.created,cat.updated" "secret123"

# Create webhook with custom headers
purrr webhooks create "https://api.example.com/webhook" "nft.minted" "secret456" '{"Authorization":"Bearer token"}'

# List all webhooks
purrr webhooks list

# Update webhook settings
purrr webhooks update 123 '{"events":["cat.created","cat.deleted"]}'

# Delete webhook
purrr webhooks delete 123

# Test webhook delivery
purrr webhooks test 123

# View delivery logs
purrr webhooks logs 123 100
```

#### Supported Events:
- `cat.created` - New cat created
- `cat.updated` - Cat information updated
- `nft.minted` - NFT minted
- `nft.transferred` - NFT ownership transferred
- `metaverse.world_joined` - User joined world
- `metaverse.vr_interaction` - VR interaction performed
- `ml.personality_predicted` - Personality prediction completed

---

## 🎯 Advanced Usage Examples

### Complete Workflow Examples:

#### 1. Create Cat NFT and List on Marketplace

```bash
# Login first
purrr login

# Mint NFT for your cat
purrr blockchain mint 123 ethereum '{"rarity":"epic","trait":"mystic"}'

# Create marketplace listing
purrr blockchain create-listing 456 0.5 ETH 30

# View your collection
purrr blockchain collection ethereum
```

#### 2. Analyze Cat Personality and Join Metaverse

```bash
# Predict personality
purrr ml predict 123 true

# Get insights and recommendations
purrr ml insights 123

# Record behavior observations
purrr ml observe 123 play 9 600 "outdoor, sunny, other_cats_present"

# Create metaverse world
purrr metaverse create "My Cat World" custom 50 friends

# Join the world
purrr metaverse join world_789 123
```

#### 3. Set Up Webhook Integration

```bash
# Create webhook for real-time updates
purrr webhooks create "https://myapp.com/updates" "nft.minted,cat.updated,metaverse.world_joined" "webhook_secret"

# Test the webhook
purrr webhooks test 456

# Monitor delivery logs
purrr webhooks logs 456 50
```

---

## 🔧 Configuration

### CLI Configuration File

The CLI stores configuration in `~/.purrr/config.json`:

```json
{
  "api_url": "https://api.purrr.love",
  "access_token": "your_oauth_token",
  "api_key": "your_api_key",
  "user_id": "your_user_id"
}
```

### Environment Variables

You can also set configuration via environment variables:

```bash
export PURR_API_URL="https://api.purrr.love"
export PURR_ACCESS_TOKEN="your_token"
export PURR_API_KEY="your_key"
```

---

## 🚨 Troubleshooting

### Common Issues:

#### 1. Authentication Errors
```bash
# Check if logged in
purrr config

# Re-login if needed
purrr logout
purrr login
```

#### 2. API Connection Issues
```bash
# Check API URL
purrr config

# Test connection
purrr blockchain stats
```

#### 3. Permission Errors
- Ensure your account has the required permissions
- Check if you're using the correct authentication method
- Verify your API key has the necessary scopes

#### 4. Data Validation Errors
- Check command syntax with `help`
- Ensure all required parameters are provided
- Validate data formats (JSON, numbers, etc.)

---

## 📚 Additional Resources

### Related Documentation:
- [API Reference](../api/README.md)
- [Advanced Features Status](../ADVANCED_FEATURES_IMPLEMENTATION_STATUS.md)
- [Database Schema](../database/advanced_features_schema.sql)

### Support:
- Use `purrr <command> help` for command-specific help
- Check the main help with `purrr help`
- Review error messages for troubleshooting guidance

---

## 🎉 What's New in v2.1.0

### New Commands Added:
- ✅ **Blockchain/NFT Management** - Complete NFT lifecycle management
- ✅ **ML Personality** - Advanced cat behavior analysis
- ✅ **Metaverse/VR** - Virtual world creation and management
- ✅ **Webhook System** - Real-time integration management

### Enhanced Features:
- 🔄 **Multi-network Support** - Ethereum, Polygon, BSC, Solana
- 🧠 **AI-Powered Insights** - Personality prediction and analysis
- 🌐 **3D World Management** - Create and manage virtual spaces
- 🔗 **Event-Driven Integration** - Webhook-based real-time updates

### Performance Improvements:
- ⚡ **Faster Response Times** - Optimized API calls
- 🎨 **Better Output Formatting** - Color-coded and structured responses
- 📊 **Comprehensive Statistics** - Detailed metrics and insights

---

*Happy cat gaming with advanced features! 🐱✨*

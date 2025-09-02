# 🚀 Purrr.love API Documentation

**Complete API Ecosystem for Cat Gaming Platform**

The Purrr.love API provides programmatic access to all platform features including cat management, gaming, breeding, quests, and more. Built with OAuth2 authentication and API key management.

## 🔐 **Authentication**

### **OAuth2 Flow**
```
1. Redirect to: /oauth/authorize
2. User authorizes application
3. Receive authorization code
4. Exchange code for access token: /oauth/token
5. Use access token in Authorization header
```

### **API Key Authentication**
```
Authorization: Bearer YOUR_API_KEY
X-API-Key: YOUR_API_KEY
```

## 🌟 **Core API Endpoints**

### **Authentication & Users**
- `POST /api/v1/auth/login` - OAuth2 login
- `POST /api/v1/auth/refresh` - Refresh access token
- `GET /api/v1/auth/profile` - Get user profile
- `PUT /api/v1/auth/profile` - Update user profile
- `POST /api/v1/auth/logout` - Logout user

### **API Key Management**
- `GET /api/v1/keys` - List user API keys
- `POST /api/v1/keys` - Generate new API key
- `PUT /api/v1/keys/{id}` - Update API key
- `DELETE /api/v1/keys/{id}` - Revoke API key
- `GET /api/v1/keys/{id}/usage` - Get API key usage stats

### **Cat Management**
- `GET /api/v1/cats` - List user's cats
- `GET /api/v1/cats/{id}` - Get cat details
- `POST /api/v1/cats` - Create new cat
- `PUT /api/v1/cats/{id}` - Update cat
- `DELETE /api/v1/cats/{id}` - Delete cat
- `GET /api/v1/cats/{id}/stats` - Get cat statistics
- `GET /api/v1/cats/{id}/personality` - Get cat personality

### **Gaming & Activities**
- `GET /api/v1/games` - List available games
- `POST /api/v1/games/{type}/play` - Play a game
- `GET /api/v1/games/history` - Get game history
- `GET /api/v1/games/leaderboard` - Get leaderboards
- `POST /api/v1/cats/{id}/feed` - Feed a cat
- `POST /api/v1/cats/{id}/play` - Play with a cat
- `POST /api/v1/cats/{id}/groom` - Groom a cat

### **Breeding & Genetics**
- `GET /api/v1/breeding/pairs` - Get breeding pairs
- `POST /api/v1/breeding/breed` - Start breeding
- `GET /api/v1/breeding/history` - Get breeding history
- `GET /api/v1/breeding/offspring` - Get offspring
- `GET /api/v1/genetics/traits` - Get genetic traits
- `GET /api/v1/genetics/predictions` - Get breeding predictions

### **Quests & Achievements**
- `GET /api/v1/quests` - List available quests
- `POST /api/v1/quests/{id}/start` - Start a quest
- `GET /api/v1/quests/progress` - Get quest progress
- `GET /api/v1/achievements` - List achievements
- `GET /api/v1/achievements/{id}` - Get achievement details

### **Store & Economy**
- `GET /api/v1/store/items` - List store items
- `POST /api/v1/store/purchase` - Purchase item
- `GET /api/v1/store/inventory` - Get user inventory
- `GET /api/v1/economy/balance` - Get crypto balances
- `POST /api/v1/economy/deposit` - Deposit crypto
- `POST /api/v1/economy/withdraw` - Withdraw crypto

### **Social Features**
- `GET /api/v1/social/friends` - List friends
- `POST /api/v1/social/friends/add` - Add friend
- `DELETE /api/v1/social/friends/{id}` - Remove friend
- `GET /api/v1/social/messages` - Get messages
- `POST /api/v1/social/messages` - Send message
- `GET /api/v1/social/neighborhoods` - List neighborhoods

## 🔧 **API Response Format**

### **Success Response**
```json
{
  "success": true,
  "data": {
    // Response data
  },
  "meta": {
    "timestamp": "2024-01-01T00:00:00Z",
    "request_id": "req_123456789"
  }
}
```

### **Error Response**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input data",
    "details": {
      "field": "email",
      "issue": "Invalid email format"
    }
  },
  "meta": {
    "timestamp": "2024-01-01T00:00:00Z",
    "request_id": "req_123456789"
  }
}
```

## 📊 **Rate Limiting**

- **Free Tier**: 100 requests/hour
- **Premium Tier**: 1000 requests/hour
- **Enterprise Tier**: 10000 requests/hour

Rate limit headers included in all responses:
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

## 🚀 **Getting Started**

### **1. Register Application**
```bash
curl -X POST https://api.purrr.love/oauth/applications \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Cat App",
    "redirect_uri": "https://myapp.com/callback"
  }'
```

### **2. Get Authorization URL**
```
https://api.purrr.love/oauth/authorize?
  client_id=YOUR_CLIENT_ID&
  redirect_uri=https://myapp.com/callback&
  response_type=code&
  scope=read write
```

### **3. Exchange Code for Token**
```bash
curl -X POST https://api.purrr.love/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "authorization_code",
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT_SECRET",
    "code": "AUTHORIZATION_CODE",
    "redirect_uri": "https://myapp.com/callback"
  }'
```

### **4. Make API Calls**
```bash
curl -X GET https://api.purrr.love/api/v1/cats \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## 🐱 **CLI Interface**

The Purrr.love CLI provides command-line access to all API features:

```bash
# Install CLI
npm install -g @purrr-love/cli

# Login
purrr login

# List cats
purrr cats list

# Play game
purrr games play mouse-hunt --cat-id 123

# Feed cat
purrr cats feed 123 --food "premium-tuna"

# Get breeding predictions
purrr breeding predict --parent1 123 --parent2 456
```

## 🔒 **Security Features**

- **OAuth2** with PKCE support
- **API Key** management with scopes
- **Rate Limiting** per user/application
- **Request Signing** for sensitive operations
- **IP Whitelisting** for enterprise users
- **Audit Logging** for all API calls

## 📈 **Monitoring & Analytics**

- **Real-time API metrics**
- **Usage analytics** per endpoint
- **Performance monitoring**
- **Error tracking** and alerting
- **User behavior insights**

---

**Ready to build amazing cat applications? Start with our API! 🐱✨**

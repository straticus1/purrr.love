# 🤝 Contributing to Purrr.love

First off, thank you for considering contributing to Purrr.love! It's people like you that make Purrr.love such a great platform for cat lovers and gaming enthusiasts worldwide.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Getting Started](#getting-started)
- [Development Process](#development-process)
- [Coding Standards](#coding-standards)
- [Testing Guidelines](#testing-guidelines)
- [Documentation Standards](#documentation-standards)
- [Submitting Changes](#submitting-changes)
- [Review Process](#review-process)
- [Advanced Features Development](#advanced-features-development)
  - [Blockchain & NFT Guidelines](#blockchain--nft-guidelines)
  - [Machine Learning Guidelines](#machine-learning-guidelines)
  - [Webhook Development](#webhook-development)
  - [Lost Pet Finder Guidelines](#lost-pet-finder-guidelines)
- [Community](#community)

## 📜 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to [admin@purrr.love](mailto:admin@purrr.love).

### Our Pledge

- Be respectful and inclusive
- Welcome newcomers and help them learn
- Focus on constructive feedback
- Respect different viewpoints and experiences
- Show empathy towards other community members

## 🎯 How Can I Contribute?

### 🐛 Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When you are creating a bug report, please include as many details as possible:

**Bug Report Template:**
```markdown
**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected behavior**
A clear and concise description of what you expected to happen.

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Environment (please complete the following information):**
- OS: [e.g. Ubuntu 20.04]
- Browser [e.g. chrome, safari]
- PHP Version [e.g. 8.0.15]
- Database [e.g. MySQL 8.0.28]

**Additional context**
Add any other context about the problem here.
```

### 💡 Suggesting Features

We love feature suggestions! Before creating enhancement suggestions, please check the existing feature requests. When creating a feature request, please include:

**Feature Request Template:**
```markdown
**Is your feature request related to a problem? Please describe.**
A clear and concise description of what the problem is.

**Describe the solution you'd like**
A clear and concise description of what you want to happen.

**Describe alternatives you've considered**
A clear and concise description of any alternative solutions or features you've considered.

**Additional context**
Add any other context or screenshots about the feature request here.

**Implementation Ideas**
If you have technical ideas about how this could be implemented, share them here.
```

### 🔧 Contributing Code

We welcome code contributions! Here are the areas where we especially need help:

**High Priority Areas:**
- Security improvements and audits
- Performance optimizations
- Accessibility enhancements
- Mobile responsiveness improvements
- Test coverage expansion
- Documentation improvements

**Feature Development:**
- New cat-themed games
- Enhanced breeding mechanics
- AI behavior improvements
- VR interaction features
- Mobile app development
- API endpoint enhancements

**Advanced Features (v2.1.0+):**
- Blockchain/NFT integrations
- Machine learning model improvements
- Webhook system enhancements
- Lost Pet Finder features
- Metaverse/VR world development
- Advanced analytics dashboard

**Bug Fixes:**
- Check our [Issues](https://github.com/straticus1/purrr.love/issues) page for bugs labeled `good-first-issue`

## 🚀 Getting Started

### Prerequisites

Before you start contributing, make sure you have:

- PHP 8.0+ installed
- Composer for dependency management
- MySQL/PostgreSQL for database
- Git for version control
- A code editor (VS Code, PHPStorm, etc.)
- Basic knowledge of PHP, JavaScript, HTML/CSS

### Setting Up Development Environment

1. **Fork the Repository**
   ```bash
   # Fork the repo on GitHub, then clone your fork
   git clone https://github.com/YOUR_USERNAME/purrr.love.git
   cd purrr.love
   ```

2. **Add Upstream Remote**
   ```bash
   git remote add upstream https://github.com/straticus1/purrr.love.git
   ```

3. **Install Dependencies**
   ```bash
   composer install
   ```

4. **Set Up Environment**
   ```bash
   cp config/config.example.php config/config.php
   cp config/database.example.php config/database.php
   # Edit configuration files with your local settings
   ```

5. **Set Up Database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE purrr_love_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p purrr_love_dev < database/schema.sql
   ```

6. **Enable Development Mode**
   ```php
   // In config/config.php
   define('DEVELOPER_MODE', true);
   define('DEBUG_MODE', true);
   define('ERROR_REPORTING', true);
   ```

7. **Run Tests**
   ```bash
   ./vendor/bin/phpunit
   ```

8. **Start Development Server**
   ```bash
   php -S localhost:8000
   ```

### Development Workflow

1. **Sync with Upstream**
   ```bash
   git fetch upstream
   git checkout main
   git merge upstream/main
   ```

2. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   # OR for bug fixes:
   git checkout -b fix/bug-description
   ```

3. **Make Changes**
   - Write clean, well-documented code
   - Follow our coding standards (see below)
   - Add tests for new functionality
   - Update documentation as needed

4. **Test Your Changes**
   ```bash
   # Run all tests
   ./vendor/bin/phpunit
   
   # Run specific test suite
   ./vendor/bin/phpunit tests/Unit/
   
   # Check code style
   ./vendor/bin/phpcs
   
   # Fix code style automatically
   ./vendor/bin/phpcbf
   ```

5. **Commit Changes**
   ```bash
   git add .
   git commit -m "feat: add new cat personality system
   
   - Add 6 distinct personality types
   - Implement behavior inheritance
   - Add personality-based quest generation
   - Include comprehensive tests
   
   Closes #123"
   ```

## 🎨 Coding Standards

### PHP Standards

We follow **PSR-12** coding standards with additional requirements:

```php
<?php
declare(strict_types=1);

namespace Purrr\Cat;

/**
 * Cat behavior management class
 * 
 * Handles cat personality types, behaviors, and interactions
 * 
 * @author Your Name <your.email@example.com>
 */
class CatBehavior
{
    private const PERSONALITY_TYPES = [
        'playful',
        'aloof', 
        'curious',
        'lazy',
        'territorial',
        'social_butterfly'
    ];

    /**
     * Get cat behavior based on personality type
     * 
     * @param string $personalityType The cat's personality type
     * @return array Behavior configuration
     * @throws InvalidArgumentException If personality type is invalid
     */
    public function getBehavior(string $personalityType): array
    {
        if (!in_array($personalityType, self::PERSONALITY_TYPES, true)) {
            throw new InvalidArgumentException("Invalid personality type: {$personalityType}");
        }

        return $this->loadBehaviorConfig($personalityType);
    }
}
```

**Key Requirements:**
- Use strict types: `declare(strict_types=1);`
- Comprehensive DocBlocks for all public methods
- Type hints for all parameters and return values
- Proper error handling with try-catch blocks
- Input validation and sanitization
- No unused imports or variables

### Database Standards

```php
// ✅ GOOD - Always use prepared statements
function getCatById(int $catId, int $userId): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM cats WHERE id = ? AND user_id = ?");
    $stmt->execute([$catId, $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// ❌ BAD - Never use string concatenation
function getCatById($catId, $userId) 
{
    $pdo = get_db();
    $result = $pdo->query("SELECT * FROM cats WHERE id = $catId AND user_id = $userId");
    return $result->fetch();
}
```

### Security Standards

```php
// ✅ GOOD - Always validate CSRF tokens
function processForm(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new BadMethodCallException('Invalid request method');
    }
    
    requireCSRFToken(); // Validates CSRF token
    
    $catName = sanitizeInput($_POST['cat_name'], 'string');
    $userId = (int) $_SESSION['user_id'];
    
    // Process form...
}

// ✅ GOOD - Always escape output
function displayCatName(string $catName): void
{
    echo htmlspecialchars($catName, ENT_QUOTES, 'UTF-8');
}
```

### JavaScript Standards

```javascript
// Use modern ES6+ features
class CatInteraction {
    constructor(catId, userId) {
        this.catId = catId;
        this.userId = userId;
        this.isInteracting = false;
    }

    async feedCat(foodType) {
        if (this.isInteracting) {
            throw new Error('Cat is already being interacted with');
        }

        this.isInteracting = true;
        
        try {
            const response = await fetch(`/api/v1/cats/${this.catId}/feed`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': getCsrfToken()
                },
                body: JSON.stringify({
                    food_type: foodType,
                    user_id: this.userId
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } finally {
            this.isInteracting = false;
        }
    }
}
```

### CSS Standards

```css
/* Use BEM methodology for class naming */
.cat-card {
    display: flex;
    flex-direction: column;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.cat-card__image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px 8px 0 0;
}

.cat-card__content {
    padding: 16px;
}

.cat-card__title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: #2d3748;
}

/* Use CSS custom properties for theming */
:root {
    --primary-color: #4299e1;
    --secondary-color: #ed8936;
    --success-color: #48bb78;
    --error-color: #f56565;
}
```

## 🔒 **NEW: Enterprise Security Development Guidelines v1.2.0**

### 🎉 **SECURITY-FIRST DEVELOPMENT APPROACH**

**🚀 With Purrr.love now being enterprise-grade secure, all contributions MUST follow our comprehensive security framework!**

As of version 1.2.0, Purrr.love implements enterprise-grade security standards. All contributors must understand and follow these security guidelines to maintain our **Enterprise Grade A+** security rating.

---

### 🔍 **Security Review Checklist**

**❗ ALL PULL REQUESTS MUST PASS THESE SECURITY CHECKS:**

#### ✅ **Authentication & Authorization**
- [ ] All endpoints require proper authentication
- [ ] User permissions are validated for every operation
- [ ] Session regeneration is implemented where needed
- [ ] Password hashing uses Argon2id algorithm
- [ ] API key scopes are properly enforced

#### ✅ **Input Validation & Sanitization**
- [ ] All user input is validated using SecurityInputValidator
- [ ] SQL injection patterns are detected and blocked
- [ ] XSS prevention is applied to all output
- [ ] File uploads are properly validated (MIME type, size, content)
- [ ] JSON inputs are validated against expected schema

#### ✅ **CSRF Protection**
- [ ] All forms include CSRF tokens
- [ ] CSRF tokens are validated on every POST/PUT/DELETE
- [ ] Origin and Referrer headers are validated
- [ ] API endpoints use proper CSRF protection

#### ✅ **Rate Limiting & DDoS Protection**
- [ ] Rate limiting is implemented for all public endpoints
- [ ] Violation tracking is properly configured
- [ ] IP banning logic is tested
- [ ] Burst protection handles traffic spikes

#### ✅ **Security Logging & Monitoring**
- [ ] Security events are logged with proper severity
- [ ] Sensitive data is not logged (passwords, tokens, etc.)
- [ ] Log entries include sufficient context for investigation
- [ ] Critical events trigger real-time alerts

---

### 📝 **Secure Coding Examples**

#### **✅ SECURE: Authentication Implementation**
```php
// ✅ EXCELLENT - Enterprise-grade authentication
function authenticateUserSecurely(string $username, string $password): array
{
    $securityLogger = new SecurityLogger();
    
    try {
        // Check rate limiting first
        if (checkLoginAttemptLimit($username)) {
            $securityLogger->logSecurityEvent('LOGIN_RATE_LIMIT', [
                'username' => $username,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ]);
            throw new SecurityException('Too many login attempts');
        }
        
        // Get user with secure query
        $user = SecureDatabase::executeSecureQuery(
            'SELECT id, username, password_hash, role FROM users WHERE username = ? AND active = 1',
            [$username]
        );
        
        if (!$user || !password_verify($password, $user[0]['password_hash'])) {
            recordFailedLogin($username);
            $securityLogger->logSecurityEvent('LOGIN_FAILED', [
                'username' => $username,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            throw new AuthenticationException('Invalid credentials');
        }
        
        // Initialize secure session
        initializeSecureSession();
        
        // Set secure session variables
        $_SESSION['user_id'] = $user[0]['id'];
        $_SESSION['user_role'] = $user[0]['role'];
        $_SESSION['csrf_token'] = generateSecureCSRFToken();
        $_SESSION['login_time'] = time();
        
        // Log successful authentication
        $securityLogger->logSecurityEvent('LOGIN_SUCCESS', [
            'user_id' => $user[0]['id'],
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        return $user[0];
        
    } catch (Exception $e) {
        $securityLogger->logSecurityEvent('AUTH_ERROR', [
            'error' => $e->getMessage(),
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        throw $e;
    }
}
```

#### **✅ SECURE: Input Validation Implementation**
```php
// ✅ EXCELLENT - Comprehensive input validation with threat detection
function processSecureCatUpdate(int $catId, array $updateData): array
{
    $validator = new SecurityInputValidator();
    $securityLogger = new SecurityLogger();
    
    try {
        // Validate ownership first
        $userId = $_SESSION['user_id'] ?? null;
        if (!canAccessCat($catId, $userId)) {
            $securityLogger->logSecurityEvent('UNAUTHORIZED_ACCESS', [
                'cat_id' => $catId,
                'user_id' => $userId,
                'action' => 'cat_update'
            ]);
            throw new UnauthorizedException('Access denied');
        }
        
        // Validate CSRF token
        if (!CSRFProtection::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new CSRFException('CSRF token validation failed');
        }
        
        // Validate and sanitize each field with type-specific validation
        $validatedData = [];
        
        if (isset($updateData['name'])) {
            $validatedData['name'] = $validator->validateInput(
                $updateData['name'], 
                'string', 
                ['max_length' => 100, 'min_length' => 1]
            );
        }
        
        if (isset($updateData['description'])) {
            $validatedData['description'] = $validator->validateInput(
                $updateData['description'], 
                'string', 
                ['max_length' => 1000, 'allow_html' => false]
            );
        }
        
        if (isset($updateData['personality_type'])) {
            $allowedPersonalities = ['playful', 'aloof', 'curious', 'lazy', 'territorial', 'social_butterfly'];
            if (!in_array($updateData['personality_type'], $allowedPersonalities)) {
                throw new ValidationException('Invalid personality type');
            }
            $validatedData['personality_type'] = $updateData['personality_type'];
        }
        
        // Update cat with secure query
        $result = SecureDatabase::executeSecureQuery(
            'UPDATE cats SET name = ?, description = ?, personality_type = ?, updated_at = NOW() WHERE id = ? AND user_id = ?',
            [
                $validatedData['name'] ?? null,
                $validatedData['description'] ?? null, 
                $validatedData['personality_type'] ?? null,
                $catId,
                $userId
            ]
        );
        
        // Log successful update
        $securityLogger->logSecurityEvent('CAT_UPDATED', [
            'cat_id' => $catId,
            'user_id' => $userId,
            'fields_updated' => array_keys($validatedData)
        ]);
        
        return ['success' => true, 'cat_id' => $catId, 'updated_fields' => array_keys($validatedData)];
        
    } catch (Exception $e) {
        $securityLogger->logSecurityEvent('CAT_UPDATE_ERROR', [
            'error' => $e->getMessage(),
            'cat_id' => $catId,
            'user_id' => $userId ?? null
        ]);
        throw $e;
    }
}
```

#### **✅ SECURE: API Endpoint Implementation**
```php
// ✅ EXCELLENT - Secure API endpoint with comprehensive protection
function handleSecureAPIRequest(string $endpoint, string $method, array $data): array
{
    $rateLimiter = new EnhancedRateLimiting();
    $securityLogger = new SecurityLogger();
    $validator = new SecurityInputValidator();
    
    try {
        // Check CORS first
        $corsHandler = new SecureCORS();
        if (!$corsHandler->handleCORSRequest()) {
            throw new CORSException('CORS validation failed');
        }
        
        // Rate limiting check
        $clientIP = $_SERVER['REMOTE_ADDR'];
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;
        $identifier = $apiKey ? "api_key:{$apiKey}" : "ip:{$clientIP}";
        
        $userTier = getUserTierFromAPIKey($apiKey) ?? 'free';
        $rateLimitResult = $rateLimiter->checkRateLimit($identifier, $endpoint, $userTier);
        
        // Set rate limit headers
        header('X-RateLimit-Limit: ' . $rateLimitResult['limit']);
        header('X-RateLimit-Remaining: ' . $rateLimitResult['remaining']);
        header('X-RateLimit-Reset: ' . $rateLimitResult['reset']);
        
        // Authenticate request
        $user = authenticateAPIRequest($apiKey);
        
        // Validate input data
        $validatedData = [];
        foreach ($data as $key => $value) {
            $validatedData[$key] = $validator->validateInput($value, getFieldType($key));
        }
        
        // Process the actual API request
        $result = processAPIEndpoint($endpoint, $method, $validatedData, $user);
        
        // Log successful API call
        $securityLogger->logSecurityEvent('API_CALL_SUCCESS', [
            'endpoint' => $endpoint,
            'method' => $method,
            'user_id' => $user['id'] ?? null,
            'response_time' => (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000
        ]);
        
        return [
            'success' => true,
            'data' => $result,
            'meta' => [
                'rate_limit' => $rateLimitResult,
                'api_version' => '1.2.0'
            ]
        ];
        
    } catch (Exception $e) {
        $securityLogger->logSecurityEvent('API_CALL_ERROR', [
            'endpoint' => $endpoint,
            'method' => $method,
            'error' => $e->getMessage(),
            'error_type' => get_class($e),
            'ip' => $clientIP
        ]);
        
        // Return appropriate error response
        http_response_code($e->getCode() ?: 500);
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'error_code' => $e->getCode() ?: 500
        ];
    }
}
```

#### **❌ INSECURE: What NOT to do**
```php
// ❌ BAD - Multiple security vulnerabilities
function badCatUpdate($catId, $data) {
    // ❌ NO authentication check
    // ❌ NO CSRF protection
    // ❌ NO input validation
    // ❌ SQL injection vulnerability
    // ❌ No error handling
    // ❌ No security logging
    
    $query = "UPDATE cats SET name = '{$data['name']}' WHERE id = $catId";
    mysql_query($query); // ❌ Deprecated function + SQL injection
    
    return "Cat updated"; // ❌ No structured response
}

// ❌ BAD - XSS vulnerability
function badDisplayCatName($catName) {
    echo $catName; // ❌ No output escaping - XSS vulnerability
}

// ❌ BAD - Weak authentication
function badAuthentication($username, $password) {
    $user = getUser($username);
    if (md5($password) == $user['password']) { // ❌ Weak hashing
        $_SESSION['user'] = $username; // ❌ No session regeneration
        return true;
    }
    return false; // ❌ No rate limiting, no logging
}
```

---

### 📈 **Security Testing Requirements**

#### **✅ Required Security Tests**
```php
// Example security test class
class SecurityTest extends PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @group security
     */
    public function it_blocks_sql_injection_attempts(): void
    {
        $validator = new SecurityInputValidator();
        
        $maliciousInputs = [
            "'; DROP TABLE cats; --",
            "1' OR '1'='1",
            "1'; UPDATE users SET password = 'hacked'; --",
            "UNION SELECT password FROM users"
        ];
        
        foreach ($maliciousInputs as $input) {
            $this->expectException(SecurityException::class);
            $validator->validateInput($input, 'int');
        }
    }
    
    /**
     * @test
     * @group security
     */
    public function it_blocks_xss_attempts(): void
    {
        $validator = new SecurityInputValidator();
        
        $xssPayloads = [
            "<script>alert('XSS')</script>",
            "javascript:alert('XSS')",
            "<img src=x onerror=alert('XSS')>",
            "<iframe src='javascript:alert(\"XSS\")'></iframe>"
        ];
        
        foreach ($xssPayloads as $payload) {
            $this->expectException(SecurityException::class);
            $validator->validateInput($payload, 'string');
        }
    }
    
    /**
     * @test
     * @group security
     */
    public function it_enforces_rate_limiting(): void
    {
        $rateLimiter = new EnhancedRateLimiting();
        
        // Simulate exceeding rate limit
        for ($i = 0; $i < 101; $i++) {
            try {
                $rateLimiter->checkRateLimit('test_ip', '/api/cats', 'free');
            } catch (RateLimitException $e) {
                $this->assertEquals(429, $e->getCode());
                return; // Test passed
            }
        }
        
        $this->fail('Rate limiting did not trigger');
    }
    
    /**
     * @test
     * @group security
     */
    public function it_validates_csrf_tokens(): void
    {
        $csrf = new CSRFProtection();
        
        // Valid token should pass
        $validToken = $csrf->generateCSRFToken();
        $this->assertTrue($csrf->validateCSRFToken($validToken));
        
        // Invalid token should fail
        $this->expectException(CSRFException::class);
        $csrf->validateCSRFToken('invalid_token');
    }
    
    /**
     * @test
     * @group security
     */
    public function it_logs_security_events(): void
    {
        $logger = new SecurityLogger();
        $logger->logSecurityEvent('TEST_EVENT', ['test' => 'data']);
        
        // Verify log was created
        $logs = $this->getSecurityLogsFromDatabase();
        $this->assertCount(1, $logs);
        $this->assertEquals('TEST_EVENT', $logs[0]['event_type']);
    }
}
```

---

### 🔍 **Security Code Review Process**

#### **✅ Review Checklist for Maintainers**

**Before approving ANY pull request, reviewers MUST verify:**

1. **🔒 Authentication & Authorization**
   - All endpoints check user authentication
   - Permission validation is present and correct
   - Session handling follows secure practices
   - API key usage is properly scoped

2. **⚡ Input Validation**
   - All user inputs use SecurityInputValidator
   - Type-specific validation is applied
   - Length and format constraints are enforced
   - Threat detection patterns are checked

3. **🛡️ CSRF Protection** 
   - Forms include CSRF tokens
   - Token validation is implemented
   - Origin/Referrer headers are checked
   - Single-use tokens are enforced

4. **📊 Rate Limiting**
   - Public endpoints have rate limiting
   - Tier-based limits are respected
   - Violation tracking is enabled
   - IP banning logic is tested

5. **📈 Security Logging**
   - Security events are logged
   - Appropriate severity levels set
   - No sensitive data in logs
   - Forensic information included

6. **🗋 Performance & Caching**
   - Caching doesn't expose sensitive data
   - Cache keys are properly namespaced
   - TTL values are appropriate
   - Cache invalidation works correctly

#### **✅ Required Security Approvals**

- **🔒 High-Risk Changes**: Require 2+ maintainer approvals
- **⚡ Medium-Risk Changes**: Require 1 maintainer approval  
- **📏 Low-Risk Changes**: Require security checklist completion

**High-Risk Changes Include:**
- Authentication/authorization modifications
- Database query changes
- File upload functionality
- API endpoint modifications
- Configuration changes

---

### 📏 **Security Documentation Requirements**

#### **✅ Required Documentation Updates**

When contributing security-related changes:

1. **Update Security Documentation**
   - Document new security features
   - Update threat models
   - Revise security architecture diagrams
   - Add configuration examples

2. **Update Installation Guide** 
   - Add security setup instructions
   - Document environment variables
   - Include security validation steps
   - Provide troubleshooting guidance

3. **Update API Documentation**
   - Document authentication requirements
   - Include rate limiting information
   - Show security headers in examples
   - Document error responses

#### **✅ Security Documentation Example**
```markdown
## 🔒 Security Implementation: Enhanced Rate Limiting

### Overview
Implements Redis-backed rate limiting with tier-based limits and automatic IP banning.

### Configuration
```php
// Rate limiting configuration
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_FREE_TIER', 100);         // 100 requests/hour
define('RATE_LIMIT_PREMIUM_TIER', 1000);     // 1000 requests/hour
define('RATE_LIMIT_ENTERPRISE_TIER', 10000); // 10000 requests/hour
```

### Security Features
- **Sliding Window**: 1-hour sliding windows prevent burst abuse
- **Violation Tracking**: Monitors repeated violations
- **Automatic Banning**: Bans IPs after 5 violations
- **Redis Storage**: High-performance rate limit storage
- **Forensic Logging**: Complete audit trail

### Testing
```bash
# Test rate limiting
./vendor/bin/phpunit tests/Security/RateLimitingTest.php
```

### Monitoring
Rate limiting violations are logged as security events with severity 'high'.
```

---

### 📊 **Security Performance Requirements**

#### **✅ Performance Benchmarks Must Be Maintained**

- **🔐 Authentication**: < 5ms overhead per request
- **⚡ Input Validation**: < 1ms overhead per field
- **🔒 CSRF Protection**: < 2ms overhead per form
- **📊 Rate Limiting**: < 3ms overhead per request  
- **📝 Security Logging**: < 1ms overhead per event
- **🏆 Total Security Overhead**: < 12ms per request

#### **✅ Load Testing Requirements**
```bash
# Performance testing commands
# Test authentication endpoint
ab -n 1000 -c 10 -H "Authorization: Bearer TOKEN" http://localhost:8000/api/auth/verify

# Test rate limiting
ab -n 200 -c 20 http://localhost:8000/api/cats

# Test CSRF protection
ab -n 500 -c 5 -p form-data.txt -H "Content-Type: application/x-www-form-urlencoded" http://localhost:8000/cats/create
```

---

### ❗ **Security Violation Consequences**

**🚫 Failure to follow security guidelines will result in:**

1. **Pull Request Rejection**: PRs failing security review are immediately rejected
2. **Contributor Education**: Required security training for repeat violations  
3. **Access Restriction**: Repeated violations may result in contribution restrictions
4. **Security Audit**: Major violations trigger full security audits

**✅ We provide security support:**
- Security mentoring for new contributors
- Security review assistance
- Best practices documentation
- Regular security training sessions

---

### 📏 **Security Resources**

- **Security Documentation**: [DOCUMENTATION.md - Security Section](DOCUMENTATION.md#security-model)
- **Security Setup Guide**: [INSTALL.md - Security Setup](INSTALL.md#security-setup)
- **Security Architecture**: [README.md - Security Features](README.md#security-features)
- **OWASP Guidelines**: [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- **PHP Security**: [PHP Security Guide](https://www.php.net/manual/en/security.php)

## 🧪 Testing Guidelines

### Writing Tests

We use PHPUnit for testing. All new features must include tests:

```php
<?php
declare(strict_types=1);

namespace Tests\Unit\Cat;

use PHPUnit\Framework\TestCase;
use Purrr\Cat\CatBehavior;
use InvalidArgumentException;

class CatBehaviorTest extends TestCase
{
    private CatBehavior $catBehavior;

    protected function setUp(): void
    {
        parent::setUp();
        $this->catBehavior = new CatBehavior();
    }

    /**
     * @test
     */
    public function it_returns_behavior_for_valid_personality_type(): void
    {
        $behavior = $this->catBehavior->getBehavior('playful');
        
        $this->assertIsArray($behavior);
        $this->assertArrayHasKey('energy_multiplier', $behavior);
        $this->assertArrayHasKey('preferred_activities', $behavior);
    }

    /**
     * @test
     */
    public function it_throws_exception_for_invalid_personality_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid personality type: invalid');
        
        $this->catBehavior->getBehavior('invalid');
    }

    /**
     * @test
     * @dataProvider validPersonalityTypesProvider
     */
    public function it_handles_all_valid_personality_types(string $personalityType): void
    {
        $behavior = $this->catBehavior->getBehavior($personalityType);
        
        $this->assertIsArray($behavior);
        $this->assertNotEmpty($behavior);
    }

    public function validPersonalityTypesProvider(): array
    {
        return [
            ['playful'],
            ['aloof'],
            ['curious'],
            ['lazy'],
            ['territorial'],
            ['social_butterfly']
        ];
    }
}
```

### Test Categories

- **Unit Tests**: Test individual classes and methods
- **Integration Tests**: Test component interactions
- **Feature Tests**: Test complete user workflows
- **API Tests**: Test API endpoints and responses
- **Security Tests**: Test security measures and vulnerabilities

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Unit/
./vendor/bin/phpunit tests/Integration/
./vendor/bin/phpunit tests/Feature/

# Run tests with coverage
./vendor/bin/phpunit --coverage-html coverage/

# Run specific test file
./vendor/bin/phpunit tests/Unit/Cat/CatBehaviorTest.php

# Run tests matching pattern
./vendor/bin/phpunit --filter="test_cat_feeding"
```

## 📚 Documentation Standards

### Code Documentation

```php
/**
 * Process cat feeding with comprehensive validation and side effects
 * 
 * This method handles the complete cat feeding workflow including:
 * - Food item validation and availability checking
 * - Cat hunger and energy level updates
 * - Experience point calculation and awarding
 * - Achievement progress tracking
 * - Quest progress updates
 * 
 * @param int $catId The unique identifier of the cat to feed
 * @param string $foodType The type of food item to use
 * @param int $quantity The number of food items to consume
 * @param array $options Additional options for feeding behavior
 * 
 * @return array Feeding result containing updated stats and rewards
 * 
 * @throws InvalidArgumentException If cat ID is invalid or not owned by user
 * @throws InsufficientResourcesException If user lacks required food items
 * @throws CatNotHungryException If cat is already at maximum hunger level
 * 
 * @see CatStats::updateHunger() For hunger calculation details
 * @see QuestManager::updateProgress() For quest progress tracking
 * 
 * @since 1.0.0
 * @version 1.2.0 Added support for special food effects
 * 
 * @example
 * ```php
 * $result = $catManager->feedCat(123, 'premium_salmon', 2, ['boost_happiness' => true]);
 * echo "Cat gained {$result['happiness_gained']} happiness points!";
 * ```
 */
public function feedCat(int $catId, string $foodType, int $quantity = 1, array $options = []): array
{
    // Implementation...
}
```

### README Updates

When adding new features, update relevant sections in README.md:

```markdown
### 🆕 New Feature: Advanced Cat Genetics

We've added a comprehensive genetics system that simulates real Mendelian inheritance:

- **Dominant/Recessive Traits**: Color, pattern, and size inheritance
- **Genetic Mutations**: Rare variations with special abilities  
- **Breed-Specific Traits**: Each breed has unique genetic markers
- **Breeding Predictions**: AI-powered offspring prediction system

#### Usage Example

```php
$genetics = new CatGenetics();
$offspring = $genetics->predictOffspring($motherCat, $fatherCat);
echo "Predicted coat color: {$offspring['coat_color']}";
```

See [Genetics Documentation](docs/genetics.md) for detailed information.

### 🌙 New Feature: Night Watch System

We've added a revolutionary nighttime cat protection system that transforms gaming into meaningful impact:

- **Guardian Cat Roles**: Scout, Guardian, Healer, and Alarm cats with specialized abilities
- **Protection Zones**: Build Cat Condos, Motion Sensors, Safe Havens, and Community Alerts
- **Real-time Threat Detection**: Weather-influenced bobcat activity monitoring
- **Stray Cat Rescue**: Find and save cats in danger during night patrols
- **Community Coordination**: Work with other players to create city-wide protection

#### CLI Usage Example

```bash
# Deploy cats for night patrol
purrr nightwatch deploy 1 3 5

# Check current status
purrr nightwatch status

# Create a protection zone
purrr nightwatch create-zone safe_haven "Home Base" "Central Park" 75
```

#### API Usage Example

```php
// Deploy night patrol via API
$result = deployNightPatrol($userId, [1, 2, 3], 'neighborhood');
if ($result['success']) {
    echo "Deployed {$result['deployed_cats']} guardian cats!";
}

// Create protection zone
$zone = createProtectionZone($userId, 'safe_haven', [
    'name' => 'Emergency Shelter',
    'location' => 'downtown',
    'radius' => 100
]);
```

See [Night Watch Documentation](NIGHT_WATCH_README.md) for complete feature overview.
```

## 📤 Submitting Changes

### Pull Request Process

1. **Ensure CI Passes**
   - All tests must pass
   - Code style checks must pass
   - No security vulnerabilities detected

2. **Update Documentation**
   - Update relevant README sections
   - Add/update API documentation
   - Include code examples for new features

3. **Create Meaningful Commits**
   Use conventional commit format:
   ```
   feat: add cat personality inheritance system
   fix: resolve SQL injection in cat search
   docs: update API documentation for breeding
   test: add integration tests for quest system
   refactor: optimize cat stats calculation
   ```

4. **Fill Out PR Template**
   ```markdown
   ## Description
   Brief description of changes made.

   ## Type of Change
   - [ ] Bug fix (non-breaking change which fixes an issue)
   - [ ] New feature (non-breaking change which adds functionality)
   - [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
   - [ ] Documentation update

   ## Testing
   - [ ] Unit tests pass
   - [ ] Integration tests pass
   - [ ] Manual testing completed

   ## Screenshots (if applicable)
   Add screenshots to help explain your changes.

   ## Checklist
   - [ ] My code follows the style guidelines
   - [ ] I have performed a self-review of my code
   - [ ] I have commented my code, particularly in hard-to-understand areas
   - [ ] I have made corresponding changes to the documentation
   - [ ] My changes generate no new warnings
   - [ ] I have added tests that prove my fix is effective or that my feature works
   ```

### Commit Message Guidelines

Follow the [Conventional Commits](https://conventionalcommits.org/) specification:

**Format:**
```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

**Types:**
- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Changes that do not affect the meaning of the code
- `refactor`: A code change that neither fixes a bug nor adds a feature
- `perf`: A code change that improves performance
- `test`: Adding missing tests or correcting existing tests
- `chore`: Changes to the build process or auxiliary tools

**Examples:**
```
feat(breeding): add genetic mutation system

Add support for rare genetic mutations in cat breeding:
- Implement mutation probability calculation
- Add special visual effects for mutated cats
- Include mutation tracking in breeding history

Closes #456

feat(nightwatch): implement bobcat threat detection system

Add real-time bobcat activity monitoring for Night Watch patrols:
- Weather-based activity calculation with seasonal patterns
- Threat level classification (low, medium, high, critical)
- Emergency alert system for community coordination
- Integration with protection zones for enhanced security

This enables meaningful cat protection simulation and community engagement.

Closes #789

fix(security): prevent XSS in cat name display

Properly escape HTML in cat name output to prevent cross-site scripting attacks.
All user-generated content now uses htmlspecialchars() for safe display.

BREAKING CHANGE: getCatName() now returns escaped HTML by default.
Use getCatName(false) to get raw name without escaping.
```

## 🔍 Review Process

### What We Look For

**Code Quality:**
- Clean, readable, and well-structured code
- Proper error handling and edge case coverage
- Performance considerations
- Security best practices

**Testing:**
- Adequate test coverage for new functionality
- Tests that cover both success and failure scenarios
- Integration tests for user-facing features

**Documentation:**
- Clear and comprehensive documentation
- Updated API documentation
- Code comments for complex logic

**User Experience:**
- Intuitive user interfaces
- Accessibility considerations
- Mobile responsiveness

### Review Timeline

- **Initial Review**: Within 48 hours
- **Follow-up Reviews**: Within 24 hours of updates
- **Final Approval**: 1-2 business days after approval from 2+ maintainers

### Addressing Review Feedback

1. **Be Responsive**: Address feedback promptly and professionally
2. **Ask Questions**: If feedback is unclear, ask for clarification
3. **Make Requested Changes**: Update code based on reviewer suggestions
4. **Re-request Review**: Once changes are made, request a new review

## 🏷️ Issue and PR Labels

We use labels to categorize and prioritize work:

**Priority:**
- `priority: high` - Critical bugs or important features
- `priority: medium` - Standard priority items
- `priority: low` - Nice-to-have improvements

**Type:**
- `type: bug` - Something isn't working
- `type: feature` - New feature or request
- `type: enhancement` - Improvement to existing feature
- `type: documentation` - Documentation improvements

**Status:**
- `status: needs-review` - Ready for review
- `status: in-progress` - Currently being worked on
- `status: blocked` - Waiting for something else

**Good First Issue:**
- `good first issue` - Suitable for newcomers

## 🚀 Advanced Features Development

Purrr.love v2.1.0 introduces revolutionary advanced features that require specialized knowledge and careful implementation. This section provides guidelines for contributing to these cutting-edge systems.

### Blockchain & NFT Guidelines

#### Overview
Our blockchain integration supports multiple networks (Ethereum, Solana, Polygon) and enables NFT minting, trading, and ownership verification of virtual cats.

#### Prerequisites
- Understanding of Web3 concepts and blockchain technology
- Experience with smart contract development (Solidity/Rust)
- Knowledge of IPFS and decentralized storage
- Familiarity with Web3.js/Ethers.js libraries

#### Development Standards

**Smart Contract Development:**
```solidity
// ✅ GOOD - Secure NFT contract with proper access controls
pragma solidity ^0.8.19;

import "@openzeppelin/contracts/token/ERC721/ERC721.sol";
import "@openzeppelin/contracts/access/Ownable.sol";
import "@openzeppelin/contracts/security/ReentrancyGuard.sol";

contract PurrrCatNFT is ERC721, Ownable, ReentrancyGuard {
    uint256 private _nextTokenId = 1;
    mapping(uint256 => string) private _tokenURIs;
    
    event CatMinted(uint256 indexed tokenId, address indexed to, string metadataURI);
    
    constructor() ERC721("PurrrCat", "PCAT") {}
    
    function mintCat(address to, string memory metadataURI) 
        public 
        onlyOwner 
        nonReentrant 
        returns (uint256) 
    {
        require(to != address(0), "Invalid recipient address");
        require(bytes(metadataURI).length > 0, "Metadata URI required");
        
        uint256 tokenId = _nextTokenId++;
        _safeMint(to, tokenId);
        _setTokenURI(tokenId, metadataURI);
        
        emit CatMinted(tokenId, to, metadataURI);
        return tokenId;
    }
}
```

**PHP Blockchain Integration:**
```php
// ✅ GOOD - Secure blockchain interaction with proper error handling
class SecureBlockchainManager {
    private $web3Provider;
    private $contractAddress;
    private $privateKey;
    
    public function mintCatNFT(int $catId, string $recipientAddress): array {
        try {
            // Validate inputs
            if (!$this->isValidAddress($recipientAddress)) {
                throw new InvalidArgumentException('Invalid recipient address');
            }
            
            $cat = $this->getCatById($catId);
            if (!$cat) {
                throw new NotFoundException('Cat not found');
            }
            
            // Generate metadata
            $metadata = $this->generateNFTMetadata($cat);
            $metadataURI = $this->uploadToIPFS($metadata);
            
            // Estimate gas
            $gasEstimate = $this->estimateGasForMinting($recipientAddress, $metadataURI);
            
            // Create and sign transaction
            $transaction = [
                'to' => $this->contractAddress,
                'data' => $this->encodeMintFunction($recipientAddress, $metadataURI),
                'gas' => $gasEstimate,
                'gasPrice' => $this->getOptimalGasPrice()
            ];
            
            $signedTx = $this->signTransaction($transaction, $this->privateKey);
            $txHash = $this->web3Provider->sendRawTransaction($signedTx);
            
            // Store NFT record
            $this->storeNFTRecord($catId, $txHash, $metadataURI, $recipientAddress);
            
            return [
                'success' => true,
                'transaction_hash' => $txHash,
                'metadata_uri' => $metadataURI,
                'estimated_gas' => $gasEstimate
            ];
            
        } catch (Exception $e) {
            $this->logBlockchainError('NFT_MINT_FAILED', [
                'cat_id' => $catId,
                'recipient' => $recipientAddress,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
```

#### Testing Requirements
- **Testnet Testing**: All blockchain features must be tested on testnets first
- **Gas Optimization**: Measure and optimize gas usage
- **Security Audits**: Smart contracts require security review
- **Integration Tests**: Test complete Web3 workflows

#### Documentation Requirements
- Contract deployment instructions
- Gas optimization strategies
- Security considerations
- Network configuration guides

### Machine Learning Guidelines

#### Overview
Our ML system analyzes cat images to predict personality traits, behavior patterns, and compatibility scores using TensorFlow and PyTorch models.

#### Prerequisites
- Python machine learning experience (TensorFlow/PyTorch)
- Computer vision knowledge (OpenCV, PIL)
- Understanding of neural networks and model training
- Experience with REST API development

#### Development Standards

**Model Development:**
```python
# ✅ GOOD - Robust ML model with proper validation
import tensorflow as tf
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report

class CatPersonalityModel:
    def __init__(self, model_path=None):
        self.model = None
        self.scaler = None
        self.label_encoder = None
        
        if model_path:
            self.load_model(model_path)
    
    def build_model(self, input_shape, num_classes):
        """Build CNN model for personality classification"""
        model = tf.keras.Sequential([
            tf.keras.layers.Conv2D(32, (3, 3), activation='relu', input_shape=input_shape),
            tf.keras.layers.BatchNormalization(),
            tf.keras.layers.MaxPooling2D(2, 2),
            
            tf.keras.layers.Conv2D(64, (3, 3), activation='relu'),
            tf.keras.layers.BatchNormalization(),
            tf.keras.layers.MaxPooling2D(2, 2),
            
            tf.keras.layers.Conv2D(128, (3, 3), activation='relu'),
            tf.keras.layers.BatchNormalization(),
            tf.keras.layers.MaxPooling2D(2, 2),
            
            tf.keras.layers.GlobalAveragePooling2D(),
            tf.keras.layers.Dropout(0.5),
            tf.keras.layers.Dense(256, activation='relu'),
            tf.keras.layers.BatchNormalization(),
            tf.keras.layers.Dropout(0.5),
            tf.keras.layers.Dense(num_classes, activation='softmax')
        ])
        
        model.compile(
            optimizer=tf.keras.optimizers.Adam(learning_rate=0.001),
            loss='categorical_crossentropy',
            metrics=['accuracy', 'precision', 'recall']
        )
        
        self.model = model
        return model
    
    def train(self, X_train, y_train, validation_split=0.2, epochs=50):
        """Train the model with proper validation"""
        # Data augmentation
        datagen = tf.keras.preprocessing.image.ImageDataGenerator(
            rotation_range=20,
            width_shift_range=0.2,
            height_shift_range=0.2,
            horizontal_flip=True,
            zoom_range=0.2,
            fill_mode='nearest'
        )
        
        # Callbacks
        callbacks = [
            tf.keras.callbacks.EarlyStopping(
                monitor='val_loss', patience=10, restore_best_weights=True
            ),
            tf.keras.callbacks.ReduceLROnPlateau(
                monitor='val_loss', factor=0.5, patience=5, min_lr=1e-7
            ),
            tf.keras.callbacks.ModelCheckpoint(
                'best_model.h5', save_best_only=True, monitor='val_accuracy'
            )
        ]
        
        # Train model
        history = self.model.fit(
            datagen.flow(X_train, y_train, batch_size=32),
            validation_split=validation_split,
            epochs=epochs,
            callbacks=callbacks,
            verbose=1
        )
        
        return history
    
    def predict_personality(self, image_array):
        """Predict personality with confidence scores"""
        if self.model is None:
            raise ValueError("Model not loaded or trained")
        
        # Preprocess image
        processed_image = self.preprocess_image(image_array)
        
        # Get prediction
        predictions = self.model.predict(processed_image)
        confidence_scores = predictions[0]
        
        # Get personality labels
        personality_labels = ['playful', 'aloof', 'curious', 'lazy', 'territorial', 'social_butterfly']
        
        results = {
            'primary_personality': personality_labels[np.argmax(confidence_scores)],
            'confidence': float(np.max(confidence_scores)),
            'all_scores': {label: float(score) for label, score in zip(personality_labels, confidence_scores)}
        }
        
        return results
```

**PHP ML Integration:**
```php
// ✅ GOOD - Robust ML service integration with caching and error handling
class MLPersonalityService {
    private string $serviceUrl;
    private int $timeout;
    private bool $cacheEnabled;
    private SecurityLogger $securityLogger;
    
    public function __construct() {
        $this->serviceUrl = getenv('ML_SERVICE_URL') ?: 'http://127.0.0.1:8088';
        $this->timeout = (int)(getenv('ML_TIMEOUT_MS') ?: 8000) / 1000;
        $this->cacheEnabled = getenv('ML_ENABLE_CACHE') === 'true';
        $this->securityLogger = new SecurityLogger();
    }
    
    public function analyzePersonality(int $catId, string $imageData): array {
        try {
            // Check cache first
            if ($this->cacheEnabled) {
                $cached = $this->getCachedAnalysis($catId);
                if ($cached) {
                    return $cached;
                }
            }
            
            // Validate image data
            if (empty($imageData) || !$this->isValidImageData($imageData)) {
                throw new InvalidArgumentException('Invalid image data provided');
            }
            
            // Prepare secure request
            $payload = [
                'image' => base64_encode($imageData),
                'cat_id' => $catId,
                'timestamp' => time(),
                'request_id' => $this->generateRequestId()
            ];
            
            // Make HTTP request with timeout and retry logic
            $response = $this->makeSecureRequest('/analyze', $payload);
            
            if (!$response['success']) {
                throw new MLAnalysisException('ML analysis failed: ' . $response['error']);
            }
            
            $analysis = $response['data'];
            
            // Validate response structure
            $this->validateAnalysisResponse($analysis);
            
            // Store results in database
            $this->storePersonalityAnalysis($catId, $analysis);
            
            // Cache results
            if ($this->cacheEnabled) {
                $this->cacheAnalysis($catId, $analysis, 3600); // 1 hour cache
            }
            
            // Log successful analysis
            $this->securityLogger->logSecurityEvent('ML_ANALYSIS_SUCCESS', [
                'cat_id' => $catId,
                'confidence' => $analysis['confidence'],
                'primary_personality' => $analysis['primary_personality']
            ]);
            
            return $analysis;
            
        } catch (Exception $e) {
            $this->securityLogger->logSecurityEvent('ML_ANALYSIS_ERROR', [
                'cat_id' => $catId,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ]);
            throw $e;
        }
    }
}
```

#### Testing Requirements
- **Model Validation**: Cross-validation with test datasets
- **Performance Testing**: Measure inference time and accuracy
- **Integration Testing**: Test PHP-Python communication
- **Load Testing**: Ensure service handles concurrent requests

### Webhook Development

#### Overview
Our webhook system provides real-time event notifications to external services with retry logic, signature verification, and comprehensive logging.

#### Prerequisites
- Understanding of HTTP protocols and REST APIs
- Knowledge of cryptographic signatures (HMAC)
- Experience with queue systems (Redis)
- Understanding of retry mechanisms and circuit breakers

#### Development Standards

**Webhook Implementation:**
```php
// ✅ GOOD - Secure webhook system with comprehensive error handling
class EnterpriseWebhookManager {
    private Redis $redis;
    private string $signingSecret;
    private int $maxRetries;
    private SecurityLogger $securityLogger;
    
    public function __construct() {
        $this->redis = getRedisConnection();
        $this->signingSecret = getenv('WEBHOOK_SIGNING_SECRET');
        $this->maxRetries = (int)(getenv('WEBHOOK_MAX_RETRIES') ?: 5);
        $this->securityLogger = new SecurityLogger();
    }
    
    public function registerWebhook(int $userId, string $url, array $events, ?string $secret = null): array {
        try {
            // Validate URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Invalid webhook URL provided');
            }
            
            // Security check - no internal URLs
            if ($this->isInternalURL($url)) {
                throw new SecurityException('Internal URLs not allowed for webhooks');
            }
            
            // Validate events
            $validEvents = $this->getValidEvents();
            foreach ($events as $event) {
                if (!in_array($event, $validEvents)) {
                    throw new InvalidArgumentException("Invalid event type: {$event}");
                }
            }
            
            // Test webhook endpoint
            $testResult = $this->testWebhookEndpoint($url);
            if (!$testResult['success']) {
                throw new WebhookTestException('Webhook endpoint test failed: ' . $testResult['error']);
            }
            
            $webhook = [
                'id' => $this->generateWebhookId(),
                'user_id' => $userId,
                'url' => $url,
                'events' => $events,
                'secret' => $secret ?: $this->generateSecret(),
                'status' => 'active',
                'created_at' => time(),
                'last_success_at' => null,
                'last_failure_at' => null,
                'failure_count' => 0,
                'rate_limit' => $this->calculateRateLimit($userId)
            ];
            
            // Store webhook
            $this->storeWebhook($webhook);
            
            // Log webhook registration
            $this->securityLogger->logSecurityEvent('WEBHOOK_REGISTERED', [
                'webhook_id' => $webhook['id'],
                'user_id' => $userId,
                'events' => $events,
                'url' => $this->sanitizeURL($url)
            ]);
            
            return $webhook;
            
        } catch (Exception $e) {
            $this->securityLogger->logSecurityEvent('WEBHOOK_REGISTRATION_ERROR', [
                'user_id' => $userId,
                'url' => $this->sanitizeURL($url),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function dispatchEvent(string $eventType, array $eventData, ?int $userId = null): array {
        try {
            // Validate event data
            $this->validateEventData($eventType, $eventData);
            
            $event = [
                'id' => $this->generateEventId(),
                'type' => $eventType,
                'data' => $eventData,
                'user_id' => $userId,
                'timestamp' => time(),
                'delivered' => false
            ];
            
            // Find matching webhooks
            $webhooks = $this->getMatchingWebhooks($eventType, $userId);
            
            $deliveryJobs = [];
            foreach ($webhooks as $webhook) {
                // Check rate limits
                if ($this->isWebhookRateLimited($webhook['id'])) {
                    continue;
                }
                
                // Queue webhook delivery
                $deliveryJob = [
                    'webhook_id' => $webhook['id'],
                    'event' => $event,
                    'attempt' => 1,
                    'scheduled_at' => time(),
                    'priority' => $this->calculatePriority($eventType)
                ];
                
                $this->queueWebhookDelivery($deliveryJob);
                $deliveryJobs[] = $deliveryJob;
            }
            
            // Store event for audit trail
            $this->storeWebhookEvent($event);
            
            // Log event dispatch
            $this->securityLogger->logSecurityEvent('WEBHOOK_EVENT_DISPATCHED', [
                'event_id' => $event['id'],
                'event_type' => $eventType,
                'webhooks_notified' => count($deliveryJobs),
                'user_id' => $userId
            ]);
            
            return [
                'event_id' => $event['id'],
                'webhooks_notified' => count($deliveryJobs),
                'delivery_jobs' => $deliveryJobs
            ];
            
        } catch (Exception $e) {
            $this->securityLogger->logSecurityEvent('WEBHOOK_DISPATCH_ERROR', [
                'event_type' => $eventType,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
```

#### Testing Requirements
- **Endpoint Testing**: Test webhook delivery to various endpoints
- **Retry Logic Testing**: Verify exponential backoff works correctly
- **Signature Testing**: Validate HMAC signature generation and verification
- **Performance Testing**: Test webhook delivery under load

### Lost Pet Finder Guidelines

#### Overview
The Lost Pet Finder is a comprehensive geospatial system for helping reunite lost pets with their owners, featuring location-based search, image recognition, and community coordination.

#### Prerequisites
- Understanding of geospatial databases (PostGIS)
- Experience with mapping APIs (Mapbox, Google Maps)
- Knowledge of image processing and recognition
- Understanding of notification systems (SMS, email)

#### Development Standards

**Geospatial Implementation:**
```php
// ✅ GOOD - Secure and efficient geospatial pet finder system
class SecureLostPetFinder {
    private PDO $pdo;
    private Redis $redis;
    private string $mapsProvider;
    private float $defaultRadius;
    private SecurityLogger $securityLogger;
    
    public function __construct() {
        $this->pdo = get_db();
        $this->redis = getRedisConnection();
        $this->mapsProvider = getenv('MAPS_PROVIDER') ?: 'mapbox';
        $this->defaultRadius = (float)(getenv('LOST_PET_FINDER_DEFAULT_RADIUS_KM') ?: 10);
        $this->securityLogger = new SecurityLogger();
    }
    
    public function reportLostPet(array $reportData): array {
        try {
            // Validate required fields
            $this->validateReportData($reportData);
            
            // Geocode location with caching
            $coordinates = $this->geocodeLocationSecure($reportData['last_seen_location']);
            
            // Process and validate uploaded images
            $imageUrls = [];
            if (!empty($reportData['images'])) {
                foreach ($reportData['images'] as $image) {
                    // Validate image
                    if (!$this->validateImage($image)) {
                        throw new InvalidImageException('Invalid image format or size');
                    }
                    
                    // Process and store securely
                    $imageUrl = $this->processAndStoreImage($image, 'lost_pets');
                    $imageUrls[] = $imageUrl;
                }
            }
            
            // Create lost pet report with geospatial data
            $stmt = $this->pdo->prepare("
                INSERT INTO lost_pets (
                    id, pet_name, pet_type, breed, color, size, age, 
                    description, distinctive_features, last_seen_location,
                    last_seen_coordinates, last_seen_date, contact_name,
                    contact_phone, contact_email, reward_amount, images,
                    status, reported_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ST_GeomFromText(?), ?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            
            $lostPetId = $this->generateSecureId();
            $geoPoint = "POINT({$coordinates['lng']} {$coordinates['lat']})";
            
            $stmt->execute([
                $lostPetId,
                $this->sanitizeInput($reportData['pet_name']),
                $this->validatePetType($reportData['pet_type']),
                $this->sanitizeInput($reportData['breed'] ?? ''),
                $this->sanitizeInput($reportData['color']),
                $this->validateSize($reportData['size'] ?? 'medium'),
                $this->sanitizeInput($reportData['age'] ?? ''),
                $this->sanitizeInput($reportData['description'] ?? ''),
                $this->sanitizeInput($reportData['distinctive_features'] ?? ''),
                $this->sanitizeInput($reportData['last_seen_location']),
                $geoPoint,
                $this->validateDate($reportData['last_seen_date']),
                $this->sanitizeInput($reportData['contact_name'] ?? ''),
                $this->validatePhone($reportData['contact_phone']),
                $this->validateEmail($reportData['contact_email']),
                $this->validateAmount($reportData['reward_amount'] ?? 0),
                json_encode($imageUrls)
            ]);
            
            // Send notifications to nearby users (asynchronously)
            $this->queueNearbyNotifications($lostPetId, $coordinates, $this->defaultRadius);
            
            // Create Facebook post if enabled and authorized
            if ($this->isFacebookIntegrationEnabled($reportData)) {
                $this->queueFacebookPost($lostPetId);
            }
            
            // Dispatch webhook event
            $webhookManager = new EnterpriseWebhookManager();
            $webhookManager->dispatchEvent('lost_pet.reported', [
                'lost_pet_id' => $lostPetId,
                'pet_name' => $reportData['pet_name'],
                'pet_type' => $reportData['pet_type'],
                'location' => $reportData['last_seen_location'],
                'coordinates' => $coordinates,
                'contact_phone' => $this->maskPhoneNumber($reportData['contact_phone']),
                'reward_amount' => $reportData['reward_amount'] ?? 0
            ]);
            
            // Log successful report
            $this->securityLogger->logSecurityEvent('LOST_PET_REPORTED', [
                'lost_pet_id' => $lostPetId,
                'pet_type' => $reportData['pet_type'],
                'location' => $reportData['last_seen_location'],
                'images_count' => count($imageUrls)
            ]);
            
            return [
                'success' => true,
                'lost_pet_id' => $lostPetId,
                'coordinates' => $coordinates,
                'nearby_users_notified' => $this->estimateNearbyUsers($coordinates, $this->defaultRadius),
                'facebook_post_scheduled' => $this->isFacebookIntegrationEnabled($reportData)
            ];
            
        } catch (Exception $e) {
            $this->securityLogger->logSecurityEvent('LOST_PET_REPORT_ERROR', [
                'error' => $e->getMessage(),
                'pet_name' => $reportData['pet_name'] ?? 'unknown',
                'location' => $reportData['last_seen_location'] ?? 'unknown'
            ]);
            throw $e;
        }
    }
    
    public function searchLostPets(array $searchCriteria): array {
        try {
            // Validate and sanitize search criteria
            $validatedCriteria = $this->validateSearchCriteria($searchCriteria);
            
            // Build secure query with geospatial search
            $sql = "
                SELECT 
                    id, pet_name, pet_type, breed, color, size,
                    description, last_seen_location, last_seen_date,
                    contact_name, contact_phone, reward_amount, images,
                    ST_X(last_seen_coordinates) as lng,
                    ST_Y(last_seen_coordinates) as lat,
                    ST_Distance_Sphere(last_seen_coordinates, ST_GeomFromText(?)) / 1000 as distance_km
                FROM lost_pets 
                WHERE status = 'active'
            ";
            
            $params = [];
            $geoPoint = null;
            
            // Add location-based search with caching
            if (!empty($validatedCriteria['location'])) {
                $coordinates = $this->geocodeLocationSecure($validatedCriteria['location']);
                $geoPoint = "POINT({$coordinates['lng']} {$coordinates['lat']})";
                $params[] = $geoPoint;
                
                $radius = $validatedCriteria['radius'] ?? $this->defaultRadius;
                $sql .= " AND ST_Distance_Sphere(last_seen_coordinates, ST_GeomFromText(?)) <= ? * 1000";
                $params[] = $geoPoint;
                $params[] = $radius;
            }
            
            // Add additional filters
            $sql = $this->addSearchFilters($sql, $params, $validatedCriteria);
            
            // Add ordering
            if ($geoPoint) {
                $sql .= " ORDER BY distance_km ASC, last_seen_date DESC";
            } else {
                $sql .= " ORDER BY last_seen_date DESC";
            }
            
            // Add pagination
            $limit = min((int)($validatedCriteria['limit'] ?? 50), 100);
            $offset = (int)($validatedCriteria['offset'] ?? 0);
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
            
            // If no location provided, add dummy parameter
            if (!$geoPoint) {
                array_unshift($params, 'POINT(0 0)');
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll();
            
            // Process and enrich results
            foreach ($results as &$result) {
                $result['images'] = json_decode($result['images'], true) ?: [];
                $result['sightings_count'] = $this->countSightings($result['id']);
                $result['days_missing'] = $this->calculateDaysMissing($result['last_seen_date']);
                
                // Mask sensitive information
                $result['contact_phone'] = $this->maskPhoneNumber($result['contact_phone']);
            }
            
            // Log search query
            $this->securityLogger->logSecurityEvent('LOST_PET_SEARCH', [
                'criteria' => $validatedCriteria,
                'results_count' => count($results),
                'search_location' => $validatedCriteria['location'] ?? null
            ]);
            
            return [
                'results' => $results,
                'total_count' => count($results),
                'search_criteria' => $validatedCriteria,
                'search_radius_km' => $validatedCriteria['radius'] ?? $this->defaultRadius
            ];
            
        } catch (Exception $e) {
            $this->securityLogger->logSecurityEvent('LOST_PET_SEARCH_ERROR', [
                'error' => $e->getMessage(),
                'criteria' => $searchCriteria
            ]);
            throw $e;
        }
    }
}
```

#### Testing Requirements
- **Geospatial Testing**: Test coordinate accuracy and distance calculations
- **Image Testing**: Validate image upload, processing, and storage
- **Notification Testing**: Test SMS, email, and push notifications
- **Performance Testing**: Test search performance with large datasets

## 🌟 Recognition

Contributors are recognized in several ways:

- **Contributors List**: Added to GitHub contributors
- **Release Notes**: Mentioned in changelog for significant contributions
- **Hall of Fame**: Featured on project website (coming soon)
- **Swag**: Special contributor merchandise (for major contributors)

## 🏆 Contributor Levels

### 🌱 New Contributor
- Made 1-2 merged pull requests
- Fixed bugs or improved documentation

### 🌿 Regular Contributor  
- Made 5+ merged pull requests
- Contributed features or significant improvements
- Helped with issue triage and community support

### 🌳 Core Contributor
- Made 20+ merged pull requests
- Led major feature development
- Mentored other contributors
- Participated in project planning

### 🔥 Maintainer
- Trusted with repository access
- Reviews and merges pull requests
- Helps guide project direction
- Represents project in community

## 📞 Getting Help

**Questions about contributing?**
- 💬 [GitHub Discussions](https://github.com/straticus1/purrr.love/discussions)
- 📧 Email: [contribute@purrr.love](mailto:contribute@purrr.love)
- 📱 Discord: [Join our community](https://discord.gg/purrr-love)

**Development Help:**
- 📖 [Technical Documentation](DOCUMENTATION.md)
- 🚀 [Installation Guide](INSTALL.md)
- 🐛 [Debugging Guide](docs/debugging.md)

## 📜 Additional Resources

- [PHP Standards Recommendations (PSR)](https://www.php-fig.org/psr/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Git Best Practices](https://deepsource.io/blog/git-best-practices/)
- [Writing Great Commit Messages](https://chris.beams.io/posts/git-commit/)

---

**Thank you for contributing to Purrr.love! 🐱❤️**

*Together, we're building the most amazing cat gaming platform ever created!*

---

*Last updated: September 3, 2025*

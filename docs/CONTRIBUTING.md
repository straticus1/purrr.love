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

### Metaverse & VR Guidelines

#### Overview
Our Metaverse & VR system provides immersive virtual worlds where cats can interact, play, and explore in 3D environments using WebGL, Three.js, and WebVR technologies.

#### Prerequisites
- Experience with WebGL and 3D graphics programming
- Knowledge of Three.js or similar 3D libraries
- Understanding of WebVR/WebXR APIs
- Familiarity with spatial interaction design
- Experience with real-time multiplayer systems

#### Development Standards

**VR World Creation:**
```php
// ✅ GOOD - Secure VR world management with validation
class SecureMetaverseManager {
    private SecurityLogger $securityLogger;
    private array $validWorldTypes;
    private int $maxPlayersPerWorld;
    
    public function __construct() {
        $this->securityLogger = new SecurityLogger();
        $this->validWorldTypes = ['social', 'adventure', 'training', 'competition', 'relaxation', 'exploration', 'custom'];
        $this->maxPlayersPerWorld = 100;
    }
    
    public function createVirtualWorld(int $userId, array $worldData): array {
        try {
            // Validate user permissions
            if (!$this->canCreateWorld($userId)) {
                throw new UnauthorizedException('Insufficient permissions to create worlds');
            }
            
            // Validate world data
            $validatedData = $this->validateWorldData($worldData);
            
            // Check world limits
            $userWorldCount = $this->getUserWorldCount($userId);
            $maxWorlds = $this->getMaxWorldsForUser($userId);
            
            if ($userWorldCount >= $maxWorlds) {
                throw new LimitExceededException("Maximum world limit reached: {$maxWorlds}");
            }
            
            // Generate secure world ID
            $worldId = $this->generateSecureWorldId();
            
            $world = [
                'id' => $worldId,
                'creator_id' => $userId,
                'name' => $validatedData['name'],
                'type' => $validatedData['type'],
                'description' => $validatedData['description'],
                'max_players' => min($validatedData['max_players'], $this->maxPlayersPerWorld),
                'is_public' => $validatedData['is_public'] ?? true,
                'world_settings' => $this->sanitizeWorldSettings($validatedData['settings'] ?? []),
                'created_at' => time(),
                'status' => 'active'
            ];
            
            // Store world in database
            $this->storeWorld($world);
            
            // Initialize world environment
            $this->initializeWorldEnvironment($worldId, $validatedData);
            
            // Log world creation
            $this->securityLogger->logSecurityEvent('VR_WORLD_CREATED', [
                'world_id' => $worldId,
                'creator_id' => $userId,
                'world_type' => $validatedData['type'],
                'max_players' => $world['max_players']
            ]);
            
            return [
                'success' => true,
                'world_id' => $worldId,
                'world_data' => $world,
                'initialization_status' => 'complete'
            ];
            
        } catch (Exception $e) {
            $this->securityLogger->logSecurityEvent('VR_WORLD_CREATION_ERROR', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'world_name' => $worldData['name'] ?? 'unknown'
            ]);
            throw $e;
        }
    }
    
    public function joinVirtualWorld(int $userId, string $worldId, int $catId): array {
        try {
            // Validate world exists and is accessible
            $world = $this->getWorldById($worldId);
            if (!$world) {
                throw new NotFoundException('Virtual world not found');
            }
            
            // Check if world is at capacity
            $currentPlayers = $this->getCurrentPlayerCount($worldId);
            if ($currentPlayers >= $world['max_players']) {
                throw new CapacityException('World is at maximum capacity');
            }
            
            // Validate cat ownership
            if (!$this->userOwnsCat($userId, $catId)) {
                throw new UnauthorizedException('Cat access denied');
            }
            
            // Check if cat is already in a world
            $currentWorld = $this->getCatCurrentWorld($catId);
            if ($currentWorld) {
                $this->leaveCatFromWorld($catId, $currentWorld['world_id']);
            }
            
            // Create world session
            $session = [
                'world_id' => $worldId,
                'user_id' => $userId,
                'cat_id' => $catId,
                'joined_at' => time(),
                'position' => $this->getWorldSpawnPosition($worldId),
                'status' => 'active'
            ];
            
            // Store session
            $this->storeWorldSession($session);
            
            // Update world player count
            $this->incrementWorldPlayerCount($worldId);
            
            // Notify other players
            $this->broadcastPlayerJoin($worldId, $session);
            
            // Log world join
            $this->securityLogger->logSecurityEvent('VR_WORLD_JOINED', [
                'world_id' => $worldId,
                'user_id' => $userId,
                'cat_id' => $catId,
                'current_players' => $currentPlayers + 1
            ]);
            
            return [
                'success' => true,
                'session_data' => $session,
                'world_data' => $world,
                'spawn_position' => $session['position']
            ];
            
        } catch (Exception $e) {
            $this->securityLogger->logSecurityEvent('VR_WORLD_JOIN_ERROR', [
                'world_id' => $worldId,
                'user_id' => $userId,
                'cat_id' => $catId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
```

**JavaScript WebVR Integration:**
```javascript
// ✅ GOOD - Secure WebVR implementation with proper error handling
class PurrrVRManager {
    constructor(apiUrl, authToken) {
        this.apiUrl = apiUrl;
        this.authToken = authToken;
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.vrButton = null;
        this.controllers = [];
        this.catModels = new Map();
        this.worldData = null;
        this.websocket = null;
        
        this.initializeVR();
    }
    
    async initializeVR() {
        try {
            // Check VR support
            if (!navigator.xr) {
                console.warn('WebXR not supported, falling back to standard 3D mode');
                this.initializeStandard3D();
                return;
            }
            
            // Initialize Three.js scene
            this.scene = new THREE.Scene();
            this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            
            this.renderer = new THREE.WebGLRenderer({ antialias: true });
            this.renderer.setSize(window.innerWidth, window.innerHeight);
            this.renderer.xr.enabled = true;
            
            document.body.appendChild(this.renderer.domElement);
            
            // Add VR button
            this.vrButton = VRButton.createButton(this.renderer);
            document.body.appendChild(this.vrButton);
            
            // Initialize controllers
            this.initializeControllers();
            
            // Set up lighting
            this.setupLighting();
            
            // Start render loop
            this.renderer.setAnimationLoop(this.render.bind(this));
            
        } catch (error) {
            console.error('VR initialization failed:', error);
            this.handleVRError(error);
        }
    }
    
    async joinWorld(worldId, catId) {
        try {
            // Request world access
            const response = await fetch(`${this.apiUrl}/api/v1/metaverse/join`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.authToken}`
                },
                body: JSON.stringify({
                    world_id: worldId,
                    cat_id: catId
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to join world');
            }
            
            this.worldData = data.world_data;
            
            // Load world environment
            await this.loadWorldEnvironment(this.worldData);
            
            // Position user cat
            await this.spawnUserCat(catId, data.spawn_position);
            
            // Connect to multiplayer WebSocket
            this.connectMultiplayer(worldId);
            
            // Start world simulation
            this.startWorldSimulation();
            
            return data;
            
        } catch (error) {
            console.error('Failed to join world:', error);
            throw error;
        }
    }
    
    async loadWorldEnvironment(worldData) {
        try {
            // Clear existing world
            this.clearScene();
            
            // Load world geometry
            const loader = new THREE.GLTFLoader();
            const worldModel = await new Promise((resolve, reject) => {
                loader.load(
                    `/api/v1/metaverse/worlds/${worldData.id}/model`,
                    resolve,
                    null,
                    reject
                );
            });
            
            this.scene.add(worldModel.scene);
            
            // Set up world physics
            this.setupWorldPhysics(worldData);
            
            // Load ambient sounds
            this.loadAmbientSounds(worldData);
            
            // Set up interactive objects
            this.setupInteractiveObjects(worldData);
            
        } catch (error) {
            console.error('Failed to load world environment:', error);
            throw error;
        }
    }
    
    async spawnUserCat(catId, position) {
        try {
            // Load cat model
            const catModel = await this.loadCatModel(catId);
            
            // Position cat
            catModel.position.set(position.x, position.y, position.z);
            
            // Add to scene
            this.scene.add(catModel);
            
            // Store reference
            this.catModels.set(catId, catModel);
            
            // Set up cat animations
            this.setupCatAnimations(catModel);
            
            // Enable cat interactions
            this.enableCatInteractions(catModel);
            
        } catch (error) {
            console.error('Failed to spawn cat:', error);
            throw error;
        }
    }
    
    connectMultiplayer(worldId) {
        try {
            const wsUrl = `wss://ws.purrr.love/metaverse/${worldId}`;
            this.websocket = new WebSocket(wsUrl);
            
            this.websocket.onopen = () => {
                console.log('Connected to multiplayer server');
                
                // Authenticate
                this.websocket.send(JSON.stringify({
                    type: 'auth',
                    token: this.authToken
                }));
            };
            
            this.websocket.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleMultiplayerMessage(data);
            };
            
            this.websocket.onerror = (error) => {
                console.error('WebSocket error:', error);
            };
            
            this.websocket.onclose = () => {
                console.log('Disconnected from multiplayer server');
                // Attempt reconnection
                setTimeout(() => this.connectMultiplayer(worldId), 5000);
            };
            
        } catch (error) {
            console.error('Failed to connect to multiplayer:', error);
        }
    }
    
    handleMultiplayerMessage(data) {
        switch (data.type) {
            case 'player_joined':
                this.spawnOtherPlayerCat(data.cat_id, data.position);
                break;
                
            case 'player_left':
                this.removeOtherPlayerCat(data.cat_id);
                break;
                
            case 'player_moved':
                this.updateOtherPlayerPosition(data.cat_id, data.position, data.rotation);
                break;
                
            case 'interaction':
                this.handlePlayerInteraction(data);
                break;
                
            case 'world_event':
                this.handleWorldEvent(data);
                break;
        }
    }
}
```

#### Testing Requirements
- **VR Compatibility Testing**: Test across different VR headsets and devices
- **Performance Testing**: Maintain 90fps for VR (60fps minimum for standard 3D)
- **Multiplayer Testing**: Test concurrent user interactions and synchronization
- **Accessibility Testing**: Ensure non-VR users can participate

#### Documentation Requirements
- VR setup instructions for different headsets
- World creation and customization guides
- Multiplayer interaction patterns
- Performance optimization strategies

### Night Watch System Guidelines

#### Overview
The Night Watch System is a revolutionary community-based cat protection platform that gamifies real-world pet safety. Players deploy guardian cats on virtual night patrols, create protection zones, and coordinate community responses to keep stray cats safe from threats like bobcats.

#### Prerequisites
- Understanding of real-time gaming mechanics
- Knowledge of geolocation and mapping systems
- Experience with WebSocket/real-time communication
- Familiarity with community coordination features
- Understanding of gamification principles

#### Development Standards

**Night Watch Patrol System:**
```php
// ✅ GOOD - Secure night watch patrol management
class SecureNightWatchManager {
    private SecurityLogger $securityLogger;
    private array $validGuardianRoles;
    private float $maxPatrolRadius;
    private int $maxCatsPerPatrol;
    
    public function __construct() {
        $this->securityLogger = new SecurityLogger();
        $this->validGuardianRoles = ['scout', 'guardian', 'healer', 'alarm'];
        $this->maxPatrolRadius = 5.0; // 5km maximum patrol radius
        $this->maxCatsPerPatrol = 8;
    }
    
    public function deployNightPatrol(int $userId, array $catIds, string $patrolArea): array {
        try {
            // Validate user permissions
            if (!$this->canDeployPatrol($userId)) {
                throw new UnauthorizedException('Insufficient permissions for night watch deployment');
            }
            
            // Validate patrol parameters
            if (count($catIds) > $this->maxCatsPerPatrol) {
                throw new ValidationException("Maximum {$this->maxCatsPerPatrol} cats allowed per patrol");
            }
            
            // Validate cat ownership and availability
            $availableCats = [];
            foreach ($catIds as $catId) {
                $cat = $this->validateCatForPatrol($userId, $catId);
                if (!$cat) {
                    throw new ValidationException("Cat {$catId} is not available for patrol");
                }
                $availableCats[] = $cat;
            }
            
            // Calculate threat level for patrol area
            $threatLevel = $this->calculateThreatLevel($patrolArea, $this->getCurrentWeatherConditions());
            
            // Assign guardian roles based on cat personalities and skills
            $deployedCats = [];
            foreach ($availableCats as $cat) {
                $guardianRole = $this->assignGuardianRole($cat, $threatLevel);
                $position = $this->calculatePatrolPosition($patrolArea, $guardianRole);
                
                $deployedCat = [
                    'cat_id' => $cat['id'],
                    'role' => $guardianRole,
                    'position' => $position,
                    'energy_level' => $cat['energy'],
                    'patrol_effectiveness' => $this->calculateEffectiveness($cat, $guardianRole),
                    'deployed_at' => time()
                ];
                
                // Update cat status
                $this->updateCatPatrolStatus($cat['id'], 'active_patrol', $guardianRole);
                
                $deployedCats[] = $deployedCat;
            }
            
            // Create patrol record
            $patrolId = $this->generateSecurePatrolId();
            $patrol = [
                'id' => $patrolId,
                'user_id' => $userId,
                'patrol_area' => $patrolArea,
                'threat_level' => $threatLevel,
                'deployed_cats' => $deployedCats,
                'status' => 'active',
                'started_at' => time(),
                'estimated_duration' => $this->calculatePatrolDuration($threatLevel),
                'protection_coverage' => $this->calculateCoverageArea($deployedCats)
            ];
            
            // Store patrol data
            $this->storeNightPatrol($patrol);
            
            // Initialize real-time patrol monitoring
            $this->startPatrolMonitoring($patrolId);
            
            // Notify community of new patrol
            $this->broadcastPatrolDeployment($patrolArea, $patrol);
            
            // Log patrol deployment
            $this->securityLogger->logSecurityEvent('NIGHT_PATROL_DEPLOYED', [
                'patrol_id' => $patrolId,
                'user_id' => $userId,
                'cats_deployed' => count($deployedCats),
                'patrol_area' => $patrolArea,
                'threat_level' => $threatLevel
            ]);
            
            return [
                'success' => true,
                'patrol_id' => $patrolId,
                'message' => 'Night patrol deployed successfully!',
                'deployed_cats' => $deployedCats,
                'estimated_duration' => $patrol['estimated_duration'],
                'protection_coverage' => $patrol['protection_coverage']
            ];
            
        } catch (Exception $e) {
            $this->securityLogger->logSecurityEvent('NIGHT_PATROL_DEPLOYMENT_ERROR', [
                'user_id' => $userId,
                'cat_ids' => $catIds,
                'patrol_area' => $patrolArea,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function createProtectionZone(int $userId, array $zoneData): array {
        try {
            // Validate zone data
            $validatedData = $this->validateZoneData($zoneData);
            
            // Check zone limits for user
            $userZoneCount = $this->getUserZoneCount($userId);
            $maxZones = $this->getMaxZonesForUser($userId);
            
            if ($userZoneCount >= $maxZones) {
                throw new LimitExceededException("Maximum zone limit reached: {$maxZones}");
            }
            
            // Validate zone placement (no overlap with existing zones)
            if ($this->hasZoneOverlap($validatedData['location'], $validatedData['radius'])) {
                throw new ValidationException('Zone overlaps with existing protection zones');
            }
            
            // Calculate zone cost and deduct coins
            $zoneCost = $this->calculateZoneCost($validatedData['zone_type'], $validatedData['radius']);
            if (!$this->deductUserCoins($userId, $zoneCost)) {
                throw new InsufficientFundsException("Insufficient coins: {$zoneCost} required");
            }
            
            // Create protection zone
            $zoneId = $this->generateSecureZoneId();
            $zone = [
                'id' => $zoneId,
                'creator_id' => $userId,
                'name' => $validatedData['name'],
                'zone_type' => $validatedData['zone_type'],
                'location' => $validatedData['location'],
                'coordinates' => $this->geocodeLocation($validatedData['location']),
                'radius' => $validatedData['radius'],
                'protection_level' => $this->calculateProtectionLevel($validatedData['zone_type']),
                'cost' => $zoneCost,
                'status' => 'active',
                'created_at' => time(),
                'effectiveness' => $this->calculateZoneEffectiveness($validatedData)
            ];
            
            // Store zone data
            $this->storeProtectionZone($zone);
            
            // Update community protection coverage
            $this->updateCommunityProtection($validatedData['location'], $zone['protection_level']);
            
            // Award achievement if applicable
            $this->checkZoneAchievements($userId, $userZoneCount + 1);
            
            // Log zone creation
            $this->securityLogger->logSecurityEvent('PROTECTION_ZONE_CREATED', [
                'zone_id' => $zoneId,
                'creator_id' => $userId,
                'zone_type' => $validatedData['zone_type'],
                'location' => $validatedData['location'],
                'cost' => $zoneCost
            ]);
            
            return [
                'success' => true,
                'zone_id' => $zoneId,
                'message' => 'Protection zone created successfully!',
                'zone_data' => $zone,
                'community_impact' => $this->calculateCommunityImpact($zone)
            ];
            
        } catch (Exception $e) {
            $this->securityLogger->logSecurityEvent('PROTECTION_ZONE_CREATION_ERROR', [
                'user_id' => $userId,
                'zone_data' => $zoneData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function processPatrolEvent(string $patrolId, string $eventType, array $eventData): array {
        try {
            $patrol = $this->getActivePatrol($patrolId);
            if (!$patrol) {
                throw new NotFoundException('Active patrol not found');
            }
            
            $eventResult = [];
            
            switch ($eventType) {
                case 'stray_cat_encounter':
                    $eventResult = $this->handleStrayCatEncounter($patrol, $eventData);
                    break;
                    
                case 'bobcat_threat':
                    $eventResult = $this->handleBobcatThreat($patrol, $eventData);
                    break;
                    
                case 'weather_change':
                    $eventResult = $this->handleWeatherChange($patrol, $eventData);
                    break;
                    
                case 'community_alert':
                    $eventResult = $this->handleCommunityAlert($patrol, $eventData);
                    break;
                    
                case 'safe_rescue':
                    $eventResult = $this->handleSafeRescue($patrol, $eventData);
                    break;
                    
                default:
                    throw new InvalidArgumentException("Unknown event type: {$eventType}");
            }
            
            // Update patrol status and log event
            $this->logPatrolEvent($patrolId, $eventType, $eventData, $eventResult);
            
            // Award experience and coins based on event outcome
            if ($eventResult['success']) {
                $this->awardPatrolRewards($patrol['user_id'], $eventType, $eventResult);
            }
            
            // Check for patrol completion
            if ($this->isPatrolComplete($patrol, $eventResult)) {
                $completionResult = $this->completePatrol($patrolId);
                $eventResult['patrol_completed'] = $completionResult;
            }
            
            return $eventResult;
            
        } catch (Exception $e) {
            $this->securityLogger->logSecurityEvent('NIGHT_PATROL_EVENT_ERROR', [
                'patrol_id' => $patrolId,
                'event_type' => $eventType,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
```

**Real-time Community Coordination:**
```javascript
// ✅ GOOD - Real-time night watch coordination system
class NightWatchCoordinator {
    constructor(apiUrl, authToken, userId) {
        this.apiUrl = apiUrl;
        this.authToken = authToken;
        this.userId = userId;
        this.websocket = null;
        this.activePatrols = new Map();
        this.protectionZones = new Map();
        this.communityAlerts = new Map();
        this.threatLevel = 'low';
        
        this.initializeCoordination();
    }
    
    async initializeCoordination() {
        try {
            // Connect to Night Watch WebSocket
            this.connectNightWatchSocket();
            
            // Load user's active patrols
            await this.loadActivePatrols();
            
            // Load nearby protection zones
            await this.loadNearbyProtectionZones();
            
            // Subscribe to community alerts
            this.subscribeToAlerts();
            
            // Start threat level monitoring
            this.startThreatMonitoring();
            
        } catch (error) {
            console.error('Failed to initialize Night Watch coordination:', error);
        }
    }
    
    connectNightWatchSocket() {
        const wsUrl = `wss://ws.purrr.love/night-watch/${this.userId}`;
        this.websocket = new WebSocket(wsUrl);
        
        this.websocket.onopen = () => {
            console.log('Connected to Night Watch coordination server');
            
            // Authenticate connection
            this.websocket.send(JSON.stringify({
                type: 'auth',
                token: this.authToken,
                user_id: this.userId
            }));
        };
        
        this.websocket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleCoordinationMessage(data);
        };
        
        this.websocket.onerror = (error) => {
            console.error('Night Watch WebSocket error:', error);
        };
        
        this.websocket.onclose = () => {
            console.log('Disconnected from Night Watch coordination server');
            // Attempt reconnection
            setTimeout(() => this.connectNightWatchSocket(), 5000);
        };
    }
    
    handleCoordinationMessage(data) {
        switch (data.type) {
            case 'patrol_event':
                this.handlePatrolEvent(data);
                break;
                
            case 'community_alert':
                this.handleCommunityAlert(data);
                break;
                
            case 'threat_level_change':
                this.handleThreatLevelChange(data);
                break;
                
            case 'stray_cat_spotted':
                this.handleStrayCatSpotted(data);
                break;
                
            case 'bobcat_warning':
                this.handleBobcatWarning(data);
                break;
                
            case 'rescue_success':
                this.handleRescueSuccess(data);
                break;
                
            case 'patrol_coordination':
                this.handlePatrolCoordination(data);
                break;
        }
    }
    
    async deployPatrol(catIds, patrolArea) {
        try {
            const response = await fetch(`${this.apiUrl}/api/v1/night-watch/deploy`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.authToken}`
                },
                body: JSON.stringify({
                    cat_ids: catIds,
                    patrol_area: patrolArea,
                    coordination_enabled: true
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Store active patrol
                this.activePatrols.set(data.patrol_id, data);
                
                // Start patrol monitoring
                this.monitorPatrol(data.patrol_id);
                
                // Notify UI
                this.notifyUI('patrol_deployed', data);
                
                return data;
            } else {
                throw new Error(data.message || 'Failed to deploy patrol');
            }
            
        } catch (error) {
            console.error('Failed to deploy night patrol:', error);
            throw error;
        }
    }
    
    async createProtectionZone(zoneType, name, location, radius) {
        try {
            const response = await fetch(`${this.apiUrl}/api/v1/night-watch/zones`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.authToken}`
                },
                body: JSON.stringify({
                    zone_type: zoneType,
                    name: name,
                    location: location,
                    radius: radius
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Store protection zone
                this.protectionZones.set(data.zone_id, data.zone_data);
                
                // Update community protection map
                this.updateProtectionMap(data.zone_data);
                
                // Notify UI
                this.notifyUI('zone_created', data);
                
                return data;
            } else {
                throw new Error(data.message || 'Failed to create protection zone');
            }
            
        } catch (error) {
            console.error('Failed to create protection zone:', error);
            throw error;
        }
    }
    
    handlePatrolEvent(eventData) {
        const patrol = this.activePatrols.get(eventData.patrol_id);
        if (!patrol) {
            return;
        }
        
        // Update patrol status
        patrol.last_event = eventData;
        patrol.updated_at = Date.now();
        
        // Handle specific event types
        switch (eventData.event_type) {
            case 'stray_cat_encounter':
                this.handleStrayCatEvent(patrol, eventData);
                break;
                
            case 'bobcat_threat':
                this.handleBobcatEvent(patrol, eventData);
                break;
                
            case 'rescue_opportunity':
                this.handleRescueEvent(patrol, eventData);
                break;
        }
        
        // Notify UI of patrol event
        this.notifyUI('patrol_event', eventData);
    }
    
    handleCommunityAlert(alertData) {
        // Store community alert
        this.communityAlerts.set(alertData.alert_id, alertData);
        
        // Check if any of user's patrols can respond
        for (const [patrolId, patrol] of this.activePatrols) {
            if (this.canPatrolRespond(patrol, alertData)) {
                this.suggestPatrolResponse(patrol, alertData);
            }
        }
        
        // Notify UI of community alert
        this.notifyUI('community_alert', alertData);
    }
    
    async coordinateEmergencyResponse(alertId, responseType) {
        try {
            const response = await fetch(`${this.apiUrl}/api/v1/night-watch/emergency-response`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.authToken}`
                },
                body: JSON.stringify({
                    alert_id: alertId,
                    response_type: responseType,
                    available_patrols: Array.from(this.activePatrols.keys())
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update patrol assignments
                if (data.patrol_assignments) {
                    for (const assignment of data.patrol_assignments) {
                        this.updatePatrolAssignment(assignment.patrol_id, assignment.mission);
                    }
                }
                
                return data;
            } else {
                throw new Error(data.message || 'Emergency response coordination failed');
            }
            
        } catch (error) {
            console.error('Failed to coordinate emergency response:', error);
            throw error;
        }
    }
}
```

#### Testing Requirements
- **Real-time Testing**: Test WebSocket connections and real-time event handling
- **Community Testing**: Test multi-user coordination and alert systems
- **Geolocation Testing**: Validate location-based patrol and zone mechanics
- **Performance Testing**: Test system performance with multiple active patrols

#### Documentation Requirements
- Guardian role explanations and optimal deployment strategies
- Protection zone types and their effectiveness calculations
- Community coordination protocols and emergency response procedures
- Threat level calculation algorithms and weather impact factors

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

### CLI Administrative Commands Guidelines

#### Overview
The Purrr.love CLI tool (`cli/purrr`) includes comprehensive administrative commands for system management, database operations, user support, and advanced feature management. These commands require special authentication keys for security.

#### Prerequisites
- Understanding of system administration and database management
- Access to appropriate administrative keys
- Knowledge of CLI tool development patterns
- Experience with secure command authentication

#### Authentication System

**Key Types:**
- **Admin Key**: Required for user management and system setup operations
- **Database Key**: Required for database repair, backup, and migration operations  
- **System Key**: Required for system health checks, cache management, and service control
- **Support Key**: Required for viewing support tickets and user assistance operations

**Setting Keys:**
```bash
# Set administrative keys
purrr keys set admin <your_admin_key>
purrr keys set database <your_database_key>
purrr keys set system <your_system_key>
purrr keys set support <your_support_key>

# View current key status
purrr keys show
```

#### Development Standards

**Secure Command Implementation:**
```php
// ✅ GOOD - Secure administrative command with proper authentication
class SecureAdminCommands {
    private SecurityLogger $securityLogger;
    private array $config;
    
    public function __construct() {
        $this->securityLogger = new SecurityLogger();
        $this->loadConfiguration();
    }
    
    private function commandAdmin($args) {
        // Require admin authentication
        if (!$this->requireAdminKey()) {
            return;
        }

        if (empty($args)) {
            $this->showAdminHelp();
            return;
        }

        $subcommand = array_shift($args);
        switch ($subcommand) {
            case 'status':
                $this->adminStatus($args);
                break;
            case 'users':
                $this->adminUsers($args);
                break;
            case 'keys':
                $this->adminKeys($args);
                break;
            case 'logs':
                $this->adminLogs($args);
                break;
            default:
                echo Colors::RED . "❌ Unknown admin command: $subcommand" . Colors::NC . "\n";
                $this->showAdminHelp();
                break;
        }
    }
    
    private function requireAdminKey(): bool {
        if (!$this->hasAdminKey()) {
            echo Colors::RED . "❌ Error: Admin key required for this operation" . Colors::NC . "\n";
            echo "   Use: purrr keys set admin <admin_key>\n";
            
            // Log unauthorized access attempt
            $this->securityLogger->logSecurityEvent('ADMIN_ACCESS_DENIED', [
                'command' => 'admin',
                'ip' => $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI'
            ]);
            
            return false;
        }
        return true;
    }
    
    private function adminStatus($args) {
        try {
            echo Colors::CYAN . "🔧 Admin Status Check" . Colors::NC . "\n";
            echo "====================\n";
            
            // System health check
            $health = $this->apiRequest('GET', '/api/health.php');
            if ($health) {
                echo "✅ System Health: " . ($health['status'] ?? 'Unknown') . "\n";
                echo "   Database: " . ($health['database']['status'] ?? 'Unknown') . "\n";
                echo "   Cache: " . ($health['cache']['status'] ?? 'Unknown') . "\n";
                echo "   Security: " . ($health['security']['status'] ?? 'Unknown') . "\n";
            }
            
            // User statistics
            $users = $this->apiRequest('GET', '/api/v1/admin/users/stats');
            if ($users) {
                echo "👥 Total Users: " . ($users['total'] ?? 'Unknown') . "\n";
                echo "   Active Today: " . ($users['active_today'] ?? 'Unknown') . "\n";
                echo "   New This Week: " . ($users['new_this_week'] ?? 'Unknown') . "\n";
            }
            
            // System performance metrics
            $performance = $this->apiRequest('GET', '/api/v1/admin/performance');
            if ($performance) {
                echo "📊 Performance Metrics:\n";
                echo "   Average Response Time: " . ($performance['avg_response_time'] ?? 'Unknown') . "ms\n";
                echo "   Memory Usage: " . ($performance['memory_usage'] ?? 'Unknown') . "%\n";
                echo "   CPU Usage: " . ($performance['cpu_usage'] ?? 'Unknown') . "%\n";
            }
            
            // Log admin status check
            $this->securityLogger->logSecurityEvent('ADMIN_STATUS_CHECK', [
                'admin_key_hash' => hash('sha256', $this->config['admin_key']),
                'system_health' => $health['status'] ?? 'unknown'
            ]);
            
        } catch (Exception $e) {
            echo Colors::RED . "❌ Failed to retrieve admin status: " . $e->getMessage() . Colors::NC . "\n";
            
            $this->securityLogger->logSecurityEvent('ADMIN_STATUS_ERROR', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function adminUsers($args) {
        try {
            if (empty($args)) {
                // List all users
                $users = $this->apiRequest('GET', '/api/v1/admin/users?limit=50');
                if ($users && isset($users['data'])) {
                    echo Colors::CYAN . "👥 User List (showing first 50)" . Colors::NC . "\n";
                    echo "==============================\n";
                    foreach ($users['data'] as $user) {
                        $statusColor = $user['status'] === 'active' ? Colors::GREEN : Colors::RED;
                        echo sprintf("ID: %d | Email: %s | Status: %s%s%s | Created: %s\n",
                            $user['id'], 
                            $user['email'], 
                            $statusColor,
                            $user['status'],
                            Colors::NC,
                            $user['created_at']
                        );
                    }
                }
            } else {
                // Get specific user details
                $userId = $args[0];
                $user = $this->apiRequest('GET', "/api/v1/admin/users/{$userId}");
                if ($user) {
                    echo Colors::CYAN . "👤 User Details" . Colors::NC . "\n";
                    echo "===============\n";
                    echo "ID: " . $user['id'] . "\n";
                    echo "Email: " . $user['email'] . "\n";
                    echo "Name: " . ($user['name'] ?? 'Not set') . "\n";
                    echo "Status: " . $user['status'] . "\n";
                    echo "Role: " . ($user['role'] ?? 'user') . "\n";
                    echo "Cats: " . ($user['cat_count'] ?? 0) . "\n";
                    echo "Coins: " . ($user['coins'] ?? 0) . "\n";
                    echo "Created: " . $user['created_at'] . "\n";
                    echo "Last Login: " . ($user['last_login'] ?? 'Never') . "\n";
                }
            }
            
            // Log user management access
            $this->securityLogger->logSecurityEvent('ADMIN_USER_ACCESS', [
                'action' => empty($args) ? 'list_users' : 'view_user',
                'target_user' => $args[0] ?? null
            ]);
            
        } catch (Exception $e) {
            echo Colors::RED . "❌ Failed to retrieve user information: " . $e->getMessage() . Colors::NC . "\n";
        }
    }
}
```

**Database Management Commands:**
```php
// ✅ GOOD - Secure database management with comprehensive validation
class SecureDatabaseCommands {
    private function commandDatabase($args) {
        if (!$this->requireDatabaseKey()) {
            return;
        }

        if (empty($args)) {
            $this->showDatabaseHelp();
            return;
        }

        $subcommand = array_shift($args);
        switch ($subcommand) {
            case 'status':
                $this->dbStatus($args);
                break;
            case 'repair':
                $this->dbRepair($args);
                break;
            case 'backup':
                $this->dbBackup($args);
                break;
            case 'restore':
                $this->dbRestore($args);
                break;
            case 'optimize':
                $this->dbOptimize($args);
                break;
            case 'migrate':
                $this->dbMigrate($args);
                break;
            default:
                $this->showDatabaseHelp();
                break;
        }
    }
    
    private function dbStatus($args) {
        try {
            echo Colors::CYAN . "🗄️ Database Status" . Colors::NC . "\n";
            echo "===================\n";
            
            $status = $this->apiRequest('GET', '/api/v1/admin/database/status');
            
            if ($status) {
                echo "Connection: " . ($status['connection'] ? Colors::GREEN . "✅ Connected" : Colors::RED . "❌ Disconnected") . Colors::NC . "\n";
                echo "Version: " . ($status['version'] ?? 'Unknown') . "\n";
                echo "Tables: " . ($status['table_count'] ?? 0) . "\n";
                echo "Total Records: " . number_format($status['total_records'] ?? 0) . "\n";
                echo "Database Size: " . ($status['size'] ?? 'Unknown') . "\n";
                
                if (isset($status['tables'])) {
                    echo "\nTable Status:\n";
                    foreach ($status['tables'] as $table) {
                        $healthColor = $table['health'] === 'OK' ? Colors::GREEN : Colors::YELLOW;
                        echo "  {$table['name']}: {$healthColor}{$table['health']}{Colors::NC} ({$table['rows']} rows)\n";
                    }
                }
            }
            
        } catch (Exception $e) {
            echo Colors::RED . "❌ Failed to get database status: " . $e->getMessage() . Colors::NC . "\n";
        }
    }
    
    private function dbBackup($args) {
        $backupName = $args[0] ?? ('backup_' . date('Y-m-d_H-i-s'));
        
        try {
            echo Colors::YELLOW . "📦 Creating database backup: $backupName" . Colors::NC . "\n";
            
            $result = $this->apiRequest('POST', '/api/v1/admin/database/backup', [
                'backup_name' => $backupName,
                'compression' => 'gzip',
                'include_logs' => false
            ]);
            
            if ($result && $result['success']) {
                echo Colors::GREEN . "✅ Backup created successfully!" . Colors::NC . "\n";
                echo "Backup file: " . $result['backup_file'] . "\n";
                echo "Size: " . $result['size'] . "\n";
                echo "Duration: " . $result['duration'] . " seconds\n";
            } else {
                throw new Exception($result['error'] ?? 'Backup failed');
            }
            
        } catch (Exception $e) {
            echo Colors::RED . "❌ Backup failed: " . $e->getMessage() . Colors::NC . "\n";
        }
    }
}
```

#### Command Categories

**Administrative Commands (`purrr admin`):**
- `status` - System health and performance overview
- `users [user_id]` - User management and details
- `keys` - API key management and statistics
- `logs [level]` - View system logs with filtering

**Database Commands (`purrr db`):**
- `status` - Database connection and table health
- `repair` - Repair corrupted tables
- `backup [filename]` - Create database backups
- `restore <backup_file>` - Restore from backup
- `optimize` - Optimize database performance
- `migrate` - Run pending database migrations

**System Commands (`purrr system`):**
- `status` - System resource usage and health
- `health` - Comprehensive system diagnostics
- `logs [service]` - View system service logs
- `cache [clear|warm]` - Cache management operations
- `restart` - Restart system services

**Support Commands (`purrr support`):**
- `tickets` - View and manage support tickets
- `users [user_id]` - Get user support information
- `logs` - View support interaction logs

#### Usage Examples

**System Management:**
```bash
# Check overall system status
purrr admin status

# View specific user details
purrr admin users 123

# Create database backup
purrr db backup production_backup_2024

# Check database health
purrr db status

# Clear system cache
purrr system cache clear

# View recent support tickets
purrr support tickets
```

**Advanced Feature Management:**
```bash
# Deploy night watch patrol
purrr nightwatch deploy 1 3 5

# Create protection zone
purrr nightwatch create-zone safe_haven "Emergency Shelter" "Downtown" 100

# Check blockchain NFT status
purrr blockchain status

# Test webhook delivery
purrr webhooks test WH_MAIN

# Search lost pets
purrr lost-pet search "Golden Retriever" "Golden" 10
```

#### Security Considerations

**Key Management:**
- Store administrative keys securely using environment variables or key management systems
- Rotate keys regularly (recommended: every 90 days)
- Use different keys for different environments (development, staging, production)
- Audit key usage through security logs

**Access Control:**
- Limit admin key distribution to authorized personnel only
- Implement role-based access for different administrative functions
- Monitor and log all administrative command usage
- Require two-factor authentication for key generation/distribution

**Audit Trail:**
```php
// All administrative commands must log their usage
$this->securityLogger->logSecurityEvent('ADMIN_COMMAND_EXECUTED', [
    'command' => $command,
    'subcommand' => $subcommand,
    'arguments' => $this->sanitizeArguments($args),
    'key_type' => $keyType,
    'key_hash' => hash('sha256', $keyValue),
    'ip_address' => $this->getClientIP(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
    'timestamp' => time(),
    'success' => $operationResult
]);
```

#### Testing Requirements
- **Authentication Testing**: Verify key validation and access control
- **Command Testing**: Test all administrative commands with valid/invalid inputs
- **Security Testing**: Attempt unauthorized access and verify proper blocking
- **Integration Testing**: Test CLI commands against actual API endpoints

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

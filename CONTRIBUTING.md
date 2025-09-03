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

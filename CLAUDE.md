# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Common Development Commands

### React Frontend (Vite + React)
```bash
cd web/react-app
npm run dev         # Development server (Vite)
npm run build       # Production build (TypeScript + Vite)
npm run start       # Preview production build
npm run lint        # ESLint
npm run type-check  # TypeScript type checking
```

### Node.js SDK
```bash
cd sdk/nodejs
npm run build       # Build TypeScript to dist/
npm run dev         # Watch mode build
npm test            # Run tests with Jest
npm run test:watch  # Watch mode testing
npm run test:coverage # Coverage reports
npm run lint        # ESLint
npm run lint:fix    # Auto-fix linting issues
npm run format      # Prettier formatting
npm run docs        # Generate TypeDoc documentation
```

### PHP Backend
```bash
composer install    # Install PHP dependencies
php -S localhost:8000 # Development server
```

### CLI Tool
```bash
cd cli
chmod +x ./purrr     # Make executable
./purrr help         # Show available commands
./purrr setup        # Initial setup
```

### Testing & Scripts
```bash
# All tests are located in tests/ directory
php tests/test-mariadb.php          # Database connection test
php tests/test-ai-name-generator.php # AI name generator test

# Administrative scripts in scripts/ directory
php scripts/create-database.php     # Database setup
php scripts/complete-db-fix.php     # Database repair
php admin/advanced-login.php        # Admin authentication
```

## Project Structure

The project is organized into clear functional directories:

```
purrr.love/
├── admin/           # Admin panel and authentication
├── api/             # API endpoints (v1, v2, webhooks)
├── cli/             # Command-line interface tools
├── config/          # Configuration files
├── docs/            # All documentation (centralized)
├── includes/        # PHP utilities and security (Phase 1)
├── node-sdk/        # Node.js SDK
├── scripts/         # Setup, deployment, and utility scripts
├── tests/           # All test files (centralized)
└── web/             # Web applications (PHP + React)
    └── react-app/   # Modern React frontend (Phase 2)
```

See `docs/PROJECT_STRUCTURE.md` for detailed organization information.

## Architecture Overview

### Multi-Component Structure
This is a comprehensive cat gaming ecosystem with multiple interconnected components:

- **PHP Backend**: Main application server with REST API endpoints, OAuth2 authentication, and database operations
- **React Frontend**: Modern Vite + React 18 application with TypeScript, Tailwind CSS, and comprehensive UI component library
- **Node.js SDK**: TypeScript SDK for external integrations with comprehensive API wrapper
- **CLI Tool**: Bash-based command-line interface for developers and power users
- **Database**: MariaDB/MySQL with complete schema for users, cats, genetics, and game data

### Key Technologies
- **Backend**: PHP 8.0+, MariaDB/MySQL, OAuth2, Stripe payments, enterprise security
- **Frontend**: Vite, React 18, TypeScript, Tailwind CSS, Framer Motion, Zustand, @tanstack/react-query
- **SDK**: TypeScript, Jest testing, axios for HTTP, WebSockets
- **Payments**: Stripe integration with subscription management
- **Infrastructure**: AWS ECS, Docker containers, SSL/TLS

### Core Features
- Advanced cat care simulation with genetics and breeding
- Cryptocurrency integration (BTC, ETH, USDC, SOL, XRP)
- Night Watch protection system for stray cats
- AI-powered personality analysis and behavior prediction
- VR/Metaverse integration for immersive cat worlds
- Real-time multiplayer interactions
- OAuth2 authentication with multiple providers

### Database Schema
The application uses MariaDB/MySQL with tables for:
- `users` - User accounts and profiles
- `cats` - Cat entities with genetics and personalities
- `sessions` - User session management
- `oauth2_*` - OAuth2 server implementation
- Game mechanics, territories, breeding, and social features

### API Structure
- REST API with 50+ endpoints under `/api/`
- OAuth2 server for secure authentication
- Comprehensive SDK for Node.js applications
- WebSocket support for real-time features

### Security Implementation (Phase 1 Complete)
- Enterprise-grade security with CSRF protection via `includes/security_utils.php`
- SQL injection prevention via prepared statements (SecurityUtils class)
- Rate limiting and audit logging
- HTTPS/SSL required for production
- Multi-factor authentication support
- Comprehensive input sanitization and validation

### Modern Frontend Architecture (Phase 2 Complete)
- **Design System**: Comprehensive design tokens in `web/react-app/src/styles/`
- **Component Library**: Modern UI components in `web/react-app/src/components/ui/`
- **State Management**: Zustand stores in `web/react-app/src/store/`
- **API Integration**: React Query hooks in `web/react-app/src/hooks/`
- **Payment System**: Stripe integration in `web/react-app/src/components/subscription/`
- **Theme Support**: Dark/light mode with system preference detection
- **Error Handling**: Comprehensive error boundaries and user feedback

## Development Notes

### Environment Setup
The application requires configuration files in `includes/`:
- `includes/config.php` - Secure configuration management (Phase 1)
- Environment variables via `.env` files
- Database connection via Config class singleton pattern

### Testing
- **All Tests**: Centralized in `tests/` directory
- **Frontend**: React Testing Library integration ready
- **SDK**: Jest with TypeScript, includes coverage reporting  
- **Backend**: PHP unit tests and integration tests in `tests/`
- **Database**: Connection and functionality tests available

### Deployment
The application is production-ready and deployed on AWS ECS with:
- Docker containerization
- Auto-scaling load balancers
- SSL certificates via AWS Certificate Manager
- MariaDB RDS for database
- Multiple domain support (purrr.love, purrr.me)
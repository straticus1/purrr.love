# Purrr.love Project Structure

This document outlines the organized directory structure of the Purrr.love project after cleanup and reorganization.

## Root Directory Structure

```
purrr.love/
├── README.md                    # Main project README
├── CHANGELOG.md                 # Version history and changes
├── CLAUDE.md                    # Claude Code guidance file
├── composer.json                # PHP dependencies
├── package.json                 # Node.js dependencies (if any)
├── 
├── admin/                       # Admin panel and authentication
│   ├── admin_login_test.php
│   ├── admin_response.php
│   └── advanced-login.php
├── 
├── api/                         # API endpoints and services
│   ├── v1/                      # Version 1 API
│   ├── v2/                      # Version 2 API
│   └── webhooks/                # Webhook handlers
├── 
├── cli/                         # Command-line interface tools
│   ├── purrr.php                # Main CLI application
│   └── commands/                # CLI command implementations
├── 
├── config/                      # Configuration files
│   └── (configuration files)
├── 
├── docs/                        # All documentation
│   ├── PROJECT_STRUCTURE.md    # This file
│   ├── INSTALL.md               # Installation guide
│   ├── DEPLOYMENT_COMPLETE.md   # Deployment documentation
│   ├── CREDENTIALS.md           # Credential management
│   ├── METAVERSE_DEPLOYMENT_GUIDE.md
│   ├── PRODUCTION_READINESS_REPORT.md
│   ├── REACT_APP_README.md      # React app specific docs
│   ├── CHANGELOG.md             # Detailed changelog
│   ├── CONTRIBUTING.md          # Contribution guidelines
│   ├── DOCUMENTATION.md         # General documentation
│   ├── TESTING.md               # Testing guidelines
│   └── (other documentation files)
├── 
├── includes/                    # PHP includes and utilities
│   ├── config.php               # Configuration management
│   ├── security_utils.php       # Security utilities (Phase 1)
│   └── (other PHP includes)
├── 
├── node-sdk/                    # Node.js SDK
│   ├── package.json
│   ├── src/
│   └── examples/
├── 
├── scripts/                     # Setup, deployment, and utility scripts
│   ├── complete-db-fix.php      # Database fix scripts
│   ├── create-database.php      # Database creation
│   ├── db-init.php              # Database initialization
│   ├── debug-env.php            # Environment debugging
│   ├── demo_registration.php    # Demo/test scripts
│   └── dns_backup/              # DNS backup files
├── 
├── tests/                       # Test files and test utilities
│   ├── test-personality-standalone.php
│   ├── standalone_test_output.php
│   ├── test-db.php
│   ├── test-admin.php
│   ├── test-mariadb.php
│   ├── db-test.php
│   ├── test-ai-name-generator.php
│   ├── test_purrr.php
│   ├── test_output.php
│   └── ai-name-generator-test-results.php
├── 
└── web/                         # Web applications
    ├── index.php                # Main PHP web application
    ├── react-app/               # React.js frontend
    │   ├── package.json
    │   ├── src/
    │   │   ├── components/      # React components
    │   │   │   ├── ui/          # UI component library (Phase 2)
    │   │   │   └── subscription/ # Subscription components
    │   │   ├── contexts/        # React contexts (Theme, etc.)
    │   │   ├── hooks/           # Custom React hooks
    │   │   ├── lib/             # Utilities and libraries
    │   │   ├── pages/           # Page components
    │   │   ├── providers/       # Provider components
    │   │   ├── store/           # Zustand state management
    │   │   ├── styles/          # Design tokens and styles
    │   │   └── types/           # TypeScript type definitions
    │   ├── public/
    │   └── dist/
    └── (other PHP files)
```

## Key Organizational Principles

### 1. **Separation by Function**
- `admin/` - Administrative functionality
- `api/` - API endpoints and services
- `cli/` - Command-line tools
- `config/` - Configuration management
- `docs/` - All documentation centralized
- `includes/` - Shared PHP utilities
- `scripts/` - Setup and utility scripts
- `tests/` - All test files centralized
- `web/` - Web applications

### 2. **Modern Frontend Structure**
The React app follows modern React/TypeScript patterns:
- `components/ui/` - Reusable UI component library
- `hooks/` - Custom React hooks for API integration
- `store/` - Zustand state management
- `providers/` - Context providers (Stripe, Query, Theme)
- `types/` - TypeScript type definitions

### 3. **Phase-based Development**
The structure supports the phased development approach:
- **Phase 1**: Security fixes in `includes/security_utils.php`
- **Phase 2**: Modern UI/UX in `web/react-app/src/`
- **Phase 3**: Enterprise features (to be added)

## Recent Improvements (Phase 2)

### Modern React Architecture
- **Design System**: Comprehensive design tokens in `styles/`
- **Component Library**: Modern UI components in `components/ui/`
- **State Management**: Zustand stores in `store/`
- **API Integration**: React Query hooks in `hooks/`
- **Payment System**: Stripe integration in `components/subscription/`

### Security Enhancements (Phase 1)
- **Security Utils**: Centralized security functions
- **Config Management**: Environment-based configuration
- **SQL Injection Prevention**: Prepared statements throughout
- **Error Handling**: Comprehensive error boundaries

## Usage Guidelines

### For Developers
1. **Documentation**: Always check `docs/` for setup and guidelines
2. **Testing**: Add tests to `tests/` directory
3. **Scripts**: Use `scripts/` for setup and utility scripts
4. **Configuration**: Use `config/` for environment settings

### For Deployment
1. **Installation**: Follow `docs/INSTALL.md`
2. **Production**: Reference `docs/PRODUCTION_READINESS_REPORT.md`
3. **Security**: Review `docs/SECURITY_IMPLEMENTATION_SUMMARY.md`

### For Future Development
1. **Frontend**: Follow React patterns in `web/react-app/src/`
2. **Backend**: Use security utilities in `includes/`
3. **API**: Extend APIs in `api/v2/`
4. **Documentation**: Update relevant docs in `docs/`

This structure provides a clean, maintainable, and scalable foundation for continued development of the Purrr.love platform.
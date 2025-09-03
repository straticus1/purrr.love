# 🚀 Purrr.love Installation Guide

This document provides comprehensive installation instructions for the Purrr.love platform across different environments and deployment scenarios.

## 📋 Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Installation](#quick-installation)
- [Detailed Installation Instructions](#detailed-installation-instructions)
  - [Local Development Environment](#local-development-environment)
  - [Shared Hosting Deployment](#shared-hosting-deployment)
  - [VPS/Dedicated Server Deployment](#vpsdedicated-server-deployment)
  - [AWS Cloud Deployment](#aws-cloud-deployment)
  - [Rocky Linux Deployment](#rocky-linux-deployment)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
  - [Core Configuration](#core-configuration)
  - [Cryptocurrency Integration](#cryptocurrency-integration)
  - [OAuth2 Setup](#oauth2-setup)
  - [AI Services Integration](#ai-services-integration)
- [Security Setup](#security-setup)
- [Troubleshooting](#troubleshooting)

## 📋 Prerequisites

Before installing Purrr.love, ensure your system meets these requirements:

- **PHP Requirements**:
  - PHP 8.0+ (recommended) or PHP 7.4+
  - PHP Extensions:
    - PDO & PDO_MySQL/PDO_PgSQL
    - cURL
    - JSON
    - mbstring
    - OpenSSL
    - fileinfo
    - GD or Imagick (for image processing)

- **Database Requirements**:
  - MySQL 8.0+ or MariaDB 10.4+
  - PostgreSQL 13+ (recommended for production)
  - SQLite 3+ (for development/testing only)

- **Web Server Requirements**:
  - Apache 2.4+ with mod_rewrite
  - Nginx 1.18+ with proper rewrite rules

- **Other Requirements**:
  - Composer (dependency management)
  - Git (for installation and updates)
  - SSL Certificate (required for OAuth2 and crypto payments)
  - Minimum 2GB RAM and 10GB storage

## 🚀 Quick Installation

### One-Command Installation (Recommended)

For a fully automated setup on a new server:

```bash
# Clone the repository
git clone https://github.com/straticus1/purrr.love.git
cd purrr.love

# Make the deployment script executable
chmod +x deploy.sh

# For traditional server deployment (Rocky Linux)
./deploy.sh --rocky --server your-server.com

# OR for AWS cloud deployment
./deploy.sh --aws --environment production
```

### Docker-Based Installation

For containerized deployment with Docker:

```bash
# Clone the repository
git clone https://github.com/straticus1/purrr.love.git
cd purrr.love/deployment/aws/docker

# Start the containers
docker-compose -f docker-compose.production.yml up -d
```

### Manual Installation

For a step-by-step manual installation:

```bash
# 1. Clone repository
git clone https://github.com/straticus1/purrr.love.git
cd purrr.love

# 2. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 3. Set up database
# For MySQL:
mysql -u root -p < database/schema.sql
# Or PostgreSQL:
psql -U postgres -d purrr_love -f database/api_schema.sql

# 4. Configure environment
cp config/config.example.php config/config.php
cp config/database.example.php config/database.php
cp config/oauth2.example.php config/oauth2.php
# Edit config files with your settings

# 5. Set proper permissions
chmod 755 uploads/
chmod 755 cache/
chown -R www-data:www-data uploads/ cache/

# 6. Set up web server (Apache/Nginx)
# See web server configuration examples below

# 7. Run CLI setup (optional)
./cli/purrr setup
```

## 📝 Detailed Installation Instructions

### Local Development Environment

For setting up a local development environment:

1. **Prerequisites**:
   - PHP 8.0+ (from php.net or via package manager)
   - Composer (from getcomposer.org)
   - Local database (MySQL, PostgreSQL, or SQLite)
   - Web server (Apache, Nginx) or PHP built-in server

2. **Installation Steps**:

   ```bash
   # Clone repository
   git clone https://github.com/straticus1/purrr.love.git
   cd purrr.love

   # Install dependencies (with dev dependencies for testing)
   composer install

   # Create development database
   mysql -u root -p -e "CREATE DATABASE purrr_love CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

   # Import database schema
   mysql -u root -p purrr_love < database/schema.sql

   # Copy configuration files
   cp config/config.example.php config/config.php
   cp config/database.example.php config/database.php
   cp config/oauth2.example.php config/oauth2.php

   # Edit database configuration
   # Set DB_HOST, DB_NAME, DB_USER, DB_PASS in config/database.php

   # Enable development mode
   # Set DEVELOPER_MODE to true in config/config.php

   # Start PHP development server
   php -S localhost:8000
   ```

3. **Access the application**:
   - Open http://localhost:8000 in your browser
   - Login with default credentials (admin/admin) - make sure to change this!

4. **Development Tools**:
   ```bash
   # Run tests
   ./vendor/bin/phpunit

   # Check code style
   ./vendor/bin/phpcs

   # Fix code style
   ./vendor/bin/phpcbf
   ```

### Shared Hosting Deployment

For deployment to standard shared hosting environments:

1. **Prerequisites**:
   - Shared hosting account with PHP 8.0+ support
   - MySQL database
   - SSH access (recommended but not required)
   - FTP/SFTP access

2. **Installation Steps**:

   a. **Local Preparation**:
   ```bash
   # Clone repository
   git clone https://github.com/straticus1/purrr.love.git
   cd purrr.love

   # Install dependencies
   composer install --no-dev --optimize-autoloader

   # Prepare configuration
   cp config/config.example.php config/config.php
   cp config/database.example.php config/database.php
   cp config/oauth2.example.php config/oauth2.php
   # Edit configuration files with your hosting details
   ```

   b. **Database Setup**:
   - Create a database through your hosting control panel
   - Import database/schema.sql using phpMyAdmin or similar tool

   c. **File Upload**:
   - Upload all files to your hosting account via FTP/SFTP
   - Ensure files are uploaded to the correct directory (public_html or www)

   d. **Permissions**:
   - Set appropriate permissions on the uploads and cache directories:
   ```bash
   chmod 755 uploads/
   chmod 755 cache/
   ```

   e. **Web Server Configuration**:
   - If you have access to .htaccess (Apache), ensure it's properly configured
   - Example .htaccess for clean URLs:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteBase /
       RewriteRule ^index\.php$ - [L]
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteRule . /index.php [L]
   </IfModule>
   ```

3. **Final Steps**:
   - Navigate to your domain in a browser
   - Complete the setup process if prompted
   - Change default credentials immediately

### VPS/Dedicated Server Deployment

For deployment to a VPS or dedicated server:

1. **Prerequisites**:
   - VPS or dedicated server with root access
   - Ubuntu 20.04+ or similar Linux distribution
   - LAMP or LEMP stack installed

2. **Installation Steps**:

   a. **Server Setup**:
   ```bash
   # Update system
   sudo apt update && sudo apt upgrade -y

   # Install required packages
   sudo apt install -y php8.0 php8.0-cli php8.0-mysql php8.0-pgsql php8.0-curl php8.0-json php8.0-mbstring php8.0-xml php8.0-zip php8.0-gd php8.0-intl mariadb-server nginx certbot python3-certbot-nginx git composer

   # Start and enable services
   sudo systemctl start nginx mysql
   sudo systemctl enable nginx mysql
   ```

   b. **Database Setup**:
   ```bash
   # Secure MySQL installation
   sudo mysql_secure_installation

   # Create database and user
   sudo mysql -e "CREATE DATABASE purrr_love CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   sudo mysql -e "CREATE USER 'purrr_user'@'localhost' IDENTIFIED BY 'secure_password';"
   sudo mysql -e "GRANT ALL PRIVILEGES ON purrr_love.* TO 'purrr_user'@'localhost';"
   sudo mysql -e "FLUSH PRIVILEGES;"
   ```

   c. **Application Installation**:
   ```bash
   # Clone repository
   cd /var/www
   sudo git clone https://github.com/straticus1/purrr.love.git
   cd purrr.love

   # Install dependencies
   sudo composer install --no-dev --optimize-autoloader

   # Import database schema
   sudo mysql purrr_love < database/schema.sql

   # Copy configuration files
   sudo cp config/config.example.php config/config.php
   sudo cp config/database.example.php config/database.php
   sudo cp config/oauth2.example.php config/oauth2.php

   # Edit configuration files
   sudo nano config/database.php
   # Set DB_HOST, DB_NAME, DB_USER, DB_PASS

   # Set permissions
   sudo chown -R www-data:www-data /var/www/purrr.love
   sudo chmod -R 755 /var/www/purrr.love
   sudo chmod -R 777 /var/www/purrr.love/uploads
   sudo chmod -R 777 /var/www/purrr.love/cache
   ```

   d. **Web Server Configuration**:
   ```bash
   # Create Nginx configuration
   sudo nano /etc/nginx/sites-available/purrr.love

   # Paste the following configuration
   server {
       listen 80;
       server_name your-domain.com;
       root /var/www/purrr.love;

       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$args;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
       }

       location ~ /\.ht {
           deny all;
       }
   }

   # Enable the site
   sudo ln -s /etc/nginx/sites-available/purrr.love /etc/nginx/sites-enabled/

   # Test configuration
   sudo nginx -t

   # Reload Nginx
   sudo systemctl reload nginx

   # Set up SSL
   sudo certbot --nginx -d your-domain.com
   ```

3. **Final Steps**:
   - Navigate to your domain in a browser
   - Complete the setup process if prompted
   - Change default credentials immediately

### AWS Cloud Deployment

For deployment to AWS using our automation:

1. **Prerequisites**:
   - AWS account with appropriate permissions
   - AWS CLI installed and configured
   - Terraform installed
   - Docker installed

2. **Automated Deployment**:

   ```bash
   # Clone repository
   git clone https://github.com/straticus1/purrr.love.git
   cd purrr.love

   # Make the deployment script executable
   chmod +x deploy.sh

   # Deploy to AWS (this creates all required resources)
   ./deploy.sh --aws --environment production
   ```

3. **Manual AWS Deployment Steps** (if you prefer more control):

   a. **Initialize Terraform**:
   ```bash
   cd deployment/aws/terraform
   terraform init
   ```

   b. **Plan the deployment**:
   ```bash
   terraform plan -var="environment=production"
   ```

   c. **Apply the configuration**:
   ```bash
   terraform apply -var="environment=production" -auto-approve
   ```

   d. **Build and push Docker image**:
   ```bash
   cd ../docker
   aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin $(terraform output -raw ecr_repository_url)
   docker build -t purrr-love:latest .
   docker tag purrr-love:latest $(terraform output -raw ecr_repository_url):latest
   docker push $(terraform output -raw ecr_repository_url):latest
   ```

   e. **Update ECS service**:
   ```bash
   aws ecs update-service --cluster purrr-love-cluster --service purrr-love-service --force-new-deployment
   ```

4. **AWS Resources Created**:
   - VPC with public and private subnets
   - RDS PostgreSQL instance
   - ECS cluster with Fargate
   - Application Load Balancer
   - S3 bucket for assets
   - CloudFront distribution
   - ECR repository for Docker images
   - IAM roles and security groups
   - CloudWatch logs
   - Route 53 DNS records (optional)

### Rocky Linux Deployment

For deployment to Rocky Linux servers:

1. **Prerequisites**:
   - Rocky Linux 8+ server
   - SSH access with sudo privileges
   - Basic knowledge of Linux administration

2. **Automated Deployment**:

   ```bash
   # Clone repository
   git clone https://github.com/straticus1/purrr.love.git
   cd purrr.love

   # Make the deployment script executable
   chmod +x deploy.sh

   # Deploy to Rocky Linux
   ./deploy.sh --rocky --server your-server.com
   ```

3. **Manual Rocky Linux Deployment Steps** (if you prefer more control):

   a. **Server Setup**:
   ```bash
   # Connect to your server
   ssh root@your-server.com

   # Update system
   dnf update -y

   # Install required packages
   dnf install -y epel-release
   dnf install -y httpd mariadb mariadb-server php php-cli php-fpm php-json php-mysqlnd php-pdo php-mbstring php-xml php-curl php-zip php-gd php-intl mod_ssl certbot python3-certbot-apache git
   
   # Start and enable services
   systemctl start httpd mariadb
   systemctl enable httpd mariadb
   ```

   b. **Database Setup**:
   ```bash
   # Secure MySQL installation
   mysql_secure_installation

   # Create database and user
   mysql -e "CREATE DATABASE purrr_love CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -e "CREATE USER 'purrr_user'@'localhost' IDENTIFIED BY 'secure_password';"
   mysql -e "GRANT ALL PRIVILEGES ON purrr_love.* TO 'purrr_user'@'localhost';"
   mysql -e "FLUSH PRIVILEGES;"
   ```

   c. **Application Installation**:
   ```bash
   # Clone repository
   cd /var/www/html
   git clone https://github.com/straticus1/purrr.love.git
   cd purrr.love

   # Install Composer
   curl -sS https://getcomposer.org/installer | php
   mv composer.phar /usr/local/bin/composer
   
   # Install dependencies
   composer install --no-dev --optimize-autoloader

   # Import database schema
   mysql purrr_love < database/schema.sql

   # Copy configuration files
   cp config/config.example.php config/config.php
   cp config/database.example.php config/database.php
   cp config/oauth2.example.php config/oauth2.php

   # Edit configuration files
   nano config/database.php
   # Set DB_HOST, DB_NAME, DB_USER, DB_PASS

   # Set permissions
   chown -R apache:apache /var/www/html/purrr.love
   chmod -R 755 /var/www/html/purrr.love
   chmod -R 777 /var/www/html/purrr.love/uploads
   chmod -R 777 /var/www/html/purrr.love/cache
   ```

   d. **Web Server Configuration**:
   ```bash
   # Create Apache configuration
   nano /etc/httpd/conf.d/purrr.love.conf

   # Paste the following configuration
   <VirtualHost *:80>
       ServerName your-domain.com
       DocumentRoot /var/www/html/purrr.love

       <Directory /var/www/html/purrr.love>
           Options -Indexes +FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>

       ErrorLog /var/log/httpd/purrr.love-error.log
       CustomLog /var/log/httpd/purrr.love-access.log combined
   </VirtualHost>

   # Test configuration
   apachectl configtest

   # Restart Apache
   systemctl restart httpd

   # Set up SSL
   certbot --apache -d your-domain.com
   ```

4. **Final Steps**:
   - Navigate to your domain in a browser
   - Complete the setup process if prompted
   - Change default credentials immediately
   - Configure SELinux if needed:
   ```bash
   semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/purrr.love/uploads(/.*)?"
   semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/purrr.love/cache(/.*)?"
   restorecon -Rv /var/www/html/purrr.love/
   ```

## 🗄️ Database Setup

### MySQL Setup

```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE purrr_love CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Create a dedicated user
mysql -u root -p -e "CREATE USER 'purrr_user'@'localhost' IDENTIFIED BY 'secure_password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON purrr_love.* TO 'purrr_user'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"

# Import the schema
mysql -u purrr_user -p purrr_love < database/schema.sql
```

### PostgreSQL Setup

```bash
# Create the database
sudo -u postgres createdb purrr_love

# Create a dedicated user
sudo -u postgres createuser --interactive --pwprompt purrr_user

# Grant privileges
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE purrr_love TO purrr_user;"

# Import the schema
psql -U purrr_user -d purrr_love -f database/api_schema.sql
```

### SQLite Setup (Development Only)

```bash
# Create the database file
touch database/purrr_love.sqlite

# Set proper permissions
chmod 666 database/purrr_love.sqlite

# Import the schema
cat database/schema_sqlite.sql | sqlite3 database/purrr_love.sqlite
```

## ⚙️ Configuration

### Core Configuration

Edit `config/config.php`:

```php
// Site configuration
define('SITE_URL', 'https://your-domain.com');
define('SITE_NAME', 'Purrr.love');
define('ADMIN_EMAIL', 'admin@your-domain.com');

// Environment settings
define('ENVIRONMENT', 'production'); // 'development', 'staging', 'production'
define('DEBUG_MODE', false);
define('DEVELOPER_MODE', false);
define('ERROR_REPORTING', false);

// Security settings
define('SESSION_LIFETIME', 86400); // 24 hours
define('CSRF_TOKEN_EXPIRE', 3600); // 1 hour
define('PASSWORD_RESET_EXPIRE', 86400); // 24 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 1800); // 30 minutes
define('API_VERSION', '1.0.0');

// Path settings
define('UPLOADS_DIR', __DIR__ . '/../uploads/');
define('CACHE_DIR', __DIR__ . '/../cache/');
define('LOGS_DIR', __DIR__ . '/../logs/');

// Feature flags
define('ENABLE_CRYPTO', true);
define('ENABLE_OAUTH2', true);
define('ENABLE_AI_GENERATION', true);
define('ENABLE_BREEDING', true);
define('ENABLE_VR', true);
define('ENABLE_MULTIPLAYER', true);
```

### Database Configuration

Edit `config/database.php`:

```php
// MySQL/MariaDB configuration
define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'purrr_love');
define('DB_USER', 'purrr_user');
define('DB_PASS', 'secure_password');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// PostgreSQL configuration (comment out if not using)
// define('DB_TYPE', 'postgresql');
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'purrr_love');
// define('DB_USER', 'purrr_user');
// define('DB_PASS', 'secure_password');
// define('DB_PORT', 5432);

// SQLite configuration (development only)
// define('DB_TYPE', 'sqlite');
// define('DB_PATH', __DIR__ . '/../database/purrr_love.sqlite');
```

### Cryptocurrency Integration

Edit `config/crypto.php`:

```php
// Crypto settings
define('ENABLE_CRYPTO_PAYMENTS', true);
define('COINBASE_API_KEY', 'your_coinbase_api_key');
define('COINBASE_WEBHOOK_SECRET', 'your_webhook_secret');
define('SUPPORTED_CRYPTOS', [
    'BTC' => 'Bitcoin',
    'ETH' => 'Ethereum',
    'USDC' => 'USD Coin',
    'SOL' => 'Solana',
    'XRP' => 'Ripple'
]);

// Exchange rate source
define('CRYPTO_PRICE_API', 'coinbase'); // Options: 'coinbase', 'coingecko', 'binance', 'custom'
define('CRYPTO_PRICE_CACHE_TIME', 300); // 5 minutes

// Withdrawal settings
define('MIN_WITHDRAWAL_USD', 10.00);
define('WITHDRAWAL_FEE_PERCENT', 1.5);
define('REQUIRE_2FA_FOR_WITHDRAWAL', true);
```

### OAuth2 Setup

Edit `config/oauth2.php`:

```php
// OAuth2 Server Configuration
define('OAUTH2_ACCESS_TOKEN_LIFETIME', 3600); // 1 hour
define('OAUTH2_REFRESH_TOKEN_LIFETIME', 2592000); // 30 days
define('OAUTH2_AUTHORIZATION_CODE_LIFETIME', 600); // 10 minutes

// OAuth2 Client Configuration
define('GOOGLE_CLIENT_ID', 'your_google_client_id');
define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret');

define('FACEBOOK_APP_ID', 'your_facebook_app_id');
define('FACEBOOK_APP_SECRET', 'your_facebook_app_secret');

define('APPLE_CLIENT_ID', 'your_apple_client_id');
define('APPLE_CLIENT_SECRET', 'your_apple_client_secret');
define('APPLE_REDIRECT_URI', 'https://your-domain.com/oauth/apple/callback');

define('TWITTER_CLIENT_ID', 'your_twitter_client_id');
define('TWITTER_CLIENT_SECRET', 'your_twitter_client_secret');
```

### AI Services Integration

Edit `config/ai.php`:

```php
// AI Configuration
define('ENABLE_AI_GENERATION', true);

// OpenAI Configuration
define('OPENAI_API_KEY', 'your_openai_api_key');
define('OPENAI_MODEL', 'gpt-4-vision-preview');
define('OPENAI_IMAGE_SIZE', '1024x1024');

// Stability AI Configuration
define('STABILITY_AI_KEY', 'your_stability_ai_key');
define('STABILITY_AI_MODEL', 'sdxl-1.0');
define('STABILITY_AI_STEPS', 30);
define('STABILITY_AI_CFG_SCALE', 7.5);

// AI Generation Limits
define('AI_GENERATION_DAILY_LIMIT', 10);
define('AI_GENERATION_COOLDOWN', 300); // 5 minutes between generations
```

## 🔒 Security Setup

### SSL Configuration

For secure HTTPS connections:

1. **Apache SSL Configuration**:
   ```apache
   <VirtualHost *:443>
       ServerName your-domain.com
       DocumentRoot /var/www/html/purrr.love

       SSLEngine on
       SSLCertificateFile /etc/letsencrypt/live/your-domain.com/fullchain.pem
       SSLCertificateKeyFile /etc/letsencrypt/live/your-domain.com/privkey.pem

       <Directory /var/www/html/purrr.love>
           Options -Indexes +FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>

       ErrorLog ${APACHE_LOG_DIR}/purrr.love-error.log
       CustomLog ${APACHE_LOG_DIR}/purrr.love-access.log combined
   </VirtualHost>
   ```

2. **Nginx SSL Configuration**:
   ```nginx
   server {
       listen 443 ssl http2;
       server_name your-domain.com;
       root /var/www/purrr.love;

       ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
       ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
       ssl_protocols TLSv1.2 TLSv1.3;
       ssl_prefer_server_ciphers on;
       ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-SHA384;
       ssl_session_timeout 1d;
       ssl_session_cache shared:SSL:10m;
       ssl_stapling on;
       ssl_stapling_verify on;

       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$args;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
       }

       location ~ /\.ht {
           deny all;
       }
   }

   server {
       listen 80;
       server_name your-domain.com;
       return 301 https://$host$request_uri;
   }
   ```

### File Permissions

Set correct file permissions:

```bash
# Standard directories
find /path/to/purrr.love -type d -exec chmod 755 {} \;
find /path/to/purrr.love -type f -exec chmod 644 {} \;

# Executable scripts
chmod +x /path/to/purrr.love/cli/purrr
chmod +x /path/to/purrr.love/deploy.sh

# Writable directories
chmod -R 777 /path/to/purrr.love/uploads
chmod -R 777 /path/to/purrr.love/cache
chmod -R 777 /path/to/purrr.love/logs
```

### Security Headers

Add security headers to your web server configuration:

**Apache** (add to .htaccess or virtual host):
```apache
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'"
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
</IfModule>
```

**Nginx** (add to server block):
```nginx
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
```

## 🔍 Troubleshooting

### Common Issues and Solutions

#### 1. Database Connection Issues
```
Error: Could not connect to database
```

**Solution**:
- Verify database credentials in `config/database.php`
- Ensure database server is running
- Check network connectivity to database server
- Verify that PHP database extensions are installed and enabled

#### 2. Permission Errors
```
Error: Could not write to file/directory
```

**Solution**:
- Check permissions on uploads, cache, and logs directories
- Ensure web server user (www-data, apache, nginx) has write permissions
- Check SELinux contexts if using RHEL/CentOS/Rocky Linux
- Verify disk space is not full

#### 3. Missing PHP Extensions
```
Error: Required extension mbstring is missing
```

**Solution**:
- Install missing PHP extensions:
  ```bash
  # For Debian/Ubuntu
  sudo apt install php8.0-mbstring php8.0-curl php8.0-xml

  # For RHEL/CentOS/Rocky
  sudo dnf install php-mbstring php-curl php-xml
  ```
- Restart PHP-FPM or web server after installing extensions

#### 4. CSRF Token Validation Failures
```
Error: CSRF token validation failed
```

**Solution**:
- Clear browser cookies and cache
- Ensure server time is correctly synchronized
- Check for session handling issues
- Verify `session.cookie_secure = On` in PHP configuration for HTTPS sites

#### 5. OAuth2 Configuration Issues
```
Error: Invalid redirect URI
```

**Solution**:
- Verify OAuth2 provider configurations
- Ensure redirect URIs match exactly with those registered in provider dashboards
- Check that SSL is properly configured if using HTTPS
- Verify correct callback URLs are set in OAuth provider settings

#### 6. Debugging Tips
- Enable development mode in `config/config.php`:
  ```php
  define('DEVELOPER_MODE', true);
  define('DEBUG_MODE', true);
  define('ERROR_REPORTING', true);
  ```
- Check error logs:
  - Application logs: `/path/to/purrr.love/logs/`
  - PHP error log: Check php.ini for error_log path
  - Web server error logs:
    - Apache: `/var/log/apache2/error.log` or `/var/log/httpd/error.log`
    - Nginx: `/var/log/nginx/error.log`
- Run the CLI diagnostics tool:
  ```bash
  ./cli/purrr diagnostics
  ```

## 🎯 Next Steps After Installation

1. **Change Default Credentials**:
   - First login with default credentials: admin/admin
   - Immediately change password and set up 2FA

2. **Set Up OAuth2 Providers**:
   - Register your app with Google, Facebook, etc.
   - Update OAuth2 configuration with client IDs and secrets

3. **Configure Cryptocurrency Integration**:
   - Set up Coinbase Commerce account
   - Update API keys in configuration

4. **Set Up Cron Jobs**:
   - Create necessary cron jobs for recurring tasks:
   ```bash
   # Daily database cleanup (2 AM)
   0 2 * * * php /path/to/purrr.love/cli/cron/cleanup.php > /dev/null 2>&1

   # Cat stats update (every 15 minutes)
   */15 * * * * php /path/to/purrr.love/cli/cron/update_cat_stats.php > /dev/null 2>&1

   # Process queued events (every 5 minutes)
   */5 * * * * php /path/to/purrr.love/cli/cron/process_queue.php > /dev/null 2>&1
   ```

5. **Test Critical Flows**:
   - User registration and login
   - Crypto deposits and withdrawals
   - Cat uploads and management
   - Game mechanics
   - API endpoints

---

For additional assistance, please refer to the [API Documentation](API_ECOSYSTEM_SUMMARY.md) and [Complete Documentation](DOCUMENTATION.md), or open an issue on the [GitHub repository](https://github.com/straticus1/purrr.love/issues).

**🐱 Purrr.love** - Building the future of feline gaming, one purr at a time! ❤️

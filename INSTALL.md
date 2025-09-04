# 🚀 Purrr.love Installation Guide

## 📋 Table of Contents

1. [Live Production System](#live-production-system)
2. [Quick Testing](#quick-testing)
3. [System Requirements](#system-requirements)
4. [AWS Production Deployment](#aws-production-deployment)
5. [Local Development Setup](#local-development-setup)
6. [Database Configuration](#database-configuration)
7. [Troubleshooting](#troubleshooting)

---

## 🌐 Live Production System

**Purrr.love is already deployed and ready to use!**

### 🔗 Access Points
- **Primary Site**: [https://purrr.love](https://purrr.love)
- **Working Login**: [https://purrr.love/working-login.php](https://purrr.love/working-login.php)
- **Health Check**: [https://purrr.love/health.php](https://purrr.love/health.php)

### 🔐 Test Credentials
Test the live system with these verified accounts:

#### Administrator Account
```
Email: admin@purrr.love
Password: admin123456789!
Role: admin
```

#### Regular User Account
```
Email: testuser@example.com
Password: testpass123
Role: user
```

---

## ⚡ Quick Testing

### Web Interface Testing
1. Visit [https://purrr.love/working-login.php](https://purrr.love/working-login.php)
2. Use either test account to login
3. Explore the cat management features

### API Testing
```bash
# Test admin login
curl -X POST "https://purrr.love/working-login.php" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@purrr.love","password":"admin123456789!"}'

# Test regular user login
curl -X POST "https://purrr.love/working-login.php" \
  -H "Content-Type: application/json" \
  -d '{"email":"testuser@example.com","password":"testpass123"}'
```

Expected response:
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "username": "admin",
        "email": "admin@purrr.love",
        "role": "admin"
    },
    "redirect": "dashboard.php"
}
```

---

## 🛠️ System Requirements

### Production (AWS)
- **Compute**: AWS ECS Fargate (2+ vCPU, 4GB+ RAM)
- **Database**: AWS RDS MariaDB 11.4.5 (db.t3.medium minimum)
- **Storage**: AWS EBS 20GB+ with automated backups
- **Network**: AWS VPC with public/private subnets
- **SSL**: AWS Certificate Manager with Route53

### Local Development
- **PHP**: 7.4+ (8.0+ recommended)
- **Database**: MariaDB 10.4+ or MySQL 8.0+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Composer**: For PHP dependency management
- **RAM**: Minimum 2GB, recommended 4GB+
- **Storage**: 10GB+ free space

---

## ☁️ AWS Production Deployment

### Current Production Infrastructure

The live system uses the following AWS services:

#### 🐳 Container Infrastructure
- **ECS Cluster**: `purrr-cluster` 
- **Service**: `purrr-app` with 2 running tasks
- **Task Definition**: Latest revision with MariaDB support
- **Container Registry**: AWS ECR with versioned images

#### 🌐 Networking & Security
- **Load Balancer**: Application Load Balancer with SSL termination
- **DNS**: Route53 with A records for purrr.love and purrr.me
- **SSL**: Certificates from AWS Certificate Manager (auto-renewal)
- **VPC**: Custom VPC with public/private subnets

#### 🗄️ Database Infrastructure
- **Database**: MariaDB 11.4.5 on AWS RDS
- **Instance**: db.t3.medium with Multi-AZ deployment
- **Storage**: 20GB SSD with automated daily backups
- **Security**: Database security group restricting access to ECS tasks

### Deployment Process

The application is deployed using containerized infrastructure:

```bash
# Current deployment status
aws ecs describe-services --cluster purrr-cluster --services purrr-app
```

#### Container Build & Deploy
```bash
# Build and push new container
docker build -t purrr-love:latest .
aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin {ECR_URI}
docker tag purrr-love:latest {ECR_URI}/purrr-love:latest
docker push {ECR_URI}/purrr-love:latest

# Update ECS service
aws ecs update-service --cluster purrr-cluster --service purrr-app --force-new-deployment
```

---

## 💻 Local Development Setup

### 1. Clone Repository
```bash
git clone https://github.com/straticus1/purrr.love.git
cd purrr.love
```

### 2. Install Dependencies
```bash
# Install PHP dependencies with Composer
composer install

# Set directory permissions
chmod 755 uploads/
chmod 755 logs/
chown -R www-data:www-data uploads/ logs/
```

### 3. Database Setup

#### Using MariaDB (Recommended)
```bash
# Install MariaDB
sudo apt update
sudo apt install mariadb-server mariadb-client

# Secure installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE purrr_love CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'purrr_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON purrr_love.* TO 'purrr_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Run Database Setup
```bash
# Initialize database schema with test data
php db-init.php
```

### 4. Configuration Files
```bash
# Copy example configuration files
cp config/config.example.php config/config.php
cp config/database.example.php config/database.php

# Edit configuration files with your settings
nano config/database.php
```

#### Database Configuration
```php
<?php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'purrr_love');
define('DB_USER', 'purrr_user');
define('DB_PASS', 'secure_password_here');
define('DB_TYPE', 'mysql');
define('DB_CHARSET', 'utf8mb4');
?>
```

### 5. Web Server Configuration

#### Apache Virtual Host
```apache
<VirtualHost *:80>
    ServerName purrr.local
    DocumentRoot /path/to/purrr.love
    
    <Directory /path/to/purrr.love>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/purrr_error.log
    CustomLog ${APACHE_LOG_DIR}/purrr_access.log combined
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name purrr.local;
    root /path/to/purrr.love;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 🗄️ Database Configuration

### MariaDB Schema

The database includes these main tables:

#### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    active BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    level INT DEFAULT 1,
    coins INT DEFAULT 100,
    experience_points INT DEFAULT 0,
    avatar_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Cats Table
```sql
CREATE TABLE cats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    owner_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    breed VARCHAR(50) DEFAULT 'Mixed',
    age INT DEFAULT 1,
    color VARCHAR(50) DEFAULT 'Orange',
    personality_openness DECIMAL(3,2) DEFAULT 3.00,
    personality_conscientiousness DECIMAL(3,2) DEFAULT 3.00,
    personality_extraversion DECIMAL(3,2) DEFAULT 3.00,
    personality_agreeableness DECIMAL(3,2) DEFAULT 3.00,
    personality_neuroticism DECIMAL(3,2) DEFAULT 3.00,
    health_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    temperature DECIMAL(4,2) DEFAULT 101.50,
    heart_rate INT DEFAULT 150,
    weight DECIMAL(5,2) DEFAULT 10.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (owner_id) REFERENCES users(id)
);
```

### Default Data

The system creates these default users:

1. **Admin User**
   - Email: admin@purrr.love
   - Password: admin123456789!
   - Role: admin

2. **Test User**
   - Email: testuser@example.com
   - Password: testpass123
   - Role: user

Both accounts include sample cats with realistic personality traits and health data.

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Database Connection Errors
```bash
# Check MariaDB service status
sudo systemctl status mariadb

# Check database credentials
mysql -u purrr_user -p purrr_love
```

#### 2. PHP Extension Requirements
```bash
# Required PHP extensions
sudo apt install php8.0-mysql php8.0-pdo php8.0-mbstring php8.0-curl php8.0-json php8.0-openssl
```

#### 3. Permission Issues
```bash
# Set correct permissions
sudo chown -R www-data:www-data /path/to/purrr.love
sudo chmod -R 755 /path/to/purrr.love
sudo chmod -R 777 uploads/ logs/
```

#### 4. SSL/HTTPS Issues (Local)
```bash
# Generate self-signed certificate for local development
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/purrr.key \
    -out /etc/ssl/certs/purrr.crt
```

### Testing Database Connection

Create a test script to verify database connectivity:

```php
<?php
// test-db.php
require_once 'config/database.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "Users in database: " . $count . "\n";
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
?>
```

### Performance Optimization

#### Database Optimization
```sql
-- Add indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_cats_user_id ON cats(user_id);
CREATE INDEX idx_cats_owner_id ON cats(owner_id);
```

#### PHP Configuration
```ini
; php.ini optimizations
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

---

## 🆘 Support

### Getting Help
- **GitHub Issues**: [Report bugs and request features](https://github.com/straticus1/purrr.love/issues)
- **Documentation**: [Complete documentation](docs/DOCUMENTATION.md)
- **Changelog**: [Version history](CHANGELOG.md)

### Development Environment
For development contributions:

1. Fork the repository
2. Set up local development environment
3. Create feature branch
4. Submit pull request

---

**Happy coding! 🐱✨**

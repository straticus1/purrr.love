# 🐱 Purrr.love Docker Image
# Multi-stage build for production-ready container

# Stage 1: Build stage
FROM php:8.1-fpm-alpine AS builder

# Install build dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    postgresql-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pdo_mysql \
        mbstring \
        xml \
        gd \
        opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Stage 2: Production stage
FROM php:8.1-fpm-alpine

# Install runtime dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-client \
    mysql-client \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pdo_mysql \
        mbstring \
        xml \
        gd \
        opcache

# Create application user
RUN addgroup -g 1000 app && \
    adduser -u 1000 -G app -s /bin/sh -D app

# Set working directory
WORKDIR /var/www/html

# Copy application files from builder
COPY --from=builder /var/www/html /var/www/html

# Copy application source code
COPY . .

# Set proper permissions
RUN chown -R app:app /var/www/html && \
    chmod -R 755 /var/www/html

# Copy configuration files
COPY deployment/aws/docker/nginx.conf /etc/nginx/nginx.conf
COPY deployment/aws/docker/php.ini /usr/local/etc/php/php.ini
COPY deployment/aws/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create necessary directories
RUN mkdir -p /var/log/nginx /var/log/php-fpm /var/log/supervisor /run/nginx

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health.php || exit 1

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

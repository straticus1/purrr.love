#!/bin/bash

# 🗃️ Purrr.love Database Setup Script
# Run database initialization inside the container

echo "🚀 Starting Database Setup..."

# Change to project directory
cd /var/www/html

# Run the PHP database initialization script
php scripts/init-database.php

echo "✅ Database setup completed!"

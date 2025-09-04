#!/bin/bash

# 🚀 Purrr.love MySQL Database Initialization Script
# Run this inside the container to set up the database

echo "🚀 Starting Purrr.love MySQL Database Setup..."

# Check if MySQL is running
if ! service mysql status > /dev/null 2>&1; then
    echo "Starting MySQL service..."
    service mysql start
    sleep 5
fi

# Check MySQL connection
echo "Testing MySQL connection..."
if mysql -u root -e "SELECT 1" > /dev/null 2>&1; then
    echo "✅ MySQL connection successful!"
else 
    echo "❌ MySQL connection failed. Checking if mysql service is running..."
    service mysql status
    exit 1
fi

# Create the database and tables
echo "Creating database and tables..."
mysql -u root < /var/www/html/create-database.sql

if [ $? -eq 0 ]; then
    echo "✅ Database initialization completed successfully!"
    
    # Verify tables were created
    echo "Verifying tables..."
    mysql -u root -D purrr_love -e "SHOW TABLES;"
    
    echo "Checking user count..."
    mysql -u root -D purrr_love -e "SELECT COUNT(*) as user_count FROM users;"
    
    echo "🎉 Database setup complete! Admin user created with username: admin, password: password"
else
    echo "❌ Database initialization failed!"
    exit 1
fi

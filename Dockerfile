# Purrr.love Production Docker Image with Metaverse Automation
FROM mattrayner/lamp:latest-1804

# Install cron for metaverse automation
RUN DEBIAN_FRONTEND=noninteractive apt-get update && apt-get install -y \
    cron \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Copy application files first to /app directory
COPY . /app/

# Set proper permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app

# Create uploads and logs directories
RUN mkdir -p /app/uploads /app/logs \
    && chown -R www-data:www-data /app/uploads /app/logs \
    && chmod -R 777 /app/uploads \
    && chmod -R 755 /app/logs

# Make CLI scripts executable
RUN chmod +x /app/cli/metaverse_automation.php

# Enable Apache modules
RUN a2enmod rewrite headers

# Create a simple process manager script
RUN echo '#!/bin/bash\n\
# Function to handle shutdown gracefully\n\
trap "echo \"Shutting down services...\"; kill -TERM \$LAMP_PID \$CRON_PID; wait" SIGTERM SIGINT\n\
# Start cron in background\n\
echo \"Starting cron daemon...\"\n\
/usr/sbin/cron &\n\
CRON_PID=\$!\n\
# Start lamp (apache/php) in background\n\
echo \"Starting LAMP stack...\"\n\
/run.sh &\n\
LAMP_PID=\$!\n\
# Wait for both processes\n\
wait' > /process-manager.sh \
    && chmod +x /process-manager.sh

# Create a custom run script that sets up everything
RUN echo '#!/bin/bash\n\
echo "Starting Purrr.love application with Metaverse Automation..."\n\
# Copy application files\n\
echo "Copying Purrr.love application files..."\n\
rm -rf /var/www/html/*\n\
cp -r /app/. /var/www/html/\n\
chown -R www-data:www-data /var/www/html\n\
chmod -R 755 /var/www/html\n\
# Ensure CLI is executable\n\
chmod +x /var/www/html/cli/metaverse_automation.php\n\
# Setup cron jobs for metaverse automation\n\
echo "Setting up metaverse automation cron jobs..."\n\
echo "# Purrr.love Metaverse Automation" > /var/spool/cron/crontabs/www-data\n\
echo "*/5 * * * * /usr/bin/php /var/www/html/cli/metaverse_automation.php monitorAndBoostMetaverseEngagement >> /var/www/html/logs/cron.log 2>&1" >> /var/spool/cron/crontabs/www-data\n\
echo "*/10 * * * * /usr/bin/php /var/www/html/cli/metaverse_automation.php spawnAICatsInLowActivityWorlds >> /var/www/html/logs/cron.log 2>&1" >> /var/spool/cron/crontabs/www-data\n\
echo "*/15 * * * * /usr/bin/php /var/www/html/cli/metaverse_automation.php balanceMetaverseWorldPopulation >> /var/www/html/logs/cron.log 2>&1" >> /var/spool/cron/crontabs/www-data\n\
echo "*/30 * * * * /usr/bin/php /var/www/html/cli/metaverse_automation.php updateMetaverseWorldWeather >> /var/www/html/logs/cron.log 2>&1" >> /var/spool/cron/crontabs/www-data\n\
echo "0 */1 * * * /usr/bin/php /var/www/html/cli/metaverse_automation.php manageMetaverseSpecialAreas >> /var/www/html/logs/cron.log 2>&1" >> /var/spool/cron/crontabs/www-data\n\
echo "*/20 * * * * /usr/bin/php /var/www/html/cli/metaverse_automation.php processMetaverseAnalytics >> /var/www/html/logs/cron.log 2>&1" >> /var/spool/cron/crontabs/www-data\n\
echo "0 6 * * * /usr/bin/php /var/www/html/cli/metaverse_automation.php updateMetaverseSeasonalContent >> /var/www/html/logs/cron.log 2>&1" >> /var/spool/cron/crontabs/www-data\n\
echo "0 0 * * * /usr/bin/php /var/www/html/cli/metaverse_automation.php generateDailyMetaverseQuests >> /var/www/html/logs/cron.log 2>&1" >> /var/spool/cron/crontabs/www-data\n\
chown www-data:crontab /var/spool/cron/crontabs/www-data\n\
chmod 600 /var/spool/cron/crontabs/www-data\n\
echo "Metaverse automation cron jobs installed successfully!"\n\
# Start process manager to handle services\n\
exec /process-manager.sh' > /custom-run.sh \
    && chmod +x /custom-run.sh

# Expose port 80
EXPOSE 80

# Start services with our custom script
CMD ["/custom-run.sh"]

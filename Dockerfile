# Purrr.love Production Docker Image with Metaverse Automation
FROM mattrayner/lamp:latest-1804

# Install cron and PostgreSQL support for metaverse automation
RUN DEBIAN_FRONTEND=noninteractive apt-get update && apt-get install -y \
    cron \
    curl \
    php-pgsql \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*

# Clear the default web directory
RUN rm -rf /var/www/html/*

# Copy application files directly to web root
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create uploads and logs directories
RUN mkdir -p /var/www/html/uploads /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/uploads /var/www/html/logs \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 755 /var/www/html/logs

# Make CLI scripts executable
RUN chmod +x /var/www/html/cli/metaverse_automation.php

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
echo "Application files already in place"\n\
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

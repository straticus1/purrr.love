# Purrr.love Production Docker Image
FROM mattrayner/lamp:latest-1804

# Copy application files first to /app directory
COPY . /app/

# Set proper permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app

# Create uploads directory if needed
RUN mkdir -p /app/uploads \
    && chown -R www-data:www-data /app/uploads \
    && chmod -R 777 /app/uploads

# Enable Apache modules
RUN a2enmod rewrite headers

# Create a custom run script that copies our app files after the LAMP setup
RUN echo '#!/bin/bash\n\
echo "Starting Purrr.love application setup..."\n\
# Run the original LAMP setup\n\
/run.sh &\n\
LAMP_PID=$!\n\
# Wait for LAMP to be ready\n\
sleep 30\n\
echo "Copying Purrr.love application files..."\n\
# List what we have in /app\n\
ls -la /app/\n\
# Remove default files and copy our application\n\
rm -rf /var/www/html/*\n\
cp -r /app/. /var/www/html/\n\
chown -R www-data:www-data /var/www/html\n\
chmod -R 755 /var/www/html\n\
echo "Purrr.love application deployed successfully!"\n\
echo "Files in /var/www/html:"\n\
ls -la /var/www/html/\n\
# Wait for the LAMP process\n\
wait $LAMP_PID' > /custom-run.sh \
    && chmod +x /custom-run.sh

# Expose port 80
EXPOSE 80

# Start services with our custom script
CMD ["/custom-run.sh"]

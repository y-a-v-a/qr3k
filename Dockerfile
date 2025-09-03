# QR3K Runtime Container
FROM php:8.2-apache as base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Enable PHP session support and required extensions  
RUN docker-php-ext-install session

# Enable Apache modules
RUN a2enmod rewrite headers expires

# Configure Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Development stage (default)
FROM base as development
# Runtime files will be mounted as volume

# Production stage
FROM base as production

# Copy runtime files for production
COPY src/runtime/ /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
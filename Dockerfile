# QR3K Runtime Container
FROM php:8.2-apache as base

# Install system dependencies (libpng enables GD for PNG QR code output)
RUN apt-get update && apt-get install -y \
    curl \
    libpng-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install gd

# Enable Apache modules (sessions and zlib are built into the official PHP image)
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
COPY src/encoding/ /var/www/encoding/
COPY src/games/ /var/www/games/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
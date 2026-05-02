FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (includes pdo_pgsql for Supabase)
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies (no dev packages in production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Install Node dependencies and build Vite assets
RUN npm ci && npm run build

# Set correct permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Apply Apache virtual host config
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Copy startup script and ensure Linux line endings
COPY start.sh /start.sh
RUN sed -i 's/\r//' /start.sh && chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]

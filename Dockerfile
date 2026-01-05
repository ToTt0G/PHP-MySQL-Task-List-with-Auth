# Use the official PHP 8.2 Apache image as a base
FROM php:8.2-apache

# Install and enable the mysqli extension for database connectivity
RUN docker-php-ext-install pdo_mysql && docker-php-ext-enable pdo_mysql

# Copy the custom Apache configuration file into the container
# This enables .htaccess files by setting AllowOverride All
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache's rewrite and headers modules
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy application code into the image
# In development, the bind mount (./:/var/www/html) overrides this
# In production, this bakes the code into the image
COPY . .

# Install PHP dependencies (will be re-run on container start in dev)
RUN composer install --no-dev --optimize-autoloader || true
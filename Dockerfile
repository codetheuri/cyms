FROM vaultke/php8-fpm-nginx

WORKDIR /var/www/html

# Copy composer files first
COPY composer.json composer.lock* ./

# Install PHP extensions & system tools
RUN apk add --no-cache \
    bash \
    curl \
    zip \
    unzip \
    php81 \
    php81-fpm \
    php81-mbstring \
    php81-openssl \
    php81-xml \
    php81-simplexml \
    php81-dom \
    php81-xmlwriter \
    php81-xmlreader \
    php81-fileinfo \
    php81-gd \
    php81-intl \
    php81-zip \
    nginx  # Ensure nginx is installed

# Run Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-req=ext-xmlreader

# Copy the rest of the app
COPY . .

# Copy nginx config (this will be overwritten by docker-compose volume mount)
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 /var/www/html \
 && chmod -R 777 /var/www/html/providers/interface/assets/core

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
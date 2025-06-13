FROM dunglas/frankenphp
WORKDIR /app

RUN install-php-extensions \
    pcntl \
    zip \
    pdo_mysql

RUN apt-get update && apt-get install -y bash curl && curl -1sLf \
    'https://artifacts-cli.infisical.com/setup.deb.sh' | bash \
    && apt-get update && apt-get install -y infisical

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /app

# Set proper permissions
RUN chmod -R 775 /app/storage /app/bootstrap/cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

RUN composer install --optimize-autoloader --no-dev

RUN php artisan optimize:clear
RUN php artisan event:cache
RUN php artisan route:cache
RUN php artisan view:cache

EXPOSE 8000
RUN chmod +x /app/start.sh
RUN chmod +x /app/start_ws.sh
CMD ["/app/start.sh"]

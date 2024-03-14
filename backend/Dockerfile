FROM php:8.2-fpm-bookworm
WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y bash curl && curl -1sLf \
'https://dl.cloudsmith.io/public/infisical/infisical-cli/setup.deb.sh' | bash \
&& apt-get update && apt-get install -y infisical

RUN echo "deb https://nginx.org/packages/debian/ bookworm nginx" | tee /etc/apt/sources.list.d/nginx.list
RUN echo "deb-src https://nginx.org/packages/debian/ bookworm nginx" | tee -a /etc/apt/sources.list.d/nginx.list
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys ABF5BD827BD9BF62
RUN apt-get update && apt-get install -y nginx

RUN apt-get update && apt-get install -y supervisor

COPY . /app/
COPY ./conf/nginx.conf /etc/nginx/nginx.conf
COPY ./conf/www.conf /usr/local/etc/php-fpm.d/www.conf
RUN mkdir -p /var/run/php/
RUN mkdir -p /var/log/supervisor
COPY ./conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN apt-get update && apt-get install -y libzip-dev 
RUN docker-php-ext-install zip pdo_mysql

RUN composer install --optimize-autoloader --no-dev

RUN php artisan event:cache
RUN php artisan route:cache
RUN php artisan view:cache

RUN chown -R www-data:www-data /app/storage

EXPOSE 9000

ENTRYPOINT [ "infisical", "run", "--env=prod", "--", "php", "artisan", "config:cache", "&&", "php", "artisan", "migrate", "--force"]

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

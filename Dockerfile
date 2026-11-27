FROM php:8.2-apache
RUN docker-php-ext-install pdo_mysql
RUN apt-get update \
&& apt-get install -y libssl-dev pkg-config unzip git \
&& pecl install mongodb \
&& docker-php-ext-enable mongodb
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html/logs
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev
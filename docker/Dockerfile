FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite headers

# Enable .htaccess support for the app root
RUN sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . /var/www/html

EXPOSE 80
ENTRYPOINT ["/bin/bash", "/usr/local/bin/apache2-foreground"]
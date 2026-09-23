#!/bin/bash
docker-php-ext-install pdo pdo_mysql
a2enmod rewrite
a2enmod headers
apache2-foreground

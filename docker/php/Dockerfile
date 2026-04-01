FROM php:8.4-apache

# PHP-Erweiterungen installieren
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Apache mod_rewrite aktivieren
RUN a2enmod rewrite

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache DocumentRoot auf das Projektverzeichnis setzen
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Apache Konfiguration anpassen
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

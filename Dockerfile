FROM php:8.3-apache

# Install drivers to connect to MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite module (useful for backend)
RUN a2enmod rewrite
FROM php:8.1.0RC3-alpine

# CONFIGURATION
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# CURL
RUN apk update && apk upgrade && apk add libzip-dev curl-dev

# PHP EXTENSIONS
RUN docker-php-ext-install mysqli pdo pdo_mysql curl

# COMPOSER
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# NPM
RUN apk add --update nodejs npm

# REDOC CLI
RUN npm install -g redoc-cli

# SUPERVISORD
#RUN apk add supervisor

WORKDIR /var/www

FROM php:7.4-buster

LABEL maintainer="Couchbase"

WORKDIR /app

ADD . /app

# Install dependencies
RUN apt-get update -y && apt-get install -y \
    software-properties-common \
    git-all \
    gnupg2 wget vim \
    libzip-dev zip \
    jq curl \
    && docker-php-ext-install zip

# Configure APT repository for libcouchbase
RUN wget https://packages.couchbase.com/clients/c/repos/deb/couchbase.key \
    && apt-key add ./couchbase.key \
    && rm ./couchbase.key \
    && apt-add-repository "deb https://packages.couchbase.com/clients/c/repos/deb/debian10 buster buster/main" \
    && apt-get update

# Install libcouchbase
RUN apt-get install -y libcouchbase3 \
    libcouchbase-dev libcouchbase3-tools \
    libcouchbase-dbg libcouchbase3-libev \
    libcouchbase3-libevent

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install couchbase sdk
RUN pecl install couchbase

# Create ini file
RUN echo extension=couchbase.so >> /usr/local/etc/php/php.ini

RUN composer install

# Expose ports
EXPOSE 8080

# Clear config cache
RUN php artisan config:clear

# Set the entrypoint
ENTRYPOINT ["./wait-for-couchbase.sh", "php", "artisan", "serve", "--host 0.0.0.0", "--port 8080"]
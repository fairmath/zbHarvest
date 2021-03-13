FROM php:7.4-cli
RUN apt-get update && apt-get install -y \
        libzip-dev \
        zip \
        libxml2-dev \
  && docker-php-ext-install zip \
  && docker-php-ext-install simplexml \
  && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /work
COPY composer.json /work
COPY composer.lock /work
RUN composer install
COPY run.php /work
CMD [ "php", "./run.php" ]
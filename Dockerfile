FROM php:7.4-cli-alpine

WORKDIR /var/www/html

COPY --chown=www-data:www-data src /var/www/html

RUN wget https://getcomposer.org/download/latest-2.x/composer.phar --output-document=/usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer && \
    composer install --no-dev

ENTRYPOINT ["tail", "-f", "/dev/null"]
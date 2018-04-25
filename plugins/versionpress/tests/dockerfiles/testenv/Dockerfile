#
# Version: 2 (!! when updating this Dockerfile, bump the version here and also in docker-compose.yml)
#
FROM prooph/php:7.2-cli-xdebug

RUN set -ex \
    && apk add --no-cache bash sudo git \
    # `wp db` commands depend on mysql client
    && apk add --no-cache mysql-client \
    && apk add --no-cache libjpeg-turbo-dev libpng-dev \
    # official PHP image ships without mysqli ¯\_(ツ)_/¯
    && docker-php-ext-install mysqli \
    && docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr \
    && docker-php-ext-install gd

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    sudo mv wp-cli.phar /usr/local/bin/wp

RUN mkdir -p /var/www/html && chown -f -R www-data:www-data /var/www/html

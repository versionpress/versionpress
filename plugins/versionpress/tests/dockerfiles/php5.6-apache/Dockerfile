#
# Version: 1 (!! when updating this Dockerfile, bump the version here and also in docker-compose.yml)
#
# Similar to https://github.com/docker-library/wordpress/blob/master/php5.6/apache/Dockerfile
# but we don't want WordPress to be automatically installed.
FROM php:5.6-apache

# install the PHP extensions we need
RUN set -ex; \
    \
    apt-get update; \
    apt-get install -y \
        libjpeg-dev \
        libpng12-dev \

    && apt-get install -y git \

    ; \
    rm -rf /var/lib/apt/lists/*; \
    \
    docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr; \
    docker-php-ext-install gd mysqli opcache

RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN a2enmod rewrite expires

VOLUME /var/www/html

CMD chown -f -R www-data:www-data /var/www/html ; apache2-foreground

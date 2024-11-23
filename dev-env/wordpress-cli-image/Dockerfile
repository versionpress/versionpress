FROM wordpress:cli

# IMPORTANT: this image is Alpine-based where www-data is UID 82 while in Debian-based WordPress image,
# it's 33. Both will be reading and writing to `/var/www/html` so we'll be using UID 33 in this Dockerfile
# to avoid any permission issues. See e.g. docker-library/wordpress#256.
#
# Never use www-data in this Dockerfile!

# ENV XDEBUG_VERSION=2.6.0

# Switching to root first, `wordpress:cli` sets the user to www-data.
USER root

# COPY cert_custom.pem /usr/local/share/ca-certificates/cert_custom.crt
# RUN  update-ca-certificates

# Install newer version of Git, required for VersionPress.
RUN apk update && apk add --no-cache git

RUN apk add --no-cache $PHPIZE_DEPS \
    && apk add --update linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && { \
    echo 'xdebug.client_host=host.docker.internal'; \
    echo 'xdebug.client_port=9003'; \
    echo 'xdebug.mode=debug'; \
    echo 'xdebug.profiler_enable = 0'; \
    } >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Docker mounts all volumes as root (moby/moby#2259) but we'll be running as UID 33. As a workaround,
# we're going to create all mount points ahead of time.
#
# ! Make sure the list of folders matches volumes in `docker-compose-test.yml`.
RUN set -ex; \
    for f in /var/www/html /var/www/.wp-cli /var/opt/versionpress/logs; \
    do \
    mkdir -p "$f"; \
    chown -R 33:33 "$f"; \
    done

# Set the final runtime user
USER 33

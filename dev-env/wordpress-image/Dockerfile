FROM wordpress:php8.2-apache
# Install prerequisites for WP-CLI & VersionPress
RUN apt-get update \
  && apt-get install -y sudo less default-mysql-client \
  && apt-get install -y git \
  && rm -rf /var/lib/apt/lists/*

#COPY cert_custom.pem /usr/local/share/ca-certificates/cert_custom.crt
#RUN  update-ca-certificates


# Install WP-CLI
RUN curl -o /bin/wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN { \
  echo '#!/bin/sh'; \
  echo '# Run WP-CLI as www-data so that permissions remain correct'; \
  echo 'sudo -u www-data /bin/wp-cli.phar "$@"'; \
  } > /bin/wp
RUN chmod +x /bin/wp-cli.phar && chmod +x /bin/wp

# Xdebug
# Adapted from https://github.com/johnrom/docker-wordpress-wp-cli-xdebug/blob/8b87351f9b65b95734fd726e97deff45ec8c8dfc/Dockerfile
RUN yes |  pecl install xdebug \
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
  for f in /var/www /var/www/.wp-cli /var/opt/versionpress/logs; \
  do \
  mkdir -p "$f"; \
  chown -R 33:33 "$f"; \
  done

# Set the final runtime user
USER 33

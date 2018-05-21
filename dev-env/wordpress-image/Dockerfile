FROM wordpress:php7.2-apache

# Install prerequisites for WP-CLI & VersionPress
RUN apt-get update \
  && apt-get install -y sudo less mysql-client \
  && apt-get install -y git \
  && rm -rf /var/lib/apt/lists/*

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
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=0" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.profiler_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini

# Make port that Apache runs on customizable – based on https://github.com/docker-library/php/issues/94#issuecomment-320695125
# Port mappings like "8089:80" won't really work for WordPress due to redirections to 'home'
# so this customization allows to do something like `-e PORT 8089 -p 8089:8089`.
ENV PORT 80
ENTRYPOINT []
CMD sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && docker-entrypoint.sh apache2-foreground

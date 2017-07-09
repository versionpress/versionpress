FROM wordpress:4.8-php5.6-apache

# Requirements for WP-CLI & VersionPress (separate lines to make it clear)
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
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=0" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.profiler_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini

COPY vp-entrypoint.sh /usr/local/bin/
ENTRYPOINT ["vp-entrypoint.sh"]
CMD ["apache2-foreground"]

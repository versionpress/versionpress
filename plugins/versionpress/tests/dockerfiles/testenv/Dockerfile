FROM prooph/php:5.6-cli-xdebug

RUN apt-get update \

  # `wp db` commands depend on mysql client
  && apt-get install -y mysql-client \

  # official PHP image ships without mysqli ¯\_(ツ)_/¯
  && docker-php-ext-install mysqli \

  && apt-get install -y git \
  && apt-get install -y sudo \

  && rm -rf /var/lib/apt /tmp/* /var/tmp/*

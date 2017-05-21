FROM wordpress:php5.6

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

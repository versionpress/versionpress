FROM wordpress:cli

# Switching to root first, `wordpress:cli` sets the user to www-data.
USER root

# Install newer version of Git, required for VersionPress.
RUN set -ex; \
    apk add --no-cache git

# Docker mounts all volumes as `root` while the parent image uses www-data.
# This creates issues with file permissions, for example, WP-CLI cannot write
# to a root-owned /var/www/.wp-cli. A workaround is to create all folders that
# are going to become mount points here, see moby/moby#2259 for related discussion.
#
# ! Make sure the list of folders matches volumes in docker-compose.yml.
RUN set -ex; \
    for f in /var/www/html /var/www/.wp-cli /opt/logs; \
    do \
      mkdir -p "$f"; \
	  chown -R www-data:www-data "$f"; \
    done

# Set the final runtime user again
USER www-data

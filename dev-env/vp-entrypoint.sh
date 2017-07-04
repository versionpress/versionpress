#!/bin/bash

# We want to install WordPress after the whole stack starts up and it turns out that the parent
# [entrypoint](https://github.com/docker-library/wordpress/blob/master/php5.6/apache/docker-entrypoint.sh)
# contains logic that waits for DB to be ready. Let's inject our code just before the end of that file.


# Inject code into docker-entrypoint.sh
ORIG_ENTRYPOINT='/usr/local/bin/docker-entrypoint.sh'
ORIG_ENTRYPOINT_FINAL_LINE='exec "$@"'

sed -i "/$ORIG_ENTRYPOINT_FINAL_LINE/d" "$ORIG_ENTRYPOINT"

cat <<'CODE_TO_INJECT' >> "$ORIG_ENTRYPOINT"
wp core install \
    --url="$WORDPRESS_SITEURL" \
    --title="$WORDPRESS_SITE_TITLE" \
    --admin_user="$WORDPRESS_ADMIN_USER" \
    --admin_password="$WORDPRESS_ADMIN_PASSWORD" \
    --admin_email="$WORDPRESS_ADMIN_EMAIL" \
    --skip-email

echo WordPress site is running at "$WORDPRESS_SITEURL"

CODE_TO_INJECT

echo $ORIG_ENTRYPOINT_FINAL_LINE >> "$ORIG_ENTRYPOINT"


# Run it
source "$ORIG_ENTRYPOINT" "$@"

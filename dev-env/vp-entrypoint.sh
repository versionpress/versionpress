#!/bin/bash

# We want to install WordPress after the whole stack starts up and it turns out that the parent
# [entrypoint](https://github.com/docker-library/wordpress/blob/master/php5.6/apache/docker-entrypoint.sh)
# contains logic that waits for DB to be ready. Let's inject our code just before the end of that file.

ORIG_ENTRYPOINT='/usr/local/bin/docker-entrypoint.sh'

# '~' characters are used to indicate newline (sed is single-line only, `tr` will fix up newlines later)
CODE_TO_INJECT=$(cat <<'CODE_TO_INJECT'
wp core install \
    --url="$WORDPRESS_SITEURL" \
    --title="$WORDPRESS_SITE_TITLE" \
    --admin_user="$WORDPRESS_ADMIN_USER" \
    --admin_password="$WORDPRESS_ADMIN_PASSWORD" \
    --admin_email="$WORDPRESS_ADMIN_EMAIL" \
    --skip-email
~
~
echo WordPress installed at "$WORDPRESS_SITEURL"
~
~
CODE_TO_INJECT
)

ORIG_CODE_TO_LOOK_FOR='exec "$@"'

# https://stackoverflow.com/a/407649/21728
sed -i -e "s/$ORIG_CODE_TO_LOOK_FOR/$(echo $CODE_TO_INJECT$ORIG_CODE_TO_LOOK_FOR | sed -e 's/\\/\\\\/g; s/\//\\\//g; s/&/\\\&/g')/g" "$ORIG_ENTRYPOINT"

# fix the newlines using `tr`
cat "$ORIG_ENTRYPOINT" | tr '~' '\n' | cat > "$ORIG_ENTRYPOINT"

# Finally, run the entrypoint
source "$ORIG_ENTRYPOINT" "$@"

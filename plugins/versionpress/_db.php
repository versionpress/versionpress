<?php
/**
 * Plugin initialization saves this file as `wp-content/db.php`. This means that it is executed
 * with every request, both public and admin.
 *
 * VersionPress uses this primarily to overwrite the `$wpdb` instance with its mirroring implementation
 * but we also use this to auto-load all the classes etc. This might be a little wasteful
 * and should be looked at, see WP-41.
 */
/** @noinspection PhpIncludeInspection */
require_once(WP_CONTENT_DIR . '/plugins/versionpress/bootstrap.php');

<?php

/**
 * Uninstallation script for VersionPress. Most things already happened in the
 * `versionpress_admin_post_deactivation_keep_repo` hook; here, we just delete the .git repo.
 */

defined('WP_UNINSTALL_PLUGIN') or die('Direct access not allowed');

require_once('src/utils/FileSystem.php');

FileSystem::setPermisionsForGitDirectory(ABSPATH);
FileSystem::getWpFilesystem()->rmdir(ABSPATH . '.git', true);


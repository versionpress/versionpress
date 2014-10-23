<?php

/**
 * Uninstallation script for VersionPress. Most things already happened in the
 * `versionpress_admin_post_confirm_deactivation` hook; here, we just delete the .git repo.
 */

defined('WP_UNINSTALL_PLUGIN') or die('Direct access not allowed');

require_once(dirname(__FILE__) . '/bootstrap.php');

if (UninstallationUtil::uninstallation_should_remove_git_repo()) {


    $backupPath = WP_CONTENT_DIR . '/backup/.git-backup-' . date("YmdHis");
    mkdir(basename($backupPath), 0777, true);

    FileSystem::setPermisionsForGitDirectory(ABSPATH);
    FileSystem::getWpFilesystem()->move(ABSPATH . '.git', $backupPath, true);
}


<?php

/**
 * Uninstallation script for VersionPress. Most things already happened in the
 * `vp_admin_post_confirm_deactivation` hook; here, we just move the .git repo.
 *
 * Testing tip: place exit() at the end of the script and then in the browser
 * just go back and try again.
 *
 * @see vp_admin_post_confirm_deactivation()
 */

use VersionPress\Utils\FileSystem;
use VersionPress\Utils\SecurityUtils;
use VersionPress\Utils\UninstallationUtil;

defined('WP_UNINSTALL_PLUGIN') or die('Direct access not allowed');

require_once(dirname(__FILE__) . '/bootstrap.php');

if (UninstallationUtil::uninstallationShouldRemoveGitRepo()) {

    $backupsDir = WP_CONTENT_DIR . '/vpbackups';
    if (!file_exists($backupsDir)) {
        FileSystem::mkdir($backupsDir);
        file_put_contents($backupsDir . '/.gitignore', 'git-backup-*');
        SecurityUtils::protectDirectory($backupsDir);
    }

    $backupPath = $backupsDir . '/git-backup-' . date("YmdHis");

    FileSystem::rename(VP_PROJECT_ROOT . '/.git', $backupPath, true);

    $productionGitignore = VP_PROJECT_ROOT . '.gitignore';
    $templateGitignore = __DIR__ . '/src/Initialization/.gitignore.tpl';

    if (FileSystem::filesHaveSameContents($productionGitignore, $templateGitignore)) {
        FileSystem::remove($productionGitignore);
    }

}


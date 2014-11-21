<?php

/**
 * Interface to WpAutomation methods and other helper commands. For example, `wp vp-automate start-over`
 * may be used to speed up testing of initialization.
 *
 * Example of usage:
 *
 *     wp --require="c:\wpsite\wp-content\plugins\versionpress\tests\automation\vp-automate.php" vp-automate start-over
 *
 */

/**
 * Internal VersionPress automation commands. Some of them depend on tests-config.ini.
 */
class VpAutomateCommand extends WP_CLI_Command {

    /**
     * Removes everything created by VP, leaves site fresh for new testing.
     *
     * ## DETAILS
     *
     * Basically does plugin deactivation plus removing the Git repo. Deactivation
     * does things like removing `vpdb`, `db.php`, VersionPress db tables etc.
     *
     * @subcommand start-over
     */
    public function startOver($args, $assoc_args) {
        vp_admin_post_confirm_deactivation();
        FileSystem::getWpFilesystem()->rmdir(ABSPATH . '.git', true);
    }

}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp-automate', 'VpAutomateCommand');
}
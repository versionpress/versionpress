<?php

namespace VersionPress\Cli;

use Symfony\Component\Process\Process;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\GitRepository;
use VersionPress\Synchronizers\SynchronizationProcess;
use WP_CLI;
use WP_CLI_Command;
use wpdb;

/**
 * Internal VersionPress commands.
 *
 * ## USAGE
 *
 * These internal commands are not registered with WP-CLI automatically like the "public"
 * `wp vp` commands in versionpress.php. You have to manually require the file, e.g.:
 *
 *     wp --require=wp-content/plugins/versionpress/src/Cli/vp-internal.php vp-internal ...
 *
 * These internal commands are mostly used by public `wp vp` commands.
 *
 */
class VPInternalCommand extends WP_CLI_Command {

    /**
     * Finishes clone operation
     *
     * --truncate-options
     * : By default, options table is not truncated. This flag changes the behavior.
     *
     * @synopsis [--truncate-options]
     *
     * @subcommand finish-init-clone
     *
     */
    public function finishInitClone($args, $assoc_args) {
        global $versionPressContainer;

        // Truncate tables

        /** @var wpdb $wpdb */
        $wpdb = $versionPressContainer->resolve(VersionPressServices::WPDB);
        $tables = $wpdb->tables();

        if (!isset($assoc_args["truncate-options"])) {
            $tables = array_filter($tables, function ($table) use ($wpdb) { return $table !== $wpdb->options; });
        }

        foreach ($tables as $table) {
            $truncateCmd = "TRUNCATE TABLE `$table`";
            $wpdb->query($truncateCmd);
        }


        // Create VersionPress tables

        /** @var \VersionPress\Initialization\Initializer $initializer */
        $initializer = $versionPressContainer->resolve(VersionPressServices::INITIALIZER);
        $initializer->createVersionPressTables();

        WP_CLI::success("VersionPress tables created");


        // Run synchronization

        /** @var SynchronizationProcess $syncProcess */
        $syncProcess = $versionPressContainer->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS);
        $syncProcess->synchronize();
        $this->flushRewriteRules();
        WP_CLI::success("Database synchronized");

    }

    /**
     * Turns on or off the maintenance mode.
     *
     * <mode>
     * : Desired state of maintenance mode. Possible values are 'on' or 'off'.
     *
     */
    public function maintenance($args) {
        $mode = $args[0];
        if ($mode === 'on') {
            vp_enable_maintenance();
        } else {
            vp_disable_maintenance();
        }
    }

    /**
     * Finishes `vp push`
     *
     * @subcommand finish-push
     *
     */
    public function finishPush($args, $assoc_args) {
        global $versionPressContainer;

        // Update working copy
        $resetCommand = "git reset --hard";
        $process = VPCommandUtils::exec($resetCommand);
        if (!$process->isSuccessful()) {
            WP_CLI::error("Working directory couldn't be reset");
        }

        // Run synchronization
        /** @var SynchronizationProcess $syncProcess */
        $syncProcess = $versionPressContainer->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS);
        $syncProcess->synchronize();
        $this->flushRewriteRules();
    }

    private function flushRewriteRules() {
        set_transient('vp_flush_rewrite_rules', 1);
        file_get_contents(get_home_url());
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp-internal', 'VersionPress\Cli\VPInternalCommand');
}

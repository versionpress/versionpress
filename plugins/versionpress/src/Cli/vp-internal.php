<?php

namespace VersionPress\Cli;

use VersionPress\Database\VpidRepository;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\MergeDriverInstaller;
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


        // Install Custom merge driver

        MergeDriverInstaller::installMergeDriver(VP_PROJECT_ROOT, VERSIONPRESS_PLUGIN_DIR, VERSIONPRESS_MIRRORING_DIR);
        WP_CLI::success("Git merge driver added");

        
        // Run synchronization

        /** @var SynchronizationProcess $syncProcess */
        $syncProcess = $versionPressContainer->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS);
        $syncProcess->synchronizeAll();
        vp_flush_regenerable_options();
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
        $syncProcess->synchronizeAll();

        vp_flush_regenerable_options();
        vp_disable_maintenance();
        $this->flushRewriteRules();
        vp_enable_maintenance();
    }

    private function flushRewriteRules() {
        set_transient('vp_flush_rewrite_rules', 1);
        /**
         * @see VPCommand::flushRewriteRules
         * @noinspection PhpUsageOfSilenceOperatorInspection
         */
        @file_get_contents(get_home_url());
    }

    /**
     * Gets `id` of an entity from `vp_id` table
     *
     * @subcommand get-entity-id
     *
     * @synopsis --vpid=<vpid>
     *
     */
    public function getEntityId($args = array(), $assoc_args = array()) {
        global $versionPressContainer;
        /** @var wpdb $wpdb */
        $wpdb = $versionPressContainer->resolve(VersionPressServices::WPDB);
        $sql = "SELECT ID FROM " . $wpdb->prefix . "vp_id WHERE vp_id=UNHEX('" . $assoc_args["vpid"] . "')";
        $newId = $wpdb->get_col($sql);
        if (isset($newId[0])) {
            echo $newId[0];

        }
    }

    /**
     * Gets `vp_id` Guid of an entity from id and entity name
     *
     * @subcommand get-entity-vpid
     *
     * @synopsis --id=<id> --name=<name>
     *
     */
    public function getEntityVpid($args = array(), $assoc_args = array()) {
        global $versionPressContainer;
        /** @var wpdb $wpdb */
        $wpdb = $versionPressContainer->resolve(VersionPressServices::WPDB);
        /** @var VpidRepository $vpIdRepository */
        $vpIdRepository = $versionPressContainer->resolve(VersionPressServices::VPID_REPOSITORY);
        echo $vpIdRepository->getVpidForEntity($assoc_args["name"], $assoc_args["id"]);
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp-internal', 'VersionPress\Cli\VPInternalCommand');
}

<?php

namespace VersionPress\Cli;

use VersionPress\Database\Database;
use VersionPress\Database\VpidRepository;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\MergeDriverInstaller;
use VersionPress\Initialization\Initializer;
use VersionPress\Synchronizers\SynchronizationProcess;
use VersionPress\Utils\WpConfigEditor;
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

        /** @var Database $database */
        $database = $versionPressContainer->resolve(VersionPressServices::DATABASE);
        $tables = $database->tables();

        if (!isset($assoc_args["truncate-options"])) {
            $tables = array_filter($tables, function ($table) use ($database) { return $table !== $database->options; });
        }

        foreach ($tables as $table) {
            $truncateCmd = "TRUNCATE TABLE `$table`";
            $database->query($truncateCmd);
        }


        // Create VersionPress tables

        /** @var \VersionPress\Initialization\Initializer $initializer */
        $initializer = $versionPressContainer->resolve(VersionPressServices::INITIALIZER);
        $initializer->createVersionPressTables();

        WP_CLI::success("VersionPress tables created");


        // Install Custom merge driver

        MergeDriverInstaller::installMergeDriver(VP_PROJECT_ROOT, VERSIONPRESS_PLUGIN_DIR, VP_VPDB_DIR);
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
     * Finishes the update with new version
     *
     * @subcommand finish-update
     */
    public function finishUpdate($args, $assoc_args) {
        global $versionPressContainer;
        activate_plugins("versionpress/versionpress.php");
        WP_CLI::success('Re-activated VersionPress');

        /** @var Initializer $initializer */
        $initializer = $versionPressContainer->resolve(VersionPressServices::INITIALIZER);
        $initializer->initializeVersionPress(true);

        WP_CLI::success('VersionPress is tracking your site');
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
         */
        wp_remote_get(get_home_url());
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
        /** @var Database $database */
        $database = $versionPressContainer->resolve(VersionPressServices::DATABASE);
        $sql = "SELECT ID FROM " . $database->vp_id . " WHERE vp_id=UNHEX('" . $assoc_args["vpid"] . "')";
        $newId = $database->get_col($sql);
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

    /**
     * Sets or updates constant or variable in wp-config.php
     *
     * ## OPTIONS
     *
     * <constant>
     * : Name of constant or variable that will be changed.
     *
     * <value>
     * : Desired value. Supported types are: string, int, float and bool.
     *
     * --plain
     * : The value will be used as is - without type detection, quoting etc.
     *
     * --variable
     * : Will set a variable instead of constant. Useful for $table_prefix.
     *
     * --common
     * : The constant / variable will be set in wp-config.common.php.
     *
     * @subcommand update-config
     *
     * @synopsis <constant> <value> [--plain] [--variable] [--common]
     *
     * @when before_wp_load
     */
    public function updateConfig($args = array(), $assoc_args = array()) {
        $wpConfigPath = \WP_CLI\Utils\locate_wp_config();
        $updateCommonConfig = isset($assoc_args['common']);

        if ($updateCommonConfig) {
            $wpConfigPath = dirname($wpConfigPath) . '/wp-config.common.php';
        }

        if ($wpConfigPath === false) {
            WP_CLI::error('Config file does not exist. Please run `wp core config` first.');
        }

        require_once __DIR__ . '/VPCommandUtils.php';
        require_once __DIR__ . '/../Utils/WpConfigEditor.php';

        $constantOrVariableName = $args[0];
        $isVariable = isset($assoc_args['variable']);
        $usePlainValue = isset($assoc_args['plain']);
        $value = $usePlainValue ? $args[1] : VPCommandUtils::fixTypeOfValue($args[1]);

        $wpConfigEditor = new WpConfigEditor($wpConfigPath, $updateCommonConfig);

        try {
            if ($isVariable) {
                $wpConfigEditor->updateConfigVariable($constantOrVariableName, $value, $usePlainValue);
            } else {
                $wpConfigEditor->updateConfigConstant($constantOrVariableName, $value, $usePlainValue);
            }
        } catch (\Exception $e) {
            WP_CLI::error('Cannot find place for defining the ' . ($isVariable ? 'variable' : 'constant')  . '. Config was probably edited manually.');
        }
    }

    /**
     * Used before pull
     *
     * @subcommand commit-frequently-written-entities
     */
    public function commitFrequentlyWrittenEntities($args = array(), $assoc_args = array()) {
        vp_commit_all_frequently_written_entities();
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp-internal', 'VersionPress\Cli\VPInternalCommand');
}

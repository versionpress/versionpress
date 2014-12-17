<?php

namespace VersionPress\Cli;
use VersionPress\DI\VersionPressServices;
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
 *     wp --require=path/to/this/vp-internal.php vp-internal ...
 *
 * These internal commands are mostly used by public `wp vp` commands.
 *
 */
class VPInternalCommand extends WP_CLI_Command {

    /**
     * Initializes a clone
     *
     * ## OPTIONS
     *
     * --name=<name>
     * : Name of the clone.
     *
     * --site-url=<url>
     * : Site URL of the clone
     *
     * --force-db
     * : Normally, init-clone fails if the targed DB already exists. Specify
     * this flag to drop the db first.
     *
     * @synopsis --name=<name> --site-url=<url> [--force-db]
     *
     * @subcommand init-clone
     *
     * @when before_wp_load
     */
    public function initClone($args, $assoc_args) {

        /** @noinspection PhpIncludeInspection */
        require_once(__DIR__ . '/../../../../../wp-config.php');

        $name = $assoc_args['name'];
        $cloneUrl = $assoc_args['site-url'];

        $dbName = DB_NAME . '_' . $name;

        // 1) Create a new Git branch here
        $createBranchCommand = 'git checkout -b ' . escapeshellarg($name);
        $process = new \Symfony\Component\Process\Process($createBranchCommand);
        $process->run();

        if (!$process->isSuccessful()) {
            WP_CLI::error("Failed creating branch on clone, message: " . $process->getErrorOutput());
        } else {
            WP_CLI::success("New Git branch created");
        }


        // 2) Update wp-config
        $wpConfigFile = ABSPATH . 'wp-config.php';
        $config = file_get_contents($wpConfigFile);

        // http://regex101.com/r/fS0zG2/1 - just remove the "g" modifier which is there for testing only
        $config = preg_replace("/^(define\\s*\\(\\s*['\"]DB_NAME['\"]\\s*\\,\\s*['\"])(.*)(['\"].*)$/m", "$1$dbName$3", $config, 1);

        file_put_contents($wpConfigFile, $config);
        WP_CLI::success("wp-config.php updated");


        // 3) Create empty db

        if (array_key_exists("force-db", $assoc_args)) {
            $dropDbCmd = 'wp db drop --yes';
            $process = new \Symfony\Component\Process\Process($dropDbCmd);
            $process->run();
            if ($process->isSuccessful()) {
                WP_CLI::success("Database dropped");
            } else {
                // Here, most probably the database could not be dropped because it didn't
                // exist. That's fine, we wanted to delete it anyway.
            }
        }


        $createDbCmd = 'wp db create';
        $process = new \Symfony\Component\Process\Process($createDbCmd);
        $process->run();
        if (!$process->isSuccessful()) {
            WP_CLI::log("Failed creating database. If the problem is existing db, try running this with --force-db flag.");
            WP_CLI::error($process->getOutput()); // WPCLI uses STDOUT instead of STDERR
        } else {
            WP_CLI::success("Database created");
        }


        // 4) Create WP tables

        $createWpTablesCmd = 'wp core install --url=' . escapeshellarg($cloneUrl) . ' --title=x --admin_user=x --admin_password=x --admin_email=x@example.com';
        $process = new \Symfony\Component\Process\Process($createWpTablesCmd);
        $process->run();
        if (!$process->isSuccessful()) {
            WP_CLI::log("Failed creating WP tables.");
            WP_CLI::error($process->getOutput());
        } else {
            WP_CLI::success("WP tables created");
        }

        // ... we need to truncate sample data (everything except `options`)


        // Remaining steps are done in `vp-internal finish-init-clone` where WP + VP is fully loaded

        $finishInitCloneCmd = 'wp --require=' . escapeshellarg(__FILE__) . ' vp-internal finish-init-clone';

        $process = new \Symfony\Component\Process\Process($finishInitCloneCmd);
        $process->run();
        if (!$process->isSuccessful()) {
            WP_CLI::log($process->getOutput());
            WP_CLI::error("Could not finish clone initialization");
        } else {
            WP_CLI::log($process->getOutput());
        }


    }

    /**
     * Finishes init-clone operation
     *
     * @subcommand finish-init-clone
     *
     */
    public function finishInitClone($args, $assoc_args) {


        // 5) Truncate tables

        /** @var wpdb $wpdb */
        global $wpdb;
        $sql = "SELECT concat('TRUNCATE TABLE `', TABLE_NAME, '`;') FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME NOT LIKE '%options';";
        $results = $wpdb->get_results($sql, ARRAY_N);

        foreach ($results as $subResult) {
            $truncateCmd = $subResult[0];
            $wpdb->query($truncateCmd);
        }


        // 6) Create VersionPress tables

        global $versionPressContainer;
        /** @var \VersionPress\Initialization\Initializer $initializer */
        $initializer = $versionPressContainer->resolve(VersionPressServices::INITIALIZER);
        $initializer->createVersionPressTables();

        WP_CLI::success("VersionPress tables created");


        // 7) Run synchronization

        /** @var SynchronizationProcess $syncProcess */
        $syncProcess = $versionPressContainer->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS);
        $syncProcess->synchronize();
        WP_CLI::success("Git -> db synchronization run");

    }

}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp-internal', 'VersionPress\Cli\VPInternalCommand');
}

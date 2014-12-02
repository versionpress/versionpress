<?php

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
class VPInternalCommand extends WP_CLI_Command
{

    /**
     * Initializes a clone
     *
     * ## OPTIONS
     *
     * --name=<name>
     * : Name of the clone.
     *
     * --force-db
     * : Normally, init-clone fails if the targed DB already exists. Specify
     * this flag to drop the db first.
     *
     * @synopsis --name=<name> [--force-db]
     *
     * @subcommand init-clone
     */
    public function initClone($args = array(), $assoc_args = array()) {
        $name = $assoc_args['name'];
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
        $config = preg_replace("/^(define.*\\(.*['\"]DB_NAME['\"]\\,.*['\"])(.*)(['\"].*)$/m", "$1$dbName$3", $config, 1);
        file_put_contents($wpConfigFile, $config);
        WP_CLI::success("wp-config.php updated");


        // 3) Create the db

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
        $createWpTablesCmd = 'wp core install --url=x --title=x --admin_user=x --admin_password=x --admin_email=x@example.com';
        $process = new \Symfony\Component\Process\Process($createWpTablesCmd);
        $process->run();
        if (!$process->isSuccessful()) {
            WP_CLI::log("Failed creating WP tables.");
            WP_CLI::error($process->getErrorOutput());
        } else {
            WP_CLI::success("WP tables created");
        }

        // 5) Run synchronization
        global $versionPressContainer;
        /** @var SynchronizationProcess $syncProcess */
        $syncProcess = $versionPressContainer->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS);
        $syncProcess->synchronize();
        WP_CLI::success("Git -> db synchronization run");


    }

}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp-internal', 'VPInternalCommand');
}

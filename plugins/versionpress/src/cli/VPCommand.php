<?php

/**
 * VersionPress CLI commands.
 */
class VPCommand extends WP_CLI_Command
{
    const NAME_FLAG = "name";

    /**
     * Clone the site to a separate folder, db and Git branch.
     *
     * ## OPTIONS
     *
     * --name=<name>
     * : Name of the clone. Will be used as a suffix of the cloned folder, new
     * database and the name of a new Git branch. For example, if the original
     * site is "wpsite" and the `name` parameter is "test", the clone will be
     * in the folder called "wpsite_test", the database will be "<orig>_test"
     * and the Git branch name will be "test".
     *
     *
     * @subcommand clone
     */
    public function clone_($args = array(), $flags = array()) {
        $name = $flags[self::NAME_FLAG];
        $this->doClone($name);
    }

    /**
     * Creates a new db
     *
     * ## OPTIONS
     *
     * --name=<name>
     * : New db name
     *
     * @subcommand new-db
     */
    public function newDb_($args = array(), $flags = array()) {
        $dbName = $flags[self::NAME_FLAG];
        $this->doNewDb($dbName);
    }

    private function doClone($name) {

        // 1) Clone the repo
        $currentWpPath = get_home_path();
        $clonePath = sprintf("%s/%s_%s", dirname($currentWpPath), basename($currentWpPath), $name);
        Git::cloneRepository($currentWpPath, $clonePath);

        // 2) Create a new Git branch there
        $createBranchCommand = 'git checkout -b ' . escapeshellarg($name);
        $process = new \Symfony\Component\Process\Process($createBranchCommand, $clonePath);
        $process->run();

        if (!$process->isSuccessful()) {
            WP_CLI::error("Failed creating branch on clone, message: " . $process->getErrorOutput());
        }

        // 3) Create the new db in the clone
        $configureCloneCmd = 'wp --require=' . escapeshellarg($clonePath . '/wp-content/plugins/versionpress/src/cli/VPCommand.php') . ' vp new-db --name=' . escapeshellarg(DB_NAME . '_' . $name);
        $process = new \Symfony\Component\Process\Process($configureCloneCmd, $clonePath);
        $process->run();

        if (!$process->isSuccessful()) {
            WP_CLI::error("Failed executing `wp vp new-db` on clone, message: " . $process->getErrorOutput());
        }


    }

    private function doNewDb($dbName) {

        WP_CLI::log("Doing new-db");

        // 1) Update wp-config
        $wpConfigFile = ABSPATH . 'wp-config.php';
        $config = file_get_contents($wpConfigFile);
        $config = preg_replace("/^(define.*\\(.*['\"]DB_NAME['\"]\\,.*['\"])(.*)(['\"].*)$/m", "$1$dbName$3", $config, 1);
        file_put_contents($wpConfigFile, $config);

        // 2) Create the db
        $createDbCmd = 'wp db create';
        $process = new \Symfony\Component\Process\Process($createDbCmd, ABSPATH);
        $process->run();

        // 3) Run synchronization
        global $versionPressContainer;
        /** @var SynchronizationProcess $syncProcess */
        $syncProcess = $versionPressContainer->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS);
        $syncProcess->synchronize();
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp', 'VPCommand');
}
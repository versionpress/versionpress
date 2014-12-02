<?php

/**
 * VersionPress CLI commands.
 */
class VPCommand extends WP_CLI_Command
{

    /**
     * Clones site to a new folder, database and Git branch.
     *
     * ## OPTIONS
     *
     * --name=<name>
     * : Name of the clone. Used as a suffix for new folder, a suffix for new
     * database and a name of the new Git branch. See example below.
     *
     * --force
     * : Forces cloning even if the target folder / database already exist.
     *
     * ## EXAMPLES
     *
     * Let's say we have a site in folder `wp01` that uses database called `wp01db`. The command
     *
     *     wp vp clone --name=test
     *
     * creates a copy of the site in `wp01_test`, a new Git branch called `test`
     * and a new database `wp01db_test`.
     *
     * @synopsis --name=<name> [--force]
     *
     * @subcommand clone
     */
    public function clone_($args = array(), $assoc_args = array()) {
        $name = $assoc_args['name'];

        $currentWpPath = get_home_path();
        $clonePath = sprintf("%s/%s_%s", dirname($currentWpPath), basename($currentWpPath), $name);

        if (is_dir($clonePath) && !array_key_exists('force', $assoc_args)) {
            WP_CLI::error("Directory '" . basename($clonePath) . "' already exists. Use --force to overwrite it or use another clone name.");
        }

        if (is_dir($clonePath)) {
            $rmDirResult = FileSystem::getWpFilesystem()->rmdir($clonePath, true);
            if (!$rmDirResult) {
                // rmdir most often fails, maybe because of Git repo in it? try some other removal strategy
                WP_CLI::error("Could not delete directory '" . basename($clonePath) . "'. Please do it manually.");
            }
        }

        Git::cloneRepository($currentWpPath, $clonePath);

        WP_CLI::success("Site files cloned");


        $configureCloneCmd = 'wp --require=' . escapeshellarg($clonePath . '/wp-content/plugins/versionpress/src/cli/vp-internal.php');
        $configureCloneCmd .= ' vp-internal init-clone --name=' . escapeshellarg($name);
        if (array_key_exists('force', $assoc_args)) {
            $configureCloneCmd .= ' --force-db';
        }
        $configureCloneCmd .= " --debug";

        $process = new \Symfony\Component\Process\Process($configureCloneCmd, $clonePath);
        $process->run();

        if (!$process->isSuccessful()) {
            WP_CLI::log($process->getOutput()); // WP-CLI sends it to STDOUT, not STDERR
            WP_CLI::error("Initializing clone failed");
        } else {
            WP_CLI::log($process->getOutput());
        }

        WP_CLI::success("Cloning done. Find your clone in '" . basename($clonePath) . "'.");

    }

}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp', 'VPCommand');
}

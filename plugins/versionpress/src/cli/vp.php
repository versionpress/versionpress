<?php
// NOTE: VersionPress must be fully activated for these commands to be available

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
        $cloneDirName = sprintf("%s_%s", basename($currentWpPath), $name);
        $clonePath = dirname($currentWpPath) . '/' . $cloneDirName;
        $cloneUrl = $this->getCloneUrl(get_site_url(), basename($currentWpPath), $cloneDirName);

        if (is_dir($clonePath) && !array_key_exists('force', $assoc_args)) {
            WP_CLI::error("Directory '" . basename($clonePath) . "' already exists. Use --force to overwrite it or use another clone name.");
        }

        if (is_dir($clonePath)) {
            try {
                FileSystem::remove($clonePath);
            } catch (\Symfony\Component\Filesystem\Exception\IOException $e) {
                WP_CLI::error("Could not delete directory '" . basename($clonePath) . "'. Please do it manually.");
            }
        }

        $cloneCommand = sprintf("git clone %s %s", escapeshellarg($currentWpPath), escapeshellarg($clonePath));

        $process = new \Symfony\Component\Process\Process($cloneCommand, $currentWpPath);
        $process->run();

        if (!$process->isSuccessful()) {
            WP_CLI::log($process->getErrorOutput());
            WP_CLI::error("Cloning Git repo failed");
        } else {
            WP_CLI::log($process->getOutput());
        }

        WP_CLI::success("Site files cloned");


        $configureCloneCmd = 'wp --require=' . escapeshellarg($clonePath . '/wp-content/plugins/versionpress/src/cli/vp-internal.php');
        $configureCloneCmd .= ' vp-internal init-clone --name=' . escapeshellarg($name);
        $configureCloneCmd .= ' --site-url=' . escapeshellarg($cloneUrl);
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

    /**
     * Examples (clone name "test"):
     *
     *   http://localhost/vp01  ->  http://localhost/vp01_test
     *   http://vp01            ->  http://vp01_test
     *   http://www.vp01.dev    ->  http://www.vp01_test.dev
     *
     * @param string $originUrl
     * @param string $originDirName
     * @param string $cloneDirName
     * @return string
     */
    private function getCloneUrl($originUrl, $originDirName, $cloneDirName) {
        return str_replace($originDirName, $cloneDirName, $originUrl);
    }

}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp', 'VPCommand');
}

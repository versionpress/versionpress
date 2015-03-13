<?php
// NOTE: VersionPress must be fully activated for these commands to be available

namespace VersionPress\Cli;

use Nette\Utils\Strings;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Process\Process;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\Reverter;
use VersionPress\Git\RevertStatus;
use VersionPress\Utils\FileSystem;
use WP_CLI;
use WP_CLI_Command;

/**
 * VersionPress CLI commands.
 */
class VPCommand extends WP_CLI_Command {

    /**
     * Configures VersionPress
     *
     * ## OPTIONS
     *
     * <key>
     * : The name of the option to set.
     *
     * [<value>]
     * : The new value. If missing, just prints out the option.
     *
     * @when before_wp_load
     */
    public function config($args, $assoc_args) {

        $configFile = __DIR__ . '/../../vpconfig.neon';
        $configContents = "";
        if (file_exists($configFile)) {
            $configContents = file_get_contents($configFile);
        }

        $configContents = $this->updateConfigValue($configContents, $args[0], $args[1]);

        file_put_contents($configFile, $configContents);

    }

    private function updateConfigValue($config, $key, $value) {

        // We don't use NEON decoding and encoding again as that removes comments etc.

        require_once(__DIR__ . '/../../vendor/nette/utils/src/Utils/Strings.php');

        // General matching: https://regex101.com/r/sE2iB1/1
        // Concrete example: https://regex101.com/r/sE2iB1/2

        $re = "/^($key)(:\\s*)(\\S[^#\\r\\n]+)(\\h+#?.*)?$/m";
        $subst = "$1$2$value$4";

        if (preg_match_all($re, $config, $matches)) {
            $result = preg_replace($re, $subst, $config);
        } else {
            // value was not there, add it to the end
            $result = $config . (Strings::endsWith($config, "\n") ? "" : "\n");
            $result .= "$key: $value\n";
        }

        return $result;

    }


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
            } catch (IOException $e) {
                WP_CLI::error("Could not delete directory '" . basename($clonePath) . "'. Please do it manually.");
            }
        }

        $cloneCommand = sprintf("git clone %s %s", escapeshellarg($currentWpPath), escapeshellarg($clonePath));

        $process = new Process($cloneCommand, $currentWpPath);
        $process->run();

        if (!$process->isSuccessful()) {
            WP_CLI::log($process->getErrorOutput());
            WP_CLI::error("Cloning Git repo failed");
        } else {
            WP_CLI::log($process->getOutput());
        }

        WP_CLI::success("Site files cloned");


        $configureCloneCmd = 'wp --require=' . escapeshellarg($clonePath . '/wp-content/plugins/versionpress/src/Cli/vp-internal.php');
        $configureCloneCmd .= ' vp-internal init-clone --name=' . escapeshellarg($name);
        $configureCloneCmd .= ' --site-url=' . escapeshellarg($cloneUrl);
        if (array_key_exists('force', $assoc_args)) {
            $configureCloneCmd .= ' --force-db';
        }
        $configureCloneCmd .= " --debug";

        $process = new Process($configureCloneCmd, $clonePath);
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

    /**
     * Reverts one commit
     *
     * ## OPTIONS
     *
     * <commit>
     * : Hash of commit that will be reverted.
     *
     * ## EXAMPLES
     *
     *     wp vp undo a34bc28
     *
     * @synopsis <commit>
     *
     * @when before_wp_load
     */
    public function undo($args = array(), $assoc_args = array()) {
        global $versionPressContainer;
        /** @var Reverter $reverter */
        $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);

        $status = $reverter->undo($args[0]);

        if ($status === RevertStatus::VIOLATED_REFERENTIAL_INTEGRITY) {
            WP_CLI::error("Violated referential integrity. Objects with missing references cannot be restored. For example we cannot restore comment where the related post was deleted.");
            return;
        }

        if ($status === RevertStatus::MERGE_CONFLICT) {
            WP_CLI::error("Merge conflict. Overwritten changes can not be reverted.");
            return;
        }

        if ($status === RevertStatus::NOT_CLEAN_WORKING_DIRECTORY) {
            WP_CLI::error("The working directory is not clean. Please commit your changes.");
            return;
        }

        WP_CLI::success("Done.");
    }


    /**
     * Rollbacks site to the same state as it was in the specified commit.
     *
     * ## OPTIONS
     *
     * <commit>
     * : Hash of commit.
     *
     * ## EXAMPLES
     *
     *     wp vp rollback a34bc28
     *
     * @synopsis <commit>
     *
     * @when before_wp_load
     *
     */
    public function rollback($args = array(), $assoc_args = array()) {
        global $versionPressContainer;
        /** @var Reverter $reverter */
        $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);

        $status = $reverter->rollback($args[0]);

        if ($status === RevertStatus::NOTHING_TO_COMMIT) {
            WP_CLI::error("Nothing to commit. Current state is the same as the one you want rollback to.");
            return;
        }

        if ($status === RevertStatus::NOT_CLEAN_WORKING_DIRECTORY) {
            WP_CLI::error("The working directory is not clean. Please commit your changes.");
            return;
        }

        WP_CLI::success("Done.");
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp', 'VersionPress\Cli\VPCommand');
}

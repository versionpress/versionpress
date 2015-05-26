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
     * Restores a WP site from Git repo / working directory.
     *
     * ## OPTIONS
     *
     * [--siteurl=<url>]
     * : The address of the restored site. Default: http://localhost/<cwd>
     *
     * [--dbname=<dbname>]
     * : Set the database name.
     *
     * [--dbuser=<dbuser>]
     * : Set the database user.
     *
     * [--dbpass=<dbpass>]
     * : Set the database user password.
     *
     * [--dbhost=<dbhost>]
     * : Set the database host. Default: 'localhost'
     *
     * [--dbprefix=<dbprefix>]
     * : Set the database table prefix. Default: 'wp_'
     *
     * [--dbcharset=<dbcharset>]
     * : Set the database charset. Default: 'utf8'
     *
     * [--dbcollate=<dbcollate>]
     * : Set the database collation. Default: ''
     *
     * [--yes]
     * : Answer yes to the confirmation message.
     *
     * ## DESCRIPTION
     *
     * The simplest possible example just executes `site-restore` without any parameters.
     * The assumptions are:
     *
     *    * The current directory must be reachable from the webserver as http://localhost/<cwd>
     *    * Credentials for the MySQL server are in the wp-config.php
     *
     * The command will then do the following:
     *
     *    * Create a db <dirname>, e.g., 'vp01'
     *    * Optionally configure WordPress to connect to this DB
     *    * Create WordPress tables in it and preconfigure it with site_url and home options
     *    * Run VP synchronizers on the database
     *
     * All DB credentials and site URL are configurable.
     *
     * @subcommand restore-site
     *
     * @when before_wp_load
     */
    public function restoreSite($args, $assoc_args) {
        if (!defined('WP_CONTENT_DIR')) {
            define('WP_CONTENT_DIR', 'xyz'); //doesn't matter, it's just to prevent the NOTICE in the require`d bootstrap.php
        }
        require_once(__DIR__ . '/../../bootstrap.php');

        $process = VPCommandUtils::runWpCliCommand('core', 'is-installed');
        if ($process->isSuccessful()) {
            WP_CLI::confirm("It looks like the site is OK. Do you really want to run the 'restore-site' command?");
            $defaultUrl = trim(VPCommandUtils::runWpCliCommand('option', 'get', array('siteurl'))->getOutput());
        } else {
            $defaultUrl = 'http://localhost/' . basename(getcwd());
        }

        $url = @$assoc_args['siteurl'] ?: $defaultUrl;

        if (!isset($assoc_args['siteurl'])) {
            WP_CLI::confirm("The site URL will be set to '$url'. Proceed?", $assoc_args);
        }

        if (file_exists(ABSPATH . 'wp-config.php')) {
            if ($this->issetConfigOption($assoc_args)) {
                WP_CLI::error("Site settings was loaded from wp-config.php. If you want to reconfigure the site, please delete the wp-config.php file");
            }
        } else {
            $this->configSite($assoc_args);
        }

        $this->createDb($assoc_args);


        // Disable VersionPress tracking
        $dbphpFile = 'wp-content/db.php';
        if (file_exists($dbphpFile)) {
            unlink($dbphpFile);
        }
        // will be restored at the end of this method


        // Create WP tables. The only important thing is site URL, all else will be rewritten later during synchronization.
        $installArgs = array(
            'url' => $url,
            'title' => 'x',
            'admin_user' => 'x',
            'admin_password' => 'x',
            'admin_email' => 'x@example.com',
        );

        $process = VPCommandUtils::runWpCliCommand('core', 'install', $installArgs);
        if (!$process->isSuccessful()) {
            WP_CLI::log("Failed creating WP tables.");
            WP_CLI::error($process->getErrorOutput());
        } else {
            WP_CLI::success("WP tables created");
        }


        // Clean the working copy - restores "db.php" and makes sure the dir is clean
        $cleanWDCmd = 'git reset --hard && git clean -xf wp-content/vpdb';

        $process = VPCommandUtils::exec($cleanWDCmd);
        if (!$process->isSuccessful()) {
            WP_CLI::log("Could not clean working directory");
            WP_CLI::error($process->getErrorOutput());
        } else {
            WP_CLI::success("Working directory reset");
        }


        // The next couple of the steps need to be done after the WP is fully loaded; we use `finish-init-clone` for that
        // The main reason for this is that we need properly set WP_CONTENT_DIR constant for reading from storages
        $process = VPCommandUtils::runWpCliCommand('vp-internal', 'finish-init-clone', array('require' => __DIR__ . '/vp-internal.php'));
        WP_CLI::log($process->getOutput());
        if (!$process->isSuccessful()) {
            WP_CLI::error("Could not finish site restore");
        }
    }

    /**
     * Returns true if there is some config option in the assoc_args.
     *
     * @param $assoc_args
     * @return bool
     */
    private function issetConfigOption($assoc_args) {
        $configOptions = array('dbname', 'dbuser', 'dbpass', 'dbhost', 'dbprefix', 'dbcharset', 'dbcollate');
        $specifiedOptions = array_keys($assoc_args);
        return count(array_intersect($specifiedOptions, $configOptions)) > 0;
    }

    private function createDb($assoc_args, $force = false) {
        $process = $force ? VPCommandUtils::runWpCliCommand('db', 'reset', array('yes' => null)) : VPCommandUtils::runWpCliCommand('db', 'create');

        if (!$process->isSuccessful()) {
            $msg = $process->getErrorOutput();
            if (Strings::contains($msg, '1007')) {
                WP_CLI::confirm('The database already exists. Do you want to drop it?', $assoc_args);
                $this->createDb($assoc_args, true);
            } else {
                WP_CLI::log("Failed creating DB");
            }
        } else {
            WP_CLI::success("DB created");
        }
    }

    private function configSite($assoc_args) {
        $configArgs = array();
        $configArgs['dbname'] = @$assoc_args['dbname'] ?: basename(getcwd());
        $configArgs['dbuser'] = @$assoc_args['dbuser'] ?: 'root';
        $configArgs['dbpass'] = @$assoc_args['dbpass'] ?: '';
        $configArgs['dbhost'] = @$assoc_args['dbhost'] ?: '127.0.0.1';
        $configArgs['dbprefix'] = @$assoc_args['dbprefix'] ?: 'wp_';
        $configArgs['dbcharset'] = @$assoc_args['dbcharset'] ?: 'utf8';
        $configArgs['dbcollate'] = @$assoc_args['dbcollate'] ?: '';


        // Create wp-config.php
        $process = VPCommandUtils::runWpCliCommand('core', 'config', $configArgs);
        if (!$process->isSuccessful()) {
            WP_CLI::log("Failed creating wp-config.php");
            WP_CLI::error($process->getErrorOutput());
        } else {
            WP_CLI::success("wp-config.php created");
        }
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

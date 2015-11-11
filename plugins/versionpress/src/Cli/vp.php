<?php
// NOTE: VersionPress must be fully activated for these commands to be available

// WORD-WRAPPING of the doc comments: 75 chars for option description, 90 chars for everything else,
// see https://github.com/wp-cli/wp-cli/wiki/Commands-Cookbook#longdesc.
// In this source file, wrap long desc at col 97 and option desc at col 84.

namespace VersionPress\Cli;

use Nette\Neon\Neon;
use Nette\Utils\Strings;
use Symfony\Component\Filesystem\Exception\IOException;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\GitRepository;
use VersionPress\Git\Reverter;
use VersionPress\Git\RevertStatus;
use VersionPress\Initialization\WpdbReplacer;
use VersionPress\Synchronizers\SynchronizationProcess;
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

        if (count($args) === 1) {
            $config = Neon::decode($configContents);
            WP_CLI::out(@$config[$args[0]]);
            return;
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
     * --siteurl=<url>
     * : The address of the restored site. Default: 'http://localhost/<cwd>'
     *
     * --dbname=<dbname>
     * : Set the database name.
     *
     * --dbuser=<dbuser>
     * : Set the database user.
     *
     * --dbpass=<dbpass>
     * : Set the database user password.
     *
     * --dbhost=<dbhost>
     * : Set the database host. Default: 'localhost'
     *
     * --dbprefix=<dbprefix>
     * : Set the database table prefix. Default: 'wp_'
     *
     * --dbcharset=<dbcharset>
     * : Set the database charset. Default: 'utf8'
     *
     * --dbcollate=<dbcollate>
     * : Set the database collation. Default: ''
     *
     * --yes
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
     * @synopsis [--siteurl=<url>] [--dbname=<dbname>] [--dbuser=<dbuser>] [--dbpass=<dbpass>] [--dbhost=<dbhost>] [--dbprefix=<dbprefix>] [--dbcharset=<dbcharset>] [--dbcollate=<dbcollate>] [--yes]
     *
     * @subcommand restore-site
     *
     * @when before_wp_load
     */
    public function restoreSite($args, $assoc_args) {

        // Load VersionPress' bootstrap (WP_CONTENT_DIR needs to be defined)
        if (!defined('WP_CONTENT_DIR')) {
            define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
        }
        require_once(__DIR__ . '/../../bootstrap.php');

        // Check if the site is installed
        $process = VPCommandUtils::runWpCliCommand('core', 'is-installed');
        if ($process->isSuccessful()) {
            WP_CLI::confirm("It looks like the site is OK. Do you really want to run the 'restore-site' command?", $assoc_args);
            $defaultUrl = trim(VPCommandUtils::runWpCliCommand('option', 'get', array('siteurl'))->getConsoleOutput());
        } else {
            $defaultUrl = 'http://localhost/' . basename(getcwd());
        }

        $url = @$assoc_args['siteurl'] ?: $defaultUrl;

        // Confirm automatically chosen site URL
        if (!isset($assoc_args['siteurl'])) {
            WP_CLI::confirm("The site URL will be set to '$url'. Proceed?", $assoc_args);
        }

        // Updating wp-config.php
        if (file_exists(ABSPATH . 'wp-config.php')) {
            if ($this->issetConfigOption($assoc_args)) {
                WP_CLI::error("Site settings was loaded from wp-config.php. If you want to reconfigure the site, please delete the wp-config.php file");
            }
        } else {
            $this->configSite($assoc_args);
        }

        // Create or empty database
        $this->prepareDatabase($assoc_args);


        // Disable VersionPress tracking
        if (!defined('WPINC')) {
            define('WPINC', 'wp-includes');
        }
        WpdbReplacer::restoreOriginal();
        unlink(VERSIONPRESS_ACTIVATION_FILE);


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
            WP_CLI::log("Failed creating database tables");
            WP_CLI::error($process->getConsoleOutput());
        } else {
            WP_CLI::success("Database tables created");
        }


        // Restores "wp-db.php", "wp-db.php.original" and ".active"
        $resetCmd = 'git reset --hard';

        $process = VPCommandUtils::exec($resetCmd);
        if (!$process->isSuccessful()) {
            WP_CLI::log("Could not clean working directory");
            WP_CLI::error($process->getConsoleOutput());
        }


        // The next couple of the steps need to be done after the WP is fully loaded; we use `finish-init-clone` for that
        // The main reason for this is that we need properly set WP_CONTENT_DIR constant for reading from storages
        $process = VPCommandUtils::runWpCliCommand('vp-internal', 'finish-init-clone', array('require' => $this->getVPInternalCommandPath()));
        WP_CLI::log($process->getConsoleOutput());
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

    /**
     * Prepares an empty database - creates it if it doesn't exist or drops its WP tables if it does.
     * Prompt will be displayed if the tables are going to be dropped, unless 'yes' is provided
     * as part of $assoc_args.
     *
     * @param array $assoc_args
     */
    private function prepareDatabase($assoc_args) {

        $process = VPCommandUtils::runWpCliCommand('db', 'create');

        if ($process->isSuccessful()) {
            WP_CLI::success("Database created");
        } else {

            $msg = $process->getConsoleOutput();
            $dbExists = Strings::contains($msg, '1007');

            if ($dbExists) {

                defined('SHORTINIT') or define('SHORTINIT', true);
                require_once ABSPATH . 'wp-config.php';
                global $table_prefix;
                if ($this->someWpTablesExist(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, $table_prefix)) {
                    WP_CLI::confirm('Database tables already exist, they will be droped and re-created. Proceed?', $assoc_args);
                }

                $this->dropTables();
            } else {
                WP_CLI::error("Failed creating database. Details:\n\n" . $msg);
            }
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
            WP_CLI::error($process->getConsoleOutput());
        } else {
            WP_CLI::success("wp-config.php created");
        }
    }

    /**
     * Clones site to a new folder and database.
     *
     * ## OPTIONS
     *
     * --name=<name>
     * : Name of the clone. Used as a directory name, part of the DB prefix
     * and an argument to the pull & push commands later.
     *
     * --siteurl=<url>
     * : URL of the clone. By default, the original URL is searched for <cwd>
     * and replaced with the clone name.
     *
     * --dbname=<dbname>
     * : Database name for the clone.
     *
     * --dbuser=<dbuser>
     * : Database user for the clone.
     *
     * --dbpass=<dbpass>
     * : Database user password for the clone.
     *
     * --dbhost=<dbhost>
     * : Database host for the clone.
     *
     * --dbprefix=<dbprefix>
     * : Database table prefix for the clone.
     *
     * --dbcharset=<dbcharset>
     * : Database charset for the clone.
     *
     * --dbcollate=<dbcollate>
     * : Database collation for the clone.
     *
     * --force
     * : Forces cloning even if the target directory or DB tables exists.
     * Basically provides --yes to all warnings / confirmations.
     *
     * --yes
     * : Another way to force the clone
     *
     * ## EXAMPLES
     *
     * The main site lives in a directory 'wpsite', uses the 'wp_' database table prefix and is
     * accessible via 'http://localhost/wpsite'. The command
     *
     *     wp vp clone --name=myclone
     *
     * does the following:
     *
     *    - Creates new directory 'myclone' next to the current one
     *    - Clones the files there
     *    - Creates new database tables prefixed with 'wp_myclone_'
     *    - Populates database tables with data
     *    - Makes the site accessible as 'http://localhost/myclone'
     *
     * @synopsis --name=<name> [--siteurl=<url>] [--dbname=<dbname>] [--dbuser=<dbuser>] [--dbpass=<dbpass>] [--dbhost=<dbhost>] [--dbprefix=<dbprefix>] [--dbcharset=<dbcharset>] [--dbcollate=<dbcollate>] [--force] [--yes]
     *
     * @subcommand clone
     */
    public function clone_($args = array(), $assoc_args = array()) {
        global $table_prefix;

        if (isset($assoc_args['force'])) {
            $assoc_args['yes'] = 1;
        }

        $name = $assoc_args['name'];

        $currentWpPath = get_home_path();
        $cloneDirName = $name;
        $clonePath = dirname($currentWpPath) . '/' . $cloneDirName;

        $cloneDbUser = isset($assoc_args['dbuser']) ? $assoc_args['dbuser'] : DB_USER;
        $cloneDbPassword = isset($assoc_args['dbpass']) ? $assoc_args['dbpass'] : DB_PASSWORD;
        $cloneDbName = isset($assoc_args['dbname']) ? $assoc_args['dbname'] : DB_NAME;
        $cloneDbHost = isset($assoc_args['dbhost']) ? $assoc_args['dbhost'] : DB_HOST;
        $cloneDbPrefix = isset($assoc_args['dbprefix']) ? $assoc_args['dbprefix'] : ($table_prefix . $name . '_');
        $cloneDbCharset = isset($assoc_args['dbcharset']) ? $assoc_args['dbcharset'] : DB_CHARSET;
        $cloneDbCollate = isset($assoc_args['dbcollate']) ? $assoc_args['dbcollate'] : DB_COLLATE;

        // Checking the DB prefix, regex from wp-admin/setup-config.php
        if (preg_match('|[^a-z0-9_]|i', $cloneDbPrefix)) {
            if (isset($assoc_args['dbprefix'])) {
                $hint = 'Please choose different one.';
            } else {
                $hint = 'Please choose different clone name or specify the --dbprefix parameter.';
            }

            WP_CLI::error(sprintf('Table prefix %s is not valid. It can only contain numbers, letters and underscores. %s', var_export($cloneDbPrefix, true), $hint));
        }

        $currentUrl = get_site_url();
        if (!Strings::contains($currentUrl, basename($currentWpPath))) {
            WP_CLI::error("The command cannot derive default clone URL. Please specify the --siteurl parameter.");
        }

        $cloneUrl = isset($assoc_args['siteurl']) ? $assoc_args['siteurl'] : $this->getCloneUrl(get_site_url(), basename($currentWpPath), $cloneDirName);

        if (is_dir($clonePath)) {
            WP_CLI::confirm("Directory '" . basename($clonePath) . "' already exists, it will be deleted before cloning. Proceed?", $assoc_args);
        }

        if ($this->someWpTablesExist($cloneDbUser, $cloneDbPassword, $cloneDbName, $cloneDbHost, $cloneDbPrefix)) {
            WP_CLI::confirm("Database tables for the clone already exist, they will be dropped and re-created. Proceed?", $assoc_args);
        }

        if (is_dir($clonePath)) {
            try {
                FileSystem::removeContent($clonePath);
            } catch (IOException $e) {
                WP_CLI::error("Could not delete directory '" . basename($clonePath) . "'. Please do it manually.");
            }
        }

        // Clone the site
        $cloneCommand = sprintf("git clone %s %s", escapeshellarg($currentWpPath), escapeshellarg($clonePath));

        $process = VPCommandUtils::exec($cloneCommand, $currentWpPath);

        if (!$process->isSuccessful()) {
            WP_CLI::error($process->getConsoleOutput(), false);
            WP_CLI::error("Cloning Git repo failed");
        } else {
            WP_CLI::success("Site files cloned");
        }

        // Adding the clone as a remote for the convenience of the `vp pull` command - its `--from`
        // parameter can then be just the name of the clone, not a path to it
        $addRemoteCommand = sprintf("git remote add %s %s", escapeshellarg($name), escapeshellarg($clonePath));
        $process = VPCommandUtils::exec($addRemoteCommand, $currentWpPath);

        if (!$process->isSuccessful()) {

            $overwriteRemote = VPCommandUtils::cliQuestion("The Git repo of this site already defines remote '$name', overwrite it?", array("y", "n"), $assoc_args);

            if ($overwriteRemote == "y") {
                $addRemoteCommand = str_replace(" add ", " set-url ", $addRemoteCommand);
                $process = VPCommandUtils::exec($addRemoteCommand, $currentWpPath);
                if (!$process->isSuccessful()) {
                    WP_CLI::error("Could not update remote's URL");
                } else {
                    WP_CLI::success("Updated remote configuration");
                }
            }

        } else {
            WP_CLI::success("Clone added as a remote");
        }


        // Enable pushing to origin
        $configCommand = "git config receive.denyCurrentBranch ignore";
        $process = VPCommandUtils::exec($configCommand);

        if ($process->isSuccessful()) {
            WP_CLI::success("Enabled pushing to the original repository");
        } else {
            WP_CLI::error("Cannot enable pushing to the original repository");
        }

        // Enable pushing to clone
        $configCommand = "git config receive.denyCurrentBranch ignore";
        $process = VPCommandUtils::exec($configCommand, $clonePath);

        if ($process->isSuccessful()) {
            WP_CLI::success("Enabled pushing to the clone");
        } else {
            WP_CLI::error("Cannot enable pushing to the clone");
        }

        // Copy & Update wp-config
        $wpConfigFile = $clonePath . '/wp-config.php';
        copy($currentWpPath . '/wp-config.php', $wpConfigFile);

        $this->updateConfig($wpConfigFile, $cloneDbUser, $cloneDbPassword, $cloneDbName, $cloneDbHost, $cloneDbPrefix, $cloneDbCharset, $cloneDbCollate);

        // Copy VersionPress
        FileSystem::copyDir(VERSIONPRESS_PLUGIN_DIR, $clonePath . '/wp-content/plugins/versionpress');
        WP_CLI::success("Copied VersionPress");

        // Finish the process by doing the standard restore-site
        $process = VPCommandUtils::runWpCliCommand('vp', 'restore-site', array('siteurl' => $cloneUrl, 'yes' => null, 'require' => __FILE__), $clonePath);
        WP_CLI::log(trim($process->getConsoleOutput()));

        if ($process->isSuccessful()) {
            WP_CLI::success("All done. Clone created here:");
            WP_CLI::log("");
            WP_CLI::log("Path:   $clonePath");
            WP_CLI::log("URL:    $cloneUrl");
        }
    }

    /**
     * Examples (clone name "test"):
     *
     *   http://localhost/vp01  ->  http://localhost/test
     *   http://vp01            ->  http://test
     *   http://www.vp01.dev    ->  http://www.test.dev
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
     * Pulls changes from another clone
     *
     * ## OPTIONS
     *
     * --from=<name|path|url>
     * : Where to pull from. Can be a clone name (specified previously during the
     * 'clone' command), a path or a URL. Defaults to 'origin' which is
     * automatically set in every clone by the 'clone' command.
     *
     * ## EXAMPLES
     *
     * Let's have a site 'wpsite' and a clone 'myclone' created from it. To pull the changes
     * from the clone back into the main site, use:
     *
     *     wp vp pull --from=myclone
     *
     * When in the clone, the pull can be run without any parameter:
     *
     *     wp vp pull
     *
     * This will pull the changes from 'origin' which was set to the parent site during the
     * 'clone' command.
     *
     * @synopsis [--from=<name|path|url>]
     */
    public function pull($args = array(), $assoc_args = array()) {

        global $versionPressContainer;

        $remote = isset($assoc_args['from']) ? $assoc_args['from'] : 'origin';
        $this->switchMaintenance('on');

        $branchToPullFrom = 'master'; // hardcoded until we support custom branches
        $pullCommand = 'git pull ' . escapeshellarg($remote) . ' ' . $branchToPullFrom;
        $process = VPCommandUtils::exec($pullCommand);

        if ($process->isSuccessful()) {
            WP_CLI::success("Pulled changes from '$remote'");
        } else {

            if (stripos($process->getConsoleOutput(), 'automatic merge failed') !== false) {
                WP_CLI::warning("");
                WP_CLI::warning("CONFLICTS DETECTED. Your options:");
                WP_CLI::warning("");
                WP_CLI::warning(" 1) Keep the conflicts. You will be able to resolve them manually.");
                WP_CLI::warning(" 2) Abort the process. The site will look like you never ran the pull.");
                WP_CLI::warning("");

                fwrite(STDOUT, "Choose 1 or 2: " );
                $answer = trim(fgets(STDIN));

                if ($answer == "1") {
                    WP_CLI::success("You've chosen to keep the conflicts on the disk. MAINTENANCE MODE IS STILL ON.");
                    WP_CLI::success("");
                    WP_CLI::success("Do this now:");
                    WP_CLI::success("");
                    WP_CLI::success(" 1. Resolve the conflicts manually as in a standard Git workflow");
                    WP_CLI::success(" 2. Stage and `git commit` the changes");
                    WP_CLI::success(" 3. Return here and run `wp vp apply-changes`");
                    WP_CLI::success("");
                    WP_CLI::success("That last step will turn the maintenance mode off.");
                    WP_CLI::success("You can also abort the merge manually by running `git merge --abort`");
                    exit();

                } else {

                    $process = VPCommandUtils::exec('git merge --abort');
                    if ($process->isSuccessful()) {
                        $this->switchMaintenance('off');
                        WP_CLI::success("Pull aborted, your site is now clean and running");
                        exit();
                    } else {
                        WP_CLI::error("Aborting pull failed, do it manually by executing 'git merge --abort'", false);
                        WP_CLI::error("and also don't fortget to turn off the maintenance mode.");
                    }
                }


            } else { // not a merge conflict, some other error
                $this->switchMaintenance('off');
                WP_CLI::error("Changes from $remote couldn't be pulled. Details:\n\n" . $process->getConsoleOutput());
            }

        }

        // Run synchronization
        /** @var SynchronizationProcess $syncProcess */
        $syncProcess = $versionPressContainer->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS);
        $syncProcess->synchronize();
        WP_CLI::success("Synchronized database");

        $this->switchMaintenance('off');
        $this->flushRewriteRules();

        WP_CLI::success("All done");

    }

    /**
     * Applies changes from the disk to the database
     *
     * ## EXAMPLES
     *
     * This command is mainly used in a merge conflict situation, after a 'pull'. When
     * the conflict is manually resolved and committed, run this command to make sure that
     * the database in sync with the Git repository / filesystem:
     *
     *     wp vp apply-changes
     *
     * Note that this command temporarily turns on the maintenance mode.
     *
     * @subcommand apply-changes
     */
    public function applyChanges($args = array(), $assoc_args = array()) {

        global $versionPressContainer;

        $this->switchMaintenance('on');

        /** @var SynchronizationProcess $syncProcess */
        $syncProcess = $versionPressContainer->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS);
        $syncProcess->synchronize();
        WP_CLI::success("Database updated");
        $this->switchMaintenance('off');
        $this->flushRewriteRules();

        WP_CLI::success("All done");



    }

    /**
     * Pushes changes to another clone
     *
     * ## OPTIONS
     *
     * --to=<name|path>
     * : Name of the clone or a path to it. Defaults to 'origin' which, in a clone,
     * points to its original site.
     *
     * ## EXAMPLES
     *
     * Push is a similar command to 'pull' but does not create a merge. To push from clone
     * to the original site, run:
     *
     *     wp vp push
     *
     * To push from the original site to the clone, use the '--to' parameter:
     *
     *     wp vp push --to=clonename
     *
     *
     * @synopsis [--to=<name|path>]
     */
    public function push($args = array(), $assoc_args = array()) {
        $remoteName = isset($assoc_args['to']) ? $assoc_args['to'] : 'origin';
        $remotePath = $this->getRemoteUrl($remoteName);
        if ($remotePath === null) {
            $remotePath = $remoteName;
            if (!is_dir($remotePath)) {
                WP_CLI::error("'$remotePath' is not a valid path to a WP site");
            }
        }

        $this->switchMaintenance('on', $remoteName);

        $currentPushType = trim(VPCommandUtils::exec('git config --local push.default')->getOutput());
        VPCommandUtils::exec('git config --local push.default simple');

        $pushCommand = "git push --set-upstream $remoteName master"; // hardcoded branch name until we support custom branches
        $process = VPCommandUtils::exec($pushCommand);
        if ($process->isSuccessful()) {
            WP_CLI::success("Changes successfully pushed");
        } else {
            $this->switchMaintenance('off', $remoteName);
            WP_CLI::error("Changes couldn't be pushed. Details:\n\n" . $process->getConsoleOutput());
        }

        if ($currentPushType === '') { // implicit value
            VPCommandUtils::exec("git config --local --unset push.default");
        } else {
            VPCommandUtils::exec("git config --local push.default $currentPushType");
        }

        $process = VPCommandUtils::runWpCliCommand('vp-internal', 'finish-push', array('require' => $this->getVPInternalCommandPath()), $remotePath);
        if ($process->isSuccessful()) {
            WP_CLI::success("Remote database synchronized");
        } else {
            WP_CLI::error("Push couldn't be finished. Details:\n\n" . $process->getConsoleOutput());
        }
        $this->switchMaintenance('off', $remoteName);
        WP_CLI::success("All done");

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
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);

        $hash = $args[0];
        $log = $repository->log($hash);
        if (count($log) === 0) {
            WP_CLI::error("Commit '$hash' does not exist.");
        }

        $this->switchMaintenance('on');

        $status = $reverter->undo($hash);

        if ($status === RevertStatus::VIOLATED_REFERENTIAL_INTEGRITY) {
            WP_CLI::error("Violated referential integrity. Objects with missing references cannot be restored. For example we cannot restore comment where the related post was deleted.", false);
        }

        if ($status === RevertStatus::MERGE_CONFLICT) {
            WP_CLI::error("Merge conflict. Overwritten changes can not be reverted.", false);
        }

        if ($status === RevertStatus::NOT_CLEAN_WORKING_DIRECTORY) {
            WP_CLI::error("The working directory is not clean. Please commit your changes.", false);
        }

        if ($status === RevertStatus::REVERTING_MERGE_COMMIT) {
            WP_CLI::error("Cannot undo a merge commit.", false);
        }

        if ($status === RevertStatus::OK) {
            WP_CLI::success("Undo was successful.");
        }

        $this->switchMaintenance('off');
        $this->flushRewriteRules();
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
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);

        $hash = $args[0];
        $log = $repository->log($hash);
        if (count($log) === 0) {
            WP_CLI::error("Commit '$hash' does not exist.");
        }

        $this->switchMaintenance('on');

        $status = $reverter->rollback($hash);

        if ($status === RevertStatus::NOTHING_TO_COMMIT) {
            WP_CLI::error("Nothing to commit. Current state is the same as the one you want rollback to.", false);
        }

        if ($status === RevertStatus::NOT_CLEAN_WORKING_DIRECTORY) {
            WP_CLI::error("The working directory is not clean. Please commit your changes.", false);
        }

        if ($status === RevertStatus::OK) {
            WP_CLI::success("Rollback was successful.");
        }

        $this->switchMaintenance('off');
        $this->flushRewriteRules();
    }

    private function dropTables() {
        global $versionPressContainer;
        $tables = array(
            'users',
            'usermeta',
            'posts',
            'comments',
            'links',
            'options',
            'postmeta',
            'terms',
            'term_taxonomy',
            'term_relationships',
            'commentmeta',
            'vp_id',
        );
        /** @var DbSchemaInfo $schema */
        $schema = $versionPressContainer->resolve(VersionPressServices::DB_SCHEMA);
        $tables = array_map(array($schema, 'getPrefixedTableName'), $tables);

        foreach ($tables as $table) {
            VPCommandUtils::runWpCliCommand('db', 'query', array("DROP TABLE IF EXISTS `$table`"));
        }
    }


    /**
     * Checks if some tables with the given prefix exist in the database
     *
     * @param string $dbUser
     * @param string $dbPassword
     * @param string $dbName
     * @param string $dbHost
     * @param string $dbPrefix
     * @return bool
     */
    private function someWpTablesExist($dbUser, $dbPassword, $dbName, $dbHost, $dbPrefix) {
        $wpdb = new \wpdb($dbUser, $dbPassword, $dbName, $dbHost);
        $wpdb->set_prefix($dbPrefix);
        $tables = $wpdb->get_col("SHOW TABLES LIKE '{$dbPrefix}_%'");
        $wpTables = array_intersect($tables, $wpdb->tables());
        return count($wpTables) > 0;
    }

    private function updateConfig($wpConfigFile, $dbUser, $dbPassword, $dbName, $dbHost, $dbPrefix, $dbCharset, $dbCollate) {
        $config = file_get_contents($wpConfigFile);

        // https://regex101.com/r/oO7gX7/4 - just remove the "g" modifier which is there for testing only
        $re = "/^(\\\$table_prefix\\s*=\\s*['\"]).*(['\"];)/m";
        $config = preg_replace($re, "\${1}{$dbPrefix}\${2}", $config, 1);

        https://regex101.com/r/zD3mJ4/1 - just remove the "g" modifier which is there for testing only
        $defineRegexPattern = "/^(define\\s*\\(\\s*['\"]%s['\"]\\s*,\\s*['\"]).*(['\"]\\s*\\)\\s*;)$/m";

        $replacements = array(
            "DB_NAME" => $dbName,
            "DB_USER" => $dbUser,
            "DB_PASSWORD" => $dbPassword,
            "DB_HOST" => $dbHost,
            "DB_CHARSET" => $dbCharset,
            "DB_COLLATE" => $dbCollate,
        );

        foreach ($replacements as $constant => $value) {
            $re = sprintf($defineRegexPattern, $constant);
            $config = preg_replace($re, "\${1}{$value}\${2}", $config, 1);
        }


        file_put_contents($wpConfigFile, $config);
        WP_CLI::success("wp-config.php updated");
    }

    /**
     * Returns URL for a remote name, or null if the remote isn't configured.
     * For example, for "origin", it might return "https://github.com/project/repo.git".
     *
     * @param string $name Remote name, e.g., "origin"
     * @return string|null Remote URL, or null if remote isn't configured
     */
    private function getRemoteUrl($name) {
        $listRemotesCommand = "git remote -v";
        $remotesRaw = VPCommandUtils::exec($listRemotesCommand)->getConsoleOutput();

        // https://regex101.com/r/iQ4kG4/2
        $numberOfMatches = preg_match_all("/^([[:alnum:]]+)\\s+(.*) \\(fetch\\)$/m", $remotesRaw, $matches);
        if ($numberOfMatches === 0) {
            return null;
        }

        $remotes = array();
        foreach ($matches[1] as $i => $cloneName) {
            $url = $matches[2][$i];
            $remotes[$cloneName] = $url;
        }

        if (isset($remotes[$name])) {
            return $remotes[$name];
        }

        return null;
    }

    /**
     * Switches the maintenance mode on or off for a site specified by a remote.
     * The remote must be "local" - on the same filesystem. If no remote name is
     * given, the current site's maintenance mode is switched.
     *
     * @param string $onOrOff "on" | "off"
     * @param string|null $remoteName
     */
    private function switchMaintenance($onOrOff, $remoteName = null) {
        $remotePath = $remoteName ? $this->getRemoteUrl($remoteName) : null;
        $process = VPCommandUtils::runWpCliCommand('vp-internal', 'maintenance', array($onOrOff, 'require' => $this->getVPInternalCommandPath()), $remotePath);

        if ($process->isSuccessful()) {
            WP_CLI::success("Maintenance mode turned $onOrOff" . ($remoteName ? " for '$remoteName'" : ""));
        } else {
            WP_CLI::error("Maintenance mode couldn't be switched" . ($remoteName ? " for '$remoteName'" : "") . ". Details:\n\n" . $process->getConsoleOutput());
        }
    }

    private function getVPInternalCommandPath() {
        return __DIR__ . '/vp-internal.php';
    }

    private function flushRewriteRules() {
        set_transient('vp_flush_rewrite_rules', 1);
        /**
         * If it fails, we just flush the rewrite rules on the next request.
         * The disadvantage is that until the next (valid) request all rewritten
         * URLs may be broken.
         * Valid request is such a request, which does not require URL rewrite
         * (e.g. homepage / administration) and finishes successfully.
         * @noinspection PhpUsageOfSilenceOperatorInspection
         */
        @file_get_contents(get_home_url());
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp', 'VersionPress\Cli\VPCommand');
}

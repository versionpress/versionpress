<?php
// NOTE: VersionPress must be fully activated for these commands to be available

namespace VersionPress\Cli;

use Nette\Utils\Strings;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Process\Process;
use VersionPress\Database\DbSchemaInfo;
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
     * --siteurl=<url>
     * : The address of the restored site. Default: http://localhost/<cwd>
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
        $url = @$assoc_args['siteurl'] ?: ('http://localhost/' . basename(getcwd()));

        if (!isset($assoc_args['siteurl'])) {
            WP_CLI::confirm("The site URL will be set to '$url'. Proceed?", $assoc_args);
        }

        if (!defined('WP_CONTENT_DIR')) {
            define('WP_CONTENT_DIR', 'xyz'); //doesn't matter, it's just to prevent the NOTICE in the require`d bootstrap.php
        }

        $this->defineGlobalTablePrefix();

        require_once(__DIR__ . '/../../bootstrap.php');

        if (file_exists(ABSPATH . 'wp-config.php')) {
            if ($this->issetConfigOption($assoc_args)) {
                WP_CLI::error("Site settings was loaded from wp-config.php. If you want to reconfigure the site, please delete the wp-config.php file");
            }
        } else {
            $this->configSite($assoc_args);
        }

        $this->prepareDatabase($assoc_args);


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
        WP_CLI::log($process->getErrorOutput());
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

    private function prepareDatabase($assoc_args) {
        $process = VPCommandUtils::runWpCliCommand('db', 'create');
        if (!$process->isSuccessful()) {
            $msg = $process->getErrorOutput();
            if (Strings::contains($msg, '1007')) { // It's OK. The database already exists.
                if (!isset($assoc_args['yes'])) {
                    defined('SHORTINIT') or define('SHORTINIT', true);
                    require_once ABSPATH . 'wp-config.php';
                    global $table_prefix;
                    $this->checkTables(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, $table_prefix);
                }
                $this->dropTables();
            } else {
                WP_CLI::error("Failed creating DB");
            }
        }

        WP_CLI::success("DB created");
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
     * : Name of the clone. Used as a suffix for new folder, a suffix of db prefix
     * and a name of the new Git branch. See example below.
     *
     * --siteurl=<url>
     * : URL of new website. By default the command tries to search in URL the name of directory
     * where the website is located and replace it with new directory name.
     * E.g. if you have website in directory called "wordpress" and it runs at http://wordpress.local,
     * the new URL will be http://wordpress-<name>.local.
     *
     * --dbname=<dbname>
     * : Set the database name for the clone.
     *
     * --dbuser=<dbuser>
     * : Set the database user for the clone.
     *
     * --dbpass=<dbpass>
     * : Set the database user password for the clone.
     *
     * --dbhost=<dbhost>
     * : Set the database host for the clone.
     *
     * --dbprefix=<dbprefix>
     * : Set the database table prefix for the clone.
     *
     * --dbcharset=<dbcharset>
     * : Set the database charset for the clone.
     *
     * --dbcollate=<dbcollate>
     * : Set the database collation for the clone.
     *
     * --yes
     * : Answer yes to the confirmation message.
     *
     * ## EXAMPLES
     *
     * Let's say we have a site in folder `wordpress` that uses database called `wordpress`
     * with tables prefixed with `wp_`. The command
     *
     *     wp vp clone --name=test
     *
     * creates a copy of the site in `test`, a new Git branch called `test`
     * and the tables in database `wordpress` will be prefixed with `wp_test_`.
     *
     * @synopsis --name=<name> [--siteurl=<url>] [--dbname=<dbname>] [--dbuser=<dbuser>] [--dbpass=<dbpass>] [--dbhost=<dbhost>] [--dbprefix=<dbprefix>] [--dbcharset=<dbcharset>] [--dbcollate=<dbcollate>] [--yes]
     *
     * @subcommand clone
     */
    public function clone_($args = array(), $assoc_args = array()) {
        global $table_prefix;

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

        $currentUrl = get_site_url();
        if (!Strings::contains($currentUrl, basename($currentWpPath))) {
            WP_CLI::error("The command cannot derive default clone URL. Please specify the --url parameter.");
        }

        $cloneUrl = isset($assoc_args['siteurl']) ? $assoc_args['siteurl'] : $this->getCloneUrl(get_site_url(), basename($currentWpPath), $cloneDirName);

        if (is_dir($clonePath) && !isset($assoc_args['yes'])) {
            WP_CLI::confirm("Directory '" . basename($clonePath) . "' already exists. It will be deleted before cloning. Proceed?");
        }

        $this->checkTables($cloneDbUser, $cloneDbPassword, $cloneDbName, $cloneDbHost, $cloneDbPrefix);

        if (is_dir($clonePath)) {
            try {
                FileSystem::removeContent($clonePath);
            } catch (IOException $e) {
                WP_CLI::error("Could not delete directory '" . basename($clonePath) . "'. Please do it manually.");
            }
        }

        // Clone the site
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

        // Create a new Git branch
        $createBranchCommand = 'git checkout -b ' . escapeshellarg($name);
        $process = new Process($createBranchCommand, $clonePath);
        $process->run();

        if (!$process->isSuccessful()) {
            WP_CLI::error("Failed creating branch on clone, message: " . $process->getErrorOutput());
        } else {
            WP_CLI::success("New Git branch created");
        }

        // Copy & Update wp-config
        $wpConfigFile = $clonePath . '/wp-config.php';
        copy($currentWpPath . '/wp-config.php', $wpConfigFile);

        $this->updateConfig($wpConfigFile, $cloneDbUser, $cloneDbPassword, $cloneDbName, $cloneDbHost, $cloneDbPrefix, $cloneDbCharset, $cloneDbCollate);

        FileSystem::copyDir(VERSIONPRESS_PLUGIN_DIR, $clonePath . '/wp-content/plugins/versionpress');
        WP_CLI::success("Copied VersionPress");

        $process = VPCommandUtils::runWpCliCommand('vp', 'restore-site', array('siteurl' => $cloneUrl, 'yes' => null, 'require' => __FILE__), $clonePath);
        WP_CLI::log($process->getOutput());
        WP_CLI::log($process->getErrorOutput());
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


    private function defineGlobalTablePrefix() {
        global $table_prefix;

        $wpConfigPath = ABSPATH . 'wp-config.php';
        $wpConfigLines = file_get_contents($wpConfigPath);
        // https://regex101.com/r/oO7gX7/2
        preg_match("/^\\\$table_prefix\\s*=\\s*['\"](.*)['\"];/m", $wpConfigLines, $matches);
        $table_prefix = $matches[1];
    }

    private function checkTables($dbUser, $dbPassword, $dbName, $dbHost, $dbPrefix) {
        $wpdb = new \wpdb($dbUser, $dbPassword, $dbName, $dbHost);
        $wpdb->set_prefix($dbPrefix);
        $tables = $wpdb->get_col("SHOW TABLES LIKE '{$dbPrefix}_%'");
        $wpTables = array_intersect($tables, $wpdb->tables());
        $wpTablesExists = count($wpTables) > 0;
        if ($wpTablesExists) {
            WP_CLI::confirm("Tables for this site already exist. They will be dropped. Proceed?");
        }
    }

    private function updateConfig($wpConfigFile, $dbUser, $dbPassword, $dbName, $dbHost, $dbPrefix, $dbCharset, $dbCollate) {
        $config = file_get_contents($wpConfigFile);

        // https://regex101.com/r/oO7gX7/3 - just remove the "g" modifier which is there for testing only
        $re = "/^(\\\$table_prefix\\s*=\\s*['\"]).*(['\"];)$/m";
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
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp', 'VersionPress\Cli\VPCommand');
}

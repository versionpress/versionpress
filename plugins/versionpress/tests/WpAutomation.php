<?php

define('CONFIG_FILE', __DIR__ . '/test-config.ini');
is_file(CONFIG_FILE) or die('Create test-config.ini for automation to work');
WpAutomation::$config = new TestConfig(parse_ini_file(CONFIG_FILE));

/**
 * Automates some common tasks like setting up a WP site, installing VersionPress etc.
 *
 * Currently, WpAutomation is a set of static functions as of v1; other options will be considered for v2, see WP-56.
 *
 * Note: Currently, the intention is to add supported tasks as public methods to this class. If this gets
 * unwieldy it will probably be split into multiple files / classes.
 */
class WpAutomation {

    /**
     * Config loaded from test-config.ini
     *
     * @var TestConfig
     */
    public static $config;


    /**
     * Does a full setup of a WP site including removing the old site,
     * downloading files from wp.org, setting up a fresh database, executing
     * the install script etc.
     */
    public static function setUpSite() {
        self::prepareFiles();
        self::createConfigFile();
        self::clearDatabase();
        self::installWp();
    }

    /**
     * Copies the plugin to the WP site.
     */
    public static function installVersionPress() {
        $versionPressDir = __DIR__ . '/../';
        $pluginDir = self::$config->getSitePath() . '/wp-content/plugins/versionpress/';
        \Nette\Utils\FileSystem::copy($versionPressDir, $pluginDir);
    }

    public static function activateVersionPress() {
        $activateCommand = "wp plugin activate versionpress";
        self::exec($activateCommand, self::$config->getSitePath());
    }

    public static function uninstallVersionPress($keepRepository = false) {
        self::runWpCliCommand('plugin', 'uninstall', array('versionpress'));
    }

    /**
     * Creates new post using WP-CLI. Returns ID of created post.
     *
     * @param array $post (as wp_insert_post)
     * @return int
     */
    public static function createPost(array $post) {
        $post["porcelain"] = "";
        return intval(self::runWpCliCommand('post', 'create', $post));
    }

    /**
     * Changes the post using WP-CLI.
     *
     * @param $id
     * @param $changes
     */
    public static function editPost($id, $changes) {
        array_unshift($changes, $id);
        self::runWpCliCommand('post', 'update', $changes);
    }

    /**
     * Deletes the post using WP-CLI.
     *
     * @param $id
     */
    public static function deletePost($id) {
        $args = array($id, '--force');
        self::runWpCliCommand('post', 'delete', $args);
    }

    /**
     * Activates VP in the administration and runs VersionPressInstaller
     */
    public static function enableVersionPress() {
        self::runWpCliCommand('plugin', 'activate', array('versionpress'));
        $code = 'global $versionPressContainer;@mkdir(VERSIONPRESS_MIRRORING_DIR, 0777, true);$installer = $versionPressContainer->resolve(VersionPressServices::INSTALLER);$installer->install();';
        self::runWpCliCommand('eval', array($code));
    }

    /**
     * Puts WP directory to a default state, as if one manually downloaded the
     * WordPress ZIP and extracted it there.
     */
    private static function prepareFiles() {
        self::ensureCleanInstallationIsAvailable();
        self::setPermisionsForGitDirectory(); // Windows hack (enables to delete files under .git/objects directory)
        \Nette\Utils\FileSystem::delete(self::$config->getSitePath() . '/*');
        \Nette\Utils\FileSystem::copy(self::getCleanInstallationPath(), self::$config->getSitePath());
    }

    /**
     * Ensures that the clean installation of WordPress is available locally. If not,
     * it downloads it from wp.org and stores it as `<clean-installations-dir>/<version>`.
     */
    private static function ensureCleanInstallationIsAvailable() {
        if (!is_dir(self::getCleanInstallationPath())) {
            $downloadPath = self::getCleanInstallationPath();
            $wpVersion = self::$config->getWpVersion();
            $downloadCommand = "wp core download --path=\"$downloadPath\" --version=$wpVersion";

            self::exec($downloadCommand, self::$config->getCleanInstallationsPath());
        }
    }

    /**
     * Returns a path where a clean installation of the configured WP version is stored and cached.
     *
     * @return string
     */
    private static function getCleanInstallationPath() {
        return self::$config->getCleanInstallationsPath() . '/' . self::$config->getWpVersion();
    }

    /**
     * Creates wp-config.php based on values in test-config.ini
     */
    private static function createConfigFile() {
        $args = array();
        $args["dbname"] = self::$config->getDbName();
        $args["dbuser"] = self::$config->getDbUser();
        if (self::$config->getDbPassword()) $args["dbpass"] = self::$config->getDbPassword();
        if (self::$config->getDbHost()) $args["dbhost"] = self::$config->getDbHost();

        self::runWpCliCommand("core", "config", $args);
    }

    /**
     * Deletes all tables from the database.
     */
    private static function clearDatabase() {
        $mysqli = new mysqli(
            self::$config->getDbHost(),
            self::$config->getDbUser(),
            self::$config->getDbPassword(),
            self::$config->getDbName()
        );
        $res = $mysqli->query('show tables');
        while ($row = $res->fetch_row()) {
            $dropTableSql = "DROP TABLE $row[0]";
            $mysqli->query($dropTableSql);
        }
    }

    /**
     * Installs WordPress. Assumes that files have been prepared on the file system, database is clean
     * and wp-config.php has been created.
     */
    private static function installWp() {
        $cmdArgs = array(
            "url" => self::$config->getSiteUrl(),
            "title" => self::$config->getSiteTitle(),
            "admin_name" => self::$config->getAdminName(),
            "admin_email" => self::$config->getAdminEmail(),
            "admin_password" =>self::$config->getAdminPassword()
        );

        self::runWpCliCommand("core", "install", $cmdArgs);
    }

    /**
     * Sets full privileges on the .git directory and everything under it
     */
    private static function setPermisionsForGitDirectory() {
        $gitDirectoryPath = self::$config->getSitePath() . '/.git/';

        if (!is_dir($gitDirectoryPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($gitDirectoryPath));

        foreach ($iterator as $item) {
            chmod($item, 0777);
        }
    }

    /**
     * Executes a $command from $executionPath
     *
     * @param string $command
     * @param string $executionPath Working directory for the command
     * @return string
     */
    private static function exec($command, $executionPath) {
        $cwd = getcwd();
        chdir($executionPath);
        $result = exec($command);
        chdir($cwd);
        return $result;
    }

    /**
     * Executes a WP-CLI command
     * http://wp-cli.org/commands/
     *
     * @param string $command
     * @param string $subcommand
     * @param array $args
     * @return string
     */
    private static function runWpCliCommand($command, $subcommand, $args = array()) {
        $cliCommand = "wp $command";

        if (is_array($subcommand)) {
            $args = $subcommand;
        } else {
            $cliCommand .= " $subcommand";
        }

        foreach ($args as $name => $value) {
            if (is_int($name)) { // position based argument without name
                $cliCommand .= " \"$value\"";
            } elseif($value) {
                $cliCommand .= " --$name=\"$value\"";
            } else {
                $cliCommand .= " --$name";
            }
        }
        return self::exec($cliCommand, self::$config->getSitePath());
    }
}
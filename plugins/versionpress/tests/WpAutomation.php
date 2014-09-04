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

    public static function uninstallVersionPress() {
        self::runWpCliCommand('plugin', 'uninstall', array('versionpress'));
    }

    /**
     * Creates new post using WP-CLI. Returns ID of created post.
     *
     * @param array $post (as wp_insert_post)
     * @return int
     */
    public static function createPost(array $post) {
        $post["porcelain"] = ""; // wp-cli returns only id
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
     * Creates new comment using WP-CLI. Returns ID of created comment.
     *
     * @param array $comment (as wp_insert_comment)
     * @return int
     */
    public static function createComment(array $comment) {
        $comment["porcelain"] = "";  // wp-cli returns only id
        return intval(self::runWpCliCommand("comment", "create", $comment));
    }

    /**
     * Changes the comment using WP-CLI.
     *
     * @param $id
     * @param $changes
     */
    public static function editComment($id, $changes) {
        array_unshift($changes, $id);
        self::runWpCliCommand('comment', 'update', $changes);
    }

    /**
     * Deletes the comment using WP-CLI.
     *
     * @param $id
     */
    public static function deleteComment($id) {
        $args = array($id, '--force');
        self::runWpCliCommand('comment', 'delete', $args);
    }

    /**
     * Creates new user using WP-CLI. Returns ID of created user.
     *
     * @param array $user (as wp_insert_comment)
     * @return int
     */
    public static function createUser(array $user) {
        $args = array($user["user_login"], $user["user_email"]);
        unset($user["user_login"], $user["user_email"]);
        $args = array_merge($args, $user);
        $args["porcelain"] = "";  // wp-cli returns only id
        return intval(self::runWpCliCommand("user", "create", $args));
    }

    /**
     * Changes the user using WP-CLI.
     *
     * @param $id
     * @param $changes
     */
    public static function editUser($id, $changes) {
        array_unshift($changes, $id);
        self::runWpCliCommand('user', 'update', $changes);
    }

    /**
     * Deletes the user using WP-CLI.
     *
     * @param $id
     */
    public static function deleteUser($id) {
        $args = array($id, 'yes' => '');
        self::runWpCliCommand('user', 'delete', $args);
    }

    /**
     * Changes the user using WP-CLI.
     *
     * @param $id
     * @param $name
     * @param $value
     */
    public static function editUserMeta($id, $name, $value) {
        self::runWpCliCommand('user', 'meta update', func_get_args());
    }

    /**
     * Creates new option using WP-CLI.
     *
     * @param string $name
     * @param mixed $value
     */
    public static function createOption($name, $value) {
        self::runWpCliCommand('option', 'add', array($name, $value));
    }

    /**
     * Changes option with given name using WP-CLI.
     *
     * @param string $name
     * @param mixed $value
     */
    public static function editOption($name, $value) {
        self::runWpCliCommand('option', 'update', array($name, $value));
    }

    /**
     * Deletes option with given name using WP-CLI.
     *
     * @param string $name
     */
    public static function deleteOption($name) {
        self::runWpCliCommand('option', 'delete', array($name));
    }


    /**
     * Activates VP in the administration and runs the Initializer
     */
    public static function enableVersionPress() {
        self::runWpCliCommand('plugin', 'activate', array('versionpress'));
        $code = 'global $versionPressContainer;$initializer = $versionPressContainer->resolve(VersionPressServices::INITIALIZER);$initializer->initializeVersionPress();';
        self::runWpCliCommand('eval', array($code));
    }

    /**
     * Puts WP directory to a default state, as if one manually downloaded the
     * WordPress ZIP and extracted it there.
     */
    private static function prepareFiles() {
        self::ensureCleanInstallationIsAvailable();
        FileSystem::setPermisionsForGitDirectory(self::$config->getSitePath()); // Windows hack (enables to delete files under .git/objects directory)
        \Nette\Utils\FileSystem::delete(self::$config->getSitePath() . '/*');
        \Nette\Utils\FileSystem::copy(self::getCleanInstallationPath(), self::$config->getSitePath());
    }

    /**
     * Ensures that the clean installation of WordPress is available locally. If not,
     * it downloads it from wp.org and stores it as `<clean-installations-dir>/<version>`.
     */
    private static function ensureCleanInstallationIsAvailable() {

        if (!is_dir(self::$config->getCleanInstallationsPath())) {
            mkdir(self::$config->getCleanInstallationsPath(), 0777, true);
        }

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
        $args["dbprefix"] = self::$config->getDbPrefix();
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
     * Executes a $command from $executionPath
     *
     * @param string $command
     * @param string $executionPath Working directory for the command
     * @return string
     */
    private static function exec($command, $executionPath) {
        $process = new \Symfony\Component\Process\Process($command, $executionPath);
        $process->run();
        return $process->getOutput();
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
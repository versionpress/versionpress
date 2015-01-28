<?php

use VersionPress\Utils\FileSystem;

define('CONFIG_FILE', __DIR__ . '/../test-config.ini');
is_file(CONFIG_FILE) or die('Create test-config.ini for automation to work');
WpAutomation::$config = new TestConfig(parse_ini_file(CONFIG_FILE));

/**
 * Automates some common tasks like setting up a WP site, installing VersionPress, working with posts, comments etc.
 *
 * You should have the whole development environment set up as described on our wiki. Specifically, these are required:
 *
 *  - WP-CLI (`wp --info` works in console)
 *  - NPM packages installed in <project_root>
 *  - Gulp (`gulp -v` works in console)
 *  - `test-config.ini` file created in `versionpress/tests`
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
     *
     * Database as specified in the config file must exist and be accessible.
     */
    public static function setUpSite() {
        self::prepareFiles();
        self::createConfigFile();
        self::clearDatabase();
        self::installWp();
    }

    /**
     * Returns true if the site is installed and working
     *
     * @return bool
     */
    public static function isSiteSetUp() {
        try {
            self::runWpCliCommand("core", "is-installed");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns true if VerisonPress is active and tracking the site
     *
     * @return bool
     */
    public static function isVersionPressInitialized() {
        return is_file(self::$config->getSitePath() . '/wp-content/vpdb/.active');
    }

    /**
     * Copies VP files to the test site and possibly removes all old files from there. It does so using
     * a Gulp script which specifies which paths to include and which ones to ignore.
     * See <project_root>\gulpfile.js.
     */
    public static function copyVersionPressFiles() {
        $versionPressDir = __DIR__ . '/../..';
        $gulpBaseDir = $versionPressDir . '/../..'; // project root as checked out from our repository
        self::exec('gulp test-deploy', $gulpBaseDir); // this also cleans the destination directory, see gulpfile.js "clean" task
    }

    public static function activateVersionPress() {
        $activateCommand = "wp plugin activate versionpress";
        self::exec($activateCommand, self::$config->getSitePath());
    }

    public static function uninstallVersionPress() {
        self::runWpCliCommand('plugin', 'deactivate', array('versionpress'));
        self::runWpCliCommand('plugin', 'uninstall', array('versionpress'));
    }

    /**
     * Creates new post using WP-CLI. Returns ID of created post.
     *
     * @see wp_insert_post()
     * @param array $post (as wp_insert_post)
     * @return int
     */
    public static function createPost(array $post) {
        $post["porcelain"] = ""; // wp-Cli returns only id
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
        $comment["porcelain"] = "";  // wp-Cli returns only id
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
        $args["porcelain"] = "";  // wp-Cli returns only id
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
     * Returns stylesheet of current theme.
     *
     * @return string
     */
    public static function getCurrentTheme() {
        $status = self::runWpCliCommand('theme', 'status');
        $status = preg_replace("/\033\[[^m]*m/", '', $status); // remove formatting

        preg_match_all("/^[^A-Z]*([A-Z]+)[^a-z]+([a-z\-]+).*$/m", $status, $matches);

        foreach ($matches[1] as $lineNumber => $status) {
            if (\Nette\Utils\Strings::contains($status, 'A')) {
                return $matches[2][$lineNumber];
            }
        }

        return null; // this should never happen, there is always some activate theme
    }

    /**
     * @param string $theme Theme stylesheet
     */
    public static function switchTheme($theme) {
        self::runWpCliCommand('theme', 'activate', array($theme));
    }

    /**
     * Returns list of sidebar IDs defined by current template (without wp_inactive_widgets).
     *
     * @return array
     */
    public static function getSidebars() {
        $sidebarsJson = self::runWpCliCommand('sidebar', 'list', array('format' => 'json', 'fields' => 'id'));
        $sidebars = json_decode($sidebarsJson);
        $sidebarIds = array_map(function ($sidebar) { return $sidebar->id; }, $sidebars);
        $sidebarIds = array_filter($sidebarIds, function ($id) { return $id != 'wp_inactive_widgets'; });
        return $sidebarIds;
    }

    /**
     * Returns list of widgets in given sidebar.
     *
     * @param string $sidebar sidebar id
     * @return array
     */
    public static function getWidgets($sidebar) {
        $widgetsJson = self::runWpCliCommand('widget', 'list', array($sidebar, 'format' => 'json', 'fields' => 'id'));
        $widgets = json_decode($widgetsJson);
        $widgetIds = array_map(function ($widget) { return $widget->id; }, $widgets);
        return $widgetIds;
    }

    /**
     * Deletes widget(s)
     *
     * @param string[]|string $widgets Name of widget or list of widgets
     */
    public static function deleteWidgets($widgets) {
        $widgets = trim(is_array($widgets) ? join(' ', $widgets) : $widgets);
        if (strlen($widgets) > 0) {
            self::exec('wp widget delete ' . $widgets, self::$config->getSitePath());
        }
    }

    /**
     * Activates VersionPress plugin and runs the Initializer
     */
    public static function initializeVersionPress() {
        self::runWpCliCommand('plugin', 'activate', array('versionpress'));
        $code = 'global $versionPressContainer; $initializer = $versionPressContainer->resolve(VersionPress\DI\VersionPressServices::INITIALIZER); $initializer->initializeVersionPress();';
        self::runWpCliCommand('eval', array($code));
    }

    /**
     * Puts WP directory to a default state, as if one manually downloaded the WordPress ZIP
     * and extracted it there. Removes all old files if necessary.
     */
    private static function prepareFiles() {
        self::ensureCleanInstallationIsAvailable();
        FileSystem::removeContent(self::$config->getSitePath());
        FileSystem::copyDir(self::getCleanInstallationPath(), self::$config->getSitePath());
    }

    /**
     * Ensures that the clean installation of WordPress is available locally. If not,
     * it downloads it from wp.org and stores it as `<clean-installations-dir>/<version>`.
     */
    private static function ensureCleanInstallationIsAvailable() {

        if (!is_dir(self::$config->getCleanInstallationsPath())) {
            FileSystem::mkdir(self::$config->getCleanInstallationsPath());
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

        /** @noinspection PhpAssignmentInConditionInspection */
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
     * @throws Exception When process execution is not successful
     * @return string
     */
    private static function exec($command, $executionPath) {
        $process = new \Symfony\Component\Process\Process($command, $executionPath);
        $process->run();

        if (!$process->isSuccessful()) {
            $msg = $process->getErrorOutput();
            if (!$msg) {
                // e.g. WP-CLI outputs to STDOUT instead of STDERR
                $msg = $process->getOutput();
            }
            throw new Exception('Error executing cmd \'' . $command . '\': ' . $msg);
        }

        return $process->getOutput();
    }

    /**
     * Executes a WP-CLI command
     * http://wp-Cli.org/commands/
     *
     * @param string $command
     * @param string $subcommand
     * @param array $args
     * @return string
     */
    public static function runWpCliCommand($command, $subcommand, $args = array()) {
        $cliCommand = "wp $command";

        if (is_array($subcommand)) {
            $args = $subcommand;
        } else {
            $cliCommand .= " $subcommand";
        }

        foreach ($args as $name => $value) {
            if (is_int($name)) { // position based argument without name
                $cliCommand .= " \"$value\"";
            } elseif ($value) {
                $escapedValue = \Symfony\Component\Process\ProcessUtils::escapeArgument($value);
                $cliCommand .= " --$name=$escapedValue";
            } else {
                $cliCommand .= " --$name";
            }
        }

        return self::exec($cliCommand, self::$config->getSitePath());
    }
}

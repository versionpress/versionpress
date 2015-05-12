<?php

namespace VersionPress\Tests\Automation;

use Exception;
use Nette\Utils\Strings;
use Symfony\Component\Process\Process;
use VersionPress\Tests\Utils\SiteConfig;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\ProcessUtils;


/**
 * Automates some common tasks like setting up a WP site, installing VersionPress, working with posts, comments etc.
 *
 * You should have the whole development environment set up as described on our wiki. Specifically, these are required:
 *
 *  - WP-CLI (`wp --info` works in console)
 *  - NPM packages installed in <project_root>
 *  - Gulp (`gulp -v` works in console)
 *  - `test-config.neon` file created in `versionpress/tests`
 *  - Vagrant configuration as described on the wiki
 *
 * Currently, WpAutomation is a set of static functions as of v1; other options will be considered for v2, see WP-56.
 *
 * Note: Currently, the intention is to add supported tasks as public methods to this class. If this gets
 * unwieldy it will probably be split into multiple files / classes.
 */
class WpAutomation {


    /** @var SiteConfig */
    private $siteConfig;

    /**
     * @param SiteConfig $siteConfig
     */
    function __construct($siteConfig) {
        $this->siteConfig = $siteConfig;
    }


    /**
     * Does a full setup of a WP site including removing the old site,
     * downloading files from wp.org, setting up a fresh database, executing
     * the install script etc.
     *
     * Database as specified in the config file must exist and be accessible.
     *
     * It takes optional parameter entityCounts, that is an array containing
     * an amount of generated entities - {@see populateSite}.
     *
     * @param array $entityCounts
     */
    public function setUpSite($entityCounts = array()) {
        $this->prepareFiles();
        $this->createConfigFile();
        $this->clearDatabase();
        $this->installWp();
        $this->populateSite($entityCounts);
    }

    /**
     * Returns true if the site is installed and working
     *
     * @return bool
     */
    public function isSiteSetUp() {
        try {
            $this->runWpCliCommand("core", "is-installed");
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
    public function isVersionPressInitialized() {
        return is_file($this->siteConfig->path . '/wp-content/vpdb/.active');
    }

    /**
     * Copies VP files to the test site and possibly removes all old files from there. It does so using
     * a Gulp script which specifies which paths to include and which ones to ignore.
     * See <project_root>\gulpfile.js.
     *
     * @param bool $createConfigFile By default, creates a vpconfig file after the files are copied
     * @throws Exception
     */
    public function copyVersionPressFiles($createConfigFile = true) {
        $versionPressDir = __DIR__ . '/../..';
        $gulpBaseDir = $versionPressDir . '/../..'; // project root as checked out from our repository
        $this->exec('gulp test-deploy', $gulpBaseDir); // this also cleans the destination directory, see gulpfile.js "clean" task
        if ($createConfigFile) {
            $this->createVpconfigFile();
        }
    }

    /**
     * Creates vpconfig file based on configuration in TestConfig
     */
    public function createVpconfigFile() {
        foreach ($this->siteConfig->vpConfig as $key => $value) {
            if (isset($value)) {
                $this->runWpCliCommand("vp", "config", array($key, $value, "require" => "wp-content/plugins/versionpress/src/Cli/vp.php"));
            }
        }
    }

    public function activateVersionPress() {
        $activateCommand = "wp plugin activate versionpress";
        $this->exec($activateCommand);
    }

    public function uninstallVersionPress() {
        $this->runWpCliCommand('plugin', 'deactivate', array('versionpress'));
        $this->runWpCliCommand('plugin', 'uninstall', array('versionpress'));
    }

    /**
     * Creates new post using WP-CLI. Returns ID of created post.
     *
     * @see wp_insert_post()
     * @param array $post (as wp_insert_post)
     * @return int
     */
    public function createPost(array $post) {
        $post["porcelain"] = null; // wp-Cli returns only id
        return intval($this->runWpCliCommand('post', 'create', $post));
    }

    /**
     * Changes the post using WP-CLI.
     *
     * @param $id
     * @param $changes
     */
    public function editPost($id, $changes) {
        array_unshift($changes, $id);
        $this->runWpCliCommand('post', 'update', $changes);
    }

    /**
     * Deletes the post using WP-CLI.
     *
     * @param $id
     */
    public function deletePost($id) {
        $args = array($id, '--force');
        $this->runWpCliCommand('post', 'delete', $args);
    }

    /**
     * Creates new comment using WP-CLI. Returns ID of created comment.
     *
     * @param array $comment (as wp_insert_comment)
     * @return int
     */
    public function createComment(array $comment) {
        $comment["porcelain"] = null;  // wp-Cli returns only id
        return intval($this->runWpCliCommand("comment", "create", $comment));
    }

    /**
     * Changes the comment using WP-CLI.
     *
     * @param $id
     * @param $changes
     */
    public function editComment($id, $changes) {
        array_unshift($changes, $id);
        $this->runWpCliCommand('comment', 'update', $changes);
    }

    /**
     * Deletes the comment using WP-CLI.
     *
     * @param $id
     */
    public function deleteComment($id) {
        $args = array($id, '--force');
        $this->runWpCliCommand('comment', 'delete', $args);
    }

    public function trashComment($id) {
        $this->runWpCliCommand('comment', 'trash', array($id));
    }

    public function untrashComment($id) {
        $this->runWpCliCommand('comment', 'untrash', array($id));
    }

    public function approveComment($id) {
        $this->runWpCliCommand('comment', 'approve', array($id));
    }

    public function unapproveComment($id) {
        $this->runWpCliCommand('comment', 'unapprove', array($id));
    }

    public function spamComment($id) {
        $this->runWpCliCommand('comment', 'spam', array($id));
    }

    public function unspamComment($id) {
        $this->runWpCliCommand('comment', 'unspam', array($id));
    }

    public function getComments() {
        return json_decode($this->runWpCliCommand('comment', 'list', array('format' => 'json')));
    }

    /**
     * Creates new user using WP-CLI. Returns ID of created user.
     *
     * @param array $user (as wp_insert_comment)
     * @return int
     */
    public function createUser(array $user) {
        $args = array($user["user_login"], $user["user_email"]);
        unset($user["user_login"], $user["user_email"]);
        $args = array_merge($args, $user);
        $args["porcelain"] = null;  // wp-Cli returns only id
        return intval($this->runWpCliCommand("user", "create", $args));
    }

    /**
     * Changes the user using WP-CLI.
     *
     * @param $id
     * @param $changes
     */
    public function editUser($id, $changes) {
        array_unshift($changes, $id);
        $this->runWpCliCommand('user', 'update', $changes);
    }

    /**
     * Deletes the user using WP-CLI.
     *
     * @param $id
     */
    public function deleteUser($id) {
        $args = array($id, 'yes' => null);
        $this->runWpCliCommand('user', 'delete', $args);
    }

    /**
     * Changes the user using WP-CLI.
     *
     * @param $id
     * @param $name
     * @param $value
     */
    public function editUserMeta($id, $name, $value) {
        $this->runWpCliCommand('user', 'meta update', func_get_args());
    }

    /**
     * Creates new option using WP-CLI.
     *
     * @param string $name
     * @param mixed $value
     */
    public function createOption($name, $value) {
        $this->runWpCliCommand('option', 'add', array($name, $value));
    }

    /**
     * Changes option with given name using WP-CLI.
     *
     * @param string $name
     * @param mixed $value
     */
    public function editOption($name, $value) {
        $this->runWpCliCommand('option', 'update', array($name, $value));
    }

    /**
     * Deletes option with given name using WP-CLI.
     *
     * @param string $name
     */
    public function deleteOption($name) {
        $this->runWpCliCommand('option', 'delete', array($name));
    }

    /**
     * Returns stylesheet of current theme.
     *
     * @return string
     */
    public function getCurrentTheme() {
        $status = $this->runWpCliCommand('theme', 'status');
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
    public function switchTheme($theme) {
        $this->runWpCliCommand('theme', 'activate', array($theme));
    }

    /**
     * Returns list of sidebar IDs defined by current template (without wp_inactive_widgets).
     *
     * @return array
     */
    public function getSidebars() {
        $sidebarsJson = $this->runWpCliCommand('sidebar', 'list', array('format' => 'json', 'fields' => 'id'));
        $sidebars = json_decode($sidebarsJson);
        $sidebarIds = array_map(function ($sidebar) {
            return $sidebar->id;
        }, $sidebars);
        $sidebarIds = array_filter($sidebarIds, function ($id) {
            return $id != 'wp_inactive_widgets';
        });
        return $sidebarIds;
    }

    /**
     * Returns list of widgets in given sidebar.
     *
     * @param string $sidebar sidebar id
     * @return array
     */
    public function getWidgets($sidebar) {
        $widgetsJson = $this->runWpCliCommand('widget', 'list', array($sidebar, 'format' => 'json', 'fields' => 'id'));
        $widgets = json_decode($widgetsJson);
        $widgetIds = array_map(function ($widget) {
            return $widget->id;
        }, $widgets);
        return $widgetIds;
    }

    /**
     * Deletes widget(s)
     *
     * @param string[]|string $widgets Name of widget or list of widgets
     */
    public function deleteWidgets($widgets) {
        $widgets = trim(is_array($widgets) ? join(' ', $widgets) : $widgets);
        if (strlen($widgets) > 0) {
            $this->exec('wp widget delete ' . $widgets);
        }
    }

    public function importMedia($files) {
        return $this->runWpCliCommand('media', 'import', array($files, 'porcelain' => null));
    }

    /**
     * Creates new menu using WP-CLI. Returns ID of created menu.
     *
     * @param string $name
     * @return int
     */
    public function createMenu($name) {
        $menu = array(
            $name,
            "porcelain" => null);
        return intval($this->runWpCliCommand("menu", "create", $menu));
    }

    /**
     * Changes the menu using WP-CLI.
     *
     * @param $id
     * @param $name
     */
    public function editMenu($id, $name) {
        $changes = array(
            "nav_menu",
            $id,
            "name" => $name);
        $this->runWpCliCommand("term", "update", $changes);
    }

    /**
     * Adds menu item using WP-CLI. Returns ID of created menu item.
     *
     * @param int|string $menu
     * @param string $type post|custom|term
     * @param array $item
     * @return int
     */
    public function addMenuItem($menu, $type, $item) {
        array_unshift($item, $menu);
        $item["porcelain"] = null;
        return intval($this->runWpCliCommand("menu", "item add-".$type, $item));
    }

    /**
     * Updates menu item using WP-CLI.
     *
     * @param int $id
     * @param array $changes
     * @return int
     */
    public function editMenuItem($id, $changes) {
        array_unshift($changes, $id);
        $this->runWpCliCommand("menu", "item update", $changes);
    }

    /**
     * Removes menu item using WP-CLI.
     *
     * @param int $id
     */
    public function removeMenuItem($id) {
        $this->runWpCliCommand("menu", "item delete", array($id));
    }

    /**
     * Deletes menu item using WP-CLI.
     *
     * @param int|string $menu
     */
    public function deleteMenu($menu) {
        $this->runWpCliCommand("menu", "delete", array($menu));
    }

    /**
     * Activates VersionPress plugin and runs the Initializer
     */
    public function initializeVersionPress() {
        $this->runWpCliCommand('plugin', 'activate', array('versionpress'));
        $code = 'global $versionPressContainer; $initializer = $versionPressContainer->resolve(VersionPress\DI\VersionPressServices::INITIALIZER); $initializer->initializeVersionPress();';
        $this->runWpCliCommand('eval', null, array($code));
    }

    /**
     * Populates the site with random entities. Their counts are specified by parameter $entityCounts:
     * array(
     *   'posts' => 100,
     *   'comments => 500,
     *   'options' => 50,
     *   'users' => 10,
     *   'terms' => 20
     * )
     *
     * @param $entityCounts
     * @throws Exception
     */
    public function populateSite($entityCounts) {
        if (count($entityCounts) == 0) {
            return;
        }

        $vpAutomateFile = __DIR__ . '/vp-automate.php';

        $entityParameters = "";
        foreach ($entityCounts as $entity => $count) {
            $entityParameters .= "--$entity=$count ";
        }

        $command = "wp --require=\"$vpAutomateFile\" vp-automate generate $entityParameters";
        $this->exec($command, $this->siteConfig->path);
    }

    /**
     * Puts WP directory to a default state, as if one manually downloaded the WordPress ZIP
     * and extracted it there. Removes all old files if necessary.
     */
    private function prepareFiles() {
        $this->ensureCleanInstallationIsAvailable();
        FileSystem::removeContent($this->siteConfig->path);
        FileSystem::copyDir($this->getCleanInstallationPath(), $this->siteConfig->path);
    }

    /**
     * Ensures that the clean installation of WordPress is available locally. If not,
     * downloads it from wp.org and stores it as `<clean-installations-dir>/<version>`.
     */
    private function ensureCleanInstallationIsAvailable() {

        if (!is_dir($this->getCleanInstallationPath())) {
            $downloadPath = $this->getCleanInstallationPath();
            FileSystem::mkdir($downloadPath);
            $wpVersion = $this->siteConfig->wpVersion;
            $downloadCommand = "wp core download --path=\"$downloadPath\" --version=$wpVersion";

            $this->exec($downloadCommand, null, false);
        }
    }

    /**
     * Returns a path where a clean installation of the configured WP version is stored and cached.
     *
     * @return string
     */
    private function getCleanInstallationPath() {

        $homeDir = getenv('HOME') ?: getenv('HOMEDRIVE') . getenv('HOMEPATH');
        $wpCliCacheDir = getenv('WP_CLI_CACHE_DIR') ?: "$homeDir/.wp-cli/cache";

        return "$wpCliCacheDir/clean-installations/{$this->siteConfig->wpVersion}";
    }

    /**
     * Creates wp-config.php
     */
    private function createConfigFile() {
        $args = array();
        $args["dbname"] = $this->siteConfig->dbName;
        $args["dbuser"] = $this->siteConfig->dbUser;
        $args["dbprefix"] = $this->siteConfig->dbTablePrefix;
        if ($this->siteConfig->dbPassword) $args["dbpass"] = $this->siteConfig->dbPassword;
        if ($this->siteConfig->dbHost) $args["dbhost"] = $this->siteConfig->dbHost;

        $this->runWpCliCommand("core", "config", $args);
    }

    /**
     * Deletes all tables from the database.
     */
    private function clearDatabase() {
        $this->runWpCliCommand("db", "reset", array("yes" => null));
    }

    /**
     * Installs WordPress. Assumes that files have been prepared on the file system, database is clean
     * and wp-config.php has been created.
     */
    private function installWp() {
        $cmdArgs = array(
            "url" => $this->siteConfig->url,
            "title" => $this->siteConfig->title,
            "admin_name" => $this->siteConfig->adminName,
            "admin_email" => $this->siteConfig->adminEmail,
            "admin_password" => $this->siteConfig->adminPassword
        );

        $this->runWpCliCommand("core", "install", $cmdArgs);
    }

    /**
     * Executes a command. If the command is WP-CLI command (starts with "wp ...") it might be rewritten
     * for remote execution on Vagrant depending on the config 'is-vagrant' value.
     *
     * @param string $command
     * @param string $executionPath Working directory for the command. If null, the path will be determined
     *   automatically (for WP-CLI commands, it depends whether they will be run locally or on Vagrant).
     * @param bool $autoSshTunnelling By default WP-CLI commands are rewritten to their SSH version if the site config
     *   says the site is a Vagrant site. In rare cases like "wp core download" we need to run WP-CLI locally even
     *   though the site is "remote" and setting this parameter to false enables that.
     *
     * @param bool $debug
     * @return string When process execution is not successful
     * @throws Exception
     */
    private function exec($command, $executionPath = null, $autoSshTunnelling = true, $debug = false) {

        $command = $this->rewriteWpCliCommand($command, $autoSshTunnelling);

        if (!$executionPath) {
            $executionPath = $this->siteConfig->isVagrant ? __DIR__ . '/..' : $this->siteConfig->path;
        }

        // Changing env variables for debugging
        // We don't need the xdebug enabled in the subprocesses,
        // but sometimes on the other hand we need it enabled only in the subprocess.
        $isDebug = isset($_SERVER["XDEBUG_CONFIG"]);
        if ($isDebug == $debug) {
            $env = null; // same as this process
        } elseif ($debug) {
            $env = $_SERVER;
            $env["XDEBUG_CONFIG"] = "idekey=xdebug"; // turn debug on
        } else {
            $env = $_SERVER;
            unset($env["XDEBUG_CONFIG"]); // turn debug off
        }

        $process = new Process($command, $executionPath, $env);
        $process->run();

        if (!$process->isSuccessful()) {
            $msg = $process->getErrorOutput();
            if (!$msg) {
                // e.g. WP-CLI outputs to STDOUT instead of STDERR
                $msg = $process->getOutput();
            }
            throw new Exception("Error executing cmd '$command' from working directory '$executionPath':\n$msg");
        }

        return $process->getOutput();
    }

    /**
     * Executes a WP-CLI command
     * http://wp-Cli.org/commands/
     *
     * @param string $command Like "core"
     * @param string $subcommand Like "config". Might be null, e.g. if the main command is "eval" there is no subcommand.
     * @param array $args Like array("dbname" => "wordpress", "dbuser" => "wpuser", "positionalargument") which will produce
     *   something like `--dbname='wordpress' --dbuser='wpuser' 'positionalargument'`
     * @param bool $debug
     * @return string
     * @throws Exception
     */
    public function runWpCliCommand($command, $subcommand, $args = array(), $debug = false) {

        $cliCommand = "wp $command";

        if ($subcommand) {
            $cliCommand .= " $subcommand";
        }

        foreach ($args as $name => $value) {
            if (is_int($name)) { // positional argument
                $cliCommand .= " " . $this->vagrantSensitiveEscapeShellArg($value);
            } elseif ($value !== null) {
                $escapedValue = $this->vagrantSensitiveEscapeShellArg($value);
                $cliCommand .= " --$name=$escapedValue";
            } else {
                $cliCommand .= " --$name";
            }
        }

        return $this->exec($cliCommand, null, true, $debug);
    }

    private function vagrantSensitiveEscapeShellArg($arg) {
        return ProcessUtils::escapeshellarg($arg, $this->siteConfig->isVagrant ? "linux" : null);
    }

    /**
     * Rewrites WP-CLI command to use a well-known binary and to possibly rewrite it for remote
     * execution over SSH. If the command is not a WP-CLI command (doesn't start with "wp ..."),
     * no rewriting is done.
     *
     * @param string $command
     * @param $autoSshTunnelling
     * @return string
     */
    private function rewriteWpCliCommand($command, $autoSshTunnelling) {

        if (!Strings::startsWith($command, "wp ")) {
            return $command;
        }

        $command = substr($command, 3); // strip "wp " prefix

        if ($this->siteConfig->isVagrant && $autoSshTunnelling) {
            $command = "ssh \"$command\" --host=" . escapeshellarg($this->siteConfig->name);
        }

        $command = "php " . escapeshellarg($this->getWpCli()) . " $command";

        return $command;


    }

    /**
     * Checks whether a WP-CLI binary is available, possibly downloads it and returns the path to it.
     *
     * We use a custom WP-CLI PHAR (latest stable) mainly because this is what wp-cli-ssh installs into Vagrant virtual
     * machines and we want to get the same behavior both locally and in Vagrant boxes. The local custom binary
     * is re-downloaded every day to keep it fresh (stable WP-CLI releases go out every couple of months).
     *
     * @return string The path to the custom WP-CLI PHAR.
     */
    public function getWpCli() {
        $wpCliPath = sys_get_temp_dir() . '/wp-cli-latest-stable.phar';
        $wpCliTmpPath = $wpCliPath . '.tmp';
        if (!file_exists($wpCliPath) || $this->fileIsOlderThanDays($wpCliPath, 1)) {
            file_put_contents($wpCliTmpPath, fopen("https://github.com/wp-cli/builds/blob/gh-pages/phar/wp-cli.phar?raw=true", 'r'));
            $checksum = trim(file_get_contents('https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar.md5'));

            if ($checksum != md5_file($wpCliTmpPath)) {
                trigger_error("Wrong checksum of WP-CLI PHAR", E_USER_NOTICE);
            } else {
                rename($wpCliTmpPath, $wpCliPath);
            }
        }
        return $wpCliPath;
    }

    private function fileIsOlderThanDays($filePath, $days) {
        return time() - filemtime($filePath) >= 60 * 60 * 24 * $days;
    }

}

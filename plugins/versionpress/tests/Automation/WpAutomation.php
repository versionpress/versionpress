<?php

namespace VersionPress\Tests\Automation;

use Exception;
use Nette\Utils\Strings;
use VersionPress\Tests\Utils\SiteConfig;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\Process;
use VersionPress\Utils\ProcessUtils;

/**
 * Automates tasks like setting up a WP site, installing VersionPress, creating posts, etc. for tests
 * based on test-config.yml. Most of the functionality is provided by WP-CLI which is downloaded automatically
 * if it's not available locally.
 *
 * NOTE: this class dates back to VersionPress 1.0 and contains some legacy approaches.
 * For example, methods for manipulating WP site entities could be moved to tests and only
 * basic site automation could stay here.
 */
class WpAutomation
{


    /** @var SiteConfig */
    private $siteConfig;
    /** @var string */
    private $wpCliVersion;

    /**
     * @param SiteConfig $siteConfig
     * @param string $wpCliVersion
     */
    public function __construct($siteConfig, $wpCliVersion)
    {
        $this->siteConfig = $siteConfig;
        $this->wpCliVersion = $wpCliVersion;
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
    public function setUpSite($entityCounts = [])
    {
        FileSystem::removeContent($this->siteConfig->path);

        if ($this->siteConfig->installationType === 'standard') {
            $this->prepareStandardWpInstallation();
        } elseif ($this->siteConfig->installationType === 'composer') {
            $this->createPedestalBasedSite();
        }

        $this->clearDatabase();
        $this->installWordPress();

        if (!$this->siteConfig->wpAutoupdate) {
            $this->disableAutoUpdate();
        }

        $this->populateSite($entityCounts);
    }

    /**
     * Returns true if the site is installed and working
     *
     * @return bool
     */
    public function isSiteSetUp()
    {
        try {
            $this->runWpCliCommand("core", "is-installed");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns true if VersionPress is active and tracking the site
     *
     * @return bool
     */
    public function isVersionPressInitialized()
    {
        $vpdbDir = $this->getVpdbDir();
        return $vpdbDir && is_file($vpdbDir . '/.active');
    }

    /**
     * Copies VersionPress from testenv (`/opt/versionpress`) to the test site. Leaves the files owned by `root`
     * which tests that we treat the plugin location as read-only (generally a good thing).
     */
    public function copyVersionPressFiles()
    {
        // Does a copy of everything incl. tests and dev dependencies which is not ideal but the alternatives
        // have their issues as well:
        //
        // - Invoking the canonical Gulp task would require bundling Node and installing dependencies in testenv (slow).
        // - More careful `cp` code here would duplicate rules in Gulpfile (a bit risky).
        //
        FileSystem::copyDir('/opt/versionpress', $this->siteConfig->path . '/wp-content/plugins/versionpress');
        $this->exec("chown -f -R www-data:www-data {$this->siteConfig->path}/wp-content/plugins/versionpress");
    }

    /**
     * Activates VersionPress as a plugin (does not start tracking the site; use `initializeVersionPress()`
     * for that).
     */
    public function activateVersionPress()
    {
        $activateCommand = "wp plugin activate versionpress";
        $this->exec($activateCommand);
    }

    public function uninstallVersionPress()
    {
        $this->runWpCliCommand('plugin', 'deactivate', ['versionpress']);
        $this->runWpCliCommand('plugin', 'uninstall', ['versionpress']);
    }

    /**
     * Creates new post using WP-CLI. Returns ID of created post.
     *
     * @see wp_insert_post()
     * @param array $post (as wp_insert_post)
     * @return int
     */
    public function createPost(array $post)
    {
        $post["porcelain"] = null; // wp-Cli returns only id
        return intval($this->runWpCliCommand('post', 'create', $post));
    }

    /**
     * Changes the post using WP-CLI.
     *
     * @param $id
     * @param $changes
     */
    public function editPost($id, $changes)
    {
        array_unshift($changes, $id);
        $this->runWpCliCommand('post', 'update', $changes);
    }

    /**
     * Deletes the post using WP-CLI.
     *
     * @param $id
     */
    public function deletePost($id)
    {
        $args = [$id, '--force'];
        $this->runWpCliCommand('post', 'delete', $args);
    }

    /**
     * Creates new comment using WP-CLI. Returns ID of created comment.
     *
     * @param array $comment (as wp_insert_comment)
     * @return int
     */
    public function createComment(array $comment)
    {
        $comment["porcelain"] = null;  // wp-Cli returns only id
        return intval($this->runWpCliCommand("comment", "create", $comment));
    }


    /**
     * Creates new commentmeta using WP-CLI for given comment.
     *
     * @param $id
     * @param $name
     * @param $value
     */
    public function createCommentMeta($id, $name, $value)
    {
        $this->runWpCliCommand('comment', 'meta update', func_get_args());
    }

    /**
     * Deletes commentmeta using WP-CLI for given comment.
     *
     * @param $id
     * @param $name
     */
    public function deleteCommentMeta($id, $name)
    {
        $this->runWpCliCommand('comment', 'meta delete', func_get_args());
    }

    /**
     * Changes the comment using WP-CLI.
     *
     * @param $id
     * @param $changes
     */
    public function editComment($id, $changes)
    {
        array_unshift($changes, $id);
        $this->runWpCliCommand('comment', 'update', $changes);
    }

    /**
     * Deletes the comment using WP-CLI.
     *
     * @param $id
     */
    public function deleteComment($id)
    {
        $args = [$id, '--force'];
        $this->runWpCliCommand('comment', 'delete', $args);
    }

    public function trashComment($id)
    {
        $this->runWpCliCommand('comment', 'trash', [$id]);
    }

    public function untrashComment($id)
    {
        $this->runWpCliCommand('comment', 'untrash', [$id]);
    }

    public function approveComment($id)
    {
        $this->runWpCliCommand('comment', 'approve', [$id]);
    }

    public function unapproveComment($id)
    {
        $this->runWpCliCommand('comment', 'unapprove', [$id]);
    }

    public function spamComment($id)
    {
        $this->runWpCliCommand('comment', 'spam', [$id]);
    }

    public function unspamComment($id)
    {
        $this->runWpCliCommand('comment', 'unspam', [$id]);
    }

    public function getComments()
    {
        return json_decode($this->runWpCliCommand('comment', 'list', ['format' => 'json']));
    }

    /**
     * Creates new user using WP-CLI. Returns ID of created user.
     *
     * @param array $user (as wp_insert_comment)
     * @return int
     */
    public function createUser(array $user)
    {
        $args = [$user["user_login"], $user["user_email"]];
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
    public function editUser($id, $changes)
    {
        array_unshift($changes, $id);
        $this->runWpCliCommand('user', 'update', $changes);
    }

    /**
     * Deletes the user using WP-CLI.
     *
     * @param $id
     */
    public function deleteUser($id)
    {
        $args = [$id, 'yes' => null];
        $this->runWpCliCommand('user', 'delete', $args);
    }

    /**
     * Changes the user using WP-CLI.
     *
     * @param $id
     * @param $name
     * @param $value
     */
    public function editUserMeta($id, $name, $value)
    {
        $this->runWpCliCommand('user', 'meta update', func_get_args());
    }

    /**
     * Creates new option using WP-CLI.
     *
     * @param string $name
     * @param mixed $value
     */
    public function createOption($name, $value)
    {
        $this->runWpCliCommand('option', 'add', [$name, $value]);
    }

    /**
     * Changes option with given name using WP-CLI.
     *
     * @param string $name
     * @param mixed $value
     */
    public function editOption($name, $value)
    {
        $this->runWpCliCommand('option', 'update', [$name, $value]);
    }

    /**
     * Deletes option with given name using WP-CLI.
     *
     * @param string $name
     */
    public function deleteOption($name)
    {
        $this->runWpCliCommand('option', 'delete', [$name]);
    }

    /**
     * Returns stylesheet of current theme.
     *
     * @return string
     */
    public function getCurrentTheme()
    {
        $status = $this->runWpCliCommand('theme', 'status');
        $status = preg_replace("/\033\[[^m]*m/", '', $status); // remove formatting

        preg_match_all("/^[^A-Z]*([A-Z]+)[^a-z]+([a-z\-]+).*$/m", $status, $matches);

        foreach ($matches[1] as $lineNumber => $status) {
            if (Strings::contains($status, 'A')) {
                return $matches[2][$lineNumber];
            }
        }

        return null; // this should never happen, there is always some activate theme
    }

    /**
     * @param string $theme Theme stylesheet
     */
    public function switchTheme($theme)
    {
        $this->runWpCliCommand('theme', 'activate', [$theme]);
    }

    /**
     * Returns list of sidebar IDs defined by current template (without wp_inactive_widgets).
     *
     * @return array
     */
    public function getSidebars()
    {
        $sidebarsJson = $this->runWpCliCommand('sidebar', 'list', ['format' => 'json', 'fields' => 'id']);
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
    public function getWidgets($sidebar)
    {
        $widgetsJson = $this->runWpCliCommand('widget', 'list', [$sidebar, 'format' => 'json', 'fields' => 'id']);
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
    public function deleteWidgets($widgets)
    {
        $widgets = trim(is_array($widgets) ? join(' ', $widgets) : $widgets);
        if (strlen($widgets) > 0) {
            $this->exec('wp widget delete ' . $widgets);
        }
    }

    public function importMedia($files)
    {
        return $this->runWpCliCommand('media', 'import', [$files, 'porcelain' => null]);
    }

    /**
     * Creates new menu using WP-CLI. Returns ID of created menu.
     *
     * @param string $name
     * @return int
     */
    public function createMenu($name)
    {
        $menu = [
            $name,
            "porcelain" => null
        ];
        return intval($this->runWpCliCommand("menu", "create", $menu));
    }

    /**
     * Changes the menu using WP-CLI.
     *
     * @param $id
     * @param $name
     */
    public function editMenu($id, $name)
    {
        $changes = [
            "nav_menu",
            $id,
            "name" => $name
        ];
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
    public function addMenuItem($menu, $type, $item)
    {
        array_unshift($item, $menu);
        $item["porcelain"] = null;
        return intval($this->runWpCliCommand("menu", "item add-" . $type, $item));
    }

    /**
     * Updates menu item using WP-CLI.
     *
     * @param int $id
     * @param array $changes
     * @return int
     */
    public function editMenuItem($id, $changes)
    {
        array_unshift($changes, $id);
        $this->runWpCliCommand("menu", "item update", $changes);
    }

    /**
     * Removes menu item using WP-CLI.
     *
     * @param int $id
     */
    public function removeMenuItem($id)
    {
        $this->runWpCliCommand("menu", "item delete", [$id]);
    }

    /**
     * Deletes menu item using WP-CLI.
     *
     * @param int|string $menu
     */
    public function deleteMenu($menu)
    {
        $this->runWpCliCommand("menu", "delete", [$menu]);
    }

    /**
     * Activates VersionPress plugin and runs the Initializer. For just activation, use `activateVersionPress()`.
     */
    public function initializeVersionPress()
    {
        $this->runWpCliCommand('plugin', 'activate', ['versionpress']);
        $this->runWpCliCommand('vp', 'activate', ['yes' => null]);
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
    public function populateSite($entityCounts)
    {
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
    private function prepareStandardWpInstallation()
    {

        // mounted as root:root by Docker
        $this->exec("chown -f -R www-data:www-data /var/www/.wp-cli");

        $wpVersion = $this->siteConfig->wpVersion;
        $wpLocale = $this->siteConfig->wpLocale;
        $downloadCommand = "wp core download --path=\"{$this->siteConfig->path}\" --version=\"$wpVersion\" --force";
        if ($wpLocale) {
            $downloadCommand .= " --locale=$wpLocale";
        }

        $this->exec($downloadCommand);
        $this->exec("chown -f -R www-data:www-data {$this->siteConfig->path}");

        $this->createConfigFile();
    }

    /**
     * Creates wp-config.php
     */
    private function createConfigFile()
    {
        $args = [];
        $args["dbname"] = $this->siteConfig->dbName;
        $args["dbuser"] = $this->siteConfig->dbUser;
        $args["dbprefix"] = $this->siteConfig->dbTablePrefix;
        if ($this->siteConfig->dbPassword) {
            $args["dbpass"] = $this->siteConfig->dbPassword;
        }
        if ($this->siteConfig->dbHost) {
            $args["dbhost"] = $this->siteConfig->dbHost;
        }

        $args["skip-salts"] = null;
        $args["skip-check"] = null;
        $args["force"] = null;

        $this->runWpCliCommand("core", "config", $args);
    }

    /**
     * Deletes all tables from the database.
     */
    private function clearDatabase()
    {
        $this->runWpCliCommand("db", "reset", ["yes" => null]);
    }

    /**
     * Installs WordPress. Assumes that files have been prepared on the file system, database is clean
     * and wp-config.php has been created.
     */
    public function installWordPress()
    {
        $cmdArgs = [
            "url" => $this->siteConfig->url,
            "title" => $this->siteConfig->title,
            "admin_user" => $this->siteConfig->adminUser,
            "admin_email" => $this->siteConfig->adminEmail,
            "admin_password" => $this->siteConfig->adminPassword
        ];

        if (version_compare($this->wpCliVersion, '0.22.0', '>=')) {
            $cmdArgs['skip-email'] = null;
        }

        $this->runWpCliCommand("core", "install", $cmdArgs);
    }

    /**
     * Executes a command. If the command is WP-CLI command (starts with "wp ...")
     *
     * @param string $command
     * @param string $executionPath Working directory for the command. If null, the path will be determined
     *   automatically.
     * @param bool $debug
     * @param null|array $env
     * @return string When process execution is not successful
     * @throws Exception
     */
    private function exec($command, $executionPath = null, $debug = false, $env = null)
    {

        $command = $this->possiblyRewriteWpCliCommand($command);

        if (!$executionPath) {
            $executionPath = $this->siteConfig->path;
        }

        // Changing env variables for debugging
        // We don't need the xdebug enabled in the subprocesses,
        // but sometimes on the other hand we need it enabled only in the subprocess.
        $isDebug = isset($_SERVER["XDEBUG_CONFIG"]);
        $childEnv = $_SERVER;
        if ($isDebug == $debug) {
            // same as this process
        } elseif ($debug) {
            $childEnv["XDEBUG_CONFIG"] = "idekey=xdebug"; // turn debug on
        } else {
            unset($childEnv["XDEBUG_CONFIG"]); // turn debug off
        }

        $childEnv = array_merge($childEnv, (array)$env);

        $process = new Process($command, $executionPath, $childEnv);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception(
                "Error executing cmd '$command' from working directory " .
                "'$executionPath':\n{$process->getConsoleOutput()}"
            );
        }

        return $process->getOutput();
    }

    /**
     * Executes a WP-CLI command
     * http://wp-Cli.org/commands/
     *
     * @param string $command Like "core"
     * @param string $subcommand Like "config". Might be null, e.g. if the main command is "eval" there is no subcommand
     * @param array $args Like array("dbname" => "wordpress", "dbuser" => "wpuser", "positionalargument") which will
     *                    produce something like `--dbname='wordpress' --dbuser='wpuser' 'positionalargument'`
     * @param bool $debug
     * @return string
     * @throws Exception
     */
    public function runWpCliCommand($command, $subcommand, $args = [], $debug = false)
    {

        $cliCommand = "wp $command";

        if ($subcommand) {
            $cliCommand .= " $subcommand";
        }

        foreach ((array)$args as $name => $value) {
            if (is_int($name)) { // positional argument
                $cliCommand .= " " . ProcessUtils::escapeshellarg($value, null);
            } elseif ($value !== null) {
                $escapedValue = ProcessUtils::escapeshellarg($value, null);
                $cliCommand .= " --$name=$escapedValue";
            } else {
                $cliCommand .= " --$name";
            }
        }

        return $this->exec($cliCommand, null, $debug);
    }


    /**
     * If the command starts "wp ", it rewrites it to a full format. No transformation
     * is done for non-WP-CLI commands.
     *
     * @param string $command
     *
     * @return string
     */
    private function possiblyRewriteWpCliCommand($command)
    {
        if (!Strings::startsWith($command, "wp ")) {
            return $command;
        }

        $command = substr($command, 3); // strip "wp " prefix
        $command = "php " . ProcessUtils::escapeshellarg($this->getWpCli()) . " $command";

        return $command;
    }

    /**
     * Checks whether a WP-CLI binary is available, possibly downloads it and returns the path to it.
     *
     * If "latest-stable" version is used, it is re-downloaded every day to keep it fresh.
     *
     * @return string The path to the custom WP-CLI PHAR.
     */
    public function getWpCli()
    {
        $wpCliName = "wp-cli-{$this->wpCliVersion}.phar";


        $wpCliPath = sys_get_temp_dir() . '/' . $wpCliName;
        $wpCliTmpPath = $wpCliPath . '.tmp';

        if (!file_exists($wpCliPath)
            || ($this->wpCliVersion === "latest-stable" && $this->fileIsOlderThanDays($wpCliPath, 1))) {
            $pharResource = @fopen($this->getWpCliDownloadUrl(), 'r');
            if (!$pharResource) {
                return $wpCliPath; // we're probably offline or there was some kind of network error
            }

            file_put_contents($wpCliTmpPath, $pharResource);

            if ($this->wpCliVersion === "latest-stable" && !$this->checkLatestStableChecksum($wpCliTmpPath)) {
                trigger_error("Wrong checksum of WP-CLI PHAR", E_USER_NOTICE);
            } else {
                rename($wpCliTmpPath, $wpCliPath);
            }
        }
        return $wpCliPath;
    }

    private function fileIsOlderThanDays($filePath, $days)
    {
        return time() - filemtime($filePath) >= 60 * 60 * 24 * $days;
    }

    private function getWpCliDownloadUrl()
    {
        if ($this->wpCliVersion === "latest-stable") {
            return "https://github.com/wp-cli/builds/blob/gh-pages/phar/wp-cli.phar?raw=true";
        } else {
            return "https://github.com/wp-cli/wp-cli/releases/download/" .
            "v{$this->wpCliVersion}/wp-cli-{$this->wpCliVersion}.phar";
        }
    }

    private function checkLatestStableChecksum($wpCliTmpPath)
    {
        $checksum = trim(file_get_contents(
            'https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar.md5'
        ));
        return $checksum == md5_file($wpCliTmpPath);
    }

    private function disableAutoUpdate()
    {
        file_put_contents(
            $this->siteConfig->path . '/wp-config.php',
            "\ndefine( 'AUTOMATIC_UPDATER_DISABLED', true );\n",
            FILE_APPEND
        );
    }

    /**
     * Creates project structure similar to Bedrock.
     * Pedestal (https://github.com/versionpress/pedestal) is inpired by Bedrock. It only have
     * a standard wp-config-based configuration system and predefined Composer scripts for VersionPress.
     */
    private function createPedestalBasedSite()
    {
        $process = new Process('composer create-project -s dev versionpress/pedestal .', $this->siteConfig->path);
        $process->run();

        $this->updateConfigConstant('DB_NAME', $this->siteConfig->dbName);
        $this->updateConfigConstant('DB_USER', $this->siteConfig->dbUser);
        $this->updateConfigConstant('DB_PASSWORD', $this->siteConfig->dbPassword);
        $this->updateConfigConstant('DB_HOST', $this->siteConfig->dbHost);
        $this->updateConfigConstant('WP_HOME', $this->siteConfig->url);
    }

    private function updateConfigConstant($constant, $value, $variable = false)
    {
        $vpInternalCommandFile = __DIR__ . '/../../src/Cli/vp-internal.php';
        $this->runWpCliCommand(
            'vp-internal',
            'update-config',
            [$constant, $value, 'require' => $vpInternalCommandFile]
        );
    }

    public function getVpdbDir()
    {
        return $this->runWpCliCommand('eval', null, ['defined("VP_VPDB_DIR") && print(VP_VPDB_DIR);']) ?: null;
    }

    public function getAbspath()
    {
        static $abspath = false;

        if ($abspath === false) {
            $abspath = $this->runWpCliCommand('eval', null, ['print(ABSPATH);']) ?: null;
        }

        return $abspath;
    }

    public function getUploadsDir()
    {
        static $uploads = false;

        if ($uploads === false) {
            $uploads = $this->runWpCliCommand('eval', null, ['print(wp_upload_dir()["basedir"]);']) ?: null;
        }

        return $uploads;
    }

    public function getPluginsDir()
    {
        static $pluginsDir = false;

        if ($pluginsDir === false) {
            $pluginsDir = $this->runWpCliCommand('eval', null, ['print(WP_PLUGIN_DIR);']) ?: null;
        }

        return $pluginsDir;
    }

    public function getWebRoot()
    {
        static $pluginsDir = false;

        if ($pluginsDir === false) {
            $pluginsDir = $this->runWpCliCommand(
                'eval',
                null,
                ['print(dirname(\WP_CLI\Utils\locate_wp_config()));']
            ) ?: null;
        }

        return $pluginsDir;
    }
}

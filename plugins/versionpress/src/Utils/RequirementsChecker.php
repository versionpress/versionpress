<?php

namespace VersionPress\Utils;

use Exception;
use Nette\Utils\Strings;
use Symfony\Component\Filesystem\Exception\IOException;
use VersionPress\Utils\SystemInfo;
use VersionPress\Database\DbSchemaInfo;
use wpdb;

class RequirementsChecker {
    private $requirements = array();
    /**
     * @var wpdb
     */
    private $database;
    /**
     * @var DbSchemaInfo
     */
    private $schema;

    /** @var string[] */
    public static $compatiblePlugins = array(
        'akismet' => 'akismet/akismet.php',
        'advanced-custom-fields' => 'advanced-custom-fields/acf.php',
        'hello-dolly' => 'hello-dolly/hello.php',
        '_hello-dolly' => 'hello.php',
        'versionpress' => 'versionpress/versionpress.php',
    );

    public static $incompatiblePlugins = array(
        'wp-super-cache' => 'wp-super-cache/wp-cache.php'
    );

    /** @var bool */
    private $isWithoutCriticalErrors;
    /** @var bool */
    private $isEverythingFulfilled;

    function __construct($wpdb, DbSchemaInfo $schema) {

        $this->database = $wpdb;
        $this->schema = $schema;

        // Markdown can be used in the 'help' field

        $this->requirements[] = array(
            'name' => 'PHP 5.3',
            'level' => 'critical',
            'fulfilled' => version_compare(PHP_VERSION, '5.3.0', '>='),
            'help' => 'PHP 5.3 is currently required.'
        );

        $this->requirements[] = array(
            'name' => "'mbstring' extension",
            'level' => 'critical',
            'fulfilled' => extension_loaded('mbstring'),
            'help' => 'Extension `mbstring` is required.'
        );

        $this->requirements[] = array(
            'name' => 'Execute external commands',
            'level' => 'critical',
            'fulfilled' => $this->tryRunProcess(),
            'help' => 'PHP function `proc_open()` must be enabled as VersionPress uses it to execute Git commands. Please update your php.ini.'
        );

        $gitCheckResult = $this->tryGit();

        switch ($gitCheckResult) {

            case "no-git":
                $gitHelpMessage = '[Git](http://git-scm.com/) must be installed on the server. If you think it is then it\'s probably not visible to the web server user – please update its PATH. Alternatively, [configure VersionPress](http://docs.versionpress.net/en/getting-started/configuration#git-binary) to use specific Git binary. [Learn more](http://docs.versionpress.net/en/getting-started/installation-uninstallation#git).';
                break;

            case "wrong-version":
                $gitHelpMessage = 'Git version ' . SystemInfo::getGitVersion() . ' detected with which there are known issues. Please install at least version ' . self::GIT_MINIMUM_REQUIRED_VERSION . ' (this can be done side-by-side and VersionPress can be [configured](http://docs.versionpress.net/en/getting-started/configuration#git-binary) to use that specific Git version). [Learn more](http://docs.versionpress.net/en/getting-started/installation-uninstallation#git).';
                break;

            default:
                $gitHelpMessage = "";
        }

        $this->requirements[] = array(
            'name' => 'Git ' . self::GIT_MINIMUM_REQUIRED_VERSION . '+ installed',
            'level' => 'critical',
            'fulfilled' => $gitCheckResult == "ok",
            'help' => $gitHelpMessage
        );

        $this->requirements[] = array(
            'name' => 'Write access on the filesystem',
            'level' => 'critical',
            'fulfilled' => $this->tryWrite(),
            'help' => 'VersionPress needs write access in the site root, its nested directories and the <abbr title="' . sys_get_temp_dir() . '" style="border-bottom: 1px dotted; border-color: inherit;">system temp directory</abbr>. Please update the permissions.'
        );

        $this->requirements[] = array(
            'name' => 'wpdb hook',
            'level' => 'critical',
            'fulfilled' => is_writable(ABSPATH . WPINC . '/wp-db.php'),
            'help' => 'For VersionPress to do its magic, it needs to change the `wpdb` class and put some code there. ' .
                'To do so it needs write access to the `wp-includes/wp-db.php` file. Please update the permissions.'
        );

        $this->requirements[] = array(
            'name' => 'Not multisite',
            'level' => 'critical',
            'fulfilled' => !is_multisite(),
            'help' => 'Currently VersionPress does not support multisites. Stay tuned!'
        );

        $this->requirements[] = array(
            'name' => 'Standard directory layout',
            'level' => 'warning',
            'fulfilled' => $this->testDirectoryLayout(),
            'help' => 'It seems like you use customized project structure. VersionPress supports only some scenarios. [Learn more](http://docs.versionpress.net/en/feature-focus/custom-project-structure).'
        );


        $this->requirements[] = array(
            'name' => '.gitignore',
            'level' => 'critical',
            'fulfilled' => $this->testGitignore(),
            'help' => 'It seems you have already created .gitignore file in the site root. It\'s necessary to add some rules for VersionPress. Please add those from `wp-content/plugins/versionpress/src/Initialization/.gitignore.tpl`.'
        );

        $this->requirements[] = array(
            'name' => 'Access rules can be installed',
            'level' => 'warning',
            'fulfilled' => $this->tryAccessControlFiles(),
            'help' => 'VersionPress automatically tries to secure certain locations, like `wp-content/vpdb`. You either don\'t have a supported web server or rules cannot be enforced. [Learn more](http://docs.versionpress.net/en/getting-started/installation-uninstallation#supported-web-servers).'
        );


        $setTimeLimitEnabled = (false === strpos(ini_get("disable_functions"), "set_time_limit"));
        $countOfEntities = $this->countEntities();

        if ($setTimeLimitEnabled) {
            $help = "The initialization will take a little longer. This website contains $countOfEntities entities.";
        } else {
            $help = "The initialization may not finish. This website contains $countOfEntities entities.";
        }

        $this->requirements[] = array(
            'name' => 'Web size',
            'level' => 'warning',
            'fulfilled' => $countOfEntities < 500,
            'help' => $help
        );

        $unsupportedPluginsCount = $this->testExternalPlugins();
        $externalPluginsHelp = "You run $unsupportedPluginsCount external ". ($unsupportedPluginsCount == 1 ? "plugin" : "plugins") ." we have not tested yet. <a href='http://docs.versionpress.net/en/feature-focus/external-plugins'>Read more about 3rd party plugins support.</a>";

        $this->requirements[] = array(
            'name' => 'External plugins',
            'level' => 'warning',
            'fulfilled' => $unsupportedPluginsCount == 0,
            'help' => $externalPluginsHelp
        );

        $this->isWithoutCriticalErrors = array_reduce($this->requirements, function ($carry, $requirement) {
            return $carry && ($requirement['fulfilled'] || $requirement['level'] === 'warning');
        }, true);

        $this->isEverythingFulfilled = array_reduce($this->requirements, function ($carry, $requirement) {
            return $carry && $requirement['fulfilled'];
        }, true);

    }

    /**
     * Returns list of requirements and their fulfillment
     *
     * @return array
     */
    public function getRequirements() {
        return $this->requirements;
    }

    public function isWithoutCriticalErrors() {
        return $this->isWithoutCriticalErrors;
    }

    public function isEverythingFulfilled() {
        return $this->isEverythingFulfilled;
    }

    private function tryRunProcess() {
        try {
            $process = new Process("echo test");
            $process->run();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return string "ok", "no-git" or "wrong-version"
     */
    private function tryGit() {
        try {
            $gitVersion = SystemInfo::getGitVersion();
            return self::gitMatchesMinimumRequiredVersion($gitVersion) ? "ok" : "wrong-version";
        } catch (Exception $e) {
            return "no-git";
        }
    }

    private function tryWrite() {
        $filename = ".vp-try-write";
        $testPaths = array(
            ABSPATH,
            WP_CONTENT_DIR,
            sys_get_temp_dir()
        );

        $writable = true;

        foreach ($testPaths as $directory) {
            $filePath = $directory . '/' . $filename;
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @file_put_contents($filePath, "");
            $writable &= is_file($filePath);
            FileSystem::remove($filePath);

            // Trying to create file from process (issue #522)
            $process = new Process(sprintf("echo test > %s", escapeshellarg($filePath)));
            $process->run();
            $writable &= is_file($filePath);

            try {
                FileSystem::remove($filePath);
            } catch (IOException $ex) {
                $writable = false; // the file could not be deleted - the permissions are wrong
            }
        }

        return $writable;
    }

    private function tryAccessControlFiles() {
        $securedUrl = site_url() . '/wp-content/plugins/versionpress/temp/security-check.txt';
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        return @file_get_contents($securedUrl) === false; // intentionally @
    }

    private function testGitignore() {
        $gitignorePath = VP_PROJECT_ROOT . '/.gitignore';
        $gitignoreExists = is_file($gitignorePath);
        if (!$gitignoreExists) {
            return true;
        }

        $gitignoreContainsVersionPressRules = Strings::contains(file_get_contents($gitignorePath), 'plugins/versionpress');
        return $gitignoreContainsVersionPressRules;
    }

    private function testDirectoryLayout() {
        $uploadDirInfo = wp_upload_dir();

        $isStandardLayout = true;
        $isStandardLayout &= ABSPATH . 'wp-content' === WP_CONTENT_DIR;
        $isStandardLayout &= WP_CONTENT_DIR . '/plugins' === WP_PLUGIN_DIR;
        $isStandardLayout &= WP_CONTENT_DIR . '/themes' === get_theme_root();
        $isStandardLayout &= WP_CONTENT_DIR . '/uploads' === $uploadDirInfo['basedir'];
        $isStandardLayout &= is_file(ABSPATH . 'wp-config.php');

        return $isStandardLayout;
    }

    /**
     * Minimum required Git version
     */
    const GIT_MINIMUM_REQUIRED_VERSION = "1.9";

    /**
     * Returns true if git version matches the minimum required version. If minimum required version
     * is not given, RequirementsChecker::GIT_MINIMUM_REQUIRED_VERSION is used by default.
     *
     * @param string $gitVersion
     * @param string $minimumRequiredVersion
     * @return bool
     */
    public static function gitMatchesMinimumRequiredVersion($gitVersion, $minimumRequiredVersion = null) {
        $minimumRequiredVersion = $minimumRequiredVersion ? $minimumRequiredVersion : self::GIT_MINIMUM_REQUIRED_VERSION;
        return version_compare($gitVersion, $minimumRequiredVersion, ">=");

    }

    private function getNeededExecutionTime() {

        $entityCount = $this->countEntities();
        $okTreshold = 500 / 30; // entities per seconds
        return $entityCount / $okTreshold;
    }

    private function countEntities() {
        $entities = $this->schema->getAllEntityNames();
        $totalEntitiesCount = 0;

        foreach ($entities as $entity) {
            $table = $this->schema->getPrefixedTableName($entity);
            $totalEntitiesCount += $this->database->get_var("SELECT COUNT(*) FROM $table");
        }

        return $totalEntitiesCount;
    }

    /**
     * @return int Number of unsupported plugins.
     */
    private function testExternalPlugins() {
        $plugins = get_option('active_plugins');
        $unsupportedPluginsCount = 0;
        foreach($plugins as $plugin) {
            if(!in_array($plugin, self::$compatiblePlugins)) {
                $unsupportedPluginsCount++;
            }
        }
        return $unsupportedPluginsCount;
    }
}

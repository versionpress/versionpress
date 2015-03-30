<?php

namespace VersionPress\Utils;

use Exception;
use Nette\Utils\Strings;
use Symfony\Component\Process\Process;
use Utils\SystemInfo;
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
    private static $supportedPlugins = array(
        'Akismet',
        'Hello Dolly',
        'VersionPress',
    );

    function __construct(wpdb $database, DbSchemaInfo $schema) {

        $this->database = $database;
        $this->schema = $schema;

        // Markdown can be used in the 'help' field

        $this->requirements[] = array(
            'name' => 'PHP 5.3',
            'level' => 'critical',
            'fulfilled' => version_compare(PHP_VERSION, '5.3.0', '>='),
            'help' => 'PHP 5.3 is currently required. We might support PHP 5.2 (the minimum WordPress-required PHP version) in some future update.'
        );

        $this->requirements[] = array(
            'name' => 'Execute external commands',
            'level' => 'critical',
            'fulfilled' => $this->tryRunProcess(),
            'help' => 'PHP function `proc_open()` must be enabled as VersionPress uses it to execute Git commands. Please update your php.ini.'
        );

        $this->requirements[] = array(
            'name' => 'Git ' . self::GIT_MINIMUM_REQUIRED_VERSION . '+ installed',
            'level' => 'critical',
            'fulfilled' => $this->tryGit(),
            'help' => '[Git](http://git-scm.com/) must be installed on the server. If you think it is then the version number probably doesn\'t match or Git is not visible to the web server - please update your PATH or use `vpconfig`. <a href="http://docs.versionpress.net/en/getting-started/installation-uninstallation#git" target="_blank">Learn more</a>.'
        );

        $this->requirements[] = array(
            'name' => 'Write access on the filesystem',
            'level' => 'critical',
            'fulfilled' => $this->tryWrite(),
            'help' => 'VersionPress needs write access in the site root and all nested directories. Please update the permissions.'
        );

        $this->requirements[] = array(
            'name' => 'db.php hook',
            'level' => 'critical',
            'fulfilled' => !is_file(WP_CONTENT_DIR . '/db.php'),
            'help' => 'For VersionPress to do its magic, it needs to create a `wp-content/db.php` file and put some code there. ' .
                'However, this file is already occupied, possibly by some other plugin. Debugger plugins often do this so if you can, please disable them ' .
                'and remove the `db.php` file physically. If you can\'t, we have plans on how to deal with this and will ' .
                'ship a solution as part of some future VersionPress udpate (it is high on our priority list and should be fixed before the final 1.0 release).'
        );

        $this->requirements[] = array(
            'name' => 'Not multisite',
            'level' => 'critical',
            'fulfilled' => !is_multisite(),
            'help' => 'Currently VersionPress does not support multisites. Stay tuned!'
        );

        $this->requirements[] = array(
            'name' => 'Standard directory layout',
            'level' => 'critical',
            'fulfilled' => $this->testDirectoryLayout(),
            'help' => 'It\'s necessary to use standard WordPress directory layout with the current version of VersionPress.'
        );


        $this->requirements[] = array(
            'name' => '.gitignore',
            'level' => 'critical',
            'fulfilled' => $this->testGitignore(),
            'help' => 'It seems you have already created .gitignore file in the site root. It\'s necessary to add some rules for VersionPress. Please add those from `wp-content/plugins/versionpress/src/Initialization/.gitignore.tpl`.'
        );

        $this->requirements[] = array(
            'name' => '.htaccess or web.config support',
            'level' => 'warning',
            'fulfilled' => $this->tryAccessControlFiles(),
            'help' => 'VersionPress automatically tries to secure certain locations, like `wp-content/vpdb`. You either don\'t have a supported web server or rules cannot be enforced. [Learn more](http://docs.versionpress.net/en/getting-started/installation-uninstallation#supported-web-servers).'
        );


        $setTimeLimitEnabled = (false === strpos(ini_get("disable_functions"), "set_time_limit"));
        $countOfEntities = $this->countEntities();

        if ($setTimeLimitEnabled) {
            $help = "The initialization will take a little longer. This website contains $countOfEntities entities, which is a lot.";
        } else {
            $help = "The initialization may not finish. This website contains $countOfEntities entities, which is a lot.";
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

        $this->everythingFulfilled = array_reduce($this->requirements, function ($carry, $requirement) {
            return $carry && ($requirement['fulfilled'] || $requirement['level'] === 'warning');
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

    public function isEverythingFulfilled() {
        return $this->everythingFulfilled;
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

    private function tryGit() {
        try {
            $gitVersion = SystemInfo::getGitVersion();
            return self::gitMatchesMinimumRequiredVersion($gitVersion);
        } catch (Exception $e) {
            return false;
        }
    }

    private function tryWrite() {
        $filename = ".vp-try-write";
        $testPaths = array(
            ABSPATH,
            WP_CONTENT_DIR,
        );

        $writable = true;

        foreach ($testPaths as $directory) {
            $filePath = $directory . '/' . $filename;
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @file_put_contents($filePath, "");
            $writable &= is_file($filePath);
            FileSystem::remove($filePath);
        }

        return $writable;
    }

    private function tryAccessControlFiles() {
        $securedUrl = site_url() . '/wp-content/plugins/versionpress/temp/security-check.txt';
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        return @file_get_contents($securedUrl) === false; // intentionally @
    }

    private function testGitignore() {
        $gitignorePath = ABSPATH . '.gitignore';
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
        $plugins = get_plugins();
        $unsupportedPluginsCount = 0;
        foreach($plugins as $plugin) {
            if(!in_array($plugin['Name'], self::$supportedPlugins)) {
                $unsupportedPluginsCount++;
            }
        }
        return $unsupportedPluginsCount;
    }
}

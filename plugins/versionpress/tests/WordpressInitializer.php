<?php

class WordpressInitializer {
    /** @var TestConfig */
    private static $config;

    public static function initialize(TestConfig $config) {
        self::$config = $config;
        self::prepareFiles();
        self::configureWordpress();
        self::clearDatabase();
        self::installWordpress();
    }

    private static function prepareFiles() {
        $testedWordpressPath = self::getTestedVersionPath();
        if (!is_dir($testedWordpressPath)) {
            self::downloadWordpress();
        }
        self::clearLivePath();
        self::copyTestedVersionToLivePath();
    }

    private static function downloadWordpress() {
        $path = self::getTestedVersionPath();
        $version = self::$config->getTestWpVersion();
        $downloadCommand = "wp core download --path=\"$path\" --version=$version";
        self::exec($downloadCommand, self::$config->getWordpressClearInstallationsPath());
    }

    private static function getTestedVersionPath() {
        return self::$config->getWordpressClearInstallationsPath() . '/' . self::$config->getTestWpVersion();
    }

    private static function exec($command, $executionPath) {
        echo "Executing command: " . $command . "\n";
        $cwd = getcwd();
        chdir($executionPath);
        $result = exec($command);
        chdir($cwd);
        return $result;
    }

    private static function clearLivePath() {
        \Nette\Utils\FileSystem::delete(self::$config->getWordpressPath() . '/*');
    }

    private static function copyTestedVersionToLivePath() {
        $testedWordpressPath = self::getTestedVersionPath();
        $livePath = self::$config->getWordpressPath();
        Nette\Utils\FileSystem::copy($testedWordpressPath, $livePath);
    }

    private static function configureWordpress() {
        $args = array();
        $args["--dbname"] = self::$config->getDbName();
        $args["--dbuser"] = self::$config->getDbUser();
        if (self::$config->getDbPassword()) $args["--dbpass"] = self::$config->getDbPassword();
        if (self::$config->getDbPassword()) $args["--dbhost"] = self::$config->getDbHost();

        $configCommand = "wp core config";
        foreach ($args as $argName => $argValue) {
            $configCommand .= " $argName=\"$argValue\"";
        }

        self::exec($configCommand, self::$config->getWordpressPath());
    }

    private static function clearDatabase() {
        $mysqli = new mysqli(self::$config->getDbHost(), self::$config->getDbUser(), self::$config->getDbPassword(), self::$config->getDbName());
        $res = $mysqli->query('show tables');
        while($row = $res->fetch_row()) {
            $dropTableSql = "DROP TABLE $row[0]";
            $mysqli->query($dropTableSql);
        }
    }

    private static function installWordpress() {
        $url = self::$config->getWordpressUrl();
        $title = self::$config->getSiteTitle();
        $adminName = self::$config->getAdminName();
        $adminEmail = self::$config->getAdminEmail();
        $adminPassword = self::$config->getAdminPassword();
        $installCommand = sprintf('wp core install --url="%s" --title="%s" --admin_name="%s" --admin_email="%s" --admin_password="%s"',
                            $url, $title, $adminName, $adminEmail, $adminPassword);
        self::exec($installCommand, self::$config->getWordpressPath());
    }
}
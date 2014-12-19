<?php

/**
 * Base class for VersionPress Selenium tests. It brings these three main "features":
 *
 *  1. `test-config.ini` is used for configuration
 *  2. The site is automatically set up before the tests are run (VersionPress is copied, activated and initialized
 *     as well). If you don't want this pass `--force-setup` on command line or override the `setUpBeforeClass()`
 *     method in subclass.
 *  3. Admin is automatically logged in before the tests are run. If you don't want this override
 *     the `setUpPage()` method in subclass.
 */
abstract class SeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase {

    /**
     * Configuration read from `test-config.ini` and set to this variable from phpunit-bootstrap.php.
     *
     * @var TestConfig
     */
    public static $config;

    /**
     * Set from phpunit-bootstrap.php to true if `--force-setup` has been passed as a command line parameter.
     *
     * @var bool
     */
    public static $forceSetup;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);

        $this->setBrowser("firefox");

        $capabilities = $this->getDesiredCapabilities();
        if (self::$config->getFirefoxExecutable()) {
            $capabilities["firefox_binary"] = self::$config->getFirefoxExecutable();
        }
        $this->setDesiredCapabilities($capabilities);

        $this->setBrowserUrl(self::$config->getSiteUrl());
    }

    /**
     * Check if site is set up and VersionPress fully activated, and if not, do so.
     */
    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        if (self::$forceSetup || !WpAutomation::isSiteSetUp()) {
            WpAutomation::setUpSite();
        }

        if (self::$forceSetup || !WpAutomation::isVersionPressInitialized()) {
            WpAutomation::copyVersionPressFiles();
            WpAutomation::initializeVersionPress();
        }

    }

    public function setUpPage() {
        $this->loginIfNecessary();
    }

    private static $loggedIn = false;
    protected function loginIfNecessary() {

        if (self::$loggedIn) {
            return;
        }

        $this->url('wp-admin');
        try {
            $this->byId('user_login');
        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            // already logged in, do nothing
            return;
        }
        $this->byId('user_login')->value(self::$config->getAdminName());
        usleep(100000); // wait for change focus
        $this->byId('user_pass')->value(self::$config->getAdminPassword());
        $this->byId('loginform')->submit();

        self::$loggedIn = true;
    }


    //----------------------------
    // Some helper asserts
    //----------------------------

    /**
     * Simple wrapper for a common scenario that is actually easy to do without this
     * helper assert but unintuitive.
     *
     * See also {@link https://github.com/giorgiosironi/phpunit-selenium/blob/a6fdffdd56f4884ef39e09a9c62e5e4eb273e42c/Tests/Selenium2TestCaseTest.php#L1065 this test case}.
     *
     * @param string $cssSelector
     */
    protected function assertElementExists($cssSelector) {
        $this->byCssSelector($cssSelector);
    }

} 

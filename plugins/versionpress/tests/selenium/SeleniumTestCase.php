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
 *  4.
 */
abstract class SeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase {

    /**
     * Configuration read from `test-config.ini` and set to this variable from phpunit-bootstrap.php.
     *
     * @var TestConfig
     */
    public static $config;

    /**
     * Set from phpunit-bootstrap.php to true if `--force-setup` has been passed as a command line parameter
     * or if 'VP_FORCE_SETUP' environment variable is true (non-empty).
     *
     * @var bool
     */
    public static $forceSetup;

    /**
     * @var \VersionPress\Git\GitRepository
     */
    protected $gitRepository;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);

        $this->setBrowser("firefox");

        $capabilities = $this->getDesiredCapabilities();
        if (self::$config->getFirefoxExecutable()) {
            $capabilities["firefox_binary"] = self::$config->getFirefoxExecutable();
        }
        $this->setDesiredCapabilities($capabilities);

        $this->setBrowserUrl(self::$config->getSiteUrl());

        $this->gitRepository = new \VersionPress\Git\GitRepository(self::$config->getSitePath());
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

    protected function loginIfNecessary() {
        try {
            $this->byId('wpadminbar');
        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            $this->url('wp-admin');
            usleep(100 * 1000); // sometimes we need to wait for the page to fully load
            $this->byId('user_login')->value(self::$config->getAdminName());
            usleep(100 * 1000); // wait for change focus
            $this->byId('user_pass')->value(self::$config->getAdminPassword());
            $this->byId("loginform")->submit();
        }
    }

    protected function logOut() {
        $this->url('wp-login.php?action=logout');
        $this->byCssSelector('body>p>a')->click();
        $this->waitAfterRedirect();
    }


    //----------------------------
    // Helper asserts / methods
    //----------------------------

    /**
     * The built-in $seleniumElement->value('xyz') method only appends to an existing value,
     * and calling $selElement->clear() before it might create additional commit if the form
     * is watched by some WordPress JavaScript / AJAX. This method is a workaround that
     * truly sets the value (overwrites the original one), hopefully without sideeffects
     * (it is using jQuery for it).
     *
     * @param $cssSelector
     * @param $value
     */
    protected function setValue($cssSelector, $value) {
        $element = $this->byCssSelector($cssSelector);
        $element->clear();
        $element->value($value);
        $this->executeScript("jQuery('$cssSelector').val('$value')");
    }

    /**
     * Small wrapper aroung built-in execute() method
     *
     * @param string $code JavaScript code
     * @return string JS result, if any
     */
    protected function executeScript($code) {
        return $this->execute(array(
            'script' => $code,
            'args' => array()
        ));
    }

    /**
     * Selenium cannot click on hidden things, JavaScript can. Use this method instead
     * of `$this->byCssSelector('...')->click()` if you need to.
     *
     * @param string $cssSelector
     */
    protected function jsClick($cssSelector) {
        $this->executeScript("jQuery(\"$cssSelector\")[0].click()");
    }

    /**
     * Uses {@link jsClick} and waits for AJAX request.
     *
     * @param string $cssSelector
     */
    protected function jsClickAndWait($cssSelector) {
        $this->jsClick($cssSelector);
        usleep(100 * 1000);
        $this->waitForAjax();
    }

    /**
     * Asserts that element exists.
     *
     * @param string $cssSelector
     * @param int $timeout Timeout for the assert to succeed. By default, assertion is done immediately.
     */
    protected function assertElementExists($cssSelector, $timeout = 0) {
        if ($timeout == 0) {
            // See e.g. https://github.com/giorgiosironi/phpunit-selenium/blob/a6fdffdd56f4884ef39e09a9c62e5e4eb273e42c/Tests/Selenium2TestCaseTest.php#L1065
            $this->byCssSelector($cssSelector);
        } else {
            $this->waitForElement($cssSelector, $timeout);
        }
    }

    /**
     * Types text into TinyMCE. Can be plain text or contain HTML tags.
     *
     * @param string $text
     */
    protected function setTinyMCEContent($text) {
        $this->executeScript("tinyMCE.activeEditor.setContent('$text')");
    }

    /**
     * Explicitly wait for the element identified by $cssSelector to appear on the screen (throws
     * assertion error if this element doesn't appear). Note that default Selenium methods
     * have this behavior built-in so you typically only need to call this method at the end of the
     * test method, before the assertions are run (it prevents them from running too soon).
     *
     * (This is just a friendly name / facade to the built-in waiting mechanism.
     * Doing something like `$el = $this->byCssSelector(..)` has the same effect but would
     * look a bit odd.)
     *
     * @param string $cssSelector
     * @param int $timeout Timeout in milliseconds. Default: 3 seconds.
     */
    protected function waitForElement($cssSelector, $timeout = 3000) {

        $previousImplicitWait = $this->timeouts()->getLastImplicitWaitValue();
        $this->timeouts()->implicitWait($timeout);
        $this->assertElementExists($cssSelector);
        $this->timeouts()->implicitWait($previousImplicitWait);
    }

    /**
     * If there is a redirect after POST (Post/Redirect/Get pattern) you have wait for the shutdown
     * function that creates a commit - the commit may take longer time then rendering new page.
     */
    protected function waitAfterRedirect() {
        $this->waitUntilTrue(function (SeleniumTestCase $testCase) {
            return $testCase->executeScript("return document.readyState;") == "complete";
        }, 5000);

        usleep(self::$config->getAfterRedirectWaitingTime() * 1000);
    }

    /**
     * Selenium API improvement. Wait until callback is true or timeout occurs.
     *
     * @param $callback
     * @param $timeout
     */
    protected function waitUntilTrue($callback, $timeout = null) {
        $this->waitUntil(function (SeleniumTestCase $testCase) use ($callback) {
            $result = call_user_func($callback, $testCase);
            return $result === true ? true : null;
        }, $timeout);
    }

    /**
     * Wait for all AJAX requests caused by jQuery are done.
     */
    protected function waitForAjax() {
        $this->waitUntilTrue(function (SeleniumTestCase $testCase) {
            return $testCase->executeScript("return jQuery.active;") === 0;
        }, 5000);
    }
} 

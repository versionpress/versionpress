<?php
namespace VersionPress\Tests\Selenium;

use PHPUnit_Extensions_Selenium2TestCase;
use PHPUnit_Extensions_Selenium2TestCase_WebDriverException;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Tests\Utils\TestRunnerOptions;

/**
 * Base class for VersionPress Selenium tests
 */
abstract class SeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase {

    /**
     * @var TestConfig
     */
    protected static $testConfig;

    /**
     * @var WpAutomation
     */
    protected static $wpAutomation;


    /**
     * If true, {@link loginIfNecessary} is called on {@link setUpSite}.
     *
     * @var bool
     */
    protected static $autologin = true;

    /**
     * @var \VersionPress\Git\GitRepository
     */
    protected $gitRepository;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);

        self::staticInitialization();

        $this->setBrowser("firefox");

        $capabilities = $this->getDesiredCapabilities();
        if (self::$testConfig->seleniumConfig->firefoxBinary) {
            $capabilities["firefox_binary"] = self::$testConfig->seleniumConfig->firefoxBinary;
        }
        $this->setDesiredCapabilities($capabilities);

        $this->setBrowserUrl(self::$testConfig->testSite->url);

        $this->gitRepository = new \VersionPress\Git\GitRepository(self::$testConfig->testSite->path);
    }

    private static $staticInitializationDone = false;

    /**
     * Makes sure some static properties are initialized. Called from constructor and from static methods
     * that might run before it.
     */
    private static function staticInitialization() {

        if (self::$staticInitializationDone) {
            return;
        }

        if (!self::$testConfig) {
            self::$testConfig = TestConfig::createDefaultConfig();
        }

        if (!self::$wpAutomation) {
            self::$wpAutomation = new WpAutomation(self::$testConfig->testSite);
        }

        self::$staticInitializationDone = true;

    }

    public static function setUpBeforeClass() {
        self::setUpSite(TestRunnerOptions::getInstance()->forceSetup == "before-class");
    }

    /**
     * Check if site is set up and VersionPress fully activated, and if not, do so. The $force
     * parametr may force this.
     *
     * @param bool $force Force all the automation actions to be taken regardless of the site state
     */
    public static function setUpSite($force) {

        self::staticInitialization();

        if ($force || !self::$wpAutomation->isSiteSetUp()) {
            self::$wpAutomation->setUpSite();
        }

        if ($force || !self::$wpAutomation->isVersionPressInitialized()) {
            self::$wpAutomation->copyVersionPressFiles();
            self::$wpAutomation->initializeVersionPress();
        }

    }

    public function setUpPage() {
        if (self::$autologin) {
            $this->loginIfNecessary();
        }
    }

    protected function loginIfNecessary() {
        if ($this->elementExists('#wpadminbar')) {
            return;
        }

        $this->url('wp-admin');
        usleep(100 * 1000); // sometimes we need to wait for the page to fully load

        if (!$this->elementExists('#user_login')) {
            return;
        }

        $this->byId('user_login')->value(self::$testConfig->testSite->adminName);
        usleep(100 * 1000); // wait for change focus
        $this->byId('user_pass')->value(self::$testConfig->testSite->adminPassword);
        $this->byId("loginform")->submit();
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
     * The built-in $element->value('xyz') method only appends to an existing value,
     * this method overwrites the whole field.
     *
     * @param $cssSelector
     * @param $value
     */
    protected function setValue($cssSelector, $value) {

        // Implementation note: calling $element->clear() causes image edit form in WP 4.1 to dispatch
        // an unwanted AJAX request, which is why we need to clear the value using JavaScript.
        // That works on both WP 4.1 and pre-4.1.

        $element = $this->byCssSelector($cssSelector);
        $this->executeScript("jQuery('$cssSelector').val('')");
        $element->value($value);
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
     */
    protected function assertElementExists($cssSelector) {
        if (!$this->elementExists($cssSelector)) {
            $this->fail("Element \"$cssSelector\" does not exist");
        }
    }

    protected function elementExists($cssSelector) {
        try {
            // See e.g. https://github.com/giorgiosironi/phpunit-selenium/blob/a6fdffdd56f4884ef39e09a9c62e5e4eb273e42c/Tests/Selenium2TestCaseTest.php#L1065
            $this->byCssSelector($cssSelector);
            return true;
        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            return false;
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
     *
     * @param int $timeout Milliseconds
     */
    protected function waitAfterRedirect($timeout = 5000) {
        $this->waitUntilTrue(function (SeleniumTestCase $testCase) {
            return $testCase->executeScript("return document.readyState;") == "complete";
        }, $timeout);

        usleep(self::$testConfig->seleniumConfig->postCommitWaitTime * 1000);
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

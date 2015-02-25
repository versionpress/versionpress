<?php

namespace VersionPress\Tests\End2End;

use PHPUnit_Extensions_Selenium2TestCase_SessionStrategy_Isolated;
use PHPUnit_Extensions_Selenium2TestCase_SessionStrategy_Shared;
use PHPUnit_Extensions_Selenium2TestCase_URL;
use PHPUnit_Extensions_Selenium2TestCase_WaitUntil;
use PHPUnit_Extensions_Selenium2TestCase_WebDriverException;
use PHPUnit_Framework_Assert;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Selenium\SeleniumTestCase;
use VersionPress\Tests\Utils\TestConfig;

/**
 * @method void acceptAlert() Press OK on an alert, or confirms a dialog
 * @method mixed alertText() alertText($value = NULL) Gets the alert dialog text, or sets the text for a prompt dialog
 * @method void back()
 * @method \PHPUnit_Extensions_Selenium2TestCase_Element byClassName() byClassName($value)
 * @method \PHPUnit_Extensions_Selenium2TestCase_Element byCssSelector() byCssSelector($value)
 * @method \PHPUnit_Extensions_Selenium2TestCase_Element byId() byId($value)
 * @method \PHPUnit_Extensions_Selenium2TestCase_Element byLinkText() byLinkText($value)
 * @method \PHPUnit_Extensions_Selenium2TestCase_Element byName() byName($value)
 * @method \PHPUnit_Extensions_Selenium2TestCase_Element byTag() byTag($value)
 * @method \PHPUnit_Extensions_Selenium2TestCase_Element byXPath() byXPath($value)
 * @method void click() click(int $button = 0) Click any mouse button (at the coordinates set by the last moveto command).
 * @method void clickOnElement() clickOnElement($id)
 * @method string currentScreenshot() BLOB of the image file
 * @method void dismissAlert() Press Cancel on an alert, or does not confirm a dialog
 * @method void doubleclick() Double clicks (at the coordinates set by the last moveto command).
 * @method \PHPUnit_Extensions_Selenium2TestCase_Element element() element(\PHPUnit_Extensions_Selenium2TestCase_ElementCriteria $criteria) Retrieves an element
 * @method array elements() elements(\PHPUnit_Extensions_Selenium2TestCase_ElementCriteria $criteria) Retrieves an array of Element instances
 * @method string execute() execute($javaScriptCode) Injects arbitrary JavaScript in the page and returns the last
 * @method string executeAsync() executeAsync($javaScriptCode) Injects arbitrary JavaScript and wait for the callback (last element of arguments) to be called
 * @method void forward()
 * @method void frame() frame(mixed $element) Changes the focus to a frame in the page (by frameCount of type int, htmlId of type string, htmlName of type string or element of type \PHPUnit_Extensions_Selenium2TestCase_Element)
 * @method void moveto() moveto(\PHPUnit_Extensions_Selenium2TestCase_Element $element) Move the mouse by an offset of the specificed element.
 * @method void refresh()
 * @method \PHPUnit_Extensions_Selenium2TestCase_Element_Select select() select($element)
 * @method string source() Returns the HTML source of the page
 * @method \PHPUnit_Extensions_Selenium2TestCase_Session_Timeouts timeouts()
 * @method string title()
 * @method void|string url() url($url = NULL)
 * @method \PHPUnit_Extensions_Selenium2TestCase_ElementCriteria using() using($strategy) Factory Method for Criteria objects
 * @method void window() window($name) Changes the focus to another window
 * @method string windowHandle() Retrieves the current window handle
 * @method string windowHandles() Retrieves a list of all available window handles
 * @method string keys() Send a sequence of key strokes to the active element.
 * @method string file($file_path) Upload a local file. Returns the fully qualified path to the transferred file.
 * @method array log(string $type) Get the log for a given log type. Log buffer is reset after each request.
 * @method array logTypes() Get available log types.
 * @method void closeWindow() Close the current window.
 * @method void close() Close the current window and clear session data.
 * @method \PHPUnit_Extensions_Selenium2TestCase_Element active() Get the element on the page that currently has focus.
 */
class SeleniumPerformer implements ITestPerformer {
    /** @var \PHPUnit_Extensions_Selenium2TestCase_Session */
    protected $session;
    /** @var \PHPUnit_Extensions_Selenium2TestCase_Session */
    private static $sharedSession;
    /** @var WpAutomation */
    protected static $wpAutomation;
    /** @var TestConfig */
    private static $testConfig;

    protected static $autologin = true;

    public function __construct(TestConfig $testConfig) {
        self::$testConfig = $testConfig;

        if (!self::$sharedSession) {
            self::startSession();
        }

        $this->session = self::$sharedSession;
    }

    private static function startSession() {
        $parameters = array(
            'host' => 'localhost',
            'port' => 4444,
            'browser' => NULL,
            'desiredCapabilities' => array(),
            'seleniumServerRequestsTimeout' => 60,
            'browserName' => 'firefox',
            'browserUrl' => new PHPUnit_Extensions_Selenium2TestCase_URL(self::$testConfig->testSite->url)
        );

        if (isset(self::$testConfig->seleniumConfig->firefoxBinary)) {
            $parameters['desiredCapabilities'] = array('firefox_binary' => self::$testConfig->seleniumConfig->firefoxBinary);
        }

        $strategy = new PHPUnit_Extensions_Selenium2TestCase_SessionStrategy_Shared(new PHPUnit_Extensions_Selenium2TestCase_SessionStrategy_Isolated());
        self::$sharedSession = $strategy->session($parameters);
        self::$wpAutomation = new WpAutomation(self::$testConfig->testSite);
    }

    function __call($name, $arguments) {
        return call_user_func_array(array($this->session, $name), $arguments);
    }

    /**
     * Check if site is set up and VersionPress fully activated, and if not, do so. The $force
     * parametr may force this.
     *
     * @param bool $force Force all the automation actions to be taken regardless of the site state
     */
    public static function setUpSite($force) {

        if ($force || !self::$wpAutomation->isSiteSetUp()) {
            self::$wpAutomation->setUpSite();
        }

        if ($force || !self::$wpAutomation->isVersionPressInitialized()) {
            self::$wpAutomation->copyVersionPressFiles();
            self::$wpAutomation->initializeVersionPress(self::$testConfig->testSite->vpConfig['git-binary']);
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
    public function executeScript($code) {
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
            PHPUnit_Framework_Assert::fail("Element \"$cssSelector\" does not exist");
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
        $this->waitUntilTrue(function (SeleniumPerformerBasedFakeTestCase $testCase) {
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
        $this->waitUntil(function (SeleniumPerformerBasedFakeTestCase $testCase) use ($callback) {
            $result = call_user_func($callback, $testCase);
            return $result === true ? true : null;
        }, $timeout);
    }

    /**
     * Wait for all AJAX requests caused by jQuery are done.
     */
    protected function waitForAjax() {
        $this->waitUntilTrue(function (SeleniumPerformerBasedFakeTestCase $testCase) {
            return $testCase->executeScript("return jQuery.active;") === 0;
        }, 5000);
    }

    /**
     * Wait until callback isn't null or timeout occurs
     *
     * @param $callback
     * @param null $timeout
     * @return mixed
     */
    protected function waitUntil($callback, $timeout = NULL)
    {
        $waitUntil = new PHPUnit_Extensions_Selenium2TestCase_WaitUntil(new SeleniumPerformerBasedFakeTestCase($this));
        return $waitUntil->run($callback, $timeout);
    }
}

/**
 * @method executeScript
 */
class SeleniumPerformerBasedFakeTestCase extends \PHPUnit_Extensions_Selenium2TestCase {

    /** @var SeleniumPerformer */
    private $performer;

    function __construct(SeleniumPerformer $performer) {
        $this->performer = $performer;
    }

    public function __call($command, $arguments) {
        return call_user_func_array(array($this->performer, $command) , $arguments);
    }
}
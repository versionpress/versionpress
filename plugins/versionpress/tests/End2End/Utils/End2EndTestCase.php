<?php

namespace VersionPress\Tests\End2End\Utils;

use PHPUnit_Framework_TestCase;
use VersionPress\Git\GitRepository;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\CommitAsserter;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Tests\Utils\TestRunnerOptions;
use VersionPress\Utils\PathUtils;

class End2EndTestCase extends PHPUnit_Framework_TestCase
{

    /** @var TestConfig */
    protected static $testConfig;
    /** @var GitRepository */
    protected $gitRepository;
    /** @var CommitAsserter */
    protected $commitAsserter;
    /** @var WpAutomation */
    protected static $wpAutomation;

    private static $skipAllBecauseOfMissingWorker = false;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->staticInitialization();
        $this->gitRepository = new GitRepository(self::$testConfig->testSite->path);
        self::$wpAutomation = new WpAutomation(self::$testConfig->testSite, self::$testConfig->wpCliVersion);

        $vpdbDir = self::$wpAutomation->getVpdbDir();
        $relativePathToVpdb = PathUtils::getRelativePath(self::$testConfig->testSite->path, $vpdbDir);

        $uploadsDir = self::$wpAutomation->getUploadsDir();
        $relativePathToUploads = PathUtils::getRelativePath(self::$testConfig->testSite->path, $uploadsDir);

        $this->commitAsserter = new CommitAsserter(
            $this->gitRepository,
            ['vpdb' => $relativePathToVpdb, 'uploads' => $relativePathToUploads]
        );
    }

    protected function setUp()
    {
        parent::setUp();
        if (self::$skipAllBecauseOfMissingWorker) {
            $this->markTestSkipped('Missing worker');
        }
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::setUpSite(TestRunnerOptions::getInstance()->forceSetup == "before-class");
    }

    private function staticInitialization()
    {
        self::$testConfig = TestConfig::createDefaultConfig();

        $class = get_called_class();
        $workerType = implode('', array_map('ucfirst', explode('-', self::$testConfig->end2endTestType)));

        $workerClass = $class . $workerType . 'Worker';

        if (!class_exists($workerClass)) {
            self::$skipAllBecauseOfMissingWorker = true;
            return;
        }

        $worker = new $workerClass(self::$testConfig);

        $propertyReflection = new \ReflectionProperty($class, 'worker');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue(null, $worker);
    }

    /**
     * Check if site is set up and VersionPress fully activated, and if not, do so. The $force
     * parametr may force this.
     *
     * @param bool $force Force all the automation actions to be taken regardless of the site state
     */
    private static function setUpSite($force)
    {

        if ($force || !self::$wpAutomation->isSiteSetUp()) {
            self::$wpAutomation->setUpSite();
        }

        if ($force || !self::$wpAutomation->isVersionPressInitialized()) {
            self::$wpAutomation->copyVersionPressFiles();
            self::$wpAutomation->initializeVersionPress();
        }

    }
}

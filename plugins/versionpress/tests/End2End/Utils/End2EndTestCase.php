<?php

namespace VersionPress\Tests\End2End\Utils;

use PHPUnit_Framework_TestCase;
use VersionPress\Git\GitRepository;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\TestConfig;

class End2EndTestCase extends PHPUnit_Framework_TestCase {

    /** @var TestConfig */
    protected static $testConfig;
    /** @var GitRepository */
    protected $gitRepository;
    /** @var WpAutomation */
    protected static $wpAutomation;

    private static $skipAllBecauseOfMissingWorker = false;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->staticInitialization();
        $this->gitRepository = new GitRepository(self::$testConfig->testSite->path);
        self::$wpAutomation = new WpAutomation(self::$testConfig->testSite);
    }

    protected function setUp() {
        parent::setUp();
        if (self::$skipAllBecauseOfMissingWorker) {
            $this->markTestSkipped('Missing worker');
        }
    }

    private function staticInitialization() {
        self::$testConfig = new TestConfig(__DIR__ . '/../../test-config.neon');

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
}
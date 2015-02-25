<?php

namespace VersionPress\Tests\End2End;

use PHPUnit_Framework_TestCase;
use VersionPress\Git\GitRepository;
use VersionPress\Tests\Utils\TestConfig;

class End2EndTestCase extends PHPUnit_Framework_TestCase {

    /** @var TestConfig */
    protected static $testConfig;
    protected $gitRepository;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->staticInitialization();
        $this->gitRepository = new GitRepository(self::$testConfig->testSite->path);
    }

    private function staticInitialization() {
        self::$testConfig = new TestConfig(__DIR__ . '/../test-config.neon');

        $class = get_called_class();
        $performerType = implode('', array_map('ucfirst', explode('-', self::$testConfig->end2endTestType)));

        $performerClass = $class . $performerType . 'Performer';
        $performer = new $performerClass(self::$testConfig);

        $propertyReflection = new \ReflectionProperty($class, 'performer');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue(null, $performer);

    }
}
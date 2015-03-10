<?php

namespace VersionPress\Tests\End2End\Utils;

use PHPUnit_Framework_TestCase;
use Tracy\Debugger;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Filters\AbsoluteUrlFilter;
use VersionPress\Git\GitRepository;
use VersionPress\Storages\StorageFactory;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\TestConfig;
use VersionPress\Tests\Utils\TestRunnerOptions;

class End2EndTestCase extends PHPUnit_Framework_TestCase {

    /** @var TestConfig */
    protected static $testConfig;
    /** @var GitRepository */
    protected $gitRepository;
    /** @var WpAutomation */
    protected static $wpAutomation;
    /** @var \mysqli */
    private $database;
    /** @var DbSchemaInfo */
    private $schemaInfo;
    /** @var StorageFactory */
    private $storageFactory;

    private static $skipAllBecauseOfMissingWorker = false;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->staticInitialization();
        $this->gitRepository = new GitRepository(self::$testConfig->testSite->path);
        self::$wpAutomation = new WpAutomation(self::$testConfig->testSite);

        $dbHost = self::$testConfig->testSite->dbHost;
        $dbUser = self::$testConfig->testSite->dbUser;
        $dbPassword = self::$testConfig->testSite->dbPassword;
        $dbName = self::$testConfig->testSite->dbName;
        $this->database = new \mysqli($dbHost, $dbUser, $dbPassword, $dbName);

        $vpdbPath = self::$testConfig->testSite->path . '/wp-content/vpdb';
        $schemaReflection = new \ReflectionClass('VersionPress\Database\DbSchemaInfo');
        $schemaFile = dirname($schemaReflection->getFileName()) . '/wordpress-schema.neon';
        $this->schemaInfo = new DbSchemaInfo($schemaFile, self::$testConfig->testSite->dbTablePrefix);

        $this->storageFactory = new StorageFactory($vpdbPath, $this->schemaInfo);
    }

    protected function setUp() {
        parent::setUp();
        if (self::$skipAllBecauseOfMissingWorker) {
            $this->markTestSkipped('Missing worker');
        }
    }

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::setUpSite(TestRunnerOptions::getInstance()->forceSetup == "before-class");
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

    /**
     * Check if site is set up and VersionPress fully activated, and if not, do so. The $force
     * parametr may force this.
     *
     * @param bool $force Force all the automation actions to be taken regardless of the site state
     */
    private static function setUpSite($force) {

        if ($force || !self::$wpAutomation->isSiteSetUp()) {
            self::$wpAutomation->setUpSite();
        }

        if ($force || !self::$wpAutomation->isVersionPressInitialized()) {
            self::$wpAutomation->copyVersionPressFiles();
            self::$wpAutomation->initializeVersionPress(self::$testConfig->testSite->vpConfig['git-binary']);
        }

    }

    protected function assertFilesEqualDatabase() {
        $entityNames = $this->schemaInfo->getAllEntityNames();
        foreach ($entityNames as $entityName) {
            $this->assertEntitiesEqualDatabase($entityName);
        }
    }

    private function assertEntitiesEqualDatabase($entityName) {
        $storage = $this->storageFactory->getStorage($entityName);
        $entityInfo = $this->schemaInfo->getEntityInfo($entityName);

        $allDbEntities = $this->selectAll($this->schemaInfo->getPrefixedTableName($entityName));
        $dbEntities = array_filter($allDbEntities, array($storage, 'shouldBeSaved'));

        $idMap = $this->getVpIdMap();
        $dbEntities = $this->replaceForeignKeys($entityName, $dbEntities, $idMap);

        $urlFilter = new AbsoluteUrlFilter(self::$testConfig->testSite->url);
        $storageEntities = array_map(function ($entity) use ($urlFilter) { return $urlFilter->restore($entity); }, $storage->loadAll());
        $countOfentitiesInDb = count($dbEntities);
        $countOfentitiesInStorage = count($storageEntities);

        if ($countOfentitiesInDb !== $countOfentitiesInStorage) {
            if ($countOfentitiesInStorage > $countOfentitiesInDb) {
                $problematicEntities = $this->findMissingEntities($entityName, $storageEntities, $dbEntities);
            } else {
                $problematicEntities = $this->findExceedingEntities($entityName, $storageEntities, $dbEntities);
            }

            $this->fail("Different count of synchronized entities ($entityName): DB = $countOfentitiesInDb, storage = $countOfentitiesInStorage\nProblematic entities: " . join(", ", $problematicEntities));
        }

        foreach ($dbEntities as $dbEntity) {
            $id = $dbEntity[$entityInfo->vpidColumnName];
            $storageEntity = $storageEntities[$id];

            foreach ($dbEntity as $column => $value) {
                if (!isset($storageEntity[$column])) {
                    continue;
                }

                $this->assertEquals($storageEntity[$column], $value, "Different values ({$entityName}[$column]: $id): DB = $value, storage = $storageEntity[$column]");
            }
        }
    }

    private function selectAll($table) {
        $res = $this->database->query("SELECT * FROM $table");
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    private function getVpIdMap() {
        $vpIdTable = $this->selectAll($this->schemaInfo->getPrefixedTableName('vp_id'));
        $idMap = array();
        foreach ($vpIdTable as $row) {
            $idMap[$row['table']][$row['id']] = strtoupper(bin2hex($row['vp_id']));
        }
        return $idMap;
    }

    private function replaceForeignKeys($entityName, $dbEntities, $idMap) {
        $entities = array();
        foreach ($dbEntities as $entity) {
            foreach ($this->schemaInfo->getEntityInfo($entityName)->references as $column => $targetEntity) {
                if ($entity[$column] != "0") {
                    $entity["vp_$column"] = $idMap[$this->schemaInfo->getTableName($targetEntity)][$entity[$column]];
                }
                unset($entity[$column]);
            }

            if (!$this->schemaInfo->getEntityInfo($entityName)->hasNaturalVpid) {
                $idColumnName = $this->schemaInfo->getEntityInfo($entityName)->idColumnName;
                $entity['vp_id'] = $idMap[$this->schemaInfo->getTableName($entityName)][$entity[$idColumnName]];
                if (!empty($entity['vp_id'])) {
                    unset($entity[$idColumnName]);
                }
            } else {
                unset($entity['option_id']);
            }

            $entities[] = $entity;
        }
        return $entities;
    }

    private function findMissingEntities($entityName, $storageEntities, $dbEntities) {
        $storageVpIds = array_keys($storageEntities);
        $idColumnName = $this->schemaInfo->getEntityInfo($entityName)->vpidColumnName;
        foreach ($dbEntities as $dbEntity) {
            unset($storageVpIds[$dbEntity[$idColumnName]]);
        }
        return $storageVpIds;
    }

    private function findExceedingEntities($entityName, $storageEntities, $dbEntities) {
        $exceedingEntities = array();
        $vpidColumnName = $this->schemaInfo->getEntityInfo($entityName)->vpidColumnName;
        $idColumnName = $this->schemaInfo->getEntityInfo($entityName)->idColumnName;

        foreach ($dbEntities as $dbEntity) {
            if (empty($dbEntity[$vpidColumnName])) {
                $exceedingEntities[] = $dbEntity[$idColumnName];
            } elseif (!isset($storageEntities[$dbEntity[$vpidColumnName]])) {
                $exceedingEntities[] = $dbEntity[$vpidColumnName];
            }
        }

        return $exceedingEntities;
    }
}
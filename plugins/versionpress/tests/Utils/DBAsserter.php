<?php

namespace VersionPress\Tests\Utils;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Database\TableSchemaStorage;
use VersionPress\Database\VpidRepository;
use VersionPress\DI\DIContainer;
use VersionPress\Storages\StorageFactory;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\ReferenceUtils;

class DBAsserter
{
    /** @var DbSchemaInfo */
    private static $schemaInfo;
    /** @var StorageFactory */
    private static $storageFactory;
    /** @var TestConfig */
    private static $testConfig;
    /** @var \mysqli */
    private static $database;
    /** @var Database */
    private static $vp_database;
    /** @var \wpdb */
    private static $wpdb;
    /** @var ShortcodesReplacer */
    private static $shortcodesReplacer;
    /** @var VpidRepository */
    private static $vpidRepository;
    /** @var WpAutomation */
    private static $wpAutomation;


    public static function assertFilesEqualDatabase()
    {
        HookMock::setUp(HookMock::TRUE_HOOKS);
        self::staticInitialization();
        $entityNames = self::$schemaInfo->getAllEntityNames();
        foreach ($entityNames as $entityName) {
            self::assertEntitiesEqualDatabase($entityName);
        }
        self::clearGlobalVariables();
        HookMock::tearDown();
    }

    private static function staticInitialization()
    {
        self::$testConfig = TestConfig::createDefaultConfig();
        self::$wpAutomation = new WpAutomation(self::$testConfig->testSite, self::$testConfig->wpCliVersion);

        $yamlDir = self::$wpAutomation->getPluginsDir() . '/versionpress/.versionpress';
        $schemaFile = $yamlDir . '/schema.yml';
        $shortcodeFile = $yamlDir . '/shortcodes.yml';

        /** @var $wp_db_version */
        require(self::$wpAutomation->getAbspath() . '/wp-includes/version.php');

        if (!function_exists('get_shortcode_regex')) {
            require_once(self::$wpAutomation->getAbspath() . '/wp-includes/shortcodes.php');
        }

        self::$schemaInfo = new DbSchemaInfo([$schemaFile], self::$testConfig->testSite->dbTablePrefix, $wp_db_version);

        $rawTaxonomies = self::$wpAutomation->runWpCliCommand(
            'taxonomy',
            'list',
            ['format' => 'json', 'fields' => 'name']
        );
        $taxonomies = array_column(json_decode($rawTaxonomies, true), 'name');

        $dbHost = self::$testConfig->testSite->dbHost;
        $dbUser = self::$testConfig->testSite->dbUser;
        $dbPassword = self::$testConfig->testSite->dbPassword;
        $dbName = self::$testConfig->testSite->dbName;
        $dbPrefix = self::$testConfig->testSite->dbTablePrefix;
        self::$database = new \mysqli($dbHost, $dbUser, $dbPassword, $dbName);
        self::$wpdb = new \wpdb($dbUser, $dbPassword, $dbName, $dbHost);
        self::$wpdb->set_prefix($dbPrefix);
        self::$vp_database = new Database(self::$wpdb);
        $shortcodesInfo = new ShortcodesInfo([$shortcodeFile]);
        self::$vpidRepository = new VpidRepository(self::$vp_database, self::$schemaInfo);
        self::$shortcodesReplacer = new ShortcodesReplacer($shortcodesInfo, self::$vpidRepository);

        $vpdbPath = self::$wpAutomation->getVpdbDir();
        $tableSchemaRepository = new TableSchemaStorage(self::$vp_database, $vpdbPath . '/.schema');
        self::$storageFactory = new StorageFactory($vpdbPath, self::$schemaInfo, self::$vp_database, $taxonomies, null, $tableSchemaRepository);

        require(self::$wpAutomation->getPluginsDir() . '/versionpress/.versionpress/hooks.php');

        self::defineGlobalVariables();
    }

    /**
     * @param $entityName
     */
    private static function assertEntitiesEqualDatabase($entityName)
    {
        $storage = self::$storageFactory->getStorage($entityName);
        $entityInfo = self::$schemaInfo->getEntityInfo($entityName);

        $allDbEntities = self::selectAll(self::$schemaInfo->getPrefixedTableName($entityName));
        $idMap = self::getVpIdMap();
        $allDbEntities = self::identifyEntities($entityName, $allDbEntities, $idMap);
        $allDbEntities = self::replaceForeignKeys($entityName, $allDbEntities, $idMap);
        $dbEntities = array_filter($allDbEntities, [$storage, 'shouldBeSaved']);


        $urlReplacer = new AbsoluteUrlReplacer(self::$testConfig->testSite->url);
        $storageEntities = array_map(function ($entity) use ($urlReplacer) {
            return $urlReplacer->restore($entity);
        }, $storage->loadAll());
        $countOfentitiesInDb = count($dbEntities);
        $countOfentitiesInStorage = count($storageEntities);

        if ($countOfentitiesInDb !== $countOfentitiesInStorage) {
            if ($countOfentitiesInStorage > $countOfentitiesInDb) {
                $problematicEntities = self::findMissingEntities($entityName, $storageEntities, $dbEntities);
            } else {
                $problematicEntities = self::findExceedingEntities($entityName, $storageEntities, $dbEntities);
            }

            throw new \PHPUnit_Framework_AssertionFailedError(
                "Different count of synchronized entities ($entityName): DB = $countOfentitiesInDb, " .
                "storage = $countOfentitiesInStorage\nProblematic entities: " . join(", ", $problematicEntities)
            );
        }

        foreach ($dbEntities as $dbEntity) {
            $id = $dbEntity[$entityInfo->vpidColumnName];
            $storageEntity = $storageEntities[$id];

            $dbEntity = self::$shortcodesReplacer->replaceShortcodesInEntity($entityName, $dbEntity);

            foreach ($dbEntity as $column => $value) {
                if ($entityInfo->idColumnName === $column || isset($entityInfo->getIgnoredColumns()[$column])) {
                    continue;
                }
                if (!isset($storageEntity[$column])) {
                    throw new \PHPUnit_Framework_AssertionFailedError(
                        "{$entityName}[$column] with value = $value, ID = $id not found in storage"
                    );
                }

                if (is_string($storageEntity[$column])) {
                    $storageEntity[$column] = str_replace("\r\n", "\n", $storageEntity[$column]);
                }

                if (is_string($value)) {
                    $value = str_replace("\r\n", "\n", $value);
                }

                if ($storageEntity[$column] != $value) {
                    throw new \PHPUnit_Framework_AssertionFailedError(
                        "Different values ({$entityName}[$column]: $id): DB = $value, storage = $storageEntity[$column]"
                    );
                }
            }
        }

        $missingReferences = [];
        $exceedingReferences = [];

        foreach ($entityInfo->mnReferences as $reference => $targetEntity) {
            if ($entityInfo->isVirtualReference($reference)) {
                continue;
            }

            $referenceDetails = ReferenceUtils::getMnReferenceDetails(self::$schemaInfo, $entityName, $reference);
            $sourceColumn = $referenceDetails['source-column'];
            $targetColumn = $referenceDetails['target-column'];
            $junctionTable = $referenceDetails['junction-table'];
            $prefixedJunctionTable = self::$schemaInfo->getPrefixedTableName($junctionTable);
            $prefixedVpIdTable = self::$schemaInfo->getPrefixedTableName('vp_id');
            $sourceTable = self::$schemaInfo->getTableName($referenceDetails['source-entity']);
            $targetTable = self::$schemaInfo->getTableName($referenceDetails['target-entity']);

            $junctionTableContent = self::fetchAll(
                "SELECT HEX(s_vp_id.vp_id), HEX(t_vp_id.vp_id) FROM $prefixedJunctionTable j
                 JOIN $prefixedVpIdTable s_vp_id ON j.$sourceColumn = s_vp_id.id AND s_vp_id.`table`='$sourceTable'
                 JOIN $prefixedVpIdTable t_vp_id ON j.$targetColumn = t_vp_id.id AND t_vp_id.`table` = '$targetTable'",
                MYSQLI_NUM
            );

            $checkedReferences = [];
            $missingReferences[$junctionTable] = [];
            foreach ($storageEntities as $storageEntity) {
                if (!isset($storageEntity["vp_$targetEntity"])) {
                    continue;
                }

                foreach ($storageEntity["vp_$targetEntity"] as $referenceVpId) {
                    if (!ArrayUtils::any(
                        $junctionTableContent,
                        function ($junctionRow) use ($storageEntity, $referenceVpId) {
                            return $junctionRow[0] === $storageEntity['vp_id'] && $junctionRow[1] === $referenceVpId;
                        }
                    )) {
                        $missingReferences[$junctionTable][] = [
                            $sourceColumn => $storageEntity['vp_id'],
                            $targetColumn => $referenceVpId
                        ];
                    }
                    $checkedReferences[] = [$storageEntity['vp_id'], $referenceVpId];
                }
            }

            $exceedingReferences[$junctionTable] = array_map(
                function ($pair) use ($sourceColumn, $targetColumn) {
                    return [$sourceColumn => $pair[0], $targetColumn => $pair[1]];
                },
                array_filter(
                    $junctionTableContent,
                    function ($pair) use ($checkedReferences) {
                        foreach ($checkedReferences as $reference) {
                            if ($reference[0] === $pair[0] && $reference[1] === $pair[1]) {
                                return false;
                            }
                        }
                        return true;
                    }
                )
            );
        }

        self::reportResultOfMnReferenceCheck($missingReferences, "Missing");
        self::reportResultOfMnReferenceCheck($exceedingReferences, "Exceeding");
    }

    private static function selectAll($table)
    {
        return self::fetchAll("SELECT * FROM $table");
    }

    private static function fetchAll($query, $resultType = MYSQLI_ASSOC)
    {
        $res = self::$database->query($query);
        if (!$res) {
            return [];
        }

        return $res->fetch_all($resultType);
    }

    private static function getVpIdMap()
    {
        $vpIdTable = self::selectAll(self::$vp_database->vp_id);
        $idMap = [];
        foreach ($vpIdTable as $row) {
            $idMap[$row['table']][$row['id']] = strtoupper(bin2hex($row['vp_id']));
        }
        return $idMap;
    }

    private static function identifyEntities($entityName, $entities, $idMap)
    {
        $entityInfo = self::$schemaInfo->getEntityInfo($entityName);
        if (!$entityInfo->usesGeneratedVpids) {
            return $entities;
        }

        $table = $entityInfo->tableName;
        $idColumnName = $entityInfo->idColumnName;

        foreach ($entities as &$entity) {
            $id = $entity[$idColumnName];
            if (isset($idMap[$table], $idMap[$table][$id])) {
                $entity[$entityInfo->vpidColumnName] = $idMap[$table][$id];
            }
        }

        return $entities;
    }

    private static function replaceForeignKeys($entityName, $dbEntities, $idMap)
    {
        $entities = [];
        foreach ($dbEntities as $entity) {
            $entities[] = self::$vpidRepository->replaceForeignKeysWithReferences($entityName, $entity);
        }
        return $entities;
    }

    private static function findMissingEntities($entityName, $storageEntities, $dbEntities)
    {
        $storageVpIds = array_keys($storageEntities);
        $idColumnName = self::$schemaInfo->getEntityInfo($entityName)->vpidColumnName;
        foreach ($dbEntities as $dbEntity) {
            unset($storageVpIds[$dbEntity[$idColumnName]]);
        }
        return $storageVpIds;
    }

    private static function findExceedingEntities($entityName, $storageEntities, $dbEntities)
    {
        $exceedingEntities = [];
        $vpidColumnName = self::$schemaInfo->getEntityInfo($entityName)->vpidColumnName;
        $idColumnName = self::$schemaInfo->getEntityInfo($entityName)->idColumnName;

        foreach ($dbEntities as $dbEntity) {
            if (empty($dbEntity[$vpidColumnName])) {
                $exceedingEntities[] = $dbEntity[$idColumnName];
            } elseif (!isset($storageEntities[$dbEntity[$vpidColumnName]])) {
                $exceedingEntities[] = $dbEntity[$vpidColumnName];
            }
        }

        return $exceedingEntities;
    }

    private static function reportResultOfMnReferenceCheck($referenceResult, $verb)
    {
        foreach ($referenceResult as $junctionTable => $references) {
            if (count($references) == 0) {
                continue;
            }

            $list = "";
            foreach ($references as $reference) {
                $list .= "[";
                foreach ($reference as $column => $vpId) {
                    $list .= "$column = $vpId ";
                }
                $list .= "] ";
            }

            throw new \PHPUnit_Framework_AssertionFailedError(
                sprintf($verb . " M:N reference in table %s %s", $junctionTable, $list)
            );
        }
    }

    private static function isInIdMap($idMap, $targetEntity, $id)
    {
        return isset($idMap[self::$schemaInfo->getTableName($targetEntity)])
        && isset($idMap[self::$schemaInfo->getTableName($targetEntity)][$id]);
    }

    /**
     * Defines global constants, container and wpdb dynamic mapping of value references.
     * It's not pretty, but makes the mapping functions very flexible (they can have various dependecies).
     */
    private static function defineGlobalVariables()
    {
        global $versionPressContainer, $wpdb, $wp_taxonomies;

        defined('VERSIONPRESS_PLUGIN_DIR') || define('VERSIONPRESS_PLUGIN_DIR', self::$testConfig->testSite->path .
            '/wp-content/plugins/versionpress');
        defined('VP_VPDB_DIR') || define('VP_VPDB_DIR', self::$testConfig->testSite->path . '/wp-content/vpdb');
        $versionPressContainer = DIContainer::getConfiguredInstance();
        $wpdb = self::$wpdb;

        $rawTaxonomies = self::$wpAutomation->runWpCliCommand(
            'taxonomy',
            'list',
            ['format' => 'json', 'fields' => 'name']
        );
        $taxonomies = array_column(json_decode($rawTaxonomies, true), 'name');
        $wp_taxonomies = array_combine($taxonomies, $taxonomies);
    }

    private static function clearGlobalVariables()
    {
        global $versionPressContainer, $wpdb, $wp_taxonomies;
        $versionPressContainer = null;
        $wpdb = null;
        $wp_taxonomies = null;
    }
}

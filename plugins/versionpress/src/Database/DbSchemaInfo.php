<?php
namespace VersionPress\Database;

use DateTime;
use Symfony\Component\Yaml\Yaml;

/**
 * Describes parts of the DB schema, specifically telling how to identify entities
 * and what are the relationships between them. The information is loaded from a *.yml file
 * which is described in `schema-readme.md`.
 */
class DbSchemaInfo {

    /**
     * Parsed YAML schema - to see what it looks like, paste the YAML into {@link http://yaml-online-parser.appspot.com/}).
     * Parsed in constructor.
     *
     * @var array|int|mixed|DateTime|null|string
     */
    private $schema;

    /**
     * Database tables prefix, e.g. "wp_"
     *
     * @var string
     */
    private $prefix;

    /**
     * @var array entityName => EntityInfo object. Lazily constructed, see getEntityInfo().
     */
    private $entityInfoRegistry;

    /** @var int */
    private $dbVersion;

    /**
     * @param string $schemaFile Path to a *.yml file to read from disk
     * @param string $prefix
     */
    function __construct($schemaFile, $prefix, $dbVersion) {
        $yamlSchema = file_get_contents($schemaFile);
        $this->dbVersion = $dbVersion;
        $this->prefix = $prefix;
        $this->schema = $this->useSchemaForCurrentVersion(Yaml::parse($yamlSchema));
    }

    /**
     * Returns EntityInfo for a given entity name (e.g., "post" or "comment")
     *
     * @param $entityName
     * @return EntityInfo
     */
    public function getEntityInfo($entityName) {
        if (!isset($this->entityInfoRegistry[$entityName])) {
            $this->entityInfoRegistry[$entityName] = new EntityInfo(array($entityName => $this->schema[$entityName]));
        }

        return $this->entityInfoRegistry[$entityName];
    }


    /**
     * Gets all entities defined by the schema
     *
     * @return array
     */
    public function getAllEntityNames() {
        return array_keys($this->schema);
    }

    /**
     * For something like "post", returns "posts"
     *
     * @param $entityName
     * @return string
     */
    public function getTableName($entityName) {
        $tableName = $this->isEntity($entityName) ? $this->getEntityInfo($entityName)->tableName : $entityName;
        return $tableName;
    }

    /**
     * For something like "post", returns "wp_posts"
     *
     * @param $entityName
     * @return string
     */
    public function getPrefixedTableName($entityName) {
        return $this->prefix . $this->getTableName($entityName);
    }

    /**
     * Returns EntityInfo for a given table name (e.g., "posts" or "commentmeta")
     *
     * @param $tableName
     * @return EntityInfo
     */
    public function getEntityInfoByTableName($tableName) {
        $entityNames = $this->getAllEntityNames();
        foreach ($entityNames as $entityName) {
            $entityInfo = $this->getEntityInfo($entityName);
            if ($entityInfo->tableName === $tableName)
                return $entityInfo;
        }
        return null;
    }

    /**
     * Returns EntityInfo for a given table name with prefix (e.g., "wp_posts" or "wp_commentmeta")
     *
     * @param $tableName
     * @return EntityInfo
     */
    public function getEntityInfoByPrefixedTableName($tableName) {
        $tableName = substr($tableName, strlen($this->prefix));
        return $this->getEntityInfoByTableName($tableName);
    }

    /**
     * Returns true if entity has a parent reference.
     *
     * @param $entityName
     * @return bool
     */
    public function isChildEntity($entityName) {
        return $this->getEntityInfo($entityName)->parentReference !== null;
    }

    /**
     * Returns all rules for frequently written entities grouped by entity name.
     *
     * @return array
     */
    public function getRulesForFrequentlyWrittenEntities() {
        $frequentlyWrittenEntities = array();
        foreach ($this->getAllEntityNames() as $entityName) {
            $entityInfo = $this->getEntityInfo($entityName);
            $frequentlyWrittenEntities[$entityName] = $entityInfo->getRulesAndIntervalsForFrequentlyWrittenEntities();
        }

        return $frequentlyWrittenEntities;
    }


    public function getIntervalsForFrequentlyWrittenEntities() {
        $rulesByEntity = $this->getRulesForFrequentlyWrittenEntities();
        $intervals = array();

        foreach ($rulesByEntity as $rules) {
            foreach ($rules as $rule) {
                $intervals[] = $rule['interval'];
            }
        }

        return array_unique($intervals);
    }

    /**
     * Returns true if given name is an entity (is defined in schema).
     * Useful for prefixing VP tables.
     *
     * @param $entityOrTableName
     * @return bool
     */
    private function isEntity($entityOrTableName) {
        return in_array($entityOrTableName, $this->getAllEntityNames());
    }

    /**
     * Returns valid schema for current version of WP.
     * For example in WP < 4.4-beta1 removes the `termmeta` entity.
     *
     * @param $schema
     * @return array
     */
    private function useSchemaForCurrentVersion($schema) {
        $currentDbVersion = $this->dbVersion;
        return array_filter($schema, function ($entitySchema) use ($currentDbVersion) {
            if (!isset($entitySchema['since'])) {
                return true;
            }

            return $entitySchema['since'] <= $currentDbVersion;
        });
    }
}

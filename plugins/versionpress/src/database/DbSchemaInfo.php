<?php
/**
 * Describes parts of the DB schema, specifically telling how to identify entities
 * and what are the relationships between them. The information is loaded from a *.neon file
 * which is described in `schema-readme.md`.
 */
class DbSchemaInfo {

    /**
     * Parsed NEON schema - to see what it looks like, paste the NEON into {@link http://ne-on.org/ ne-on.org}).
     * Parsed in constructor.
     *
     * @var array|int|mixed|NDateTime53|NNeonEntity|null|string
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

    /**
     * @param string $schemaFile Path to a *.neon file to read from disk
     * @param string $prefix
     */
    function __construct($schemaFile, $prefix) {
        $neonSchema = file_get_contents($schemaFile);
        $this->schema = NNeon::decode($neonSchema);
        $this->prefix = $prefix;
    }

    /**
     * Returns EntityInfo for a given entity name (e.g., "posts" or "comments")
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
     * For something like "posts", returns "wp_posts"
     *
     * @param $entityName
     * @return string
     */
    public function getPrefixedTableName($entityName) {
        return $this->prefix . $entityName;
    }
}
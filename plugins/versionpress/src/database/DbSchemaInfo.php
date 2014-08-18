<?php
/**
 * Represents DB schema info, mostly the part about primary IDs and references to other entities.
 * The information is loaded from a *.neon file whose structure is shown in `schema.sample.neon`.
 */
class DbSchemaInfo {

    /**
     * Stores schema info in the form of nested arrays (e.g. paste
     * the NEON into {@link http://ne-on.org/ ne-on.org}).
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
     * @param string $schemaFile Path to a *.neon file to read from disk
     * @param string $prefix
     */
    function __construct($schemaFile, $prefix) {
        $neonSchema = file_get_contents($schemaFile);
        $this->schema = NNeon::decode($neonSchema);
        $this->prefix = $prefix;
    }

    /**
     * Returns true if $entityName has a single unique ID column, i.e. a simple primary key.
     * For example, posts have a simple ID while term_relationships don't.
     *
     * @param string $entityName Like "posts" or "comments"
     * @return bool
     */
    public function hasId($entityName) {
        return isset($this->schema[$entityName]['id']);
    }

    /**
     * Returns true if $entityName has references (foreign keys) to other entities, i.e. if the table
     * has foreign keys.
     *
     * @param string $entityName Like "posts" or "comments"
     * @return bool
     */
    public function hasReferences($entityName) {
        return isset($this->schema[$entityName]['references']) &&
               count($this->schema[$entityName]['references']) > 0;
    }

    /**
     * Returns references (foreign keys) of the entity.
     *
     * TODO: It currently produces "undefined index" error if the entity has no references.
     * Maybe it should return an empty array instead?
     *
     * @param string $entityName Like "posts" or "comments"
     * @return array
     */
    public function getReferences($entityName) {
        return $this->schema[$entityName]['references'];
    }

    /**
     * If hasId(), return the name of the ID (simple primary key) column. Otherwise, produces "undefined index" error.
     *
     * @param string $entityName
     * @return mixed
     */
    public function getIdColumnName($entityName) {
        return $this->schema[$entityName]['id'];
    }

    /**
     * Gets a named reference of the entity. For example, posts have the
     * `post_author` reference. A call to `getReference('posts', 'post_author')`
     * returns which table it points to, for example `array('table' => 'users')`.
     *
     * TODO: maybe there's not need to return an array but straight the table name?
     *
     * @param string $entityName
     * @param string $referenceName
     * @return array
     */
    public function getReference($entityName, $referenceName) {
        return $this->schema[$entityName]['references'][$referenceName];
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
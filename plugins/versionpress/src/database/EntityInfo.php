<?php

/**
 * Info about an entity. Basically represents a section of the NEON schema file -  find
 * the parsing logic in the constructor.
 */
class EntityInfo {

    /**
     * Name of the entity, e.g. 'post' or 'comment'.
     *
     * @var string
     */
    public $entityName;

    /**
     * Name of DB table where the entity is stored. By default it's {@see entityName}.
     *
     * @var string
     */
    public $tableName;

    /**
     * Name of a column that uniquely identifies the entity within a db table. This is most
     * commonly an auto-increment primary key but not always - e.g., options use the 'option_name'
     * which is not a primary key in that table but is a local id as far as VersionPress is concerned.
     *
     * @var string
     */
    public $idColumnName;

    /**
     * Name of a column that stores a globally unique VersionPress ID (VPID). For entities
     * that use auto-increment $idColumnName this will be 'vp_id' and VersionPress will generate
     * and maintain VPIDs for such entities. The opposite example is the options entity - the 'option_name'
     * column already identifies the option globally and uniquely so it can be this VPID column name directly.
     *
     * @var string
     */
    public $vpidColumnName;

    /**
     * True if this entity uses VersionPress-generated VPIDs. It is the case for most entities
     * except options in clean WordPress. (Technically, this is equivalent to querying whether
     * $vpidColumnName is different from $idColumnName or whether $vpidColumnName is 'vp_id'.)
     *
     * The opposite is $hasNaturalVpid.
     *
     * @var bool
     */
    public $usesGeneratedVpids;

    /**
     * True if the entity has a natural VPID, i.e. VersionPress doesn't need to generate anything
     * and can use some existing DB column directly. Options and their 'option_name' are an example
     * of this.
     *
     * If the entity has natural VPID, the $idColumnName and $vpidColumnName will point to the same column.
     * The opposite is $usesGeneratedVpids.
     *
     * @var bool
     */
    public $hasNaturalVpid;

    /**
     * Keys are column names in this entity, values are names of referenced entities. E.g.:
     *
     *     array(
     *       'post_author' => 'user',
     *       'post_parent' => 'post'
     *     )
     *
     * If the entity doesn't have references, returns empty array.
     *
     * @var array
     */
    public $references;

    /**
     * True if entity has references. Basically returns count($references) > 0.
     *
     * @var
     */
    public $hasReferences;

    /**
     * Does the parsing and sets all properties
     *
     * @param array $entitySchema Example:
     *   array('post' => array(
     *     'table' => 'posts',
     *     'id' => 'ID',
     *     'references' => array (
     *        'post_author' => 'user'
     *      )
     *   ))
     */
    public function __construct($entitySchema) {
        list($key) = array_keys($entitySchema);
        $this->entityName = $key;

        $schemaInfo = $entitySchema[$key];

        if (isset($schemaInfo['table'])) {
            $this->tableName = $schemaInfo['table'];
        } else {
            $this->tableName = $this->entityName;
        }

        // The schema defines either 'id' or 'vpid', see schema-readme.md. This has this meaning:
        if (isset($schemaInfo['id'])) {
            $this->idColumnName = $schemaInfo['id'];
            $this->vpidColumnName = 'vp_id'; // convention
            $this->usesGeneratedVpids = true;
            $this->hasNaturalVpid = false;
        } else {
            $this->idColumnName = $schemaInfo['vpid'];
            $this->vpidColumnName = $schemaInfo['vpid'];
            $this->usesGeneratedVpids = false;
            $this->hasNaturalVpid = true;
        }

        if (isset($schemaInfo['references'])) {
            $this->references = $schemaInfo['references'];
            $this->hasReferences = true;
        } else {
            $this->references = array();
            $this->hasReferences = false;
        }
    }
}

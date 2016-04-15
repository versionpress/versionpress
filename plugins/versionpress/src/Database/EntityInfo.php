<?php

namespace VersionPress\Database;

use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use VersionPress\Utils\QueryLanguageUtils;

/**
 * Info about an entity. Basically represents a section of the YAML schema file -  find
 * the parsing logic in the constructor.
 */
class EntityInfo {

    const FREQUENTLY_WRITTEN_DEFAULT_INTERVAL = 'hourly';

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
    public $references = array();

    /**
     * Just like $references, only for M:N relationships (with junction table).
     * The key is composed from table name and column in the junction table.
     * This kind of relationship has to be described in both entities.
     *
     * Post:
     * array(
     *       'term_taxonomy.term_taxonomy_id' => 'term_taxonomy',
     *     )
     *
     * Term taxonomy:
     * array(
     *       'term_taxonomy.object_id' => 'post',
     *     )
     *
     * @var array
     */
    public $mnReferences = array();

    /**
     * The same as $references, only for dependent columns.
     * The key consists of name of column, where reference source is stored with its value and column, where is name of referenced entities.
     *
     *     array(
     *       'meta_key=_thumbnail_id@meta_value' => 'post',
     *       'meta_key=menu_object_item_id@meta_value' => 'post',
     *     )
     *
     * @var array
     */
    public $valueReferences = array();

    /**
     * True if entity has references. Basically returns count($references) > 0.
     *
     * @var bool
     */
    public $hasReferences = false;

    /**
     * If entity is child entity (meta, term_taxonomy etc.), this contains name of reference
     * to its parent.
     *
     * @var string
     */
    public $parentReference;

    private $virtualReferences = array();

    private $frequentlyWritten = array();

    private $ignoredEntities = array();
    
    private $ignoredColumns = array();

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

        if (isset($schemaInfo['parent-reference'])) {
            $this->parentReference = $schemaInfo['parent-reference'];
        }

        if (isset($schemaInfo['references'])) {
            $this->references = $schemaInfo['references'];
            $this->hasReferences = true;
        }

        if (isset($schemaInfo['mn-references'])) {
            foreach ($schemaInfo['mn-references'] as $reference => $targetEntity) {
                if (Strings::startsWith($reference, '~')) {
                    $reference = Strings::substring($reference, 1);
                    $this->virtualReferences[$reference] = true;
                }
                $this->mnReferences[$reference] = $targetEntity;
            }
            $this->hasReferences = true;
        }

        if (isset($schemaInfo['value-references'])) {
            foreach ($schemaInfo['value-references'] as $key => $references) {
                list($keyCol, $valueCol) = explode('@', $key);
                foreach ($references as $reference => $targetEntity) {
                    $key = $keyCol . '=' . $reference . '@' . $valueCol;
                    $this->valueReferences[$key] = $targetEntity;
                }
            }
            $this->hasReferences = true;
        }

        if (isset($schemaInfo['frequently-written'])) {
            $this->frequentlyWritten = $schemaInfo['frequently-written'];
        }

        if (isset($schemaInfo['ignored-entities'])) {
            $this->ignoredEntities = $schemaInfo['ignored-entities'];
        }

        if (isset($schemaInfo['ignored-columns'])) {

            foreach ($schemaInfo['ignored-columns'] as $column) {
                if (is_string($column)) {
                    $this->ignoredColumns[$column] = function () {}; // if column does not have any compute function we create 'NOOP' function
                } else {
                    $this->ignoredColumns[array_keys($column)[0]] = substr(array_values($column)[0], 1);
                }
            }
        }

    }

    public function getIgnoredColumns() {
        return $this->ignoredColumns;
    }

    public function isVirtualReference($reference) {
        return isset($this->virtualReferences[$reference]);
    }

    public function isFrequentlyWrittenEntity($entity) {
        $rulesAndIntervals = $this->getRulesAndIntervalsForFrequentlyWrittenEntities();
        $rules = array_column($rulesAndIntervals, 'rule');
        return QueryLanguageUtils::entityMatchesSomeRule($entity, $rules);
    }

    public function isIgnoredEntity($entity) {
        $rules = $this->getRulesForIgnoredEntities();
        return QueryLanguageUtils::entityMatchesSomeRule($entity, $rules);
    }

    public function getRulesAndIntervalsForFrequentlyWrittenEntities() {
        $queries = array_map(function ($banan) {
            return is_string($banan) ? $banan : $banan['query'];
        }, $this->frequentlyWritten);

        $rules = QueryLanguageUtils::createRulesFromQueries($queries);

        $rulesAndIntervals = array();
        foreach ($rules as $key => $rule) {
            $interval = isset($this->frequentlyWritten[$key]['interval']) ? $this->frequentlyWritten[$key]['interval'] : self::FREQUENTLY_WRITTEN_DEFAULT_INTERVAL;
            $rulesAndIntervals[] = array('rule' => $rule, 'interval' => $interval);
        }


        return $rulesAndIntervals;
    }

    public function getRulesForIgnoredEntities() {
        return QueryLanguageUtils::createRulesFromQueries($this->ignoredEntities);
    }
}

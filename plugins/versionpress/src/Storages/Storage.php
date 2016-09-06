<?php
namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Storages\Serialization\IniSerializer;

/**
 * Stores an entity to a file that can be versioned by Git. Storages are chosen by {@link VersionPress\Storages\Mirror},
 * in a way "implement" its API of saving and deleting entities and in the end
 * manage INI files in the `wp-content/vpdb` folder.
 */
abstract class Storage
{

    const PREFIX_PLACEHOLDER = "<<table-prefix>>";

    /** @var bool */
    protected $isTransaction;

    /** @var bool */
    public $ignoreFrequentlyWrittenEntities = true;

    /** @var EntityInfo */
    protected $entityInfo;

    /** @var string */
    private $dbPrefix;

    public function __construct(EntityInfo $entityInfo, $dbPrefix)
    {
        $this->entityInfo = $entityInfo;
        $this->dbPrefix = $dbPrefix;
    }

    /**
     * Saves data to a storage
     *
     * @param array $data Associative array with values to save. On `insert`, the $data will contain full entity data
     *                    plus things like VPID. On further updates, the data will typically contain just the updated
     *                    values and a VPID (assigned in {@link VersionPress\Database\WpdbMirrorBridge} so that
     *                    the appropriate file could be located).
     * @return ChangeInfo|null Null indicates that the save operation didn't really change anything (may happen).
     *                         Otherwise, the ChangeInfo object is returned.
     */
    abstract public function save($data);


    /**
     * Deletes entity from a storage
     *
     * @param array $restriction An array that typically contains a 'vp_id' key that specifies which entity to delete
     * @return ChangeInfo|null Null indicates that no actual delete happened (for example, the INI file for a given VPID
     *                         didn't exist). Otherwise, the ChangeInfo object is returned (its action is usually
     *                         'delete').
     */
    abstract public function delete($restriction);

    /**
     * Load an entity by given VPID
     *
     * @param string $id VPID
     * @param string $parentId VPID of parent entity (for example post for postmeta)
     * @return array Array representing an entity
     */
    abstract public function loadEntity($id, $parentId);

    /**
     * Loads all entities managed by this storage
     *
     * @return array[] Array of arrays where keys are VPIDs and values are arrays with entity data
     */
    abstract public function loadAll();

    /**
     * True / false if the entity should be saved / ignored. Works as a filtering method.
     *
     * @param array $data
     * @return bool
     */
    public function shouldBeSaved($data)
    {
        $shouldBeSaved = true;

        if ($this->entityInfo->isIgnoredEntity($data)) {
            $shouldBeSaved = false;
        }

        if ($this->ignoreFrequentlyWrittenEntities) {
            $isFrequentlyWrittenEntity = $this->entityInfo->isFrequentlyWrittenEntity($data);
            $shouldBeSaved = $shouldBeSaved && !$isFrequentlyWrittenEntity;
        }

        $entityName = $this->entityInfo->entityName;
        return apply_filters("vp_entity_should_be_saved_$entityName", $shouldBeSaved, $data, $this);
    }

    /**
     * Called from {@link VersionPress\Initialization\Initializer} to give storage a chance to prepare itself.
     * For example, directory storage uses this to create its folder.
     *
     * Note: consider if this method needs to be here
     */
    abstract public function prepareStorage();

    /**
     * Returns a physical path to an INI file where the entity is stored
     *
     * @param string $id VPID
     * @param string|null $parentId VPID of parent entity (for example post for postmeta)
     * @return string
     */
    abstract public function getEntityFilename($id, $parentId);

    abstract public function getPathCommonToAllEntities();

    /**
     * Returns true if the entity exists.
     *
     * @param string $id VPID
     * @param string|null $parentId VPID of parent entity (for example post for postmeta)
     * @return bool
     */
    abstract public function exists($id, $parentId);

    /**
     * Saves data only in memory (used during initialization).
     *
     * @param array $data {@see Storage::save()}
     */
    abstract public function saveLater($data);

    /**
     * Transfers data from memory (saved by Storage::saveLater()) to the files.
     */
    abstract public function commit();

    protected function deserializeEntity($serializedEntity)
    {
        if ($serializedEntity === '') {
            return [];
        }

        $entity = IniSerializer::deserialize($serializedEntity);
        $entity = $this->flattenEntity($entity);
        $entity[$this->entityInfo->vpidColumnName] = $this->maybeReplacePlaceholderWithPrefix($entity[$this->entityInfo->vpidColumnName]);
        return $entity;
    }

    protected function serializeEntity($vpid, $entity)
    {
        $vpid = $this->maybeReplacePrefixWithPlaceholder($vpid);

        unset($entity[$this->entityInfo->vpidColumnName]);
        return IniSerializer::serialize([$vpid => $entity]);
    }

    private function flattenEntity($entity)
    {
        if (count($entity) === 0) {
            return $entity;
        }

        reset($entity);
        $vpid = key($entity);
        $flatEntity = $entity[$vpid];
        $flatEntity[$this->entityInfo->vpidColumnName] = $vpid;

        return $flatEntity;
    }

    private function maybeReplacePrefixWithPlaceholder($key)
    {
        if (Strings::startsWith($key, $this->dbPrefix)) {
            return self::PREFIX_PLACEHOLDER . Strings::substring($key, Strings::length($this->dbPrefix));
        }
        return $key;
    }

    private function maybeReplacePlaceholderWithPrefix($key)
    {
        if (Strings::startsWith($key, self::PREFIX_PLACEHOLDER)) {
            return $this->dbPrefix . Strings::substring($key, Strings::length(self::PREFIX_PLACEHOLDER));
        }
        return $key;
    }
}

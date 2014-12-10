<?php

/**
 * Stores an entity to a file that can be versioned by Git. Storages are chosen by {@link Mirror},
 * in a way "implement" its API of saving and deleting entities and in the end
 * manage INI files in the `wp-content/vpdb` folder.
 */
abstract class Storage {

    /**
     * Saves data to a storage
     *
     * @param array $data Associative array with values to save. On `insert`, the $data will contain full entity data
     *                    plus things like VPID. On further updates, the data will typically contain just the updated values
     *                    and a VPID (assigned in {@link MirroringDatabase} so that the appropriate file could
     *                    be located).
     * @return ChangeInfo|null Null indicates that the save operation didn't really change anything (may happen). Otherwise,
     *                         the ChangeInfo object is returned.
     */
    abstract function save($data);


    /**
     * Deletes entity from a storage
     *
     * @param array $restriction An array that typically contains a 'vp_id' key that specifies which entity to delete
     * @return ChangeInfo|null Null indicates that no actual delete happened (for example, the INI file for a given VPID
     *                         didn't exist). Otherwise, the ChangeInfo object is returned (its action is usually 'delete').
     */
    abstract function delete($restriction);

    /**
     * Load an entity by given VPID
     *
     * @param $id string VPID
     * @return array Array representing an entity
     */
    abstract function loadEntity($id);

    /**
     * Loads all entities managed by this storage
     *
     * @return array[] Array of arrays where keys are VPIDs and values are arrays with entity data
     */
    abstract function loadAll();

    /**
     * True / false if the entity should be saved / ignored. Works as a filtering method.
     *
     * @param array $data
     * @return bool
     */
    abstract function shouldBeSaved($data);

    /**
     * Called from {@link Initializer} to give storage a chance to prepare itself.
     * For example, directory storage uses this to create its folder.
     *
     * Note: consider if this method needs to be here
     */
    abstract function prepareStorage();

    /**
     * Only used by Initializer, possibly remove?
     *
     * @param $entities
     */
    abstract function saveAll($entities);


    /**
     * Returns a physical path to an INI file where the entity is stored
     *
     * @param string $id VPID
     * @return string
     */
    abstract function getEntityFilename($id);

    /**
     * Internal method to create a ChangeInfo. Though it is mostly an implementation
     * detail of the `save()` and `delete()` methods, most storages create ChangeInfos
     * in similar ways so the method has been extracted here, at least for the sake
     * of consistency and documentation.
     *
     * @param array $oldEntity The entity as it was stored last time. Note that the previous state
     *   is not always known or some storages might not want to provide this to the function so it sometimes
     *   is null.
     * @param array $newEntity The updated entity. Always contains the full data, never null.
     * @param string $action Code that calls this method (save() and delete() methods)
     *   provides typically a basic action (create / edit / delete). More specific action can be
     *   determined from the $oldEntity / $newEntity in implementation of this method.
     *
     * @return ChangeInfo Eventually used as the return value of the `save()` or the `delete()` method
     */
    protected abstract function createChangeInfo($oldEntity, $newEntity, $action);

    /**
     * @param string $path Full path to the storage
     * @param EntityInfo $entityInfo Entity info
     */
    abstract function __construct($path, $entityInfo);

}
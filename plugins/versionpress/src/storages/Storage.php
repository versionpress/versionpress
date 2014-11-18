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
     * @param array $data Associative array with values to save. On `insert`, the $data will contain a full entity,
     *                    on further updates, the data will typically contain just the updated values
     *                    and a VPID (assigned in {@link MirroringDatabase} so that the appropriate file could
     *                    be located).
     */
    abstract function save($data);


    /**
     * Deletes entity from a storage
     *
     * @param array $restriction An array that typically contains a 'vp_id' key that specifies which entity to delete
     */
    abstract function delete($restriction);

    /**
     * Loads all entities managed by this storage
     *
     * @return array[] Array of arrays where keys are VPIDs and values are arrays with entity data
     */
    abstract function loadAll();

    /**
     * True / false if the entity should be saved / ignored. Works as a filtering method.
     *
     * @param $data
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
     * @var callable[]
     */
    private $onChangeListeners = array();

    /**
     * This storage notifies "change listeners" of changes done to a storage
     * when the save() action is done. Currently only used by the Mirror and
     * there is only one listener at any time so it could probably be replaced
     * by a return value of the save() method.
     *
     * @param callable $callback
     */
    function addChangeListener($callback) {
        $this->onChangeListeners[] = $callback;
    }

    protected function callOnChangeListeners(EntityChangeInfo $changeInfo) {
        foreach ($this->onChangeListeners as $onChangeListener) {
            call_user_func($onChangeListener, $changeInfo);
        }
    }
}
<?php

namespace VersionPress\Utils;

/**
 * VersionPress "entities" are often just associative arrays, often not even complete
 * (for instance if the SQL update method is captured and only part of the entity data
 * is available). Here are some helper methods for such arrays.
 */
class EntityUtils
{

    /**
     * Used by storages to determine a diff between two entity data. Note that the data might
     * not be complet - for example, the $oldEntityData might be the previous, full state
     * of the entity while the $newEntityData is just the change that was captured by VersionPress.
     * Or the $newEntityData might be another full entity, for example in its new state.
     * It doesn't really matter, the algo is always the same: it scans for new keys
     * in $newEntityData and changed values on existing keys and adds those two things
     * to the result. 'vp_id' key is ignored by default.
     *
     * Note: keys may never be "removed" (or marked as removed) in the diff because that
     * will just not ever happen - the SQL UPDATE command is only capable of sending a change,
     * clearing a key value at most but never "removing" it entirely.
     *
     * @param array $oldEntityData Usually a full entity data in its original state
     * @param array $newEntityData Full or partial data of the new entity
     *
     * @return array Key=>value pairs of things that are new or changed in $newEntity (they can
     *               never be "removed", that will never happen when capturing SQL UPDATE actions)
     */
    public static function getDiff($oldEntityData, $newEntityData)
    {
        $diff = [];
        foreach ($newEntityData as $key => $value) {
            if (!isset($oldEntityData[$key]) || self::isChanged($oldEntityData[$key], $value)) {
                $diff[$key] = $value;
            }
        }

        return $diff;

    }

    /**
     * Evaluates if there is difference between old and new value.
     * Threads string numbers as equals to their numeric equivalents so e.g. "0" = 0
     *
     * @param $oldData
     * @param $newValue
     * @return bool
     */
    private static function isChanged($oldData, $newValue)
    {
        if (is_numeric($oldData) && is_numeric($newValue)) {
            return $oldData != $newValue;
        }
        if (is_string($oldData) && $oldData === "" && $newValue === null) {
            return false;
        }
        return $oldData !== $newValue;
    }
}

<?php

namespace VersionPress\Tests\Utils;

use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\CompositeChangeInfo;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\Database\EntityInfo;

class ChangeInfoUtils {

    /**
     * Returns full action such as "post/edit" or "wordpress/update".
     *
     * Note: changeinfo objects in production code don't have a need to represent
     * the concept of "full action"; tracked entities simply have entity names and actions.
     * This helper is so far useful only in tests so it has been moved here.
     *
     * @param ChangeInfo $changeInfo
     * @return string
     */
    public static function getFullAction($changeInfo) {

        /** @var TrackedChangeInfo $actualChangeInfo */
        $actualChangeInfo = null;

        if ($changeInfo instanceof CompositeChangeInfo) {
            /** @var CompositeChangeInfo $changeInfo */
            $sortedChangeInfos = $changeInfo->getSortedChangeInfoList();
            $actualChangeInfo = $sortedChangeInfos[0];
        } else {
            /** @var TrackedChangeInfo $changeInfo */
            $actualChangeInfo = $changeInfo;
        }

        return sprintf("%s/%s", $actualChangeInfo->getEntityName(), $actualChangeInfo->getAction());

    }

    /**
     * @param ChangeInfo $changeInfo
     * @param string $tagKey
     * @return string|null Value or null if key not found
     */
    public static function getCustomTagValue($changeInfo, $tagKey) {
        /** @var TrackedChangeInfo $actualChangeInfo */
        $actualChangeInfo = null;

        if ($changeInfo instanceof CompositeChangeInfo) {
            /** @var CompositeChangeInfo $changeInfo */
            $sortedChangeInfos = $changeInfo->getSortedChangeInfoList();
            $actualChangeInfo = $sortedChangeInfos[0];
        } else {
            /** @var TrackedChangeInfo $changeInfo */
            $actualChangeInfo = $changeInfo;
        }

        $customTags = $actualChangeInfo->getCustomTags();
        if (isset($customTags[$tagKey])) {
            return $customTags[$tagKey];
        } else {
            return null;
        }

    }

    /**
     * Returns true if two changeinfos capture the same thing, i.e., if their commits are logically
     * equivalent. Currently compares that it was the same action on the same entity (incl. VPID)
     * and that the set of custom VP tags is the same (keys must be the same, values may differ).
     *
     * Note: currently doesn't work for CompositeChangeInfo.
     *
     * @param TrackedChangeInfo $changeInfo1
     * @param TrackedChangeInfo $changeInfo2
     * @return bool
     */
    public static function captureSameAction($changeInfo1, $changeInfo2) {

        if (get_class($changeInfo1) !== get_class($changeInfo2)) {
            return false;
        }

        if ($changeInfo1->getAction() !== $changeInfo2->getAction()) {
            return false;
        }

        if ($changeInfo1 instanceof EntityInfo) {
            /** @var EntityChangeInfo $changeInfo1 */
            /** @var EntityChangeInfo $changeInfo2 */

            if ($changeInfo1->getEntityId() !== $changeInfo2->getEntityId()) {
                return false;
            }
        }

        $keysIn1ButNot2 = array_diff_key($changeInfo1->getCustomTags(), $changeInfo2->getCustomTags());
        $keysIn2ButNot1 = array_diff_key($changeInfo2->getCustomTags(), $changeInfo1->getCustomTags());
        $differentKeys = array_merge($keysIn1ButNot2, $keysIn2ButNot1);

        return count($differentKeys) == 0;

    }

}

<?php

namespace VersionPress\Tests\Utils;

use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\ChangeInfoEnvelope;
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
        $actualChangeInfo = self::getTrackedChangeInfo($changeInfo);
        return sprintf("%s/%s", $actualChangeInfo->getEntityName(), $actualChangeInfo->getAction());
    }

    /**
     * @param ChangeInfo $changeInfo
     * @param string $tagKey
     * @return string|null Value or null if key not found
     */
    public static function getCustomTagValue($changeInfo, $tagKey) {
        $actualChangeInfo = self::getTrackedChangeInfo($changeInfo);
        $customTags = $actualChangeInfo->getCustomTags();
        if (isset($customTags[$tagKey])) {
            return $customTags[$tagKey];
        } else {
            return null;
        }

    }

    public static function getVpid($changeInfo) {
        $actualChangeInfo = self::getTrackedChangeInfo($changeInfo);
        if ($actualChangeInfo instanceof EntityChangeInfo) {
            return $actualChangeInfo->getEntityId();
        } else {
            throw new \Exception("This method only work on EntityChangeInfo");
        }
    }

    /**
     * Returns true if two changeinfos capture the same thing, i.e., if their commits are logically
     * equivalent. Currently compares that it was the same action on the same entity (incl. VPID)
     * and that the set of custom VP tags is the same (keys must be the same, values may differ).
     *
     * @param TrackedChangeInfo|ChangeInfoEnvelope $changeInfo1
     * @param TrackedChangeInfo|ChangeInfoEnvelope $changeInfo2
     * @return bool
     */
    public static function captureSameAction($changeInfo1, $changeInfo2) {

        if (self::firstIsEnvelopeContainingSecond($changeInfo1, $changeInfo2) ||
            self::firstIsEnvelopeContainingSecond($changeInfo2, $changeInfo1) ||
            self::bothAreEnvelopesContainingTheSame($changeInfo1, $changeInfo2)) {
            return true;
        }

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

    /**
     * Returns an actionable tracked change info (ChangeInfoEnvelope isn't so it returns its most
     * important internal changeinfo).
     *
     * @param ChangeInfo $changeInfo
     * @return TrackedChangeInfo
     */
    public static function getTrackedChangeInfo($changeInfo) {

        if ($changeInfo instanceof ChangeInfoEnvelope) {
            /** @var ChangeInfoEnvelope $changeInfo */
            $sortedChangeInfos = $changeInfo->getSortedChangeInfoList();
            return $sortedChangeInfos[0];
        } else {
            return $changeInfo;
        }
    }

    private static function firstIsEnvelopeContainingSecond($firstChangeInfo, $secondChangeInfo) {
        $initialConditionsSatisfied = $firstChangeInfo instanceof ChangeInfoEnvelope && $secondChangeInfo instanceof TrackedChangeInfo;

        if (!$initialConditionsSatisfied) {
            return false;
        }

        $changeInfoList = $firstChangeInfo->getChangeInfoList();
        return count($changeInfoList) === 1 && self::captureSameAction($changeInfoList[0], $secondChangeInfo);
    }

    /**
     * @param ChangeInfoEnvelope $firstChangeInfo
     * @param ChangeInfoEnvelope $secondChangeInfo
     * @return bool
     */
    private static function bothAreEnvelopesContainingTheSame($firstChangeInfo, $secondChangeInfo) {
        $initialConditionsSatisfied = $firstChangeInfo instanceof ChangeInfoEnvelope && $secondChangeInfo instanceof ChangeInfoEnvelope;

        if (!$initialConditionsSatisfied) {
            return false;
        }

        $firstChangeInfoList = $firstChangeInfo->getChangeInfoList();
        $secondChangeInfoList = $secondChangeInfo->getChangeInfoList();

        if (count($firstChangeInfoList) !== count($secondChangeInfoList)) {
            return false;
        }

        return self::listsContainSameChangeInfos($firstChangeInfoList, $secondChangeInfoList);
    }

    /**
     * @param TrackedChangeInfo[] $firstChangeInfoList
     * @param TrackedChangeInfo[] $secondChangeInfoList
     * @return bool
     */
    private static function listsContainSameChangeInfos($firstChangeInfoList, $secondChangeInfoList) {
        $length = count($firstChangeInfoList);
        for ($i = 0; $i < $length; $i++) {
            $changeInfoInFirstList = $firstChangeInfoList[$i];
            $changeInfoInSecondList = $secondChangeInfoList[$i];

            if (!self::captureSameAction($changeInfoInFirstList, $changeInfoInSecondList)) {
                return false;
            }
        }

        return true;
    }
}

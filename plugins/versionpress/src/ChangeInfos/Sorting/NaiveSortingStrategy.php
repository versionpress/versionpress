<?php

namespace ChangeInfos\Sorting;


use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\OptionChangeInfo;
use VersionPress\ChangeInfos\ThemeChangeInfo;
use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\Utils\ArrayUtils;

class NaiveSortingStrategy implements SortingStrategy {

    /**
     * List of change info classes ordered by their priorities.
     * They are listed in commits / commit table in this order.
     *
     * @var string[]
     */
    private $priorityOrder = array(
        "VersionPress\ChangeInfos\WordPressUpdateChangeInfo",
        "VersionPress\ChangeInfos\VersionPressChangeInfo",
        "VersionPress\ChangeInfos\PostChangeInfo",
        "VersionPress\ChangeInfos\CommentChangeInfo",
        "VersionPress\ChangeInfos\UserChangeInfo",
        "VersionPress\ChangeInfos\RevertChangeInfo",
        "VersionPress\ChangeInfos\PluginChangeInfo",
        "VersionPress\ChangeInfos\ThemeChangeInfo",
        "VersionPress\ChangeInfos\TermChangeInfo",
        "VersionPress\ChangeInfos\OptionChangeInfo",
        "VersionPress\ChangeInfos\PostMetaChangeInfo",
        "VersionPress\ChangeInfos\UserMetaChangeInfo",
    );

    function sort($changeInfoList) {
        ArrayUtils::stablesort($changeInfoList, array($this, 'compareChangeInfo'));
        return $changeInfoList;
    }

    /**
     * Compare function for sort()
     *
     * @param TrackedChangeInfo $changeInfo1
     * @param TrackedChangeInfo $changeInfo2
     * @return int If $changeInfo1 is more important, returns -1, and the opposite for $changeInfo2. ChangeInfos
     *   of same priorities return zero.
     */
    public function compareChangeInfo($changeInfo1, $changeInfo2) {
        $class1 = get_class($changeInfo1);
        $class2 = get_class($changeInfo2);

        $priority1 = array_search($class1, $this->priorityOrder);
        $priority2 = array_search($class2, $this->priorityOrder);

        if ($priority1 < $priority2) {
            return -1;
        }

        if ($priority1 > $priority2) {
            return 1;
        }

        // From here both ChangeInfo objects are instance of the same class

        if ($changeInfo1 instanceof ThemeChangeInfo) {
            return $this->compareThemeChangeInfo($changeInfo1, $changeInfo2);
        }

        if ($changeInfo1 instanceof OptionChangeInfo) {
            return $this->compareOptionChangeInfo($changeInfo1, $changeInfo2);
        }


        if ($changeInfo1 instanceof EntityChangeInfo) {
            // Generally, the "create" action takes precedence
            if ($changeInfo1->getAction() === "create") {
                return -1;
            }

            if ($changeInfo2->getAction() === "create") {
                return 1;
            }

            return 0;
        }

        return 0;
    }

    /**
     * @param ThemeChangeInfo $changeInfo1
     * @param ThemeChangeInfo $changeInfo2
     * @return int
     */
    private function compareThemeChangeInfo($changeInfo1, $changeInfo2) {
        // For two VersionPress\ChangeInfos\ThemeChangeInfo objects, the "switch" one wins
        if ($changeInfo1->getAction() == "switch") {
            return -1;
        } else if ($changeInfo2->getAction() == "switch") {
            return 1;
        }

        return 0;
    }

    /**
     * @param OptionChangeInfo $changeInfo1
     * @param OptionChangeInfo $changeInfo2
     * @return int
     */
    private function compareOptionChangeInfo($changeInfo1, $changeInfo2) {

        // First, the "create" action takes precedence
        if ($changeInfo1->getAction() === "create" && $changeInfo2->getAction() !== "create") {
            return -1;
        }

        if ($changeInfo2->getAction() === "create" && $changeInfo1->getAction() !== "create") {
            return 1;
        }

        // Else, just sort by alphabet (which is their order in the databse and rougly OK
        // until we work out something better
        return strcmp($changeInfo1->getEntityId(), $changeInfo2->getEntityId());
    }
}

<?php

namespace ChangeInfos\Sorting;


use Nette\Utils\Strings;
use VersionPress\ChangeInfos\BulkChangeInfo;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\OptionChangeInfo;
use VersionPress\ChangeInfos\PostChangeInfo;
use VersionPress\ChangeInfos\TermChangeInfo;
use VersionPress\ChangeInfos\ThemeChangeInfo;
use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\ChangeInfos\TranslationChangeInfo;
use VersionPress\Utils\ArrayUtils;

class SortingStrategy {

    /**
     * List of change info classes ordered by their priorities.
     * They are listed in commits / commit table in this order.
     * BulkChangeInfo objects are checked dynamically.
     *
     * @var string[]
     */
    private $priorityOrder = array(
        'VersionPress\ChangeInfos\WordPressUpdateChangeInfo',
        'VersionPress\ChangeInfos\VersionPressChangeInfo',
        'VersionPress\ChangeInfos\UserChangeInfo',
        'VersionPress\ChangeInfos\PostChangeInfo',
        'VersionPress\ChangeInfos\CommentChangeInfo',
        'VersionPress\ChangeInfos\RevertChangeInfo',
        'VersionPress\ChangeInfos\PluginChangeInfo',
        'VersionPress\ChangeInfos\ThemeChangeInfo',
        'VersionPress\ChangeInfos\TermChangeInfo',
        'VersionPress\ChangeInfos\TranslationChangeInfo',
        'VersionPress\ChangeInfos\OptionChangeInfo',
        'VersionPress\ChangeInfos\PostMetaChangeInfo',
        'VersionPress\ChangeInfos\UserMetaChangeInfo',
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

        $class1 = $this->stripBulkFromClassName($class1);
        $class2 = $this->stripBulkFromClassName($class2);

        $priority1 = array_search($class1, $this->priorityOrder);
        $priority2 = array_search($class2, $this->priorityOrder);

        if ($priority1 < $priority2) {
            return -1;
        }

        if ($priority1 > $priority2) {
            return 1;
        }

        // From here both ChangeInfo objects are instance of the same class

        if ($changeInfo1 instanceof ThemeChangeInfo && $changeInfo2 instanceof ThemeChangeInfo) {
            return $this->compareThemeChangeInfo($changeInfo1, $changeInfo2);
        }

        if ($changeInfo1 instanceof OptionChangeInfo && $changeInfo2 instanceof OptionChangeInfo) {
            return $this->compareOptionChangeInfo($changeInfo1, $changeInfo2);
        }

        if ($changeInfo1 instanceof TermChangeInfo && $changeInfo2 instanceof TermChangeInfo) {
            return $this->compareTermChangeInfo($changeInfo1, $changeInfo2);
        }

        if ($changeInfo1 instanceof PostChangeInfo && $changeInfo2 instanceof PostChangeInfo) {
            return $this->comparePostChangeInfo($changeInfo1, $changeInfo2);
        }

        if ($changeInfo1 instanceof TranslationChangeInfo && $changeInfo2 instanceof TranslationChangeInfo) {
            return $this->compareTranslationChangeInfo($changeInfo1, $changeInfo2);
        }

        if (($changeInfo1 instanceof EntityChangeInfo && $changeInfo2 instanceof EntityChangeInfo)
         || ($changeInfo1 instanceof EntityChangeInfo && $changeInfo2 instanceof BulkChangeInfo)
         || ($changeInfo1 instanceof BulkChangeInfo && $changeInfo2 instanceof EntityChangeInfo)
         || ($changeInfo1 instanceof BulkChangeInfo && $changeInfo2 instanceof BulkChangeInfo)) {
            // Generally, the "create" action takes precedence
            if ($changeInfo1->getAction() === "create") {
                return -1;
            }

            if ($changeInfo2->getAction() === "create") {
                return 1;
            }
            // Then "delete" action
            if ($changeInfo1->getAction() === "delete") {
                return -1;
            }

            if ($changeInfo2->getAction() === "delete") {
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

        // The WPLANG option always has the lowest priority (it is a "noise")
        if ($changeInfo1->getEntityId() == "WPLANG") {
            return 1;
        } else if ($changeInfo2->getEntityId() == "WPLANG") {
            return -1;
        }

        // The "create" action takes precedence
        if ($changeInfo1->getAction() === "create" && $changeInfo2->getAction() !== "create") {
            return -1;
        }

        if ($changeInfo2->getAction() === "create" && $changeInfo1->getAction() !== "create") {
            return 1;
        }

        // Finally, sort by alphabet. It is the options order in the databse and rougly OK
        // until we work out something better.
        return strcmp($changeInfo1->getEntityId(), $changeInfo2->getEntityId());
    }

    /**
     * @param TermChangeInfo $changeInfo1
     * @param TermChangeInfo $changeInfo2
     * @return int
     */
    private function compareTermChangeInfo($changeInfo1, $changeInfo2) {
        // For two VersionPress\ChangeInfos\TermChangeInfo objects, the "delete" one wins
        if ($changeInfo1->getAction() == "delete") {
            return -1;
        } else if ($changeInfo2->getAction() == "delete") {
            return 1;
        }

        return 0;
    }

    /**
     * @param PostChangeInfo $changeInfo1
     * @param PostChangeInfo $changeInfo2
     * @return int
     */
    private function comparePostChangeInfo($changeInfo1, $changeInfo2) {
        /*
         * TODO: Needs refactor
         * For two VersionPress\ChangeInfos\PostChangeInfo objects, the action precendence is
         *  "create" > "delete" > "edit" > all other actions
         */
        if ($changeInfo1->getAction() == "create") {
            return -1;
        } else if ($changeInfo2->getAction() == "create") {
            return 1;
        } else if ($changeInfo1->getAction() == "delete") {
            return -1;
        } else if ($changeInfo2->getAction() == "delete") {
            return 1;
        } else if ($changeInfo1->getAction() == "draft") {
            return -1;
        } else if ($changeInfo2->getAction() == "draft") {
            return 1;
        } else if ($changeInfo1->getAction() == "edit") {
            return -1;
        } else if ($changeInfo2->getAction() == "edit") {
            return 1;
        }

        return 0;
    }

    /**
     * @param TranslationChangeInfo $changeInfo1
     * @param TranslationChangeInfo $changeInfo2
     * @return int
     */
    private function compareTranslationChangeInfo($changeInfo1, $changeInfo2) {
        // For two VersionPress\ChangeInfos\TranslationChangeInfo objects, the "activate" one wins
        if ($changeInfo1->getAction() == "activate") {
            return -1;
        } else if ($changeInfo2->getAction() == "activate") {
            return 1;
        }

        return 0;
    }

    private function stripBulkFromClassName($className) {
        if (Strings::contains($className, "Bulk")) {
            $className = Strings::replace($className, "~Bulk~");
        }
        return $className;
    }
}

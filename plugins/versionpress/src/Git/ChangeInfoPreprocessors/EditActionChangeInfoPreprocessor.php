<?php
namespace VersionPress\Git\ChangeInfoPreprocessors;


use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\PostChangeInfo;
use VersionPress\ChangeInfos\TrackedChangeInfo;

class EditActionChangeInfoPreprocessor implements ChangeInfoPreprocessor {

    /**
     * More actions '* /edit' for same entity are replaced with one '* /edit' action.
     *
     * @param ChangeInfo[] $changeInfoList
     * @return ChangeInfo[][]
     */
    function process($changeInfoList) {

        // 1) Find all post/edit
        $entities = $this->getChangeInfosByIndicies($changeInfoList, array("edit"));

        // 2) Replace all post/edit with single post/edit action
        foreach ($entities as $entityId => $changeInfos) {
            $edits = $changeInfos['edit'];
            if (count($edits) > 1) {
                $updatedProperties = array();
                /** @var EntityChangeInfo $firstEditChangeInfo */
                $firstEditChangeInfo = $changeInfoList[$changeInfos["edit"][0]];
                foreach ($edits as $edit) {
                    /** @var EntityChangeInfo $editChangeInfo */
                    $editChangeInfo = $changeInfoList[$edit];
                    if (method_exists($editChangeInfo, "getPostUpdatedProperties")) {
                        $updatedProperties = array_unique(array_merge($updatedProperties, $editChangeInfo->getPostUpdatedProperties()));
                    }
                    unset($changeInfoList[$edit]);
                }
                if (method_exists($firstEditChangeInfo, "setPostUpdatedProperties")) {
                    $firstEditChangeInfo->setPostUpdatedProperties($updatedProperties);
                }
                $changeInfoList[] = $firstEditChangeInfo;
            }
        }
        return array($changeInfoList);
    }

    /**
     * Find all changeInfos and group them according to provided indicies in $changeInfoList by VPID
     * @param ChangeInfo[] $changeInfoList
     * @param array $indicies
     * @return array
     */
    private function getChangeInfosByIndicies($changeInfoList, $indicies) {
        $entities = array();
        foreach ($changeInfoList as $key => $changeInfo) {
            if ($changeInfo instanceof EntityChangeInfo && in_array($changeInfo->getAction(), $indicies)) {
                if (!isset($entities[$changeInfo->getEntityId()])) {
                    $entities[$changeInfo->getEntityId()] = array();
                }
                $entities[$changeInfo->getEntityId()][$changeInfo->getAction()][] = $key;
            }
        }
        return $entities;
    }

}

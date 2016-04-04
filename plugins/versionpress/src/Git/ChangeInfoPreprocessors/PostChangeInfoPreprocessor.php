<?php

namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\PostChangeInfo;

class PostChangeInfoPreprocessor implements ChangeInfoPreprocessor {

    /**
     * If both 'post/draft' and 'post/publish' actions exist for the same entity, replace them with one 'post/create' action.
     *
     * @param ChangeInfo[] $changeInfoList
     * @return ChangeInfo[][]
     */
    function process($changeInfoList) {

        // 1) Find combination of post/draft and post/publish
        $entities = $this->getChangeInfosByIndicies($changeInfoList, array("draft", "publish"));

        // 2) Replace combination of post/draft and post/publish with single post/create action
        foreach ($entities as $entityId => $changeInfos) {
            if (count($changeInfos) == 2) {
                /** @var PostChangeInfo $publish */
                $publish = $changeInfoList[$changeInfos["publish"][0]];
                $this->removeChangeInfos($changeInfoList, $changeInfos);
                $changeInfoList[] = new PostChangeInfo("create", $publish->getEntityId(), $publish->getPostType(), $publish->getPostTitle());
            }
        }

        // 1) Find combination of post/create and post/edit
        $entities = $this->getChangeInfosByIndicies($changeInfoList, array("create", "edit"));

        // 2) Replace combination of post/create and post/edit with single post/create action
        foreach ($entities as $entityId => $changeInfos) {
            if (count($changeInfos) == 2) {
                /** @var PostChangeInfo $create */
                $create = $changeInfoList[$changeInfos["create"][0]];
                $this->removeChangeInfos($changeInfoList, $changeInfos);
                $changeInfoList[] = new PostChangeInfo("create", $create->getEntityId(), $create->getPostType(), $create->getPostTitle());
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
            if ($changeInfo instanceof PostChangeInfo && in_array($changeInfo->getAction(), $indicies)) {
                if (!isset($entities[$changeInfo->getEntityId()])) {
                    $entities[$changeInfo->getEntityId()] = array();
                }
                $entities[$changeInfo->getEntityId()][$changeInfo->getAction()][] = $key;
            }
        }
        return $entities;
    }

    /**
     * Removes all changeInfos from source changeInfoList
     * @param $changeInfoList
     * @param $changeInfos
     */
    private function removeChangeInfos(&$changeInfoList, $changeInfos) {
        foreach ($changeInfos as $indicie => $indexes) {
            foreach ($indexes as $index) {
                unset($changeInfoList[$index]);
            }
        }
    }

}

<?php

namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\PostChangeInfo;

class PostChangeInfoPreprocessor implements ChangeInfoPreprocessor {

    /**
     * Processes the ChangeInfo list and returns one or more
     * new lists.
     *
     * @param ChangeInfo[] $changeInfoList
     * @return ChangeInfo[][]
     */
    function process($changeInfoList) {
        $entities = array();
        foreach ($changeInfoList as $key => $changeInfo) {
            if ($changeInfo instanceof PostChangeInfo && in_array($changeInfo->getAction(), array("draft", "publish"))) {
                if (!isset($entities[$changeInfo->getEntityId()]))
                    $entities[$changeInfo->getEntityId()] = array();
                $entities[$changeInfo->getEntityId()][$changeInfo->getAction()] = $key;
            }
        }

        foreach($entities as $entityId => $changeInfos) {
            if(count($changeInfos) == 2) {
                /** @var PostChangeInfo $publish */
                $publish = $changeInfoList[$changeInfos["publish"]];
                unset($changeInfoList[$changeInfos["draft"]]);
                unset($changeInfoList[$changeInfos["publish"]]);
                $changeInfoList[] = new PostChangeInfo("create", $publish->getEntityId(), $publish->getPostType(), $publish->getPostTitle());
            }
        }
        return array($changeInfoList);
    }
}
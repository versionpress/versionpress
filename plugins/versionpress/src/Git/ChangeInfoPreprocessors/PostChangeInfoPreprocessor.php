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
        $entities = array();

        // 1) Find all post/draft and post/publish actions and group their indices in $changeInfoList by VPID
        foreach ($changeInfoList as $key => $changeInfo) {
            if ($changeInfo instanceof PostChangeInfo && in_array($changeInfo->getAction(), array("draft", "publish"))) {
                if (!isset($entities[$changeInfo->getEntityId()])) {
                    $entities[$changeInfo->getEntityId()] = array();
                }
                $entities[$changeInfo->getEntityId()][$changeInfo->getAction()] = $key;
            }
        }

        // 2) Replace combination of post/draft and post/publish with single post/create action
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
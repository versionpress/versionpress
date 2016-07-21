<?php

namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\ChangeInfoUtils;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\PostChangeInfo;

class PostChangeInfoPreprocessor implements ChangeInfoPreprocessor
{

    /**
     * If both 'post/draft' and 'post/publish' actions exist for the same entity,
     * replace them with one 'post/create' action.
     *
     * @param ChangeInfo[] $changeInfoList
     * @return ChangeInfo[][]
     */
    public function process($changeInfoList)
    {

        // 1) Find and replace combination of post/draft and post/publish with single post/create action
        $this->replaceChangeInfosCombination($changeInfoList, ["draft", "publish"], "create");

        // 1) Find and replace combination of post/draft and post/edit with single post/create action
        $this->replaceChangeInfosCombination($changeInfoList, ["draft", "edit"], "draft");

        // 1) Find and replace combination of post/create and post/edit with single post/create action
        $this->replaceChangeInfosCombination($changeInfoList, ["create", "edit"], "create");

        return [$changeInfoList];
    }

    /**
     * Find all changeInfos and group them according to provided indicies in $changeInfoList by VPID
     * @param ChangeInfo[] $changeInfoList
     * @param array $indicies
     * @return array
     */
    private function getChangeInfosByIndicies($changeInfoList, $indicies)
    {
        $entities = [];
        foreach ($changeInfoList as $key => $changeInfo) {
            /** @var EntityChangeInfo $changeInfo */
            if (ChangeInfoUtils::changeInfoRepresentsEntity($changeInfo, 'post') && in_array($changeInfo->getAction(), $indicies)) {
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
    private function removeChangeInfos(&$changeInfoList, $changeInfos)
    {
        foreach ($changeInfos as $indicie => $indexes) {
            foreach ($indexes as $index) {
                unset($changeInfoList[$index]);
            }
        }
    }

    /**
     * @param $changeInfoList
     * @param array $indicies
     * @param string $resultAction
     */
    private function replaceChangeInfosCombination(&$changeInfoList, $indicies, $resultAction)
    {
        $entities = $this->getChangeInfosByIndicies($changeInfoList, $indicies);
        foreach ($entities as $entityId => $changeInfos) {
            if (count($changeInfos) == 2) {
                /** @var PostChangeInfo $sourceChangeInfo */
                $sourceChangeInfo = $changeInfoList[$changeInfos[$indicies[0]][0]];
                $this->removeChangeInfos($changeInfoList, $changeInfos);
                $changeInfoList[] = new PostChangeInfo(
                    $resultAction,
                    $sourceChangeInfo->getEntityId(),
                    $sourceChangeInfo->getPostType(),
                    $sourceChangeInfo->getPostTitle()
                );
            }
        }
    }
}

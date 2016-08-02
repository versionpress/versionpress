<?php
namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\EntityChangeInfo;

class EditActionChangeInfoPreprocessor implements ChangeInfoPreprocessor
{

    /**
     * More actions '* /edit' for same entity are replaced with one '* /edit' action.
     *
     * @param ChangeInfo[] $changeInfoList
     * @return ChangeInfo[][]
     */
    public function process($changeInfoList)
    {

        // 1) Find all post/edit
        $entities = $this->getChangeInfosByIndicies($changeInfoList, ["edit"]);

        // 2) Replace all post/edit with single post/edit action
        foreach ($entities as $entityId => $changeInfos) {
            $edits = $changeInfos['edit'];
            if (count($edits) > 1) {
                $updatedProperties = [];
                /** @var EntityChangeInfo $firstEditChangeInfo */
                $firstEditChangeInfo = $changeInfoList[$changeInfos["edit"][0]];
                foreach ($edits as $edit) {
                    unset($changeInfoList[$edit]);
                }

                $changeInfoList[] = $firstEditChangeInfo;
            }
        }
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
            if ($changeInfo instanceof EntityChangeInfo && in_array($changeInfo->getAction(), $indicies)) {
                $entities[$changeInfo->getId()][$changeInfo->getAction()][] = $key;
            }
        }
        return $entities;
    }
}

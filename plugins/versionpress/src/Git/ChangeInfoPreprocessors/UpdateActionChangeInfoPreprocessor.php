<?php
namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\ChangeInfoFactory;
use VersionPress\ChangeInfos\EntityChangeInfo;

class UpdateActionChangeInfoPreprocessor implements ChangeInfoPreprocessor
{

    public function __construct(ChangeInfoFactory $changeInfoFactory)
    {
    }

    /**
     * More actions '* /update' for same entity are replaced with one '* /update' action.
     *
     * @param ChangeInfo[] $changeInfoList
     * @return ChangeInfo[][]
     */
    public function process($changeInfoList)
    {

        // 1) Find all post/update
        $entities = $this->getChangeInfosByIndicies($changeInfoList, ["update"]);

        // 2) Replace all post/update with single post/update action
        foreach ($entities as $entityId => $changeInfos) {
            $updates = $changeInfos['update'];
            if (count($updates) > 1) {
                /** @var EntityChangeInfo $firstUpdateChangeInfo */
                $firstUpdateChangeInfo = $changeInfoList[$changeInfos["update"][0]];
                foreach ($updates as $update) {
                    unset($changeInfoList[$update]);
                }

                $changeInfoList[] = $firstUpdateChangeInfo;
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

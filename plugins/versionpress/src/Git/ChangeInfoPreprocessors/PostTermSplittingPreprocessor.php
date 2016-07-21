<?php

namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\ChangeInfoUtils;
use VersionPress\Utils\ArrayUtils;

class PostTermSplittingPreprocessor implements ChangeInfoPreprocessor
{

    public function process($changeInfoList)
    {
        if ($this->containsPostChangeInfo($changeInfoList) && $this->containsTermChangeInfo($changeInfoList)) {

            $filterFn = function ($changeInfo) {
                return ChangeInfoUtils::changeInfoRepresentsEntity($changeInfo, 'term') ||
                ChangeInfoUtils::changeInfoRepresentsEntity($changeInfo, 'term_taxonomy');
            };

            $termChangeInfoList = array_values(array_filter($changeInfoList, $filterFn));
            $restChangeInfoList = array_values(array_filter($changeInfoList, function ($changeInfo) use ($filterFn) {
                return !$filterFn($changeInfo);
            }));

            return [$termChangeInfoList, $restChangeInfoList];
        }

        return [$changeInfoList];
    }

    private function containsPostChangeInfo($changeInfoList)
    {
        return ArrayUtils::any($changeInfoList, function ($changeInfo) {
            return ChangeInfoUtils::changeInfoRepresentsEntity($changeInfo, 'post');
        });
    }

    private function containsTermChangeInfo($changeInfoList)
    {
        return ArrayUtils::any($changeInfoList, function ($changeInfo) {
            return ChangeInfoUtils::changeInfoRepresentsEntity($changeInfo, 'term');
        });
    }
}

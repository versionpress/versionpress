<?php

namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\PostChangeInfo;
use VersionPress\ChangeInfos\TermChangeInfo;
use VersionPress\Utils\ArrayUtils;

class PostTermSplittingPreprocessor implements ChangeInfoPreprocessor
{

    public function process($changeInfoList)
    {
        if ($this->containsPostChangeInfo($changeInfoList) && $this->containsTermChangeInfo($changeInfoList)) {
            $termChangeInfoList = array_values(array_filter($changeInfoList, function ($changeInfo) {
                return $changeInfo instanceof TermChangeInfo;
            }));
            $restChangeInfoList = array_values(array_filter($changeInfoList, function ($changeInfo) {
                return !($changeInfo instanceof TermChangeInfo);
            }));

            return [$termChangeInfoList, $restChangeInfoList];
        }

        return [$changeInfoList];
    }

    private function containsPostChangeInfo($changeInfoList)
    {
        return ArrayUtils::any($changeInfoList, function ($changeInfo) {
            return $changeInfo instanceof PostChangeInfo;
        });
    }

    private function containsTermChangeInfo($changeInfoList)
    {
        return ArrayUtils::any($changeInfoList, function ($changeInfo) {
            return $changeInfo instanceof TermChangeInfo;
        });
    }
}

<?php

namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\PostChangeInfo;
use VersionPress\ChangeInfos\TermChangeInfo;
use VersionPress\Utils\ArrayUtils;

class PostTermSplittingPreprocessor implements ChangeInfoPreprocessor {

    function process($changeInfoList) {
        if ($this->containsPostChangeInfo($changeInfoList) && $this->containsTermChangeInfo($changeInfoList)) {
            $termChangeInfoList = array_values(array_filter($changeInfoList, function ($changeInfo) { return $changeInfo instanceof TermChangeInfo; }));
            $restChangeInfoList = array_values(array_filter($changeInfoList, function ($changeInfo) { return !($changeInfo instanceof TermChangeInfo); }));

            return array ($termChangeInfoList, $restChangeInfoList);
        }

        return array($changeInfoList);
    }

    private function containsPostChangeInfo($changeInfoList) {
        return ArrayUtils::any($changeInfoList, function ($changeInfo) {
                return $changeInfo instanceof PostChangeInfo;
            });
    }

    private function containsTermChangeInfo($changeInfoList) {
        return ArrayUtils::any($changeInfoList, function ($changeInfo) {
            return $changeInfo instanceof TermChangeInfo;
        });
    }
}
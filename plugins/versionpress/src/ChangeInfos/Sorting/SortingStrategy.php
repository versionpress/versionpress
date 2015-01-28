<?php

namespace ChangeInfos\Sorting;


use VersionPress\ChangeInfos\TrackedChangeInfo;

interface SortingStrategy {
    /**
     * Sorts given list of ChangeInfo objects
     *
     * @param TrackedChangeInfo[] $changeInfoList
     * @return TrackedChangeInfo[]
     */
    function sort($changeInfoList);
}
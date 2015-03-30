<?php

namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\ChangeInfo;

interface ChangeInfoPreprocessor {
    /**
     * Processes the ChangeInfo list and returns one or more
     * new lists.
     *
     * @param ChangeInfo[] $changeInfoList
     * @return ChangeInfo[][]
     */
    function process($changeInfoList);
}
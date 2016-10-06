<?php

namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\ChangeInfoFactory;

interface ChangeInfoPreprocessor
{

    public function __construct(ChangeInfoFactory $changeInfoFactory);

    /**
     * Processes the ChangeInfo list and returns one or more
     * new lists.
     *
     * @param ChangeInfo[] $changeInfoList
     * @return ChangeInfo[][]
     */
    public function process($changeInfoList);
}

<?php

namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Utils\StringUtils;

class BulkPostMetaChangeInfo extends BulkChangeInfo {

    public function getChangeDescription() {
        return Strings::firstUpper(StringUtils::verbToPastTense($this->getAction())) . " $this->count post-meta";
    }
}
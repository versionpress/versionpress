<?php

namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Utils\StringUtils;

class BulkUserMetaChangeInfo extends BulkChangeInfo {

    public function getChangeDescription() {
        return Strings::firstUpper(StringUtils::verbToPastTense($this->getAction())) . " $this->count user-meta";
    }
}
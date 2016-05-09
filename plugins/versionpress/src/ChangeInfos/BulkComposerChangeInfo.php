<?php

namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Utils\StringUtils;

class BulkComposerChangeInfo extends BulkChangeInfo
{

    public function getChangeDescription()
    {
        return sprintf(
            "%s %d Composer packages",
            Strings::capitalize(StringUtils::verbToPastTense($this->getAction())),
            $this->count
        );
    }
}

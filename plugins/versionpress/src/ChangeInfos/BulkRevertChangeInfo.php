<?php

namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Utils\StringUtils;

class BulkRevertChangeInfo extends BulkChangeInfo {

    public function getChangeDescription() {
        if ($this->count === 1) {
            return $this->changeInfos[0]->getChangeDescription();
        }

        return "Reverted" . " $this->count changes";
    }
}

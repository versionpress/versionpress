<?php

namespace VersionPress\ChangeInfos;

class BulkUserChangeInfo extends BulkChangeInfo {

    public function getChangeDescription() {
        if ($this->count === 1) {
            return $this->changeInfos[0]->getChangeDescription();
        }

        return parent::getChangeDescription();
    }
}
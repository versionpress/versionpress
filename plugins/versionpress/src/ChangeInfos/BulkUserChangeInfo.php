<?php

namespace VersionPress\ChangeInfos;

class BulkUserChangeInfo extends BulkChangeInfo {

    public function getChangeDescription() {
        if ($this->getAction() === "delete") {
            return "Deleted $this->count users";
        }

        return parent::getChangeDescription();
    }
}
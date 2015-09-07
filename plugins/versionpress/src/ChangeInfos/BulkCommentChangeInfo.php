<?php

namespace VersionPress\ChangeInfos;

class BulkCommentChangeInfo extends BulkChangeInfo {

    public function getChangeDescription() {
        if ($this->count === 1) {
            return $this->changeInfos[0]->getChangeDescription();
        }

        switch ($this->getAction()) {
            case "trash":
                return "Moved $this->count comments into trash";
            case "untrash":
                return "Moved $this->count comments from trash";
            case "spam":
                return "Marked $this->count comments as spam";
            case "unspam":
                return "Marked $this->count comments as not spam";
        }

        return parent::getChangeDescription();
    }
}
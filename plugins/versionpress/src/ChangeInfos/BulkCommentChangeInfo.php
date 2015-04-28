<?php

namespace VersionPress\ChangeInfos;

class BulkCommentChangeInfo extends BulkChangeInfo {

    public function getChangeDescription() {
        switch ($this->getAction()) {
            case "delete":
                return "Deleted $this->count comments";
            case "trash":
                return "Moved $this->count comments into trash";
            case "untrash":
                return "Moved $this->count comments from trash";
            case "spam":
                return "Marked $this->count comments as spam";
            case "unspam":
                return "Marked $this->count comments as not spam";
            case "approve":
                return "Approved $this->count comments";
            case "unapprove":
                return "Unapproved $this->count comments";
        }

        return parent::getChangeDescription();
    }
}
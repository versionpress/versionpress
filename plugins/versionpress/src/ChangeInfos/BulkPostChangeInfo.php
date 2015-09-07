<?php

namespace VersionPress\ChangeInfos;

use VersionPress\Utils\StringUtils;

class BulkPostChangeInfo extends BulkChangeInfo {

    public function getChangeDescription() {
        /** @var PostChangeInfo $postChangeInfo */
        $postChangeInfo = $this->changeInfos[0];
        $postTypePlural = StringUtils::pluralize($postChangeInfo->getPostType());

        if ($postTypePlural === "nav_menu_item") {
            return "Updated menu items";
        }

        if ($this->count === 1) {
            return $this->changeInfos[0]->getChangeDescription();
        }

        switch ($this->getAction()) {
            case "trash":
                return "Moved $this->count $postTypePlural to trash";
            case "untrash":
                return "Moved $this->count $postTypePlural from trash";
            case "edit":
                return "Updated $this->count $postTypePlural";
        }

        return parent::getChangeDescription();
    }
}
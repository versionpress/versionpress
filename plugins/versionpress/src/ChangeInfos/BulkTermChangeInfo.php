<?php

namespace VersionPress\ChangeInfos;

use Tracy\Debugger;
use VersionPress\Utils\StringUtils;

class BulkTermChangeInfo extends BulkChangeInfo {

    public function getChangeDescription() {
        $taxonomies = StringUtils::pluralize($this->getTaxonomyName());

        if ($this->getAction() === "delete") {
            return "Deleted $this->count $taxonomies";
        }

        return parent::getChangeDescription();
    }

    private function getTaxonomyName() {
        /** @var TermChangeInfo $termChangeInfo */
        $termChangeInfo = $this->changeInfos[0];
        Debugger::barDump($termChangeInfo);
        return $termChangeInfo->getTaxonomyName();
    }
}
<?php

namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Utils\StringUtils;

class BulkTranslationChangeInfo extends BulkChangeInfo {

    public function getChangeDescription() {
        $languages = array();
        /** @var TranslationChangeInfo $changeInfo */
        foreach ($this->changeInfos as $changeInfo) {
            if (!in_array($changeInfo->getLanguageCode(), $languages)) {
                $languages[] = $changeInfo->getLanguageCode();
            }
        }

        if (count($languages) === 1) {
            return $this->changeInfos[0]->getChangeDescription();
        }
        return Strings::capitalize(StringUtils::verbToPastTense($this->getAction())) . " $this->count translations";
    }
}

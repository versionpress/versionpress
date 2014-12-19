<?php
namespace VersionPress\ChangeInfos;

use NStrings;
use VersionPress\Git\CommitMessage;

/**
 * Term (categories, tags) changes.
 *
 * VP tags:
 *
 *     VP-Action: term/(create|edit|rename|delete)/VPID
 *     VP-Term-Name: Uncategorized
 *
 */
class TermChangeInfo extends EntityChangeInfo {

    const TERM_NAME_TAG = "VP-Term-Name";
    const TERM_OLD_NAME_TAG = "VP-Term-OldName";
    const TERM_TAXONOMY_TAG = "VP-Term-Taxonomy";

    /** @var string */
    private $termName;

    /** @var string */
    private $oldTermName;

    /** @var string */
    private $taxonomy;

    public function __construct($action, $entityId, $termName, $taxonomy, $oldTermName = null) {
        parent::__construct("term", $action, $entityId);
        $this->termName = $termName;
        $this->taxonomy = $taxonomy;
        $this->oldTermName = $oldTermName;
    }

    public function getChangeDescription() {
        $taxonomy = $this->formatTaxonomyName();

        switch ($this->getAction()) {
            case "create":
                return "New {$taxonomy} '{$this->termName}'";
            case "rename":
                return NStrings::firstUpper($taxonomy) . " '{$this->oldTermName}' renamed to '{$this->termName}'";
        }

        return "Edited {$taxonomy} '{$this->termName}'";
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list(, $action, $entityId) = explode("/", $actionTag, 3);
        $name = $tags[self::TERM_NAME_TAG];
        $oldName = isset($tags[self::TERM_OLD_NAME_TAG]) ? $tags[self::TERM_OLD_NAME_TAG] : null;
        $taxonomy = $tags[self::TERM_TAXONOMY_TAG];
        return new self($action, $entityId, $name, $taxonomy, $oldName);
    }

    public function getCustomTags() {
        $tags = array(
            self::TERM_NAME_TAG => $this->termName,
            self::TERM_TAXONOMY_TAG => $this->taxonomy,
        );

        if ($this->getAction() === "rename") {
            $tags[self::TERM_OLD_NAME_TAG] = $this->oldTermName;
        }

        return $tags;
    }

    private function formatTaxonomyName() {
        return str_replace("_", " ", $this->taxonomy);
    }

}

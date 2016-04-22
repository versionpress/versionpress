<?php
namespace VersionPress\ChangeInfos;

use VersionPress\Git\CommitMessage;

/**
 * Term taxonomy (categories, tags) changes.
 *
 * VP tags:
 *
 *     VP-Action: term-taxonomy/(create|edit|delete)/VPID
 *     VP-TermTaxonomy-Taxonomy: category|post_tag|...
 *     VP-Term-Name: Uncategorized
 *
 */
class TermTaxonomyChangeInfo extends EntityChangeInfo
{

    const TAXONOMY_TAG = "VP-TermTaxonomy-Taxonomy";
    const TERM_NAME_TAG = "VP-Term-Name";

    /** @var string */
    private $taxonomy;
    /** @var string */
    private $termName;

    public function __construct($action, $entityId, $taxonomy, $termName)
    {
        parent::__construct("term_taxonomy", $action, $entityId);
        $this->taxonomy = $taxonomy;
        $this->termName = $termName;
    }

    public function getChangeDescription()
    {
        $taxonomy = $this->getTaxonomyName();

        switch ($this->getAction()) {
            case "create":
                return "New {$taxonomy} '{$this->termName}'";
            case "delete":
                return "Deleted {$taxonomy} '{$this->termName}'";
        }

        return "Edited {$taxonomy} '{$this->termName}'";
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage)
    {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list(, $action, $entityId) = explode("/", $actionTag, 3);
        $termName = $tags[self::TERM_NAME_TAG];
        $taxonomy = $tags[self::TAXONOMY_TAG];
        return new self($action, $entityId, $taxonomy, $termName);
    }

    public function getCustomTags()
    {
        $tags = [
            self::TERM_NAME_TAG => $this->termName,
            self::TAXONOMY_TAG => $this->taxonomy,
        ];

        return $tags;
    }

    public function getTaxonomyName()
    {
        return str_replace("_", " ", $this->taxonomy);
    }

    public function getChangedFiles()
    {
        $changes = parent::getChangedFiles();
        $changes[] = [
            "type" => "all-storage-files",
            "entity" => "option"
        ]; // sometimes term change can affect option (e.g. deleting menu)
        return $changes;
    }
}

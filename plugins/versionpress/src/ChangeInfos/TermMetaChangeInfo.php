<?php
namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\Git\CommitMessage;
use VersionPress\Utils\StringUtils;

/**
 * Changes of term meta.
 *
 * VP tags:
 *
 *     VP-Action: termmeta/(create|edit|delete)/VPID
 *     VP-Term-Name: Some category
 *     VP-TermMeta-Key: some_meta
 *     VP-Term-Id: VPID
 *
 */
class TermMetaChangeInfo extends EntityChangeInfo {

    const TERM_NAME_TAG = "VP-Term-Name";
    const TERM_META_KEY = "VP-TermMeta-Key";
    const TERM_VPID_TAG = "VP-Term-Id";

    /** @var string */
    private $termName;

    /** @var string */
    private $termVpId;

    /** @var string */
    private $metaKey;

    public function __construct($action, $entityId, $termName, $termVpId, $metaKey) {
        parent::__construct("termmeta", $action, $entityId);
        $this->termName = $termName;
        $this->termVpId = $termVpId;
        $this->metaKey = $metaKey;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list(, $action, $entityId) = explode("/", $actionTag, 3);
        $termName = isset($tags[self::TERM_NAME_TAG]) ? $tags[self::TERM_NAME_TAG] : $entityId;
        $metaKey = $tags[self::TERM_META_KEY];
        $termVpid = $tags[self::TERM_VPID_TAG];
        return new self($action, $entityId, $termName, $termVpid, $metaKey);
    }

    public function getChangeDescription() {
        $verb = "Edited";
        $subject = "term-meta '{$this->metaKey}'";
        $rest = "for term '{$this->termName}'";

        if ($this->getAction() === "create" || $this->getAction() === "delete") {
            $verb = Strings::firstUpper(StringUtils::verbToPastTense($this->getAction()));
        }

        return sprintf("%s %s %s", $verb, $subject, $rest);
    }

    public function getCustomTags() {
        return array(
            self::TERM_NAME_TAG => $this->termName,
            self::TERM_META_KEY => $this->metaKey,
            self::TERM_VPID_TAG => $this->termVpId,
        );
    }

    public function getParentId() {
        return $this->termVpId;
    }
}

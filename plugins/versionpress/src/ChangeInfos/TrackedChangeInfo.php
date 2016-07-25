<?php
namespace VersionPress\ChangeInfos;

use VersionPress\Git\ActionsInfo;
use VersionPress\Git\CommitMessage;

/**
 * Base class for ChangeInfos that are properly tracked by VersionPress. They use
 * commit metadata in form of VP tags to which some useful information is persisted
 * and later read when the main VersionPress table is being rendered. At least
 * the VP-Action tag is always present, something like:
 *
 *     VP-Action: post/edit/VPID123
 *
 * Specific subclasses optionally add their own tags.
 *
 * @see CommitMessage::getVersionPressTags()
 * @see UntrackedChangeInfo Changes created outside of VersionPress.
 */
class TrackedChangeInfo implements ChangeInfo
{

    /** @var string */
    private $entityName;

    /** @var string */
    private $action;

    /** @var string */
    private $entityId;

    /** @var array */
    private $customTags;

    /** @var array */
    private $customFiles;

    /** @var ActionsInfo */
    private $actionsInfo;

    /** @var string */
    private $commitMessageSubject;

    /**
     * VP tag common to all tracked change infos. It is the only required tag for them.
     */
    const ACTION_TAG = "VP-Action";

    public function __construct($entityName, $actionsInfo, $action, $entityId, $customTags = [], $customFiles = [])
    {
        $this->entityName = $entityName;
        $this->actionsInfo = $actionsInfo;
        $this->action = $action;
        $this->entityId = $entityId;
        $this->customTags = $customTags;
        $this->customFiles = $customFiles;
    }

    public function getCommitMessage()
    {
        return new CommitMessage($this->getChangeDescription(), $this->getCommitMessageBody());
    }

    /**
     * Constructs commit message body, which is typically a couple of lines of VP tags.
     *
     * General algorithm is defined in this base class and the subclasses
     * only need to provide content for VP-Action tag and an array of custom VP tags.
     *
     * @see getActionTagValue()
     * @see getCustomTags()
     *
     * @return string
     */
    private function getCommitMessageBody()
    {
        $actionTag = $this->getActionTagValue();

        $tags = [];
        if ($actionTag) {
            $tags[self::ACTION_TAG] = $actionTag;
        }

        $customTags = $this->getCustomTags();
        $tags = array_merge($tags, $customTags);

        $body = "";
        foreach ($tags as $tagName => $tagValue) {
            $body .= "$tagName: $tagValue\n";
        }
        return $body;
    }

    /**
     * Text displayed in the main VersionPress table (see admin/index.php). Also used
     * to construct commit message subject (first line) when the commit is first
     * physically created.
     *
     * @return string
     */
    public function getChangeDescription()
    {
        if (empty($this->commitMessageSubject)) {
            $this->commitMessageSubject = $this->actionsInfo->createCommitMessage($this->getEntityName(), $this->getAction(), $this->getEntityId(), $this->getCustomTags());
        }

        return $this->commitMessageSubject;
    }

    /**
     * Object type, the first part of the VP-Action tag value.
     *
     * For example, when objectType is "post", the VP-Action tag will be something like "post/edit/VPID123".
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * The action done on the object type, for instance "install" or "activate" if the object was a plugin.
     * Action is always part of VP-Action tag as the second segment.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Used to construct a commit message body, subclasses provide a string for the VP-Action tag value
     * using this method.
     *
     * @see getCommitMessageBody()
     * @return string
     */
    protected function getActionTagValue()
    {
        $actionTagValue = "{$this->getEntityName()}/{$this->getAction()}";

        if ($this->entityId) {
            $actionTagValue .= "/{$this->getEntityId()}";
        }

        return $actionTagValue;
    }

    /**
     * Used to construct a commit message body, subclasses provide an array of VP tags
     * using this method. If they don't need custom tags, they return an empty array.
     *
     * @see getCommitMessageBody()
     * @return array
     */
    public function getCustomTags()
    {
        return $this->customTags;
    }

    /**
     * Reports changes in files that relate to this ChangeInfo. Used by {@see Committer::stageRelatedFiles()}.
     *
     * Path specifications are either pointers to storage files based on entity name and VPID
     * or a concrete path (optionally with wildcards).
     *
     * An example:
     *
     *     array(
     *         array("type" => "storage-file", "entity" => "post", "id" => VPID, "parent-id" => null),
     *         array("type" => "storage-file", "entity" => "usermeta", "id" => VPID, "parent-id" => user-VPID),
     *         array("type" => "path", "path" => "c:/wp/example.txt"),
     *         array("type" => "path", "path" => "c:/wp/folder/*")
     *     );
     *
     * @return array
     */
    public function getChangedFiles()
    {
        return $this->customFiles;
    }
}

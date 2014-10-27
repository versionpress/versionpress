<?php

/**
 * Base class for all ChangeInfo objects representing commits tracked
 * by VersionPress. One common thing about them is that they build
 * on top of metadata stored in VP tags inside of commit messages
 * (all of them have at least the `VP-Action` tag).
 *
 * See also UntrackedChangeInfo which represents a commit that was created
 * outside of VersionPress.
 *
 * @see CommitMessage::getVersionPressTags()
 * @see UntrackedChangeInfo
 */
abstract class TrackedChangeInfo implements ChangeInfo {

    const ACTION_TAG = "VP-Action";

    /**
     * Object type part of the VP-Action tag value. The `matchesCommitMessage()` method
     * usually uses this to match ChangeInfo type to a raw commit message.
     *
     * For example, if the VP-Action tag has the value of "post/edit/VPID123", the
     * objectType is "post".
     *
     * @return string
     */
    abstract function getObjectType();

    /**
     * The action part of the VP-Action tag value. Identifies what happened with
     * the object type, for instance, a plugin (object type) may have been
     * updated, installed, deactivated etc.
     *
     * @return string
     */
    abstract function getAction();

    /**
     * @inheritdoc
     *
     * @return CommitMessage
     */
    public function getCommitMessage() {
        return new CommitMessage($this->getChangeDescription(), $this->constructCommitMessageBody());
    }


    /**
     * Constructs commit message body, which is typically a couple of lines
     * with VP tags.
     *
     * General algorithm is defined in this base class implementation and the subclasses
     * only need to provide content for VP-Action tag and an array of custom VP tags.
     *
     * @see constructActionTagValue()
     * @see getCustomTags()
     *
     * @return string
     */
    private function constructCommitMessageBody() {
        $actionTag = $this->constructActionTagValue();

        $tags = array();
        if($actionTag) {
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
     * Constructs string for the VP-Action tag value based on the ChangeInfo properties.
     * Used to construct the commit message body from this ChangeInfo object.
     *
     * @see constructCommitMessageBody()
     *
     * @return string
     */
    abstract protected function constructActionTagValue();

    /**
     * Subclasses return an associative array of addition VP tags they wish
     * to store to a commit message body. By default, there are no additional
     * VP tags (an empty array).
     *
     * @return array
     */
    protected function getCustomTags() {
        return array();
    }
}
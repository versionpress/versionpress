<?php

abstract class BaseChangeInfo implements ChangeInfo {

    const ACTION_TAG = "VP-Action";

    /**
     * @return string
     */
    abstract function getObjectType();

    /**
     * @return string
     */
    abstract function getAction();

    /**
     * @return CommitMessage
     */
    public function getCommitMessage() {
        return new CommitMessage($this->getCommitMessageHead(), $this->getCommitMessageBody());
    }

    /**
     * Returns the first line of commit message
     *
     * @return string
     */
    abstract protected function getCommitMessageHead();

    /**
     * Returns formated tags
     *
     * @return string
     */
    protected function getCommitMessageBody() {
        $actionTag = $this->getActionTag();

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
     * Returns the content of VP-Action tag
     *
     * @return string
     */
    abstract protected function getActionTag();

    /**
     * @return array
     */
    protected function getCustomTags() {
        return array();
    }
}
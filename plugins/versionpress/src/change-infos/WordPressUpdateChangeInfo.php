<?php

class WordPressUpdateChangeInfo implements ChangeInfo {

    /** @var  string */
    private $version;

    public function __construct($version) {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getObjectType() {
        return 'wordpress';
    }

    /**
     * @return string
     */
    public function getAction() {
        return 'update';
    }

    /**
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * @return CommitMessage
     */
    function getCommitMessage() {
        $messageHead = 'WordPress updated to version ' . $this->version;
        $messageBody = 'VP-Action: wordpress/update/' . $this->version;
        return new CommitMessage($messageHead, $messageBody);
    }
}
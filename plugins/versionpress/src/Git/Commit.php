<?php

namespace VersionPress\Git;
use DateTime;

class Commit {

    /** @var string */
    private $hash;

    /** @var DateTime */
    private $date;

    /** @var string */
    private $relativeDate;

    /** @var string */
    private $authorName;

    /** @var string */
    private $authorEmail;

    /** @var CommitMessage */
    private $message;


    /**
     * Creates instance from string matching pattern:
     * <hash><div><date><div><relative-date><div><author-name><div><author-email><div><message-head><div><message-body>
     * where <div> is record separator character (ascii ordinary number 30)
     * @param $rawCommit string
     * @return Commit
     */
    public static function buildFromString($rawCommit) {
        list($hash, $date, $relativeDate, $authorName, $authorEmail, $messageHead, $messageBody) = explode(chr(30), $rawCommit);
        $commit = new Commit();
        $commit->hash = $hash;
        $commit->date = new DateTime($date);
        $commit->relativeDate = $relativeDate;
        $commit->authorName = $authorName;
        $commit->authorEmail = $authorEmail;
        $commit->message = new CommitMessage($messageHead, $messageBody);
        return $commit;
    }

    /**
     * @return string
     */
    public function getHash() {
        return $this->hash;
    }

    /**
     * Short hash - first 7 characters.
     *
     * @return string
     */
    public function getShortHash() {
        return substr($this->hash, 0, 7);
    }

    /**
     * @return DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getRelativeDate() {
        return $this->relativeDate;
    }

    /**
     * @return string
     */
    public function getAuthorName() {
        return $this->authorName;
    }

    /**
     * @return string
     */
    public function getAuthorEmail() {
        return $this->authorEmail;
    }

    /**
     * @return CommitMessage
     */
    public function getMessage() {
        return $this->message;
    }
}

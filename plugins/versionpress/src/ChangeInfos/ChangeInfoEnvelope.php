<?php
namespace VersionPress\ChangeInfos;

use ChangeInfos\Sorting\NaiveSortingStrategy;
use ChangeInfos\Sorting\SortingStrategy;
use Nette\Utils\Strings;
use VersionPress\Git\CommitMessage;
use VersionPress\VersionPress;

/**
 * Class representing more changes in one commit
 */
class ChangeInfoEnvelope implements ChangeInfo {

    /**
     * VP meta tag that says the version of VersionPress in which was the commit made.
     * It's parsed into {@link version} field by the {@link buildFromCommitMessage} method.
     */
    const VP_VERSION_TAG = "X-VP-Version";

    /** @var TrackedChangeInfo[] */
    private $changeInfoList;

    private $version;

    /** @var SortingStrategy */
    private $sortingStrategy;

    /**
     * @param TrackedChangeInfo[] $changeInfoList
     * @param string|null $version
     * @param SortingStrategy $sortingStrategy
     */
    public function __construct($changeInfoList, $version = null, $sortingStrategy = null) {
        $this->changeInfoList = $changeInfoList;
        $this->version = $version === null ? VersionPress::getVersion() : $version;
        $this->sortingStrategy = $sortingStrategy === null ? new NaiveSortingStrategy() : $sortingStrategy;
    }

    /**
     * Creates a commit message from this ChangeInfo. Used by Committer.
     *
     * @see Committer::commit()
     * @return CommitMessage
     */
    public function getCommitMessage() {
        $subject = $this->getChangeDescription();

        $bodies = array();
        foreach ($this->getSortedChangeInfoList() as $changeInfo) {
            $bodies[] = $changeInfo->getCommitMessage()->getBody();
        }

        $body = join("\n\n", $bodies);
        $body .= sprintf("\n\n%s: %s", self::VP_VERSION_TAG, $this->version);

        return new CommitMessage($subject, $body);
    }

    /**
     * Text displayed in the main VersionPress table (see admin/index.php). Also used
     * to construct commit message subject (first line) when the commit is first
     * physically created.
     *
     * @return string
     */
    public function getChangeDescription() {
        $changeList = $this->getSortedChangeInfoList();
        $firstChangeDescription = $changeList[0]->getChangeDescription();
        return $firstChangeDescription;
    }

    /**
     * Factory method - builds a ChangeInfo object from a commit message. Used when VersionPress
     * table is constructed; hooks use the normal constructor.
     *
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $fullBody = $commitMessage->getBody();
        $splittedBodies = explode("\n\n", $fullBody);
        $lastBody = $splittedBodies[count($splittedBodies) - 1];
        $changeInfoList = array();
        $version = null;

        if (self::containsVersion($lastBody)) {
            $version = self::extractVersion($lastBody);
            array_pop($splittedBodies);
        }

        foreach ($splittedBodies as $body) {
            $partialCommitMessage = new CommitMessage("", $body);
            /** @var ChangeInfo $matchingChangeInfoType */
            $matchingChangeInfoType = ChangeInfoMatcher::findMatchingChangeInfo($partialCommitMessage);
            $changeInfoList[] = $matchingChangeInfoType::buildFromCommitMessage($partialCommitMessage);
        }

        return new self($changeInfoList, $version);
    }

    /**
     * Returns all ChangeInfo objects encapsulated in ChangeInfoEnvelope.
     *
     * @return TrackedChangeInfo[]
     */
    public function getChangeInfoList() {
        return $this->changeInfoList;
    }

    /**
     * @return TrackedChangeInfo[]
     */
    public function getSortedChangeInfoList() {
        return $this->sortingStrategy->sort($this->changeInfoList);
    }

    private static function containsVersion($lastBody) {
        return Strings::startsWith($lastBody, self::VP_VERSION_TAG);
    }

    private static function extractVersion($lastBody) {
        $tmpMessage = new CommitMessage("", $lastBody);
        $version = $tmpMessage->getVersionPressTag(self::VP_VERSION_TAG);
        return $version;
    }
}

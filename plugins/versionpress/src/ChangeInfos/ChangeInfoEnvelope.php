<?php
namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\Sorting\SortingStrategy;
use VersionPress\Git\CommitMessage;
use VersionPress\Utils\ArrayUtils;
use VersionPress\VersionPress;

/**
 * Class representing more changes in one commit
 */
class ChangeInfoEnvelope implements ChangeInfo
{

    /**
     * VP meta tag that says the version of VersionPress in which was the commit made.
     * It's parsed into {@link version} field by the {@link buildFromCommitMessage} method.
     */
    const VP_VERSION_TAG = "X-VP-Version";

    /**
     * VP meta tag that says the name of current environment. For clone it's the name of clone,
     * for original site it's master. It's based on constant {@link VP_ENVIRONMENT}.
     */
    const VP_ENVIRONMENT_TAG = "X-VP-Environment";

    /** @var TrackedChangeInfo[] */
    private $changeInfoList;

    private $version;

    /** @var SortingStrategy */
    private $sortingStrategy;

    private $bulkChangeInfoClasses = [
        "comment" => 'VersionPress\ChangeInfos\BulkCommentChangeInfo',
        "option" => 'VersionPress\ChangeInfos\BulkOptionChangeInfo',
        "plugin" => 'VersionPress\ChangeInfos\BulkPluginChangeInfo',
        "post" => 'VersionPress\ChangeInfos\BulkPostChangeInfo',
        "postmeta" => 'VersionPress\ChangeInfos\BulkPostMetaChangeInfo',
        "term" => 'VersionPress\ChangeInfos\BulkTermChangeInfo',
        "termmeta" => 'VersionPress\ChangeInfos\BulkTermMetaChangeInfo',
        "theme" => 'VersionPress\ChangeInfos\BulkThemeChangeInfo',
        "translation" => 'VersionPress\ChangeInfos\BulkTranslationChangeInfo',
        "user" => 'VersionPress\ChangeInfos\BulkUserChangeInfo',
        "usermeta" => 'VersionPress\ChangeInfos\BulkUserMetaChangeInfo',
        "versionpress" => 'VersionPress\ChangeInfos\BulkRevertChangeInfo',
    ];

    /**
     * @var null|string
     */
    private $environment;

    /**
     * @param TrackedChangeInfo[] $changeInfoList
     * @param string|null $version
     * @param string|null $environment
     * @param SortingStrategy $sortingStrategy
     */
    public function __construct($changeInfoList, $version = null, $environment = null, $sortingStrategy = null)
    {
        $this->changeInfoList = $changeInfoList;
        $this->version = $version === null ? VersionPress::getVersion() : $version;
        $this->environment = $environment ?: VersionPress::getEnvironment();
        $this->sortingStrategy = $sortingStrategy === null ? new SortingStrategy() : $sortingStrategy;
    }

    /**
     * Creates a commit message from this ChangeInfo. Used by Committer.
     *
     * @see Committer::commit()
     * @return CommitMessage
     */
    public function getCommitMessage()
    {
        $subject = $this->getChangeDescription();

        $bodies = [];
        foreach ($this->getSortedChangeInfoList() as $changeInfo) {
            $bodies[] = $changeInfo->getCommitMessage()->getBody();
        }

        $body = join("\n\n", $bodies);
        $body .= sprintf("\n\n%s: %s", self::VP_VERSION_TAG, $this->version);
        $body .= sprintf("\n%s: %s", self::VP_ENVIRONMENT_TAG, $this->environment);

        return new CommitMessage($subject, $body);
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
        $changeList = $this->getReorganizedInfoList();
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
    public static function buildFromCommitMessage(CommitMessage $commitMessage)
    {
        $fullBody = $commitMessage->getBody();
        $splittedBodies = explode("\n\n", $fullBody);
        $lastBody = $splittedBodies[count($splittedBodies) - 1];
        $changeInfoList = [];
        $version = null;
        $environment = null;

        if (self::containsVersion($lastBody)) {
            $version = self::extractTag(self::VP_VERSION_TAG, $lastBody);
            $environment = self::extractTag(self::VP_ENVIRONMENT_TAG, $lastBody);
            array_pop($splittedBodies);
        }

        if (count($splittedBodies) === 0 || $lastBody === "") {
            $changeInfoList[] = UntrackedChangeInfo::buildFromCommitMessage($commitMessage);
        }

        foreach ($splittedBodies as $body) {
            $partialCommitMessage = new CommitMessage("", $body);
            /** @var ChangeInfo $matchingChangeInfoType */
            $matchingChangeInfoType = ChangeInfoMatcher::findMatchingChangeInfo($partialCommitMessage);
            $changeInfoList[] = $matchingChangeInfoType::buildFromCommitMessage($partialCommitMessage);
        }

        return new self($changeInfoList, $version, $environment);
    }

    /**
     * Returns all ChangeInfo objects encapsulated in ChangeInfoEnvelope.
     *
     * @return TrackedChangeInfo[]
     */
    public function getChangeInfoList()
    {
        return $this->changeInfoList;
    }

    /**
     * Returns sorted list of ChangeInfo objects with bulk actions encapsulated into BulkChangeInfo objects.
     *
     * @return TrackedChangeInfo[]
     */
    public function getReorganizedInfoList()
    {
        return $this->sortingStrategy->sort($this->groupBulkActions($this->changeInfoList));
    }

    /**
     * @return null|string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return TrackedChangeInfo[]
     */
    private function getSortedChangeInfoList()
    {
        return $this->sortingStrategy->sort($this->changeInfoList);
    }

    private static function containsVersion($lastBody)
    {
        return Strings::startsWith($lastBody, self::VP_VERSION_TAG);
    }

    private static function extractTag($tag, $lastBody)
    {
        $tmpMessage = new CommitMessage("", $lastBody);
        return $tmpMessage->getVersionPressTag($tag);
    }

    private function groupBulkActions($changeInfoList)
    {
        $bulkChangeInfoClasses = $this->bulkChangeInfoClasses;

        $groupedChangeInfos = ArrayUtils::mapreduce($changeInfoList, function (ChangeInfo $item, $mapEmit) {
            if ($item instanceof TrackedChangeInfo) {
                $key = "{$item->getEntityName()}/{$item->getAction()}";
                $mapEmit($key, $item);
            } else {
                $mapEmit(spl_object_hash($item), $item);
            }
        }, function ($key, $items, $reduceEmit) use ($bulkChangeInfoClasses) {
            /** @var TrackedChangeInfo[] $items */
            if (count($items) > 1) {
                $entityName = $items[0]->getEntityName();
                if (isset($bulkChangeInfoClasses[$entityName])) {
                    $reduceEmit(new $bulkChangeInfoClasses[$entityName]($items));
                } else {
                    $reduceEmit($items);
                }
            } else {
                $reduceEmit($items[0]);
            }
        });

        $changeInfos = [];
        foreach ($groupedChangeInfos as $changeInfoGroup) {
            if (is_array($changeInfoGroup)) {
                foreach ($changeInfoGroup as $changeInfo) {
                    $changeInfos[] = $changeInfo;
                }
            } else {
                $changeInfos[] = $changeInfoGroup;
            }
        }

        return $changeInfos;
    }
}

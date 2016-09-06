<?php
namespace VersionPress\ChangeInfos;

use Nette\Utils\Strings;
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
     * It's parsed into {@link version} field.
     */
    const VP_VERSION_TAG = "X-VP-Version";

    /**
     * VP meta tag that says the name of current environment. For clone it's the name of clone,
     * for original site it's 'default'. It's based on constant {@link VP_ENVIRONMENT}.
     */
    const VP_ENVIRONMENT_TAG = "X-VP-Environment";

    /** @var TrackedChangeInfo[] */
    private $changeInfoList;

    private $version;

    /**
     * @var null|string
     */
    private $environment;

    /**
     * @param TrackedChangeInfo[] $changeInfoList
     * @param string|null $version
     * @param string|null $environment
     */
    public function __construct($changeInfoList, $version = null, $environment = null)
    {
        $this->changeInfoList = $changeInfoList;
        $this->version = $version === null ? VersionPress::getVersion() : $version;
        $this->environment = $environment ?: VersionPress::getEnvironment();
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
        return $this->sortChangeInfoList($this->groupBulkActions($this->changeInfoList));
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
        return $this->sortChangeInfoList($this->changeInfoList);
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

    /**
     * @param TrackedChangeInfo[] $changeInfoList
     * @return TrackedChangeInfo[]
     */
    private function sortChangeInfoList($changeInfoList)
    {
        ArrayUtils::stablesort($changeInfoList, function ($changeInfo1, $changeInfo2) {
            /** @var TrackedChangeInfo|BulkChangeInfo $changeInfo1 */
            /** @var TrackedChangeInfo|BulkChangeInfo $changeInfo2 */

            return  $changeInfo1->getPriority() -  $changeInfo2->getPriority();
        });

        return $changeInfoList;
    }

    private function groupBulkActions($changeInfoList)
    {
        $groupedChangeInfos = ArrayUtils::mapreduce($changeInfoList, function (ChangeInfo $item, $mapEmit) {
            if ($item instanceof TrackedChangeInfo) {
                $key = "{$item->getScope()}/{$item->getAction()}";
                $mapEmit($key, $item);
            } else {
                $mapEmit(spl_object_hash($item), $item);
            }
        }, function ($key, $items, $reduceEmit) {
            /** @var TrackedChangeInfo[] $items */
            if (count($items) > 1) {
                $reduceEmit(new BulkChangeInfo($items));
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

    public function getPriority()
    {
        // There are never envelopes inside an envelope; therefore, the priority is irrelevant.
        return 0;
    }
}

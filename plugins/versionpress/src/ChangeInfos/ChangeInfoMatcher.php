<?php

namespace VersionPress\ChangeInfos;

use Exception;
use VersionPress\Git\CommitMessage;

class ChangeInfoMatcher {

    /**
     * Map between partial regex describing the format of the VP-Action tag
     * and a ChangeInfo class. The full PCRE regex will be constructed from the string
     * by adding "^" and "$" so the values below have to match the whole VP-Action value.
     *
     * @var array
     */
    private static $changeInfoMap = array(

        // VersionPress actions:
        "versionpress/(?!(undo|rollback)).*" => 'VersionPress\ChangeInfos\VersionPressChangeInfo',
        "versionpress/(undo|rollback)/.*" => 'VersionPress\ChangeInfos\RevertChangeInfo',

        // WordPress core actions:
        "translation/.*" => 'VersionPress\ChangeInfos\TranslationChangeInfo',
        "plugin/.*" => 'VersionPress\ChangeInfos\PluginChangeInfo',
        "theme/.*" => 'VersionPress\ChangeInfos\ThemeChangeInfo',
        "wordpress/update/.*" => 'VersionPress\ChangeInfos\WordPressUpdateChangeInfo',

        // Actions on entities:
        "post/.*" => 'VersionPress\ChangeInfos\PostChangeInfo',
        "postmeta/.*" => 'VersionPress\ChangeInfos\PostMetaChangeInfo',
        "comment/.*" => 'VersionPress\ChangeInfos\CommentChangeInfo',
        "option/.*" => 'VersionPress\ChangeInfos\OptionChangeInfo',
        "term/.*" => 'VersionPress\ChangeInfos\TermChangeInfo',
        "termmeta/.*" => 'VersionPress\ChangeInfos\TermMetaChangeInfo',
        "usermeta/.*" => 'VersionPress\ChangeInfos\UserMetaChangeInfo',
        "user/.*" => 'VersionPress\ChangeInfos\UserChangeInfo',

        // Unknown action:
        "" => 'VersionPress\ChangeInfos\UntrackedChangeInfo',

    );

    /**
     * For a given commit message, creates ChangeInfoEnvelope containing all ChangeInfo.
     *
     * @param CommitMessage $commitMessage
     * @return ChangeInfoEnvelope|UntrackedChangeInfo
     */
    public static function buildChangeInfo(CommitMessage $commitMessage) {
        if (self::findMatchingChangeInfo($commitMessage) === 'VersionPress\ChangeInfos\UntrackedChangeInfo') {
            return UntrackedChangeInfo::buildFromCommitMessage($commitMessage);
        }

        return ChangeInfoEnvelope::buildFromCommitMessage($commitMessage);
    }

    /**
     * Returns matching ChangeInfo type for a given commit message. Matching is done based on the value of the VP-Action tag.
     *
     * @param CommitMessage $commitMessage
     * @throws Exception When no matching ChangeInfo type is found (should never happen)
     * @return string "Class" of the matching ChangeInfo object
     */
    public static function findMatchingChangeInfo(CommitMessage $commitMessage) {

        if (substr_count($commitMessage->getBody(), TrackedChangeInfo::ACTION_TAG) > 1) {
            return "VersionPress\ChangeInfos\ChangeInfoEnvelope";
        }

        $actionTagValue = $commitMessage->getVersionPressTag(TrackedChangeInfo::ACTION_TAG); // can be empty string which is not a problem

        foreach (self::$changeInfoMap as $actionTagExpression => $changeInfoType) {
            $regex = "~^" . $actionTagExpression . "$~";
            if (preg_match($regex, $actionTagValue)) {
                return $changeInfoType;
            }
        }

        // Code execution should never reach this point, at least the 'UntrackedChangeInfo' should match
        throw new Exception("Matching ChangeInfo type not found");
    }

    /**
     * Return true if the given $commitMesssage matches the $changeInfoClass
     *
     * @param CommitMessage $commitMessage
     * @param string $changeInfoClass
     * @return bool
     */
    public static function matchesChangeInfo(CommitMessage $commitMessage, $changeInfoClass) {
        return self::findMatchingChangeInfo($commitMessage) == $changeInfoClass;
    }

}

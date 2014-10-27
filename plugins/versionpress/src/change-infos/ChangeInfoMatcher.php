<?php

class ChangeInfoMatcher {

    /**
     * For a given commit message, finds the matching ChangeInfo class and creates
     * an instance of it.
     *
     * Matching is done based on the value of the VP-Action tag.
     *
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    public static function createMatchingChangeInfo(CommitMessage $commitMessage) {

        // Map between partial regex describing the format of the VP-Action tag
        // and a ChangeInfo class. The full PCRE regex will be constructed from the string
        // by adding "^" and "$" so the values below have to match the whole VP-Action value.

        $map = array(

            // VersionPress actions:
            "versionpress/(?!(undo|rollback)).*" => 'VersionPressChangeInfo',
            "versionpress/(undo|rollback)/.*" => 'RevertChangeInfo',

            // WordPress core actions:
            "plugin/.*" => 'PluginChangeInfo',
            "theme/.*" => 'ThemeChangeInfo',
            "wordpress/update/.*" => 'WordPressUpdateChangeInfo',

            // Actions on entities:
            "post/.*" => 'PostChangeInfo',
            "comment/.*" => 'CommentChangeInfo',
            "option/.*" => 'OptionChangeInfo',
            "term/.*" => 'TermChangeInfo',
            "usermeta/.*" => 'UserMetaChangeInfo',
            "user/.*" => 'UserChangeInfo',

        );

        $changeInfoTypeToCreate = 'UntrackedChangeInfo'; // fallback

        $actionTagValue = $commitMessage->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
        if ($actionTagValue != '') {
            foreach ($map as $expr => $changeInfoType) {
                $regex = "~^" . $expr . "$~";
                if (preg_match($regex, $actionTagValue)) {
                    $changeInfoTypeToCreate = $changeInfoType;
                }
            }
        }

        return $changeInfoTypeToCreate::buildFromCommitMessage($commitMessage);

    }

}
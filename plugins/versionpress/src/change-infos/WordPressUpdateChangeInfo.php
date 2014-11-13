<?php

/**
 * Change info about updating WordPress itself.
 *
 * Nitpicker's corner: the word "update" in the class name is better than upgrade,
 * see the frequency (and the title) of the {@link http://codex.wordpress.org/Updating_WordPress Updating WordPress}
 * topic in Codex.
 *
 * VP tags:
 *
 *     VP-Action: wordpress/update/4.0
 *
 * No custom tags needed.
 */
class WordPressUpdateChangeInfo extends TrackedChangeInfo {

    const OBJECT_TYPE = "wordpress";
    const ACTION = "update";

    /** @var string */
    private $newVersion;

    public function __construct($version) {
        $this->newVersion = $version;
    }

    public function getObjectType() {
        return self::OBJECT_TYPE;
    }

    public function getAction() {
        return self::ACTION;
    }

    public function getNewVersion() {
        return $this->newVersion;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage) {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list( , , $version) = explode("/", $actionTag, 3);
        return new self($version);
    }

    function getChangeDescription() {
        return "WordPress updated to version " . $this->getNewVersion();
    }

    protected function getActionTagValue() {
        return "{$this->getObjectType()}/{$this->getAction()}/{$this->getNewVersion()}";
    }

    protected function getCustomTags() {
        return array();
    }


    /**
     * Reports changes in files that relate to given ChangeInfo. Used in Committer
     * to commit only related files.
     * Returns data in this format:
     *
     * add  =>   [
     *             [ type => "storage-file",
     *               entity => "post",
     *               id => <VPID> ],
     *             [ type => "path",
     *               path => C:/www/wp/wp-content/upload/* ],
     *           ],
     * delete => [
     *             [ type => "storage-file",
     *               entity => "user",
     *               id => <VPID> ],
     *             ...
     *           ]
     *
     * @return array
     */
    public function getChangedFiles() {
        return array("add" => array(array("type" => "path", "path" => "*")));
    }
}
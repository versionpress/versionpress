<?php
namespace VersionPress\ChangeInfos;

use VersionPress\Git\CommitMessage;

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

    /**
     * @param string $version WordPress version that was udated to
     */
    public function __construct($version) {
        $this->newVersion = $version;
    }

    public function getEntityName() {
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
        list(, , $version) = explode("/", $actionTag, 3);
        return new self($version);
    }

    function getChangeDescription() {
        return "WordPress updated to version " . $this->getNewVersion();
    }

    protected function getActionTagValue() {
        return "{$this->getEntityName()}/{$this->getAction()}/{$this->getNewVersion()}";
    }

    public function getCustomTags() {
        return array();
    }

    public function getChangedFiles() {
        $adminPath = str_replace( get_bloginfo( 'url' ) . '/', ABSPATH, get_admin_url() ); // https://gist.github.com/andrezrv/f7a083627f3bd89c3d9e
        return array(
            array("type" => "path", "path" => "index.php"),
            array("type" => "path", "path" => "license.txt"),
            array("type" => "path", "path" => "readme.html"),
            array("type" => "path", "path" => "wp-activate.php"),
            array("type" => "path", "path" => "wp-blog-header.php"),
            array("type" => "path", "path" => "wp-comments-post.php"),
            array("type" => "path", "path" => "wp-config-sample.php"),
            array("type" => "path", "path" => "wp-cron.php"),
            array("type" => "path", "path" => "wp-links-opml.php"),
            array("type" => "path", "path" => "wp-load.php"),
            array("type" => "path", "path" => "wp-login.php"),
            array("type" => "path", "path" => "wp-mail.php"),
            array("type" => "path", "path" => "wp-settings.php"),
            array("type" => "path", "path" => "wp-signup.php"),
            array("type" => "path", "path" => "wp-trackback.php"),
            array("type" => "path", "path" => "xmlrpc.php"),
            array("type" => "path", "path" => ABSPATH . WPINC . '/*'),
            array("type" => "path", "path" => $adminPath . '*'),
            array("type" => "path", "path" => WP_CONTENT_DIR . '/themes/twenty*'),
        );
    }
}

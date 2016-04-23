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
class WordPressUpdateChangeInfo extends TrackedChangeInfo
{

    const OBJECT_TYPE = "wordpress";
    const ACTION = "update";

    /** @var string */
    private $newVersion;

    /**
     * @param string $version WordPress version that was udated to
     */
    public function __construct($version)
    {
        $this->newVersion = $version;
    }

    public function getEntityName()
    {
        return self::OBJECT_TYPE;
    }

    public function getAction()
    {
        return self::ACTION;
    }

    public function getNewVersion()
    {
        return $this->newVersion;
    }

    public static function buildFromCommitMessage(CommitMessage $commitMessage)
    {
        $tags = $commitMessage->getVersionPressTags();
        $actionTag = $tags[TrackedChangeInfo::ACTION_TAG];
        list(, , $version) = explode("/", $actionTag, 3);
        return new self($version);
    }

    public function getChangeDescription()
    {
        return "WordPress updated to version " . $this->getNewVersion();
    }

    protected function getActionTagValue()
    {
        return "{$this->getEntityName()}/{$this->getAction()}/{$this->getNewVersion()}";
    }

    public function getCustomTags()
    {
        return [];
    }

    public function getChangedFiles()
    {

        return [
            // All files from WP root
            // Git can't add only files from current directory (non-recursively), so we have to add them manually.
            // It should be OK because the list of files didn't change since at least Jan 2013.
            ["type" => "path", "path" => "index.php"],
            ["type" => "path", "path" => "license.txt"],
            ["type" => "path", "path" => "readme.html"],
            ["type" => "path", "path" => "wp-activate.php"],
            ["type" => "path", "path" => "wp-blog-header.php"],
            ["type" => "path", "path" => "wp-comments-post.php"],
            ["type" => "path", "path" => "wp-config-sample.php"],
            ["type" => "path", "path" => "wp-cron.php"],
            ["type" => "path", "path" => "wp-links-opml.php"],
            ["type" => "path", "path" => "wp-load.php"],
            ["type" => "path", "path" => "wp-login.php"],
            ["type" => "path", "path" => "wp-mail.php"],
            ["type" => "path", "path" => "wp-settings.php"],
            ["type" => "path", "path" => "wp-signup.php"],
            ["type" => "path", "path" => "wp-trackback.php"],
            ["type" => "path", "path" => "xmlrpc.php"],

            // wp-includes and wp-admin directories
            ["type" => "path", "path" => ABSPATH . WPINC . '/*'],
            ["type" => "path", "path" => ABSPATH . 'wp-admin/*'],

            // WP themes - we bet that all WP themes begin with "twenty"
            ["type" => "path", "path" => WP_CONTENT_DIR . '/themes/twenty*'],

            // Translations
            ["type" => "path", "path" => WP_CONTENT_DIR . '/languages/*'],
        ];
    }
}

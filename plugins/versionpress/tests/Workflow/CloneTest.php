<?php

namespace VersionPress\Tests\Workflow;

use PHPUnit_Framework_TestCase;
use VersionPress\Cli\VPCommandUtils;
use VersionPress\Database\VpidRepository;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\SiteConfig;
use VersionPress\Tests\Utils\TestConfig;

class CloneTest extends PHPUnit_Framework_TestCase {

    private static $cloneSiteName;

    /** @var TestConfig */
    private static $testConfig;
    /** @var SiteConfig */
    private static $siteConfig;
    /** @var SiteConfig */
    private static $cloneSiteConfig;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$testConfig = TestConfig::createDefaultConfig();
        self::$siteConfig = self::$testConfig->testSite;

        self::$cloneSiteName = self::$siteConfig->name . 'clone';
        self::$cloneSiteConfig = self::changeSite(self::$siteConfig, self::$cloneSiteName);


    }

    /**
     * @test
     */
    public function cloneLooksExactlySameAsOriginal() {
        $this->prepareSiteWithClone();
        $this->assertCloneLooksExactlySameAsOriginal();
    }

    /**
     * @test
     * @depends cloneLooksExactlySameAsOriginal
     */
    public function updatedCloneCanBeMergedBack() {
        $cloneWpAutomation = new WpAutomation(self::$cloneSiteConfig, self::$testConfig->wpCliVersion);
        $cloneWpAutomation->editOption('blogname', 'Blogname from clone');

        $wpAutomation = new WpAutomation(self::$siteConfig, self::$testConfig->wpCliVersion);
        $wpAutomation->runWpCliCommand('vp', 'pull', array('from' => self::$cloneSiteName));

        $this->assertCloneLooksExactlySameAsOriginal();
    }

    /**
     * @test
     * @depends cloneLooksExactlySameAsOriginal
     */
    public function updatedSiteCanBePushedToClone() {
        $wpAutomation = new WpAutomation(self::$siteConfig, self::$testConfig->wpCliVersion);
        $wpAutomation->editOption('blogname', 'Blogname from original');

        $wpAutomation->runWpCliCommand('vp', 'push', array('to' => self::$cloneSiteName));

        $this->assertCloneLooksExactlySameAsOriginal();
    }

    /**
     * @test
     */
    public function updatedArticleCanBeMergedFromClone() {
        $cloneSiteConfig = self::$cloneSiteConfig;

        $wpAutomation = new WpAutomation(self::$siteConfig, self::$testConfig->wpCliVersion);

        $this->prepareSite($wpAutomation);
        $post = array(
            "post_type" => "page",
            "post_status" => "publish",
            "post_title" => "Test page for menu",
            "post_date" => "2011-11-11 11:11:11",
            "post_content" => "Test page",
            "post_author" => 1
        );
        $postId = $wpAutomation->createPost($post);
        $process = VPCommandUtils::runWpCliCommand('vp-internal', 'get-entity-vpid', array('require' => $this->getVPInternalCommandPath(), 'id' => $postId, 'name' => 'posts'), self::$testConfig->testSite->path);
        $postVpId = $process->getConsoleOutput();
        $wpAutomation->runWpCliCommand('vp', 'clone', array('name' => 'vp01clone', 'dbprefix' => $cloneSiteConfig->dbTablePrefix, 'yes' => null));
        $wpAutomation->editPost($postId, array('post_title' => 'Some new title'));

        $cloneWpAutomation = new WpAutomation(self::$cloneSiteConfig, self::$testConfig->wpCliVersion);
        $process = VPCommandUtils::runWpCliCommand('vp-internal', 'get-entity-id', array('require' => $this->getVPInternalCommandPath(), 'vpid' => $postVpId), self::$cloneSiteConfig->path);
        $clonedPostId = $process->getConsoleOutput();
        $cloneWpAutomation->editPost($clonedPostId, array('post_content' => 'Some new content'));

        $wpAutomation->runWpCliCommand('vp', 'pull', array('from' => self::$cloneSiteName));

        $process = VPCommandUtils::runWpCliCommand('post get', $postId, array('field' => 'post_modified'), self::$testConfig->testSite->path);
        $modifiedDate = $process->getConsoleOutput();

        $process = VPCommandUtils::runWpCliCommand('post get', $clonedPostId, array('field' => 'post_modified'), self::$cloneSiteConfig->path);
        $clonedModifiedDate = $process->getConsoleOutput();

        $this->assertEquals($clonedModifiedDate, $modifiedDate);
    }

    private function getVPInternalCommandPath() {
        return __DIR__ . '/../../src/Cli/vp-internal.php';
    }

    /**
     * @test
     *
     */
    public function sitesAreNotMergedIfThereIsConflict() {
        $cloneWpAutomation = new WpAutomation(self::$cloneSiteConfig, self::$testConfig->wpCliVersion);
        $cloneWpAutomation->editOption('blogname', 'Blogname from clone - conflict');

        $wpAutomation = new WpAutomation(self::$siteConfig, self::$testConfig->wpCliVersion);
        $wpAutomation->editOption('blogname', 'Blogname from original - conflict');

        $output = $wpAutomation->runWpCliCommand('vp', 'pull', array('from' => self::$cloneSiteName));

        $this->assertContains("Pull aborted", $output);
    }

    /**
     * Creates SiteConfig for new site based on another SiteConfig.
     *
     * @param SiteConfig $testSite
     * @param $siteName
     * @return SiteConfig
     */
    private static function changeSite(SiteConfig $testSite, $siteName) {
        $testSite = clone $testSite;
        $testSite->name = $siteName;
        $testSite->path = dirname($testSite->path) . "/$siteName";
        $testSite->url = dirname($testSite->url) . "/$siteName";
        $testSite->dbTablePrefix = "wp_{$siteName}_";

        return $testSite;
    }

    /**
     * Creates site and its clone.
     */
    private function prepareSiteWithClone() {
        $siteConfig = self::$siteConfig;
        $cloneSiteConfig = self::$cloneSiteConfig;
        $wpAutomation = new WpAutomation($siteConfig, self::$testConfig->wpCliVersion);
        $this->prepareSite($wpAutomation);
        $wpAutomation->runWpCliCommand('vp', 'clone', array('name' => 'vp01clone', 'dbprefix' => $cloneSiteConfig->dbTablePrefix, 'yes' => null));
    }

    private function getTextContentAtUrl($url) {
        $dom = new \DOMDocument();
        @$dom->loadHTML(file_get_contents($url));
        return $dom->textContent;
    }

    private function assertCloneLooksExactlySameAsOriginal() {
        $origContent = $this->getTextContentAtUrl(self::$siteConfig->url);
        // todo: remove replacing of line endings in #589
        $cloneContent = str_replace("\r\n", "\n", str_replace(self::$cloneSiteName, self::$siteConfig->name, $this->getTextContentAtUrl(self::$cloneSiteConfig->url)));

        $this->assertEquals($origContent, $cloneContent);
    }

    /**
     * @param WpAutomation $wpAutomation
     */
    private function prepareSite($wpAutomation) {
        $wpAutomation->setUpSite();
        $wpAutomation->copyVersionPressFiles();
        $wpAutomation->disableDebugger();

        $wpAutomation->activateVersionPress();
        $wpAutomation->initializeVersionPress();
    }
}

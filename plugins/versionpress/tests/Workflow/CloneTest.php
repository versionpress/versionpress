<?php

namespace VersionPress\Tests\Workflow;

use PHPUnit_Framework_TestCase;
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
        $wpAutomation->setUpSite();
        $wpAutomation->copyVersionPressFiles();
        $wpAutomation->disableDebugger();

        $wpAutomation->activateVersionPress();
        $wpAutomation->initializeVersionPress();
        $wpAutomation->runWpCliCommand('vp', 'clone', array('name' => 'vp01clone', 'dbprefix' => $cloneSiteConfig->dbTablePrefix, 'yes' => null));

        // todo: remove this SQL in #588
        $db = new \mysqli($siteConfig->dbHost, $siteConfig->dbUser, $siteConfig->dbPassword, $siteConfig->dbName);
        $db->query("
UPDATE {$cloneSiteConfig->dbTablePrefix}posts c_posts
  JOIN {$cloneSiteConfig->dbTablePrefix}vp_id c_vp_id ON c_posts.ID = c_vp_id.id AND c_vp_id.`table` = 'posts'
  JOIN {$siteConfig->dbTablePrefix}vp_id o_vp_id ON o_vp_id.vp_id = c_vp_id.vp_id
  JOIN {$siteConfig->dbTablePrefix}posts o_posts ON o_posts.ID = o_vp_id.id
  SET c_posts.post_modified = o_posts.post_modified, c_posts.post_modified_gmt = o_posts.post_modified_gmt;");
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
}
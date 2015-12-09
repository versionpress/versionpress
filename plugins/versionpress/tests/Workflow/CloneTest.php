<?php

namespace VersionPress\Tests\Workflow;

use PHPUnit_Framework_TestCase;
use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\SiteConfig;
use VersionPress\Tests\Utils\TestConfig;

class CloneTest extends PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function cloneLooksExactlySameAsOriginal() {
        $origSiteName = 'vp01orig';
        $cloneSiteName = 'vp01clone';
        $testConfig = TestConfig::createDefaultConfig();

        $siteConfig = $this->changeSite($testConfig->testSite, $origSiteName);
        $cloneSiteConfig = $this->changeSite($siteConfig, $cloneSiteName);

        $wpAutomation = new WpAutomation($siteConfig, $testConfig->wpCliVersion);
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

        $domOrig = new \DOMDocument();
        @$domOrig->loadHTML(file_get_contents($siteConfig->url));
        $domClone = new \DOMDocument();
        @$domClone->loadHTML(file_get_contents($cloneSiteConfig->url));

        $t1 = $domOrig->textContent;
        // todo: remove replacing of line endings in #589
        $t2 = str_replace("\r\n", "\n", str_replace($cloneSiteName, $origSiteName, $domClone->textContent));

        $this->assertEquals($t1, $t2);
    }

    /**
     * Creates SiteConfig for new site based on another SiteConfig.
     *
     * @param SiteConfig $testSite
     * @param $siteName
     * @return SiteConfig
     */
    private function changeSite(SiteConfig $testSite, $siteName) {
        $testSite = clone $testSite;
        $testSite->name = $siteName;
        $testSite->path = dirname($testSite->path) . "/$siteName";
        $testSite->url = dirname($testSite->url) . "/$siteName";
        $testSite->dbTablePrefix = "wp_{$siteName}_";

        return $testSite;
    }
}
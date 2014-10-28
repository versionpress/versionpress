<?php

class OptionTest extends WpCliTestCase {
    private $someOption = array(
        "option_name" => "vp_test_option",
        "option_value" => "some value"
    );

    public function testNewOption() {
        WpAutomation::createOption($this->someOption["option_name"], $this->someOption["option_value"]);

        $lastCommit = $this->getLastCommit();
        $commitAction = $lastCommit->getMessage()->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
        $this->assertEquals("option/create/{$this->someOption["option_name"]}", $commitAction);

        list($_, $__, $optionId) = explode("/", $commitAction, 3);
        $commitedOption = $this->getCommitedEntity($optionId);
        $this->assertEntityEquals($this->someOption, $commitedOption);
    }

    public function testChangeOption() {
        $changedOption = $this->someOption;
        $changedOption["option_value"] = "other value";
        WpAutomation::editOption($changedOption["option_name"], $changedOption["option_value"]);

        $lastCommit = $this->getLastCommit();
        $commitAction = $lastCommit->getMessage()->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
        $this->assertEquals("option/edit/$changedOption[option_name]", $commitAction);

        list($_, $__, $optionId) = explode("/", $commitAction, 3);
        $commitedOption = $this->getCommitedEntity($optionId);
        $this->assertEntityEquals($changedOption, $commitedOption);
    }

    public function testDeleteOption() {
        WpAutomation::deleteOption($this->someOption["option_name"]);

        $lastCommit = $this->getLastCommit();
        $commitAction = $lastCommit->getMessage()->getVersionPressTag(TrackedChangeInfo::ACTION_TAG);
        $this->assertEquals("option/delete/{$this->someOption["option_name"]}", $commitAction);
        $this->assertNull(@$this->getCommitedEntity($this->someOption["option_name"]));
    }

    protected function getCommitedEntity($vpId) {
        $path = self::$config->getSitePath() . '/wp-content/plugins/versionpress/db/options.ini';
        $options = IniSerializer::deserialize(file_get_contents($path), true);
        return $options[$vpId];
    }
} 